<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index(Request $request)
    {
        $locations = Location::withCount('devices')
            ->when($request->filled('type'), function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('search'), function($query) use ($request) {
                return $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(15);

        $locationTypes = Location::distinct()->pluck('type');

        return view('locations.index', compact('locations', 'locationTypes'));
    }

    public function create()
    {
        $parentLocations = Location::whereIn('type', ['airport', 'terminal', 'it_room'])
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->full_path
                ];
            });

        return view('locations.create', compact('parentLocations'));
    }

    public function store(StoreLocationRequest $request)
    {
        try {
            $location = $this->locationService->createLocation($request->validated());

            return redirect()
                ->route('locations.show', $location->id)
                ->with('success', 'Location created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create location: ' . $e->getMessage());
        }
    }

    public function show(Location $location)
    {
        $location->load(['parent', 'children' => function($query) {
            $query->withCount('devices');
        }]);

        $devices = $location->devices()
            ->with('ports')
            ->paginate(10);

        return view('locations.show', compact('location', 'devices'));
    }

    public function edit(Location $location)
    {
        $parentLocations = Location::where('id', '!=', $location->id)
            ->whereIn('type', ['airport', 'terminal', 'it_room'])
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->full_path
                ];
            });

        return view('locations.edit', compact('location', 'parentLocations'));
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        try {
            $this->locationService->updateLocation($location, $request->validated());

            return redirect()
                ->route('locations.show', $location->id)
                ->with('success', 'Location updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update location: ' . $e->getMessage());
        }
    }

    public function destroy(Location $location)
    {
        try {
            $this->locationService->deleteLocation($location);

            return redirect()
                ->route('locations.index')
                ->with('success', 'Location deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tree()
    {
        $locationTree = $this->locationService->getLocationTree();

        if (request()->ajax()) {
            return response()->json($locationTree);
        }

        return view('locations.tree', compact('locationTree'));
    }

    public function devices(Location $location)
    {
        $devices = $location->devices()
            ->with('ports')
            ->withCount('ports')
            ->get()
            ->map(function($device) {
                $device->active_ports = $device->ports->where('status', 'active')->count();
                $device->utilization = $device->ports_count > 0
                    ? round(($device->active_ports / $device->ports_count) * 100, 2)
                    : 0;
                return $device;
            });

        return view('locations.devices', compact('location', 'devices'));
    }
}
