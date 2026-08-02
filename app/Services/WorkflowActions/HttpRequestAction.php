<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;
use Illuminate\Support\Facades\Http;
use Exception;

class HttpRequestAction implements ActionInterface
{
    public function handle(WorkflowStep $step, array $payload): array
    {
        // O campo 'config' do step guardará as informações de URL e método HTTP
        // Exemplo de config: ['url' => 'https://api.exemplo.com/webhook', 'method' => 'POST']
        $config = $step->config ?? [];
        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');

        if (!$url) {
            throw new Exception("Passo [{$step->name}]: URL não configurada para a ação HTTP.");
        }

        // Executa a requisição externa via Laravel HTTP Client
        $response = Http::send($method, $url, [
            'json' => $payload,
        ]);

        if ($response->failed()) {
            throw new Exception("Passo [{$step->name}]: Falha na requisição HTTP externa com status {$response->status()}.");
        }

        // Adiciona a resposta da API externa ao payload do fluxo
        $payload['http_responses']['step_' . $step->id] = $response->json();

        return $payload;
    }
}