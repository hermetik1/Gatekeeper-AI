<?php
namespace AIPM;

use AIPM\Logging\Logger;

/**
 * Main plugin initialization class with comprehensive error handling.
 */
class Plugin
{
    /**
     * Whether plugin has been initialized
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Initialize the plugin.
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        try {
            Logger::info('=== Plugin Initialization Started ===');

            // Initialize Logger first
            Logger::init();

            // Register debug toolbar
            \AIPM\Debug\DebugToolbar::register();

            // Register admin functionality
            self::register_admin();
            Logger::debug('Admin service provider registered');
            
            // Register frontend functionality
            self::register_frontend();
            Logger::debug('Frontend service provider registered');
            
            // Register REST API routes
            self::register_rest_api();
            Logger::debug('REST API routes registered');
            
            // Register robots.txt handler
            self::register_robots_txt();
            Logger::debug('Robots.txt handler registered');
            
            // Register C2PA media attachment handler
            self::register_c2pa();
            Logger::debug('C2PA handler registered');

            self::$initialized = true;
            Logger::info('=== Plugin Initialization Completed ===');

        } catch (\Exception $e) {
            Logger::error('Plugin initialization failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Add admin notice for errors
            add_action('admin_notices', function () use ($e) {
                printf(
                    '<div class="notice notice-error"><p><strong>Gatekeeper AI Error:</strong> %s</p></div>',
                    esc_html($e->getMessage())
                );
            });
        }
    }

    /**
     * Register admin functionality
     *
     * @return void
     */
    private static function register_admin(): void
    {
        if (!class_exists('AIPM\\Admin\\AdminServiceProvider')) {
            error_log('Gatekeeper AI: Admin service provider class not found');
            
            add_action('admin_notices', function () {
                printf(
                    '<div class="notice notice-error"><p><strong>Gatekeeper AI Error:</strong> Admin service provider class not found. Please check that the plugin files are installed correctly.</p></div>',
                );
            });
            
            return;
        }

        add_action('init', [\AIPM\Admin\AdminServiceProvider::class, 'register']);
    }

    /**
     * Register frontend functionality
     *
     * @return void
     */
    private static function register_frontend(): void
    {
        // Try multiple namespace candidates for frontend service provider
        $candidates = [
            'AIPM\\Public_\\FrontendServiceProvider',
            'AIPM\\Public\\FrontendServiceProvider',
        ];

        $provider_class = null;
        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                $provider_class = $candidate;
                break;
            }
        }

        if ($provider_class === null) {
            error_log('Gatekeeper AI: Frontend service provider class not found. Tried: ' . implode(', ', $candidates));
            
            add_action('admin_notices', function () use ($candidates) {
                printf(
                    '<div class="notice notice-error"><p><strong>Gatekeeper AI Error:</strong> Frontend service provider class not found. Please check that the plugin files are installed correctly. Tried: %s</p></div>',
                    esc_html(implode(', ', $candidates))
                );
            });
            
            return;
        }

        add_action('init', [$provider_class, 'register']);
    }

    /**
     * Register REST API routes
     *
     * @return void
     */
    private static function register_rest_api(): void
    {
        if (!class_exists('AIPM\\REST\\Routes')) {
            error_log('Gatekeeper AI: REST routes class not found');
            return;
        }

        add_action('rest_api_init', [\AIPM\REST\Routes::class, 'register']);
    }

    /**
     * Register robots.txt handler
     *
     * @return void
     */
    private static function register_robots_txt(): void
    {
        if (!class_exists('AIPM\\Policies\\RobotsTxtGenerator')) {
            error_log('Gatekeeper AI: Robots.txt generator class not found');
            return;
        }

        add_action('do_robots', [\AIPM\Policies\RobotsTxtGenerator::class, 'output']);
    }

    /**
     * Register C2PA functionality
     *
     * @return void
     */
    private static function register_c2pa(): void
    {
        if (!class_exists('AIPM\\C2PA\\MediaAttachment')) {
            error_log('Gatekeeper AI: C2PA media attachment class not found');
            return;
        }

        add_action('init', [\AIPM\C2PA\MediaAttachment::class, 'register']);
    }
}
