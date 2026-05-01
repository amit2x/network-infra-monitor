<?php

namespace App\Services;

use App\Models\Device;
use App\Models\NetworkTopology;
use App\Models\Port;
use Illuminate\Support\Facades\Cache;

class TopologyService
{
    protected $snmpService;

    public function __construct(SNMPService $snmpService)
    {
        $this->snmpService = $snmpService;
    }

    /**
     * Discover network topology via CDP/LLDP
     */
    public function discoverTopology(Device $device): array
    {
        $connections = [];
        
        if (!$device->snmp_enabled) {
            return $connections;
        }

        try {
            // CDP (Cisco Discovery Protocol) OIDs
            $cdpCache = $this->discoverCDPNeighbors($device);
            
            // LLDP (Link Layer Discovery Protocol) OIDs
            $lldpCache = $this->discoverLLDPNeighbors($device);
            
            $connections = array_merge($cdpCache, $lldpCache);
            
            // Save discovered connections
            $this->saveConnections($device, $connections);
            
        } catch (\Exception $e) {
            \Log::error("Topology discovery failed for {$device->name}: " . $e->getMessage());
        }

        return $connections;
    }

    /**
     * Discover CDP neighbors
     */
    protected function discoverCDPNeighbors(Device $device): array
    {
        $neighbors = [];
        
        try {
            // CDP OIDs
            $cdpInterface = $this->snmpService->walkOID($device->ip_address, '1.3.6.1.4.1.9.9.23.1.1.1.1', $device->snmp_community);
            $cdpDeviceName = $this->snmpService->walkOID($device->ip_address, '1.3.6.1.4.1.9.9.23.1.1.1.6', $device->snmp_community);
            $cdpDevicePort = $this->snmpService->walkOID($device->ip_address, '1.3.6.1.4.1.9.9.23.1.1.1.7', $device->snmp_community);
            $cdpDeviceIP = $this->snmpService->walkOID($device->ip_address, '1.3.6.1.4.1.9.9.23.1.1.1.4', $device->snmp_community);

            foreach ($cdpInterface as $key => $localInterface) {
                if (!empty($cdpDeviceName[$key]) && !empty($cdpDeviceIP[$key])) {
                    $neighborName = str_replace('"', '', $cdpDeviceName[$key]);
                    $neighborIP = str_replace('"', '', $cdpDeviceIP[$key]);
                    
                    $neighbors[] = [
                        'protocol' => 'CDP',
                        'local_interface' => str_replace('"', '', $localInterface),
                        'remote_device' => $neighborName,
                        'remote_interface' => str_replace('"', '', $cdpDevicePort[$key] ?? ''),
                        'remote_ip' => $neighborIP,
                    ];
                }
            }
        } catch (\Exception $e) {
            // CDP not supported or not enabled
        }

        return $neighbors;
    }

    /**
     * Discover LLDP neighbors
     */
    protected function discoverLLDPNeighbors(Device $device): array
    {
        $neighbors = [];
        
        try {
            // LLDP OIDs (IEEE 802.1AB)
            $lldpLocalPort = $this->snmpService->walkOID($device->ip_address, '1.0.8802.1.1.2.1.4.1.1.5', $device->snmp_community);
            $lldpRemoteName = $this->snmpService->walkOID($device->ip_address, '1.0.8802.1.1.2.1.4.1.1.9', $device->snmp_community);
            $lldpRemotePort = $this->snmpService->walkOID($device->ip_address, '1.0.8802.1.1.2.1.4.1.1.7', $device->snmp_community);

            foreach ($lldpLocalPort as $key => $localInterface) {
                if (!empty($lldpRemoteName[$key])) {
                    $neighbors[] = [
                        'protocol' => 'LLDP',
                        'local_interface' => str_replace('"', '', $localInterface),
                        'remote_device' => str_replace('"', '', $lldpRemoteName[$key]),
                        'remote_interface' => str_replace('"', '', $lldpRemotePort[$key] ?? ''),
                        'remote_ip' => null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // LLDP not supported
        }

        return $neighbors;
    }

    /**
     * Save discovered connections to database
     */
    protected function saveConnections(Device $device, array $connections): void
    {
        // Clear existing connections for this device
        NetworkTopology::where('device_id', $device->id)->delete();

        foreach ($connections as $connection) {
            // Try to find neighbor device in our database
            $neighborDevice = null;
            if (!empty($connection['remote_ip'])) {
                $neighborDevice = Device::where('ip_address', $connection['remote_ip'])->first();
            }
            if (!$neighborDevice && !empty($connection['remote_device'])) {
                $neighborDevice = Device::where('name', 'like', "%{$connection['remote_device']}%")->first();
            }

            NetworkTopology::create([
                'device_id' => $device->id,
                'neighbor_device_id' => $neighborDevice?->id,
                'local_interface' => $this->mapInterfaceToPort($connection['local_interface']),
                'remote_interface' => $connection['remote_interface'],
                'connection_type' => $connection['protocol'] === 'CDP' ? 'cdp' : 'lldp',
                'status' => 'active',
                'metadata' => $connection,
            ]);
        }
    }

    /**
     * Map SNMP interface name to port number
     */
    protected function mapInterfaceToPort(string $interface): string
    {
        // Convert common interface names to port numbers
        if (preg_match('/\d+/', $interface, $matches)) {
            return 'Port ' . $matches[0];
        }
        return $interface;
    }

    /**
     * Get network topology map data
     */
    public function getTopologyMap(): array
    {
        return Cache::remember('network_topology_map', 300, function () {
            $devices = Device::where('monitoring_enabled', true)
                ->whereIn('type', ['switch', 'router'])
                ->get();
            
            $nodes = [];
            $edges = [];
            
            // Create nodes
            foreach ($devices as $device) {
                $nodes[] = [
                    'id' => $device->id,
                    'label' => $device->name,
                    'type' => $device->type,
                    'ip' => $device->ip_address,
                    'status' => $device->status,
                    'shape' => $device->type === 'router' ? 'diamond' : 'box',
                    'color' => $device->status === 'online' ? '#1cc88a' : ($device->status === 'offline' ? '#e74a3b' : '#f6c23e'),
                ];
            }
            
            // Create edges from topology data
            $connections = NetworkTopology::with(['device', 'neighbor'])->get();
            
            foreach ($connections as $conn) {
                if ($conn->neighbor) {
                    $edges[] = [
                        'from' => $conn->device_id,
                        'to' => $conn->neighbor_device_id,
                        'label' => $conn->local_interface . ' ↔ ' . $conn->remote_interface,
                        'type' => $conn->connection_type,
                        'color' => '#858796',
                    ];
                }
            }
            
            return ['nodes' => $nodes, 'edges' => $edges];
        });
    }
}