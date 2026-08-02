<?php

namespace App\Services;

use App\Models\ExecutionLog;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowActions\ConditionAction;
use App\Services\WorkflowActions\HttpRequestAction;
use Illuminate\Support\Facades\Log;
use Throwable;

class WorkflowExecutorService
{
    public function execute(Workflow $workflow, array $payload = []): ExecutionLog
    {
        $executionLog = ExecutionLog::create([
            'workflow_id' => $workflow->id,
            'status' => 'processing',
            'input_payload' => $payload,
        ]);

        try {
            $steps = $workflow->steps()->orderBy('order')->get();
            $currentPayload = $payload;

            foreach ($steps as $step) {
                $executionLog->update([
                    'workflow_step_id' => $step->id,
                ]);

                $currentPayload = $this->executeStep($step, $currentPayload);
            }

            $executionLog->update([
                'status' => 'completed',
                'output_payload' => $currentPayload,
            ]);

        } catch (Throwable $e) {
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
        // Mapa de tipos de passos para suas respectivas classes de ação
        $actions = [
            'http_request' => HttpRequestAction::class,
            'condition'    => ConditionAction::class,
        ];

        // Se o tipo do passo estiver no nosso mapa, resolvemos via Container do Laravel
        if (array_key_exists($step->type, $actions)) {
            $actionInstance = app($actions[$step->type]);
            return $actionInstance->handle($step, $payload);
        }

        // Lógicas legadas/simples de fallback
        switch ($step->type) {
            case 'action':
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