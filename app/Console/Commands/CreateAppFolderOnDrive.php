<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class CreateAppFolderOnDrive extends Command
{
    protected $signature = 'backup:create-app-folder';
    protected $description = 'إنشاء مجلد باسم التطبيق على Google Drive';

    public function handle()
    {
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        if (!$folderId) {
            $this->error('GOOGLE_DRIVE_FOLDER_ID is not set in .env file');
            return 1;
        }

        // استخراج Folder ID من الرابط إن وجد
        if (filter_var($folderId, FILTER_VALIDATE_URL)) {
            if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                $folderId = $matches[1];
            } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                $folderId = $matches[1];
            }
        }
        $folderId = trim($folderId);

        $appName = config('backup.backup.name', env('APP_NAME', 'laravel-backup'));
        
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/google/service-account.json'));
            $client->addScope(Drive::DRIVE_FILE);
            $service = new Drive($client);

            // البحث عن المجلد
            $query = "name='{$appName}' and parents in '{$folderId}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
            $response = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1
            ]);

            if (count($response->getFiles()) > 0) {
                $this->info('✅ المجلد موجود بالفعل: ' . $response->getFiles()[0]->getName());
                $this->info('🆔 Folder ID: ' . $response->getFiles()[0]->getId());
                return 0;
            }

            // إنشاء المجلد
            $folderMetadata = new Drive\DriveFile([
                'name' => $appName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$folderId]
            ]);

            $folder = $service->files->create($folderMetadata, [
                'fields' => 'id, name, webViewLink'
            ]);

            $this->info('✅ تم إنشاء المجلد بنجاح!');
            $this->info('📁 اسم المجلد: ' . $folder->getName());
            $this->info('🆔 Folder ID: ' . $folder->getId());
            $this->info('🔗 الرابط: ' . $folder->getWebViewLink());
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ فشل إنشاء المجلد: ' . $e->getMessage());
            return 1;
        }
    }
}

