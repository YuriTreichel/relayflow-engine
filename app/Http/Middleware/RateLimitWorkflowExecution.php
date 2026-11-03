<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitWorkflowExecution
{
    public function handle(Request $request, Closure $next): Response
    {
        $workflowId = $request->route('workflow');

        // Normalize ID if route model binding injected Workflow instance
        if (is_object($workflowId)) {
            $workflowId = $workflowId->id;
        }

        // Unique rate limiting key per workflow (e.g., workflow_execution:1)
        $key = 'workflow_execution:' . $workflowId;

        // Max 10 requests per minute per workflow
        $maxAttempts = 10;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests (Rate limit exceeded). Please try again soon.',
                'retry_after_seconds' => $seconds,
            ], 429, [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
        ]);
    }
}
