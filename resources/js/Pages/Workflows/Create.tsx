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

    // Add a new step to the form
    const addStep = () => {
        setData('steps', [
            ...data.steps,
            { name: '', type: 'log', config: {} }
        ]);
    };

    // Remove a step by index
    const removeStep = (index: number) => {
        const newSteps = [...data.steps];
        newSteps.splice(index, 1);
        setData('steps', newSteps);
    };

    // Update specific step field
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
            header={<h2 className="font-display font-semibold text-xl text-white leading-tight">Create New Workflow</h2>}
        >
            <Head title="Create Workflow" />

            <div className="max-w-4xl mx-auto">
                <form onSubmit={handleSubmit} className="bg-slate-900 border border-slate-800 p-8 rounded-xl space-y-8">
                    
                    <div className="space-y-6">
                        {/* Workflow Name */}
                        <div>
                            <label className="block text-sm font-semibold text-slate-300">Workflow Name</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white placeholder-slate-600 focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 px-4 transition-colors duration-150"
                                placeholder="e.g. Lead Automation or Order Sync"
                                required
                            />
                            {errors.name && <span className="text-rose-400 text-xs mt-1 block">{errors.name}</span>}
                        </div>

                        {/* Status */}
                        <div>
                            <label className="block text-sm font-semibold text-slate-300">Initial Status</label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value as 'active' | 'draft')}
                                className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 px-4 transition-colors duration-150 cursor-pointer"
                            >
                                <option value="active">Active (Ready for executions)</option>
                                <option value="draft">Draft (Inactive)</option>
                            </select>
                        </div>
                    </div>

                    <hr className="border-slate-800" />

                    {/* Steps Section */}
                    <div className="space-y-4">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="font-display text-lg font-bold text-white">Workflow Steps</h3>
                                <p className="text-xs text-slate-400 mt-0.5">Define trigger actions and conditional branches.</p>
                            </div>
                            <button
                                type="button"
                                onClick={addStep}
                                className="px-4 py-2 border border-slate-800 bg-slate-950 hover:bg-slate-850 hover:border-slate-700 text-slate-300 rounded-lg text-sm font-semibold transition-colors duration-150 cursor-pointer"
                            >
                                + Add Step
                            </button>
                        </div>

                        {data.steps.length === 0 ? (
                            <div className="text-center py-10 border-2 border-dashed border-slate-800 rounded-lg bg-slate-950/20">
                                <p className="text-slate-500 text-sm">
                                    No steps defined yet. Add steps to automate your integrations.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {data.steps.map((step, index) => (
                                    <div key={index} className="p-5 border border-slate-800 rounded-xl bg-slate-950/40 relative space-y-4 hover:border-slate-700 transition duration-150">
                                        <div className="flex justify-between items-center">
                                            <span className="font-display font-semibold text-sm text-slate-400">Step #{index + 1}</span>
                                            <button
                                                type="button"
                                                onClick={() => removeStep(index)}
                                                className="text-rose-400 hover:text-rose-300 text-xs font-semibold flex items-center gap-1 cursor-pointer transition"
                                            >
                                                Remove
                                            </button>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-400">Step Name</label>
                                                <input
                                                    type="text"
                                                    value={step.name}
                                                    onChange={(e) => handleStepChange(index, 'name', e.target.value)}
                                                    className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white placeholder-slate-700 focus:border-emerald-500 focus:ring-emerald-500 text-xs py-2 px-3 transition-colors duration-150"
                                                    placeholder="e.g. Dispatch CRM Webhook"
                                                    required
                                                />
                                            </div>

                                            <div>
                                                <label className="block text-xs font-semibold text-slate-400">Action Type</label>
                                                <select
                                                    value={step.type}
                                                    onChange={(e) => handleStepChange(index, 'type', e.target.value as any)}
                                                    className="mt-2 block w-full rounded-lg bg-slate-950 border border-slate-800 text-white focus:border-emerald-500 focus:ring-emerald-500 text-xs py-2 px-3 transition-colors duration-150 cursor-pointer"
                                                >
                                                    <option value="log">Simple Log</option>
                                                    <option value="http_request">HTTP Request (Webhook)</option>
                                                    <option value="condition">Conditional (Filter)</option>
                                                    <option value="action">Generic Action</option>
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
                            Save Workflow
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
