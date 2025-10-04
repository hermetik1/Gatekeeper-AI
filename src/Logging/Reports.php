<?php
namespace AIPM\Logging;

/**
 * Log reporting and analytics.
 */
class Reports
{
    /**
     * Get log statistics
     *
     * @param int $days Number of days to analyze.
     * @return array<string, mixed>
     */
    public static function get_statistics(int $days = 7): array
    {
        $entries = Logger::get_entries(10000); // Get large sample
        $cutoff_date = strtotime("-{$days} days");

        $stats = [
            'total' => 0,
            'by_level' => [
                Logger::ERROR => 0,
                Logger::WARNING => 0,
                Logger::INFO => 0,
                Logger::DEBUG => 0,
            ],
            'by_date' => [],
            'recent_errors' => [],
        ];

        foreach ($entries as $entry) {
            $timestamp = strtotime($entry['timestamp']);
            
            // Skip entries older than cutoff
            if ($timestamp < $cutoff_date) {
                continue;
            }

            $stats['total']++;

            // Count by level
            if (isset($stats['by_level'][$entry['level']])) {
                $stats['by_level'][$entry['level']]++;
            }

            // Count by date
            $date = date('Y-m-d', $timestamp);
            if (!isset($stats['by_date'][$date])) {
                $stats['by_date'][$date] = 0;
            }
            $stats['by_date'][$date]++;

            // Collect recent errors
            if ($entry['level'] === Logger::ERROR && count($stats['recent_errors']) < 10) {
                $stats['recent_errors'][] = $entry;
            }
        }

        return $stats;
    }

    /**
     * Get error trends
     *
     * @param int $days Number of days to analyze.
     * @return array<string, array<string, int>>
     */
    public static function get_trends(int $days = 30): array
    {
        $entries = Logger::get_entries(50000);
        $cutoff_date = strtotime("-{$days} days");

        $trends = [];

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $trends[$date] = [
                Logger::ERROR => 0,
                Logger::WARNING => 0,
                Logger::INFO => 0,
                Logger::DEBUG => 0,
            ];
        }

        foreach ($entries as $entry) {
            $timestamp = strtotime($entry['timestamp']);
            
            if ($timestamp < $cutoff_date) {
                continue;
            }

            $date = date('Y-m-d', $timestamp);
            
            if (isset($trends[$date]) && isset($trends[$date][$entry['level']])) {
                $trends[$date][$entry['level']]++;
            }
        }

        return $trends;
    }

    /**
     * Get most common errors
     *
     * @param int $limit Maximum number of errors to return.
     * @return array<int, array<string, mixed>>
     */
    public static function get_common_errors(int $limit = 10): array
    {
        $entries = Logger::get_entries(10000, Logger::ERROR);
        $errors = [];

        foreach ($entries as $entry) {
            // Extract error message (first line)
            $lines = explode("\n", $entry['message']);
            $error_key = $lines[0];

            if (!isset($errors[$error_key])) {
                $errors[$error_key] = [
                    'message' => $error_key,
                    'count' => 0,
                    'first_seen' => $entry['timestamp'],
                    'last_seen' => $entry['timestamp'],
                ];
            }

            $errors[$error_key]['count']++;
            $errors[$error_key]['last_seen'] = $entry['timestamp'];
        }

        // Sort by count
        usort($errors, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($errors, 0, $limit);
    }

    /**
     * Export logs as CSV
     *
     * @param int         $limit  Maximum number of entries.
     * @param string|null $level  Filter by level.
     * @param string|null $search Search term.
     * @return string CSV content.
     */
    public static function export_csv(int $limit = 1000, ?string $level = null, ?string $search = null): string
    {
        $entries = Logger::get_entries($limit, $level, $search);

        $csv = "Timestamp,Level,Message\n";

        foreach ($entries as $entry) {
            // Escape CSV values
            $timestamp = '"' . str_replace('"', '""', $entry['timestamp']) . '"';
            $level_val = '"' . str_replace('"', '""', $entry['level']) . '"';
            $message = '"' . str_replace('"', '""', $entry['message']) . '"';

            $csv .= "{$timestamp},{$level_val},{$message}\n";
        }

        return $csv;
    }

    /**
     * Get log summary
     *
     * @return array<string, mixed>
     */
    public static function get_summary(): array
    {
        $log_file = Logger::get_log_file();
        $log_size = Logger::get_size();
        $entries = Logger::get_entries(10000);

        $levels = [
            Logger::ERROR => 0,
            Logger::WARNING => 0,
            Logger::INFO => 0,
            Logger::DEBUG => 0,
        ];

        foreach ($entries as $entry) {
            if (isset($levels[$entry['level']])) {
                $levels[$entry['level']]++;
            }
        }

        return [
            'log_file' => $log_file,
            'log_size' => $log_size,
            'log_size_formatted' => size_format($log_size),
            'total_entries' => count($entries),
            'levels' => $levels,
            'oldest_entry' => !empty($entries) ? end($entries)['timestamp'] : null,
            'newest_entry' => !empty($entries) ? $entries[0]['timestamp'] : null,
        ];
    }
}

