<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateHmacSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if signature and timestamp headers exist
        $signature = $request->header('X-RelayFlow-Signature');
        $timestamp = $request->header('X-RelayFlow-Timestamp');

        if (!$signature || !$timestamp) {
            return response()->json([
                'message' => 'Access denied. Missing signature headers (X-RelayFlow-Signature / X-RelayFlow-Timestamp).'
            ], 401);
        }

        // 2. Replay attack protection (Reject requests older than 5 minutes)
        if (abs(time() - (int)$timestamp) > 300) {
            return response()->json([
                'message' => 'Request expired (Possible Replay Attack).'
            ], 403);
        }

        // 3. Retrieve secret key from configuration
        $secret = config('app.key');

        // 4. Recalculate HMAC SHA-256 hash using timestamp + request payload
        $payloadToSign = $timestamp . '.' . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payloadToSign, $secret);

        // 5. Constant-time comparison against timing attacks
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Invalid HMAC signature. Request payload may have been tampered with.'
            ], 401);
        }

        return $next($request);
    }
}
