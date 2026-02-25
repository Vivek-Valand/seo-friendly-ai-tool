<div class="p-4 sm:p-6 bg-gradient-to-t to-transparent">
    <div class="w-full mx-auto relative group">
        <!-- Input Wrapper -->
        <form id="chat-form"
            class="relative flex items-end glass rounded-[24px] overflow-hidden shadow-2xl transition-all duration-300 border border-slate-800 focus-within:border-slate-700/60 focus-within:ring-2 focus-within:ring-indigo-500/20 bg-slate-900/60">

            <!-- Textarea -->
            <textarea id="chat-input" rows="1" placeholder="Enter website URL or ask an SEO question..."
                data-placeholder-desktop="Enter website URL or ask an SEO question..."
                data-placeholder-mobile="Ask about SEO"
                class="flex-1 bg-transparent border-none focus:ring-0 focus:outline-none focus-visible:outline-none text-slate-200 py-3.5 sm:py-4 px-4 sm:px-5 max-h-48 resize-none text-base leading-6 placeholder:text-slate-500"></textarea>

            <!-- Send Button -->
            <div class="p-2 flex-shrink-0">
                <button type="submit" id="send-btn"
                    class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 shadow-lg border bg-slate-800 text-slate-500 border-slate-700/50">

                    <svg id="send-icon" xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 transform transition-transform text-slate-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>

                    <svg id="loading-icon" class="animate-spin h-5 w-5 text-white hidden"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
