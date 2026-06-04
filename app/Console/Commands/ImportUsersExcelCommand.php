<?php

namespace App\Console\Commands;

use App\Imports\UsersHrImport;
use App\Models\UsersStaging;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportUsersExcelCommand extends Command
{
    protected $signature = 'users:import-excel
                            {path : Absolute path ke file Excel (.xlsx/.csv)}
                            {--fresh : Truncate users_staging sebelum import}';

    protected $description = 'Import user dari Excel HR (employeeId, name, departmentName, workUnitName) ke users_staging.';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Truncating users_staging ...');
            UsersStaging::query()->truncate();
        }

        $batchId = (string) Str::ulid();
        $this->info("Batch ID: {$batchId}");
        $this->info("Importing: {$path}");

        Excel::import(new UsersHrImport($batchId), $path);

        $count = UsersStaging::where('batch_id', $batchId)->count();
        $this->info("✓ Imported {$count} row ke users_staging (batch {$batchId})");
        $this->line('');
        $this->line('Langkah berikutnya:');
        $this->line("  php artisan users:auto-match --batch={$batchId}");

        return self::SUCCESS;
    }
}
