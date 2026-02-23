@extends('layouts.master')

@section('content')
    <div class="flex flex-col h-full relative" x-data="{
        messages: {{ Js::from($messages) }},
        conversationId: '{{ $conversationId }}',
        loading: false,
        async refreshSidebarHistory() {
            try {
                const response = await axios.get('{{ route('chat.sidebar_history') }}');
                const container = document.getElementById('chat-history-list');
                if (container) {
                    container.innerHTML = response.data;
                }
            } catch (error) {
                console.error('Failed to refresh sidebar history:', error);
            }
        },
        async loadConversation(id) {
            if (this.loading) return;
            this.loading = true;
            try {
                const response = await axios.get(`/chat/${id}`);
                this.messages = response.data.messages;
                this.conversationId = response.data.conversation_id;
    
                // Update URL without reload for better UX if needed, or just let it stay
                // window.history.pushState({}, '', `/chat/${id}`);
    
                this.$nextTick(() => {
                    const el = document.getElementById('chat-scroll-container');
                    el.scrollTop = el.scrollHeight;
                });
            } catch (error) {
                console.error('Failed to load conversation:', error);
            } finally {
                this.loading = false;
            }
        },
        async sendMessage(prompt) {
            if (!prompt.trim() || this.loading) return;
    
            const userMsg = { role: 'user', content: prompt };
            this.messages.push(userMsg);
            this.loading = true;
    
            // Auto-scroll to bottom
            this.$nextTick(() => {
                const el = document.getElementById('chat-scroll-container');
                el.scrollTop = el.scrollHeight;
            });
    
            try {
                const response = await axios.post('{{ route('chat.send') }}', {
                    prompt,
                    conversation_id: this.conversationId
                });
    
                this.messages.push({ role: 'assistant', content: String(response.data.message) });
                this.conversationId = response.data.conversation_id;
                this.refreshSidebarHistory();
    
                this.$nextTick(() => {
                    const el = document.getElementById('chat-scroll-container');
                    el.scrollTop = el.scrollHeight;
                });
            } catch (error) {
                console.error(error);
                this.messages.push({ role: 'assistant', content: 'Sorry, something went wrong. Please try again.' });
            } finally {
                this.loading = false;
            }
        }
    }" @load-chat.window="loadConversation($event.detail.id)">
        <!-- Conversation Area - Scrollable -->
        <div class="flex-1 overflow-y-auto" id="chat-scroll-container">
            <div class="max-w-full sm:max-w-[70%] mx-auto w-full px-3 sm:px-0">
                @include('chat.partials.conversation')
            </div>
        </div>

        <!-- Input Area - Fixed at bottom -->
        <div class="w-full">
            <div class="max-w-full sm:max-w-[70%] mx-auto pb-4 px-3 sm:px-0">
                @include('chat.partials.input')
            </div>
        </div>
    </div>
@endsection
