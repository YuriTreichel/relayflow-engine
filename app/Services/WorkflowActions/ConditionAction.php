<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;
use Exception;

class ConditionAction implements ActionInterface
{
    public function handle(WorkflowStep $step, array $payload): array
    {
        // Exemplo de config: ['field' => 'amount', 'operator' => '>', 'value' => 100]
        $config = $step->config ?? [];
        $field = $config['field'] ?? null;
        $operator = $config['operator'] ?? '==';
        $targetValue = $config['value'] ?? null;

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
            throw new Exception("Condição interrompida no passo [{$step->name}]: {$field} ({$currentValue}) não é {$operator} {$targetValue}.");
        }

        $payload['conditions_passed']['step_' . $step->id] = true;

        return $payload;
    }
}