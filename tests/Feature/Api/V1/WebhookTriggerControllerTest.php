<?php

use App\Jobs\ExecuteWorkflowJob;
use App\Models\Workflow;
use Illuminate\Support\Facades\Queue;

it('triggers an active workflow via webhook and dispatches job', function () {
    // 1. Simula a fila para interceptar a Job sem precisar rodar o RabbitMQ no teste
    Queue::fake();

    // 2. Cria um fluxo ativo no banco de dados SQLite de teste
    $workflow = Workflow::create([
        'name' => 'Fluxo de Venda Aprovada',
        'status' => 'active',
    ]);

    // 3. Simula uma requisição HTTP POST externa enviando um payload JSON
    $response = $this->postJson("/api/v1/workflows/{$workflow->id}/execute", [
        'event' => 'order.paid',
        'customer' => [
            'email' => 'cliente@email.com',
            'name' => 'João Silva'
        ]
    ]);

    // 4. Verifica se a resposta HTTP foi 202 (Accepted)
    $response->assertStatus(202)
             ->assertJson([
                 'message' => 'Workflow adicionado à fila com sucesso!',
                 'workflow_id' => $workflow->id,
             ]);

    // 5. Garante que a Job foi despachada para a fila com o workflow correto
    Queue::assertPushed(ExecuteWorkflowJob::class, function ($job) use ($workflow) {
        return $job->workflow->id === $workflow->id 
            && $job->payload['event'] === 'order.paid';
    });
});

it('rejects a webhook if the workflow is not active', function () {
    // Cria um fluxo que está em modo rascunho (draft)
    $workflow = Workflow::create([
        'name' => 'Fluxo Inativo',
        'status' => 'draft',
    ]);

    $response = $this->postJson("/api/v1/workflows/{$workflow->id}/execute", [
        'event' => 'test'
    ]);

    // Deve retornar erro 422 impedindo a execução
    $response->assertStatus(422);
});