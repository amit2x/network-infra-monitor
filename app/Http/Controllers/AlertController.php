<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with('device');

        // Apply filters
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('resolved') !== null) {
            $query->where('is_resolved', $request->resolved);
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('alerts.index', compact('alerts'));
    }

    public function show(Alert $alert)
    {
        $alert->load(['device.location', 'resolvedBy']);
        return view('alerts.show', compact('alert'));
    }

    public function markAsRead(Alert $alert)
    {
        $alert->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.'
        ]);
    }

    public function resolve(Alert $alert)
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully.'
        ]);
    }

    public function bulkResolve(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:alerts,id'
        ]);

        Alert::whereIn('id', $request->alert_ids)->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->alert_ids) . ' alerts resolved.'
        ]);
    }

    public function getUnreadCount()
    {
        $count = Alert::where('is_read', false)->count();

        return response()->json([
            'count' => $count
        ]);
    }
}
