<?php
namespace AIPM\Logging;

/**
 * Comprehensive logging system for Gatekeeper AI.
 * Provides multiple log levels and file-based logging.
 */
class Logger
{
    /**
     * Log levels
     */
    public const ERROR = 'ERROR';
    public const WARNING = 'WARNING';
    public const INFO = 'INFO';
    public const DEBUG = 'DEBUG';

    /**
     * Log file path
     *
     * @var string|null
     */
    private static ?string $log_file = null;

    /**
     * Whether logging is enabled
     *
     * @var bool|null
     */
    private static ?bool $enabled = null;

    /**
     * Current log level threshold
     *
     * @var string
     */
    private static string $level_threshold = self::INFO;

    /**
     * Log level priorities (lower = more severe)
     *
     * @var array<string, int>
     */
    private static array $level_priorities = [
        self::ERROR => 1,
        self::WARNING => 2,
        self::INFO => 3,
        self::DEBUG => 4,
    ];

    /**
     * Initialize logger
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$enabled !== null) {
            return;
        }

        // Check if logging is enabled in settings
        $settings = get_option('gatekeeper_ai_settings', []);
        self::$enabled = $settings['logging']['enabled'] ?? true;

        // Set log level from settings
        $log_level = $settings['logging']['level'] ?? self::INFO;
        if (isset(self::$level_priorities[$log_level])) {
            self::$level_threshold = $log_level;
        }

        // Determine log file path
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/gatekeeper-ai-logs';

        // Create log directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // Add .htaccess to protect logs
            file_put_contents(
                $log_dir . '/.htaccess',
                "deny from all\n"
            );
            
            // Add index.php to prevent directory listing
            file_put_contents(
                $log_dir . '/index.php',
                "<?php\n// Silence is golden.\n"
            );
        }

        self::$log_file = $log_dir . '/debug.log';

        // Register error handler if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::register_error_handler();
        }
    }

    /**
     * Register PHP error handler
     *
     * @return void
     */
    private static function register_error_handler(): void
    {
        set_error_handler([self::class, 'handle_php_error'], E_ALL);
        register_shutdown_function([self::class, 'handle_fatal_error']);
    }

    /**
     * Handle PHP errors
     *
     * @param int    $errno   Error number.
     * @param string $errstr  Error message.
     * @param string $errfile Error file.
     * @param int    $errline Error line.
     * @return bool
     */
    public static function handle_php_error(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Only log errors from our plugin
        if (strpos($errfile, 'gatekeeper-ai') === false) {
            return false;
        }

        $level = self::ERROR;
        $error_type = 'PHP Error';

        switch ($errno) {
            case E_WARNING:
            case E_USER_WARNING:
                $level = self::WARNING;
                $error_type = 'PHP Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $level = self::INFO;
                $error_type = 'PHP Notice';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $level = self::DEBUG;
                $error_type = 'PHP Deprecated';
                break;
        }

        $message = sprintf(
            '[%s] %s in %s on line %d',
            $error_type,
            $errstr,
            $errfile,
            $errline
        );

        self::log($level, $message);

        return false; // Let PHP's error handler also run
    }

