<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Port;
use App\Models\Location;
use Carbon\Carbon;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        $rackLocations = Location::where('type', 'rack')->get();

        $devices = [
            [
                'name' => 'Core-Switch-DEL-01',
                'type' => 'switch',
                'vendor' => 'Cisco',
                'model' => 'WS-C3850-48P',
                'serial_number' => 'FOC1234ABC01',
                'ip_address' => '192.168.1.1',
                'mac_address' => '00:1B:44:11:3A:B7',
                'firmware_version' => '16.12.5',
                'status' => 'online',
                'is_critical' => true,
                'port_count' => 48
            ],
            [
                'name' => 'Distribution-Switch-DEL-01',
                'type' => 'switch',
                'vendor' => 'Cisco',
                'model' => 'WS-C2960X-24PS',
                'serial_number' => 'FOC1234ABC02',
                'ip_address' => '192.168.1.2',
                'mac_address' => '00:1B:44:11:3A:B8',
                'firmware_version' => '15.2.7',
                'status' => 'online',
                'is_critical' => false,
                'port_count' => 24
            ],
            [
                'name' => 'Edge-Router-DEL-01',
                'type' => 'router',
                'vendor' => 'Cisco',
                'model' => 'ISR4331',
                'serial_number' => 'FOC1234ABC03',
                'ip_address' => '192.168.1.254',
                'mac_address' => '00:1B:44:11:3A:B9',
                'firmware_version' => '16.9.5',
                'status' => 'online',
                'is_critical' => true,
                'port_count' => 0
            ],
            [
                'name' => 'Firewall-DEL-01',
                'type' => 'firewall',
                'vendor' => 'Fortinet',
                'model' => 'FortiGate-100F',
                'serial_number' => 'FGT100F1234567',
                'ip_address' => '192.168.1.253',
                'mac_address' => '00:1B:44:11:3A:C0',
                'firmware_version' => '7.0.12',
                'status' => 'online',
                'is_critical' => true,
                'port_count' => 0
            ],
            [
                'name' => 'Access-Switch-MUM-01',
                'type' => 'switch',
                'vendor' => 'HP',
                'model' => 'Aruba 2930F',
                'serial_number' => 'HPS1234ABC01',
                'ip_address' => '10.10.1.1',
                'mac_address' => '00:1B:44:11:3A:C1',
                'firmware_version' => '16.10.0',
                'status' => 'online',
                'is_critical' => false,
                'port_count' => 24
            ],
            [
                'name' => 'Server-DB-01',
                'type' => 'server',
                'vendor' => 'Dell',
                'model' => 'PowerEdge R740',
                'serial_number' => 'DEL1234ABC01',
                'ip_address' => '192.168.1.100',
                'mac_address' => '00:1B:44:11:3A:C2',
                'firmware_version' => null,
                'status' => 'online',
                'is_critical' => true,
                'port_count' => 0
            ],
        ];

        foreach ($devices as $index => $deviceData) {
            $rackLocation = $rackLocations[$index % count($rackLocations)];
            $portCount = $deviceData['port_count'] ?? 0;
            unset($deviceData['port_count']);

            $device = Device::create(array_merge($deviceData, [
                'device_code' => 'DEV' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'location_id' => $rackLocation->id,
                'procurement_date' => Carbon::now()->subMonths(rand(6, 24)),
                'installation_date' => Carbon::now()->subMonths(rand(1, 6)),
                'warranty_expiry' => Carbon::now()->addMonths(rand(6, 36)),
                'amc_expiry' => Carbon::now()->addMonths(rand(1, 12)),
                'eol_date' => Carbon::now()->addYears(rand(3, 7)),
                'monitoring_enabled' => true
            ]));

            // Create ports for switches
            if ($device->type === 'switch' && $portCount > 0) {
                $ports = [];
                for ($i = 1; $i <= $portCount; $i++) {
                    $status = ['active', 'free', 'free', 'free'][rand(0, 3)];
                    $ports[] = [
                        'device_id' => $device->id,
                        'port_number' => $i,
                        'type' => $i <= 24 ? 'copper' : 'sfp',
                        'status' => $status,
                        'service_name' => $status === 'active' ? $this->getRandomService() : null,
                        'connected_device' => $status === 'active' ? 'Device-' . str_pad($i, 3, '0', STR_PAD_LEFT) : null,
                        'vlan_id' => rand(1, 100),
                        'speed_mbps' => $i <= 24 ? 1000 : 10000,
                        'description' => $status === 'active' ? 'Connected to ' . $this->getRandomService() : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                Port::insert($ports);
            }
        }
    }

    private function getRandomService()
    {
        $services = ['CCTV', 'WiFi', 'VoIP', 'Server', 'Access Point', 'Camera', 'IoT Device', 'Printer'];
        return $services[array_rand($services)];
    }
}
