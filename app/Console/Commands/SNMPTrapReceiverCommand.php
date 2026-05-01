<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\Alert;
use App\Events\SNMPTrapReceived;

class SNMPTrapReceiverCommand extends Command
{
    protected $signature = 'snmp:trap-listen 
                            {--port=162 : Port to listen for SNMP traps}
                            {--community=public : Community string for trap authentication}';

    protected $description = 'Listen for SNMP traps and process them';

    protected $socket;
    protected $running = true;

    public function handle(): int
    {
        $port = (int) $this->option('port');
        $community = $this->option('community');

        $this->info("🔍 Starting SNMP trap listener on UDP port {$port}...");
        $this->info("Community: {$community}");
        $this->info("Press Ctrl+C to stop.");
        $this->newLine();

        // Create UDP socket
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        
        if (!$this->socket) {
            $this->error('Failed to create socket: ' . socket_strerror(socket_last_error()));
            return Command::FAILURE;
        }

        // Bind to port
        if (!socket_bind($this->socket, '0.0.0.0', $port)) {
            $this->error('Failed to bind socket: ' . socket_strerror(socket_last_error($this->socket)));
            return Command::FAILURE;
        }

        // Set non-blocking
        socket_set_nonblock($this->socket);

        // Set socket timeout
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);

        $this->info('✅ Trap listener started. Waiting for traps...');
        $this->newLine();

        // Signal handler for graceful shutdown
        pcntl_signal(SIGINT, function () {
            $this->running = false;
        });
        pcntl_signal(SIGTERM, function () {
            $this->running = false;
        });

        // Listen loop
        while ($this->running) {
            $buffer = '';
            $from = '';
            $port = 0;

            $bytes = @socket_recvfrom($this->socket, $buffer, 65535, 0, $from, $port);

            if ($bytes > 0) {
                $this->processTrap($buffer, $from, $port);
            }

            pcntl_signal_dispatch();
            usleep(100000); // 100ms sleep
        }

