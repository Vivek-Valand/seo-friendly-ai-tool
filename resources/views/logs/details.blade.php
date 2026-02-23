@extends('layouts.master')

@section('content')
    <div class="p-4 sm:p-6 h-full min-h-0 overflow-auto">
        <div class="max-w-5xl mx-auto w-full min-h-0 flex flex-col gap-4">
            <div class="glass rounded-xl p-4 sm:p-6 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold">Log Details</h1>
                    <p class="text-slate-400 text-sm mt-1">Environment and recent log tail.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 border border-white/10 transition"
                        onclick="window.location.reload()">
                        Refresh
                    </button>
                    <button type="button"
                        class="px-3 py-2 text-xs font-semibold rounded-lg bg-red-600/80 hover:bg-red-600 border border-white/10 transition"
                        onclick="clearLogs()">
                        Clear Logs
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Date / Time</div>
                    <div class="text-sm mt-1">{{ $now }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">App Env</div>
                    <div class="text-sm mt-1">{{ $appEnv }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">App URL</div>
                    <div class="text-sm mt-1 break-all">{{ $appUrl }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Host</div>
                    <div class="text-sm mt-1 break-all">{{ $host }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">PHP / Laravel</div>
                    <div class="text-sm mt-1">{{ $phpVersion }} / {{ $laravelVersion }}</div>
                </div>
                <div class="glass rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wide text-slate-400">Log File</div>
                    <div class="text-sm mt-1 break-all">{{ $logPath }}</div>
                    <div class="text-xs text-slate-400 mt-1">
                        {{ $logExists ? 'Exists' : 'Missing' }} · {{ number_format($logSize) }} bytes
                    </div>
                </div>
            </div>

            <div class="glass rounded-xl p-4 sm:p-6 flex flex-col min-h-0">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-medium">Recent Log Tail</div>
                    <div class="text-xs text-slate-400">Last 200KB</div>
                </div>
                <div class="mt-3 h-[60vh] max-w-full overflow-auto rounded-lg border border-white/10 bg-slate-950/60">
                    @if ($logContent)
                        <pre class="text-xs leading-relaxed p-4 whitespace-pre font-mono min-w-max">{{ $logContent }}</pre>
                    @else
                        <div class="p-4 text-sm text-slate-400">
                            No log content found. If you are on Railway, logs may be streamed only and not written
                            to storage. Trigger a request and check the Railway logs panel.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        async function clearLogs() {
            if (!confirm('Clear the log file? This cannot be undone.')) return;
            try {
                const response = await fetch("{{ route('logs.clear') }}?token={{ request()->query('token') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                if (!response.ok) throw new Error('Failed to clear logs');
                window.location.reload();
            } catch (error) {
                alert('Failed to clear logs. Check server logs for details.');
            }
        }
    </script>
@endsection
