<?php

namespace App\Http\Controllers;

use App\Http\Requests\Port\UpdatePortRequest;
use App\Models\Port;
use App\Models\Device;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Device $device)
    {
        $ports = $device->ports()->orderBy('port_number')->get();
        return view('ports.index', compact('device', 'ports'));
    }

    public function show(Device $device, Port $port)
    {
        if ($port->device_id !== $device->id) {
            abort(404);
        }

        return view('ports.show', compact('device', 'port'));
    }

    public function edit(Device $device, Port $port)
    {
        if ($port->device_id !== $device->id) {
            abort(404);
        }

        return view('ports.edit', compact('device', 'port'));
    }

    public function update(UpdatePortRequest $request, Device $device, Port $port)
    {
        if ($port->device_id !== $device->id) {
            abort(404);
        }

        try {
            $port->update($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Port updated successfully.',
                    'port' => $port->fresh()
                ]);
            }

            return redirect()
                ->route('devices.ports.show', [$device->id, $port->id])
                ->with('success', 'Port updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update port: ' . $e->getMessage());
        }
    }

    public function bulkUpdate(Request $request, Device $device)
    {
        $request->validate([
            'ports' => 'required|array',
            'ports.*.id' => 'required|exists:ports,id,device_id,' . $device->id,
            'ports.*.status' => 'required|in:active,free,down,disabled',
            'ports.*.service_name' => 'nullable|string|max:255',
            'ports.*.connected_device' => 'nullable|string|max:255',
            'ports.*.vlan_id' => 'nullable|integer|between:1,4096',
        ]);

        try {
            foreach ($request->ports as $portData) {
                Port::where('id', $portData['id'])->update([
                    'status' => $portData['status'],
                    'service_name' => $portData['service_name'] ?? null,
                    'connected_device' => $portData['connected_device'] ?? null,
                    'vlan_id' => $portData['vlan_id'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ports updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
