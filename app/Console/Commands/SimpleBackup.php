<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SimpleBackup extends Command
{
    protected $signature = 'backup:simple';
    protected $description = 'نسخ قاعدة البيانات ورفعها إلى Google Drive';

    public function handle()
    {
        $this->info('🚀 بدء النسخ الاحتياطي...');

        // 1. نسخ قاعدة البيانات
        $backupFile = $this->backupDatabase();

        if ($backupFile) {
            // 2. رفع إلى Google Drive
            $this->uploadToGoogleDrive($backupFile);

            $this->info('✅ تم النسخ الاحتياطي بنجاح!');
        }
    }

    private function backupDatabase()
    {
        $config = config('database.connections.mysql');

        $filename = 'complaints-backup' . date('Y-m-d-H-i-s') . '.sql';
        $filepath = storage_path('app/temp/' . $filename);

        // إنشاء مجلد مؤقت
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            $config['username'],
            $config['password'],
            $config['host'],
            $config['database'],
            $filepath
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("📦 تم نسخ قاعدة البيانات: {$filename}");
            return $filepath;
        } else {
            $this->error('❌ فشل نسخ قاعدة البيانات!');
            return false;
        }
    }

    private function uploadToGoogleDrive($filepath)
    {
        try {
            $filename = basename($filepath);

            // هذه هي الطريقة الصحيحة مع masbug/flysystem-google-drive-ext
            Storage::disk('google')->put($filename, file_get_contents($filepath));

            $this->info("☁️ تم الرفع إلى Google Drive: {$filename}");

            // حذف الملف المحلي
            unlink($filepath);

        } catch (\Exception $e) {
            $this->error('❌ فشل الرفع: ' . $e->getMessage());
        }
    }
}
