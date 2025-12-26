<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupToDrive extends Command
{
    protected $signature = 'app:backup-to-drive';
    protected $description = 'Upload local backups to Google Drive using rclone';

    public function handle()
{
    $backupPath = storage_path('app/Government Complaint System');

    $rclone = '"C:\rclone\rclone-v1.72.1-windows-amd64\rclone.exe"';

    $command = $rclone . ' copy ' .
        '"' . $backupPath . '" ' .
        '"batoul_drive:Government Complaint System" ' .
        '--log-file="' . storage_path('logs/rclone.log') . '" ' .
        '--log-level INFO';

    exec($command, $output, $status);

    if ($status !== 0) {
        Log::error('Backup upload to Google Drive failed', [
            'output' => $output
        ]);

        $this->error('❌ Upload failed');
        return Command::FAILURE;
    }

    $this->info('✅ Backup uploaded to Google Drive successfully');
    return Command::SUCCESS;
}

}
