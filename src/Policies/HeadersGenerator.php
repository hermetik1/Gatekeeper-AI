<?php
namespace AIPM\Policies;

/**
 * Generates X-Robots-Tag HTTP headers based on policies.
 */
class HeadersGenerator
{
    /**
     * Send X-Robots-Tag headers based on current request policy.
     * Should be hooked early (e.g., 'send_headers' or 'template_redirect').
     *
     * @return void
     */
    public static function send_headers(): void
    {
        // Skip in admin, AJAX, feed, or REST API contexts
        if (is_admin() || wp_doing_ajax() || is_feed() || defined('REST_REQUEST')) {
            return;
        }
        
        // Skip if headers already sent
        if (headers_sent()) {
            return;
        }
        
        // Get current path and post ID
        $path = self::get_current_path();
        $post_id = self::get_current_post_id();
        
        // Get merged policy for current request
        $policy = PolicyManager::merged_for_path($path, $post_id);
        
        // If any bots are blocked, send restrictive header
        if (!empty($policy['block'])) {
            // Send general X-Robots-Tag for all bots
            header('X-Robots-Tag: noai, noimageai', false);
            
            // Send bot-specific headers
            foreach ($policy['block'] as $bot) {
                header("X-Robots-Tag: {$bot}: noindex, nofollow", false);
            }
        }
    }
    
    /**
     * Get the current request path relative to site root.
     *
     * @return string Current path.
     */
    private static function get_current_path(): string
    {
        $url = home_url('/');
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        
        // Parse URL to get path component
        $home_path = parse_url($url, PHP_URL_PATH) ?? '/';
        
        // Remove query string
        $path = strtok($request_uri, '?');
        
        // Remove home path prefix if WordPress is in a subdirectory
        if ($home_path !== '/' && strpos($path, $home_path) === 0) {
            $path = substr($path, strlen($home_path) - 1);
        }
        
        return $path ?: '/';
    }
    
    /**
     * Get current post ID if on a singular post page.
     *
     * @return int|null Post ID or null.
     */
    private static function get_current_post_id(): ?int
    {
        if (is_singular()) {
            $post_id = get_queried_object_id();
            return $post_id > 0 ? $post_id : null;
        }
        return null;
    }
}