    /**
     * Handle fatal errors
     *
     * @return void
     */
    public static function handle_fatal_error(): void
    {
        $error = error_get_last();
        
        if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        // Only log errors from our plugin
        if (strpos($error['file'], 'gatekeeper-ai') === false) {
            return;
        }

        $message = sprintf(
            '[FATAL ERROR] %s in %s on line %d',
            $error['message'],
            $error['file'],
            $error['line']
        );

        self::log(self::ERROR, $message, ['backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)]);
    }

    /**
     * Log a message
     *
     * @param string $level   Log level.
     * @param string $message Message to log.
     * @param array  $context Additional context data.
     * @return bool True if logged successfully.
     */
    public static function log(string $level, string $message, array $context = []): bool
    {
        if (!self::should_log($level)) {
            return false;
        }

        self::init();

        if (!self::$enabled || self::$log_file === null) {
            return false;
        }

        // Build log entry
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = sprintf(
            "[%s] [%s] %s\n",
            $timestamp,
            $level,
            $message
        );

        // Add context if provided
        if (!empty($context)) {
            $log_entry .= 'Context: ' . wp_json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }

        $log_entry .= "---\n";

        // Write to log file
        return error_log($log_entry, 3, self::$log_file) !== false;
    }

    /**
     * Check if a message should be logged based on level threshold
     *
     * @param string $level Log level to check.
     * @return bool
     */
    private static function should_log(string $level): bool
    {
        if (!isset(self::$level_priorities[$level])) {
            return false;
        }

        $message_priority = self::$level_priorities[$level];
        $threshold_priority = self::$level_priorities[self::$level_threshold];

        return $message_priority <= $threshold_priority;
    }

    /**
     * Log an error message
     *
     * @param string $message Message to log.
     * @param array  $context Additional context.
     * @return bool
     */
    public static function error(string $message, array $context = []): bool
    {
        return self::log(self::ERROR, $message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message Message to log.
     * @param array  $context Additional context.
     * @return bool
     */
    public static function warning(string $message, array $context = []): bool
    {
        return self::log(self::WARNING, $message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message Message to log.
     * @param array  $context Additional context.
     * @return bool
     */
    public static function info(string $message, array $context = []): bool
    {
        return self::log(self::INFO, $message, $context);
    }

    /**
     * Log a debug message
     *
     * @param string $message Message to log.
     * @param array  $context Additional context.
     * @return bool
     */
    public static function debug(string $message, array $context = []): bool
    {
        return self::log(self::DEBUG, $message, $context);
    }

    /**
     * Get log file path
     *
     * @return string|null
     */
    public static function get_log_file(): ?string
    {
        self::init();
        return self::$log_file;
    }

    /**
     * Get log entries
     *
     * @param int         $limit  Maximum number of entries.
     * @param string|null $level  Filter by level.
     * @param string|null $search Search term.
     * @return array<int, array<string, mixed>>
     */
    public static function get_entries(int $limit = 100, ?string $level = null, ?string $search = null): array
    {
        self::init();

        if (self::$log_file === null || !file_exists(self::$log_file)) {
            return [];
        }

        $content = file_get_contents(self::$log_file);
        if ($content === false) {
            return [];
        }

        // Split by entry separator
        $raw_entries = explode("---\n", $content);
        $entries = [];

        foreach ($raw_entries as $raw_entry) {
            $raw_entry = trim($raw_entry);
            if (empty($raw_entry)) {
                continue;
            }

            // Parse entry
            preg_match('/\[([^\]]+)\] \[([^\]]+)\] (.+)/s', $raw_entry, $matches);
            
            if (count($matches) < 4) {
                continue;
            }

            $entry = [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => $matches[3],
            ];

            // Apply filters
            if ($level !== null && $entry['level'] !== $level) {
                continue;
            }

            if ($search !== null && stripos($entry['message'], $search) === false) {
                continue;
            }

            $entries[] = $entry;
        }

        // Return most recent entries first
        $entries = array_reverse($entries);

        return array_slice($entries, 0, $limit);
    }

    /**
     * Clear log file
     *
     * @return bool
     */
    public static function clear(): bool
    {
        self::init();

        if (self::$log_file === null || !file_exists(self::$log_file)) {
            return true;
        }

        return unlink(self::$log_file);
    }

    /**
     * Get log file size
     *
     * @return int Size in bytes, 0 if file doesn't exist.
     */
    public static function get_size(): int
    {
        self::init();

        if (self::$log_file === null || !file_exists(self::$log_file)) {
            return 0;
        }

        $size = filesize(self::$log_file);
        return $size !== false ? $size : 0;
    }
}
