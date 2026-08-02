import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';

interface Step {
    name: string;
    type: 'log' | 'http_request' | 'condition' | 'action';
    config: Record<string, any>;
}

interface FormState {
    name: string;
    status: 'active' | 'draft';
    steps: Step[];
}

export default function Create({ auth }: { auth: any }) {
    const { data, setData, post, processing, errors } = useForm<FormState>({
        name: '',
        status: 'active',
        steps: [],
    });

    // Função para adicionar um novo passo ao formulário
    const addStep = () => {
        setData('steps', [
            ...data.steps,
            { name: '', type: 'log', config: {} }
        ]);
    };

    // Função para remover um passo
    const removeStep = (index: number) => {
        const newSteps = [...data.steps];
        newSteps.splice(index, 1);
        setData('steps', newSteps);
    };

    // Atualiza um campo de um passo específico
    const handleStepChange = (index: number, field: keyof Step, value: any) => {
        const newSteps = [...data.steps];
        newSteps[index] = {
            ...newSteps[index],
            [field]: value
        };
        setData('steps', newSteps);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('workflows.store'));
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-display font-semibold text-xl text-white leading-tight">Criar Novo Workflow</h2>}
        >
            <Head title="Criar Workflow" />

            <div className="max-w-4xl mx-auto">
                <form onSubmit={handleSubmit} className="bg-slate-900 border border-slate-800 p-8 rounded-xl space-y-8">
                    
                    <div className="space-y-6">
                        {/* Nome do Workflow */}
                        <div>
                            <label className="block text-sm font-semibold text-slate-300">Nome do Workflow</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white placeholder-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 px-4 transition-colors duration-150"
                                placeholder="Ex: Automação de Leads ou Sincronização de Pedidos"
                                required
                            />
                            {errors.name && <span className="text-rose-400 text-xs mt-1 block">{errors.name}</span>}
                        </div>

                        {/* Status */}
                        <div>
                            <label className="block text-sm font-semibold text-slate-300">Status Inicial</label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value as 'active' | 'draft')}
                                className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 px-4 transition-colors duration-150 cursor-pointer"
                            >
                                <option value="active">Ativo (Pronto para receber execuções)</option>
                                <option value="draft">Rascunho (Inativo)</option>
                            </select>
                        </div>
                    </div>

                    <hr className="border-slate-800" />

                    {/* Seção dos Steps */}
                    <div className="space-y-4">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="font-display text-lg font-bold text-white">Etapas do Fluxo (Steps)</h3>
                                <p className="text-xs text-slate-400 mt-0.5">Defina as ações que serão disparadas e condicionais do fluxo.</p>
                            </div>
                            <button
                                type="button"
                                onClick={addStep}
                                className="px-4 py-2 border border-slate-800 bg-slate-950 hover:bg-slate-850 hover:border-slate-700 text-slate-300 rounded-lg text-sm font-semibold transition-colors duration-150 cursor-pointer"
                            >
                                + Adicionar Etapa
                            </button>
                        </div>

                        {data.steps.length === 0 ? (
                            <div className="text-center py-10 border-2 border-dashed border-slate-800 rounded-lg bg-slate-950/20">
                                <p className="text-slate-500 text-sm">
                                    Nenhuma etapa definida para este fluxo. Adicione etapas para automatizar suas integrações.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {data.steps.map((step, index) => (
                                    <div key={index} className="p-5 border border-slate-800 rounded-xl bg-slate-950/40 relative space-y-4 hover:border-slate-700 transition duration-150">
                                        <div className="flex justify-between items-center">
                                            <span className="font-display font-semibold text-sm text-slate-400">Etapa #{index + 1}</span>
                                            <button
                                                type="button"
                                                onClick={() => removeStep(index)}
                                                className="text-rose-400 hover:text-rose-300 text-xs font-semibold flex items-center gap-1 cursor-pointer transition"
                                            >
                                                Remover
                                            </button>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-400">Nome da Etapa</label>
                                                <input
                                                    type="text"
                                                    value={step.name}
                                                    onChange={(e) => handleStepChange(index, 'name', e.target.value)}
                                                    className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white placeholder-slate-700 focus:border-emerald-500 focus:ring-emerald-500 text-xs py-2 px-3 transition-colors duration-150"
                                                    placeholder="Ex: Disparar Webhook CRM"
                                                    required
                                                />
                                            </div>

                                            <div>
                                                <label className="block text-xs font-semibold text-slate-400">Tipo de Ação</label>
                                                <select
                                                    value={step.type}
                                                    onChange={(e) => handleStepChange(index, 'type', e.target.value as any)}
                                                    className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-emerald-500 text-xs py-2 px-3 transition-colors duration-150 cursor-pointer"
                                                >
                                                    <option value="log">Log Simples</option>
                                                    <option value="http_request">Requisição HTTP (Webhook)</option>
                                                    <option value="condition">Condicional (Filtro)</option>
                                                    <option value="action">Ação Genérica</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="flex justify-end pt-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold rounded-lg shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 disabled:opacity-50 transition-colors duration-150 cursor-pointer"
                        >
                            Salvar Workflow
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}