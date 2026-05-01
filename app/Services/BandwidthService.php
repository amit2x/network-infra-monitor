<?php

namespace App\Services;

use App\Models\Device;
use App\Models\BandwidthData;
use App\Models\Port;
use Illuminate\Support\Facades\Cache;

class BandwidthService
{
    protected $snmpService;

    public function __construct(SNMPService $snmpService)
    {
        $this->snmpService = $snmpService;
    }

    /**
     * Collect bandwidth data for a device
     */
    public function collectBandwidthData(Device $device): array
    {
        $results = [];
        
        if (!$device->snmp_enabled) {
            return $results;
        }

        try {
            $interfaces = $this->snmpService->getInterfaceStats(
                $device->ip_address,
                $device->snmp_community
            );

            $previousData = BandwidthData::where('device_id', $device->id)
                ->orderBy('collected_at', 'desc')
                ->take(count($interfaces))
                ->get()
                ->keyBy('port_number');

            foreach ($interfaces as $interface) {
                $portNumber = $interface['index'];
                $previous = $previousData->get($portNumber);
                
                $bandwidthData = new BandwidthData([
                    'device_id' => $device->id,
                    'port_number' => $portNumber,
                    'in_octets' => $interface['in_octets'],
                    'out_octets' => $interface['out_octets'],
                    'collected_at' => now(),
                ]);

                // Calculate bandwidth if we have previous data
                if ($previous) {
                    $timeDiff = now()->diffInSeconds($previous->collected_at);
                    
                    if ($timeDiff > 0) {
                        $inOctetsDiff = $interface['in_octets'] - $previous->in_octets;
                        $outOctetsDiff = $interface['out_octets'] - $previous->out_octets;
                        
                        // Handle counter wrap (32-bit)
                        if ($inOctetsDiff < 0) $inOctetsDiff += 4294967296;
                        if ($outOctetsDiff < 0) $outOctetsDiff += 4294967296;
                        
                        $bandwidthData->in_bandwidth_bps = ($inOctetsDiff * 8) / $timeDiff;
                        $bandwidthData->out_bandwidth_bps = ($outOctetsDiff * 8) / $timeDiff;
                        
                        // Calculate utilization
                        $speed = $this->parseSpeedToBps($interface['speed']);
                        if ($speed > 0) {
                            $bandwidthData->in_utilization_percent = round(($bandwidthData->in_bandwidth_bps / $speed) * 100, 2);
                            $bandwidthData->out_utilization_percent = round(($bandwidthData->out_bandwidth_bps / $speed) * 100, 2);
                            $bandwidthData->port_speed = $speed;
                        }
                    }
                }

                $bandwidthData->save();
                $results[] = $bandwidthData;
            }

        } catch (\Exception $e) {
            \Log::error("Bandwidth collection failed for {$device->name}: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Get bandwidth trend data for a port
     */
    public function getBandwidthTrend(Device $device, int $portNumber, int $hours = 24): array
    {
        $data = BandwidthData::where('device_id', $device->id)
            ->where('port_number', $portNumber)
            ->where('collected_at', '>=', now()->subHours($hours))
            ->orderBy('collected_at')
            ->get();

        return [
            'labels' => $data->pluck('collected_at')->map(function ($date) {
                return $date->format('H:i');
            })->toArray(),
            'in_bps' => $data->pluck('in_bandwidth_bps')->toArray(),
            'out_bps' => $data->pluck('out_bandwidth_bps')->toArray(),
            'in_utilization' => $data->pluck('in_utilization_percent')->toArray(),
            'out_utilization' => $data->pluck('out_utilization_percent')->toArray(),
            'avg_in' => round($data->avg('in_bandwidth_bps') ?? 0),
            'avg_out' => round($data->avg('out_bandwidth_bps') ?? 0),
            'max_in' => round($data->max('in_bandwidth_bps') ?? 0),
            'max_out' => round($data->max('out_bandwidth_bps') ?? 0),
        ];
    }

    /**
     * Get top talkers (highest bandwidth ports)
     */
    public function getTopTalkers(int $limit = 10): array
    {
        return BandwidthData::with('device')
            ->where('collected_at', '>=', now()->subHour())
            ->orderByDesc('in_bandwidth_bps')
            ->take($limit)
            ->get()
            ->map(function ($data) {
                return [
                    'device_name' => $data->device->name,
                    'port_number' => $data->port_number,
                    'in_bandwidth' => $this->formatBandwidth($data->in_bandwidth_bps),
                    'out_bandwidth' => $this->formatBandwidth($data->out_bandwidth_bps),
                    'in_utilization' => $data->in_utilization_percent,
                    'out_utilization' => $data->out_utilization_percent,
                ];
            })
            ->toArray();
    }

    /**
     * Parse speed string to bps
     */
    protected function parseSpeedToBps(string $speed): int
    {
        if (str_contains($speed, 'Gbps')) {
            return (int)((float)$speed * 1000000000);
        }
        if (str_contains($speed, 'Mbps')) {
            return (int)((float)$speed * 1000000);
        }
        if (str_contains($speed, 'Kbps')) {
            return (int)((float)$speed * 1000);
        }
        return (int)$speed;
    }

    /**
     * Format bandwidth to human readable
     */
    public function formatBandwidth($bps): string
    {
        if ($bps === null || $bps === 0) return '0 bps';
        
        $units = ['bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps'];
        $unitIndex = 0;
        
        while ($bps >= 1000 && $unitIndex < count($units) - 1) {
            $bps /= 1000;
            $unitIndex++;
        }
        
        return round($bps, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Clean old bandwidth data
     */
    public function cleanOldData(int $days = 7): int
    {
        return BandwidthData::where('collected_at', '<', now()->subDays($days))->delete();
    }
}