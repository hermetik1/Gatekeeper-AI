<?php
/**
 * Plugin Name: Gatekeeper AI
 * Description: Granulare Kontrolle für AI-Crawler + Content-Provenance (C2PA-Light) in WordPress. Von ki Kraft (Non-Profit, Österreich).
 * Version: 0.1.0
 * Author: ki Kraft
 * Author URI: https://kikraft.at/
 * Text Domain: gatekeeper-ai
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */
defined('ABSPATH') || exit;
define('GKAI_VERSION', '0.1.0');
define('GKAI_BRAND', 'ki Kraft');
define('GKAI_FILE', __FILE__);
define('GKAI_PATH', plugin_dir_path(__FILE__));
define('GKAI_URL', plugin_dir_url(__FILE__));
add_action('init', static function () {
    load_plugin_textdomain('gatekeeper-ai', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});
if (file_exists(GKAI_PATH . 'vendor/autoload.php')) { require GKAI_PATH . 'vendor/autoload.php'; }
else {
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'AIPM\\') !== 0) return;
        $rel = str_replace(['AIPM\\', '\\'], ['', '/'], $class);
        $file = GKAI_PATH . 'src/' . $rel . '.php';
        if (file_exists($file)) require $file;
    });
}
register_activation_hook(__FILE__, ['AIPM\\Activation', 'run']);
register_deactivation_hook(__FILE__, ['AIPM\\Deactivation', 'run']);
add_action('plugins_loaded', static function () { 
    if (class_exists('AIPM\\Plugin')) { 
        AIPM\Plugin::init(); 
    }
});
