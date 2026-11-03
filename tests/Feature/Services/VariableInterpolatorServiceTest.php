<?php

use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutorService;
use Illuminate\Support\Facades\Http;

it('interpolates nested variables and chains output between steps', function () {
    // 1. Mock API calls for step 1 auth and step 2 CRM
    Http::fake([
        'api.auth.com/login' => Http::response(['token' => 'bearer_token_xyz123'], 200),
        'api.crm.com/leads'  => Http::response(['success' => true], 200),
    ]);

    // 2. Create Workflow
    $workflow = Workflow::create([
        'name' => 'Dynamic Variables Workflow',
        'status' => 'active',
    ]);

    // Step 1: Authenticate and retrieve token
    $step1 = WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Get Auth Token',
        'type' => 'http_request',
        'config' => [
            'url' => 'https://api.auth.com/login',
            'method' => 'POST',
        ],
        'order' => 1,
    ]);

    // Step 2: Send lead using input payload customer email and Step 1 token
    WorkflowStep::create([
        'workflow_id' => $workflow->id,
        'name' => 'Send CRM Lead',
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

    // 3. Execute workflow with nested input payload
    $payload = [
        'customer' => [
            'name' => 'Charles Smith',
            'email' => 'charles@company.com',
        ],
    ];

    $log = $executor->execute($workflow, $payload);

    // 4. Assertions
    expect($log->status)->toBe('completed');

    // Verify Step 2 interpolated {{ customer.email }} and {{ steps.step_1.output.token }}
    Http::assertSent(function ($request) {
        if ($request->url() === 'https://api.crm.com/leads') {
            return $request['email'] === 'charles@company.com' &&
                   $request['auth_token'] === 'bearer_token_xyz123';
        }
        return true;
    });
});
