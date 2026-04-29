<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--path= : Backup path (default: storage/backups)}
                            {--compress : Compress the backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the application database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('💾 Starting database backup...');

        try {
            $path = $this->option('path') ?? storage_path('backups');
            $compress = $this->option('compress');

            // Create backup directory if not exists
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
            $filepath = $path . '/' . $filename;

            // Get database configuration
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            // Build mysqldump command
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$filepath}";

            if ($compress) {
                $command .= " && gzip {$filepath}";
                $filepath .= '.gz';
            }

            // Execute backup
            $this->line("Executing: mysqldump...");
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                $size = filesize($filepath);
                $this->info("✅ Backup created successfully!");
                $this->line("File: {$filepath}");
                $this->line("Size: " . round($size / 1024 / 1024, 2) . " MB");

                // Clean old backups (keep last 7 days)
                $this->cleanOldBackups($path, 7);
            } else {
                $this->error('❌ Backup failed!');
                return Command::FAILURE;
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function cleanOldBackups(string $path, int $keepDays): void
    {
        $files = glob($path . '/backup-*.sql*');
        $cutoff = now()->subDays($keepDays)->timestamp;

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->line("🧹 Cleaned {$deleted} old backup(s).");
        }
    }
}
