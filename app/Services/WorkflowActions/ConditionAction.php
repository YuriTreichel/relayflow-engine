<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;
use App\Services\VariableInterpolatorService;
use Exception;

class ConditionAction implements ActionInterface
{
    public function __construct(
        private VariableInterpolatorService $interpolator
    ) {}

    public function handle(WorkflowStep $step, array $payload): array
    {
        $config = $step->config ?? [];
        $field = $config['field'] ?? null;
        $operator = $config['operator'] ?? '==';
        
        // Permite usar {{ order.total }} no valor de comparação também
        $targetValue = $this->interpolator->interpolate($config['value'] ?? null, $payload);
        $currentValue = data_get($payload, $field);

        $passed = match ($operator) {
            '==' => $currentValue == $targetValue,
            '!=' => $currentValue != $targetValue,
            '>'  => $currentValue > $targetValue,
            '<'  => $currentValue < $targetValue,
            '>=' => $currentValue >= $targetValue,
            '<=' => $currentValue <= $targetValue,
            default => false,
        };

        if (!$passed) {
            throw new Exception("Condição interrompida no passo [{$step->name}]: {$field} ({$currentValue}) não atendeu ao critério {$operator} {$targetValue}.");
        }

        $payload['steps']['step_' . $step->id] = ['status' => 'passed'];

        return $payload;
    }
}