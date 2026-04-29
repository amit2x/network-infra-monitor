<?php

use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\CheckExpiryDatesCommand;
use App\Console\Commands\CleanOldLogsCommand;
use App\Console\Commands\GenerateDailyReportCommand;
use App\Console\Commands\RunMonitoringCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This is where you may register your Closure based console commands.
| Each Closure is bound to a command instance allowing a simple approach
| to interacting with each command's IO methods.
|
*/

// ============================================
// Monitoring Schedules
// ============================================

// Run monitoring every 5 minutes for critical devices
Schedule::command(RunMonitoringCommand::class, ['--critical-only'])
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/monitoring-critical.log'));

// Run full monitoring every 15 minutes
Schedule::command(RunMonitoringCommand::class)
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/monitoring-full.log'));

// Check for warranty and AMC expiry dates daily at 9:00 AM
Schedule::command(CheckExpiryDatesCommand::class, ['--notify'])
    ->dailyAt('09:00')
    ->appendOutputTo(storage_path('logs/expiry-check.log'));

// Clean old monitoring logs daily at 1:00 AM
Schedule::command(CleanOldLogsCommand::class, [
    '--days' => 30,
    '--type' => 'all',
    '--force' => true
])
    ->dailyAt('01:00')
    ->appendOutputTo(storage_path('logs/clean-logs.log'));

// ============================================
// Report Schedules
// ============================================

// Generate daily report at 8:00 AM
Schedule::command(GenerateDailyReportCommand::class, [
    '--email' => 'admin@example.com', // Change this to your admin email
])
    ->dailyAt('08:00')
    ->appendOutputTo(storage_path('logs/daily-report.log'));

// Generate weekly summary report every Monday at 9:00 AM
Schedule::command(GenerateDailyReportCommand::class, [
    '--date' => 'last week',
    '--format' => 'json',
])
    ->weeklyOn(1, '09:00')
    ->appendOutputTo(storage_path('logs/weekly-report.log'));

// ============================================
// Maintenance Schedules
// ============================================

// Backup database daily at 2:00 AM
Schedule::command(BackupDatabaseCommand::class, ['--compress'])
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/backup.log'));

// Clean old log files weekly
Schedule::call(function () {
    $logPath = storage_path('logs');
    $files = glob($logPath . '/*.log');
    $cutoff = now()->subDays(30);

    foreach ($files as $file) {
        if (filemtime($file) < $cutoff->timestamp) {
            unlink($file);
        }
    }
})->weekly()->sundays()->at('03:00');

// Optimize database weekly
Schedule::call(function () {
    DB::statement('OPTIMIZE TABLE devices');
    DB::statement('OPTIMIZE TABLE ports');
    DB::statement('OPTIMIZE TABLE monitoring_logs');
    DB::statement('OPTIMIZE TABLE alerts');
})->weekly()->sundays()->at('02:30');

// ============================================
// Health Checks
// ============================================

// Ping critical devices every minute
Schedule::command(RunMonitoringCommand::class, ['--critical-only', '--timeout=3'])
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

// Check for offline devices every 10 minutes
Schedule::call(function () {
    $offlineDevices = \App\Models\Device::where('status', 'offline')
        ->where('monitoring_enabled', true)
        ->where('is_critical', true)
        ->count();

    if ($offlineDevices > 0) {
        Log::warning("{$offlineDevices} critical devices are offline");
    }
})->everyTenMinutes();

// Clear application cache weekly
Schedule::command('cache:clear')
    ->weekly()
    ->sundays()
    ->at('04:00');

// ============================================
// Queue Worker (if using queues)
// ============================================

// Keep queue worker running
// Schedule::command('queue:work --stop-when-empty')
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->runInBackground();
