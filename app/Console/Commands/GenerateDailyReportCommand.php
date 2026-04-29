<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\Port;
use App\Models\Alert;
use App\Models\MonitoringLog;
use App\Services\DeviceService;
use Carbon\Carbon;

class GenerateDailyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily
                            {--date= : Date for report (Y-m-d format, default: today)}
                            {--email= : Email address to send report to}
                            {--format=text : Output format (text, json, csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily network monitoring report';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $format = $this->option('format');

        $this->info("📊 Generating daily report for {$date->format('d-M-Y')}...");
        $this->newLine();

        try {
            // Collect statistics
            $stats = $this->collectDailyStats($date);

            if ($format === 'json') {
                $this->outputJson($stats);
            } elseif ($format === 'csv') {
                $this->outputCsv($stats);
            } else {
                $this->outputText($stats, $date);
            }

            // Send email if requested
            if ($email = $this->option('email')) {
                $this->sendReportEmail($email, $stats, $date);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to generate report: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function collectDailyStats(Carbon $date): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'devices' => [
                'total' => Device::count(),
                'online' => Device::where('status', 'online')->count(),
                'offline' => Device::where('status', 'offline')->count(),
                'maintenance' => Device::where('status', 'maintenance')->count(),
                'new_today' => Device::whereDate('created_at', $date)->count(),
            ],
            'ports' => [
                'total' => Port::count(),
                'active' => Port::where('status', 'active')->count(),
                'free' => Port::where('status', 'free')->count(),
                'down' => Port::where('status', 'down')->count(),
                'utilization' => Port::count() > 0
                    ? round((Port::where('status', 'active')->count() / Port::count()) * 100, 2)
                    : 0,
            ],
            'monitoring' => [
                'total_checks' => MonitoringLog::whereDate('created_at', $date)
                    ->where('event_type', 'ping_check')
                    ->count(),
                'successful_checks' => MonitoringLog::whereDate('created_at', $date)
                    ->where('event_type', 'ping_check')
                    ->where('status', 'success')
                    ->count(),
                'avg_response_time' => round(MonitoringLog::whereDate('created_at', $date)
                    ->where('event_type', 'ping_check')
                    ->where('status', 'success')
                    ->avg('response_time_ms') ?? 0, 2),
            ],
            'alerts' => [
                'total_today' => Alert::whereDate('created_at', $date)->count(),
                'critical' => Alert::whereDate('created_at', $date)
                    ->where('severity', 'critical')
                    ->count(),
                'resolved' => Alert::whereDate('created_at', $date)
                    ->where('is_resolved', true)
                    ->count(),
                'unresolved' => Alert::where('is_resolved', false)->count(),
            ],
            'uptime' => $this->calculateDailyUptime($date),
        ];
    }

    private function calculateDailyUptime(Carbon $date): float
    {
        $total = MonitoringLog::whereDate('created_at', $date)
            ->where('event_type', 'ping_check')
            ->count();

        if ($total === 0) return 0;

        $success = MonitoringLog::whereDate('created_at', $date)
            ->where('event_type', 'ping_check')
            ->where('status', 'success')
            ->count();

        return round(($success / $total) * 100, 2);
    }

    private function outputText(array $stats, Carbon $date): void
    {
        $this->line('═══════════════════════════════════════');
        $this->info('   DAILY NETWORK MONITORING REPORT');
        $this->line('═══════════════════════════════════════');
        $this->line("Date: {$date->format('d-M-Y')}");
        $this->line('───────────────────────────────────────');
        $this->newLine();

        $this->info('📡 DEVICE STATUS:');
        $this->line("  Total Devices: {$stats['devices']['total']}");
        $this->line("  Online: {$stats['devices']['online']}");
        $this->line("  Offline: {$stats['devices']['offline']}");
        $this->line("  Maintenance: {$stats['devices']['maintenance']}");
        $this->newLine();

        $this->info('🔌 PORT UTILIZATION:');
        $this->line("  Total Ports: {$stats['ports']['total']}");
        $this->line("  Active: {$stats['ports']['active']}");
        $this->line("  Free: {$stats['ports']['free']}");
        $this->line("  Utilization: {$stats['ports']['utilization']}%");
        $this->newLine();

        $this->info('📊 MONITORING:');
        $this->line("  Total Checks: {$stats['monitoring']['total_checks']}");
        $this->line("  Successful: {$stats['monitoring']['successful_checks']}");
        $this->line("  Avg Response: {$stats['monitoring']['avg_response_time']}ms");
        $this->line("  Daily Uptime: {$stats['uptime']}%");
        $this->newLine();

        $this->info('🔔 ALERTS:');
        $this->line("  Today: {$stats['alerts']['total_today']}");
        $this->line("  Critical: {$stats['alerts']['critical']}");
        $this->line("  Unresolved: {$stats['alerts']['unresolved']}");
        $this->newLine();

        $this->line('───────────────────────────────────────');
        $this->line('Report generated at: ' . now()->format('d-M-Y H:i:s'));
    }

    private function outputJson(array $stats): void
    {
        $this->line(json_encode($stats, JSON_PRETTY_PRINT));
    }

    private function outputCsv(array $stats): void
    {
        $csv = [];
        foreach ($stats as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $csv[] = [$key . '_' . $subKey, $subValue];
                }
            } else {
                $csv[] = [$key, $value];
            }
        }

        foreach ($csv as $row) {
            $this->line(implode(',', $row));
        }
    }

    private function sendReportEmail(string $email, array $stats, Carbon $date): void
    {
        $this->info("📧 Sending report to {$email}...");

        \Mail::to($email)->send(new \App\Mail\DailyReport($stats, $date));

        $this->info('✅ Report sent successfully.');
    }
}
