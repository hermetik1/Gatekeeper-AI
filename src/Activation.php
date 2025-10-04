<?php
namespace AIPM;

use AIPM\Logging\Logger;

/**
 * Plugin activation handler with comprehensive error handling.
 */
class Activation
{
    /**
     * Run activation tasks.
     *
     * @return void
     */
    public static function run()
    {
        try {
            Logger::info('=== Plugin Activation Started ===');
            
            // Check WordPress version
            self::check_wordpress_version();
            Logger::info('WordPress version check passed');
            
            // Check PHP version
            self::check_php_version();
            Logger::info('PHP version check passed');
            
            // Initialize default settings
            self::initialize_settings();
            Logger::info('Settings initialized');
            
            // Initialize Logger after settings are ready
            Logger::init();
            Logger::info('Logger initialized');
            
            // Create required directories
            self::create_directories();
            Logger::info('Directories created');
            
            // Verify dependencies
            self::verify_dependencies();
            Logger::info('Dependencies verified');
            
            Logger::info('=== Plugin Activation Completed Successfully ===');
            
        } catch (\Exception $e) {
            Logger::error('Plugin activation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Store activation error for admin notice
            set_transient('gatekeeper_ai_activation_error', $e->getMessage(), 60);
            
            // Re-throw to prevent activation
            throw $e;
        }
    }

    /**
     * Check WordPress version requirement
     *
     * @return void
     * @throws \Exception If WordPress version is too old.
     */
    private static function check_wordpress_version(): void
    {
        global $wp_version;
        
        $required_version = '6.4';
        
        if (version_compare($wp_version, $required_version, '<')) {
            throw new \Exception(
                sprintf(
                    'Gatekeeper AI requires WordPress %s or higher. You are running WordPress %s.',
                    $required_version,
                    $wp_version
                )
            );
        }
    }

    /**
     * Check PHP version requirement
     *
     * @return void
     * @throws \Exception If PHP version is too old.
     */
    private static function check_php_version(): void
    {
        $required_version = '8.1';
        
        if (version_compare(PHP_VERSION, $required_version, '<')) {
            throw new \Exception(
                sprintf(
                    'Gatekeeper AI requires PHP %s or higher. You are running PHP %s.',
                    $required_version,
                    PHP_VERSION
                )
            );
        }
    }

    /**
     * Initialize default settings
     *
     * @return void
     */
    private static function initialize_settings(): void
    {
        // Initialize default settings if they don't exist
        if (!get_option('gatekeeper_ai_settings')) {
            $default_settings = [
                'policies' => [
                    'global' => [
                        'allow' => [],
                        'block' => []
                    ],
                    'routes' => [],
                    'per_post' => []
                ],
                'c2pa' => [
                    'enabled' => false,
                    'ai_assisted_default' => false
                ],
                'logging' => [
                    'enabled' => true,
                    'level' => Logger::INFO
                ],
                'debug' => [
                    'enabled' => defined('WP_DEBUG') && WP_DEBUG,
                    'profiling' => false
                ]
            ];
            
            add_option('gatekeeper_ai_settings', $default_settings);
        }
    }

    /**
     * Create required directories
     *
     * @return void
     * @throws \Exception If directory creation fails.
     */
    private static function create_directories(): void
    {
        $upload_dir = wp_upload_dir();
        
        $directories = [
            'logs' => $upload_dir['basedir'] . '/gatekeeper-ai-logs',
            'c2pa' => $upload_dir['basedir'] . '/gatekeeper-ai',
        ];

        foreach ($directories as $name => $path) {
            if (!file_exists($path)) {
                if (!wp_mkdir_p($path)) {
                    throw new \Exception(
                        sprintf('Failed to create %s directory: %s', $name, $path)
                    );
                }
                
                // Add protection files
                file_put_contents($path . '/.htaccess', "deny from all\n");
                file_put_contents($path . '/index.php', "<?php\n// Silence is golden.\n");
            }
        }
    }

    /**
     * Verify plugin dependencies
     *
     * @return void
     * @throws \Exception If required class doesn't exist.
     */
    private static function verify_dependencies(): void
    {
        $required_classes = [
            'AIPM\\Plugin',
            'AIPM\\Admin\\AdminServiceProvider',
            'AIPM\\Public_\\FrontendServiceProvider',
            'AIPM\\REST\\Routes',
            'AIPM\\Policies\\PolicyManager',
            'AIPM\\Policies\\RobotsTxtGenerator',
            'AIPM\\C2PA\\MediaAttachment',
        ];

        foreach ($required_classes as $class) {
            if (!class_exists($class)) {
                throw new \Exception(
                    sprintf('Required class not found: %s', $class)
                );
            }
        }
    }
}
