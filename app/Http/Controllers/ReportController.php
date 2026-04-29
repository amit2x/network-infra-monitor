<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Location;
use App\Models\MonitoringLog;
use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ReportController extends Controller
{
    // public function __construct()
    // {
    //     // $this->middleware(['auth', 'permission:view reports']);
    //     $this->middleware('auth');
    //     $this->middleware(PermissionMiddleware::class . ':view reports');
    // }

    public function __construct()
{
    $this->middleware(['auth', 'permission:view reports']);
}
    public function inventory(Request $request)
    {
        $query = Device::with(['location.parent.parent', 'ports']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vendor')) {
            $query->where('vendor', $request->vendor);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $devices = $query->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total_devices' => $devices->count(),
            'by_type' => $devices->groupBy('type')->map->count(),
            'by_status' => $devices->groupBy('status')->map->count(),
            'by_vendor' => $devices->groupBy('vendor')->map->count(),
            'total_ports' => $devices->sum(function($d) { return $d->ports->count(); }),
            'active_ports' => $devices->sum(function($d) { return $d->ports->where('status', 'active')->count(); }),
            'critical_devices' => $devices->where('is_critical', true)->count(),
            'monitored_devices' => $devices->where('monitoring_enabled', true)->count(),
        ];

        // Filters for dropdowns
        $deviceTypes = Device::distinct()->pluck('type');
        $vendors = Device::distinct()->pluck('vendor');
        $locations = Location::where('type', 'rack')->get();

        return view('reports.inventory', compact('devices', 'stats', 'deviceTypes', 'vendors', 'locations'));
    }

    public function expiry(Request $request)
    {
        $query = Device::with(['location.parent.parent']);

        // Get devices with upcoming expiries
        if ($request->filled('expiry_type')) {
            if ($request->expiry_type == 'warranty') {
                $query->whereNotNull('warranty_expiry');
            } elseif ($request->expiry_type == 'amc') {
                $query->whereNotNull('amc_expiry');
            }
        }

        $daysFilter = $request->filled('days') ? $request->days : 90;

        $query->where(function($q) use ($daysFilter) {
            $q->whereBetween('warranty_expiry', [now(), now()->addDays($daysFilter)])
              ->orWhereBetween('amc_expiry', [now(), now()->addDays($daysFilter)]);
        });

        $devices = $query->orderBy('warranty_expiry')
                        ->orderBy('amc_expiry')
                        ->get()
                        ->map(function($device) {
                            $device->warranty_days_left = $device->warranty_expiry
                                ? now()->diffInDays($device->warranty_expiry, false)
                                : null;
                            $device->amc_days_left = $device->amc_expiry
                                ? now()->diffInDays($device->amc_expiry, false)
                                : null;
                            return $device;
                        });

        // Expiry statistics
        $stats = [
            'total_expiring' => $devices->count(),
            'warranty_expiring' => $devices->where('warranty_days_left', '>', 0)->count(),
            'warranty_expired' => $devices->where('warranty_days_left', '<=', 0)->where('warranty_days_left', '!=', null)->count(),
            'amc_expiring' => $devices->where('amc_days_left', '>', 0)->count(),
            'amc_expired' => $devices->where('amc_days_left', '<=', 0)->where('amc_days_left', '!=', null)->count(),
            'critical_expiring' => $devices->where('is_critical', true)->count(),
        ];

        return view('reports.expiry', compact('devices', 'stats', 'daysFilter'));
    }

    public function portUsage(Request $request)
    {
        $devices = Device::with(['ports', 'location.parent.parent'])
            ->when($request->filled('device_id'), function($query) use ($request) {
                return $query->where('id', $request->device_id);
            })
            ->when($request->filled('location_id'), function($query) use ($request) {
                return $query->where('location_id', $request->location_id);
            })
            ->orderBy('name')
            ->get()
            ->map(function($device) {
                $device->total_ports = $device->ports->count();
                $device->active_ports = $device->ports->where('status', 'active')->count();
                $device->free_ports = $device->ports->where('status', 'free')->count();
                $device->down_ports = $device->ports->where('status', 'down')->count();
                $device->disabled_ports = $device->ports->where('status', 'disabled')->count();
                $device->utilization_percent = $device->total_ports > 0
                    ? round(($device->active_ports / $device->total_ports) * 100, 2)
                    : 0;
                $device->copper_ports = $device->ports->where('type', 'copper')->count();
                $device->sfp_ports = $device->ports->whereIn('type', ['sfp', 'sfp_plus', 'qsfp'])->count();
                return $device;
            });

        $allDevices = Device::orderBy('name')->get();
        $locations = Location::where('type', 'rack')->get();

        // Overall statistics
        $stats = [
            'total_devices_with_ports' => $devices->where('total_ports', '>', 0)->count(),
            'total_ports' => $devices->sum('total_ports'),
            'total_active' => $devices->sum('active_ports'),
            'total_free' => $devices->sum('free_ports'),
            'total_down' => $devices->sum('down_ports'),
            'overall_utilization' => $devices->sum('total_ports') > 0
                ? round(($devices->sum('active_ports') / $devices->sum('total_ports')) * 100, 2)
                : 0,
        ];

        return view('reports.port-usage', compact('devices', 'stats', 'allDevices', 'locations'));
    }

    public function availability(Request $request)
    {
        $dateRange = $request->filled('days') ? $request->days : 30;
        $startDate = now()->subDays($dateRange);

        // Device availability calculation
        $devices = Device::with(['location.parent.parent'])
            ->when($request->filled('device_id'), function($query) use ($request) {
                return $query->where('id', $request->device_id);
            })
            ->orderBy('name')
            ->get()
            ->map(function($device) use ($startDate) {
                $totalChecks = MonitoringLog::where('device_id', $device->id)
                    ->where('event_type', 'ping_check')
                    ->where('created_at', '>=', $startDate)
                    ->count();

                $successfulChecks = MonitoringLog::where('device_id', $device->id)
                    ->where('event_type', 'ping_check')
                    ->where('status', 'success')
                    ->where('created_at', '>=', $startDate)
                    ->count();

                $device->availability_percent = $totalChecks > 0
                    ? round(($successfulChecks / $totalChecks) * 100, 2)
                    : 0;
                $device->total_checks = $totalChecks;
                $device->successful_checks = $successfulChecks;
                $device->failed_checks = $totalChecks - $successfulChecks;
                $device->avg_response_time = MonitoringLog::where('device_id', $device->id)
                    ->where('event_type', 'ping_check')
                    ->where('status', 'success')
                    ->where('created_at', '>=', $startDate)
                    ->avg('response_time_ms');

                return $device;
            });

        // Daily availability trend
        $dailyTrend = [];
        for ($i = $dateRange; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayChecks = MonitoringLog::where('event_type', 'ping_check')
                ->whereDate('created_at', $date);

            $dailyTrend[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $dayChecks->count(),
                'successful' => $dayChecks->where('status', 'success')->count(),
                'percentage' => $dayChecks->count() > 0
                    ? round(($dayChecks->where('status', 'success')->count() / $dayChecks->count()) * 100, 2)
                    : 0,
                'avg_response_time' => round($dayChecks->where('status', 'success')->avg('response_time_ms') ?? 0, 2)
            ];
        }

        $allDevices = Device::orderBy('name')->get();

        $stats = [
            'total_devices' => $devices->count(),
            'average_availability' => round($devices->avg('availability_percent'), 2),
            'high_availability' => $devices->where('availability_percent', '>=', 99)->count(),
            'medium_availability' => $devices->where('availability_percent', '>=', 95)->where('availability_percent', '<', 99)->count(),
            'low_availability' => $devices->where('availability_percent', '<', 95)->count(),
            'overall_avg_response_time' => round($devices->avg('avg_response_time'), 2),
        ];

        return view('reports.availability', compact('devices', 'stats', 'dailyTrend', 'dateRange', 'allDevices'));
    }

    public function export($type, Request $request)
    {
        try {
            switch ($type) {
                case 'inventory':
                    return $this->exportInventory();
                case 'expiry':
                    return $this->exportExpiry();
                case 'port-usage':
                    return $this->exportPortUsage();
                case 'availability':
                    return $this->exportAvailability();
                default:
                    return back()->with('error', 'Invalid export type');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function exportInventory()
    {
        $devices = Device::with('location')->get();

        $csvData = [];
        $csvData[] = ['Device Code', 'Name', 'Type', 'Vendor', 'Model', 'Serial Number',
                      'IP Address', 'MAC Address', 'Status', 'Location', 'Warranty Expiry',
                      'AMC Expiry', 'Critical', 'Monitored'];

        foreach ($devices as $device) {
            $csvData[] = [
                $device->device_code,
                $device->name,
                $device->type,
                $device->vendor,
                $device->model,
                $device->serial_number,
                $device->ip_address,
                $device->mac_address,
                $device->status,
                $device->location->full_path ?? 'N/A',
                optional($device->warranty_expiry)->format('Y-m-d'),
                optional($device->amc_expiry)->format('Y-m-d'),
                $device->is_critical ? 'Yes' : 'No',
                $device->monitoring_enabled ? 'Yes' : 'No',
            ];
        }

        return $this->downloadCSV($csvData, 'device-inventory-' . now()->format('Y-m-d') . '.csv');
    }

    private function exportExpiry()
    {
        $devices = Device::with('location')
            ->where(function($q) {
                $q->whereDate('warranty_expiry', '<=', now()->addDays(90))
                  ->orWhereDate('amc_expiry', '<=', now()->addDays(90));
            })
            ->get();

        $csvData = [];
        $csvData[] = ['Device Name', 'Type', 'Vendor', 'Location', 'Warranty Expiry',
                      'Days Left (Warranty)', 'AMC Expiry', 'Days Left (AMC)', 'Critical'];

        foreach ($devices as $device) {
            $csvData[] = [
                $device->name,
                $device->type,
                $device->vendor,
                $device->location->full_path ?? 'N/A',
                optional($device->warranty_expiry)->format('Y-m-d'),
                $device->warranty_expiry ? now()->diffInDays($device->warranty_expiry, false) : 'N/A',
                optional($device->amc_expiry)->format('Y-m-d'),
                $device->amc_expiry ? now()->diffInDays($device->amc_expiry, false) : 'N/A',
                $device->is_critical ? 'Yes' : 'No',
            ];
        }

        return $this->downloadCSV($csvData, 'expiry-report-' . now()->format('Y-m-d') . '.csv');
    }

    private function exportPortUsage()
    {
        $devices = Device::with('ports')->get();

        $csvData = [];
        $csvData[] = ['Device Name', 'Total Ports', 'Active', 'Free', 'Down', 'Disabled',
                      'Utilization %', 'Copper Ports', 'SFP Ports'];

        foreach ($devices as $device) {
            $csvData[] = [
                $device->name,
                $device->ports->count(),
                $device->ports->where('status', 'active')->count(),
                $device->ports->where('status', 'free')->count(),
                $device->ports->where('status', 'down')->count(),
                $device->ports->where('status', 'disabled')->count(),
                $device->ports->count() > 0
                    ? round(($device->ports->where('status', 'active')->count() / $device->ports->count()) * 100, 2) . '%'
                    : '0%',
                $device->ports->where('type', 'copper')->count(),
                $device->ports->whereIn('type', ['sfp', 'sfp_plus', 'qsfp'])->count(),
            ];
        }

        return $this->downloadCSV($csvData, 'port-usage-' . now()->format('Y-m-d') . '.csv');
    }

    private function exportAvailability()
    {
        $devices = Device::with('monitoringLogs')->get();

        $csvData = [];
        $csvData[] = ['Device Name', 'IP Address', 'Availability %', 'Total Checks',
                      'Successful Checks', 'Failed Checks', 'Avg Response Time (ms)'];

        foreach ($devices as $device) {
            $totalChecks = $device->monitoringLogs->where('event_type', 'ping_check')->count();
            $successfulChecks = $device->monitoringLogs->where('event_type', 'ping_check')->where('status', 'success')->count();

            $csvData[] = [
                $device->name,
                $device->ip_address,
                $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 2) . '%' : '0%',
                $totalChecks,
                $successfulChecks,
                $totalChecks - $successfulChecks,
                round($device->monitoringLogs->where('event_type', 'ping_check')->where('status', 'success')->avg('response_time_ms') ?? 0, 2),
            ];
        }

        return $this->downloadCSV($csvData, 'availability-report-' . now()->format('Y-m-d') . '.csv');
    }

    private function downloadCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
