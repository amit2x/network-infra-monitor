<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Port;
use App\Models\MonitoringLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceService
{
    public function createDevice(array $data): Device
    {
        DB::beginTransaction();
        try {
            $data['device_code'] = $this->generateDeviceCode($data['type']);
            
            // Set SNMP defaults if enabled
            if (isset($data['snmp_enabled']) && $data['snmp_enabled']) {
                $data['snmp_port'] = $data['snmp_port'] ?? 161;
                $data['snmp_timeout'] = $data['snmp_timeout'] ?? 1;
                $data['snmp_version'] = $data['snmp_version'] ?? '2c';
                $data['snmp_community'] = $data['snmp_community'] ?? config('snmp.defaults.community', 'public');
                $data['snmp_polling_interval'] = $data['snmp_polling_interval'] ?? 300;
            }
            
            $device = Device::create($data);
            
            // Create default ports if type is switch
            if ($data['type'] === 'switch') {
                $this->createDefaultPorts($device, $data['port_count'] ?? 24);
            }
            
            DB::commit();
            return $device;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create device: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateDevice(Device $device, array $data): Device
    {
        $device->update($data);
        return $device->fresh();
    }

    public function deleteDevice(Device $device): bool
    {
        return $device->delete();
    }

    public function pingDevice(Device $device): array
    {
        $ip = $device->ip_address;
        $startTime = microtime(true);

        // Execute ping command
        $result = $this->executePing($ip);
        $responseTime = (microtime(true) - $startTime) * 1000;

        $status = $result['success'] ? 'online' : 'offline';

        // Update device status
        $oldStatus = $device->status;
        $device->update(['status' => $status]);

        // Log monitoring event
        MonitoringLog::create([
            'device_id' => $device->id,
            'event_type' => 'ping_check',
            'status' => $result['success'] ? 'success' : 'failure',
            'message' => $result['message'],
            'response_time_ms' => $result['success'] ? $responseTime : null,
            'details' => [
                'output' => $result['output'] ?? null,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]
        ]);

        return [
            'success' => $result['success'],
            'message' => $result['message'],
            'response_time' => $responseTime,
            'status_changed' => $oldStatus !== $status
        ];
    }

    private function executePing(string $ip): array
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            $command = "ping -n 1 -w 2000 " . escapeshellarg($ip);
        } else {
            $command = "ping -c 1 -W 2 " . escapeshellarg($ip);
        }

        exec($command, $output, $returnVar);

        return [
            'success' => $returnVar === 0,
            'message' => $returnVar === 0 ? 'Device is reachable' : 'Device is unreachable',
            'output' => implode("\n", $output)
        ];
    }

    public function generateDeviceCode(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $count = Device::where('device_code', 'like', $prefix . '%')->count();
        return $prefix . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    private function createDefaultPorts(Device $device, int $count): void
    {
        $ports = [];
        for ($i = 1; $i <= $count; $i++) {
            $ports[] = [
                'device_id' => $device->id,
                'port_number' => $i,
                'type' => $i <= 24 ? 'copper' : 'sfp',
                'status' => 'free',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Port::insert($ports);
    }

    public function getDeviceStats(): array
    {
        return [
            'total' => Device::count(),
            'online' => Device::where('status', 'online')->count(),
            'offline' => Device::where('status', 'offline')->count(),
            'maintenance' => Device::where('status', 'maintenance')->count(),
            'by_type' => Device::groupBy('type')
                ->select('type', DB::raw('count(*) as count'))
                ->pluck('count', 'type')
                ->toArray()
        ];
    }
}
