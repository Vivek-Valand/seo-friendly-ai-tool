<div class="p-3 sm:p-6 space-y-6 sm:space-y-8 w-full" id="chat-messages">

    <!-- Empty State -->
    <div id="empty-state"
        class="{{ count($messages) > 0 ? 'hidden' : '' }} flex flex-col items-center justify-center h-full text-center space-y-6 opacity-80 py-20">
        <div
            class="w-20 h-20 rounded-3xl bg-indigo-600/10 flex items-center justify-center border border-indigo-500/20 shadow-2xl shadow-indigo-500/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="space-y-2">
            <h2 class="text-3xl font-bold text-white gradient-text leading-tight">Welcome to SEOFriendly</h2>
            <p class="text-slate-400 max-w-md mx-auto">Enter your website URL to begin a comprehensive SEO analysis and
                get ranking suggestions.</p>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="chat-messages-container"
        class="flex flex-col gap-6 sm:gap-10 px-2 sm:px-4 md:px-6 lg:px-10 py-2 overflow-x-hidden">
        @foreach ($messages as $msg)
            <div
                class="flex items-start gap-3 sm:gap-4 {{ $msg['role'] === 'user' ? 'flex-row-reverse' : '' }} message-container">
                <!-- Avatar -->
                <div
                    class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center text-xs font-bold shadow-lg border border-slate-700/50 {{ $msg['role'] === 'user' ? 'bg-slate-800 text-indigo-400' : 'bg-indigo-600 text-white' }} avatar-container">
                    <span class="avatar-text">{{ $msg['role'] === 'user' ? 'ME' : 'AI' }}</span>
                </div>

                <!-- Context Bubble -->
                <div
                    class="flex flex-col w-full max-w-full sm:max-w-[95%] md:max-w-[85%] min-w-0 message-wrapper {{ $msg['role'] === 'user' ? 'items-end' : 'items-start' }}">
                    <div
                        class="p-2.5 sm:p-4 rounded-2xl border transition-all duration-300 relative group/msg message-bubble w-fit {{ $msg['role'] === 'user' ? 'rounded-tr-none bg-indigo-600 text-white border-indigo-500 shadow-xl shadow-indigo-950/30' : 'rounded-tl-none bg-slate-800/80 backdrop-blur-sm text-slate-200 border-slate-700/50 shadow-lg' }}">
                        <div
                            class="prose prose-invert prose-sm sm:prose-base max-w-none break-words leading-relaxed text-[13px] sm:text-base message-content">
                            {!! $msg['content'] !!}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-[10px] text-slate-500 font-medium tracking-wider uppercase role-name">
                            {{ $msg['role'] === 'user' ? 'You' : 'SEOFriendly AI' }}
                        </span>

                        @if ($msg['role'] === 'assistant')
                            <button title="Copy Chat"
                                class="text-slate-500 hover:text-indigo-400 transition-colors copy-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden flex items-start gap-4 animate-pulse">
        <div class="w-8 h-8 rounded-lg bg-slate-800 flex-shrink-0"></div>
        <div class="p-4 rounded-2xl rounded-tl-none bg-slate-800 border border-slate-700/50 w-24">
            <div class="flex gap-1 justify-center">
                <div class="w-1.5 h-1.5 bg-slate-600 rounded-full animate-bounce"></div>
                <div class="w-1.5 h-1.5 bg-slate-600 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                <div class="w-1.5 h-1.5 bg-slate-600 rounded-full animate-bounce [animation-delay:0.4s]"></div>
            </div>
        </div>
    </div>

    <!-- Message Template (for jQuery) -->
    <template id="message-template">
        <div class="flex items-start gap-3 sm:gap-4 message-container">
            <div
                class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center text-xs font-bold shadow-lg border border-slate-700/50 avatar-container">
                <span class="avatar-text"></span>
            </div>
            <div class="flex flex-col w-full max-w-full sm:max-w-[95%] md:max-w-[85%] min-w-0 message-wrapper">
                <div
                    class="p-2.5 sm:p-4 rounded-2xl border transition-all duration-300 relative group/msg message-bubble w-fit">
                    <div
                        class="prose prose-invert prose-sm sm:prose-base max-w-none whitespace-pre-wrap break-words leading-relaxed text-[13px] sm:text-base message-content">
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="text-[10px] text-slate-500 font-medium tracking-wider uppercase role-name"></span>
                    <button title="Copy Chat" class="text-slate-500 hover:text-indigo-400 transition-colors copy-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
