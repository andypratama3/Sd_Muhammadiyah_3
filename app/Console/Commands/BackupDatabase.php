<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=7 : Jumlah backup yang akan disimpan}';
    protected $description = 'Backup database MySQL ke file SQL';

    protected $tables = [];
    protected $ignoreTables = [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'sessions',
        'notifications',
    ];

    public function handle()
    {
        $this->info('🚀 Memulai backup database...');

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $backupPath = storage_path("backups/{$filename}");

        if (!File::isDirectory(storage_path('backups'))) {
            File::makeDirectory(storage_path('backups'), 0755, true);
        }

        $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName}";

        if (!$this->option('no-tables') ?? false) {
            $this->tables = $this->getTables();
            $command .= ' ' . implode(' ', $this->tables);
        } else {
            $command .= ' --ignore-table=' . implode(' --ignore-table=', $this->tables);
        }

        $command .= " --single-transaction --quick --routines --triggers --events > {$backupPath}";

        $this->info("📁 Menyimpan ke: {$backupPath}");

        $startTime = microtime(true);

        try {
            $process = proc_open($command, [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            if (!is_resource($process)) {
                throw new \RuntimeException('Tidak dapat menjalankan mysqldump');
            }

            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            if ($exitCode !== 0) {
                throw new \RuntimeException("mysqldump gagal: {$stderr}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Backup gagal: {$e->getMessage()}");

            $this->info('💡 Mencoba alternatif dengan PHP...');
            $this->backupWithPHP($backupPath, $dbName);
        }

        $this->info("✅ Backup selesai dalam " . round(microtime(true) - $startTime, 2) . " detik");

        $this->cleanupOldBackups();

        $this->info('✅ Selesai!');

        return 0;
    }

    protected function getTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . config('database.connections.mysql.database');

        return collect($tables)
            ->pluck($tableKey)
            ->reject(fn ($table) => in_array($table, $this->ignoreTables))
            ->toArray();
    }

    protected function backupWithPHP(string $path, string $dbName)
    {
        $this->warn('⚠️  Backup dengan PHP (data saja)...');

        $tables = $this->getTables();
        $output = "-- Database: {$dbName}\n";
        $output .= "-- Backup Date: " . Carbon::now()->toDateTimeString() . "\n\n";

        foreach ($tables as $table) {
            $this->info("  📄 Backup table: {$table}");

            $rows = DB::table($table)->get();
            $output .= "\n-- Table: {$table}\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $create = DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $output .= $create->{'Create Table'} . ";\n\n";

            foreach ($rows as $row) {
                $values = collect((array) $row)
                    ->map(fn ($val) => $val === null ? 'NULL' : "'" . addslashes($val) . "'")
                    ->join(', ');

                $output .= "INSERT INTO `{$table}` VALUES ({$values});\n";
            }
        }

        File::put($path, $output);
        $this->info("  ✅ {$table} selesai");
    }

    protected function cleanupOldBackups()
    {
        $keep = (int) $this->option('keep');
        $backupDir = storage_path('backups');

        if (!File::isDirectory($backupDir)) {
            return;
        }

        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => str_starts_with($file->getFilename(), 'backup_'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->skip($keep);

        $count = 0;
        foreach ($files as $file) {
            File::delete($file->getPathname());
            $count++;
        }

        if ($count > 0) {
            $this->info("🗑️  Deleted {$count} old backup(s). keeping latest {$keep}");
        }
    }
}