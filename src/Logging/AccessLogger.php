<?php
namespace AIPM\Logging;

use AIPM\Support\Options;
use AIPM\Policies\BotDirectory;

/**
 * Lightweight GDPR-friendly access logger for AI bots.
 * Uses ring buffer (max 5000 entries) stored in options.
 */
class AccessLogger
{
    const OPTION_KEY = 'gatekeeper_ai_access_logs';
    const MAX_ENTRIES = 5000;

    /**
     * Log a bot access attempt.
     *
     * @param string $path URL path accessed.
     * @param string $user_agent User agent string.
     * @param string $matched_bot Matched bot name (from BotDirectory).
     * @param string $result 'allow' or 'block'.
     * @param string $source_rule Source of the rule: 'global', 'route', or 'per_post'.
     * @return bool True if logged successfully.
     */
    public static function log(string $path, string $user_agent, string $matched_bot, string $result, string $source_rule): bool
    {
        // Check if logging is enabled
        $options = Options::get();
        if (empty($options['logging']['enabled'])) {
            return false;
        }

        // Only log if bot is from our known directory
        $known_bots = array_column(BotDirectory::list(), 'name');
        if (!in_array($matched_bot, $known_bots, true)) {
            return false;
        }

        // Get existing logs
        $logs = get_option(self::OPTION_KEY, []);
        if (!is_array($logs)) {
            $logs = [];
        }

        // Create log entry (no PII - no IP addresses)
        $entry = [
            'timestamp' => current_time('mysql'),
            'path' => substr($path, 0, 255), // Limit path length
            'bot' => $matched_bot,
            'result' => $result, // 'allow' or 'block'
            'source' => $source_rule, // 'global', 'route', 'per_post'
        ];

        // Add to beginning of array (newest first)
        array_unshift($logs, $entry);

        // Implement ring buffer: keep only MAX_ENTRIES
        if (count($logs) > self::MAX_ENTRIES) {
            $logs = array_slice($logs, 0, self::MAX_ENTRIES);
        }

        // Save logs
        return update_option(self::OPTION_KEY, $logs, false);
    }

    /**
     * Get logs with optional filters.
     *
     * @param array $filters Optional filters: 'bot', 'since', 'until', 'result', 'limit'.
     * @return array Array of log entries.
     */
    public static function get_logs(array $filters = []): array
    {
        $logs = get_option(self::OPTION_KEY, []);
        if (!is_array($logs)) {
            $logs = [];
        }

        // Apply filters
        if (!empty($filters['bot'])) {
            $logs = array_filter($logs, function ($entry) use ($filters) {
                return $entry['bot'] === $filters['bot'];
            });
        }

        if (!empty($filters['since'])) {
            $since = strtotime($filters['since']);
            $logs = array_filter($logs, function ($entry) use ($since) {
                return strtotime($entry['timestamp']) >= $since;
            });
        }

        if (!empty($filters['until'])) {
            $until = strtotime($filters['until']);
            $logs = array_filter($logs, function ($entry) use ($until) {
                return strtotime($entry['timestamp']) <= $until;
            });
        }

        if (!empty($filters['result'])) {
            $logs = array_filter($logs, function ($entry) use ($filters) {
                return $entry['result'] === $filters['result'];
            });
        }

        // Apply limit
        $limit = $filters['limit'] ?? 200;
        $logs = array_slice($logs, 0, $limit);

        // Re-index array
        return array_values($logs);
    }

    /**
     * Get statistics from logs.
     *
     * @param int $days Number of days to analyze (7, 30, etc.).
     * @return array Statistics array.
     */
    public static function get_stats(int $days = 7): array
    {
        $logs = get_option(self::OPTION_KEY, []);
        if (!is_array($logs)) {
            $logs = [];
        }

        $since = strtotime("-{$days} days");
        
        // Filter logs by date range
        $filtered_logs = array_filter($logs, function ($entry) use ($since) {
            return strtotime($entry['timestamp']) >= $since;
        });

        // Count by bot
        $bots = [];
        $routes = [];
        $results = ['allow' => 0, 'block' => 0];

        foreach ($filtered_logs as $entry) {
            // Count by bot
            $bot = $entry['bot'] ?? 'unknown';
            if (!isset($bots[$bot])) {
                $bots[$bot] = 0;
            }
            $bots[$bot]++;

            // Count by route/path
            $path = $entry['path'] ?? '/';
            if (!isset($routes[$path])) {
                $routes[$path] = 0;
            }
            $routes[$path]++;

            // Count by result
            $result = $entry['result'] ?? 'unknown';
            if (isset($results[$result])) {
                $results[$result]++;
            }
        }

        // Sort by count (descending)
        arsort($bots);
        arsort($routes);

        return [
            'total_requests' => count($filtered_logs),
            'days' => $days,
            'top_bots' => array_slice($bots, 0, 10, true),
            'top_routes' => array_slice($routes, 0, 10, true),
            'results' => $results,
        ];
    }

    /**
     * Clear all logs.
     *
     * @return bool True if cleared successfully.
     */
    public static function clear_logs(): bool
    {
        return delete_option(self::OPTION_KEY);
    }
}
