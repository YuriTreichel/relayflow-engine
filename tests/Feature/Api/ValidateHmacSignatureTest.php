<?php

use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->workflow = Workflow::create([
        'name' => 'Fluxo Seguro',
        'status' => 'active',
    ]);
});

it('rejects requests without HMAC headers', function () {
    $response = $this->postJson("/api/v1/workflows/{$this->workflow->id}/execute", [
        'event' => 'user.created',
    ]);

    $response->assertStatus(401)
             ->assertJson(['message' => 'Acesso negado. Headers de assinatura ausentes (X-RelayFlow-Signature / X-RelayFlow-Timestamp).']);
});

it('rejects requests with expired timestamp (replay attack protection)', function () {
    $expiredTimestamp = time() - 400; // 400 segundos no passado (> 5 minutos)
    $payload = json_encode(['event' => 'user.created']);
    $secret = config('app.key');
    
    $signature = hash_hmac('sha256', $expiredTimestamp . '.' . $payload, $secret);

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $signature,
        'X-RelayFlow-Timestamp' => (string) $expiredTimestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", ['event' => 'user.created']);

    $response->assertStatus(403)
             ->assertJson(['message' => 'Requisição expirada (Possível Replay Attack).']);
});

it('rejects requests with invalid signature', function () {
    $timestamp = time();

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => 'assinatura_falsa_12345',
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", ['event' => 'user.created']);

    $response->assertStatus(401)
             ->assertJson(['message' => 'Assinatura HMAC inválida. A requisição pode ter sido adulterada.']);
});

it('allows requests with valid HMAC signature and timestamp', function () {
    $timestamp = time();
    $data = ['event' => 'user.created'];
    $payload = json_encode($data);
    $secret = config('app.key');

    // Gera o HASH exato com a mesma chave do sistema
    $validSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $response = $this->withHeaders([
        'X-RelayFlow-Signature' => $validSignature,
        'X-RelayFlow-Timestamp' => (string) $timestamp,
    ])->postJson("/api/v1/workflows/{$this->workflow->id}/execute", $data);

    $response->assertStatus(202)
             ->assertJson(['message' => 'Workflow adicionado à fila com sucesso!']);
});