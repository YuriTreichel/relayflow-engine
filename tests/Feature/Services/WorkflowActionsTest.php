<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;
use Illuminate\Support\Facades\Http;

it('executes a workflow with condition and http request steps', function () {
    // 1. Simula a resposta HTTP externa para não fazer chamadas de verdade na rede
    Http::fake([
        'api.exemplo.com/*' => Http::response(['success' => true], 200),
    ]);

    // 2. Criar Workflow
    $workflow = Workflow::create([
        'name' => 'Fluxo de Teste de Ações',
        'status' => 'active',
    ]);

    // Passo 1: Condicional (Se amount > 50)
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Verificar Valor',
        'type' => 'condition',
        'config' => ['field' => 'amount', 'operator' => '>', 'value' => 50],
        'order' => 1,
    ]);

    // Passo 2: Disparo HTTP
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Notificar API',
        'type' => 'http_request',
        'config' => ['url' => 'https://api.exemplo.com/webhook', 'method' => 'POST'],
        'order' => 2,
    ]);

    $executor = new WorkflowExecutorService();

    // 3. Executar com um payload válido (amount = 100)
    $log = $executor->execute($workflow, ['amount' => 100]);

    // 4. Validações
    expect($log->status)->toBe('completed');
    expect($log->output_payload)->toHaveKey('conditions_passed');
    expect($log->output_payload)->toHaveKey('http_responses');

    // Garante que o HTTP Client do Laravel recebeu o disparo POST
    Http::assertSent(function ($request) {
        return $request->url() == 'https://api.exemplo.com/webhook' && $request['amount'] == 100;
    });
});

it('fails execution when condition step is not met', function () {
    $workflow = Workflow::create([
        'name' => 'Fluxo com Condição Falha',
        'status' => 'active',
    ]);

    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Verificar Valor Mínimo',
        'type' => 'condition',
        'config' => ['field' => 'amount', 'operator' => '>', 'value' => 100],
        'order' => 1,
    ]);

    $executor = new WorkflowExecutorService();

    // Executa com amount = 20 (menor que 100, deve falhar)
    $log = $executor->execute($workflow, ['amount' => 20]);

    expect($log->status)->toBe('failed');
    expect($log->error_message)->toContain('Condição interrompida');
});