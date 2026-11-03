<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;
use Illuminate\Support\Facades\Http;

it('executes a workflow with condition and http request steps', function () {
    // 1. Mock external HTTP response
    Http::fake([
        'api.example.com/*' => Http::response(['success' => true], 200),
    ]);

    // 2. Create Workflow
    $workflow = Workflow::create([
        'name' => 'Actions Test Workflow',
        'status' => 'active',
    ]);

    // Step 1: Condition (amount > 50)
    $stepCondition = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Check Amount',
        'type' => 'condition',
        'config' => ['field' => 'amount', 'operator' => '>', 'value' => 50],
        'order' => 1,
    ]);

    // Step 2: HTTP Dispatch
    $stepHttp = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Notify API',
        'type' => 'http_request',
        'config' => ['url' => 'https://api.example.com/webhook', 'method' => 'POST'],
        'order' => 2,
    ]);

    $executor = new WorkflowExecutorService();

    // 3. Execute with valid payload (amount = 100)
    $log = $executor->execute($workflow, ['amount' => 100]);

    // 4. Assertions
    expect($log->status)->toBe('completed');
    expect(data_get($log->output_payload, 'steps.step_' . $stepCondition->id . '.status'))->toBe('passed');
    expect(data_get($log->output_payload, 'steps.step_' . $stepHttp->id . '.output'))->toBe(['success' => true]);

    // Ensure Laravel HTTP client dispatched POST request
    Http::assertSent(function ($request) {
        return $request->url() == 'https://api.example.com/webhook' && $request['amount'] == 100;
    });
});

it('fails execution when condition step is not met', function () {
    $workflow = Workflow::create([
        'name' => 'Failing Condition Workflow',
        'status' => 'active',
    ]);

    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Check Minimum Amount',
        'type' => 'condition',
        'config' => ['field' => 'amount', 'operator' => '>', 'value' => 100],
        'order' => 1,
    ]);

    $executor = new WorkflowExecutorService();

    // Execute with amount = 20 (less than 100, should fail)
    $log = $executor->execute($workflow, ['amount' => 20]);

    expect($log->status)->toBe('failed');
    expect($log->error_message)->toContain('Condition interrupted');
});
