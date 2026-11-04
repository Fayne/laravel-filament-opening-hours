<?php

namespace Fayne\OpeningHours;

use Illuminate\Support\ServiceProvider;

class OpeningHoursServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-opening-hours');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-opening-hours');
    }
}
