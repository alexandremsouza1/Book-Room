<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class MetricsMiddleware
{
    private $histogram;

    public function __construct()
    {
        $registry = \Prometheus\CollectorRegistry::getDefault();

        $this->histogram = $registry->registerHistogram(
            '',  // Namespace vazio
            'http_request_duration_seconds',
            'Duration of HTTP requests',
            ['method', 'route'], 
            [0.01, 0.05, 0.1, 0.5, 1, 2, 5]
        );
    }

    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        $this->histogram->observe($duration, [$request->method(), $request->path()]);

        return $response;
    }
}
