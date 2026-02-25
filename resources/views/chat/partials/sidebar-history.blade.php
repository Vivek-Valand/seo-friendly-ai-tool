@php
    $history =
        $history ??
        \Illuminate\Support\Facades\DB::table('agent_conversations')
            ->where('user_id', 1)
            ->orderBy('updated_at', 'desc')
            ->get();
@endphp

@if ($history->count() > 0)
    @foreach ($history as $item)
        <div class="load-chat-item group cursor-pointer p-2.5 rounded-xl transition-all duration-200 relative flex items-center gap-3 {{ isset($conversationId) && $conversationId == $item->id ? 'bg-slate-800 shadow-lg' : 'hover:bg-slate-800/40' }}"
            data-id="{{ $item->id }}">
            <div class="flex items-center gap-3 flex-1 overflow-hidden sidebar-item-content">
                <div
                    class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center flex-shrink-0 sidebar-item-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="overflow-hidden sidebar-item-text">
                    <p class="text-[13px] font-medium text-slate-200 truncate leading-tight">
                        {{ $item->title ?? 'Previous Chat' }}</p>
                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</p>
                </div>
            </div>

            <!-- Delete Button -->
            <button
                class="delete-chat-item p-2 text-slate-500 hover:text-rose-500 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity sidebar-item-delete"
                data-id="{{ $item->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    @endforeach
@else
    <div class="p-4 text-center sidebar-no-history">
        <p class="text-xs text-slate-600 italic">No chat history yet</p>
    </div>
@endif
