<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Alert;
use App\Models\MonitoringLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeviceDownAlert;

class MonitoringService
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function runMonitoringCycle(): array
    {
        $devices = Device::where('monitoring_enabled', true)
            ->whereIn('status', ['online', 'offline', 'maintenance'])
            ->get();

        $results = [
            'total' => $devices->count(),
            'checked' => 0,
            'online' => 0,
            'offline' => 0,
            'status_changes' => 0,
            'alerts_generated' => 0
        ];

        foreach ($devices as $device) {
            try {
                $pingResult = $this->deviceService->pingDevice($device);
                $results['checked']++;

                if ($pingResult['success']) {
                    $results['online']++;
                } else {
                    $results['offline']++;
                }

                if ($pingResult['status_changed']) {
                    $results['status_changes']++;

                    if (!$pingResult['success']) {
                        $this->generateDeviceDownAlert($device);
                        $results['alerts_generated']++;
                    } else {
                        $this->generateDeviceUpAlert($device);
                        $results['alerts_generated']++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Monitoring failed for device {$device->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    protected function generateDeviceDownAlert(Device $device): void
    {
        $alert = Alert::create([
            'device_id' => $device->id,
            'type' => 'device_down',
            'severity' => $device->is_critical ? 'critical' : 'high',
            'title' => "Device Down: {$device->name}",
            'message' => "Device {$device->name} ({$device->ip_address}) is not responding to ping. Location: {$device->location->full_path}",
            'additional_data' => [
                'device_type' => $device->type,
                'location' => $device->location->full_path
            ]
        ]);

        // Send email notification to network engineers
        $this->sendAlertNotification($alert);
    }

    protected function generateDeviceUpAlert(Device $device): void
    {
        Alert::create([
            'device_id' => $device->id,
            'type' => 'device_up',
            'severity' => 'low',
            'title' => "Device Up: {$device->name}",
            'message' => "Device {$device->name} ({$device->ip_address}) is now responding to ping.",
            'additional_data' => [
                'device_type' => $device->type,
                'location' => $device->location->full_path
            ]
        ]);
    }

    protected function sendAlertNotification(Alert $alert): void
    {
        try {
            // Get users with network engineer role
            $users = \App\Models\User::role('network_engineer')->get();

            foreach ($users as $user) {
                Mail::to($user->email)->queue(new DeviceDownAlert($alert));
            }
        } catch (\Exception $e) {
            Log::error("Failed to send alert notification: " . $e->getMessage());
        }
    }

    public function checkExpiryDates(): void
    {
        $devices = Device::where(function($query) {
            $query->whereDate('warranty_expiry', '<=', now()->addDays(30))
                  ->orWhereDate('amc_expiry', '<=', now()->addDays(30));
        })->get();

        foreach ($devices as $device) {
            if ($device->warranty_expiry && $device->warranty_expiry->diffInDays(now()) <= 30) {
                $this->generateExpiryAlert($device, 'warranty');
            }

            if ($device->amc_expiry && $device->amc_expiry->diffInDays(now()) <= 30) {
                $this->generateExpiryAlert($device, 'amc');
            }
        }
    }

    protected function generateExpiryAlert(Device $device, string $type): void
    {
        $expiryDate = $type === 'warranty' ? $device->warranty_expiry : $device->amc_expiry;
        $daysUntilExpiry = now()->diffInDays($expiryDate, false);

        Alert::create([
            'device_id' => $device->id,
            'type' => $type . '_expiry',
            'severity' => $daysUntilExpiry <= 7 ? 'high' : 'medium',
            'title' => ucfirst($type) . " Expiring: {$device->name}",
            'message' => "Device {$device->name} {$type} is expiring on {$expiryDate->format('d-M-Y')} ({$daysUntilExpiry} days remaining)",
            'additional_data' => [
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'days_remaining' => $daysUntilExpiry
            ]
        ]);
    }
}
