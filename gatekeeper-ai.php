<?php
/**
 * Plugin Name: Gatekeeper AI
 * Description: Granulare Kontrolle für AI-Crawler + Content-Provenance (C2PA-Light) in WordPress.
 * Version: 0.1.0
 * Author: ki Kraft
 * Author URI: https://kikraft.at/
 * Plugin URI: https://kikraft.at/
 * Text Domain: gatekeeper-ai
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */
defined('ABSPATH') || exit;
define('GKAI_VERSION', '0.1.0');
define('GKAI_FILE', __FILE__);
define('GKAI_PATH', plugin_dir_path(__FILE__));
define('GKAI_URL', plugin_dir_url(__FILE__));

// Load text domain
add_action('init', static function () {
    load_plugin_textdomain('gatekeeper-ai', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// Set up autoloader
if (file_exists(GKAI_PATH . 'vendor/autoload.php')) { 
    require GKAI_PATH . 'vendor/autoload.php'; 
}
else {
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'AIPM\\') !== 0) return;
        $rel = str_replace(['AIPM\\', '\\'], ['', '/'], $class);
        $file = GKAI_PATH . 'src/' . $rel . '.php';
        if (file_exists($file)) require $file;
    });
}

// Register activation hook with error handling
register_activation_hook(__FILE__, static function() {
    try {
        if (class_exists('AIPM\\Activation')) {
            AIPM\Activation::run();
        } else {
            throw new Exception('Activation class not found');
        }
    } catch (Exception $e) {
        // Log to WordPress debug.log if enabled
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Gatekeeper AI Activation Error: ' . $e->getMessage());
        }
        
        // Deactivate plugin to prevent further issues
        deactivate_plugins(plugin_basename(__FILE__));
        
        // Show error message
        wp_die(
            sprintf(
                '<h1>Plugin Activation Failed</h1><p>%s</p><p><a href="%s">Back to Plugins</a></p>',
                esc_html($e->getMessage()),
                esc_url(admin_url('plugins.php'))
            ),
            'Gatekeeper AI Activation Error',
            ['back_link' => true]
        );
    }
});

// Register deactivation hook
register_deactivation_hook(__FILE__, ['AIPM\\Deactivation', 'run']);

// Initialize plugin with error handling
add_action('plugins_loaded', static function () { 
    try {
        if (class_exists('AIPM\\Plugin')) { 
            AIPM\Plugin::init(); 
        } else {
            throw new Exception('Plugin class not found');
        }
    } catch (Exception $e) {
        // Log error
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Gatekeeper AI Initialization Error: ' . $e->getMessage());
        }
        
        // Show admin notice
        add_action('admin_notices', static function() use ($e) {
            printf(
                '<div class="notice notice-error"><p><strong>Gatekeeper AI Error:</strong> %s</p></div>',
                esc_html($e->getMessage())
            );
        });
    }
});

// Display activation errors if any
add_action('admin_notices', static function() {
    $error = get_transient('gatekeeper_ai_activation_error');
    if ($error) {
        delete_transient('gatekeeper_ai_activation_error');
        printf(
            '<div class="notice notice-error is-dismissible"><p><strong>Gatekeeper AI Activation Warning:</strong> %s</p></div>',
            esc_html($error)
        );
    }
});
