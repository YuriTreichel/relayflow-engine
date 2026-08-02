<?php

use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Queue::fake();
    RateLimiter::clear('workflow_execution:1');

    $this->workflow = Workflow::create([
        'name' => 'Fluxo com Rate Limit',
        'status' => 'active',
    ]);
});

it('allows up to 10 requests per minute and blocks the 11th with 429 Too Many Requests', function () {
    $timestamp = time();
    $data = ['event' => 'order.created'];
    $payload = json_encode($data);
    $secret = config('app.key');
    $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $headers = [
        'X-RelayFlow-Signature' => $signature,
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ];

    // Dispara 10 requisições seguidas (devem ser aceitas com 202)
    for ($i = 1; $i <= 10; $i++) {
        $response = $this->withHeaders($headers)->postJson("/api/v1/workflows/{$this->workflow->id}/execute", $data);
        $response->assertStatus(202);
    }

    // A 11ª requisição deve ser bloqueada pelo Rate Limiter
    $response11 = $this->withHeaders($headers)->postJson("/api/v1/workflows/{$this->workflow->id}/execute", $data);

    $response11->assertStatus(429)
               ->assertJsonStructure(['message', 'retry_after_seconds'])
               ->assertHeader('X-RateLimit-Remaining', '0');
});