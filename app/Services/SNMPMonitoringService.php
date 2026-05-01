<?php

namespace App\Services;

use App\Models\Device;
use App\Models\SnmpData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SNMPMonitoringService
{
    protected $snmp;
    protected $deviceService;
    protected $metrics = [];
    protected $batchSize = 10;
    protected $batchDelay = 500000; // 500ms in microseconds

    public function __construct(SNMPService $snmp, DeviceService $deviceService)
    {
        $this->snmp = $snmp;
        $this->deviceService = $deviceService;
    }

    /**
     * Run SNMP monitoring cycle for all eligible devices
     */
    public function runMonitoringCycle(): array
    {
        $startTime = microtime(true);
        $results = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'duration' => 0,
            'details' => [],
        ];

        // Get devices with SNMP enabled
        $devices = Device::where('snmp_enabled', true)
            ->where('monitoring_enabled', true)
            ->whereIn('status', ['online', 'offline'])
            ->orderBy('is_critical', 'desc')
            ->get();

        $results['total'] = $devices->count();

        // Process in batches to avoid overwhelming network
        $batches = $devices->chunk($this->batchSize);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $device) {
                try {
                    $deviceResult = $this->monitorDevice($device);
                    
                    if ($deviceResult['success']) {
                        $results['successful']++;
                    } else {
                        $results['failed']++;
                    }
                    
                    $results['details'][] = $deviceResult;
                } catch (\Exception $e) {
                    $results['failed']++;
                    Log::error("SNMP monitoring failed for device {$device->id}: " . $e->getMessage());
                }
            }

            // Delay between batches (except last batch)
            if ($batchIndex < $batches->count() - 1) {
                usleep($this->batchDelay);
            }
        }

        $results['duration'] = round(microtime(true) - $startTime, 2);
        
        // Save monitoring results
        $this->saveMonitoringResults($results);

        return $results;
    }

    /**
     * Monitor a single device via SNMP
     */
    protected function monitorDevice(Device $device): array
    {
        $result = [
            'device_id' => $device->id,
            'device_name' => $device->name,
            'success' => false,
            'timestamp' => now()->toIso8601String(),
            'data' => [],
        ];

        try {
            // Get system info
            $systemInfo = $this->snmp->getSystemInfo(
                $device->ip_address,
                $device->snmp_community ?? null
            );

            if (!$systemInfo['snmp_enabled']) {
                $result['error'] = $systemInfo['error'] ?? 'SNMP not responding';
                return $result;
            }

            $result['data']['system'] = $systemInfo;

            // Get CPU usage (for non-switch devices or specific types)
            if ($device->type !== 'access_point') {
                $cpuData = $this->snmp->getCPUUsage(
                    $device->ip_address,
                    $device->vendor,
                    $device->snmp_community ?? null
                );
                $result['data']['cpu'] = $cpuData;
            }

            // Get memory usage
            if ($device->type !== 'access_point') {
                $memoryData = $this->snmp->getMemoryUsage(
                    $device->ip_address,
                    $device->vendor,
                    $device->snmp_community ?? null
                );
                $result['data']['memory'] = $memoryData;
            }

            // Get interface stats (for switches and routers)
            if (in_array($device->type, ['switch', 'router'])) {
                $interfaceData = $this->snmp->getInterfaceStats(
                    $device->ip_address,
                    $device->snmp_community ?? null
                );
                $result['data']['interfaces'] = $interfaceData;
            }

            // Save to database
            $this->saveDeviceData($device, $result['data']);

            // Update device status based on SNMP response
            if ($device->status !== 'maintenance' && $device->status !== 'decommissioned') {
                $device->update(['status' => 'online']);
            }

            $result['success'] = true;
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            Log::error("SNMP monitor failed for {$device->name}: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Save SNMP data to database
     */
    protected function saveDeviceData(Device $device, array $data): void
    {
        try {
            $snmpData = new SnmpData();
            $snmpData->device_id = $device->id;
            $snmpData->system_info = $data['system'] ?? null;
            $snmpData->cpu_usage = $data['cpu']['5sec'] ?? null;
            $snmpData->memory_usage = $data['memory']['usage_percent'] ?? null;
            $snmpData->memory_total = $data['memory']['total'] ?? null;
            $snmpData->memory_used = $data['memory']['used'] ?? null;
            $snmpData->interface_count = count($data['interfaces'] ?? []);
            $snmpData->interfaces_data = $data['interfaces'] ?? null;
            $snmpData->raw_data = $data;
            $snmpData->collected_at = now();
            $snmpData->save();

            // Keep only last 24 hours of data for non-critical devices
            if (!$device->is_critical) {
                SnmpData::where('device_id', $device->id)
                    ->where('collected_at', '<', now()->subHours(24))
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error("Failed to save SNMP data: " . $e->getMessage());
        }
    }

    /**
     * Get device performance metrics
     */
    public function getDeviceMetrics(Device $device, int $hours = 24): array
    {
        $data = SnmpData::where('device_id', $device->id)
            ->where('collected_at', '>=', now()->subHours($hours))
            ->orderBy('collected_at')
            ->get();

        return [
            'cpu_trend' => $data->pluck('cpu_usage', 'collected_at')->toArray(),
            'memory_trend' => $data->pluck('memory_usage', 'collected_at')->toArray(),
            'avg_cpu' => round($data->avg('cpu_usage'), 2),
            'avg_memory' => round($data->avg('memory_usage'), 2),
            'max_cpu' => $data->max('cpu_usage'),
            'max_memory' => $data->max('memory_usage'),
            'data_points' => $data->count(),
        ];
    }

    /**
     * Save monitoring results
     */
    protected function saveMonitoringResults(array $results): void
    {
        // Could save to a summary table or log file
        Log::info("SNMP Monitoring Cycle Complete", [
            'total' => $results['total'],
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'duration' => $results['duration'],
        ]);
    }
    
    /**
     * Check thresholds and generate alerts.
     */
    protected function checkThresholds(Device $device, array $data): void
    {
        $thresholds = [
            'cpu' => [
                'warning' => 70,
                'critical' => 90,
            ],
            'memory' => [
                'warning' => 75,
                'critical' => 90,
            ],
        ];
    
        // Check CPU threshold
        if (isset($data['cpu']['5sec'])) {
            $cpuUsage = (int) $data['cpu']['5sec'];
            
            if ($cpuUsage >= $thresholds['cpu']['critical']) {
                $this->createThresholdAlert($device, 'cpu', $cpuUsage, 'critical');
                event(new \App\Events\SNMPThresholdExceeded($device, 'cpu', $cpuUsage, $thresholds['cpu']['critical']));
            } elseif ($cpuUsage >= $thresholds['cpu']['warning']) {
                $this->createThresholdAlert($device, 'cpu', $cpuUsage, 'warning');
            }
        }
    
        // Check Memory threshold
        if (isset($data['memory']['usage_percent'])) {
            $memUsage = (float) $data['memory']['usage_percent'];
            
            if ($memUsage >= $thresholds['memory']['critical']) {
                $this->createThresholdAlert($device, 'memory', $memUsage, 'critical');
                event(new \App\Events\SNMPThresholdExceeded($device, 'memory', $memUsage, $thresholds['memory']['critical']));
            } elseif ($memUsage >= $thresholds['memory']['warning']) {
                $this->createThresholdAlert($device, 'memory', $memUsage, 'warning');
            }
        }
    }
    
    /**
     * Create threshold alert.
     */
    protected function createThresholdAlert(Device $device, string $metric, float $value, string $severity): void
    {
        // Check if recent alert exists to avoid duplicates
        $recentAlert = \App\Models\Alert::where('device_id', $device->id)
            ->where('type', "high_{$metric}")
            ->where('is_resolved', false)
            ->where('created_at', '>=', now()->subHour())
            ->exists();
    
        if (!$recentAlert) {
            \App\Models\Alert::create([
                'device_id' => $device->id,
                'type' => "high_{$metric}",
                'severity' => $severity,
                'title' => "High " . strtoupper($metric) . " Usage: {$device->name}",
                'message' => "Device {$device->name} ({$device->ip_address}) {$metric} usage is at {$value}%",
                'additional_data' => [
                    'metric' => $metric,
                    'value' => $value,
                    'threshold' => $severity === 'critical' ? 90 : 70,
                ],
            ]);
        }
    }
}