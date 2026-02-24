<?php

namespace App\Ai\Tools;

use App\Services\MarkdownRenderer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request as AiRequest;
use Stringable;

class SaveSEOReportTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Save the formatted SEO report locally on the server. Use this tool AFTER generating the final Markdown report so the user can download or view it.';
    }

    /**
     * Execute the tool.
     */
    public function handle(AiRequest $request): Stringable|string
    {
        $content = $request['content'];
        $conversationId = request('conversation_id');

        if (! $conversationId) {
            $conversationId = uniqid('offline_');
        }

        $fileName = "reports/seo_report_{$conversationId}.doc";

        $htmlBody = $content;
        if (stripos($content, '<html') === false && stripos($content, '<body') === false) {
            $htmlBody = app(MarkdownRenderer::class)->toHtml((string) $content);
        }

        $document = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SEO Report</title>
<style>
    body { font-family: Arial, sans-serif; color: #0f172a; }
    h1, h2, h3 { color: #0f172a; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
    th { background: #f1f5f9; }
</style>
</head>
<body>
$htmlBody
</body>
</html>
HTML;

        Storage::disk('public')->put($fileName, $document);

        $url = route('chat.report', ['id' => $conversationId]);

        return "Report saved successfully. You can provide this URL to the user to download or view the report: $url";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->description('The fully formatted Markdown content of the SEO report.')->required(),
        ];
    }
}
