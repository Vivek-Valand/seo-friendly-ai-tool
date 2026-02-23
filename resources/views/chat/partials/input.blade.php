{{-- <div class="p-6 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent"> --}}
<div class="p-4 sm:p-6 bg-gradient-to-t to-transparent">
    <div class="w-full mx-auto relative group"
        x-data="{ prompt: '', isMobile: window.innerWidth < 640 }"
        x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 640 })">
        <!-- Input Wrapper -->
        <form
            @submit.prevent="if(prompt.trim()) { sendMessage(prompt); prompt = ''; $refs.textarea.style.height = 'auto' }"
            class="relative flex items-end glass rounded-3xl overflow-hidden shadow-2xl transition-all duration-300 border border-slate-800 focus-within:border-slate-700 focus-within:ring-1 focus-within:ring-slate-700/60 bg-slate-900/40">

            <!-- Attachment Button -->
            {{-- <label
                class="dp-4 cursor-pointer text-slate-400 hover:text-indigo-400 transition-colors flex-shrink-0 group/icon">
                <input type="file" class="hidden">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-6 h-6 transform group-hover/icon:rotate-12 transition-transform" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
            </label> --}}

            <!-- Textarea -->
            <textarea x-model="prompt" x-ref="textarea" rows="1"
                @keydown.enter.prevent="if(!loading && prompt.trim()) { $el.closest('form').dispatchEvent(new Event('submit')) }"
                @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                x-bind:placeholder="isMobile ? 'Ask about SEO' : 'Enter website URL or ask an SEO question...'"
                class="flex-1 bg-transparent border-none focus:ring-0 focus:outline-none focus-visible:outline-none text-slate-200 py-3 sm:py-4 px-4 sm:px-5 max-h-48 resize-none text-base placeholder:text-slate-500"></textarea>

            <!-- Send Button -->
            <div class="p-2 flex-shrink-0">
                <button type="submit" :disabled="!prompt.trim() || loading"
                    class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-lg disabled:opacity-20 disabled:grayscale disabled:cursor-not-allowed"
                    :class="prompt.trim() && !loading ? 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-indigo-500/20' :
                        'bg-slate-700 text-slate-400'">
                    <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 transform transition-transform"
                        :class="prompt.trim() ? 'rotate-45 -translate-y-0.5 translate-x-0.5' : ''" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        </form>


        <!-- Suggestions/Info -->
        {{-- <p
            class="text-[10px] text-center mt-3 text-slate-600 font-medium tracking-wide flex items-center justify-center gap-1.5 uppercase">
            <span>Powered by Gemini 2.0</span>
            <span class="w-1 h-1 rounded-full bg-slate-800"></span>
            <span>Always check for accuracy</span>
        </p> --}}
    </div>
</div>
