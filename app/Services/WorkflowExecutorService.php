<?php

namespace App\Services;

use App\Models\ExecutionLog;
use App\Models\Workflow;
use App\Services\WorkflowActions\ActionFactory;
use Throwable;

class WorkflowExecutorService
{
    public function execute(Workflow $workflow, array $payload): ExecutionLog
    {
        $log = ExecutionLog::create([
            'workflow_id' => $workflow->id,
            'input_payload' => $payload,
            'status' => 'pending',
            'output_payload' => [],
        ]);

        $stepsList = $workflow->steps()->orderBy('order', 'asc')->get();

        if ($stepsList->isEmpty()) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Nenhum passo cadastrado para este workflow.',
            ]);
            return $log;
        }

        $stepsById = $stepsList->keyBy('id');
        $currentStep = $stepsList->first();
        $currentPayload = $payload;
        $visitedSteps = [];

        try {
            while ($currentStep) {
                if (in_array($currentStep->id, $visitedSteps)) {
                    break;
                }
                $visitedSteps[] = $currentStep->id;

                $action = ActionFactory::make($currentStep->type);

                try {
                    $currentPayload = $action->handle($currentStep, $currentPayload);

                    // Se passou com sucesso:
                    if ($currentStep->next_step_id) {
                        $currentStep = $stepsById->get($currentStep->next_step_id);
                    } else {
                        // Se não tem próximo passo explícito, segue a ordem linear apenas se não for um desvio final
                        $currentStep = $stepsList->where('order', '>', $currentStep->order)->first();
                    }
                } catch (Throwable $stepException) {
                    // Se falhou mas tem bifurcação de falha (on_failure_step_id)
                    if ($currentStep->on_failure_step_id) {
                        // AQUI ESTÁ O SEGREDO: Gravamos explicitamente o status 'failed_branch' e o erro
                        $currentPayload['steps']['step_' . $currentStep->id] = [
                            'status' => 'failed_branch',
                            'error' => $stepException->getMessage(),
                        ];

                        // Redireciona para o passo de falha
                        $currentStep = $stepsById->get($currentStep->on_failure_step_id);
                    } else {
                        // Sem tratamento de falha, estoura a exceção para falhar o workflow
                        throw $stepException;
                    }
                }
            }

            $log->update([
                'status' => 'completed',
                'output_payload' => $currentPayload,
            ]);

        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'output_payload' => $currentPayload,
            ]);
        }

        return $log;
    }
}