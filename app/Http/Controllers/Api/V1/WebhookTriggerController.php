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
        // 1. Verifica se o fluxo está ativo para receber disparos
        if ($workflow->status !== 'active') {
            return response()->json([
                'message' => 'Este workflow não está ativo.',
            ], 422);
        }

        // 2. Extrai todo o JSON enviado no corpo da requisição
        $payload = $request->all();

        // 3. Joga o processamento do fluxo na fila do RabbitMQ
        ExecuteWorkflowJob::dispatch($workflow, $payload);

        // 4. Retorna resposta 202 (Accepted) dizendo que a requisição foi aceita para processamento
        return response()->json([
            'message' => 'Workflow adicionado à fila com sucesso!',
            'workflow_id' => $workflow->id,
        ], 202);
    }
}