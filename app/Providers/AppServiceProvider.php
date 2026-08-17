<?php

namespace App\Providers;

use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WhatsAppService::class, function () {
            return new WhatsAppService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('printQr', function ($user) {
            return $user->canPrintQr();
        });

        // Register WhatsApp notification channel
        $this->app->make(ChannelManager::class)->extend('whatsapp', function ($app) {
            return new WhatsAppChannel($app->make(WhatsAppService::class));
        });
    }
}
