<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;
use Illuminate\Support\Facades\Http;

it('interpolates nested variables and chains output between steps', function () {
    // 1. Simula a chamada da API do Passo 1 (Retorna um Token)
    Http::fake([
        'api.auth.com/login' => Http::response(['token' => 'bearer_token_xyz123'], 200),
        'api.crm.com/leads'  => Http::response(['success' => true], 200),
    ]);

    // 2. Cria o Workflow
    $workflow = Workflow::create([
        'name' => 'Fluxo com Variáveis Dinâmicas',
        'status' => 'active',
    ]);

    // Passo 1: Autenticar e obter Token
    $step1 = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Obter Token API',
        'type' => 'http_request',
        'config' => [
            'url' => 'https://api.auth.com/login',
            'method' => 'POST',
        ],
        'order' => 1,
    ]);

    // Passo 2: Enviar Lead para o CRM usando o e-mail do payload de entrada e o token gerado pelo Passo 1!
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Enviar Lead CRM',
        'type' => 'http_request',
        'config' => [
            'url' => 'https://api.crm.com/leads',
            'method' => 'POST',
            'body' => [
                'email' => '{{ customer.email }}',
                'auth_token' => '{{ steps.step_' . $step1->id . '.output.token }}',
            ],
        ],
        'order' => 2,
    ]);

    $executor = new WorkflowExecutorService();

    // 3. Executa o fluxo com payload complexo aninhado
    $payload = [
        'customer' => [
            'name' => 'Carlos Silva',
            'email' => 'carlos@empresa.com',
        ],
    ];

    $log = $executor->execute($workflow, $payload);

    // 4. Assertivas
    expect($log->status)->toBe('completed');

    // Garante que o Passo 2 interpolou perfeitamente {{ customer.email }} e {{ steps.step_1.output.token }}
    Http::assertSent(function ($request) {
        if ($request->url() === 'https://api.crm.com/leads') {
            return $request['email'] === 'carlos@empresa.com' &&
                   $request['auth_token'] === 'bearer_token_xyz123';
        }
        return true;
    });
});