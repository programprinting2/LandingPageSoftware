<?php

namespace App\Providers;

use App\Services\MailSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('system_settings')) {
                $this->app->make(MailSettingsService::class)->applyToConfig();
            }
        } catch (\Throwable) {
            // Database belum siap saat migrate/install.
        }
    }
}
