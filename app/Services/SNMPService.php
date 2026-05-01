<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SNMPService
{
    protected $config;
    protected $sessions = [];
    protected $metrics = [
        'successful_queries' => 0,
        'failed_queries' => 0,
        'cached_responses' => 0,
        'timeouts' => 0,
    ];

    public function __construct()
    {
        $this->config = config('snmp');
        
        // Check if SNMP extension is loaded
        if (!extension_loaded('snmp')) {
            Log::warning('PHP SNMP extension is not loaded. SNMP monitoring will not work.');
        }
    }

    /**
     * Check if SNMP is available
     */
    public function isAvailable(): bool
    {
        return extension_loaded('snmp');
    }

    /**
     * Get device system information via SNMP
     */
    public function getSystemInfo(string $host, string $community = null, int $timeout = null): array
    {
        if (!$this->isAvailable()) {
            return ['snmp_enabled' => false, 'error' => 'SNMP extension not loaded'];
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        $cacheKey = "snmp_system_{$host}_{$community}";

        if ($this->config['cache']['enabled']) {
            return Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($host, $community, $timeout) {
                return $this->querySystemInfo($host, $community, $timeout);
            });
        }

        return $this->querySystemInfo($host, $community, $timeout);
    }

    /**
     * Query system information from device
     */
    protected function querySystemInfo(string $host, string $community, int $timeout): array
    {
        try {
            $session = $this->createSession($host, $community, $timeout);
            
            $result = [
                'description' => @snmpget($host, $community, $this->config['oids']['system']['description'], $timeout * 1000000),
                'uptime' => $this->parseUptime(@snmpget($host, $community, $this->config['oids']['system']['uptime'], $timeout * 1000000)),
                'contact' => @snmpget($host, $community, $this->config['oids']['system']['contact'], $timeout * 1000000),
                'name' => @snmpget($host, $community, $this->config['oids']['system']['name'], $timeout * 1000000),
                'location' => @snmpget($host, $community, $this->config['oids']['system']['location'], $timeout * 1000000),
                'snmp_enabled' => true,
            ];

            $this->metrics['successful_queries']++;
            return $result;
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            Log::warning("SNMP system info query failed for {$host}: " . $e->getMessage());
            
            return [
                'snmp_enabled' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get interface statistics
     */
    public function getInterfaceStats(string $host, string $community = null, int $timeout = null): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        try {
            $interfaces = [];
            
            // Get interface count
            $ifCount = (int) @snmpget($host, $community, $this->config['oids']['interfaces']['count'], $timeout * 1000000);
            
            // Limit to reasonable number of interfaces
            $maxInterfaces = min($ifCount, 100);
            
            for ($i = 1; $i <= $maxInterfaces; $i++) {
                $description = @snmpget($host, $community, "{$this->config['oids']['interfaces']['description']}.{$i}", $timeout * 1000000);
                
                // Skip if interface doesn't exist
                if ($description === false) {
                    continue;
                }
                
                $interfaces[] = [
                    'index' => $i,
                    'description' => str_replace('"', '', $description),
                    'type' => $this->getInterfaceType((int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['type']}.{$i}", $timeout * 1000000)),
                    'speed' => $this->parseSpeed((int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['speed']}.{$i}", $timeout * 1000000)),
                    'mac' => $this->formatMac(@snmpget($host, $community, "{$this->config['oids']['interfaces']['mac']}.{$i}", $timeout * 1000000)),
                    'admin_status' => $this->getInterfaceStatus((int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['admin_status']}.{$i}", $timeout * 1000000)),
                    'oper_status' => $this->getInterfaceStatus((int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['oper_status']}.{$i}", $timeout * 1000000)),
                    'in_octets' => (int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['in_octets']}.{$i}", $timeout * 1000000),
                    'out_octets' => (int) @snmpget($host, $community, "{$this->config['oids']['interfaces']['out_octets']}.{$i}", $timeout * 1000000),
                    'in_errors' => (int) @snmpget($host, $community, "1.3.6.1.2.1.2.2.1.14.{$i}", $timeout * 1000000),
                    'out_errors' => (int) @snmpget($host, $community, "1.3.6.1.2.1.2.2.1.20.{$i}", $timeout * 1000000),
                ];
            }

            $this->metrics['successful_queries']++;
            return $interfaces;
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            Log::warning("SNMP interface query failed for {$host}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CPU utilization using native PHP SNMP
     */
    public function getCPUUsage(string $host, string $vendor = 'cisco', string $community = null, int $timeout = null): array
    {
        if (!$this->isAvailable()) {
            return ['error' => 'SNMP extension not loaded'];
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        try {
            // Try standard OIDs first
            $cpuOids = $this->getCPUIDsForVendor($vendor);
            
            $result = [
                '5sec' => null,
                '1min' => null,
                '5min' => null,
            ];

            foreach ($cpuOids as $key => $oid) {
                if ($oid) {
                    $value = @snmpget($host, $community, $oid, $timeout * 1000000);
                    if ($value !== false) {
                        $result[$key] = (int) $value;
                    }
                }
            }

            $this->metrics['successful_queries']++;
            return $result;
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            Log::warning("SNMP CPU query failed for {$host}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get memory utilization
     */
    public function getMemoryUsage(string $host, string $vendor = 'cisco', string $community = null, int $timeout = null): array
    {
        if (!$this->isAvailable()) {
            return ['error' => 'SNMP extension not loaded'];
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        try {
            $memoryOids = $this->getMemoryOIDsForVendor($vendor);
            
            $total = null;
            $used = null;
            $free = null;

            if (isset($memoryOids['total'])) {
                $total = @snmpget($host, $community, $memoryOids['total'], $timeout * 1000000);
            }
            
            if (isset($memoryOids['used'])) {
                $used = @snmpget($host, $community, $memoryOids['used'], $timeout * 1000000);
            }
            
            if (isset($memoryOids['free'])) {
                $free = @snmpget($host, $community, $memoryOids['free'], $timeout * 1000000);
            }

            $total = ($total !== false) ? (int) $total : null;
            $used = ($used !== false) ? (int) $used : null;
            $free = ($free !== false) ? (int) $free : null;

            $result = [
                'total' => $total,
                'used' => $used,
                'free' => $free,
                'usage_percent' => ($total && $used) ? round(($used / $total) * 100, 2) : 0,
            ];

            $this->metrics['successful_queries']++;
            return $result;
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            Log::warning("SNMP memory query failed for {$host}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Bulk walk an SNMP OID tree
     */
    public function walkOID(string $host, string $oid, string $community = null, int $timeout = null): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        $cacheKey = "snmp_walk_{$host}_" . md5($oid);

        if ($this->config['cache']['enabled']) {
            return Cache::remember($cacheKey, $this->config['cache']['ttl'], function () use ($host, $oid, $community, $timeout) {
                return $this->performWalk($host, $oid, $community, $timeout);
            });
        }

        return $this->performWalk($host, $oid, $community, $timeout);
    }

    /**
     * Perform SNMP walk
     */
    protected function performWalk(string $host, string $oid, string $community, int $timeout): array
    {
        try {
            $result = @snmpwalk($host, $community, $oid, $timeout * 1000000);
            
            if ($result === false) {
                $this->metrics['failed_queries']++;
                return [];
            }

            $this->metrics['successful_queries']++;
            
            // Clean up results
            return array_map(function($value) {
                return str_replace(['STRING: ', 'INTEGER: ', 'Counter32: ', 'Gauge32: ', 'Timeticks: '], '', $value);
            }, $result);
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            Log::warning("SNMP walk failed for {$host} OID {$oid}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get SNMP value
     */
    public function get(string $host, string $oid, string $community = null, int $timeout = null): string|false
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        try {
            $result = @snmpget($host, $community, $oid, $timeout * 1000000);
            
            if ($result !== false) {
                $this->metrics['successful_queries']++;
            } else {
                $this->metrics['failed_queries']++;
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->metrics['failed_queries']++;
            return false;
        }
    }

    /**
     * Set SNMP value
     */
    public function set(string $host, string $oid, string $type, string $value, string $community = null, int $timeout = null): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $community = $community ?? $this->config['defaults']['community'];
        $timeout = $timeout ?? $this->config['defaults']['timeout'];

        try {
            $result = @snmpset($host, $community, $oid, $type, $value, $timeout * 1000000);
            return $result !== false;
        } catch (\Exception $e) {
            Log::warning("SNMP set failed for {$host} OID {$oid}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get CPU OIDs based on vendor
     */
    protected function getCPUIDsForVendor(string $vendor): array
    {
        return match(strtolower($vendor)) {
            'cisco' => [
                '5sec' => '1.3.6.1.4.1.9.9.109.1.1.1.1.6.1',
                '1min' => '1.3.6.1.4.1.9.9.109.1.1.1.1.7.1',
                '5min' => '1.3.6.1.4.1.9.9.109.1.1.1.1.8.1',
            ],
            'juniper' => [
                '5sec' => '1.3.6.1.4.1.2636.3.1.13.1.8.1',
                '1min' => '1.3.6.1.4.1.2636.3.1.13.1.8.2',
                '5min' => '1.3.6.1.4.1.2636.3.1.13.1.8.3',
            ],
            'hp', 'aruba' => [
                '5sec' => '1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0',
                '1min' => null,
                '5min' => null,
            ],
            'fortinet' => [
                '5sec' => '1.3.6.1.4.1.12356.101.4.1.3.0',
                '1min' => null,
                '5min' => null,
            ],
            default => [
                '5sec' => $this->config['oids']['cpu']['5sec'] ?? null,
                '1min' => $this->config['oids']['cpu']['1min'] ?? null,
                '5min' => $this->config['oids']['cpu']['5min'] ?? null,
            ],
        };
    }

    /**
     * Get Memory OIDs based on vendor
     */
    protected function getMemoryOIDsForVendor(string $vendor): array
    {
        return match(strtolower($vendor)) {
            'cisco' => [
                'total' => '1.3.6.1.4.1.9.9.48.1.1.1.5.1',
                'used' => '1.3.6.1.4.1.9.9.48.1.1.1.6.1',
                'free' => '1.3.6.1.4.1.9.9.48.1.1.1.7.1',
            ],
            'juniper' => [
                'total' => '1.3.6.1.4.1.2636.3.1.13.1.11.1',
                'used' => '1.3.6.1.4.1.2636.3.1.13.1.11.2',
                'free' => null,
            ],
            'hp', 'aruba' => [
                'total' => '1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.5.1',
                'used' => '1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.6.1',
                'free' => '1.3.6.1.4.1.11.2.14.11.5.1.1.2.2.1.1.7.1',
            ],
            default => [
                'total' => $this->config['oids']['memory']['total'] ?? null,
                'used' => $this->config['oids']['memory']['used'] ?? null,
                'free' => $this->config['oids']['memory']['free'] ?? null,
            ],
        };
    }

    /**
     * Create SNMP session (for persistent connections)
     */
    protected function createSession(string $host, string $community, int $timeout)
    {
        // PHP's SNMP functions don't require session management
        // They use stateless calls, which is simpler but less efficient for bulk queries
        return true;
    }

    /**
     * Parse SNMP uptime ticks to human readable
     */
    protected function parseUptime($uptime): string
    {
        if (!$uptime || $uptime === false) {
            return 'Unknown';
        }

        $uptime = str_replace(['Timeticks: ', '(', ')'], '', (string)$uptime);
        $ticks = (int) $uptime;
        $seconds = $ticks / 100;
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    /**
     * Get interface type name
     */
    protected function getInterfaceType(int $type): string
    {
        return match($type) {
            1 => 'Other',
            6 => 'Ethernet',
            7 => 'Token Ring',
            23 => 'PPP',
            24 => 'Loopback',
            117 => 'Gigabit Ethernet',
            131 => 'Tunnel',
            135 => 'L2 VLAN',
            161 => 'IEEE 802.11',
            default => "Type {$type}",
        };
    }

    /**
     * Parse interface speed to human readable
     */
    protected function parseSpeed(int $speed): string
    {
        if ($speed <= 0) {
            return 'N/A';
        }
        
        if ($speed >= 10000000000) {
            return round($speed / 10000000000, 1) . ' Gbps';
        } elseif ($speed >= 1000000000) {
            return round($speed / 1000000000, 1) . ' Gbps';
        } elseif ($speed >= 1000000) {
            return round($speed / 1000000) . ' Mbps';
        } elseif ($speed >= 1000) {
            return round($speed / 1000) . ' Kbps';
        }
        return $speed . ' bps';
    }

    /**
     * Format MAC address
     */
    protected function formatMac($mac): string
    {
        if (!$mac || $mac === false) {
            return 'N/A';
        }

        // Clean up the MAC address
        $hex = str_replace([' ', ':', '-', '"', 'STRING: '], '', strtoupper((string)$mac));
        
        // Ensure even length
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        
        // Format as XX:XX:XX:XX:XX:XX
        $parts = str_split($hex, 2);
        return implode(':', array_slice($parts, 0, 6));
    }

    /**
     * Get interface status
     */
    protected function getInterfaceStatus(int $status): string
    {
        return match($status) {
            1 => 'up',
            2 => 'down',
            3 => 'testing',
            4 => 'unknown',
            5 => 'dormant',
            6 => 'notPresent',
            7 => 'lowerLayerDown',
            default => 'unknown',
        };
    }

    /**
     * Test SNMP connectivity
     */
    public function testConnection(string $host, string $community = 'public', int $timeout = 2): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $result = @snmpget($host, $community, '1.3.6.1.2.1.1.1.0', $timeout * 1000000);
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Discover SNMP devices on a network
     */
    public function discoverDevices(string $network, string $community = 'public', int $timeout = 1): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $devices = [];
        
        // Parse network range (e.g., 192.168.1.0/24)
        list($subnet, $mask) = explode('/', $network);
        $ipParts = explode('.', $subnet);
        
        // For /24 subnet
        if ($mask == '24') {
            $baseIP = implode('.', array_slice($ipParts, 0, 3));
            
            // Scan only common device IPs (not all 254 addresses)
            $commonEndings = [1, 2, 254, 253, 100, 101, 200, 201];
            
            foreach ($commonEndings as $ending) {
                $ip = "{$baseIP}.{$ending}";
                
                $result = @snmpget($ip, $community, '1.3.6.1.2.1.1.1.0', $timeout * 1000000);
                
                if ($result !== false) {
                    $sysName = @snmpget($ip, $community, '1.3.6.1.2.1.1.5.0', $timeout * 1000000);
                    $sysDescr = @snmpget($ip, $community, '1.3.6.1.2.1.1.1.0', $timeout * 1000000);
                    
                    $devices[] = [
                        'ip' => $ip,
                        'name' => str_replace(['STRING: ', '"'], '', (string)$sysName),
                        'description' => str_replace(['STRING: ', '"'], '', (string)$sysDescr),
                    ];
                }
            }
        }
        
        return $devices;
    }

    /**
     * Get SNMP metrics
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Close all SNMP sessions (cleanup)
     */
    public function closeSessions(): void
    {
        // PHP SNMP functions are stateless, no cleanup needed
    }

    public function __destruct()
    {
        $this->closeSessions();
    }
}