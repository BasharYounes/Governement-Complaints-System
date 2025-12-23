<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Storage;


class UploadBackupToGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upload-backup-to-google-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client();
    $client->setAuthConfig(storage_path('app/google/service-account.json'));
    $client->addScope(Drive::DRIVE_FILE);

    $drive = new Drive($client);

    $files = Storage::disk('local')->files('Laravel');
    $latest = collect($files)->sortDesc()->first();

    $fileMetadata = new Drive\DriveFile([
        'name' => basename($latest),
        'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
    ]);

    $content = Storage::disk('local')->get($latest);

    $drive->files->create($fileMetadata, [
        'data' => $content,
        'uploadType' => 'multipart',
    ]);

    $this->info('Backup uploaded to Google Drive ✔');

    }
}
