<?php

use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->workflow = Workflow::create([
        'name' => 'Secure Workflow',
        'status' => 'active',
    ]);
});

it('rejects requests without HMAC headers', function () {
    $response = $this->postJson("/api/v1/workflows/{$this->workflow->id}/execute", [
        'event' => 'user.created',
    ]);

    $response->assertStatus(401)
             ->assertJson(['message' => 'Access denied. Missing signature headers (X-RelayFlow-Signature / X-RelayFlow-Timestamp).']);
});

it('rejects requests with expired timestamp (replay attack protection)', function () {
    $expiredTimestamp = time() - 400; // 400 seconds in the past (> 5 minutes)
    $payload = json_encode(['event' => 'user.created']);
    $secret = config('app.key');
    
    $signature = hash_hmac('sha256', $expiredTimestamp . '.' . $payload, $secret);

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $signature,
        'X-RelayFlow-Timestamp' => (string) $expiredTimestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", ['event' => 'user.created']);

    $response->assertStatus(403)
             ->assertJson(['message' => 'Request expired (Possible Replay Attack).']);
});

it('rejects requests with invalid signature', function () {
    $timestamp = time();

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => 'fake_signature_12345',
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", ['event' => 'user.created']);

    $response->assertStatus(401)
             ->assertJson(['message' => 'Invalid HMAC signature. Request payload may have been tampered with.']);
});

it('allows requests with valid HMAC signature and timestamp', function () {
    $timestamp = time();
    $data = ['event' => 'user.created'];
    $payload = json_encode($data);
    $secret = config('app.key');

    // Generate valid HMAC using system app key
    $validSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $validSignature,
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", $data);

    $response->assertStatus(202)
             ->assertJson(['message' => 'Workflow queued successfully!']);
});
