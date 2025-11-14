<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\ComplaintAuditLog;
use App\Strategies\Content\AudiobookStrategy;
use App\Strategies\Content\PodcastStrategy;
use App\Services\Channel\ChannelService;
use App\Repositories\Channel\Contents\PodcastRepository;
use App\Repositories\Channel\Contents\AudiobookRepository;
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

        // تسجيل مراقب نموذج الشكوى
        Complaint::observe(\App\Observer\ComplaintObserver::class);
    }
}
