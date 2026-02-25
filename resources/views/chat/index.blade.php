@extends('layouts.master')

@section('content')
    <!-- Push external assets -->
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('js/chat.js') }}"></script>
        <script>
            $(document).ready(function() {
                if (window.ChatApp) {
                    window.ChatApp.init(
                        '{{ $conversationId }}',
                        '{{ route('chat.sidebar_history') }}',
                        '{{ route('chat.send') }}'
                    );
                }
            });
        </script>
    @endpush

    <div class="flex flex-col h-full relative" id="chat-app-container">

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
