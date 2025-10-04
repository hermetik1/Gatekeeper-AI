<?php
namespace AIPM\Policies;

use AIPM\Support\Options;

/**
 * Manages AI bot access policies with deterministic merge logic.
 * Priority: per_post > route > global
 */
class PolicyManager
{
    /**
     * Get raw policies from options.
     *
     * @return array Policies array with global, routes, and per_post keys.
     */
    public static function get(): array
    {
        $o = Options::get();
        return $o['policies'] ?? [
            'global' => ['allow' => [], 'block' => []],
            'routes' => [],
            'per_post' => []
        ];
    }

    /**
     * Get list of known bot names from BotDirectory.
     *
     * @return array Array of valid bot names.
     */
    private static function known_bots(): array
    {
        static $cache = null;
        if ($cache === null) {
            $bots = BotDirectory::list();
            $cache = array_column($bots, 'name');
        }
        return $cache;
    }

    /**
     * Merge policies for a given path with proper priority.
     * Priority: per_post > route > global
     * Route patterns support wildcards (*).
     *
     * @param string   $path    The URL path to check.
     * @param int|null $post_id Optional post ID for per-post overrides.
     * @return array Merged policy with 'allow' and 'block' keys (validated bot names only).
     */
    public static function merged_for_path(string $path, ?int $post_id = null): array
    {
        $p = self::get();
        $known = self::known_bots();
        
        // Start with global policies
        $result = [
            'allow' => array_intersect($p['global']['allow'] ?? [], $known),
            'block' => array_intersect($p['global']['block'] ?? [], $known)
        ];

        // Apply route-based policies (overrides global)
        $matching_routes = [];
        foreach (($p['routes'] ?? []) as $rule) {
            $pattern = $rule['pattern'] ?? '';
            if (empty($pattern)) {
                continue;
            }
            
            // Convert wildcard pattern to regex
            // Escape special regex chars, then convert * to .*
            $regex_pattern = preg_quote($pattern, '/');
            $regex_pattern = str_replace('\*', '.*', $regex_pattern);
            
            if (preg_match('/^' . $regex_pattern . '$/i', $path)) {
                // Store matching route with specificity for deterministic ordering
                // More specific patterns (fewer wildcards, longer) have higher priority
                $specificity = strlen($pattern) - substr_count($pattern, '*') * 100;
                $matching_routes[] = [
                    'rule' => $rule,
                    'specificity' => $specificity
                ];
            }
        }

        // Sort by specificity (most specific first) for deterministic collision resolution
        usort($matching_routes, function ($a, $b) {
            return $b['specificity'] <=> $a['specificity'];
        });

        // Apply matching routes in order of specificity
        foreach ($matching_routes as $match) {
            $rule = $match['rule'];
            $result['allow'] = array_values(array_unique(array_merge(
                $result['allow'],
                array_intersect($rule['allow'] ?? [], $known)
            )));
            $result['block'] = array_values(array_unique(array_merge(
                $result['block'],
                array_intersect($rule['block'] ?? [], $known)
            )));
        }

        // Apply per-post policies (highest priority)
        if ($post_id && isset($p['per_post'][$post_id])) {
            $post_policy = $p['per_post'][$post_id];
            $result['allow'] = array_values(array_unique(array_merge(
                $result['allow'],
                array_intersect($post_policy['allow'] ?? [], $known)
            )));
            $result['block'] = array_values(array_unique(array_merge(
                $result['block'],
                array_intersect($post_policy['block'] ?? [], $known)
            )));
        }

        // Remove duplicates and reindex
        $result['allow'] = array_values(array_unique($result['allow']));
        $result['block'] = array_values(array_unique($result['block']));

        return $result;
    }
}
