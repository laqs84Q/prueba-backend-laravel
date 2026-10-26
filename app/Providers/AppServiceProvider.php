<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Este proyecto no necesita servicios personalizados por ahora.
    }

    public function boot(): void
    {
    }
}
