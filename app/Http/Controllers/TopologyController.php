<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\TopologyService;
use Illuminate\Http\Request;

class TopologyController extends Controller
{
    protected $topologyService;

    public function __construct(TopologyService $topologyService)
    {
        $this->topologyService = $topologyService;
                $this->middleware(['auth', 'permission:run monitoring']);

    }


    /**
     * Display network topology map
     */
    public function index()
    {
        $devices = Device::where('monitoring_enabled', true)
            ->whereIn('type', ['switch', 'router'])
            ->get();

        return view('topology.index', compact('devices'));
    }

    /**
     * Get topology data for visualization
     */
    public function data()
    {
        $topologyData = $this->topologyService->getTopologyMap();
        return response()->json($topologyData);
    }

    /**
     * Discover topology for a device
     */
    public function discover(Device $device)
    {
        try {
            $connections = $this->topologyService->discoverTopology($device);
            
            return response()->json([
                'success' => true,
                'message' => "Discovered " . count($connections) . " connection(s)",
                'data' => $connections,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Discover topology for all devices
     */
    public function discoverAll()
    {
        $devices = Device::where('snmp_enabled', true)
            ->where('monitoring_enabled', true)
            ->whereIn('type', ['switch', 'router'])
            ->get();

        $totalConnections = 0;

        foreach ($devices as $device) {
            $connections = $this->topologyService->discoverTopology($device);
            $totalConnections += count($connections);
        }

        return response()->json([
            'success' => true,
            'message' => "Discovered {$totalConnections} total connection(s) across {$devices->count()} device(s)",
        ]);
    }
}