document.addEventListener("alpine:init", () => {
    Alpine.data(
        "chatApp",
        (initialMessages, initialConversationId, historyUrl, sendUrl) => ({
            messages: initialMessages,
            conversationId: initialConversationId,
            loading: false,
            prompt: "",
            isMobile: window.matchMedia("(max-width: 639px)").matches,

            init() {
                Alpine.store('chat', { 
                    activeId: this.conversationId 
                });

                const media = window.matchMedia("(max-width: 639px)");
                const updateIsMobile = () => {
                    this.isMobile = media.matches;
                };
                updateIsMobile();
                media.addEventListener("change", updateIsMobile);
            },

            async refreshSidebarHistory() {
                try {
                    const response = await axios.get(historyUrl);
                    const container =
                        document.getElementById("chat-history-list");
                    if (container) {
                        container.innerHTML = response.data;
                    }
                } catch (error) {
                    console.error("Failed to refresh sidebar history:", error);
                }
            },

            newChat() {
                if(this.loading) return;
                this.messages = [];
                this.conversationId = null;
                this.prompt = "";
                Alpine.store('chat').activeId = null;
                window.history.pushState({}, '', '/');
                
                // Reset textarea height if possible
                const textarea = document.querySelector('textarea');
                if(textarea) textarea.style.height = 'auto';
            },

            async loadConversation(id) {
                if (this.loading) return;
                this.loading = true;
                try {
                    const response = await axios.get(`/api/chat/${id}`);
                    this.messages = response.data.messages;
                    this.conversationId = response.data.conversation_id;
                    Alpine.store('chat').activeId = this.conversationId;
                    window.history.pushState({}, '', `/c/${id}`);

                    this.$nextTick(() => {
                        const el = document.getElementById(
                            "chat-scroll-container",
                        );
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                } catch (error) {
                    console.error("Failed to load conversation:", error);
                } finally {
                    this.loading = false;
                }
            },

            async sendMessage(promptOverride) {
                const text = promptOverride || this.prompt;
                if (!text.trim() || this.loading) return;

                const currentPrompt = text;
                if(!promptOverride) this.prompt = ""; // Clear if called from input

                const userMsg = { role: "user", content: currentPrompt };
                this.messages.push(userMsg);
                this.loading = true;

                this.$nextTick(() => {
                    const el = document.getElementById("chat-scroll-container");
                    if (el) el.scrollTop = el.scrollHeight;
                });

                try {
                    const response = await axios.post(sendUrl, {
                        prompt: currentPrompt,
                        conversation_id: this.conversationId,
                    });

                    let responseContent = String(response.data.message);

                    // Check for invalid URL animation trigger
                    if (
                        responseContent.includes(
                            "[TRIGGER_INVALID_URL_ANIMATION]",
                        )
                    ) {
                        responseContent = responseContent.replace(
                            "[TRIGGER_INVALID_URL_ANIMATION]",
                            "",
                        );
                        
                        responseContent = `
                        <div class="p-4 border border-rose-500/20 bg-rose-500/10 rounded-xl">
                            <h3 class="font-bold text-rose-400 text-lg mb-1">Invalid URL Detected</h3>
                            <p class="text-slate-200 text-sm">${responseContent}</p>
                        </div>
                    `;
                    }

                    this.messages.push({
                        role: "assistant",
                        content: responseContent,
                    });

                    if (
                        response.data.conversation_id &&
                        this.conversationId !== response.data.conversation_id
                    ) {
                        this.conversationId = response.data.conversation_id;
                        Alpine.store('chat').activeId = this.conversationId;
                        window.history.pushState({}, '', `/c/${this.conversationId}`);
                        this.refreshSidebarHistory();
                    }

                    this.$nextTick(() => {
                        const el = document.getElementById(
                            "chat-scroll-container",
                        );
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                } catch (error) {
                    console.error(error);
                    this.messages.push({
                        role: "assistant",
                        content:
                            "Sorry, something went wrong. Please try again.",
                    });
                } finally {
                    this.loading = false;
                }
            },

            copyToClipboard(textHtml, event) {
                // Strip HTML to get plain text
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = textHtml;
                const textToCopy =
                    tempDiv.textContent || tempDiv.innerText || "";

                navigator.clipboard
                    .writeText(textToCopy)
                    .then(() => {
                        const btn = event?.currentTarget;
                        if (!btn) return;
                        const originalHTML = btn.innerHTML;

                        // Show success checkmark
                        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;

                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                        }, 2000);
                    })
                    .catch((err) => {
                        console.error("Failed to copy!", err);
                    });
            },
        }),
    );
});
