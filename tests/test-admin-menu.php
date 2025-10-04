<?php
/**
 * Test admin menu registration.
 * 
 * This test verifies that Gatekeeper AI is registered as a top-level menu
 * with the correct submenus.
 */

// Simple test to verify menu structure
echo "Testing Admin Menu Registration...\n";

// Mock WordPress globals
global $menu, $submenu;
$menu = [];
$submenu = [];

// Mock WordPress functions needed for menu registration
if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon = '', $position = null) {
        global $menu;
        $menu[] = [
            'title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'slug' => $menu_slug,
            'callback' => $callback,
            'icon' => $icon,
            'position' => $position
        ];
        return $menu_slug;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback) {
        global $submenu;
        if (!isset($submenu[$parent_slug])) {
            $submenu[$parent_slug] = [];
        }
        $submenu[$parent_slug][] = [
            'title' => $page_title,
            'menu_title' => $menu_title,
            'capability' => $capability,
            'slug' => $menu_slug,
            'callback' => $callback
        ];
        return $menu_slug;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

// Load the class
require_once __DIR__ . '/../src/Support/Capabilities.php';
require_once __DIR__ . '/../src/Admin/SettingsPage.php';
require_once __DIR__ . '/../src/Admin/DebugPage.php';

// Register menus
\AIPM\Admin\SettingsPage::menu();
\AIPM\Admin\DebugPage::menu();

// Test 1: Check that top-level menu is registered
$found_toplevel = false;
foreach ($menu as $item) {
    if ($item['slug'] === 'gatekeeper-ai' && $item['icon'] === 'dashicons-shield-alt') {
        $found_toplevel = true;
        echo "✓ Top-level menu 'Gatekeeper AI' registered with dashicons-shield-alt\n";
        break;
    }
}

if (!$found_toplevel) {
    echo "✗ FAILED: Top-level menu not found\n";
    exit(1);
}

// Test 2: Check that Dashboard submenu exists
$found_dashboard = false;
if (isset($submenu['gatekeeper-ai'])) {
    foreach ($submenu['gatekeeper-ai'] as $item) {
        if ($item['slug'] === 'gatekeeper-ai') {
            $found_dashboard = true;
            echo "✓ Dashboard submenu registered\n";
            break;
        }
    }
}

if (!$found_dashboard) {
    echo "✗ FAILED: Dashboard submenu not found\n";
    exit(1);
}

// Test 3: Check that Debug submenu exists
$found_debug = false;
if (isset($submenu['gatekeeper-ai'])) {
    foreach ($submenu['gatekeeper-ai'] as $item) {
        if ($item['slug'] === 'gatekeeper-ai-debug') {
            $found_debug = true;
            echo "✓ GKAI Debug submenu registered under Gatekeeper AI\n";
            break;
        }
    }
}

if (!$found_debug) {
    echo "✗ FAILED: Debug submenu not found under Gatekeeper AI\n";
    exit(1);
}

echo "\nAll admin menu tests passed!\n";
exit(0);
