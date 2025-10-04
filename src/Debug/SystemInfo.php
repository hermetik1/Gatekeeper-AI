<?php
namespace AIPM\Debug;

/**
 * System information collector.
 */
class SystemInfo
{
    /**
     * Collect system information
     *
     * @return array<string, array<string, string>>
     */
    public static function collect(): array
    {
        global $wp_version;

        return [
            'WordPress' => self::wordpress_info(),
            'PHP' => self::php_info(),
            'Server' => self::server_info(),
            'Plugin' => self::plugin_info(),
            'Active Plugins' => self::active_plugins(),
            'Theme' => self::theme_info(),
        ];
    }

    /**
     * Get WordPress information
     *
     * @return array<string, string>
     */
    private static function wordpress_info(): array
    {
        global $wp_version;

        return [
            'Version' => $wp_version,
            'Site URL' => get_site_url(),
            'Home URL' => get_home_url(),
            'Multisite' => is_multisite() ? 'Yes' : 'No',
            'Debug Mode' => (defined('WP_DEBUG') && WP_DEBUG) ? 'Enabled' : 'Disabled',
            'Debug Log' => (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) ? 'Enabled' : 'Disabled',
            'Memory Limit' => WP_MEMORY_LIMIT,
            'Max Memory Limit' => WP_MAX_MEMORY_LIMIT,
        ];
    }

    /**
     * Get PHP information
     *
     * @return array<string, string>
     */
    private static function php_info(): array
    {
        return [
            'Version' => PHP_VERSION,
            'SAPI' => PHP_SAPI,
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . 's',
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
            'Display Errors' => ini_get('display_errors') ? 'On' : 'Off',
            'Error Reporting' => self::get_error_reporting_level(),
            'Extensions' => implode(', ', self::get_relevant_extensions()),
        ];
    }

    /**
     * Get server information
     *
     * @return array<string, string>
     */
    private static function server_info(): array
    {
        return [
            'Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
            'HTTPS' => is_ssl() ? 'Yes' : 'No',
            'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'Request Time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
        ];
    }

    /**
     * Get plugin information
     *
     * @return array<string, string>
     */
    private static function plugin_info(): array
    {
        $settings = get_option('gatekeeper_ai_settings', []);
        
        return [
            'Version' => GKAI_VERSION,
            'Path' => GKAI_PATH,
            'URL' => GKAI_URL,
            'Logging Enabled' => ($settings['logging']['enabled'] ?? true) ? 'Yes' : 'No',
            'Log Level' => $settings['logging']['level'] ?? 'INFO',
            'C2PA Enabled' => ($settings['c2pa']['enabled'] ?? false) ? 'Yes' : 'No',
            'Debug Mode' => ($settings['debug']['enabled'] ?? false) ? 'Yes' : 'No',
        ];
    }

    /**
     * Get active plugins
     *
     * @return array<string, string>
     */
    private static function active_plugins(): array
    {
        $active_plugins = get_option('active_plugins', []);
        $plugins = [];

        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin, false, false);
            $plugins[$plugin_data['Name']] = $plugin_data['Version'];
        }

        return $plugins;
    }

    /**
     * Get theme information
     *
     * @return array<string, string>
     */
    private static function theme_info(): array
    {
        $theme = wp_get_theme();

        return [
            'Name' => $theme->get('Name'),
            'Version' => $theme->get('Version'),
            'Author' => $theme->get('Author'),
            'Template' => $theme->get('Template'),
        ];
    }

    /**
     * Get error reporting level
     *
     * @return string
     */
    private static function get_error_reporting_level(): string
    {
        $level = error_reporting();
        
        if ($level === E_ALL) {
            return 'E_ALL';
        }
        
        if ($level === 0) {
            return 'None';
        }

        $levels = [];
        $constants = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        foreach ($constants as $value => $name) {
            if ($level & $value) {
                $levels[] = $name;
            }
        }

        return implode(' | ', $levels);
    }

    /**
     * Get relevant PHP extensions
     *
     * @return array<int, string>
     */
    private static function get_relevant_extensions(): array
    {
        $extensions = ['json', 'mbstring', 'curl', 'gd', 'imagick', 'zip'];
        $loaded = [];

        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                $loaded[] = $ext;
            }
        }

        return $loaded;
    }
}
