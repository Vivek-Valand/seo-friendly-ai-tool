<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SeoAnalyzerTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Analyze a website URL for SEO purposes, including meta tags, headings, keyword density, and overall performance suggestions.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $url = $request['url'];

        
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return "Error: The provided URL '$url' is not valid. Please provide a full URL starting with http:// or https://.";
        }

        try {
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ];

            $head = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders($headers)
                ->head($url);

            if ($head->failed() || $head->status() >= 400) {
                $status = $head->status();
                return "Error: The URL '$url' is not reachable (HTTP $status). Please check the URL and try again.";
            }

            $startTime = microtime(true);
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withHeaders($headers)
                ->get($url);
            
            $loadTimeMs = round((microtime(true) - $startTime) * 1000);
            
            if ($response->failed()) {
                return "Failed to analyze $url. The server returned status " . $response->status() . ". This could be due to bot protection or the site being offline.";
            }

            $html = $response->body();
            
            // Refined extraction
            preg_match('/<title>(.*?)<\/title>/is', $html, $titleMatches);
            $title = trim($titleMatches[1] ?? 'No title found');
            
            preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $descMatches);
            if (empty($descMatches)) {
                preg_match('/<meta[^>]*content=["\'](.*?)["\'][^>]*name=["\']description["\']/is', $html, $descMatches);
            }
            $description = trim($descMatches[1] ?? 'No meta description found');

            preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
            $h1s = array_map('strip_tags', array_map('trim', $h1Matches[1] ?? []));

            // Word count approximation
            $textOnly = strip_tags($html);
            $wordCount = str_word_count($textOnly);

            // Images analysis
            preg_match_all('/<img[^>]+>/i', $html, $imgMatches);
            $imagesCount = count($imgMatches[0] ?? []);
            $imagesWithoutAlt = 0;
            foreach ($imgMatches[0] ?? [] as $img) {
                if (!preg_match('/alt=[\'"]([^\'"]+)[\'"]/i', $img, $altMatch) || empty(trim($altMatch[1] ?? ''))) {
                    $imagesWithoutAlt++;
                }
            }

            // Links analysis
            preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $html, $linkMatches);
            $linksCount = count($linkMatches[0] ?? []);
            $internalLinks = 0;
            $externalLinks = 0;
            $parsedUrl = parse_url($url);
            $host = $parsedUrl['host'] ?? '';
            foreach ($linkMatches[1] ?? [] as $href) {
                if (str_starts_with($href, 'http') && !str_contains($href, $host)) {
                    $externalLinks++;
                } else if (!str_starts_with($href, 'mailto:') && !str_starts_with($href, 'tel:')) {
                    $internalLinks++;
                }
            }

            return json_encode([
                'url' => $url,
                'title' => $title,
                'meta_description' => $description,
                'h1_tags' => array_slice($h1s, 0, 5), // Keep it concise
                'word_count' => $wordCount,
                'load_time_ms' => $loadTimeMs,
                'images_total' => $imagesCount,
                'images_missing_alt' => $imagesWithoutAlt,
                'links_total' => $linksCount,
                'internal_links' => $internalLinks,
                'external_links' => $externalLinks,
                'status_code' => $response->status(),
                'page_size' => round(strlen($html) / 1024, 2) . ' KB',
                'suggestion_prompt' => "Expert Analysis: Based on the title '$title', description '$description', $imagesWithoutAlt images missing alt, and $loadTimeMs ms load time, evaluate the SEO health of $url.",
            ]);

        } catch (\Exception $e) {
            return "Error: Unable to verify that '$url' is reachable. Please check the URL and try again.";
        }
    }


    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()->format('uri')->description('The website URL to analyze for SEO.')->required(),
        ];
    }

}
