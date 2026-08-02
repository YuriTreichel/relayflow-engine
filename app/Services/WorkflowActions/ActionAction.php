<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;

class ActionAction implements ActionInterface
{
    public function handle(WorkflowStep $step, array $payload): array
    {
        $payload['processed_by_step_' . $step->id] = true;
        return $payload;
    }
}
