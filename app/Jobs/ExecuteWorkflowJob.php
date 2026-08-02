<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\WorkflowExecutorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Workflow $workflow,
        public array $payload = []
    ) {}

    public function handle(WorkflowExecutorService $executor): void
    {
        $executor->execute($this->workflow, $this->payload);
    }
}