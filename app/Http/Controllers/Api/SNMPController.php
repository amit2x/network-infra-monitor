<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SnmpData;
use App\Services\SNMPService;
use App\Services\SNMPMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SNMPController extends Controller
{
    protected $snmpService;
    protected $snmpMonitoring;

    public function __construct(SNMPService $snmpService, SNMPMonitoringService $snmpMonitoring)
    {
        $this->snmpService = $snmpService;
        $this->snmpMonitoring = $snmpMonitoring;
        
        // Apply auth middleware
        $this->middleware('auth:sanctum');
        
        // Apply permission middleware for specific actions
        $this->middleware('permission:run monitoring')->only(['runMonitoring', 'testConnection', 'discoverDevices']);
        $this->middleware('permission:view reports')->only(['dashboard', 'performance', 'interfaces', 'systemInfo']);
    }

    /**
     * SNMP Dashboard Statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        try {
            $snmpDevices = Device::where('snmp_enabled', true)
                ->where('monitoring_enabled', true)
                ->count();

            $onlineSnmpDevices = Device::where('snmp_enabled', true)
                ->where('status', 'online')
                ->count();

            $dataPointsToday = SnmpData::whereDate('collected_at', today())->count();
            
            $dataPointsThisWeek = SnmpData::whereBetween('collected_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count();

            // Get average metrics across all devices
            $avgCpu = SnmpData::whereDate('collected_at', today())
                ->whereNotNull('cpu_usage')
                ->avg('cpu_usage');

            $avgMemory = SnmpData::whereDate('collected_at', today())
                ->whereNotNull('memory_usage')
                ->avg('memory_usage');

            // Get devices with high CPU usage
            $highCpuDevices = SnmpData::with('device:id,name,ip_address')
                ->where('collected_at', '>=', now()->subHour())
                ->where('cpu_usage', '>', 80)
                ->orderByDesc('cpu_usage')
                ->take(5)
                ->get()
                ->map(function ($data) {
                    return [
                        'device_id' => $data->device_id,
                        'device_name' => $data->device->name ?? 'Unknown',
                        'ip_address' => $data->device->ip_address ?? 'N/A',
                        'cpu_usage' => $data->cpu_usage,
                        'collected_at' => $data->collected_at,
                    ];
                });

            // Get recent SNMP data collection stats
            $recentCollection = SnmpData::selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN cpu_usage IS NOT NULL THEN 1 ELSE 0 END) as cpu_collected,
                    SUM(CASE WHEN memory_usage IS NOT NULL THEN 1 ELSE 0 END) as memory_collected,
                    MAX(collected_at) as last_collection
                ')
                ->where('collected_at', '>=', now()->subHour())
                ->first();

            $stats = [
                'devices' => [
                    'total_snmp_enabled' => $snmpDevices,
                    'online' => $onlineSnmpDevices,
                    'offline' => $snmpDevices - $onlineSnmpDevices,
                ],
                'metrics' => [
                    'avg_cpu_usage' => round($avgCpu ?? 0, 2),
                    'avg_memory_usage' => round($avgMemory ?? 0, 2),
                    'data_points_today' => $dataPointsToday,
                    'data_points_this_week' => $dataPointsThisWeek,
                ],
                'alerts' => [
                    'high_cpu_devices' => $highCpuDevices,
                ],
                'collection_status' => [
                    'total_collections_last_hour' => $recentCollection->total ?? 0,
                    'cpu_collected' => $recentCollection->cpu_collected ?? 0,
                    'memory_collected' => $recentCollection->memory_collected ?? 0,
                    'last_collection_at' => $recentCollection->last_collection ?? null,
                ],
                'snmp_service_status' => [
                    'extension_loaded' => $this->snmpService->isAvailable(),
                    'metrics' => $this->snmpService->getMetrics(),
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('SNMP dashboard error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load SNMP dashboard data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device performance metrics.
     * 
     * @param Device $device
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function performance(Device $device, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hours' => 'nullable|integer|min:1|max:720', // Max 30 days
            'metrics' => 'nullable|array',
            'metrics.*' => 'nullable|in:cpu,memory,interfaces,system',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $hours = $request->get('hours', 24);
            $requestedMetrics = $request->get('metrics', ['cpu', 'memory']);
            
            $response = [
                'device' => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'ip_address' => $device->ip_address,
                    'type' => $device->type,
                    'vendor' => $device->vendor,
                ],
                'snmp_config' => [
                    'enabled' => $device->snmp_enabled,
                    'community' => $device->snmp_enabled ? '***' : null, // Mask for security
                    'version' => $device->snmp_version,
                    'port' => $device->snmp_port,
                    'polling_interval' => $device->snmp_polling_interval,
                ],
                'metrics' => [],
                'timestamp' => now()->toIso8601String(),
            ];

            // Get historical data
            $snmpData = SnmpData::where('device_id', $device->id)
                ->where('collected_at', '>=', now()->subHours($hours))
                ->orderBy('collected_at')
                ->get();

            if ($snmpData->isEmpty()) {
                // Try to get live data if no historical data
                if ($device->snmp_enabled) {
                    try {
                        if (in_array('cpu', $requestedMetrics)) {
                            $cpuData = $this->snmpService->getCPUUsage(
                                $device->ip_address,
                                $device->vendor,
                                $device->snmp_community,
                                $device->snmp_timeout
                            );
                            $response['metrics']['cpu'] = [
                                'current' => $cpuData,
                                'trend' => [],
                                'avg' => $cpuData['5sec'] ?? 0,
                                'max' => $cpuData['5sec'] ?? 0,
                            ];
                        }

                        if (in_array('memory', $requestedMetrics)) {
                            $memoryData = $this->snmpService->getMemoryUsage(
                                $device->ip_address,
                                $device->vendor,
                                $device->snmp_community,
                                $device->snmp_timeout
                            );
                            $response['metrics']['memory'] = [
                                'current' => $memoryData,
                                'trend' => [],
                                'avg' => $memoryData['usage_percent'] ?? 0,
                                'max' => $memoryData['usage_percent'] ?? 0,
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::warning("Live SNMP query failed for {$device->name}: " . $e->getMessage());
                    }
                }

                $response['message'] = 'No historical data available. Live data provided where possible.';
            } else {
                // Process historical data
                if (in_array('cpu', $requestedMetrics)) {
                    $cpuTrend = $snmpData->pluck('cpu_usage', 'collected_at')
                        ->map(function ($value, $key) {
                            return [
                                'timestamp' => $key,
                                'value' => round($value ?? 0, 2),
                            ];
                        })->values();

                    $response['metrics']['cpu'] = [
                        'current' => $snmpData->last()->cpu_usage ?? 0,
                        'trend' => $cpuTrend,
                        'avg' => round($snmpData->avg('cpu_usage') ?? 0, 2),
                        'max' => round($snmpData->max('cpu_usage') ?? 0, 2),
                        'min' => round($snmpData->min('cpu_usage') ?? 0, 2),
                    ];
                }

                if (in_array('memory', $requestedMetrics)) {
                    $memoryTrend = $snmpData->pluck('memory_usage', 'collected_at')
                        ->map(function ($value, $key) {
                            return [
                                'timestamp' => $key,
                                'value' => round($value ?? 0, 2),
                            ];
                        })->values();

                    $response['metrics']['memory'] = [
                        'current' => $snmpData->last()->memory_usage ?? 0,
                        'trend' => $memoryTrend,
                        'avg' => round($snmpData->avg('memory_usage') ?? 0, 2),
                        'max' => round($snmpData->max('memory_usage') ?? 0, 2),
                        'min' => round($snmpData->min('memory_usage') ?? 0, 2),
                    ];
                }

                if (in_array('interfaces', $requestedMetrics)) {
                    $latestInterfaces = $snmpData->last()->interfaces_data ?? [];
                    $response['metrics']['interfaces'] = $latestInterfaces;
                }

                if (in_array('system', $requestedMetrics)) {
                    $latestSystem = $snmpData->last()->system_info ?? [];
                    $response['metrics']['system'] = $latestSystem;
                }

                $response['data_points'] = $snmpData->count();
            }

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error("Performance query failed for device {$device->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get performance data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device interface statistics.
     * 
     * @param Device $device
     * @return \Illuminate\Http\JsonResponse
     */
    public function interfaces(Device $device): JsonResponse
    {
        try {
            // Check cache first
            $cacheKey = "snmp_interfaces_{$device->id}";
            
            $interfaces = Cache::remember($cacheKey, 300, function () use ($device) {
                if (!$device->snmp_enabled) {
                    return [
                        'error' => 'SNMP not enabled for this device',
                        'interfaces' => [],
                    ];
                }

                return [
                    'interfaces' => $this->snmpService->getInterfaceStats(
                        $device->ip_address,
                        $device->snmp_community,
                        $device->snmp_timeout
                    ),
                ];
            });

            // Enrich interface data with port mapping if available
            $enrichedInterfaces = collect($interfaces['interfaces'])->map(function ($iface) use ($device) {
                // Try to match with configured port
                $port = $device->ports()
                    ->where('port_number', $iface['index'])
                    ->first();

                $iface['port_config'] = $port ? [
                    'service_name' => $port->service_name,
                    'connected_device' => $port->connected_device,
                    'vlan_id' => $port->vlan_id,
                    'status' => $port->status,
                ] : null;

                return $iface;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'device' => [
                        'id' => $device->id,
                        'name' => $device->name,
                        'ip_address' => $device->ip_address,
                    ],
                    'interfaces' => $enrichedInterfaces,
                    'total_count' => $enrichedInterfaces->count(),
                    'up_count' => $enrichedInterfaces->where('oper_status', 'up')->count(),
                    'down_count' => $enrichedInterfaces->where('oper_status', 'down')->count(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Interface query failed for device {$device->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get interface data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device system information via SNMP.
     * 
     * @param Device $device
     * @return \Illuminate\Http\JsonResponse
     */
    public function systemInfo(Device $device): JsonResponse
    {
        try {
            if (!$device->snmp_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'SNMP is not enabled for this device.',
                ], 422);
            }

            $systemInfo = $this->snmpService->getSystemInfo(
                $device->ip_address,
                $device->snmp_community,
                $device->snmp_timeout
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'system_info' => $systemInfo,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("System info query failed for device {$device->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system information.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SNMP connection to a device.
     * 
     * @param Device $device
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Device $device, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'community' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'timeout' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $community = $request->community ?? $device->snmp_community ?? 'public';
            $timeout = $request->timeout ?? $device->snmp_timeout ?? 2;

            $startTime = microtime(true);
            $isConnected = $this->snmpService->testConnection(
                $device->ip_address,
                $community,
                $timeout
            );
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($isConnected) {
                // Get system info for verification
                $systemInfo = $this->snmpService->getSystemInfo(
                    $device->ip_address,
                    $community,
                    $timeout
                );

                return response()->json([
                    'success' => true,
                    'message' => 'SNMP connection successful',
                    'data' => [
                        'response_time_ms' => $responseTime,
                        'system_info' => $systemInfo,
                        'connection_params' => [
                            'host' => $device->ip_address,
                            'community' => '***', // Mask for security
                            'version' => $device->snmp_version ?? '2c',
                            'port' => $device->snmp_port ?? 161,
                        ],
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'SNMP connection failed. Verify community string, SNMP service, and network connectivity.',
                'data' => [
                    'response_time_ms' => $responseTime,
                    'host' => $device->ip_address,
                ],
            ], 422);
        } catch (\Exception $e) {
            Log::error("SNMP test failed for device {$device->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'SNMP test error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Discover SNMP devices on a network.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function discoverDevices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'network' => 'required|string|regex:/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}$/',
            'community' => 'required|string|max:255',
            'timeout' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $network = $request->network;
            $community = $request->community;
            $timeout = $request->timeout ?? 1;

            Log::info("Starting SNMP discovery on network: {$network}");

            $discoveredDevices = $this->snmpService->discoverDevices(
                $network,
                $community,
                $timeout
            );

            // Check which discovered devices already exist in our system
            $knownIPs = Device::whereIn('ip_address', collect($discoveredDevices)->pluck('ip'))
                ->pluck('ip_address')
                ->toArray();

            $devices = collect($discoveredDevices)->map(function ($device) use ($knownIPs) {
                $device['already_exists'] = in_array($device['ip'], $knownIPs);
                return $device;
            });

            Log::info("SNMP discovery completed. Found {$devices->count()} device(s).");

            return response()->json([
                'success' => true,
                'message' => "Found {$devices->count()} device(s) on network {$network}",
                'data' => [
                    'network' => $network,
                    'community' => '***', // Mask for security
                    'devices' => $devices,
                    'new_devices' => $devices->where('already_exists', false)->values(),
                    'existing_devices' => $devices->where('already_exists', true)->values(),
                    'total_found' => $devices->count(),
                    'new_count' => $devices->where('already_exists', false)->count(),
                    'existing_count' => $devices->where('already_exists', true)->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("SNMP discovery failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Device discovery failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run SNMP monitoring for devices.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function runMonitoring(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'nullable|integer|exists:devices,id',
            'critical_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $startTime = microtime(true);

            if ($request->filled('device_id')) {
                // Monitor single device
                $device = Device::findOrFail($request->device_id);
                
                if (!$device->snmp_enabled) {
                    return response()->json([
                        'success' => false,
                        'message' => 'SNMP is not enabled for this device.',
                    ], 422);
                }

                $result = $this->snmpMonitoring->monitorDevice($device);
                $duration = round(microtime(true) - $startTime, 2);

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] 
                        ? 'Device monitored successfully' 
                        : 'Device monitoring failed',
                    'data' => [
                        'device' => $result,
                        'duration_seconds' => $duration,
                    ],
                ]);
            }

            // Run full monitoring cycle
            $results = $this->snmpMonitoring->runMonitoringCycle();
            $duration = round(microtime(true) - $startTime, 2);

            return response()->json([
                'success' => true,
                'message' => "Monitoring cycle completed. {$results['successful']}/{$results['total']} devices successful.",
                'data' => [
                    'summary' => [
                        'total_devices' => $results['total'],
                        'successful' => $results['successful'],
                        'failed' => $results['failed'],
                        'skipped' => $results['skipped'],
                        'duration_seconds' => $duration,
                    ],
                    'details' => $results['details'],
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("SNMP monitoring failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Monitoring cycle failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SNMP walk for a specific OID.
     * 
     * @param Device $device
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function walkOID(Device $device, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'oid' => 'required|string|regex:/^(\d+\.?){1,20}$/',
            'community' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OID format.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if (!$device->snmp_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'SNMP not enabled for this device.',
                ], 422);
            }

            $community = $request->community ?? $device->snmp_community;
            $oid = $request->oid;

            $result = $this->snmpService->walkOID(
                $device->ip_address,
                $oid,
                $community,
                $device->snmp_timeout
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'oid' => $oid,
                    'values' => $result,
                    'count' => count($result),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("SNMP walk failed for OID {$request->oid}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'SNMP walk failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set SNMP value on a device.
     * 
     * @param Device $device
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setValue(Device $device, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'oid' => 'required|string',
            'type' => 'required|string|in:i,s,a,x,d,n',
            'value' => 'required|string',
            'community' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if (!$device->snmp_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'SNMP not enabled for this device.',
                ], 422);
            }

            $community = $request->community ?? $device->snmp_community;
            
            $success = $this->snmpService->set(
                $device->ip_address,
                $request->oid,
                $request->type,
                $request->value,
                $community,
                $device->snmp_timeout
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'SNMP value set successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to set SNMP value.',
            ], 422);
        } catch (\Exception $e) {
            Log::error("SNMP set failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'SNMP set operation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SNMP-enabled devices list.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function devices(): JsonResponse
    {
        try {
            $devices = Device::where('snmp_enabled', true)
                ->with('latestSnmpData')
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'name' => $device->name,
                        'ip_address' => $device->ip_address,
                        'type' => $device->type,
                        'vendor' => $device->vendor,
                        'status' => $device->status,
                        'is_critical' => $device->is_critical,
                        'snmp_config' => [
                            'version' => $device->snmp_version,
                            'port' => $device->snmp_port,
                            'polling_interval' => $device->snmp_polling_interval,
                            'polling_enabled' => $device->snmp_polling_enabled,
                        ],
                        'latest_metrics' => $device->latestSnmpData ? [
                            'cpu_usage' => $device->latestSnmpData->cpu_usage,
                            'memory_usage' => $device->latestSnmpData->memory_usage,
                            'collected_at' => $device->latestSnmpData->collected_at,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'devices' => $devices,
                    'total' => $devices->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get SNMP devices: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get devices list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}