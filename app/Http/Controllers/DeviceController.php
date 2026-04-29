<?php

namespace App\Http\Controllers;

use App\Http\Requests\Device\StoreDeviceRequest;
use App\Http\Requests\Device\UpdateDeviceRequest;
use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

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

        $devices = $query->orderBy('name')->paginate(15)->withQueryString();

        // Get filter options
        $deviceTypes = Device::distinct()->pluck('type');
        $vendors = Device::distinct()->pluck('vendor');
        $locations = \App\Models\Location::where('type', 'rack')->get();

        return view('devices.index', compact('devices', 'deviceTypes', 'vendors', 'locations'));
    }

    public function create()
    {
        $locations = \App\Models\Location::where('type', 'rack')
            ->with('parent.parent')
            ->get()
            ->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->full_path
                ];
            });

        return view('devices.create', compact('locations'));
    }

    public function store(StoreDeviceRequest $request)
    {
        try {
            $device = $this->deviceService->createDevice($request->validated());

            return redirect()
                ->route('devices.show', $device->id)
                ->with('success', 'Device created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create device: ' . $e->getMessage());
        }
    }

    public function show(Device $device)
    {
        $device->load(['location.parent.parent', 'ports' => function($query) {
            $query->orderBy('port_number');
        }]);

        $monitoringLogs = $device->monitoringLogs()
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $alerts = $device->alerts()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('devices.show', compact('device', 'monitoringLogs', 'alerts'));
    }

    public function edit(Device $device)
    {
        $locations = \App\Models\Location::where('type', 'rack')
            ->with('parent.parent')
            ->get()
            ->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->full_path
                ];
            });

        return view('devices.edit', compact('device', 'locations'));
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        try {
            $this->deviceService->updateDevice($device, $request->validated());

            return redirect()
                ->route('devices.show', $device->id)
                ->with('success', 'Device updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update device: ' . $e->getMessage());
        }
    }

    public function destroy(Device $device)
    {
        try {
            $this->deviceService->deleteDevice($device);

            return redirect()
                ->route('devices.index')
                ->with('success', 'Device deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete device: ' . $e->getMessage());
        }
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
}
