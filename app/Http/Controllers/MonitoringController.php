<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MonitoringLog;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function logs(Request $request)
    {
        $query = MonitoringLog::with('device');

        // Apply filters
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);
        $devices = Device::orderBy('name')->get(['id', 'name']);

        return view('monitoring.logs', compact('logs', 'devices'));
    }

    public function stats()
    {
        $stats = [
            'total_checks' => MonitoringLog::where('event_type', 'ping_check')->count(),
            'success_rate' => MonitoringLog::where('event_type', 'ping_check')
                ->where('status', 'success')
                ->count(),
            'avg_response_time' => MonitoringLog::where('event_type', 'ping_check')
                ->where('status', 'success')
                ->avg('response_time_ms'),
            'uptime_today' => $this->calculateUptime(now()),
            'uptime_week' => $this->calculateUptime(now()->subWeek()),
            'device_availability' => $this->getDeviceAvailability(),
            'response_time_trend' => $this->getResponseTimeTrend(),
        ];

        return view('monitoring.stats', compact('stats'));
    }

    public function runMonitoring()
    {
        $results = $this->monitoringService->runMonitoringCycle();
        $this->monitoringService->checkExpiryDates();

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Monitoring cycle completed successfully'
        ]);
    }

    private function calculateUptime($since)
    {
        $totalChecks = MonitoringLog::where('event_type', 'ping_check')
            ->where('created_at', '>=', $since)
            ->count();

        if ($totalChecks === 0) return 0;

        $successfulChecks = MonitoringLog::where('event_type', 'ping_check')
            ->where('status', 'success')
            ->where('created_at', '>=', $since)
            ->count();

        return round(($successfulChecks / $totalChecks) * 100, 2);
    }

    private function getDeviceAvailability()
    {
        return Device::withCount(['monitoringLogs as total_checks' => function($query) {
            $query->where('event_type', 'ping_check');
        }])
        ->withCount(['monitoringLogs as successful_checks' => function($query) {
            $query->where('event_type', 'ping_check')
                  ->where('status', 'success');
        }])
        ->get()
        ->map(function($device) {
            $device->availability = $device->total_checks > 0
                ? round(($device->successful_checks / $device->total_checks) * 100, 2)
                : 0;
            return $device;
        });
    }

    private function getResponseTimeTrend()
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $avgTime = MonitoringLog::where('event_type', 'ping_check')
                ->where('status', 'success')
                ->whereDate('created_at', $date)
                ->avg('response_time_ms');

            $trend[] = [
                'date' => $date->format('Y-m-d'),
                'avg_response_time' => round($avgTime ?? 0, 2)
            ];
        }

        return $trend;
    }
}
