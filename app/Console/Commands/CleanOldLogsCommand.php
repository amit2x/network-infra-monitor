<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonitoringLog;
use App\Models\Alert;
use Carbon\Carbon;

class CleanOldLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:clean-logs
                            {--days=30 : Number of days to keep logs for}
                            {--type=all : Type of data to clean (logs, alerts, all)}
                            {--force : Force clean without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old monitoring logs and alerts to optimize database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $type = $this->option('type');
        $force = $this->option('force');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("🧹 Cleaning data older than {$days} days (before {$cutoffDate->format('Y-m-d')})...");
        $this->newLine();

        // Show current data counts
        $currentLogs = MonitoringLog::count();
        $currentAlerts = Alert::count();

        $this->line('Current data:');
        $this->line("  • Monitoring logs: {$currentLogs}");
        $this->line("  • Alerts: {$currentAlerts}");
        $this->newLine();

        // Confirmation
        if (!$force && !$this->confirm('Do you want to proceed with cleaning?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            $deletedLogs = 0;
            $deletedAlerts = 0;

            // Clean monitoring logs
            if ($type === 'logs' || $type === 'all') {
                $oldLogs = MonitoringLog::where('created_at', '<', $cutoffDate);
                $countLogs = $oldLogs->count();

                $this->line("Deleting {$countLogs} old monitoring logs...");
                $deletedLogs = $oldLogs->delete();
                $this->info("✅ Deleted {$deletedLogs} monitoring logs.");

                // Optimize table
                $this->line('Optimizing monitoring_logs table...');
                \DB::statement('OPTIMIZE TABLE monitoring_logs');
            }

            // Clean resolved alerts
            if ($type === 'alerts' || $type === 'all') {
                $oldAlerts = Alert::where('is_resolved', true)
                    ->where('created_at', '<', $cutoffDate);
                $countAlerts = $oldAlerts->count();

                $this->line("Deleting {$countAlerts} old resolved alerts...");
                $deletedAlerts = $oldAlerts->delete();
                $this->info("✅ Deleted {$deletedAlerts} old alerts.");

                // Optimize table
                $this->line('Optimizing alerts table...');
                \DB::statement('OPTIMIZE TABLE alerts');
            }

            // Show results
            $this->newLine();
            $this->line('───────────────────────────────────────');
            $this->info('📊 Cleanup Summary:');
            $this->table(
                ['Item', 'Deleted', 'Remaining'],
                [
                    ['Monitoring Logs', $deletedLogs, MonitoringLog::count()],
                    ['Alerts', $deletedAlerts, Alert::count()],
                    ['Total', $deletedLogs + $deletedAlerts, MonitoringLog::count() + Alert::count()],
                ]
            );

            // Calculate space saved (approximate)
            $spaceSaved = ($deletedLogs * 0.5) + ($deletedAlerts * 0.3); // Approximate KB
            $this->info("💾 Approximate space saved: " . round($spaceSaved / 1024, 2) . " MB");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            $this->line('Error: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
