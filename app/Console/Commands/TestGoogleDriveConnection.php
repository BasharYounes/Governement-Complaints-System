<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;

class TestGoogleDriveConnection extends Command
{
    protected $signature = 'backup:test-google-drive';
    protected $description = 'اختبار الاتصال بـ Google Drive والتحقق من الإعدادات';

    public function handle()
    {
        $this->info('🔍 التحقق من إعدادات Google Drive...');
        $this->newLine();

        // 1. التحقق من ملف service-account.json
        $credentialsPath = storage_path('app/google/service-account.json');
        if (!file_exists($credentialsPath)) {
            $this->error('❌ ملف service-account.json غير موجود في: ' . $credentialsPath);
            return 1;
        }
        $this->info('✅ ملف service-account.json موجود');

        // 2. التحقق من Folder ID
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        if (!$folderId) {
            $this->error('❌ GOOGLE_DRIVE_FOLDER_ID غير موجود في ملف .env');
            $this->newLine();
            $this->warn('📝 كيفية الحصول على Folder ID:');
            $this->line('   1. افتح Google Drive وأنشئ مجلد جديد');
            $this->line('   2. افتح المجلد');
            $this->line('   3. انسخ الرابط من شريط العنوان');
            $this->line('   4. مثال: https://drive.google.com/drive/folders/1abc123xyz456');
            $this->line('   5. أضف Folder ID إلى .env: GOOGLE_DRIVE_FOLDER_ID=1abc123xyz456');
            $this->line('   أو يمكنك إضافة الرابط الكامل وسيتم استخراج ID تلقائياً');
            return 1;
        }
        $this->info('✅ GOOGLE_DRIVE_FOLDER_ID موجود: ' . substr($folderId, 0, 50) . (strlen($folderId) > 50 ? '...' : ''));

        // 3. استخراج Folder ID من الرابط إن وجد وفحص التكرار
        $originalFolderId = $folderId;
        if (filter_var($folderId, FILTER_VALIDATE_URL)) {
            if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                $folderId = $matches[1];
                $this->info('📝 تم استخراج Folder ID من الرابط: ' . $folderId);
            } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                $folderId = $matches[1];
                $this->info('📝 تم استخراج Folder ID من الرابط: ' . $folderId);
            } else {
                $this->error('❌ لا يمكن استخراج Folder ID من الرابط: ' . substr($originalFolderId, 0, 50));
                return 1;
            }
        } else {
            // التحقق من التكرار في Folder ID
            if (strlen($folderId) > 40) {
                // قد يكون مكرراً - نحاول إيجاد نمط
                $halfLength = floor(strlen($folderId) / 2);
                $firstHalf = substr($folderId, 0, $halfLength);
                $secondHalf = substr($folderId, $halfLength);
                
                if ($firstHalf === $secondHalf) {
                    $folderId = $firstHalf;
                    $this->warn('⚠️  تم اكتشاف Folder ID مكرر، تم تصحيحه تلقائياً: ' . $folderId);
                    $this->warn('   يُرجى تحديث ملف .env بقيمة: GOOGLE_DRIVE_FOLDER_ID=' . $folderId);
                }
            }
        }
        
        // تنظيف Folder ID من المسافات
        $folderId = trim($folderId);

        // 4. التحقق من حساب الخدمة
        try {
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Drive::DRIVE_FILE);
            $service = new Drive($client);

            // قراءة معلومات حساب الخدمة
            $serviceAccountInfo = json_decode(file_get_contents($credentialsPath), true);
            $serviceAccountEmail = $serviceAccountInfo['client_email'] ?? 'غير معروف';
            $this->info('✅ حساب الخدمة: ' . $serviceAccountEmail);

            // 5. التحقق من وجود المجلد والوصول إليه
            try {
                $folder = $service->files->get($folderId);
                $this->info('✅ المجلد موجود على Google Drive: ' . $folder->getName());
                
                // التحقق من الصلاحيات
                $permissions = $service->permissions->listPermissions($folderId);
                $hasAccess = false;
                foreach ($permissions->getPermissions() as $permission) {
                    if ($permission->getEmailAddress() === $serviceAccountEmail) {
                        $hasAccess = true;
                        $this->info('✅ حساب الخدمة لديه صلاحيات الوصول: ' . $permission->getRole());
                        break;
                    }
                }
                
                if (!$hasAccess) {
                    $this->warn('⚠️  قد لا يكون لحساب الخدمة صلاحيات الوصول للمجلد');
                    $this->warn('   تأكد من مشاركة المجلد مع: ' . $serviceAccountEmail);
                }

            } catch (\Google\Service\Exception $e) {
                $this->error('❌ لا يمكن الوصول للمجلد');
                $this->newLine();
                $this->warn('📋 Folder ID المستخدم: ' . $folderId);
                $this->warn('📧 حساب الخدمة: ' . $serviceAccountEmail);
                $this->newLine();
                $this->error('🔧 الحلول المقترحة:');
                $this->newLine();
                $this->line('1️⃣  التحقق من Folder ID:');
                $this->line('   - افتح Google Drive');
                $this->line('   - افتح المجلد المطلوب');
                $this->line('   - انسخ الرابط من شريط العنوان');
                $this->line('   - مثال: https://drive.google.com/drive/folders/1abc123xyz456');
                $this->line('   - استخرج Folder ID (الجزء بعد /folders/)');
                $this->newLine();
                $this->line('2️⃣  إنشاء مجلد جديد (إن لم يكن موجوداً):');
                if ($this->confirm('هل تريد إنشاء مجلد جديد على Google Drive تلقائياً؟', true)) {
                    try {
                        $folderName = 'ComplaintsSystem_Backups_' . date('Y-m-d');
                        $folderMetadata = new Drive\DriveFile([
                            'name' => $folderName,
                            'mimeType' => 'application/vnd.google-apps.folder'
                        ]);
                        
                        $createdFolder = $service->files->create($folderMetadata, [
                            'fields' => 'id, name, webViewLink'
                        ]);
                        
                        $newFolderId = $createdFolder->getId();
                        $this->newLine();
                        $this->info('✅ تم إنشاء المجلد بنجاح!');
                        $this->info('📁 اسم المجلد: ' . $createdFolder->getName());
                        $this->info('🆔 Folder ID: ' . $newFolderId);
                        $this->newLine();
                        $this->warn('⚠️  خطوات مهمة:');
                        $this->line('   1. افتح المجلد على Google Drive من هذا الرابط:');
                        $this->line('      ' . $createdFolder->getWebViewLink());
                        $this->line('   2. انقر بالزر الأيمن على المجلد → "مشاركة"');
                        $this->line('   3. أضف هذا البريد: ' . $serviceAccountEmail);
                        $this->line('   4. أعطه صلاحية "محرر" (Editor)');
                        $this->line('   5. احفظ التغييرات');
                        $this->newLine();
                        $this->info('📝 أضف هذا السطر إلى ملف .env:');
                        $this->line('   GOOGLE_DRIVE_FOLDER_ID=' . $newFolderId);
                        $this->newLine();
                        
                        if ($this->confirm('هل تريد تحديث ملف .env الآن؟', true)) {
                            $this->updateEnvFile('GOOGLE_DRIVE_FOLDER_ID', $newFolderId);
                            $this->info('✅ تم تحديث ملف .env بنجاح!');
                            $this->warn('⚠️  تأكد من مشاركة المجلد مع حساب الخدمة قبل تشغيل النسخ الاحتياطي');
                        }
                        
                        return 0;
                    } catch (\Exception $createError) {
                        $this->error('❌ فشل إنشاء المجلد: ' . $createError->getMessage());
                    }
                }
                $this->newLine();
                $this->line('3️⃣  مشاركة المجلد مع حساب الخدمة:');
                $this->line('   - افتح المجلد على Google Drive');
                $this->line('   - انقر بالزر الأيمن → "مشاركة"');
                $this->line('   - أضف هذا البريد: ' . $serviceAccountEmail);
                $this->line('   - أعطه صلاحية "محرر" (Editor)');
                $this->line('   - احفظ التغييرات');
                return 1;
            }

            // 6. اختبار الكتابة
            $this->newLine();
            $this->info('🧪 اختبار الكتابة على Google Drive...');
            try {
                $testFileName = 'test-' . date('Y-m-d-H-i-s') . '.txt';
                $testContent = 'This is a test file created at ' . now();
                
                Storage::disk('google')->put($testFileName, $testContent);
                $this->info('✅ تم إنشاء ملف تجريبي بنجاح: ' . $testFileName);
                
                // حذف الملف التجريبي
                Storage::disk('google')->delete($testFileName);
                $this->info('✅ تم حذف الملف التجريبي');
                
            } catch (\Exception $e) {
                $this->error('❌ فشل اختبار الكتابة: ' . $e->getMessage());
                return 1;
            }

            $this->newLine();
            $this->info('✅ جميع الاختبارات نجحت! Google Drive جاهز للاستخدام.');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ خطأ في الاتصال بـ Google Drive: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * تحديث ملف .env
     */
    private function updateEnvFile($key, $value)
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->error('ملف .env غير موجود!');
            return false;
        }

        $envContent = file_get_contents($envFile);
        
        // استبدال القيمة الموجودة أو إضافة جديدة
        if (preg_match("/^{$key}=.*$/m", $envContent)) {
            $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}\n";
        }

        file_put_contents($envFile, $envContent);
        return true;
    }
}

