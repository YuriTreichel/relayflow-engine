<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use App\Models\ExecutionLog;
use App\Jobs\ExecuteWorkflowJob;

class WorkflowController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Workflows/Create');
    }

    public function replay(ExecutionLog $log): RedirectResponse
    {
        // Reenvia o payload original registrado na falha para a fila novamente
        ExecuteWorkflowJob::dispatch($log->workflow, $log->input_payload);

        return redirect()->back()->with('message', "Re-execução do Workflow #{$log->workflow_id} enviada para a fila!");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,draft',
            'steps' => 'nullable|array',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.type' => 'required|string|in:log,action,http_request,condition',
            'steps.*.config' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Cria o Workflow
            $workflow = Workflow::create([
                'name' => $validated['name'],
                'status' => $validated['status'],
            ]);

            // 2. Cria os Steps associados
            if (!empty($validated['steps'])) {
                foreach ($validated['steps'] as $index => $stepData) {
                    $workflow->steps()->create([
                        'name' => $stepData['name'],
                        'type' => $stepData['type'],
                        'config' => $stepData['config'] ?? [],
                        'order' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('dashboard')->with('message', 'Workflow criado com sucesso!');
    }
}
