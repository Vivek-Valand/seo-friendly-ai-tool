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
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])
                ->get($url);
            
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

            return json_encode([
                'url' => $url,
                'title' => $title,
                'meta_description' => $description,
                'h1_tags' => array_slice($h1s, 0, 5), // Keep it concise
                'word_count' => $wordCount,
                'status_code' => $response->status(),
                'page_size' => round(strlen($html) / 1024, 2) . ' KB',
                'suggestion_prompt' => "Expert Analysis: Based on the title '$title', description '$description', and presence of H1 tags, evaluate the SEO health of $url.",
            ]);

        } catch (\Exception $e) {
            return "An internal error occurred while analyzing the URL: " . $e->getMessage();
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
