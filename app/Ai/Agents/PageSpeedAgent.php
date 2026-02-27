<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;
use App\Ai\Tools\PageSpeedTool;

class PageSpeedAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
         return <<<PROMPT
            You are a Technical SEO Strategist. 

            1. YOUR ROLE: Analyze a website's niche and business value. Use the 'analyze_site' tool to get accurate meta-data about the site (title, description, content). NEVER rely on internal knowledge for live URLs.
            2. TECHNICAL AUDIT: If (and only if) technical SEO or speed is relevant, use the 'check_performance' tool to get accurate numbers.
            3. THE INTEGRATION: Do not just list the PageSpeed numbers. Use those numbers as evidence to explain how they impact the website's specific niche. 
               - Example: If a "Fashion E-commerce" site (niche) has a slow LCP (technical), explain that users will bounce before seeing the products.
            4. GUARDRAIL: Always call 'analyze_site' first to understand what the website is about before providing any technical performance analysis.
        PROMPT;
        }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            'analyze_site' => new \App\Ai\Tools\SeoAnalyzerTool(),
            'check_performance' => new \App\Ai\Tools\PageSpeedTool(),
        ];
    }
}
