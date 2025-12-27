<?php

namespace Core\Providers;

use Core\Http\LaravelHttpContext;
use Core\Interfaces\IHttpContext;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрация конфига
        $this->mergeConfigFrom(
            __DIR__.'/../../config/core.php',
            'core'
        );

        $this->app->singleton(IHttpContext::class, LaravelHttpContext::class);
    }

    public function boot()
    {
        // Публикация конфига
        $this->publishes([
            __DIR__.'/../../config/core.php' => config_path('core.php'),
        ], 'core-config');
    }
}
