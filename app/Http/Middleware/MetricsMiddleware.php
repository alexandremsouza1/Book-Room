<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;

class MetricsMiddleware
{
    private $histogram;
    private $counter; // Adicionando o contador

    public function __construct()
    {
        $registry = CollectorRegistry::getDefault();
    
        $this->histogram = $registry->getOrRegisterHistogram(
            '',  // Namespace vazio
            'http_request_duration_seconds',
            'Duration of HTTP requests',
            ['method', 'route'], 
            [0.01, 0.05, 0.1, 0.5, 1, 2, 5]
        );

        // Contador para status 200
        $this->counter = $registry->getOrRegisterCounter(
            '',
            'http_request_total',
            'Total number of HTTP requests',
            ['method', 'route', 'status_code']
        );
    }

    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        // Registrando duração
        $this->histogram->observe($duration, [$request->method(), $request->path()]);

        // Incrementando contador para status 200
        if ($response->getStatusCode() === 200) {
            $this->counter->inc([$request->method(), $request->path(), '200']);
        }

        return $response;
    }
}
