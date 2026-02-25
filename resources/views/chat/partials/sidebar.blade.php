<!-- Mobile Menu Button -->
<div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="sidebar-toggle-mobile"
        class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white shadow-2xl transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
    </button>
</div>

<!-- Sidebar Backdrop (Mobile Only) -->
<div id="sidebar-backdrop"
    class="hidden lg:hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40 transition-opacity">
</div>

<div class="bg-slate-900 flex shrink-0 h-full">

    <aside id="main-sidebar"
        class="flex flex-col border-r border-slate-800 transition-all duration-300 ease-in-out bg-slate-900 fixed inset-y-0 left-0 z-50 lg:relative shrink-0 overflow-hidden w-80 sidebar-container">
        <!-- Header -->
        <div class="p-4 flex items-center justify-between border-b border-slate-800/50 min-h-[73px] bg-slate-900">
            <div class="flex items-center gap-3 overflow-hidden sidebar-header-logo">
                <div
                    class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-white whitespace-nowrap">SEOFriendly</h1>
            </div>

            <button id="sidebar-toggle"
                class="p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                <svg class="sidebar-icon-open w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <svg class="sidebar-icon-closed w-6 h-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- New Chat Button (Large) -->
        <div class="px-4 py-4 bg-slate-900 sidebar-new-chat-large">
            <button
                class="new-chat-btn w-full flex items-center justify-between p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 hover:border-indigo-500/50 hover:bg-slate-800 transition-all group relative overflow-hidden shadow-sm">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-600/10 flex items-center justify-center text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-slate-300 group-hover:text-white">New Chat</span>
                </div>
            </button>
        </div>

        <!-- New Chat Button (Collapsed) -->
        <div class="px-2 py-4 flex justify-center bg-slate-900 sidebar-new-chat-small hidden">
            <button
                class="new-chat-btn w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800/40 border border-slate-700/50 hover:border-indigo-500/50 hover:bg-slate-800 text-slate-400 hover:text-white transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <!-- Chat List (Dynamic History) -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden p-1 space-y-2 bg-slate-900" id="chat-history-list">
            @include('chat.partials.sidebar-history')
        </div>

    </aside>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div id="delete-modal-backdrop-inner" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
        <div
            class="glass relative w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-800 transition-all duration-300 transform scale-100 opacity-100">
            <div class="text-center space-y-4">
                <div class="w-16 h-16 bg-rose-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-rose-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Delete Chat</h3>
                <p class="text-slate-400 text-sm">This will permanently remove this conversation history. This action
                    cannot be undone.</p>
                <div class="flex gap-3 pt-4">
                    <button id="close-delete-modal"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-medium hover:bg-slate-700 transition-colors border border-slate-700">Cancel</button>
                    <button id="confirm-delete-btn"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 text-white font-medium hover:bg-rose-500 transition-colors shadow-lg shadow-rose-500/20">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
