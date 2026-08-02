<?php

namespace App\Services;

use App\Models\ExecutionLog;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorkflowExecutorService
{
    public function execute(Workflow $workflow, array $payload = []): ExecutionLog
    {
        // 1. Cria o log inicial de execução
        $executionLog = ExecutionLog::create([
            'workflow_id' => $workflow->id,
            'status' => 'processing',
            'input_payload' => $payload,
        ]);

        try {
            // 2. Busca todos os passos do fluxo ordenados
            $steps = $workflow->steps;

            $currentPayload = $payload;

            foreach ($steps as $step) {
                // Atualiza o log informando qual passo está rodando
                $executionLog->update([
                    'workflow_step_id' => $step->id,
                ]);

                // Executa a lógica do passo específico
                $currentPayload = $this->executeStep($step, $currentPayload);
            }

            // 3. Marca como concluído com sucesso
            $executionLog->update([
                'status' => 'completed',
                'output_payload' => $currentPayload,
            ]);

        } catch (Throwable $e) {
            // 4. Em caso de erro, grava no log a falha
            Log::error("Erro na execução do Workflow #{$workflow->id}: " . $e->getMessage());

            $executionLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $executionLog;
    }

    private function executeStep(WorkflowStep $step, array $payload): array
    {
        // Simulador simples de execução baseada no tipo de passo
        switch ($step->type) {
            case 'action':
                // Exemplo: Simula processamento de dados do payload
                $payload['processed_by_step_' . $step->id] = true;
                break;

            case 'log':
                Log::info("Passo [{$step->name}] executado no fluxo com payload: ", $payload);
                break;

            default:
                Log::warning("Tipo de passo desconhecido: {$step->type}");
                break;
        }

        return $payload;
    }
}