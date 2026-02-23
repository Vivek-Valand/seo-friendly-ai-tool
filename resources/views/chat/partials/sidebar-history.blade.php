@php
    $history = $history ?? \Illuminate\Support\Facades\DB::table('agent_conversations')
        ->where('user_id', 1)
        ->orderBy('updated_at', 'desc')
        ->get();
@endphp

@if ($history->count() > 0)
    @foreach ($history as $item)
        <div @click="$dispatch('load-chat', { id: '{{ $item->id }}' })"
            class="group cursor-pointer p-2 rounded-xl hover:bg-slate-800/30 border border-transparent hover:border-slate-700/30 transition-all relative flex items-center gap-3">
            <div class="flex items-center gap-3 flex-1 overflow-hidden" :class="!sidebarOpen && 'justify-center'">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center flex-shrink-0">
                    <span class="text-sm text-slate-500">#</span>
                </div>
                <div class="overflow-hidden" x-show="sidebarOpen" x-transition>
                    <p class="text-sm font-medium text-slate-300 truncate">{{ $item->title ?? 'Previous Chat' }}</p>
                    <p class="text-xs text-slate-500 truncate">
                        {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</p>
                </div>
            </div>

            <!-- Delete Button -->
            <button @click.stop="confirmDeleteId = '{{ $item->id }}'; showDeleteModal = true"
                class="p-2 text-slate-500 hover:text-rose-500 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity"
                x-show="sidebarOpen">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    @endforeach
@else
    <div x-show="sidebarOpen" class="p-4 text-center">
        <p class="text-xs text-slate-600 italic">No chat history yet</p>
    </div>
@endif
