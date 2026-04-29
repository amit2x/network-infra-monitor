<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoringService;
use App\Models\Device;
use App\Models\MonitoringLog;
use Carbon\Carbon;

class RunMonitoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:run
                            {--device= : Monitor specific device by ID}
                            {--critical-only : Only monitor critical devices}
                            {--timeout=5 : Ping timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run device monitoring cycle and check device availability';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoringService): int
    {
        $startTime = microtime(true);

        $this->info('🔍 Starting network monitoring cycle...');
        $this->newLine();

        // Show monitoring parameters
        if ($this->option('device')) {
            $this->info("Target: Device ID {$this->option('device')}");
        }
        if ($this->option('critical-only')) {
            $this->info('Mode: Critical devices only');
        }

        $this->line('───────────────────────────────────────');

        try {
            // Run monitoring
            $results = $monitoringService->runMonitoringCycle();

            // Also check expiry dates
            if (!$this->option('device')) {
                $monitoringService->checkExpiryDates();
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Display results
            $this->newLine();
            $this->info('✅ Monitoring completed in ' . $duration . 'ms');
            $this->newLine();

            // Results table
            $this->table(
                ['Metric', 'Value', 'Status'],
                [
                    ['Total Devices', $results['total'], ''],
                    ['Devices Checked', $results['checked'], $results['checked'] === $results['total'] ? '✅' : '⚠️'],
                    ['Online', $results['online'], $results['online'] > 0 ? '✅' : '❌'],
                    ['Offline', $results['offline'], $results['offline'] === 0 ? '✅' : '❌'],
                    ['Status Changes', $results['status_changes'], $results['status_changes'] === 0 ? '✅' : '⚠️'],
                    ['Alerts Generated', $results['alerts_generated'], $results['alerts_generated'] === 0 ? '✅' : '⚠️'],
                ]
            );

            // Show offline devices if any
            if ($results['offline'] > 0) {
                $this->newLine();
                $this->warn('⚠️ Offline Devices:');

                $offlineDevices = Device::where('status', 'offline')
                    ->where('monitoring_enabled', true)
                    ->get(['id', 'name', 'ip_address', 'updated_at']);

                $this->table(
                    ['ID', 'Name', 'IP Address', 'Last Check'],
                    $offlineDevices->map(function($device) {
                        return [
                            $device->id,
                            $device->name,
                            $device->ip_address,
                            $device->updated_at->diffForHumans()
                        ];
                    })->toArray()
                );
            }

            // Show recent failures
            if ($results['status_changes'] > 0) {
                $this->newLine();
                $this->line('📋 Recent Status Changes:');

                $recentChanges = MonitoringLog::where('event_type', 'ping_check')
                    ->where('status', 'failure')
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->with('device')
                    ->latest()
                    ->take(5)
                    ->get();

                foreach ($recentChanges as $change) {
                    $this->line("  • {$change->device->name} ({$change->device->ip_address}) - {$change->message}");
                }
            }

            $this->newLine();
            $this->info('📊 Monitoring Summary: ' .
                round(($results['online'] / max($results['checked'], 1)) * 100, 2) . '% devices online');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Monitoring failed: ' . $e->getMessage());
            $this->line('Error trace: ' . $e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
