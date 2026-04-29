<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use Carbon\Carbon;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Create Airports
        $airport1 = Location::create([
            'name' => 'Indira Gandhi International Airport',
            'code' => 'LOC-AIR-0001',
            'type' => 'airport',
            'level' => 0,
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110037',
            'latitude' => 28.5562,
            'longitude' => 77.1000,
            'description' => 'Main international airport serving Delhi NCR',
            'is_active' => true
        ]);

        $airport2 = Location::create([
            'name' => 'Chhatrapati Shivaji International Airport',
            'code' => 'LOC-AIR-0002',
            'type' => 'airport',
            'level' => 0,
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'pincode' => '400099',
            'latitude' => 19.0896,
            'longitude' => 72.8656,
            'description' => 'Main international airport serving Mumbai',
            'is_active' => true
        ]);

        // Create Terminals for Airport 1
        $terminal1 = Location::create([
            'name' => 'Terminal 3 - IGI',
            'code' => 'LOC-TER-0001',
            'type' => 'terminal',
            'parent_id' => $airport1->id,
            'level' => 1,
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110037',
            'description' => 'International Terminal',
            'is_active' => true
        ]);

        $terminal2 = Location::create([
            'name' => 'Terminal 2 - IGI',
            'code' => 'LOC-TER-0002',
            'type' => 'terminal',
            'parent_id' => $airport1->id,
            'level' => 1,
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110037',
            'description' => 'Domestic Terminal',
            'is_active' => true
        ]);

        // Create Terminals for Airport 2
        $terminal3 = Location::create([
            'name' => 'Terminal 2 - CSIA',
            'code' => 'LOC-TER-0003',
            'type' => 'terminal',
            'parent_id' => $airport2->id,
            'level' => 1,
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'pincode' => '400099',
            'description' => 'Main Terminal CSIA',
            'is_active' => true
        ]);

        // Create IT Rooms for Terminal 3
        $itRooms = [];
        for ($i = 1; $i <= 3; $i++) {
            $itRooms[] = Location::create([
                'name' => "IT Room $i - T3",
                'code' => "LOC-ITR-000{$i}",
                'type' => 'it_room',
                'parent_id' => $terminal1->id,
                'level' => 2,
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'pincode' => '110037',
                'description' => "IT Room $i in Terminal 3",
                'is_active' => true
            ]);
        }

        // Create IT Rooms for Terminal 2
        for ($i = 1; $i <= 2; $i++) {
            $index = $i + 3;
            $itRooms[] = Location::create([
                'name' => "IT Room $i - T2",
                'code' => "LOC-ITR-000{$index}",
                'type' => 'it_room',
                'parent_id' => $terminal2->id,
                'level' => 2,
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'pincode' => '110037',
                'description' => "IT Room $i in Terminal 2",
                'is_active' => true
            ]);
        }

        // Create IT Rooms for Mumbai Terminal
        for ($i = 1; $i <= 2; $i++) {
            $index = $i + 5;
            $itRooms[] = Location::create([
                'name' => "IT Room $i - CSIA T2",
                'code' => "LOC-ITR-000{$index}",
                'type' => 'it_room',
                'parent_id' => $terminal3->id,
                'level' => 2,
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'pincode' => '400099',
                'description' => "IT Room $i in CSIA Terminal 2",
                'is_active' => true
            ]);
        }

        // Create Racks for each IT Room
        // Use alphabet cycling for rack prefixes (A-Z, then AA, AB, etc.)
        $rackPrefixes = range('A', 'Z');
        $rackNumber = 1;

        foreach ($itRooms as $room) {
            // Create 2-3 racks per IT room
            $racksPerRoom = rand(2, 3);
            for ($j = 1; $j <= $racksPerRoom; $j++) {
                // Cycle through alphabet (A, B, C, ..., Z, A, B, ...)
                $prefixIndex = ($rackNumber - 1) % count($rackPrefixes);
                $prefix = $rackPrefixes[$prefixIndex];

                Location::create([
                    'name' => "Rack {$prefix}-" . str_pad($j, 2, '0', STR_PAD_LEFT),
                    'code' => "LOC-RAC-" . str_pad($rackNumber, 4, '0', STR_PAD_LEFT),
                    'type' => 'rack',
                    'parent_id' => $room->id,
                    'level' => 3,
                    'city' => $room->city,
                    'state' => $room->state,
                    'country' => $room->country,
                    'pincode' => $room->pincode,
                    'description' => "Network rack in {$room->name}",
                    'is_active' => true
                ]);
                $rackNumber++;
            }
        }
    }
}
