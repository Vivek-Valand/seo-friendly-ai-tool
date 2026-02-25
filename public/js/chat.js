window.ChatApp = {
    conversationId: null,
    loading: false,
    historyUrl: '',
    sendUrl: '',
    confirmDeleteId: null,

    init(initialConversationId, historyUrl, sendUrl) {
        console.log('ChatApp: Initializing...', { initialConversationId, historyUrl, sendUrl });
        this.conversationId = initialConversationId;
        this.historyUrl = historyUrl;
        this.sendUrl = sendUrl;
        this.setupEventListeners();
        this.wrapTablesIn($('#chat-messages-container'));
        this.updateInputPlaceholder();
        this.updateSidebarForViewport();
        this.scrollToBottom();
        this.updateActiveSidebarItem();
        this.toggleSendButton(); // Initial state
        console.log('ChatApp: Initialization complete.');
    },

    setupEventListeners() {
        console.log('ChatApp: Setting up event listeners...');
        const self = this;
        
        // Form submission
        $(document).on('submit', '#chat-form', function(e) {
            e.preventDefault();
            self.sendMessage();
        });

        // Enter key in textarea
        $(document).on('keydown', '#chat-input', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#chat-form').submit();
            }
        });

        // Auto-expand textarea and toggle send button
        $(document).on('input', '#chat-input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            self.toggleSendButton();
        });

        // Placeholder swap on resize/orientation change
        $(window).on('resize orientationchange', function() {
            self.updateInputPlaceholder();
            self.updateSidebarForViewport();
        });

        // Load chat event
        $(document).on('click', '.load-chat-item', function(e) {
            if ($(e.target).closest('.delete-chat-item').length) return;
            e.preventDefault();
            const id = $(this).data('id');
            self.loadConversation(id);
        });

        // New chat button
        $(document).on('click', '.new-chat-btn', function(e) {
            e.preventDefault();
            self.newChat();
        });

        // Sidebar toggle (Desktop & Mobile)
        $(document).on('click', '#sidebar-toggle, #sidebar-toggle-mobile, #sidebar-backdrop', function() {
            $('body').toggleClass('sidebar-open');
        });

        // Delete modal trigger
        $(document).on('click', '.delete-chat-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.confirmDeleteId = $(this).data('id');
            $('#delete-modal').removeClass('hidden');
        });

        $(document).on('click', '#close-delete-modal, #delete-modal-backdrop-inner', function() {
            $('#delete-modal').addClass('hidden');
            self.confirmDeleteId = null;
        });

        $(document).on('click', '#confirm-delete-btn', function() {
            self.deleteChat();
        });

        // Copy button
        $(document).on('click', '.copy-btn', function() {
            const content = $(this).closest('.message-wrapper').find('.message-content').html();
            self.copyToClipboard(content, this);
        });
    },

    async sendMessage() {
        const $input = $('#chat-input');
        const prompt = $input.val().trim();
        
        if (!prompt || this.loading) return;

        this.appendMessage('user', prompt);
        $input.val('');
        $input.css('height', 'auto');
        this.toggleSendButton();
        
        this.setLoading(true);

        try {
            const response = await $.ajax({
                url: this.sendUrl,
                method: 'POST',
                data: {
                    prompt: prompt,
                    conversation_id: this.conversationId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });

            this.handleResponse(response);
        } catch (error) {
            console.error('Send message failed:', error);
            this.appendMessage('assistant', 'Sorry, something went wrong. Please try again.');
        } finally {
            this.setLoading(false);
        }
    },

    handleResponse(response) {
        let content = response.message;
        
        if (content.includes("[TRIGGER_INVALID_URL_ANIMATION]")) {
            content = content.replace("[TRIGGER_INVALID_URL_ANIMATION]", "");
            content = `
                <div class="p-4 border border-rose-500/20 bg-rose-500/10 rounded-xl">
                    <h3 class="font-bold text-rose-400 text-lg mb-1">Invalid URL Detected</h3>
                    <p class="text-slate-200 text-sm">${content}</p>
                </div>
            `;
        }

        if (this.looksLikeJson(content)) {
            const escaped = this.escapeHtml(content.trim());
            content = `<pre><code class="language-json">${escaped}</code></pre>`;
        }

        if (content.includes("<table>")) {
            // Very simple replacement for tables to ensure they have a wrapper
            content = content.replace(/<table/g, '<div class="table-wrapper"><table');
            content = content.replace(/<\/table>/g, '</table></div>');
        }

        this.appendMessage('assistant', content);

        if (response.conversation_id && this.conversationId !== response.conversation_id) {
            this.conversationId = response.conversation_id;
            window.history.pushState({}, '', `/c/${this.conversationId}`);
            this.refreshSidebarHistory();
        }
    },

    async loadConversation(id) {
        if (this.loading) return;
        this.setLoading(true);

        try {
            const response = await $.get(`/api/chat/${id}`);
            const $container = $('#chat-messages-container');
            $container.empty();
            
            if (response.messages && response.messages.length > 0) {
                $('#empty-state').hide();
                response.messages.forEach(msg => {
                    this.appendMessage(msg.role, msg.content, false);
                });
            } else {
                $('#empty-state').show();
            }

            this.conversationId = response.conversation_id;
            window.history.pushState({}, '', `/c/${id}`);
            this.scrollToBottom();
            this.updateActiveSidebarItem();
        } catch (error) {
            console.error('Failed to load conversation:', error);
        } finally {
            this.setLoading(false);
        }
    },

    newChat() {
        if (this.loading) return;
        this.conversationId = null;
        $('#chat-messages-container').empty();
        $('#empty-state').show();
        window.history.pushState({}, '', '/');
        this.updateActiveSidebarItem();
    },

    async refreshSidebarHistory() {
        try {
            const response = await $.get(this.historyUrl);
            $('#chat-history-list').html(response);
            this.updateActiveSidebarItem();
        } catch (error) {
            console.error('Failed to refresh sidebar history:', error);
        }
    },

    async deleteChat() {
        if (!this.confirmDeleteId) return;
        try {
            await $.ajax({
                url: `/chat/${this.confirmDeleteId}`,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            if (window.location.pathname.includes(this.confirmDeleteId)) {
                window.location.href = '/';
            } else {
                this.refreshSidebarHistory();
                $('#delete-modal').addClass('hidden');
            }
        } catch (error) {
            console.error('Delete failed:', error);
        } finally {
            this.confirmDeleteId = null;
        }
    },

    appendMessage(role, content, scroll = true) {
        $('#empty-state').hide();
        
        const template = $('#message-template').html();
        const $msg = $(template);
        
        $msg.find('.avatar-text').text(role === 'user' ? 'ME' : 'AI');
        $msg.find('.role-name').text(role === 'user' ? 'You' : 'SEOFriendly AI');
        $msg.find('.message-content').html(content);

        if (role === 'assistant') {
            this.wrapTablesIn($msg);
        }
        
        if (role === 'user') {
            $msg.addClass('flex-row-reverse');
            $msg.find('.message-wrapper').addClass('items-end');
            $msg.find('.message-bubble').addClass('rounded-tr-none bg-indigo-600 text-white border-indigo-500 shadow-xl shadow-indigo-950/30');
            $msg.find('.avatar-container').addClass('bg-slate-800 text-indigo-400');
            $msg.find('.copy-btn').remove();
        } else {
            $msg.find('.message-wrapper').addClass('items-start');
            $msg.find('.message-bubble').addClass('rounded-tl-none bg-slate-800/80 backdrop-blur-sm text-slate-200 border-slate-700/50 shadow-lg');
            $msg.find('.avatar-container').addClass('bg-indigo-600 text-white');
        }

        $('#chat-messages-container').append($msg);
        if (scroll) this.scrollToBottom();
    },

    setLoading(loading) {
        this.loading = loading;
        if (loading) {
            $('#loading-indicator').removeClass('hidden');
            $('#send-btn').prop('disabled', true);
            $('#send-icon').addClass('hidden');
            $('#loading-icon').removeClass('hidden');
        } else {
            $('#loading-indicator').addClass('hidden');
            $('#send-btn').prop('disabled', false);
            $('#send-icon').removeClass('hidden');
            $('#loading-icon').addClass('hidden');
        }
    },

    scrollToBottom() {
        const el = document.getElementById('chat-scroll-container');
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    },

    updateActiveSidebarItem() {
        $('.load-chat-item').removeClass('bg-slate-800 shadow-lg');
        if (this.conversationId) {
            $(`.load-chat-item[data-id="${this.conversationId}"]`).addClass('bg-slate-800 shadow-lg');
        }
    },

    copyToClipboard(textHtml, btn) {
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = textHtml;
        const textToCopy = tempDiv.textContent || tempDiv.innerText || "";

        navigator.clipboard.writeText(textToCopy).then(() => {
            const $btn = $(btn);
            const originalHTML = $btn.html();
            $btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>');
            setTimeout(() => { $btn.html(originalHTML); }, 2000);
        }).catch(err => {
            console.error('Failed to copy!', err);
        });
    },

    toggleSendButton() {
        const val = $('#chat-input').val().trim();
        $('#send-btn').prop('disabled', !val || this.loading);
        if (!val || this.loading) {
            $('#send-btn').addClass('opacity-50 cursor-not-allowed').removeClass('bg-indigo-600 text-white').addClass('bg-slate-800 text-slate-500');
            $('#send-icon').addClass('text-slate-500').removeClass('text-white');
        } else {
            $('#send-btn').removeClass('opacity-50 cursor-not-allowed').addClass('bg-indigo-600 text-white').removeClass('bg-slate-800 text-slate-500');
            $('#send-icon').removeClass('text-slate-500').addClass('text-white');
        }
    },

    updateInputPlaceholder() {
        const input = document.getElementById('chat-input');
        if (!input) return;
        const desktopText = input.getAttribute('data-placeholder-desktop') || input.getAttribute('placeholder') || '';
        const mobileText = input.getAttribute('data-placeholder-mobile') || desktopText;
        const isMobile = window.matchMedia('(max-width: 640px)').matches;
        input.setAttribute('placeholder', isMobile ? mobileText : desktopText);
    },

    updateSidebarForViewport() {
        const isMobile = window.matchMedia('(max-width: 1023px)').matches;
        if (isMobile) {
            $('body').removeClass('sidebar-open');
        } else {
            $('body').addClass('sidebar-open');
        }
    },

    looksLikeJson(content) {
        if (!content) return false;
        const trimmed = content.trim();
        if (trimmed.startsWith('<') || trimmed.includes('<pre') || trimmed.includes('<code') || trimmed.includes('<table')) return false;
        if (!(trimmed.startsWith('{') || trimmed.startsWith('['))) return false;
        return trimmed.endsWith('}') || trimmed.endsWith(']');
    },

    escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    wrapTablesIn($root) {
        if (!$root || !$root.length) return;
        $root.find('table').each(function() {
            const $table = $(this);
            if ($table.closest('.table-wrapper').length) return;
            $table.wrap('<div class="table-wrapper"></div>');
        });
    }
};
