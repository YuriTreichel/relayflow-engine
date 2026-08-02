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

        // Normaliza para obter a ID caso a rota injete a Model Workflow
        if (is_object($workflowId)) {
            $workflowId = $workflowId->id;
        }

        // Define uma chave única de rate limit por workflow (ex: workflow_execution:1)
        $key = 'workflow_execution:' . $workflowId;

        // Limite de 10 requisições por minuto por Workflow
        $maxAttempts = 10;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Limite de requisições excedido (Rate Limit). Tente novamente em breve.',
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