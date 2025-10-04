<?php
namespace AIPM\Policies;

/**
 * Generates robots.txt based on configured policies.
 * Supports route-specific rules and per-bot directives.
 */
class RobotsTxtGenerator
{
    /**
     * Output robots.txt content based on policies.
     * Called via do_robots hook.
     *
     * @return void
     */
    public static function output(): void
    {
        $p = PolicyManager::get();
        $global = $p['global'] ?? ['allow' => [], 'block' => []];
        $routes = $p['routes'] ?? [];
        
        header('Content-Type: text/plain; charset=utf-8');
        
        // Collect all bots mentioned in policies (deterministic order: alphabetically)
        $all_bots = array_unique(array_merge(
            $global['allow'] ?? [],
            $global['block'] ?? []
        ));
        
        // Also collect bots from route rules
        foreach ($routes as $rule) {
            $all_bots = array_unique(array_merge(
                $all_bots,
                $rule['allow'] ?? [],
                $rule['block'] ?? []
            ));
        }

        // Sort alphabetically for deterministic output
        sort($all_bots);
        
        // If no bots configured, output default open policy
        if (empty($all_bots)) {
            echo "User-agent: *\n";
            echo "Allow: /\n";
            return;
        }
        
        // Output directives for each bot (deterministic order)
        foreach ($all_bots as $bot) {
            echo "User-agent: {$bot}\n";
            
            // Check if bot is globally blocked
            $is_globally_blocked = in_array($bot, $global['block'] ?? [], true);
            $is_globally_allowed = in_array($bot, $global['allow'] ?? [], true);
            
            // Collect allows and disallows for this bot
            $allows = [];
            $disallows = [];
            
            if ($is_globally_blocked) {
                // If globally blocked, disallow everything by default
                $disallows[] = '/';
                
                // But check if specific routes override this (allow)
                foreach ($routes as $rule) {
                    $pattern = $rule['pattern'] ?? '';
                    if (empty($pattern)) {
                        continue;
                    }
                    
                    // If bot is explicitly allowed in this route, add to allows
                    if (in_array($bot, $rule['allow'] ?? [], true)) {
                        $path = self::normalize_robots_path($pattern);
                        if (!in_array($path, $allows, true)) {
                            $allows[] = $path;
                        }
                    }
                }
            } else {
                // Not globally blocked - allow by default
                // Check route-specific blocks
                foreach ($routes as $rule) {
                    $pattern = $rule['pattern'] ?? '';
                    if (empty($pattern)) {
                        continue;
                    }
                    
                    // If bot is blocked in this route, add to disallows
                    if (in_array($bot, $rule['block'] ?? [], true)) {
                        $path = self::normalize_robots_path($pattern);
                        if (!in_array($path, $disallows, true)) {
                            $disallows[] = $path;
                        }
                    }
                }
            }

            // Sort paths for deterministic output
            sort($allows);
            sort($disallows);

            // Output allows first (if any)
            if (!empty($allows)) {
                foreach ($allows as $path) {
                    echo "Allow: {$path}\n";
                }
            }
            
            // Then disallows
            if (!empty($disallows)) {
                foreach ($disallows as $path) {
                    echo "Disallow: {$path}\n";
                }
            } else if (empty($allows)) {
                // If nothing blocked and nothing explicitly allowed, allow all
                echo "Allow: /\n";
            }
            
            echo "\n";
        }
    }

    /**
     * Normalize path pattern for robots.txt format.
     * Converts wildcards to robots.txt compatible format.
     *
     * @param string $pattern Route pattern with potential wildcards.
     * @return string Normalized path for robots.txt.
     */
    private static function normalize_robots_path(string $pattern): string
    {
        // Ensure leading slash
        if (!str_starts_with($pattern, '/')) {
            $pattern = '/' . $pattern;
        }
        
        // For robots.txt, wildcard at the end means "everything under this path"
        // So /blog/* becomes /blog/
        // /uploads/*.pdf becomes /uploads/ (robots.txt doesn't support file extension wildcards well)
        if (str_contains($pattern, '*')) {
            // Remove the wildcard and everything after it
            $pattern = preg_replace('/\*.*$/', '', $pattern);
            // Ensure trailing slash for directory patterns
            if (!str_ends_with($pattern, '/') && $pattern !== '') {
                $pattern .= '/';
            }
        }
        
        return $pattern ?: '/';
    }
}
