<?php

namespace LetoceilingCoder\VK;

use Illuminate\Support\ServiceProvider;
use LetoceilingCoder\VK\Bot;
use LetoceilingCoder\VK\MiniApp;

class VKServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем singleton для основных классов
        $this->app->singleton('vk.bot', function ($app) {
            return new Bot(config('vk.access_token'), config('vk.api_version'));
        });

        $this->app->singleton('vk.miniapp', function ($app) {
            return new MiniApp(config('vk.secret_key'));
        });

        // Алиасы
        $this->app->alias('vk.bot', Bot::class);
        $this->app->alias('vk.miniapp', MiniApp::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Публикация конфига
        $this->publishes([
            __DIR__.'/../config/vk.php' => config_path('vk.php'),
        ], 'vk-config');
    }
}

