<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    protected $registry;

    public function __construct()
    {
        $this->registry = \Prometheus\CollectorRegistry::getDefault();
    }


    public function index()
    {
        $renderer = new RenderTextFormat();
        return response($renderer->render($this->registry->getMetricFamilySamples()), 200)
            ->header('Content-Type', 'text/plain');
    }

    // public function index()
    // {
    //     try {
    //         $counter = $this->registry->registerCounter(
    //             'laravel', 
    //             'http_requests_total', 
    //             'Total de requisições HTTP', 
    //             ['method', 'status_code']
    //         );
    //         $counter->inc(['GET', '200']);
    //     } catch (MetricsRegistrationException $e) {
    //         return response()->json(['error' => 'Falha ao registrar métrica: ' . $e->getMessage()], 500);
    //     }

    //     return response()->json(['status' => 'metrics updated']);
    // }

    // public function expose()
    // {
    //     $renderer = new RenderTextFormat();
    //     $result = $renderer->render($this->registry->getMetricFamilySamples());

    //     return response($result, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
    // }
}
