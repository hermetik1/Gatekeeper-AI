<?php
/**
 * Test REST API nonce validation.
 * 
 * This test verifies that REST API POST endpoints properly validate nonces.
 */

echo "Testing REST API Nonce Validation...\n";
echo "====================================\n\n";

// Mock WordPress nonce functions
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        // Simulate valid nonce
        if ($nonce === 'valid_nonce_12345' && $action === 'wp_rest') {
            return 1;
        }
        return false;
    }
}

// Set up autoloading
require_once __DIR__ . '/../vendor/autoload.php';

// Test 1: Check with valid nonce in HTTP header
echo "Test 1: Valid nonce in HTTP_X_WP_NONCE header\n";
$_SERVER['HTTP_X_WP_NONCE'] = 'valid_nonce_12345';
$_REQUEST = [];

$result = \AIPM\REST\Nonces::check();
if ($result === true) {
    echo "✓ PASS: Valid nonce accepted\n\n";
} else {
    echo "✗ FAIL: Valid nonce rejected\n\n";
    exit(1);
}

// Test 2: Check with invalid nonce in HTTP header
echo "Test 2: Invalid nonce in HTTP_X_WP_NONCE header\n";
$_SERVER['HTTP_X_WP_NONCE'] = 'invalid_nonce';
$_REQUEST = [];

$result = \AIPM\REST\Nonces::check();
if ($result === false) {
    echo "✓ PASS: Invalid nonce rejected\n\n";
} else {
    echo "✗ FAIL: Invalid nonce accepted\n\n";
    exit(1);
}

// Test 3: Check with valid nonce in request parameter (fallback)
echo "Test 3: Valid nonce in _wpnonce parameter (fallback)\n";
unset($_SERVER['HTTP_X_WP_NONCE']);
$_REQUEST['_wpnonce'] = 'valid_nonce_12345';

$result = \AIPM\REST\Nonces::check();
if ($result === true) {
    echo "✓ PASS: Valid nonce in parameter accepted\n\n";
} else {
    echo "✗ FAIL: Valid nonce in parameter rejected\n\n";
    exit(1);
}

// Test 4: Check with no nonce
echo "Test 4: No nonce provided\n";
unset($_SERVER['HTTP_X_WP_NONCE']);
$_REQUEST = [];

$result = \AIPM\REST\Nonces::check();
if ($result === false) {
    echo "✓ PASS: Missing nonce rejected\n\n";
} else {
    echo "✗ FAIL: Missing nonce accepted\n\n";
    exit(1);
}

// Test 5: Check with empty nonce
echo "Test 5: Empty nonce provided\n";
$_SERVER['HTTP_X_WP_NONCE'] = '';
$_REQUEST = [];

$result = \AIPM\REST\Nonces::check();
if ($result === false) {
    echo "✓ PASS: Empty nonce rejected\n\n";
} else {
    echo "✗ FAIL: Empty nonce accepted\n\n";
    exit(1);
}

// Test 6: Test permission_callback integration
echo "Test 6: Permission callback integration\n";

// Mock WordPress functions for permission check
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return $capability === 'manage_options';
    }
}

// Set valid nonce
$_SERVER['HTTP_X_WP_NONCE'] = 'valid_nonce_12345';

// Simulate what would happen in Routes.php
$can_manage = true; // Simulating Capabilities::can_manage() && is_user_logged_in()
$nonce_valid = \AIPM\REST\Nonces::check();
$permission_granted = $can_manage && $nonce_valid;

if ($permission_granted === true) {
    echo "✓ PASS: Permission granted with valid capability and nonce\n\n";
} else {
    echo "✗ FAIL: Permission denied despite valid capability and nonce\n\n";
    exit(1);
}

// Test 7: Permission denied without nonce
echo "Test 7: Permission denied without valid nonce\n";
$_SERVER['HTTP_X_WP_NONCE'] = 'invalid_nonce';

$can_manage = true;
$nonce_valid = \AIPM\REST\Nonces::check();
$permission_granted = $can_manage && $nonce_valid;

if ($permission_granted === false) {
    echo "✓ PASS: Permission denied without valid nonce\n\n";
} else {
    echo "✗ FAIL: Permission granted without valid nonce\n\n";
    exit(1);
}

echo "====================================\n";
echo "All REST nonce tests passed!\n";
exit(0);
