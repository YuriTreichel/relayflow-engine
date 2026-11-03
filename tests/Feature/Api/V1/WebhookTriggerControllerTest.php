<?php

use App\Jobs\ExecuteWorkflowJob;
use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;

it('triggers an active workflow via webhook and dispatches job', function () {
    // 1. Fake the queue to intercept job without RabbitMQ running
    Queue::fake();

    // 2. Create active workflow in test SQLite database
    $workflow = Workflow::create([
        'name' => 'Approved Sale Workflow',
        'status' => 'active',
    ]);

    $data = [
        'event' => 'order.paid',
        'customer' => [
            'email' => 'customer@email.com',
            'name' => 'John Doe'
        ]
    ];
    $timestamp = time();
    $payload = json_encode($data);
    $secret = config('app.key');
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    // 3. Simulate inbound external POST request with HMAC signatures
    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $signature,
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$workflow->id}/execute", $data);

    // 4. Assert HTTP status 202 (Accepted)
    $response->assertStatus(202)
             ->assertJson([
                 'message' => 'Workflow queued successfully!',
                 'workflow_id' => $workflow->id,
              ]);

    // 5. Assert job was pushed to queue with expected payload
    Queue::assertPushed(ExecuteWorkflowJob::class, function ($job) use ($workflow) {
        return $job->workflow->id === $workflow->id 
            && $job->payload['event'] === 'order.paid';
    });
});

it('rejects a webhook if the workflow is not active', function () {
    // Create workflow in draft mode
    $workflow = Workflow::create([
        'name' => 'Draft Workflow',
        'status' => 'draft',
    ]);

    $data = [
        'event' => 'test'
    ];
    $timestamp = time();
    $payload = json_encode($data);
    $secret = config('app.key');
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $signature,
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$workflow->id}/execute", $data);

    // Must return 422 preventing execution
    $response->assertStatus(422)
             ->assertJson([
                 'message' => 'This workflow is not active.'
             ]);
});
