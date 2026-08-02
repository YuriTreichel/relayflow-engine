import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
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
    const removeStep = (index) => {
        const newSteps = [...data.steps];
        newSteps.splice(index, 1);
        setData('steps', newSteps);
    };

    // Atualiza um campo de um passo específico
    const handleStepChange = (index, field, value) => {
        const newSteps = [...data.steps];
        newSteps[index][field] = value;
        setData('steps', newSteps);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('workflows.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Criar Novo Workflow</h2>}
        >
            <Head title="Criar Workflow" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">
                        
                        {/* Nome do Workflow */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Nome do Workflow</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ex: Automação de Boas-Vindas"
                                required
                            />
                            {errors.name && <span className="text-red-500 text-xs">{errors.name}</span>}
                        </div>

                        {/* Status */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Status Inicial</label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="active">Ativo</option>
                                <option value="draft">Rascunho (Inativo)</option>
                            </select>
                        </div>

                        <hr className="my-6 border-gray-200" />

                        {/* Seção dos Steps */}
                        <div>
                            <div className="flex justify-between items-center mb-4">
                                <h3 className="text-lg font-semibold text-gray-800">Etapas do Fluxo (Steps)</h3>
                                <button
                                    type="button"
                                    onClick={addStep}
                                    className="px-3 py-1.5 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700"
                                >
                                    + Adicionar Passo
                                </button>
                            </div>

                            {data.steps.length === 0 ? (
                                <p className="text-gray-400 text-sm text-center py-4 border-2 border-dashed border-gray-200 rounded-lg">
                                    Nenhum passo adicionado. Clique no botão acima para adicionar a primeira etapa.
                                </p>
                            ) : (
                                <div className="space-y-4">
                                    {data.steps.map((step, index) => (
                                        <div key={index} className="p-4 border rounded-lg bg-gray-50 relative space-y-3">
                                            <div className="flex justify-between items-center">
                                                <span className="font-semibold text-sm text-gray-600">Passo #{index + 1}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => removeStep(index)}
                                                    className="text-red-500 text-xs font-semibold hover:underline"
                                                >
                                                    Remover
                                                </button>
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="block text-xs font-medium text-gray-600">Nome da Etapa</label>
                                                    <input
                                                        type="text"
                                                        value={step.name}
                                                        onChange={(e) => handleStepChange(index, 'name', e.target.value)}
                                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                                        placeholder="Ex: Disparar Webhook CRM"
                                                        required
                                                    />
                                                </div>

                                                <div>
                                                    <label className="block text-xs font-medium text-gray-600">Tipo de Ação</label>
                                                    <select
                                                        value={step.type}
                                                        onChange={(e) => handleStepChange(index, 'type', e.target.value)}
                                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
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
                                className="px-6 py-2 bg-green-600 text-white font-semibold rounded-md shadow-sm hover:bg-green-700 disabled:opacity-50"
                            >
                                Salvar Workflow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}