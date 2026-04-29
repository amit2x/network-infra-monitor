<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceService;
use App\Models\Alert;
use App\Models\Device;
use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function stats()
    {
        $stats = [
            'devices' => [
                'total' => Device::count(),
                'online' => Device::where('status', 'online')->count(),
                'offline' => Device::where('status', 'offline')->count(),
                'maintenance' => Device::where('status', 'maintenance')->count(),
            ],
            'ports' => [
                'total' => \App\Models\Port::count(),
                'active' => \App\Models\Port::where('status', 'active')->count(),
                'free' => \App\Models\Port::where('status', 'free')->count(),
                'down' => \App\Models\Port::where('status', 'down')->count(),
            ],
            'alerts' => [
                'total' => Alert::count(),
                'critical' => Alert::where('severity', 'critical')->where('is_resolved', false)->count(),
                'unresolved' => Alert::where('is_resolved', false)->count(),
            ],
            'monitoring' => [
                'success_rate' => $this->getSuccessRate(),
                'avg_response_time' => $this->getAvgResponseTime(),
                'last_check' => MonitoringLog::latest()->first()?->created_at?->toISOString(),
            ]
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    private function getSuccessRate()
    {
        $total = MonitoringLog::where('event_type', 'ping_check')->count();
        if ($total === 0) return 0;

        $success = MonitoringLog::where('event_type', 'ping_check')
            ->where('status', 'success')
            ->count();

        return round(($success / $total) * 100, 2);
    }

    private function getAvgResponseTime()
    {
        return round(MonitoringLog::where('event_type', 'ping_check')
            ->where('status', 'success')
            ->avg('response_time_ms') ?? 0, 2);
    }
}
