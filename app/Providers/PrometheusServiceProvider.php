<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register()
    {
        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => env('REDIS_PORT', 6379),
                'password' => env('REDIS_PASSWORD', null),
                'timeout' => 0.1,
                'read_timeout' => 10,
                'persistent_connections' => false
            ]
        );

        $this->app->singleton(CollectorRegistry::class, function () {
            return new CollectorRegistry(new Redis());
        });
    }

    public function boot()
    {
        //
    }
}
