<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LocationService
{
    public function createLocation(array $data): Location
    {
        if (isset($data['parent_id'])) {
            $parent = Location::findOrFail($data['parent_id']);
            $data['level'] = $parent->level + 1;
        } else {
            $data['level'] = 0;
        }

        $data['code'] = $this->generateLocationCode($data['type']);

        return Location::create($data);
    }

    public function updateLocation(Location $location, array $data): Location
    {
        $location->update($data);
        return $location->fresh();
    }

    public function deleteLocation(Location $location): bool
    {
        if ($location->devices()->exists()) {
            throw new \Exception('Cannot delete location with associated devices');
        }

        if ($location->children()->exists()) {
            throw new \Exception('Cannot delete location with child locations');
        }

        return $location->delete();
    }

    public function generateLocationCode(string $type): string
    {
        $prefix = 'LOC-' . strtoupper(substr($type, 0, 3));
        $count = Location::where('code', 'like', $prefix . '%')->count();
        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getLocationTree(): array
    {
        $locations = Location::whereNull('parent_id')
            ->with('children.children')
            ->get();

        return $this->buildTreeArray($locations);
    }

    private function buildTreeArray($locations): array
    {
        $tree = [];
        foreach ($locations as $location) {
            $node = [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'type' => $location->type,
                'device_count' => $location->devices()->count()
            ];

            if ($location->children->count() > 0) {
                $node['children'] = $this->buildTreeArray($location->children);
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
