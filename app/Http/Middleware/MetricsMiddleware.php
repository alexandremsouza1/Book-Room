<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;

class MetricsMiddleware
{
    private $histogram;
    private $counter; 

    public function __construct()
    {
        $registry = CollectorRegistry::getDefault();
    
        $this->histogram = $registry->getOrRegisterHistogram(
            '', 
            'http_request_duration_seconds',
            'Duration of HTTP requests',
            ['method', 'route'], 
            [0.01, 0.05, 0.1, 0.5, 1, 2, 5]
        );


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
    
        $this->histogram->observe($duration, [$request->method(), $request->path()]);
    
        $statusCode = $response->getStatusCode();
        $this->counter->inc([$request->method(), $request->path(), (string) $statusCode]);
    
        return $response;
    }
}
