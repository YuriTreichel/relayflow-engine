<?php

namespace App\Services\WorkflowActions;

use Exception;

class ActionFactory
{
    public static function make(string $type): ActionInterface
    {
        return match ($type) {
            'http_request' => app(HttpRequestAction::class),
            'condition'    => app(ConditionAction::class),
            'log'          => app(LogAction::class),
            'action'       => app(ActionAction::class),
            default        => throw new Exception("Tipo de ação não suportado: {$type}"),
        };
    }
}