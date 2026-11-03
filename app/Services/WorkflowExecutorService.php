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
                'error_message' => 'No steps configured for this workflow.',
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

                    // If succeeded:
                    if ($currentStep->next_step_id) {
                        $currentStep = $stepsById->get($currentStep->next_step_id);
                    } else {
                        // If no explicit next step, follow linear order
                        $currentStep = $stepsList->where('order', '>', $currentStep->order)->first();
                    }
                } catch (Throwable $stepException) {
                    // If failed but has failure branch (on_failure_step_id)
                    if ($currentStep->on_failure_step_id) {
                        $currentPayload['steps']['step_' . $currentStep->id] = [
                            'status' => 'failed_branch',
                            'error' => $stepException->getMessage(),
                        ];

                        // Route to failure step
                        $currentStep = $stepsById->get($currentStep->on_failure_step_id);
                    } else {
                        // Without failure handling, escalate exception
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
