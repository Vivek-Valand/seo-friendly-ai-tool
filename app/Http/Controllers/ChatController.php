<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Tools\Request as AiRequest;

use App\Ai\Agents\SEOFriendlyAgent;
use App\Ai\Agents\SEOReportGeneratorAgent;
use App\Ai\Tools\PageSpeedTool;
use App\Services\MarkdownRenderer;
use Illuminate\Support\Facades\DB;
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
            
            $conversationId = $request->input('conversation_id');
            
        if (is_string($conversationId)) {
            $conversationId = trim($conversationId);
        }
        if ($conversationId === '') {
            $conversationId = null;
        }

        try {
            $prompt = (string) $request->prompt;
            $url = $this->extractUrlFromPrompt($prompt);

            if ($this->isReportRequest($prompt)) {
                if (! $conversationId) {
                    return response()->json([
                        'message' => 'Please run the SEO analysis first, then confirm you want the full report.',
                    ], 400);
                }

                $reportContent = $this->getLatestAssistantContent($conversationId);
                if ($reportContent !== null && $this->assistantAskedForReport($reportContent)) {
                    $reportContent = $this->stripReportPrompt($reportContent);

                    if ($reportContent !== '') {
                        $reportAgent = SEOReportGeneratorAgent::make()->forUser($user);
                        $reportAgent->continue($conversationId, $user);

                        $reportPrompt = "Expand and format the following SEO analysis into a comprehensive report. "
                            . "Keep each section brief but descriptive, cover all required areas, use clear headings, "
                            . "and add tables where helpful. Remove conversational filler.\n\n"
                            . "Raw analysis:\n{$reportContent}";

                        $response = $reportAgent->prompt($reportPrompt);

                        $cleanContent = $this->sanitizeAssistantContent((string) $response);
                        $aiContent = app(MarkdownRenderer::class)->toHtml($cleanContent);
                        $conversationId = resolve(ConversationStore::class)->latestConversationId($user->id);

                        return response()->json([
                            'message' => (string) $aiContent,
                            'conversation_id' => $conversationId,
                        ]);
                    }
                }
            }

            if ($url === null) {
                $agent = SEOFriendlyAgent::make()->forUser($user);
                if ($conversationId) {
                    $agent->continue($conversationId, $user);
                }
                $response = $agent->prompt($prompt);
            } else {
                $pageSpeedData = $this->fetchPageSpeedData($url);

                if ($pageSpeedData === null) {
                    $agent = SEOFriendlyAgent::make()->forUser($user);
                    if ($conversationId) {
                        $agent->continue($conversationId, $user);
                    }
                    $response = $agent->prompt($prompt);
                } else {
                    $internalUser = (object) ['id' => 0];
                    $baseAgent = SEOFriendlyAgent::make()->forUser($internalUser);
                    $baseAnalysis = $baseAgent->prompt($prompt);

                    $finalAgent = SEOFriendlyAgent::make()->forUser($user);
                    if ($conversationId) {
                        $finalAgent->continue($conversationId, $user);
                    }

                    $finalPrompt = "User request:\n{$prompt}\n\n"
                        . "Base SEO analysis (from AI):\n{$baseAnalysis}\n\n"
                        . "Technical performance data (JSON):\n{$pageSpeedData}\n\n"
                        . "Combine the base analysis with the technical performance data into one final response. "
                        . "Use the technical data ONLY for performance/technical SEO sections (Core Web Vitals, speed, render-blocking, caching). "
                        . "Do NOT use it to infer niche, audience, content topics, or business details. "
                        . "Do NOT mention tools, APIs, or data sources. If anything conflicts, prefer the technical data for performance metrics.";

                    $response = $finalAgent->prompt($finalPrompt);
                }
            }
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

        $cleanContent = $this->sanitizeAssistantContent((string) $response);
        $aiContent = app(MarkdownRenderer::class)->toHtml($cleanContent);
        $conversationId = resolve(ConversationStore::class)->latestConversationId($user->id);

        return response()->json([
            'message' => (string) $aiContent,
            'conversation_id' => $conversationId,
        ]);
    }

    private function extractUrlFromPrompt(string $prompt): ?string
    {
        if (preg_match('~https?://[^\s)\]}>]+~i', $prompt, $match)) {
            $url = rtrim($match[0], ".,);");
            return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
        }

        if (preg_match('~\b([a-z0-9-]+(?:\.[a-z0-9-]+)+)(/[^\s)\]}>]*)?~i', $prompt, $match)) {
            $url = rtrim($match[0], ".,);");
            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = 'https://' . $url;
            }
            return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
        }

        return null;
    }

    private function fetchPageSpeedData(string $url): ?string
    {
        try {
            $tool = new PageSpeedTool();
            $result = (string) $tool->handle(new AiRequest([
                'url' => $url,
                'strategy' => 'desktop',
                'categories' => ['performance'],
            ]));
        } catch (\Throwable $e) {
            return null;
        }

        if ($result === '' || $result === 'API_OFFLINE') {
            return null;
        }

        return $result;
    }

    private function sanitizeAssistantContent(string $content): string
    {
        $content = str_ireplace(
            ['PageSpeed Insights', 'PageSpeed'],
            ['performance data', 'performance'],
            $content
        );

        $patterns = [
            '/I am sorry, but I am unable to access the .*? tool.*?/i',
            '/I\'m sorry, but I am unable to access the .*? tool.*?/i',
            '/unable to access the .*? tool/i',
            '/\\btool\\b.*?(?:analysis|perform|use|access)/i',
            '/analyze_seo/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return "I’m unable to access the analysis data right now. Please try again in a moment or provide more details so I can help.";
            }
        }

        return $content;
    }

    private function isReportRequest(string $prompt): bool
    {
        $prompt = strtolower(trim($prompt));
        if ($prompt === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(yes|yep|yeah|sure|ok|okay|please|generate(?:\s+a)?\s+report|create(?:\s+a)?\s+report|download(?:\s+the)?\s+report|make(?:\s+a)?\s+report)\b/i',
            $prompt
        );
    }

    private function getLatestAssistantContent(string $conversationId): ?string
    {
        $message = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $message) {
            return null;
        }

        return (string) $message->content;
    }

    private function stripReportPrompt(string $content): string
    {
        $content = preg_replace(
            '/\*\*Would you like me to generate a comprehensive, fully-formatted SEO report for you to download\?\*\*/i',
            '',
            $content
        );

        return trim((string) $content);
    }

    private function assistantAskedForReport(string $content): bool
    {
        return stripos($content, 'generate a comprehensive, fully-formatted SEO report') !== false
            || stripos($content, 'SEO report for you to download') !== false;
    }


}
