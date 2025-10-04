<?php
namespace AIPM\Debug;

/**
 * Plugin health check utility.
 */
class HealthCheck
{
    /**
     * Run all health checks
     *
     * @return array<int, array<string, mixed>>
     */
    public static function run(): array
    {
        return [
            self::check_php_version(),
            self::check_wordpress_version(),
            self::check_required_classes(),
            self::check_file_permissions(),
            self::check_autoloader(),
            self::check_settings(),
            self::check_directories(),
            self::check_dependencies(),
        ];
    }

    /**
     * Check PHP version
     *
     * @return array<string, mixed>
     */
    private static function check_php_version(): array
    {
        $required = '8.1';
        $current = PHP_VERSION;
        $status = version_compare($current, $required, '>=') ? 'pass' : 'fail';

        return [
            'label' => 'PHP Version',
            'status' => $status,
            'message' => sprintf(
                'Required: %s, Current: %s',
                $required,
                $current
            ),
        ];
    }

    /**
     * Check WordPress version
     *
     * @return array<string, mixed>
     */
    private static function check_wordpress_version(): array
    {
        global $wp_version;
        
        $required = '6.4';
        $current = $wp_version;
        $status = version_compare($current, $required, '>=') ? 'pass' : 'fail';

        return [
            'label' => 'WordPress Version',
            'status' => $status,
            'message' => sprintf(
                'Required: %s, Current: %s',
                $required,
                $current
            ),
        ];
    }

    /**
     * Check required classes exist
     *
     * @return array<string, mixed>
     */
    private static function check_required_classes(): array
    {
        $required_classes = [
            'AIPM\\Plugin',
            'AIPM\\Activation',
            'AIPM\\Deactivation',
            'AIPM\\Admin\\AdminServiceProvider',
            'AIPM\\Public_\\FrontendServiceProvider',
            'AIPM\\REST\\Routes',
            'AIPM\\Policies\\PolicyManager',
            'AIPM\\Policies\\RobotsTxtGenerator',
            'AIPM\\Policies\\HeadersGenerator',
            'AIPM\\C2PA\\MediaAttachment',
            'AIPM\\Logging\\Logger',
        ];

        $missing = [];
        foreach ($required_classes as $class) {
            if (!class_exists($class)) {
                $missing[] = $class;
            }
        }

        $status = empty($missing) ? 'pass' : 'fail';
        $message = empty($missing) 
            ? 'All required classes are loaded' 
            : 'Missing classes: ' . implode(', ', $missing);

        return [
            'label' => 'Required Classes',
            'status' => $status,
            'message' => $message,
            'details' => $missing,
        ];
    }

    /**
     * Check file permissions
     *
     * @return array<string, mixed>
     */
    private static function check_file_permissions(): array
    {
        $upload_dir = wp_upload_dir();
        $paths_to_check = [
            $upload_dir['basedir'],
            $upload_dir['basedir'] . '/gatekeeper-ai-logs',
            $upload_dir['basedir'] . '/gatekeeper-ai',
        ];

        $issues = [];
        foreach ($paths_to_check as $path) {
            if (!file_exists($path)) {
                continue;
            }

            if (!is_writable($path)) {
                $issues[] = $path;
            }
        }

        $status = empty($issues) ? 'pass' : 'warning';
        $message = empty($issues) 
            ? 'All directories are writable' 
            : 'Not writable: ' . implode(', ', $issues);

        return [
            'label' => 'File Permissions',
            'status' => $status,
            'message' => $message,
            'details' => $issues,
        ];
    }

    /**
     * Check autoloader
     *
     * @return array<string, mixed>
     */
    private static function check_autoloader(): array
    {
        $composer_autoload = GKAI_PATH . 'vendor/autoload.php';
        $has_composer = file_exists($composer_autoload);

        $status = 'pass';
        $message = $has_composer 
            ? 'Using Composer autoloader' 
            : 'Using fallback SPL autoloader';

        return [
            'label' => 'Autoloader',
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Check plugin settings
     *
     * @return array<string, mixed>
     */
    private static function check_settings(): array
    {
        $settings = get_option('gatekeeper_ai_settings');

        if (empty($settings)) {
            return [
                'label' => 'Plugin Settings',
                'status' => 'fail',
                'message' => 'Settings not initialized',
            ];
        }

        $required_keys = ['policies', 'c2pa', 'logging'];
        $missing = [];

        foreach ($required_keys as $key) {
            if (!isset($settings[$key])) {
                $missing[] = $key;
            }
        }

        $status = empty($missing) ? 'pass' : 'warning';
        $message = empty($missing) 
            ? 'All settings configured' 
            : 'Missing settings: ' . implode(', ', $missing);

        return [
            'label' => 'Plugin Settings',
            'status' => $status,
            'message' => $message,
            'details' => $missing,
        ];
    }

    /**
     * Check required directories
     *
     * @return array<string, mixed>
     */
    private static function check_directories(): array
    {
        $upload_dir = wp_upload_dir();
        $required_dirs = [
            'Logs' => $upload_dir['basedir'] . '/gatekeeper-ai-logs',
            'C2PA' => $upload_dir['basedir'] . '/gatekeeper-ai',
        ];

        $missing = [];
        foreach ($required_dirs as $name => $path) {
            if (!file_exists($path)) {
                $missing[] = $name . ' (' . $path . ')';
            }
        }

        $status = empty($missing) ? 'pass' : 'warning';
        $message = empty($missing) 
            ? 'All required directories exist' 
            : 'Missing directories: ' . implode(', ', $missing);

        return [
            'label' => 'Required Directories',
            'status' => $status,
            'message' => $message,
            'details' => $missing,
        ];
    }

    /**
     * Check dependencies
     *
     * @return array<string, mixed>
     */
    private static function check_dependencies(): array
    {
        $required_functions = [
            'wp_upload_dir',
            'add_action',
            'add_filter',
            'register_rest_route',
            'get_option',
            'update_option',
        ];

        $missing = [];
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                $missing[] = $func;
            }
        }

        $status = empty($missing) ? 'pass' : 'fail';
        $message = empty($missing) 
            ? 'All WordPress functions available' 
            : 'Missing functions: ' . implode(', ', $missing);

        return [
            'label' => 'WordPress Dependencies',
            'status' => $status,
            'message' => $message,
            'details' => $missing,
        ];
    }
}
