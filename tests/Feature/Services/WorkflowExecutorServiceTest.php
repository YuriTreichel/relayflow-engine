<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;

it('executes a workflow with steps successfully', function () {
    // 1. ARRANGE (Preparar os dados de teste)
    $workflow = Workflow::create([
        'name' => 'Fluxo de Teste de Boas-Vindas',
        'status' => 'active',
    ]);

    // Criando 2 passos para o fluxo
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Log de Entrada',
        'type' => 'log',
        'order' => 1,
    ]);

    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Processar Dados',
        'type' => 'action',
        'order' => 2,
    ]);

    $executor = new WorkflowExecutorService();

    // 2. ACT (Executar o serviço que queremos testar)
    $executionLog = $executor->execute($workflow, ['user_id' => 123]);

    // 3. ASSERT (Verificar se o resultado foi o esperado)
    expect($executionLog->status)->toBe('completed');
    expect($executionLog->workflow_id)->toBe($workflow->id);
    expect($executionLog->output_payload)->toHaveKey('processed_by_step_2');
    
    // Confirma que gravou a execução no banco de dados SQLite
    $this->assertDatabaseHas('execution_logs', [
        'id' => $executionLog->id,
        'status' => 'completed',
    ]);
});