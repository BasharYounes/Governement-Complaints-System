<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanBackupTemp extends Command
{
    protected $signature = 'backup:clean-temp';
    protected $description = 'Clean the temporary backup directory (storage/app/backup-temp)';

    public function handle()
    {
        $path = storage_path('app/backup-temp');

        if (!File::exists($path)) {
            $this->info('No temporary backup directory found: ' . $path);
            return 0;
        }

        try {
            File::deleteDirectory($path);
            $this->info('Deleted temporary backup directory: ' . $path);
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to delete temporary backup directory: ' . $e->getMessage());
            return 1;
        }
    }
}
