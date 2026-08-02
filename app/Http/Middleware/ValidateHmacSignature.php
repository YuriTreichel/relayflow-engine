<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateHmacSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verifica se o header de assinatura existe
        $signature = $request->header('X-RelayFlow-Signature');
        $timestamp = $request->header('X-RelayFlow-Timestamp');

        if (!$signature || !$timestamp) {
            return response()->json([
                'message' => 'Acesso negado. Headers de assinatura ausentes (X-RelayFlow-Signature / X-RelayFlow-Timestamp).'
            ], 401);
        }

        // 2. Proteção contra Replay Attack (Rejeita requisições com mais de 5 minutos de diferença)
        if (abs(time() - (int)$timestamp) > 300) {
            return response()->json([
                'message' => 'Requisição expirada (Possível Replay Attack).'
            ], 403);
        }

        // 3. Recupera a chave secreta (usando o APP_KEY do .env para este exemplo)
        $secret = config('app.key');

        // 4. Recalcula o HASH com base no Payload + Timestamp
        $payloadToSign = $timestamp . '.' . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payloadToSign, $secret);

        // 5. Comparação segura contra Timing Attacks
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Assinatura HMAC inválida. A requisição pode ter sido adulterada.'
            ], 401);
        }

        return $next($request);
    }
}