<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\WorkflowExecutorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de tentativas da Job.
     */
    public int $tries = 3;

    /**
     * Backoff exponencial: aguarda 5s na 1ª falha, 15s na 2ª.
     */
    public function backoff(): array
    {
        return [5, 15];
    }

    public function __construct(
        public Workflow $workflow,
        public array $payload
    ) {}

    public function handle(WorkflowExecutorService $executor): void
    {
        $log = $executor->execute($this->workflow, $this->payload);

        // Se a execução falhou no ExecutorService, lança uma exceção para o sistema de filas do Laravel contabilizar a tentativa
        if ($log->status === 'failed') {
            throw new \Exception("Falha ao processar Workflow #{$this->workflow->id}: {$log->error_message}");
        }
    }

    /**
     * Método chamado quando a Job estoura todas as tentativas (DLQ).
     */
    public function failed(Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::channel('single')->error(
            "CRÍTICO: Workflow #{$this->workflow->id} estourou todas as {$this->tries} tentativas e foi para a DLQ. Erro: {$exception->getMessage()}"
        );
    }
}