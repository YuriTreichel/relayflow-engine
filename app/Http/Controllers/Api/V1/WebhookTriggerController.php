<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ExecuteWorkflowJob;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookTriggerController extends Controller
{
    public function trigger(Request $request, Workflow $workflow): JsonResponse
    {
        // 1. Verify if workflow is active to receive triggers
        if ($workflow->status !== 'active') {
            return response()->json([
                'message' => 'This workflow is not active.',
            ], 422);
        }

        // 2. Extract JSON payload from incoming request body
        $payload = $request->all();

        // 3. Dispatch workflow execution to the message queue
        ExecuteWorkflowJob::dispatch($workflow, $payload);

        // 4. Return 202 (Accepted) response indicating successful queueing
        return response()->json([
            'message' => 'Workflow queued successfully!',
            'workflow_id' => $workflow->id,
        ], 202);
    }
}
