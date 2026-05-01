<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\SNMPService;
use Illuminate\Http\Request;

class MIBBrowserController extends Controller
{
    protected $snmpService;

    public function __construct(SNMPService $snmpService)
    {
        $this->snmpService = $snmpService;
        $this->middleware(['auth', 'permission:run monitoring']);

    }

    

    /**
     * Display MIB browser
     */
    public function index()
    {
        $devices = Device::where('snmp_enabled', true)->get();
        
        // Common MIB OIDs for quick access
        $commonOIDs = [
            ['oid' => '1.3.6.1.2.1.1.1.0', 'name' => 'System Description'],
            ['oid' => '1.3.6.1.2.1.1.3.0', 'name' => 'System Uptime'],
            ['oid' => '1.3.6.1.2.1.1.5.0', 'name' => 'System Name'],
            ['oid' => '1.3.6.1.2.1.1.6.0', 'name' => 'System Location'],
            ['oid' => '1.3.6.1.2.1.2.1.0', 'name' => 'Interface Count'],
            ['oid' => '1.3.6.1.4.1.9.9.109.1.1.1.1.6.1', 'name' => 'CPU 5sec (Cisco)'],
            ['oid' => '1.3.6.1.4.1.9.9.48.1.1.1.5.1', 'name' => 'Total Memory (Cisco)'],
        ];

        return view('mib-browser.index', compact('devices', 'commonOIDs'));
    }

    /**
     * Walk OID tree
     */
    public function walk(Device $device, Request $request)
    {
        $request->validate([
            'oid' => 'required|string|regex:/^[\d.]+$/',
        ]);

        try {
            $result = $this->snmpService->walkOID(
                $device->ip_address,
                $request->oid,
                $device->snmp_community,
                $device->snmp_timeout
            );

            return response()->json([
                'success' => true,
                'data' => $result,
                'count' => count($result),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single OID value
     */
    public function get(Device $device, Request $request)
    {
        $request->validate([
            'oid' => 'required|string|regex:/^[\d.]+$/',
        ]);

        try {
            $result = $this->snmpService->get(
                $device->ip_address,
                $request->oid,
                $device->snmp_community,
                $device->snmp_timeout
            );

            return response()->json([
                'success' => $result !== false,
                'data' => $result !== false ? str_replace(['STRING: ', 'INTEGER: '], '', $result) : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}