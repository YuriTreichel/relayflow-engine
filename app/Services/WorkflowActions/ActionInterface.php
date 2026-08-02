<?php

namespace App\Services\WorkflowActions;

use App\Models\WorkflowStep;

interface ActionInterface
{
    /**
     * Executa a ação do passo do workflow.
     * 
     * @param WorkflowStep $step Configurações do passo.
     * @param array $payload Dados recebidos do passo anterior.
     * @return array Retorna o payload modificado ou enriquecido.
     */
    public function handle(WorkflowStep $step, array $payload): array;
}