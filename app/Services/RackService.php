<?php

namespace App\Services;

use App\Models\Rack;
use App\Models\RackItem;
use App\Models\Device;
use Illuminate\Support\Facades\Cache;

class RackService
{
    /**
     * Get rack layout data for visualization
     */
    public function getRackLayout(Rack $rack): array
    {
        return Cache::remember("rack_layout_{$rack->id}", 300, function () use ($rack) {
            $items = $rack->rackItems()->with('device')->orderBy('unit_start')->get();
            
            $layout = [
                'rack' => [
                    'id' => $rack->id,
                    'name' => $rack->name,
                    'code' => $rack->rack_code,
                    'total_units' => $rack->total_units,
                    'location' => $rack->location->full_path ?? 'N/A',
                ],
                'units' => [],
            ];

            // Initialize all units as empty
            for ($i = 1; $i <= $rack->total_units; $i++) {
                $layout['units'][$i] = [
                    'position' => $i,
                    'front' => null,
                    'rear' => null,
                ];
            }

            // Fill in rack items
            foreach ($items as $item) {
                $side = $item->side; // front or rear
                for ($u = $item->unit_start; $u < $item->unit_start + $item->unit_height; $u++) {
                    if (isset($layout['units'][$u])) {
                        $layout['units'][$u][$side] = [
                            'id' => $item->id,
                            'device_id' => $item->device_id,
                            'device_name' => $item->device->name,
                            'device_type' => $item->device->type,
                            'device_status' => $item->device->status,
                            'color' => $item->color ?? $this->getDeviceColor($item->device),
                            'unit_start' => $item->unit_start,
                            'unit_height' => $item->unit_height,
                            'is_first' => $u === $item->unit_start,
                        ];
                    }
                }
            }

            return $layout;
        });
    }

    /**
     * Add device to rack
     */
    public function addDeviceToRack(Rack $rack, Device $device, int $unitStart, int $unitHeight = 1, string $side = 'front'): RackItem
    {
        // Check if space is available
        $existingItems = $rack->rackItems()
            ->where('side', $side)
            ->where(function ($query) use ($unitStart, $unitHeight) {
                $query->whereBetween('unit_start', [$unitStart, $unitStart + $unitHeight - 1])
                    ->orWhere(function ($q) use ($unitStart, $unitHeight) {
                        $q->where('unit_start', '<=', $unitStart)
                          ->whereRaw('unit_start + unit_height > ?', [$unitStart]);
                    });
            })
            ->exists();

        if ($existingItems) {
            throw new \Exception('Space is already occupied in the rack.');
        }

        return RackItem::create([
            'rack_id' => $rack->id,
            'device_id' => $device->id,
            'unit_start' => $unitStart,
            'unit_height' => $unitHeight,
            'side' => $side,
            'color' => $this->getDeviceColor($device),
        ]);
    }

    /**
     * Remove device from rack
     */
    public function removeDeviceFromRack(Rack $rack, Device $device): bool
    {
        return RackItem::where('rack_id', $rack->id)
            ->where('device_id', $device->id)
            ->delete();
    }

    /**
     * Get device color based on type and status
     */
    protected function getDeviceColor(Device $device): string
    {
        if ($device->status === 'offline') {
            return '#e74a3b'; // Red
        }
        if ($device->status === 'maintenance') {
            return '#f6c23e'; // Yellow
        }
        
        return match($device->type) {
            'switch' => '#4e73df',     // Blue
            'router' => '#1cc88a',     // Green
            'firewall' => '#e74a3b',   // Red
            'server' => '#36b9cc',     // Cyan
            'access_point' => '#f6c23e', // Yellow
            default => '#858796',       // Gray
        };
    }

    /**
     * Get all racks for a location
     */
    public function getLocationRacks($locationId): array
    {
        return Rack::where('location_id', $locationId)
            ->with(['rackItems.device'])
            ->get()
            ->map(function ($rack) {
                return [
                    'id' => $rack->id,
                    'name' => $rack->name,
                    'code' => $rack->rack_code,
                    'total_units' => $rack->total_units,
                    'device_count' => $rack->rackItems->pluck('device_id')->unique()->count(),
                    'utilization' => $rack->total_units > 0 
                        ? round(($rack->rackItems->sum('unit_height') / $rack->total_units) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }
}