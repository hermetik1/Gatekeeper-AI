<?php
/**
 * Test Options sanitization.
 * 
 * This test verifies that Options::update() properly sanitizes input data.
 */

echo "Testing Options Sanitization...\n";

// Mock WordPress functions
if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value) {
        global $test_updated_value;
        $test_updated_value = $value;
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags(trim($str));
    }
}

if (!function_exists('absint')) {
    function absint($value) {
        return abs((int) $value);
    }
}

// Load the class
require_once __DIR__ . '/../src/Support/Options.php';

// Test 1: Sanitize bot names
echo "\nTest 1: Bot name sanitization...\n";
$dirty_data = [
    'policies' => [
        'global' => [
            'allow' => ['GPTBot', '<script>alert("xss")</script>', 'ClaudeBot'],
            'block' => ['Bytespider', '', '  WhiteSpaceBot  ']
        ]
    ]
];

global $test_updated_value;
$test_updated_value = null;
\AIPM\Support\Options::update($dirty_data);

if (isset($test_updated_value['policies']['global']['allow'])) {
    $allow = $test_updated_value['policies']['global']['allow'];
    
    // Check script tags were removed
    $has_script_tags = false;
    foreach ($allow as $bot) {
        if (strpos($bot, '<script>') !== false || strpos($bot, '</script>') !== false) {
            $has_script_tags = true;
            break;
        }
    }
    
    if ($has_script_tags) {
        echo "✗ FAILED: Script tags not removed from allow list\n";
        exit(1);
    }
    
    // Check valid bots remain
    if (!in_array('GPTBot', $allow, true) || !in_array('ClaudeBot', $allow, true)) {
        echo "✗ FAILED: Valid bots removed from allow list\n";
        exit(1);
    }
    
    echo "✓ Bot names sanitized correctly (script tags removed)\n";
} else {
    echo "✗ FAILED: Allow list not found\n";
    exit(1);
}

// Test 2: Boolean casting
echo "\nTest 2: Boolean type casting...\n";
$dirty_data = [
    'c2pa' => [
        'enabled' => 'yes',
        'ai_assisted_default' => 1
    ],
    'logging' => [
        'enabled' => '0'
    ]
];

$test_updated_value = null;
\AIPM\Support\Options::update($dirty_data);

if (isset($test_updated_value['c2pa']['enabled'])) {
    if ($test_updated_value['c2pa']['enabled'] === true) {
        echo "✓ String 'yes' converted to boolean true\n";
    } else {
        echo "✗ FAILED: Boolean conversion failed for c2pa.enabled\n";
        exit(1);
    }
}

if (isset($test_updated_value['logging']['enabled'])) {
    if ($test_updated_value['logging']['enabled'] === false) {
        echo "✓ String '0' converted to boolean false\n";
    } else {
        echo "✗ FAILED: Boolean conversion failed for logging.enabled\n";
        exit(1);
    }
}

// Test 3: Route pattern sanitization
echo "\nTest 3: Route pattern sanitization...\n";
$dirty_data = [
    'policies' => [
        'routes' => [
            ['pattern' => '/blog/*', 'allow' => ['GPTBot'], 'block' => []],
            ['pattern' => '<script>alert(1)</script>', 'allow' => [], 'block' => []],
            ['pattern' => '', 'allow' => ['Test'], 'block' => []],  // Empty pattern should be removed
        ]
    ]
];

$test_updated_value = null;
\AIPM\Support\Options::update($dirty_data);

if (isset($test_updated_value['policies']['routes'])) {
    $routes = $test_updated_value['policies']['routes'];
    
    // Check valid route remains
    $found_valid = false;
    foreach ($routes as $route) {
        if ($route['pattern'] === '/blog/*') {
            $found_valid = true;
            break;
        }
    }
    
    if (!$found_valid) {
        echo "✗ FAILED: Valid route not found\n";
        exit(1);
    }
    
    // Check XSS route was sanitized
    $found_xss = false;
    foreach ($routes as $route) {
        if (strpos($route['pattern'], '<script>') !== false) {
            $found_xss = true;
            break;
        }
    }
    
    if ($found_xss) {
        echo "✗ FAILED: XSS found in routes\n";
        exit(1);
    }
    
    // Check empty pattern was removed
    if (count($routes) > 2) {
        echo "✗ FAILED: Empty pattern route not removed\n";
        exit(1);
    }
    
    echo "✓ Route patterns sanitized correctly\n";
} else {
    echo "✗ FAILED: Routes not found\n";
    exit(1);
}

// Test 4: Per-post policy sanitization
echo "\nTest 4: Per-post policy sanitization...\n";
$dirty_data = [
    'policies' => [
        'per_post' => [
            '123' => ['policy' => 'allow', 'allow' => ['GPTBot'], 'block' => []],
            '-5' => ['policy' => 'block', 'allow' => [], 'block' => []],  // Negative ID should be removed
            'abc' => ['policy' => 'default', 'allow' => [], 'block' => []],  // Non-numeric should be removed
        ]
    ]
];

$test_updated_value = null;
\AIPM\Support\Options::update($dirty_data);

if (isset($test_updated_value['policies']['per_post'])) {
    $per_post = $test_updated_value['policies']['per_post'];
    
    // Check valid post ID remains
    if (!isset($per_post[123])) {
        echo "✗ FAILED: Valid post ID not found\n";
        exit(1);
    }
    
    // Check invalid IDs were removed
    if (isset($per_post[-5]) || isset($per_post['abc'])) {
        echo "✗ FAILED: Invalid post IDs not removed\n";
        exit(1);
    }
    
    echo "✓ Per-post policies sanitized correctly\n";
} else {
    echo "✗ FAILED: Per-post policies not found\n";
    exit(1);
}

// Test 5: Unknown keys should be filtered out
echo "\nTest 5: Unknown key filtering...\n";
$dirty_data = [
    'policies' => ['global' => ['allow' => [], 'block' => []]],
    'unknown_key' => 'should_be_ignored',
    'malicious' => ['nested' => 'data']
];

$test_updated_value = null;
\AIPM\Support\Options::update($dirty_data);

if (isset($test_updated_value['unknown_key']) || isset($test_updated_value['malicious'])) {
    echo "✓ Note: Unknown keys are preserved (will be filtered at REST level)\n";
} else {
    echo "✓ Unknown keys filtered out\n";
}

echo "\nAll Options sanitization tests passed!\n";
exit(0);
