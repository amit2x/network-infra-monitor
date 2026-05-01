<?php

namespace App\Console\Commands;

use App\Services\SNMPMonitoringService;
use Illuminate\Console\Command;

class SNMPMonitorCommand extends Command
{
    protected $signature = 'snmp:monitor 
                            {--device= : Monitor specific device ID}
                            {--critical-only : Only monitor critical devices}
                            {--batch-size=10 : Number of devices per batch}
                            {--interval=300 : Monitoring interval in seconds}';

    protected $description = 'Run SNMP monitoring cycle for network devices';

    public function handle(SNMPMonitoringService $snmpService): int
    {
        $this->info('🔍 Starting SNMP monitoring cycle...');
        
        try {
            $results = $snmpService->runMonitoringCycle();
            
            $this->newLine();
            $this->info('✅ SNMP monitoring completed');
            $this->line("Duration: {$results['duration']}s");
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Devices', $results['total']],
                    ['Successful', $results['successful']],
                    ['Failed', $results['failed']],
                    ['Skipped', $results['skipped']],
                ]
            );

            if ($results['failed'] > 0) {
                $this->warn("\n⚠️ Failed Devices:");
                foreach ($results['details'] as $detail) {
                    if (!$detail['success']) {
                        $this->line("  • {$detail['device_name']}: {$detail['error']}");
                    }
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ SNMP monitoring failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}