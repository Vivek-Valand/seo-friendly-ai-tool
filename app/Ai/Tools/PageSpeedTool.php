<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Illuminate\Support\Facades\Log;

class PageSpeedTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Fetch ONLY technical speed and performance metrics (Core Web Vitals, speed index) for a URL. Do NOT use this for niche, content, or general SEO analysis.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $url = trim((string) ($request['url'] ?? ''));
        $strategy = trim((string) ($request['strategy'] ?? 'desktop'));
        $categories = $request['categories'] ?? ['performance'];

        if ($url === '') {
            return 'API_OFFLINE';
        }

        $apiKey = (string) config('ai.pagespeed.key');
        if ($apiKey === '') {
            return 'API_OFFLINE';
        }

        $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

        try {
            $response = Http::timeout(20)
                ->retry(2, 200)
                ->get($apiUrl, [
                    'url' => $url,
                    'key' => $apiKey,
                    'strategy' => $strategy,
                    'category' => $categories,
                ]);

            if ($response->failed()) {
                return 'API_OFFLINE';
            }

            $data = $response->json();
            if (!is_array($data) || !isset($data['lighthouseResult'])) {
                return 'API_OFFLINE';
            }
        } catch (\Throwable $e) {
            return 'API_OFFLINE';
        }
        
        $audits = $data['lighthouseResult']['audits'] ?? [];
        $metrics = [
            'performance_score' => isset($data['lighthouseResult']['categories']['performance']['score'])
                ? round($data['lighthouseResult']['categories']['performance']['score'] * 100)
                : null,
            'lcp' => $audits['largest-contentful-paint']['displayValue'] ?? null,
            'inp' => $audits['interaction-to-next-paint']['displayValue'] ?? null,
            'cls' => $audits['cumulative-layout-shift']['displayValue'] ?? null,
            'fcp' => $audits['first-contentful-paint']['displayValue'] ?? null,
            'tti' => $audits['interactive']['displayValue'] ?? null,
            'tbt' => $audits['total-blocking-time']['displayValue'] ?? null,
            'speed_index' => $audits['speed-index']['displayValue'] ?? null,
        ];
        Log::info([
            'url' => $url,
            'strategy' => $strategy,
            'categories' => $categories,
            'metrics' => $metrics,
            'audits' => $audits,
        ]);

        return json_encode([
            'url' => $url,
            'strategy' => $strategy,
            'categories' => $categories,
            'metrics' => $metrics,
            // 'audits' => [
            //     'opportunities' => $audits['diagnostics']['details']['items'] ?? null,
            //     'render_blocking' => $audits['render-blocking-resources']['details']['items'] ?? null,
            //     'unused_css' => $audits['unused-css-rules']['details']['items'] ?? null,
            //     'unused_js' => $audits['unused-javascript']['details']['items'] ?? null,
            // ],
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()->format('uri')->description('The URL to audit for technical performance.')->required(),
            'strategy' => $schema->string()->enum(['mobile', 'desktop'])->description('Device strategy for speed audit.'),
            'categories' => $schema->array()->items($schema->string()->enum(['performance']))
                ->description('Requested technical audit categories (strictly performance).'),
        ];
    }
}
