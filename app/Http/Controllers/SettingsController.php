<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $settings = [
            'monitoring_interval' => config('monitoring.interval', 5),
            'alert_email_enabled' => config('monitoring.alert_email', true),
            'ping_timeout' => config('monitoring.ping_timeout', 2),
            'log_retention_days' => config('monitoring.log_retention_days', 30),
            'default_port_count' => config('monitoring.default_port_count', 24),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'monitoring_interval' => 'required|integer|min:1|max:60',
            'alert_email_enabled' => 'boolean',
            'ping_timeout' => 'required|integer|min:1|max:10',
            'log_retention_days' => 'required|integer|min:7|max:365',
            'default_port_count' => 'required|integer|in:8,16,24,48',
        ]);

        try {
            // Update settings in database or .env file
            $this->updateEnvironmentFile([
                'MONITORING_INTERVAL' => $request->monitoring_interval,
                'PING_TIMEOUT' => $request->ping_timeout,
                'LOG_RETENTION_DAYS' => $request->log_retention_days,
            ]);

            Artisan::call('config:clear');

            return redirect()
                ->route('admin.settings')
                ->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    protected function updateEnvironmentFile(array $data)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        }

        file_put_contents($envFile, $envContent);
    }

    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os' => PHP_OS,
            'database' => config('database.default'),
            'database_size' => $this->getDatabaseSize(),
            'disk_free' => disk_free_space('/'),
            'disk_total' => disk_total_space('/'),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'uptime' => $this->getServerUptime(),
        ];

        return response()->json($info);
    }

    protected function getDatabaseSize()
    {
        try {
            $result = \DB::select("SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?", [config('database.connections.mysql.database')]);

            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    protected function getServerUptime()
    {
        if (PHP_OS === 'Linux') {
            $uptime = shell_exec('uptime -p');
            return trim(str_replace('up ', '', $uptime));
        }
        return 'N/A';
    }
}
