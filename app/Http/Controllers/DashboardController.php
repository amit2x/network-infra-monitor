<?php

namespace App\Http\Controllers;

use App\Services\DeviceService;
use App\Models\Alert;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index()
    {
        $deviceStats = $this->deviceService->getDeviceStats();

        // Get recent alerts
        $recentAlerts = Alert::with('device')
            ->where('is_resolved', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get device status distribution for chart
        $deviceStatusDistribution = Device::groupBy('status')
            ->select('status', DB::raw('count(*) as count'))
            ->pluck('count', 'status')
            ->toArray();

        // Get port utilization
        $totalPorts = \App\Models\Port::count();
        $activePorts = \App\Models\Port::where('status', 'active')->count();
        $portUtilization = $totalPorts > 0 ? round(($activePorts / $totalPorts) * 100, 2) : 0;

        return view('dashboard', compact(
            'deviceStats',
            'recentAlerts',
            'deviceStatusDistribution',
            'portUtilization',
            'totalPorts',
            'activePorts'
        ));
    }

    public function getDashboardData()
    {
        $data = [
            'deviceStats' => $this->deviceService->getDeviceStats(),
            'alertStats' => [
                'total' => Alert::count(),
                'critical' => Alert::where('severity', 'critical')->where('is_resolved', false)->count(),
                'unresolved' => Alert::where('is_resolved', false)->count(),
            ],
            'recentActivity' => \App\Models\MonitoringLog::with('device')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function($log) {
                    return [
                        'time' => $log->created_at->diffForHumans(),
                        'device' => $log->device->name,
                        'event' => $log->event_type,
                        'status' => $log->status,
                        'message' => $log->message
                    ];
                })
        ];

        return response()->json($data);
    }
}
