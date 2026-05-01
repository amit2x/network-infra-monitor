<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\BandwidthService;
use Illuminate\Http\Request;

class BandwidthController extends Controller 
{
    protected $bandwidthService;

    public function __construct(BandwidthService $bandwidthService)
    {
        $this->bandwidthService = $bandwidthService;
        $this->middleware(['auth', 'permission:view reports']);

    }

    

    /**
     * Display bandwidth dashboard
     */
    public function dashboard()
    {
        $topTalkers = $this->bandwidthService->getTopTalkers(20);
        $devices = Device::where('snmp_enabled', true)
            ->whereIn('type', ['switch', 'router'])
            ->get();

        return view('bandwidth.dashboard', compact('topTalkers', 'devices'));
    }

    /**
     * Get bandwidth data for a device port
     */
    public function getPortBandwidth(Device $device, Request $request)
    {
        $request->validate([
            'port_number' => 'required|integer',
            'hours' => 'nullable|integer|min:1|max:168',
        ]);

        $portNumber = $request->port_number;
        $hours = $request->hours ?? 24;

        $data = $this->bandwidthService->getBandwidthTrend($device, $portNumber, $hours);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Collect bandwidth data now
     */
    public function collectNow(Device $device)
    {
        try {
            $results = $this->bandwidthService->collectBandwidthData($device);
            
            return response()->json([
                'success' => true,
                'message' => 'Bandwidth data collected successfully',
                'ports_collected' => count($results),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}