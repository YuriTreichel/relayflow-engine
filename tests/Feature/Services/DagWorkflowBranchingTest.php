<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;

it('executes alternative branch (on_failure_step_id) when a condition fails', function () {
    $workflow = Workflow::create([
        'name' => 'DAG Branching Workflow',
        'status' => 'active',
    ]);

    // Step 2 (VIP Route)
    $stepHighValue = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Log High-Value Order',
        'type' => 'log',
        'config' => ['message' => 'Processing high-value VIP order!'],
        'order' => 2,
    ]);

    // Step 3 (Standard Route)
    $stepLowValue = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Log Low-Value Order',
        'type' => 'log',
        'config' => ['message' => 'Processing standard order'],
        'order' => 3,
    ]);

    // Step 1 (Root Condition - order 1)
    $stepCondition = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Validate Order Amount',
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

    // Trigger 1: amount = 150 (Condition fails -> routes to Step 3)
    $payloadLow = ['amount' => 150];
    $logLow = $executor->execute($workflow, $payloadLow);

    expect($logLow->status)->toBe('completed');
    expect(data_get($logLow->output_payload, 'steps.step_' . $stepCondition->id . '.status'))->toBe('failed_branch');

    // Trigger 2: amount = 800 (Condition passes -> routes to Step 2)
    $payloadHigh = ['amount' => 800];
    $logHigh = $executor->execute($workflow, $payloadHigh);

    expect($logHigh->status)->toBe('completed');
    expect(data_get($logHigh->output_payload, 'steps.step_' . $stepCondition->id . '.status'))->toBe('passed');
});
