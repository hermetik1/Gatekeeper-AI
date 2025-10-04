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
        
        // Collect all bots mentioned in policies
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
        
        // If no bots configured, output default open policy
        if (empty($all_bots)) {
            echo "User-agent: *\n";
            echo "Allow: /\n";
            return;
        }
        
        // Output directives for each bot
        foreach ($all_bots as $bot) {
            echo "User-agent: {$bot}\n";
            
            // Check if bot is globally blocked
            $is_globally_blocked = in_array($bot, $global['block'] ?? [], true);
            
            // Collect route-specific disallows for this bot
            $disallows = [];
            if ($is_globally_blocked) {
                // If globally blocked, disallow everything
                $disallows[] = '/';
            } else {
                // Check route-specific blocks
                foreach ($routes as $rule) {
                    $pattern = $rule['pattern'] ?? '';
                    if (empty($pattern)) {
                        continue;
                    }
                    
                    // If bot is blocked in this route, add to disallows
                    if (in_array($bot, $rule['block'] ?? [], true)) {
                        // Convert wildcard pattern to robots.txt path
                        // For robots.txt, we keep the pattern more literal
                        $disallow_path = str_replace('*', '', $pattern);
                        if (!in_array($disallow_path, $disallows, true)) {
                            $disallows[] = $disallow_path;
                        }
                    }
                }
            }
            
            if (!empty($disallows)) {
                foreach ($disallows as $path) {
                    echo "Disallow: {$path}\n";
                }
            } else {
                echo "Allow: /\n";
            }
            
            echo "\n";
        }
    }
}
