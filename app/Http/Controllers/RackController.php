<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\Device;
use App\Models\Location;
use App\Services\RackService;
use Illuminate\Http\Request;

class RackController extends Controller 
{
    protected $rackService;

    public function __construct(RackService $rackService)
    {
        $this->rackService = $rackService;
        $this->middleware(['auth', 'permission:view devices']);

    }

    

    /**
     * Display list of racks
     */
    public function index(Request $request)
    {
        $query = Rack::with(['location', 'rackItems.device']);
        
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rack_code', 'like', "%{$search}%");
            });
        }
        
        $racks = $query->orderBy('name')->paginate(12);
        $locations = Location::where('type', 'it_room')->get();
        
        return view('racks.index', compact('racks', 'locations'));
    }

    /**
     * Show rack visualization
     */
    public function show(Rack $rack)
    {
        $rack->load(['location.parent', 'rackItems.device']);
        
        $layout = $this->rackService->getRackLayout($rack);
        $availableDevices = Device::whereDoesntHave('rackItems', function($query) use ($rack) {
            $query->where('rack_id', $rack->id);
        })->get();
        
        $locationRacks = Rack::where('location_id', $rack->location_id)
            ->where('id', '!=', $rack->id)
            ->get();
        
        return view('racks.show', compact('rack', 'layout', 'availableDevices', 'locationRacks'));
    }

    /**
     * Show form for creating a new rack
     */
    public function create()
    {
        $locations = Location::where('type', 'it_room')
            ->with('parent')
            ->get()
            ->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->full_path ?? $location->name,
                ];
            });
        
        return view('racks.create', compact('locations'));
    }

    /**
     * Store a newly created rack
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'total_units' => 'required|integer|min:4|max:52',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $validated['rack_code'] = 'RACK-' . strtoupper(uniqid());
            
            $rack = Rack::create($validated);
            
            return redirect()
                ->route('racks.show', $rack->id)
                ->with('success', 'Rack created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create rack: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing a rack
     */
    public function edit(Rack $rack)
    {
        $locations = Location::where('type', 'it_room')
            ->with('parent')
            ->get()
            ->map(function($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->full_path ?? $location->name,
                ];
            });
        
        return view('racks.edit', compact('rack', 'locations'));
    }

    /**
     * Update rack
     */
    public function update(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'total_units' => 'required|integer|min:4|max:52',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            // Don't allow reducing total_units if devices are mounted above new limit
            if ($validated['total_units'] < $rack->total_units) {
                $maxUsedUnit = $rack->rackItems()->max('unit_start') + 
                               $rack->rackItems()->max('unit_height') - 1;
                
                if ($maxUsedUnit > $validated['total_units']) {
                    return back()->with('error', 
                        'Cannot reduce rack size. Devices are mounted at position ' . $maxUsedUnit . '.'
                    );
                }
            }
            
            $rack->update($validated);
            
            return redirect()
                ->route('racks.show', $rack->id)
                ->with('success', 'Rack updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update rack: ' . $e->getMessage());
        }
    }

    /**
     * Delete rack
     */
    public function destroy(Rack $rack)
    {
        try {
            if ($rack->rackItems()->count() > 0) {
                return back()->with('error', 'Cannot delete rack with mounted devices. Remove all devices first.');
            }
            
            $rackName = $rack->name;
            $rack->delete();
            
            return redirect()
                ->route('racks.index')
                ->with('success', "Rack '{$rackName}' deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete rack: ' . $e->getMessage());
        }
    }

    /**
     * Add device to rack
     */
    public function addDevice(Request $request, Rack $rack, Device $device)
    {
        $validated = $request->validate([
            'unit_start' => 'required|integer|min:1|max:' . $rack->total_units,
            'unit_height' => 'required|integer|min:1|max:' . $rack->total_units,
            'side' => 'required|in:front,rear',
        ]);

        try {
            $this->rackService->addDeviceToRack(
                $rack,
                $device,
                $validated['unit_start'],
                $validated['unit_height'],
                $validated['side']
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Device added to rack successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove device from rack
     */
    public function removeDevice(Rack $rack, Device $device)
    {
        try {
            $this->rackService->removeDeviceFromRack($rack, $device);
            
            return response()->json([
                'success' => true,
                'message' => 'Device removed from rack.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get rack layout data
     */
    public function layout(Rack $rack)
    {
        try {
            $layout = $this->rackService->getRackLayout($rack);
            
            return response()->json([
                'success' => true,
                'data' => $layout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}