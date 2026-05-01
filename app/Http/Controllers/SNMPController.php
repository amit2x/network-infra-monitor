<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SnmpData;
use App\Services\SNMPService;
use App\Services\SNMPMonitoringService;
use Illuminate\Http\Request;

class SNMPController extends Controller 
{
    protected $snmpService;
    protected $snmpMonitoring;

    public function __construct(SNMPService $snmpService, SNMPMonitoringService $snmpMonitoring)
    {
        $this->snmpService = $snmpService;
        $this->snmpMonitoring = $snmpMonitoring;
        // $this->middleware(['auth', 'permission:run monitoring']);
        $this->middleware(['auth']);
    }



    /**
     * Display SNMP dashboard.
     */
    public function dashboard()
    {
        $snmpDevices = Device::where('snmp_enabled', true)
            ->with(['location', 'latestSnmpData'])
            ->get();

        $stats = [
            'total_snmp_devices' => $snmpDevices->count(),
            'online_snmp_devices' => $snmpDevices->where('status', 'online')->count(),
            'data_points_today' => SnmpData::whereDate('collected_at', today())->count(),
        ];

        return view('snmp.dashboard', compact('snmpDevices', 'stats'));
    }

    /**
     * Test SNMP connection to device.
     */
    public function testConnection(Device $device)
    {
        try {
            $isConnected = $this->snmpService->testConnection(
                $device->ip_address,
                $device->snmp_community ?? 'public'
            );

            if ($isConnected) {
                $systemInfo = $this->snmpService->getSystemInfo(
                    $device->ip_address,
                    $device->snmp_community ?? 'public'
                );

                return response()->json([
                    'success' => true,
                    'message' => 'SNMP connection successful',
                    'data' => $systemInfo,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'SNMP connection failed. Check community string and device reachability.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SNMP Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device performance data.
     */
    public function performance(Device $device, Request $request)
    {
        $hours = $request->get('hours', 24);
        $metrics = $this->snmpMonitoring->getDeviceMetrics($device, $hours);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Display performance view.
     */
    public function performanceView(Device $device)
    {
        $metrics = $this->snmpMonitoring->getDeviceMetrics($device, 24);
        return view('snmp.performance', compact('device', 'metrics'));
    }

    /**
     * Get interface statistics.
     */
    public function interfaces(Device $device)
    {
        try {
            $interfaces = $this->snmpService->getInterfaceStats(
                $device->ip_address,
                $device->snmp_community ?? 'public'
            );

            return response()->json([
                'success' => true,
                'data' => $interfaces,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display interface view.
     */
    public function interfacesView(Device $device)
    {
        return view('snmp.interfaces', compact('device'));
    }

    /**
     * Discover SNMP devices on network.
     */
    public function discover(Request $request)
    {
        $request->validate([
            'network' => 'required|string',
            'community' => 'required|string',
        ]);

        try {
            $devices = $this->snmpService->discoverDevices(
                $request->network,
                $request->community
            );

            return response()->json([
                'success' => true,
                'data' => $devices,
                'message' => count($devices) . ' device(s) found',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run SNMP monitoring for a device.
     */
    public function runMonitoring(Device $device = null)
    {
        try {
            if ($device) {
                // Monitor single device
                $result = $this->snmpMonitoring->monitorDevice($device);
                return response()->json([
                    'success' => $result['success'],
                    'data' => $result,
                ]);
            }

            // Run full cycle
            $results = $this->snmpMonitoring->runMonitoringCycle();
            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}