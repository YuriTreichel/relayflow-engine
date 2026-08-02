import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";

interface Workflow {
    id: number;
    name: string;
    steps_count: number;
    status: 'active' | 'draft';
}

interface Stats {
    total_workflows: number;
    active_workflows: number;
    completed_runs: number;
    failed_runs: number;
}

interface Log {
    id: number;
    workflow_id: number;
    status: 'completed' | 'failed' | 'pending' | 'running';
    created_at: string;
    workflow?: {
        name: string;
    };
}

interface Props {
    auth: {
        user: {
            name: string;
            email: string;
        };
    };
    stats: Stats;
    workflows: Workflow[];
    recentLogs: Log[];
}

export default function Dashboard({ auth, stats, workflows, recentLogs }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="font-display font-semibold text-xl text-white leading-tight">
                    Visão Geral
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="space-y-8">
                {/* Header Section */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 className="font-display text-2xl font-bold text-white tracking-tight">
                            Seus Fluxos de Automação
                        </h3>
                        <p className="text-slate-400 text-sm mt-1">
                            Monitore a integridade e as execuções dos seus pipelines.
                        </p>
                    </div>
                    <Link
                        href={route("workflows.create")}
                        className="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-sm font-semibold rounded-lg transition-all duration-150 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 cursor-pointer"
                    >
                        + Novo Workflow
                    </Link>
                </div>

                {/* METRICS CARDS */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Card 1 */}
                    <div className="bg-slate-900 border border-slate-800 p-6 rounded-xl flex flex-col justify-between relative overflow-hidden group">
                        <div className="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Total de Workflows
                        </span>
                        <span className="mt-2 text-3xl font-display font-bold text-white">
                            {stats.total_workflows}
                        </span>
                    </div>

                    {/* Card 2 */}
                    <div className="bg-slate-900 border border-slate-800 p-6 rounded-xl flex flex-col justify-between relative overflow-hidden group">
                        <div className="absolute top-0 left-0 w-1 h-full bg-teal-500"></div>
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Workflows Ativos
                        </span>
                        <span className="mt-2 text-3xl font-display font-bold text-white">
                            {stats.active_workflows}
                        </span>
                    </div>

                    {/* Card 3 */}
                    <div className="bg-slate-900 border border-slate-800 p-6 rounded-xl flex flex-col justify-between relative overflow-hidden group">
                        <div className="absolute top-0 left-0 w-1 h-full bg-sky-500"></div>
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Execuções Concluídas
                        </span>
                        <span className="mt-2 text-3xl font-display font-bold text-white">
                            {stats.completed_runs}
                        </span>
                    </div>

                    {/* Card 4 */}
                    <div className="bg-slate-900 border border-slate-800 p-6 rounded-xl flex flex-col justify-between relative overflow-hidden group">
                        <div className="absolute top-0 left-0 w-1 h-full bg-rose-500"></div>
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Falhas / Erros
                        </span>
                        <span className="mt-2 text-3xl font-display font-bold text-white">
                            {stats.failed_runs}
                        </span>
                    </div>
                </div>

                {/* TABLES AND RECENTS */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Lista de Workflows */}
                    <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 lg:col-span-2">
                        <h3 className="font-display text-lg font-bold text-white mb-4">
                            Automações Cadastradas
                        </h3>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm text-slate-300">
                                <thead className="border-b border-slate-800 text-slate-400 uppercase text-xs tracking-wider">
                                    <tr>
                                        <th className="pb-3 pr-4 font-semibold">Nome</th>
                                        <th className="pb-3 px-4 font-semibold">Etapas</th>
                                        <th className="pb-3 pl-4 font-semibold text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/50">
                                    {workflows.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={3}
                                                className="py-8 text-center text-slate-500"
                                            >
                                                Nenhum workflow cadastrado.
                                            </td>
                                        </tr>
                                    ) : (
                                        workflows.map((wf) => (
                                            <tr key={wf.id} className="hover:bg-slate-800/20 transition-colors duration-150">
                                                <td className="py-4 pr-4 font-semibold text-white">
                                                    {wf.name}
                                                </td>
                                                <td className="py-4 px-4 text-slate-400">
                                                    {wf.steps_count} {wf.steps_count === 1 ? 'etapa' : 'etapas'}
                                                </td>
                                                <td className="py-4 pl-4 text-right">
                                                    <span
                                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                                            wf.status === "active"
                                                                ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                                                : "bg-slate-800 text-slate-400 border border-slate-700/50"
                                                        }`}
                                                    >
                                                        {wf.status === "active" ? "Ativo" : "Rascunho"}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Execuções Recentes */}
                    <div className="bg-slate-900 border border-slate-800 rounded-xl p-6">
                        <h3 className="font-display text-lg font-bold text-white mb-4">
                            Atividade Recente
                        </h3>
                        <div className="space-y-4 max-h-[28rem] overflow-y-auto pr-1">
                            {recentLogs.length === 0 ? (
                                <p className="text-slate-500 text-sm py-4 text-center">
                                    Nenhum histórico de execução.
                                </p>
                            ) : (
                                recentLogs.map((log) => (
                                    <div
                                        key={log.id}
                                        className="p-4 rounded-lg border border-slate-800/80 bg-slate-950/40 flex flex-col gap-3 hover:border-slate-700 transition-colors duration-150"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <p className="text-sm font-semibold text-white">
                                                    {log.workflow
                                                        ? log.workflow.name
                                                        : `Workflow #${log.workflow_id}`}
                                                </p>
                                                <p className="text-xs text-slate-500 mt-0.5">
                                                    Execução #{log.id} • {new Date(log.created_at).toLocaleString("pt-BR")}
                                                </p>
                                            </div>
                                            <span
                                                className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                                                    log.status === "completed"
                                                        ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                                        : log.status === "failed"
                                                            ? "bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                                            : "bg-amber-500/10 text-amber-400 border border-amber-500/20"
                                                }`}
                                            >
                                                {log.status}
                                            </span>
                                        </div>

                                        {/* Action Button displayed on failure */}
                                        {log.status === "failed" && (
                                            <button
                                                onClick={() =>
                                                    router.post(
                                                        route("logs.replay", log.id)
                                                    )
                                                }
                                                className="w-full mt-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-semibold rounded border border-slate-700 hover:border-slate-600 transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                                                title="Re-executar Payload"
                                            >
                                                <span>🔄</span> Re-executar Passo (Replay)
                                            </button>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
