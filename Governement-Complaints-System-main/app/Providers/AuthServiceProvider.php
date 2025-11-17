<?php

namespace App\Providers;

use Illuminate\Auth\Access\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Channel;
use App\Policies\ChannelPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Channel::class => ChannelPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        
    }
}