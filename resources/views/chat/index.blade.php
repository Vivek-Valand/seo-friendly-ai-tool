@extends('layouts.master')

@section('content')
    <!-- Push external assets -->
    @push('styles')
        @vite('resources/css/chat.css')
    @endpush

    @push('scripts')
        @vite('resources/js/chat.js')
    @endpush

    <div class="flex flex-col h-full relative" x-data="chatApp({{ Js::from($messages) }}, '{{ $conversationId }}', '{{ route('chat.sidebar_history') }}', '{{ route('chat.send') }}')" @load-chat.window="loadConversation($event.detail.id)"
        @new-chat.window="newChat()">

        <!-- Conversation Area - Scrollable -->
        <div class="flex-1 overflow-y-auto custom-scrollbar" id="chat-scroll-container">
            <div class="max-w-full sm:max-w-[980px] lg:max-w-[1120px] mx-auto w-full px-2 sm:px-4">
                @include('chat.partials.conversation')
            </div>
        </div>

        <!-- Input Area - Sticky at bottom -->
        <div class="w-full relative z-20">
            <div class="max-w-full sm:max-w-[980px] lg:max-w-[1120px] mx-auto pb-4 sm:pb-8 px-2 sm:px-4">
                @include('chat.partials.input')
            </div>
        </div>
    </div>
@endsection
