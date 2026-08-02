<?php

use App\Http\Controllers\Api\V1\WebhookTriggerController;
use App\Http\Middleware\ValidateHmacSignature;
use Illuminate\Support\Facades\Route;

Route::post('/v1/workflows/{workflow}/execute', [WebhookTriggerController::class, 'trigger'])
    ->middleware(ValidateHmacSignature::class);