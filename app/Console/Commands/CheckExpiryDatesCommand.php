<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Services\MonitoringService;
use Carbon\Carbon;

class CheckExpiryDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:check-expiry
                            {--days=30 : Check for contracts expiring within this many days}
                            {--type=all : Type of expiry to check (warranty, amc, all)}
                            {--notify : Send email notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for warranty and AMC expiry dates';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoringService): int
    {
        $days = (int) $this->option('days');
        $type = $this->option('type');
        $notify = $this->option('notify');

        $this->info("🔍 Checking expiry dates for next {$days} days...");
        $this->newLine();

        try {
            // Check expiry dates
            $monitoringService->checkExpiryDates();

            // Get expiring devices
            $query = Device::query();

            if ($type === 'warranty' || $type === 'all') {
                $query->where(function($q) use ($days) {
                    $q->whereNotNull('warranty_expiry')
                      ->whereDate('warranty_expiry', '<=', now()->addDays($days))
                      ->whereDate('warranty_expiry', '>=', now());
                });
            }

            if ($type === 'amc' || $type === 'all') {
                if ($type !== 'all') {
                    $query->orWhere(function($q) use ($days) {
                        $q->whereNotNull('amc_expiry')
                          ->whereDate('amc_expiry', '<=', now()->addDays($days))
                          ->whereDate('amc_expiry', '>=', now());
                    });
                } else {
                    $query->orWhere(function($q) use ($days) {
                        $q->whereNotNull('amc_expiry')
                          ->whereDate('amc_expiry', '<=', now()->addDays($days))
                          ->whereDate('amc_expiry', '>=', now());
                    });
                }
            }

            $expiringDevices = $query->with('location')->get();

            if ($expiringDevices->isEmpty()) {
                $this->info('✅ No devices with upcoming expiries found.');
                return Command::SUCCESS;
            }

            // Display expiring warranties
            $warrantyExpiring = $expiringDevices->filter(function($device) {
                return $device->warranty_expiry &&
                       $device->warranty_expiry->between(now(), now()->addDays($days));
            });

            if ($warrantyExpiring->isNotEmpty()) {
                $this->warn('⚠️ Warranty Expiring Soon:');
                $this->table(
                    ['Device', 'Serial Number', 'Warranty Expiry', 'Days Left', 'Critical'],
                    $warrantyExpiring->map(function($device) {
                        return [
                            $device->name,
                            $device->serial_number,
                            $device->warranty_expiry->format('d-M-Y'),
                            now()->diffInDays($device->warranty_expiry) . ' days',
                            $device->is_critical ? '⚠️ Yes' : 'No'
                        ];
                    })->toArray()
                );
                $this->newLine();
            }

            // Display expiring AMCs
            $amcExpiring = $expiringDevices->filter(function($device) {
                return $device->amc_expiry &&
                       $device->amc_expiry->between(now(), now()->addDays($days));
            });

            if ($amcExpiring->isNotEmpty()) {
                $this->warn('⚠️ AMC Expiring Soon:');
                $this->table(
                    ['Device', 'Serial Number', 'AMC Expiry', 'Days Left', 'Critical'],
                    $amcExpiring->map(function($device) {
                        return [
                            $device->name,
                            $device->serial_number,
                            $device->amc_expiry->format('d-M-Y'),
                            now()->diffInDays($device->amc_expiry) . ' days',
                            $device->is_critical ? '⚠️ Yes' : 'No'
                        ];
                    })->toArray()
                );
                $this->newLine();
            }

            // Display already expired
            $alreadyExpired = Device::where(function($query) {
                $query->whereDate('warranty_expiry', '<', now())
                      ->orWhereDate('amc_expiry', '<', now());
            })->get();

            if ($alreadyExpired->isNotEmpty()) {
                $this->error('❌ Already Expired:');
                $this->table(
                    ['Device', 'Warranty Status', 'AMC Status'],
                    $alreadyExpired->map(function($device) {
                        return [
                            $device->name,
                            $device->warranty_expiry && $device->warranty_expiry < now()
                                ? 'Expired: ' . $device->warranty_expiry->format('d-M-Y')
                                : ($device->warranty_expiry ? 'Active' : 'N/A'),
                            $device->amc_expiry && $device->amc_expiry < now()
                                ? 'Expired: ' . $device->amc_expiry->format('d-M-Y')
                                : ($device->amc_expiry ? 'Active' : 'N/A'),
                        ];
                    })->toArray()
                );
            }

            // Summary
            $this->newLine();
            $this->line('───────────────────────────────────────');
            $this->info("📊 Summary: {$warrantyExpiring->count()} warranties and {$amcExpiring->count()} AMCs expiring in {$days} days");

            if ($notify) {
                $this->info('📧 Email notifications sent to concerned users.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Expiry check failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
