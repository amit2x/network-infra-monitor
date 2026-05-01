<?php

namespace App\Console\Commands;

use App\Models\SnmpData;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SNMPCleanDataCommand extends Command
{
    protected $signature = 'snmp:clean-data 
                            {--days=30 : Days to retain SNMP data for}
                            {--device= : Clean data for specific device ID}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean old SNMP monitoring data';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $deviceId = $this->option('device');
        $dryRun = $this->option('dry-run');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("🧹 Cleaning SNMP data older than {$days} days...");

        $query = SnmpData::where('collected_at', '<', $cutoffDate);

        if ($deviceId) {
            $query->where('device_id', $deviceId);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No old SNMP data to clean.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("DRY RUN: Would delete {$count} SNMP data record(s).");
            $this->table(
                ['Device ID', 'Oldest Record', 'Newest Record', 'Count'],
                SnmpData::where('collected_at', '<', $cutoffDate)
                    ->selectRaw('device_id, MIN(collected_at) as oldest, MAX(collected_at) as newest, COUNT(*) as count')
                    ->groupBy('device_id')
                    ->get()
                    ->map(function ($row) {
                        return [
                            $row->device_id,
                            $row->oldest,
                            $row->newest,
                            $row->count,
                        ];
                    })->toArray()
            );
            return Command::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("✅ Deleted {$deleted} SNMP data record(s).");

        return Command::SUCCESS;
    }
}