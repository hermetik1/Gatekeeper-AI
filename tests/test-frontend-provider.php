<?php
/**
 * Test frontend service provider loading.
 * 
 * This test verifies that the frontend provider loads correctly
 * and handles missing classes gracefully.
 */

echo "Testing Frontend Service Provider Loading...\n";
echo "============================================\n\n";

// Set up autoloading
require_once __DIR__ . '/../vendor/autoload.php';

// Test 1: Verify FrontendServiceProvider exists in Public_ namespace
echo "Test 1: FrontendServiceProvider class exists (AIPM\\Public_)\n";
$class_exists = class_exists('AIPM\\Public_\\FrontendServiceProvider');
if ($class_exists) {
    echo "✓ PASS: AIPM\\Public_\\FrontendServiceProvider class found\n\n";
} else {
    echo "✗ FAIL: AIPM\\Public_\\FrontendServiceProvider class not found\n\n";
    exit(1);
}

// Test 2: Verify BotDetectionMiddleware exists in Public namespace
echo "Test 2: BotDetectionMiddleware class exists (AIPM\\Public)\n";
$class_exists = class_exists('AIPM\\Public\\BotDetectionMiddleware');
if ($class_exists) {
    echo "✓ PASS: AIPM\\Public\\BotDetectionMiddleware class found\n\n";
} else {
    echo "✗ FAIL: AIPM\\Public\\BotDetectionMiddleware class not found\n\n";
    exit(1);
}

// Test 3: Verify MetaTags class exists
echo "Test 3: MetaTags class exists (AIPM\\Public_\\Output)\n";
$class_exists = class_exists('AIPM\\Public_\\Output\\MetaTags');
if ($class_exists) {
    echo "✓ PASS: AIPM\\Public_\\Output\\MetaTags class found\n\n";
} else {
    echo "✗ FAIL: AIPM\\Public_\\Output\\MetaTags class not found\n\n";
    exit(1);
}

// Test 4: Verify CredentialBadge class exists
echo "Test 4: CredentialBadge class exists (AIPM\\Public_\\Badges)\n";
$class_exists = class_exists('AIPM\\Public_\\Badges\\CredentialBadge');
if ($class_exists) {
    echo "✓ PASS: AIPM\\Public_\\Badges\\CredentialBadge class found\n\n";
} else {
    echo "✗ FAIL: AIPM\\Public_\\Badges\\CredentialBadge class not found\n\n";
    exit(1);
}

// Test 5: Test Plugin.php fallback logic simulation
echo "Test 5: Plugin fallback logic for frontend provider\n";
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

if ($provider_class === 'AIPM\\Public_\\FrontendServiceProvider') {
    echo "✓ PASS: Fallback logic found correct provider class\n\n";
} else {
    echo "✗ FAIL: Fallback logic failed to find provider class\n\n";
    exit(1);
}

// Test 6: Verify FrontendServiceProvider has register method
echo "Test 6: FrontendServiceProvider has register() method\n";
if (method_exists('AIPM\\Public_\\FrontendServiceProvider', 'register')) {
    echo "✓ PASS: register() method exists\n\n";
} else {
    echo "✗ FAIL: register() method not found\n\n";
    exit(1);
}

// Test 7: Test graceful handling of missing class (simulation)
echo "Test 7: Graceful handling of missing class\n";
$fake_candidates = [
    'AIPM\\NonExistent\\FrontendServiceProvider',
    'AIPM\\AlsoNonExistent\\FrontendServiceProvider',
];

$provider_class = null;
foreach ($fake_candidates as $candidate) {
    if (class_exists($candidate)) {
        $provider_class = $candidate;
        break;
    }
}

if ($provider_class === null) {
    echo "✓ PASS: Gracefully handles missing classes (no exception)\n\n";
} else {
    echo "✗ FAIL: Unexpected class found\n\n";
    exit(1);
}

// Test 8: Verify directory structure matches namespace
echo "Test 8: Directory structure matches namespace\n";
$frontend_file = __DIR__ . '/../src/Public_/FrontendServiceProvider.php';
$bot_file = __DIR__ . '/../src/Public/BotDetectionMiddleware.php';

if (file_exists($frontend_file) && file_exists($bot_file)) {
    echo "✓ PASS: Files in correct directories (Public_ and Public)\n\n";
} else {
    echo "✗ FAIL: Files not in expected directories\n";
    if (!file_exists($frontend_file)) {
        echo "  Missing: $frontend_file\n";
    }
    if (!file_exists($bot_file)) {
        echo "  Missing: $bot_file\n";
    }
    echo "\n";
    exit(1);
}

echo "============================================\n";
echo "All frontend provider loading tests passed!\n";
exit(0);
