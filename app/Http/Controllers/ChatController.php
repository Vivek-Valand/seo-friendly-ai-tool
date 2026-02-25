<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Contracts\ConversationStore;

use App\Ai\Agents\SEOFriendlyAgent;
use Laravel\Ai\Facades\Ai;
use App\Services\MarkdownRenderer;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index($id = null)
    {
        $messages = [];
        $conversationId = $id;

        if ($id) {
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
            
            if ($messages->isEmpty()) {
                return redirect()->route('home');
            }
        }

        return view('chat.index', compact('messages', 'conversationId'));
    }


    public function show(\Illuminate\Http\Request $request, $id)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('chat.open', ['id' => $id]);
        }
        
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

        Storage::disk('public')->delete("reports/seo_report_{$id}.doc");
        Storage::disk('public')->delete("reports/seo_report_{$id}.md");

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('agent_conversations')
            ->where('id', $id)
            ->update([
                'title' => $request->title,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    public function downloadReport($id)
    {
        $fileName = "reports/seo_report_{$id}.doc";
        if (!Storage::disk('public')->exists($fileName)) {
            $fileName = "reports/seo_report_{$id}.md";
        }

        if (!Storage::disk('public')->exists($fileName)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($fileName);
        $downloadName = "seo_report_{$id}." . pathinfo($fileName, PATHINFO_EXTENSION);

        return response()->download($path, $downloadName, [
            'Content-Type' => $fileName === "reports/seo_report_{$id}.doc"
                ? 'application/msword; charset=UTF-8'
                : 'text/markdown; charset=UTF-8',
        ]);
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
