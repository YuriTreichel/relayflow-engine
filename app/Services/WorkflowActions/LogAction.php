<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;
use App\Services\VariableInterpolatorService;
use Illuminate\Support\Facades\Log;

class LogAction implements ActionInterface
{
    public function __construct(
        private VariableInterpolatorService $interpolator
    ) {}

    public function handle(WorkflowStep $step, array $payload): array
    {
        $config = $this->interpolator->interpolate($step->config ?? [], $payload);
        $message = $config['message'] ?? 'Executando passo de log';

        Log::info("Workflow Step [{$step->name}]: {$message}");

        $payload['steps']['step_' . $step->id] = [
            'status' => 'completed',
            'message' => $message,
        ];

        return $payload;
    }
}