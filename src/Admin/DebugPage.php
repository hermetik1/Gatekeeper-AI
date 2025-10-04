<?php
namespace AIPM\Admin;

use AIPM\Logging\Logger;
use AIPM\Debug\SystemInfo;
use AIPM\Debug\HealthCheck;

/**
 * Debug dashboard admin page.
 */
class DebugPage
{
    /**
     * Register admin menu
     *
     * @return void
     */
    public static function menu(): void
    {
        add_submenu_page(
            'tools.php',
            __('Gatekeeper AI Debug', 'gatekeeper-ai'),
            __('GKAI Debug', 'gatekeeper-ai'),
            'manage_options',
            'gatekeeper-ai-debug',
            [self::class, 'render']
        );
    }

    /**
     * Handle AJAX actions
     *
     * @return void
     */
    public static function handle_ajax(): void
    {
        // Clear logs
        add_action('wp_ajax_gkai_clear_logs', function() {
            check_ajax_referer('gkai_debug_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized']);
                return;
            }

            Logger::clear();
            wp_send_json_success(['message' => 'Logs cleared successfully']);
        });

        // Export debug report
        add_action('wp_ajax_gkai_export_report', function() {
            check_ajax_referer('gkai_debug_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized']);
                return;
            }

            $report = self::generate_debug_report();
            wp_send_json_success(['report' => $report]);
        });

        // Run health check
        add_action('wp_ajax_gkai_health_check', function() {
            check_ajax_referer('gkai_debug_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized']);
                return;
            }

            $health = HealthCheck::run();
            wp_send_json_success(['health' => $health]);
        });
    }

    /**
     * Render debug page
     *
     * @return void
     */
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Handle form submissions
        if (isset($_POST['action']) && check_admin_referer('gkai_debug_action')) {
            self::handle_form_action();
        }

        $tab = $_GET['tab'] ?? 'logs';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Gatekeeper AI - Debug Dashboard', 'gatekeeper-ai'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=gatekeeper-ai-debug&tab=logs" class="nav-tab <?php echo $tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Logs', 'gatekeeper-ai'); ?>
                </a>
                <a href="?page=gatekeeper-ai-debug&tab=system" class="nav-tab <?php echo $tab === 'system' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('System Info', 'gatekeeper-ai'); ?>
                </a>
                <a href="?page=gatekeeper-ai-debug&tab=health" class="nav-tab <?php echo $tab === 'health' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Health Check', 'gatekeeper-ai'); ?>
                </a>
            </h2>

            <div class="gkai-debug-content" style="margin-top: 20px;">
                <?php
                switch ($tab) {
                    case 'logs':
                        self::render_logs_tab();
                        break;
                    case 'system':
                        self::render_system_tab();
                        break;
                    case 'health':
                        self::render_health_tab();
                        break;
                }
                ?>
            </div>
        </div>

        <style>
            .gkai-log-entry {
                padding: 10px;
                margin-bottom: 10px;
                background: #fff;
                border-left: 4px solid #ddd;
                font-family: monospace;
                font-size: 12px;
            }
            .gkai-log-entry.level-ERROR {
                border-left-color: #dc3232;
                background: #fef7f7;
            }
            .gkai-log-entry.level-WARNING {
                border-left-color: #f0b849;
                background: #fef9f5;
            }
            .gkai-log-entry.level-INFO {
                border-left-color: #00a0d2;
                background: #f7fcfe;
            }
            .gkai-log-entry.level-DEBUG {
                border-left-color: #826eb4;
                background: #f9f8fc;
            }
            .gkai-log-timestamp {
                color: #666;
                font-weight: bold;
            }
            .gkai-log-level {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-weight: bold;
                font-size: 10px;
                margin: 0 5px;
            }
            .gkai-log-level.ERROR { background: #dc3232; color: white; }
            .gkai-log-level.WARNING { background: #f0b849; color: white; }
            .gkai-log-level.INFO { background: #00a0d2; color: white; }
            .gkai-log-level.DEBUG { background: #826eb4; color: white; }
            .gkai-system-info table {
                width: 100%;
                border-collapse: collapse;
            }
            .gkai-system-info th,
            .gkai-system-info td {
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            .gkai-system-info th {
                background: #f5f5f5;
                font-weight: bold;
                width: 30%;
            }
            .gkai-health-item {
                padding: 15px;
                margin-bottom: 10px;
                background: #fff;
                border-left: 4px solid #ddd;
            }
            .gkai-health-item.status-pass {
                border-left-color: #46b450;
            }
            .gkai-health-item.status-warning {
                border-left-color: #f0b849;
            }
            .gkai-health-item.status-fail {
                border-left-color: #dc3232;
            }
        </style>
        <?php
    }

    /**
     * Render logs tab
     *
     * @return void
     */
    private static function render_logs_tab(): void
    {
        $level = $_GET['level'] ?? null;
        $search = $_GET['search'] ?? null;
        $limit = (int) ($_GET['limit'] ?? 100);

        $entries = Logger::get_entries($limit, $level, $search);
        $log_size = Logger::get_size();
        ?>
        <div class="gkai-logs-tab">
            <div class="tablenav top">
                <form method="get" style="display: inline-block;">
                    <input type="hidden" name="page" value="gatekeeper-ai-debug">
                    <input type="hidden" name="tab" value="logs">
                    
                    <select name="level">
                        <option value=""><?php esc_html_e('All Levels', 'gatekeeper-ai'); ?></option>
                        <option value="ERROR" <?php selected($level, 'ERROR'); ?>>ERROR</option>
                        <option value="WARNING" <?php selected($level, 'WARNING'); ?>>WARNING</option>
                        <option value="INFO" <?php selected($level, 'INFO'); ?>>INFO</option>
                        <option value="DEBUG" <?php selected($level, 'DEBUG'); ?>>DEBUG</option>
                    </select>
                    
                    <input type="text" name="search" value="<?php echo esc_attr($search ?? ''); ?>" 
                           placeholder="<?php esc_attr_e('Search...', 'gatekeeper-ai'); ?>">
                    
                    <select name="limit">
                        <option value="50" <?php selected($limit, 50); ?>>50 entries</option>
                        <option value="100" <?php selected($limit, 100); ?>>100 entries</option>
                        <option value="500" <?php selected($limit, 500); ?>>500 entries</option>
                        <option value="1000" <?php selected($limit, 1000); ?>>1000 entries</option>
                    </select>
                    
                    <button type="submit" class="button"><?php esc_html_e('Filter', 'gatekeeper-ai'); ?></button>
                </form>

                <form method="post" style="display: inline-block; margin-left: 10px;">
                    <?php wp_nonce_field('gkai_debug_action'); ?>
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="button button-secondary" 
                            onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs?', 'gatekeeper-ai'); ?>');">
                        <?php esc_html_e('Clear Logs', 'gatekeeper-ai'); ?>
                    </button>
                </form>

                <form method="post" style="display: inline-block; margin-left: 10px;">
                    <?php wp_nonce_field('gkai_debug_action'); ?>
                    <input type="hidden" name="action" value="export_report">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Export Debug Report', 'gatekeeper-ai'); ?>
                    </button>
                </form>

                <p style="margin-top: 10px;">
                    <strong><?php esc_html_e('Log File Size:', 'gatekeeper-ai'); ?></strong>
                    <?php echo esc_html(size_format($log_size)); ?>
                </p>
            </div>

            <?php if (empty($entries)): ?>
                <div class="notice notice-info" style="margin-top: 20px;">
                    <p><?php esc_html_e('No log entries found.', 'gatekeeper-ai'); ?></p>
                </div>
            <?php else: ?>
                <div class="gkai-log-entries" style="margin-top: 20px;">
                    <?php foreach ($entries as $entry): ?>
                        <div class="gkai-log-entry level-<?php echo esc_attr($entry['level']); ?>">
                            <span class="gkai-log-timestamp"><?php echo esc_html($entry['timestamp']); ?></span>
                            <span class="gkai-log-level <?php echo esc_attr($entry['level']); ?>">
                                <?php echo esc_html($entry['level']); ?>
                            </span>
                            <pre style="margin: 5px 0 0 0; white-space: pre-wrap;"><?php echo esc_html($entry['message']); ?></pre>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render system info tab
     *
     * @return void
     */
    private static function render_system_tab(): void
    {
        $info = SystemInfo::collect();
        ?>
        <div class="gkai-system-info">
            <h3><?php esc_html_e('System Information', 'gatekeeper-ai'); ?></h3>
            <table class="widefat">
                <tbody>
                    <?php foreach ($info as $section => $data): ?>
                        <tr>
                            <th colspan="2" style="background: #0073aa; color: white;">
                                <?php echo esc_html($section); ?>
                            </th>
                        </tr>
                        <?php foreach ($data as $key => $value): ?>
                            <tr>
                                <th><?php echo esc_html($key); ?></th>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <form method="post">
                    <?php wp_nonce_field('gkai_debug_action'); ?>
                    <input type="hidden" name="action" value="export_report">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Export System Info', 'gatekeeper-ai'); ?>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render health check tab
     *
     * @return void
     */
    private static function render_health_tab(): void
    {
        $health = HealthCheck::run();
        ?>
        <div class="gkai-health-check">
            <h3><?php esc_html_e('Plugin Health Check', 'gatekeeper-ai'); ?></h3>
            
            <?php foreach ($health as $check): ?>
                <div class="gkai-health-item status-<?php echo esc_attr($check['status']); ?>">
                    <h4><?php echo esc_html($check['label']); ?></h4>
                    <p><?php echo esc_html($check['message']); ?></p>
                    <?php if (!empty($check['details'])): ?>
                        <details>
                            <summary><?php esc_html_e('Details', 'gatekeeper-ai'); ?></summary>
                            <pre style="margin-top: 10px; background: #f5f5f5; padding: 10px; overflow: auto;"><?php 
                                echo esc_html(print_r($check['details'], true)); 
                            ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Handle form actions
     *
     * @return void
     */
    private static function handle_form_action(): void
    {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'clear_logs':
                Logger::clear();
                add_settings_error(
                    'gkai_debug',
                    'logs_cleared',
                    __('Logs cleared successfully.', 'gatekeeper-ai'),
                    'success'
                );
                break;

            case 'export_report':
                self::export_debug_report();
                break;
        }
    }

    /**
     * Generate debug report
     *
     * @return string Debug report content.
     */
    private static function generate_debug_report(): string
    {
        $report = "GATEKEEPER AI DEBUG REPORT\n";
        $report .= str_repeat('=', 80) . "\n\n";
        $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n\n";

        // System info
        $report .= "SYSTEM INFORMATION\n";
        $report .= str_repeat('-', 80) . "\n";
        $info = SystemInfo::collect();
        foreach ($info as $section => $data) {
            $report .= "\n" . $section . ":\n";
            foreach ($data as $key => $value) {
                $report .= sprintf("  %s: %s\n", $key, $value);
            }
        }

        // Health check
        $report .= "\n\nHEALTH CHECK\n";
        $report .= str_repeat('-', 80) . "\n";
        $health = HealthCheck::run();
        foreach ($health as $check) {
            $report .= sprintf(
                "\n[%s] %s: %s\n",
                strtoupper($check['status']),
                $check['label'],
                $check['message']
            );
        }

        // Recent logs
        $report .= "\n\nRECENT LOG ENTRIES (Last 50)\n";
        $report .= str_repeat('-', 80) . "\n";
        $entries = Logger::get_entries(50);
        foreach ($entries as $entry) {
            $report .= sprintf(
                "\n[%s] [%s] %s\n",
                $entry['timestamp'],
                $entry['level'],
                $entry['message']
            );
        }

        return $report;
    }

    /**
     * Export debug report as download
     *
     * @return void
     */
    private static function export_debug_report(): void
    {
        $report = self::generate_debug_report();
        $filename = sprintf('gatekeeper-ai-debug-%s.txt', date('Y-m-d-His'));

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($report));
        
        echo $report;
        exit;
    }
}
