import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-slate-950 px-4 py-8 font-sans">
            <div className="mb-8">
                <Link href="/" className="flex items-center gap-3">
                    <div className="size-10 rounded-lg bg-emerald-500 flex items-center justify-center font-display font-bold text-xl text-slate-950 shadow-lg shadow-emerald-500/20">
                        R
                    </div>
                    <span className="font-display font-bold text-2xl tracking-tight text-white">
                        RelayFlow<span className="text-emerald-500">.</span>
                    </span>
                </Link>
            </div>

            <div className="w-full sm:max-w-md bg-slate-900 border border-slate-800 p-8 rounded-xl shadow-xl text-slate-100">
                {children}
            </div>
        </div>
    );
}
