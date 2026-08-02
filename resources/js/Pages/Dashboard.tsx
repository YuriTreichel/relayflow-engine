import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";

export default function Dashboard({ auth, stats, workflows, recentLogs }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard - RelayFlow Engine
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-semibold text-gray-800">
                            Seus Fluxos de Automação
                        </h3>
                        <Link
                            href={route("workflows.create")}
                            className="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700"
                        >
                            + Novo Workflow
                        </Link>
                    </div>
                    {/* CARDS DE MÉTRICAS */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                            <div className="text-sm font-medium text-gray-500">
                                Total de Workflows
                            </div>
                            <div className="mt-1 text-3xl font-semibold text-gray-900">
                                {stats.total_workflows}
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                            <div className="text-sm font-medium text-gray-500">
                                Workflows Ativos
                            </div>
                            <div className="mt-1 text-3xl font-semibold text-gray-900">
                                {stats.active_workflows}
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                            <div className="text-sm font-medium text-gray-500">
                                Execuções Concluídas
                            </div>
                            <div className="mt-1 text-3xl font-semibold text-gray-900">
                                {stats.completed_runs}
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                            <div className="text-sm font-medium text-gray-500">
                                Falhas / Erros
                            </div>
                            <div className="mt-1 text-3xl font-semibold text-gray-900">
                                {stats.failed_runs}
                            </div>
                        </div>
                    </div>

                    {/* TABELA DE WORKFLOWS E FEED DE EXECUÇÕES */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Lista de Workflows */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                Seus Fluxos de Automação
                            </h3>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm text-gray-600">
                                    <thead className="bg-gray-50 text-gray-700 uppercase text-xs">
                                        <tr>
                                            <th className="py-3 px-4">Nome</th>
                                            <th className="py-3 px-4">
                                                Passos
                                            </th>
                                            <th className="py-3 px-4">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {workflows.length === 0 ? (
                                            <tr>
                                                <td
                                                    colSpan="3"
                                                    className="py-4 text-center text-gray-400"
                                                >
                                                    Nenhum workflow cadastrado.
                                                </td>
                                            </tr>
                                        ) : (
                                            workflows.map((wf) => (
                                                <tr key={wf.id}>
                                                    <td className="py-3 px-4 font-medium text-gray-900">
                                                        {wf.name}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {wf.steps_count} etapas
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <span
                                                            className={`px-2 py-1 rounded text-xs font-semibold ${
                                                                wf.status ===
                                                                "active"
                                                                    ? "bg-green-100 text-green-700"
                                                                    : "bg-gray-100 text-gray-600"
                                                            }`}
                                                        >
                                                            {wf.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {/* Execuções Recentes (Logs) */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                Execuções Recentes
                            </h3>
                            <div className="space-y-3 max-h-96 overflow-y-auto">
                                {recentLogs.length === 0 ? (
                                    <p className="text-gray-400 text-sm">
                                        Nenhum histórico de execução até o
                                        momento.
                                    </p>
                                ) : (
                                    recentLogs.map((log) => (
                                        <div
                                            key={log.id}
                                            className="p-3 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-between"
                                        >
                                            <div>
                                                <p className="text-sm font-medium text-gray-800">
                                                    {log.workflow
                                                        ? log.workflow.name
                                                        : `Workflow #${log.workflow_id}`}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    ID da Execução: #{log.id} •{" "}
                                                    {new Date(
                                                        log.created_at,
                                                    ).toLocaleString("pt-BR")}
                                                </p>
                                            </div>
                                            <div>
                                                <span
                                                    className={`px-2 py-1 rounded-full text-xs font-semibold ${
                                                        log.status ===
                                                        "completed"
                                                            ? "bg-green-100 text-green-700"
                                                            : log.status ===
                                                                "failed"
                                                              ? "bg-red-100 text-red-700"
                                                              : "bg-yellow-100 text-yellow-700"
                                                    }`}
                                                >
                                                    {log.status}
                                                </span>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
