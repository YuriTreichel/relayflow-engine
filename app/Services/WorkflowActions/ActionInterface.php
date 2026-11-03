<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;

interface ActionInterface
{
    /**
     * Executes the workflow step action.
     * 
     * @param WorkflowStep $step Step configuration.
     * @param array $payload Data received from previous step.
     * @return array Returns modified or enriched payload.
     */
    public function handle(WorkflowStep $step, array $payload): array;
}
