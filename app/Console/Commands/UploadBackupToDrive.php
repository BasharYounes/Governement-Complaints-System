<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Storage;

class UploadBackupToDrive extends Command
{
    protected $signature = 'backup:upload-drive';
    protected $description = 'Upload latest backup to Google Drive';

    public function handle()
    {
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        if (!$folderId) {
            $this->error('GOOGLE_DRIVE_FOLDER_ID is not set in .env file');
            return 1;
        }

        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->addScope(Drive::DRIVE_FILE);

        $service = new Drive($client);

        $folder = config('backup.backup.name');
        $files = Storage::disk('local')->files($folder);

        if (empty($files)) {
            $this->error('No backup files found in ' . $folder);
            return 1;
        }

        $path = collect($files)->last();

        try {
            $file = new Drive\DriveFile([
                'name' => basename($path),
                'parents' => [$folderId],
            ]);

            $service->files->create($file, [
                'data' => Storage::disk('local')->get($path),
                'uploadType' => 'multipart',
            ]);

            $this->info('Backup uploaded to Google Drive successfully');
        } catch (\Google\Service\Exception $e) {
            $this->error('Failed to upload backup: ' . $e->getMessage());
            $this->error('Folder ID: ' . $folderId);
            $this->error('Verify that the folder exists and the service account has access');
            return 1;
        }
    }
}
