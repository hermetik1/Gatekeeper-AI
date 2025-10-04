#!/usr/bin/env php
<?php
/**
 * Simple test script to verify plugin classes can be loaded
 */

echo "Gatekeeper AI - Plugin Class Loading Test\n";
echo str_repeat('=', 50) . "\n\n";

// Set up minimal WordPress environment simulation
define('ABSPATH', '/tmp/wp/');
define('GKAI_VERSION', '0.1.0');
define('GKAI_FILE', __DIR__ . '/gatekeeper-ai.php');
define('GKAI_PATH', __DIR__ . '/');
define('GKAI_URL', 'http://example.com/wp-content/plugins/gatekeeper-ai/');
define('WP_DEBUG', true);

// Mock WordPress functions
function add_action() { return true; }
function add_filter() { return true; }
function register_rest_route() { return true; }
function get_option($key, $default = false) { return $default; }
function update_option() { return true; }
function add_option() { return true; }
function wp_upload_dir() {
    return [
        'basedir' => '/tmp/uploads',
        'baseurl' => 'http://example.com/wp-content/uploads'
    ];
}
function load_plugin_textdomain() {}
function plugin_basename() { return 'gatekeeper-ai/gatekeeper-ai.php'; }
function is_admin() { return false; }
function current_user_can() { return true; }
function size_format($bytes) { return $bytes . ' bytes'; }
function wp_mkdir_p($dir) { return @mkdir($dir, 0755, true); }
function is_ssl() { return false; }
function current_time($format) { return date($format); }
function get_site_url() { return 'http://example.com'; }
function get_home_url() { return 'http://example.com'; }
function is_multisite() { return false; }
function get_plugin_data($file, $markup = true, $translate = true) {
    return ['Name' => 'Test Plugin', 'Version' => '1.0'];
}
function wp_get_theme() {
    return new class {
        public function get($key) { return 'Test Theme'; }
    };
}
function wp_json_encode($data, $options = 0) { return json_encode($data, $options); }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_url($url) { return $url; }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }

if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);
if (!defined('WP_MEMORY_LIMIT')) define('WP_MEMORY_LIMIT', '40M');
if (!defined('WP_MAX_MEMORY_LIMIT')) define('WP_MAX_MEMORY_LIMIT', '256M');
if (!defined('WP_PLUGIN_DIR')) define('WP_PLUGIN_DIR', '/tmp/wp-content/plugins');

$GLOBALS['wp_version'] = '6.7';
$_SERVER['SERVER_SOFTWARE'] = 'Test Server';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test Agent';
$_SERVER['REQUEST_TIME'] = time();

// Set up autoloader
$plugin_root = dirname(__DIR__);
spl_autoload_register(function ($class) use ($plugin_root) {
    if (strpos($class, 'AIPM\\') !== 0) return;
    $rel = str_replace(['AIPM\\', '\\'], ['', '/'], $class);
    $file = $plugin_root . '/src/' . $rel . '.php';
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});

echo "Testing autoloader...\n";
echo "Plugin root: $plugin_root\n\n";

// Test core classes
$classes_to_test = [
    'AIPM\\Plugin',
    'AIPM\\Activation',
    'AIPM\\Deactivation',
    'AIPM\\Logging\\Logger',
    'AIPM\\Logging\\Reports',
    'AIPM\\Debug\\SystemInfo',
    'AIPM\\Debug\\HealthCheck',
    'AIPM\\Debug\\DebugToolbar',
    'AIPM\\Admin\\DebugPage',
    'AIPM\\Admin\\AdminServiceProvider',
    'AIPM\\Policies\\PolicyManager',
    'AIPM\\Policies\\RobotsTxtGenerator',
    'AIPM\\C2PA\\MediaAttachment',
];

$passed = 0;
$failed = 0;

foreach ($classes_to_test as $class) {
    if (class_exists($class)) {
        echo "✓ $class\n";
        $passed++;
    } else {
        echo "✗ $class NOT FOUND\n";
        $failed++;
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Results: $passed passed, $failed failed\n";

if ($failed > 0) {
    exit(1);
}

echo "\n✓ All classes loaded successfully!\n";
exit(0);
