<?php

namespace App\Providers;

use App\Models\Complaint;
use Illuminate\Support\Facades\Storage;
use Masbug\Flysystem\GoogleDrive\GoogleDriveAdapter;
use League\Flysystem\Filesystem as Flysystem;
use Illuminate\Support\ServiceProvider;
use Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل مسارات API
        Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

        Complaint::observe(\App\Observer\ComplaintObserver::class);

        Storage::extend('google', function ($app, $config) {
            try {
                // التحقق من وجود ملف حساب الخدمة
                $credentialsPath = $config['serviceAccount']['credentials'] ?? null;
                if (!$credentialsPath || !file_exists($credentialsPath)) {
                    throw new \Exception('Service account file not found at: ' . ($credentialsPath ?? 'not specified'));
                }

                $client = new \Google\Client();
                $client->setAuthConfig($credentialsPath);
                $client->addScope(\Google\Service\Drive::DRIVE_FILE);

                $service = new \Google\Service\Drive($client);

                // التحقق من وجود folder ID واستخراجه من الرابط إن وجد
                $folderId = $config['folder'] ?? null;
                if (!$folderId) {
                    throw new \Exception('GOOGLE_DRIVE_FOLDER_ID is not set in .env file');
                }

                // استخراج Folder ID من الرابط إذا كان رابط كامل
                // مثلاً: https://drive.google.com/drive/folders/1abc123xyz456 → 1abc123xyz456
                if (filter_var($folderId, FILTER_VALIDATE_URL)) {
                    // استخراج Folder ID من الرابط
                    if (preg_match('/\/folders\/([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                        $folderId = $matches[1];
                    } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $folderId, $matches)) {
                        $folderId = $matches[1];
                    } else {
                        throw new \Exception('Could not extract Folder ID from URL. Please provide only the Folder ID (e.g., 1abc123xyz456) or a valid Google Drive folder URL.');
                    }
                }

                // تنظيف Folder ID من أي مسافات أو أحرف غير مرغوب فيها
                $folderId = trim($folderId);

                // التحقق من Folder ID المكرر (إصلاح تلقائي)
                if (strlen($folderId) > 40) {
                    // قد يكون مكرراً - نحاول إيجاد نمط
                    $halfLength = floor(strlen($folderId) / 2);
                    $firstHalf = substr($folderId, 0, $halfLength);
                    $secondHalf = substr($folderId, $halfLength);

                    if ($firstHalf === $secondHalf) {
                        $folderId = $firstHalf;
                        \Log::warning('Google Drive Folder ID was duplicated, automatically fixed. Original: ' . $config['folder'] . ', Fixed: ' . $folderId);
                    }
                }

                // التحقق من أن Folder ID غير فارغ بعد التنظيف
                if (empty($folderId)) {
                    throw new \Exception('GOOGLE_DRIVE_FOLDER_ID is empty or invalid');
                }

                // التحقق من صحة تنسيق Folder ID (عادة ما يكون 33 حرفاً)
                if (strlen($folderId) < 25 || strlen($folderId) > 40) {
                    \Log::warning('Google Drive Folder ID length seems unusual: ' . strlen($folderId) . ' characters. Expected: 25-40 characters.');
                }

                // التأكد من وجود مجلد باسم التطبيق داخل المجلد المحدد
                // Spatie Backup سيقوم تلقائياً بإنشاء/الوصول إلى مجلد باسم التطبيق داخل الجذر
                $appName = config('backup.backup.name', env('APP_NAME', 'laravel-backup'));
                $this->ensureAppFolderExists($service, $folderId, $appName);

                // استخدام المجلد الرئيسي كجذر (root) - Spatie Backup سيقوم تلقائياً بإنشاء مجلد باسم التطبيق
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $folderId);

                // إنشاء Filesystem مخصص يتعامل مع writeStream() و listContents() بشكل صحيح
                // لأن Spatie Backup يستخدم getDriver()->writeStream() مباشرة
                $customFilesystem = new class($adapter, $appName, $service, $folderId) extends Flysystem {
                    private $appName;
                    private $driveService;
                    private $rootFolderId;
                    private $appFolderId;

                    public function __construct($adapter, $appName, $driveService, $rootFolderId) {
                        parent::__construct($adapter);
                        $this->appName = $appName;
                        $this->driveService = $driveService;
                        $this->rootFolderId = $rootFolderId;
                        $this->appFolderId = null;
                    }

                    private function ensureAppFolder() {
                        if ($this->appFolderId !== null) {
                            return $this->appFolderId;
                        }

                        try {
                            // البحث عن مجلد باسم التطبيق
                            $query = "name='{$this->appName}' and parents in '{$this->rootFolderId}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
                            $response = $this->driveService->files->listFiles([
                                'q' => $query,
                                'fields' => 'files(id)',
                                'pageSize' => 1
                            ]);

                            if (count($response->getFiles()) > 0) {
                                $this->appFolderId = $response->getFiles()[0]->getId();
                                return $this->appFolderId;
                            }

                            // إنشاء المجلد
                            $folderMetadata = new \Google\Service\Drive\DriveFile([
                                'name' => $this->appName,
                                'mimeType' => 'application/vnd.google-apps.folder',
                                'parents' => [$this->rootFolderId]
                            ]);

                            $folder = $this->driveService->files->create($folderMetadata, [
                                'fields' => 'id'
                            ]);

                            $this->appFolderId = $folder->getId();
                            return $this->appFolderId;
                        } catch (\Exception $e) {
                            \Log::error('Error ensuring app folder in writeStream: ' . $e->getMessage());
                            return $this->rootFolderId;
                        }
                    }

                    public function writeStream(string $path, $contents, array $config = []): void {
                        try {
                            $appFolderId = $this->ensureAppFolder();

                            // إزالة اسم التطبيق من المسار إن وجد
                            $fileName = $path;
                            if (strpos($path, $this->appName . '/') === 0) {
                                $fileName = str_replace($this->appName . '/', '', $path);
                            }

                            // كتابة الملف مباشرة في مجلد التطبيق باستخدام Google Drive API
                            // قراءة محتوى الملف من resource
                            $fileContent = '';
                            if (is_resource($contents)) {
                                rewind($contents);
                                while (!feof($contents)) {
                                    $fileContent .= fread($contents, 8192);
                                }
                            } else {
                                $fileContent = $contents;
                            }

                            // إنشاء ملف جديد في مجلد التطبيق
                            $fileMetadata = new \Google\Service\Drive\DriveFile([
                                'name' => $fileName,
                                'parents' => [$appFolderId]
                            ]);

                            $this->driveService->files->create($fileMetadata, [
                                'data' => $fileContent,
                                'uploadType' => 'multipart',
                                'fields' => 'id, name'
                            ]);

                            \Log::info("Successfully wrote file '{$fileName}' to app folder on Google Drive");

                        } catch (\Exception $e) {
                            \Log::error('Error in custom writeStream: ' . $e->getMessage() . ' Path: ' . $path);
                            throw $e;
                        }
                    }

                    public function listContents(string $location, bool $deep = self::LIST_SHALLOW): \League\Flysystem\DirectoryListing {
                        // إذا كان المسار هو اسم التطبيق، إرجاع محتويات مجلد التطبيق
                        if ($location === $this->appName) {
                            $appFolderId = $this->ensureAppFolder();

                            // إنشاء Adapter جديد باستخدام مجلد التطبيق كجذر
                            $appAdapter = new \Masbug\Flysystem\GoogleDriveAdapter($this->driveService, $appFolderId);

                            // إنشاء Filesystem مؤقت باستخدام مجلد التطبيق
                            $tempFilesystem = new Flysystem($appAdapter);
                            return $tempFilesystem->listContents('', $deep);
                        }
                        return parent::listContents($location, $deep);
                    }
                };

                // إنشاء FilesystemAdapter مخصص يتعامل مع files() بشكل صحيح
                return new class($customFilesystem, $adapter, $config, $appName) extends \Illuminate\Filesystem\FilesystemAdapter {
                    private $appName;

                    public function __construct($filesystem, $adapter, $config, $appName) {
                        parent::__construct($filesystem, $adapter, $config);
                        $this->appName = $appName;
                    }

                    public function files($directory = null, $recursive = false) {
                        // إذا كان المسار هو اسم التطبيق، احصل على ملفات مجلد التطبيق
                        if ($directory === $this->appName) {
                            return parent::files($directory, $recursive);
                        }
                        return parent::files($directory, $recursive);
                    }
                };
            } catch (\Exception $e) {
                // إظهار رسالة خطأ واضحة
                \Log::error('Google Drive Storage Extension Error: ' . $e->getMessage());
                throw new \Exception('Failed to initialize Google Drive storage: ' . $e->getMessage());
            }
        });

        // Ensure spatie backup uses a unique temporary directory per run to avoid
        // "Path .../backup-temp/temp already exists" errors when previous
        // temp directories couldn't be fully deleted.
        $this->app->bind('backup-temporary-project', function () {
            $base = config('backup.backup.temporary_directory') ?? storage_path('app/backup-temp');
            $unique = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('backup_', true);

            return new \Spatie\TemporaryDirectory\TemporaryDirectory($unique);
        });
    }

    /**
     * التأكد من وجود مجلد باسم التطبيق داخل المجلد المحدد
     */
    private function ensureAppFolderExists($service, $parentFolderId, $appName)
    {
        try {
            // البحث عن مجلد باسم التطبيق داخل المجلد المحدد
            $query = "name='{$appName}' and parents in '{$parentFolderId}' and mimeType='application/vnd.google-apps.folder' and trashed=false";
            $response = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'pageSize' => 1
            ]);

            if (count($response->getFiles()) > 0) {
                // المجلد موجود - إرجاع ID الخاص به
                return $response->getFiles()[0]->getId();
            }

            // المجلد غير موجود - إنشاؤه
            $folderMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $appName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId]
            ]);

            $folder = $service->files->create($folderMetadata, [
                'fields' => 'id, name'
            ]);

            \Log::info("Created app folder '{$appName}' on Google Drive with ID: " . $folder->getId());

            return $folder->getId();

        } catch (\Exception $e) {
            // في حالة الفشل، نستخدم المجلد الرئيسي
            \Log::warning('Failed to create/check app folder on Google Drive: ' . $e->getMessage() . '. Using parent folder instead.');
            return $parentFolderId;
        }
    }
}
