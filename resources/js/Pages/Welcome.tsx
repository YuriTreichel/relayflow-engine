import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({
    auth,
    laravelVersion,
    phpVersion,
}: PageProps<{ laravelVersion: string; phpVersion: string }>) {
    return (
        <>
            <Head title="RelayFlow Engine - Real-Time Workflow Automation" />
            <div className="min-h-screen bg-slate-900 text-slate-100 font-sans selection:bg-emerald-500 selection:text-slate-900">
                {/* Header */}
                <header className="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center border-b border-slate-800">
                    <div className="flex items-center gap-2">
                        <div className="size-10 rounded-lg bg-emerald-500 flex items-center justify-center font-display font-bold text-xl text-slate-950 shadow-lg shadow-emerald-500/20">
                            R
                        </div>
                        <span className="font-display font-semibold text-lg tracking-tight text-white">
                            RelayFlow<span className="text-emerald-500">.</span>
                        </span>
                    </div>

                    <nav className="flex gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="px-4 py-2 text-sm font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-white transition-all duration-200 cursor-pointer"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors duration-200 cursor-pointer"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all duration-200 shadow-md shadow-emerald-500/10 cursor-pointer"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                {/* Hero Section */}
                <main className="max-w-7xl mx-auto px-6 py-20 lg:py-32 flex flex-col items-center text-center">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-8">
                        ⚡ New Release: DAG Workflow Branching
                    </div>

                    <h1 className="font-display font-bold text-5xl md:text-7xl text-white tracking-tight leading-none max-w-4xl">
                        Orchestrate workflows with ease and precision
                    </h1>

                    <p className="mt-6 text-lg md:text-xl text-slate-400 max-w-2xl leading-relaxed">
                        Design and manage high-performance Directed Acyclic Graph (DAG) integrations. Execute smart conditional branches, handle webhooks securely, and monitor real-time execution logs.
                    </p>

                    <div className="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="w-full sm:w-auto px-8 py-4 text-base font-semibold rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all duration-200 shadow-lg shadow-emerald-500/20 cursor-pointer"
                            >
                                Go to Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('register')}
                                    className="w-full sm:w-auto px-8 py-4 text-base font-semibold rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition-all duration-200 shadow-lg shadow-emerald-500/20 cursor-pointer"
                                >
                                    Get Started
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="w-full sm:w-auto px-8 py-4 text-base font-semibold rounded-lg border border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white transition-all duration-200 cursor-pointer"
                                >
                                    Sign In
                                </Link>
                            </>
                        )}
                    </div>

                    {/* Features Grid */}
                    <div className="mt-24 lg:mt-36 grid grid-cols-1 md:grid-cols-3 gap-8 w-full text-left">
                        {/* Feature 1 */}
                        <div className="p-8 rounded-2xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1">
                            <div className="size-12 rounded-xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 mb-6">
                                <svg className="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 className="font-display font-semibold text-xl text-white">DAG Executions</h3>
                            <p className="mt-3 text-slate-400 leading-relaxed text-sm">
                                Full support for directed acyclic graphs, enabling parallel conditionals and robust task chaining without circular dependencies.
                            </p>
                        </div>

                        {/* Feature 2 */}
                        <div className="p-8 rounded-2xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1">
                            <div className="size-12 rounded-xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 mb-6">
                                <svg className="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 className="font-display font-semibold text-xl text-white">Advanced Security</h3>
                            <p className="mt-3 text-slate-400 leading-relaxed text-sm">
                                Built-in HMAC signatures, real-time rate limiting, and replay attack protection to shield your webhook endpoints.
                            </p>
                        </div>

                        {/* Feature 3 */}
                        <div className="p-8 rounded-2xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-1">
                            <div className="size-12 rounded-xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center text-emerald-400 mb-6">
                                <svg className="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17m0 0V5" />
                                </svg>
                            </div>
                            <h3 className="font-display font-semibold text-xl text-white">Replay & Audit Logs</h3>
                            <p className="mt-3 text-slate-400 leading-relaxed text-sm">
                                Inspect full execution histories with granular step logs and one-click replay capabilities for failed payloads.
                            </p>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="max-w-7xl mx-auto px-6 py-16 text-center text-xs text-slate-500 border-t border-slate-800/50 mt-20">
                    RelayFlow Engine • Built with Laravel v{laravelVersion} (PHP v{phpVersion})
                </footer>
            </div>
        </>
    );
}
