<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\WorkflowExecutorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum retry attempts for this job.
     */
    public int $tries = 3;

    /**
     * Exponential backoff: waits 5s on 1st failure, 15s on 2nd failure.
     */
    public function backoff(): array
    {
        return [5, 15];
    }

    public function __construct(
        public Workflow $workflow,
        public array $payload
    ) {}

    public function handle(WorkflowExecutorService $executor): void
    {
        $log = $executor->execute($this->workflow, $this->payload);

        // If execution failed in ExecutorService, throw exception so Laravel queue system records attempt
        if ($log->status === 'failed') {
            throw new \Exception("Failed to process Workflow #{$this->workflow->id}: {$log->error_message}");
        }
    }

    /**
     * Called when the job exhausts all retries (routed to DLQ).
     */
    public function failed(Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::channel('single')->error(
            "CRITICAL: Workflow #{$this->workflow->id} exhausted all {$this->tries} attempts and was sent to DLQ. Error: {$exception->getMessage()}"
        );
    }
}
