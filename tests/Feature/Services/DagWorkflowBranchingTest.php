<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;

it('executes alternative branch (on_failure_step_id) when a condition fails', function () {
    $workflow = Workflow::create([
        'name' => 'Workflow com Bifurcação DAG',
        'status' => 'active',
    ]);

    // Passo 2 (Caminho VIP)
    $stepHighValue = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Log Pedido VIP / Alto Valor',
        'type' => 'log',
        'config' => ['message' => 'Processando pedido VIP!'],
        'order' => 2,
    ]);

    // Passo 3 (Caminho Padrão)
    $stepLowValue = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Log Pedido de Baixo Valor',
        'type' => 'log',
        'config' => ['message' => 'Processando pedido padrão'],
        'order' => 3,
    ]);

    // Passo 1 (Condição Raiz - order 1)
    $stepCondition = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Validar Valor do Pedido',
        'type' => 'condition',
        'config' => [
            'field' => 'amount',
            'operator' => '>=',
            'value' => 500,
        ],
        'order' => 1,
        'next_step_id' => $stepHighValue->id,
        'on_failure_step_id' => $stepLowValue->id,
    ]);

    $executor = new WorkflowExecutorService();

    // Disparo 1: amount = 150 (FALHA na condição -> Desvia para o Passo 3)
    $payloadLow = ['amount' => 150];
    $logLow = $executor->execute($workflow, $payloadLow);

    expect($logLow->status)->toBe('completed');
    expect(data_get($logLow->output_payload, 'steps.step_' . $stepCondition->id . '.status'))->toBe('failed_branch');

    // Disparo 2: amount = 800 (SUCESSO na condição -> Vai para o Passo 2)
    $payloadHigh = ['amount' => 800];
    $logHigh = $executor->execute($workflow, $payloadHigh);

    expect($logHigh->status)->toBe('completed');
    expect(data_get($logHigh->output_payload, 'steps.step_' . $stepCondition->id . '.status'))->toBe('passed');
});