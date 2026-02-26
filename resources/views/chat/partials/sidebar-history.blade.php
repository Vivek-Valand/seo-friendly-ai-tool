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
                <div class="overflow-hidden sidebar-item-text flex-1">
                    <p class="text-[13px] font-medium text-slate-200 truncate leading-tight chat-title-text">
                        {{ $item->title ?? 'Previous Chat' }}</p>
                    <input type="text"
                        class="chat-title-input hidden bg-slate-800 text-slate-200 text-[13px] font-medium rounded px-1 py-0.5 w-full border border-indigo-500/50 focus:outline-none focus:border-indigo-500"
                        value="{{ $item->title ?? 'Previous Chat' }}" data-id="{{ $item->id }}">
                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</p>
                </div>
            </div>

            <!-- Context Menu Button -->
            <div class="relative flex-shrink-0 sidebar-item-delete">
                <button
                    class="chat-context-menu-btn p-1.5 text-slate-500 hover:text-slate-300 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-all rounded-lg hover:bg-slate-700/50"
                    data-id="{{ $item->id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    class="chat-context-menu hidden absolute right-0 mt-2 w-36 bg-slate-800 border border-slate-700/50 rounded-xl shadow-2xl z-[60] overflow-hidden backdrop-blur-xl">
                    <button
                        class="rename-chat-btn w-full text-left px-3.5 py-2.5 text-xs font-medium text-slate-300 hover:bg-slate-700/50 hover:text-white flex items-center gap-2.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Rename
                    </button>
                    <button
                        class="delete-chat-item w-full text-left px-3.5 py-2.5 text-xs font-medium text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 flex items-center gap-2.5 transition-colors border-t border-slate-700/30"
                        data-id="{{ $item->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="p-4 text-center sidebar-no-history">
        <p class="text-xs text-slate-600 italic">No chat history yet</p>
    </div>
@endif
