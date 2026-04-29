<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index(Request $request)
    {
        $query = Device::with('location');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $devices = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }

    public function show(Device $device)
    {
        $device->load(['location.parent', 'ports', 'monitoringLogs' => function($query) {
            $query->latest()->take(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $device
        ]);
    }

    public function ping(Device $device)
    {
        try {
            $result = $this->deviceService->pingDevice($device);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ports(Device $device)
    {
        $ports = $device->ports()->orderBy('port_number')->get();

        return response()->json([
            'success' => true,
            'data' => $ports
        ]);
    }
}
