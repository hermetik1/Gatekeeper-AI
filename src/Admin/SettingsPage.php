<?php
namespace AIPM\Admin;

use AIPM\Support\Capabilities;

/**
 * Admin settings page for Gatekeeper AI.
 */
class SettingsPage
{
    const SLUG = 'gatekeeper-ai';

    /**
     * Add settings page to WordPress admin menu.
     *
     * @return void
     */
    public static function menu(): void
    {
        // Add top-level menu
        add_menu_page(
            __('Gatekeeper AI', 'gatekeeper-ai'),
            __('Gatekeeper AI', 'gatekeeper-ai'),
            'manage_options',
            self::SLUG,
            [self::class, 'render'],
            'dashicons-shield-alt',
            30
        );

        // Add Dashboard submenu (points to same callback as top-level)
        add_submenu_page(
            self::SLUG,
            __('Dashboard', 'gatekeeper-ai'),
            __('Dashboard', 'gatekeeper-ai'),
            'manage_options',
            self::SLUG,
            [self::class, 'render']
        );
    }

    /**
     * Enqueue admin assets on settings page.
     *
     * @param string $hook Current admin page hook.
     * @return void
     */
    public static function assets($hook): void
    {
        // Accept both toplevel and submenu hooks
        $valid_hooks = [
            'toplevel_page_' . self::SLUG,
            self::SLUG . '_page_' . self::SLUG
        ];

        if (!in_array($hook, $valid_hooks, true)) {
            return;
        }

        wp_enqueue_style(
            'gatekeeper-ai-admin',
            GKAI_URL . 'assets/admin.css',
            [],
            GKAI_VERSION
        );

        wp_enqueue_script(
            'gatekeeper-ai-admin',
            GKAI_URL . 'src/Admin/Assets/index.js',
            ['wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components'],
            GKAI_VERSION,
            true
        );

        // Set script translations for i18n
        wp_set_script_translations(
            'gatekeeper-ai-admin',
            'gatekeeper-ai',
            dirname(GKAI_FILE) . '/languages'
        );

        wp_localize_script('gatekeeper-ai-admin', 'GKAI', [
            'nonce' => wp_create_nonce('wp_rest'),
            'rest' => esc_url_raw(rest_url('aipm/v1'))
        ]);
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public static function render(): void
    {
        if (!Capabilities::can_manage()) {
            wp_die(__('Insufficient permissions', 'gatekeeper-ai'));
        }

        echo '<div class="wrap">';
        echo '<div id="gkai-app">';
        echo '<p>' . esc_html__('Loading…', 'gatekeeper-ai') . '</p>';
        echo '</div>';
        echo '</div>';
    }
}
