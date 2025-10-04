<?php
namespace AIPM\Policies;

/**
 * Directory of known AI bots with metadata.
 */
class BotDirectory
{
    /**
     * Get list of known AI bots.
     *
     * @return array Array of bot definitions with name, user-agent regex, docs URL, and default action.
     */
    public static function list(): array
    {
        return [
            [
                'name' => 'GPTBot',
                'ua_regex' => '/GPTBot/i',
                'docs_url' => 'https://openai.com/gptbot',
                'default_action' => 'block'
            ],
            [
                'name' => 'ClaudeBot',
                'ua_regex' => '/ClaudeBot/i',
                'docs_url' => 'https://anthropic.com/bot',
                'default_action' => 'block'
            ],
            [
                'name' => 'Google-Extended',
                'ua_regex' => '/Google-Extended/i',
                'docs_url' => 'https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers',
                'default_action' => 'allow'
            ],
            [
                'name' => 'PerplexityBot',
                'ua_regex' => '/PerplexityBot/i',
                'docs_url' => 'https://www.perplexity.ai',
                'default_action' => 'block'
            ],
            [
                'name' => 'Bytespider',
                'ua_regex' => '/Bytespider/i',
                'docs_url' => 'https://bytedance.com',
                'default_action' => 'block'
            ],
            [
                'name' => 'Applebot-Extended',
                'ua_regex' => '/Applebot-Extended/i',
                'docs_url' => 'https://support.apple.com/en-us/119829',
                'default_action' => 'block'
            ],
            [
                'name' => 'CCBot',
                'ua_regex' => '/CCBot/i',
                'docs_url' => 'https://commoncrawl.org/ccbot',
                'default_action' => 'block'
            ],
            [
                'name' => 'Amazonbot',
                'ua_regex' => '/Amazonbot/i',
                'docs_url' => 'https://developer.amazon.com/amazonbot',
                'default_action' => 'block'
            ],
        ];
    }
}
