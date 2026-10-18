<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(
            HtmlSanitizerConfig::class,
            static fn (HtmlSanitizerConfig $config): HtmlSanitizerConfig => $config->allowAttribute('data-config', allowedElements: 'div'),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Filament stores RichEditor custom block configuration on data-config.
        // The video block validates every value before using it.
    }
}
