<?php
namespace AIPM\Public_\Output;

use AIPM\Policies\PolicyManager;

/**
 * Handles output of policy-based meta robots tags.
 */
class MetaTags
{
    /**
     * Output meta robots tags based on current request policy.
     * Hooked to wp_head with priority 0.
     *
     * @return void
     */
    public static function output(): void
    {
        // Skip in admin, AJAX, feed, or REST API contexts
        if (is_admin() || wp_doing_ajax() || is_feed() || defined('REST_REQUEST')) {
            return;
        }
        
        // Get current path and post ID
        $path = self::get_current_path();
        $post_id = self::get_current_post_id();
        
        // Get merged policy for current request
        $policy = PolicyManager::merged_for_path($path, $post_id);
        
        // If any bots are blocked, output restrictive meta tag
        if (!empty($policy['block'])) {
            // For MVP, use conservative directives when any bot is blocked
            // Future: could be more granular per bot
            echo '<!-- Gatekeeper AI Policy -->' . "\n";
            echo '<meta name="robots" content="noai, noimageai" />' . "\n";
            
            // Also output for specific bots if needed
            foreach ($policy['block'] as $bot) {
                $bot_safe = esc_attr(strtolower($bot));
                echo '<meta name="' . $bot_safe . '" content="noindex, nofollow" />' . "\n";
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
