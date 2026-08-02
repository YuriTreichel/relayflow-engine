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
        // Interpola a configuração do step com base nos dados que estão transitando no payload
        $config = $this->interpolator->interpolate($step->config ?? [], $payload);

        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');
        $body = $config['body'] ?? $payload;

        if (!$url) {
            throw new Exception("Passo [{$step->name}]: URL não configurada para a ação HTTP.");
        }

        $response = Http::send($method, $url, [
            'json' => $body,
        ]);

        if ($response->failed()) {
            throw new Exception("Passo [{$step->name}]: Falha na requisição HTTP externa com status {$response->status()}.");
        }

        $payload['steps']['step_' . $step->id] = [
            'output' => $response->json(),
        ];

        return $payload;
    }
}