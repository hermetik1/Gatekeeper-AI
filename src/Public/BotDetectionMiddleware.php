<?php
namespace AIPM\Public;

use AIPM\Policies\PolicyManager;
use AIPM\Policies\BotDirectory;
use AIPM\Logging\AccessLogger;

/**
 * Middleware to detect and log bot requests.
 */
class BotDetectionMiddleware
{
    /**
     * Register hooks for bot detection and logging.
     *
     * @return void
     */
    public static function register(): void
    {
        // Hook early in the request cycle
        add_action('wp', [self::class, 'detect_and_log'], 5);
    }

    /**
     * Detect if current request is from a known bot and log it.
     *
     * @return void
     */
    public static function detect_and_log(): void
    {
        // Skip admin, AJAX, REST, and feed requests
        if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST') || is_feed()) {
            return;
        }

        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($user_agent)) {
            return;
        }

        // Check if it matches a known bot
        $matched_bot = self::match_bot($user_agent);
        if (!$matched_bot) {
            return;
        }

        // Get current path and post ID
        $path = self::get_current_path();
        $post_id = self::get_current_post_id();

        // Get merged policy for this path
        $policy = PolicyManager::merged_for_path($path, $post_id);

        // Determine result: blocked if bot is in block list and not in allow list
        // (Allow takes precedence if bot is in both, though validation should prevent this)
        $result = 'allow';
        $source_rule = 'global';

        if (in_array($matched_bot, $policy['block'], true)) {
            $result = 'block';
            
            // Determine source (simplified - could be enhanced to track exact source)
            if ($post_id) {
                $post_policies = PolicyManager::get()['per_post'][$post_id] ?? null;
                if ($post_policies && (in_array($matched_bot, $post_policies['block'] ?? [], true) || 
                                       in_array($matched_bot, $post_policies['allow'] ?? [], true))) {
                    $source_rule = 'per_post';
                } else {
                    // Check if any route matches
                    $routes = PolicyManager::get()['routes'] ?? [];
                    foreach ($routes as $route) {
                        $pattern = $route['pattern'] ?? '';
                        if (empty($pattern)) {
                            continue;
                        }
                        
                        // Check if route matches
                        $regex_pattern = preg_quote($pattern, '/');
                        $regex_pattern = str_replace('\*', '.*', $regex_pattern);
                        if (preg_match('/^' . $regex_pattern . '$/i', $path)) {
                            $source_rule = 'route';
                            break;
                        }
                    }
                }
            } else {
                // Check if any route matches
                $routes = PolicyManager::get()['routes'] ?? [];
                foreach ($routes as $route) {
                    $pattern = $route['pattern'] ?? '';
                    if (empty($pattern)) {
                        continue;
                    }
                    
                    $regex_pattern = preg_quote($pattern, '/');
                    $regex_pattern = str_replace('\*', '.*', $regex_pattern);
                    if (preg_match('/^' . $regex_pattern . '$/i', $path)) {
                        $source_rule = 'route';
                        break;
                    }
                }
            }
        }

        // Log the access
        AccessLogger::log($path, $user_agent, $matched_bot, $result, $source_rule);
    }

    /**
     * Match user agent against known bots.
     *
     * @param string $user_agent User agent string.
     * @return string|null Matched bot name or null.
     */
    private static function match_bot(string $user_agent): ?string
    {
        $bots = BotDirectory::list();
        
        foreach ($bots as $bot) {
            $regex = $bot['ua_regex'] ?? '';
            if (empty($regex)) {
                continue;
            }
            
            if (preg_match($regex, $user_agent)) {
                return $bot['name'];
            }
        }
        
        return null;
    }

    /**
     * Get the current request path relative to site root.
     *
     * @return string Current path.
     */
    private static function get_current_path(): string
    {
        $url = home_url('/');
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        
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
