<?php
namespace AIPM\Debug;

use AIPM\Logging\Logger;

/**
 * Debug toolbar for developers.
 */
class DebugToolbar
{
    /**
     * Performance metrics
     *
     * @var array<string, mixed>
     */
    private static array $metrics = [];

    /**
     * Register debug toolbar
     *
     * @return void
     */
    public static function register(): void
    {
        $settings = get_option('gatekeeper_ai_settings', []);
        $debug_enabled = $settings['debug']['enabled'] ?? false;

        if (!$debug_enabled || !current_user_can('manage_options')) {
            return;
        }

        // Start performance tracking
        self::start_tracking();

        // Add toolbar to admin bar
        add_action('admin_bar_menu', [self::class, 'add_toolbar'], 100);
        
        // Output toolbar styles and scripts
        add_action('wp_footer', [self::class, 'output_assets']);
        add_action('admin_footer', [self::class, 'output_assets']);
    }

    /**
     * Start performance tracking
     *
     * @return void
     */
    private static function start_tracking(): void
    {
        self::$metrics['start_time'] = microtime(true);
        self::$metrics['start_memory'] = memory_get_usage();
    }

    /**
     * Add toolbar to admin bar
     *
     * @param \WP_Admin_Bar $wp_admin_bar WordPress admin bar.
     * @return void
     */
    public static function add_toolbar($wp_admin_bar): void
    {
        // Calculate metrics
        $execution_time = microtime(true) - self::$metrics['start_time'];
        $memory_usage = memory_get_usage() - self::$metrics['start_memory'];
        $peak_memory = memory_get_peak_usage();

        // Main menu
        $wp_admin_bar->add_menu([
            'id' => 'gkai-debug',
            'title' => sprintf(
                '<span class="ab-icon dashicons-before dashicons-admin-tools"></span>GKAI Debug <span class="gkai-badge">%s</span>',
                number_format($execution_time * 1000, 2) . 'ms'
            ),
            'href' => admin_url('admin.php?page=gatekeeper-ai-debug'),
        ]);

        // Performance submenu
        $wp_admin_bar->add_menu([
            'parent' => 'gkai-debug',
            'id' => 'gkai-debug-performance',
            'title' => sprintf(
                'Performance<br><small>Time: %sms | Memory: %s / %s peak</small>',
                number_format($execution_time * 1000, 2),
                size_format($memory_usage),
                size_format($peak_memory)
            ),
        ]);

        // Queries submenu
        $wp_admin_bar->add_menu([
            'parent' => 'gkai-debug',
            'id' => 'gkai-debug-queries',
            'title' => sprintf(
                'Database<br><small>%d queries</small>',
                get_num_queries()
            ),
        ]);

        // Hooks submenu
        $wp_admin_bar->add_menu([
            'parent' => 'gkai-debug',
            'id' => 'gkai-debug-hooks',
            'title' => sprintf(
                'Hooks<br><small>%d actions | %d filters</small>',
                self::count_hooks('action'),
                self::count_hooks('filter')
            ),
        ]);

        // Settings link
        $wp_admin_bar->add_menu([
            'parent' => 'gkai-debug',
            'id' => 'gkai-debug-settings',
            'title' => 'View Debug Dashboard →',
            'href' => admin_url('admin.php?page=gatekeeper-ai-debug'),
        ]);
    }

    /**
     * Count registered hooks
     *
     * @param string $type Hook type (action or filter).
     * @return int
     */
    private static function count_hooks(string $type): int
    {
        global $wp_filter;
        
        if ($type === 'action') {
            global $wp_actions;
            return count($wp_actions);
        }

        return count($wp_filter);
    }

    /**
     * Output toolbar assets
     *
     * @return void
     */
    public static function output_assets(): void
    {
        ?>
        <style>
            #wpadminbar #wp-admin-bar-gkai-debug .gkai-badge {
                display: inline-block;
                background: #00a0d2;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                margin-left: 5px;
            }
            #wpadminbar #wp-admin-bar-gkai-debug small {
                display: block;
                font-size: 11px;
                color: #999;
                margin-top: 3px;
            }
        </style>
        <?php
    }

    /**
     * Start profiling a section
     *
     * @param string $label Section label.
     * @return void
     */
    public static function start_profile(string $label): void
    {
        if (!isset(self::$metrics['profiles'])) {
            self::$metrics['profiles'] = [];
        }

        self::$metrics['profiles'][$label] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
        ];
    }

    /**
     * End profiling a section
     *
     * @param string $label Section label.
     * @return void
     */
    public static function end_profile(string $label): void
    {
        if (!isset(self::$metrics['profiles'][$label])) {
            return;
        }

        $profile = self::$metrics['profiles'][$label];
        $execution_time = microtime(true) - $profile['start_time'];
        $memory_usage = memory_get_usage() - $profile['start_memory'];

        self::$metrics['profiles'][$label]['execution_time'] = $execution_time;
        self::$metrics['profiles'][$label]['memory_usage'] = $memory_usage;

        // Log profile data
        Logger::debug(sprintf(
            'Profile [%s]: %sms, %s memory',
            $label,
            number_format($execution_time * 1000, 2),
            size_format($memory_usage)
        ));
    }

    /**
     * Get all profiling data
     *
     * @return array<string, mixed>
     */
    public static function get_profiles(): array
    {
        return self::$metrics['profiles'] ?? [];
    }
}