        $this->cleanup();
        return Command::SUCCESS;
    }

    /**
     * Process received SNMP trap
     */
    protected function processTrap(string $trapData, string $from, int $port): void
    {
        $this->info("📨 Trap received from {$from}:{$port} at " . now()->format('Y-m-d H:i:s'));

        try {
            // Parse trap data
            $trap = $this->parseTrap($trapData);

            if (!$trap) {
                $this->warn('  ⚠️ Could not parse trap data');
                return;
            }

            // Find source device
            $device = Device::where('ip_address', $from)->first();
            
            if (!$device) {
                $this->warn("  ⚠️ Unknown device: {$from}");
                // Still log unknown traps
                $this->logUnknownTrap($trap, $from);
                return;
            }

            $this->info("  📡 Device: {$device->name}");
            $this->info("  📋 Type: {$trap['type']}");

            // Process different trap types
            switch ($trap['type']) {
                case 'linkDown':
                    $this->handleLinkDownTrap($device, $trap);
                    break;
                case 'linkUp':
                    $this->handleLinkUpTrap($device, $trap);
                    break;
                case 'coldStart':
                    $this->handleColdStartTrap($device, $trap);
                    break;
                case 'warmStart':
                    $this->handleWarmStartTrap($device, $trap);
                    break;
                case 'authenticationFailure':
                    $this->handleAuthFailureTrap($device, $trap);
                    break;
                default:
                    $this->handleGenericTrap($device, $trap);
            }

            // Broadcast event
            event(new SNMPTrapReceived($device, $trap));

        } catch (\Exception $e) {
            $this->error('  ❌ Error processing trap: ' . $e->getMessage());
            \Log::error('SNMP Trap processing error: ' . $e->getMessage(), [
                'from' => $from,
                'port' => $port,
                'data' => bin2hex($trapData),
            ]);
        }
    }

    /**
     * Parse raw SNMP trap data
     */
    protected function parseTrap(string $data): ?array
    {
        // Basic SNMP trap parsing
        // In production, you'd use a proper SNMP trap parser library
        
        $trap = [
            'raw' => bin2hex($data),
            'type' => 'unknown',
            'oid' => null,
            'value' => null,
            'timestamp' => now()->toIso8601String(),
        ];

        // Try to extract common trap information
        if (preg_match('/linkDown|linkUp|coldStart|warmStart|authenticationFailure/', $data, $matches)) {
            $trap['type'] = $matches[0];
        }

        // Extract interface index for link traps
        if (preg_match('/ifIndex\.(\d+)/', $data, $matches)) {
            $trap['interface_index'] = (int) $matches[1];
        }

        return $trap;
    }

    /**
     * Handle link down trap
     */
    protected function handleLinkDownTrap(Device $device, array $trap): void
    {
        $interfaceInfo = '';
        if (isset($trap['interface_index'])) {
            $port = $device->ports()->where('port_number', $trap['interface_index'])->first();
            if ($port) {
                $port->update(['status' => 'down']);
                $interfaceInfo = " (Port {$port->port_number}" . ($port->service_name ? " - {$port->service_name}" : "") . ")";
            }
        }

        Alert::create([
            'device_id' => $device->id,
            'type' => 'port_down',
            'severity' => 'high',
            'title' => "Link Down: {$device->name}{$interfaceInfo}",
            'message' => "SNMP trap: Interface down on {$device->name}{$interfaceInfo}",
            'additional_data' => $trap,
        ]);

        $this->info("  🔴 Created link down alert");
    }

    /**
     * Handle link up trap
     */
    protected function handleLinkUpTrap(Device $device, array $trap): void
    {
        $interfaceInfo = '';
        if (isset($trap['interface_index'])) {
            $port = $device->ports()->where('port_number', $trap['interface_index'])->first();
            if ($port && $port->status === 'down') {
                $port->update(['status' => 'active']);
                $interfaceInfo = " (Port {$port->port_number}" . ($port->service_name ? " - {$port->service_name}" : "") . ")";
            }
        }

        // Resolve previous link down alerts for this device
        Alert::where('device_id', $device->id)
            ->where('type', 'port_down')
            ->where('is_resolved', false)
            ->update([
                'is_resolved' => true,
                'resolved_at' => now(),
            ]);

        $this->info("  🟢 Link restored");
    }

    /**
     * Handle cold start trap
     */
    protected function handleColdStartTrap(Device $device, array $trap): void
    {
        Alert::create([
            'device_id' => $device->id,
            'type' => 'device_down',
            'severity' => 'critical',
            'title' => "Device Restarted (Cold): {$device->name}",
            'message' => "SNMP trap: {$device->name} has performed a cold start (full restart).",
            'additional_data' => $trap,
        ]);

        $this->info("  🔵 Device cold start detected");
    }

    /**
     * Handle warm start trap
     */
    protected function handleWarmStartTrap(Device $device, array $trap): void
    {
        Alert::create([
            'device_id' => $device->id,
            'type' => 'device_up',
            'severity' => 'medium',
            'title' => "Device Restarted (Warm): {$device->name}",
            'message' => "SNMP trap: {$device->name} has performed a warm start (soft restart).",
            'additional_data' => $trap,
        ]);

        $this->info("  🟡 Device warm start detected");
    }

    /**
     * Handle authentication failure trap
     */
    protected function handleAuthFailureTrap(Device $device, array $trap): void
    {
        Alert::create([
            'device_id' => $device->id,
            'type' => 'error',
            'severity' => 'high',
            'title' => "SNMP Auth Failed: {$device->name}",
            'message' => "SNMP authentication failure detected on {$device->name}. Possible unauthorized access attempt.",
            'additional_data' => $trap,
        ]);

        $this->info("  🔴 Authentication failure detected");
    }

    /**
     * Handle generic/unrecognized trap
     */
    protected function handleGenericTrap(Device $device, array $trap): void
    {
        Alert::create([
            'device_id' => $device->id,
            'type' => 'error',
            'severity' => 'low',
            'title' => "SNMP Trap: {$device->name}",
            'message' => "Unknown SNMP trap received from {$device->name}.",
            'additional_data' => $trap,
        ]);

        $this->info("  ⚪ Generic trap logged");
    }

    /**
     * Log trap from unknown device
     */
    protected function logUnknownTrap(array $trap, string $sourceIP): void
    {
        \Log::warning('SNMP trap from unknown device', [
            'source_ip' => $sourceIP,
            'trap_data' => $trap,
        ]);
    }

    /**
     * Cleanup resources
     */
    protected function cleanup(): void
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
        $this->info('🛑 Trap listener stopped.');
    }
}