<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;

it('executes a workflow with steps successfully', function () {
    // 1. ARRANGE
    $workflow = Workflow::create([
        'name' => 'Welcome Test Workflow',
        'status' => 'active',
    ]);

    // Create 2 steps for workflow
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Inbound Log',
        'type' => 'log',
        'order' => 1,
    ]);

    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Process Data',
        'type' => 'action',
        'order' => 2,
    ]);

    $executor = new WorkflowExecutorService();

    // 2. ACT
    $executionLog = $executor->execute($workflow, ['user_id' => 123]);

    // 3. ASSERT
    expect($executionLog->status)->toBe('completed');
    expect($executionLog->workflow_id)->toBe($workflow->id);
    expect($executionLog->output_payload)->toHaveKey('processed_by_step_2');
    
    // Confirm execution record was persisted in database
    $this->assertDatabaseHas('execution_logs', [
        'id' => $executionLog->id,
        'status' => 'completed',
    ]);
});
