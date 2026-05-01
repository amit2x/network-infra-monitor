<?php

namespace App\Console\Commands;

use App\Services\SNMPService;
use Illuminate\Console\Command;

class SNMPDiscoverCommand extends Command
{
    protected $signature = 'snmp:discover 
                            {network : Network range to scan (e.g., 192.168.1.0/24)}
                            {--community=public : SNMP community string}
                            {--timeout=2 : SNMP timeout in seconds}
                            {--add-devices : Automatically add discovered devices to database}';

    protected $description = 'Discover SNMP-enabled devices on a network';

    public function handle(SNMPService $snmpService): int
    {
        $network = $this->argument('network');
        $community = $this->option('community');
        $timeout = (int) $this->option('timeout');
        $addDevices = $this->option('add-devices');

        $this->info("🔍 Scanning network {$network} for SNMP devices...");
        $this->newLine();

        try {
            $devices = $snmpService->discoverDevices($network, $community, $timeout);

            if (empty($devices)) {
                $this->warn('No SNMP devices found on this network.');
                return Command::SUCCESS;
            }

            $this->info("Found " . count($devices) . " device(s):");
            $this->newLine();

            $this->table(
                ['IP Address', 'Device Name', 'Description'],
                collect($devices)->map(function ($device) {
                    return [
                        $device['ip'],
                        $device['name'] ?? 'Unknown',
                        \Illuminate\Support\Str::limit($device['description'] ?? 'N/A', 50),
                    ];
                })->toArray()
            );

            if ($addDevices) {
                $added = 0;
                foreach ($devices as $device) {
                    $exists = \App\Models\Device::where('ip_address', $device['ip'])->exists();
                    
                    if (!$exists) {
                        \App\Models\Device::create([
                            'name' => $device['name'] ?? 'Discovered Device',
                            'type' => 'other',
                            'vendor' => 'Unknown',
                            'model' => 'Unknown',
                            'serial_number' => 'DISCOVERED-' . \Illuminate\Support\Str::random(8),
                            'ip_address' => $device['ip'],
                            'snmp_enabled' => true,
                            'snmp_community' => $community,
                            'monitoring_enabled' => true,
                            'device_code' => 'DISC' . str_pad(\App\Models\Device::count() + 1, 6, '0', STR_PAD_LEFT),
                        ]);
                        $added++;
                    }
                }

                $this->newLine();
                $this->info("✅ Added {$added} new device(s) to the database.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Discovery failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}