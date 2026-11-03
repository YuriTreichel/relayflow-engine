<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;
use App\Services\VariableInterpolatorService;
use Illuminate\Support\Facades\Http;
use Exception;

class HttpRequestAction implements ActionInterface
{
    public function __construct(
        private VariableInterpolatorService $interpolator
    ) {}

    public function handle(WorkflowStep $step, array $payload): array
    {
        // Interpolate step config based on payload data
        $config = $this->interpolator->interpolate($step->config ?? [], $payload);

        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');
        $body = $config['body'] ?? $payload;

        if (!$url) {
            throw new Exception("Step [{$step->name}]: URL not configured for HTTP action.");
        }

        $response = Http::send($method, $url, [
            'json' => $body,
        ]);

        if ($response->failed()) {
            throw new Exception("Step [{$step->name}]: External HTTP request failed with status {$response->status()}.");
        }

        $payload['steps']['step_' . $step->id] = [
            'output' => $response->json(),
        ];

        return $payload;
    }
}
