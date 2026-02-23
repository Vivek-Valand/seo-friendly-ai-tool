<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Contracts\ConversationStore;

use App\Ai\Agents\SEOFriendlyAgent;
use Laravel\Ai\Facades\Ai;
use App\Services\MarkdownRenderer;

class ChatController extends Controller
{
    public function index()
    {
        // Fetch last conversation messages for persistence
        $conversation = \Illuminate\Support\Facades\DB::table('agent_conversations')
            ->where('user_id', 1)
            ->orderBy('updated_at', 'desc')
            ->first();

        $messages = [];
        $conversationId = null;
        if ($conversation) {
            $conversationId = $conversation->id;
            $messages = \Illuminate\Support\Facades\DB::table('agent_conversation_messages')
                ->where('conversation_id', $conversationId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'role' => $m->role,
                    'content' => $m->role === 'assistant'
                        ? app(MarkdownRenderer::class)->toHtml((string) $m->content)
                        : (string) $m->content
                ]);
        }

        return view('chat.index', compact('messages', 'conversationId'));
    }

    public function newChat()
    {
        return view('chat.index', ['messages' => [], 'conversationId' => null]);
    }

    public function show($id)
    {
        $messages = \Illuminate\Support\Facades\DB::table('agent_conversation_messages')
            ->where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->role === 'assistant'
                    ? app(MarkdownRenderer::class)->toHtml((string) $m->content)
                    : (string) $m->content
            ]);

        return response()->json([
            'messages' => $messages,
            'conversation_id' => $id,
        ]);
    }

    public function historyPartial()
    {
        $history = \Illuminate\Support\Facades\DB::table('agent_conversations')
            ->where('user_id', 1)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('chat.partials.sidebar-history', compact('history'));
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\DB::table('agent_conversations')->where('id', $id)->delete();
        \Illuminate\Support\Facades\DB::table('agent_conversation_messages')->where('conversation_id', $id)->delete();

        return response()->json(['success' => true]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'conversation_id' => 'nullable|string',
            ]);
            
            // Mock user for persistence as requested
            $user = (object) ['id' => 1];
            
            $agent = SEOFriendlyAgent::make()
            ->forUser($user);
            
            $conversationId = $request->input('conversation_id');
            
        if (is_string($conversationId)) {
            $conversationId = trim($conversationId);
        }
        if ($conversationId === '') {
            $conversationId = null;
        }

        if ($conversationId) {
            $agent->continue($conversationId, $user);
        }

        try {
            $response = $agent->prompt($request->prompt);
        } catch (RateLimitedException $e) {
            return response()->json([
                'message' => 'The AI provider is rate limiting requests. Please wait a moment and try again.',
            ], 429);
        } catch (\Throwable $e) {
            Log::error('AI prompt failed', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Server error while processing the request.',
            ], 500);
        }

        $aiContent = app(MarkdownRenderer::class)->toHtml((string) $response);
        $conversationId = $agent->currentConversation()
            ?? $response->conversationId
            ?? resolve(ConversationStore::class)->latestConversationId($user->id);

        return response()->json([
            'message' => (string) $aiContent,
            'conversation_id' => $conversationId,
        ]);
    }


}
