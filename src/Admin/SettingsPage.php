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
        add_management_page(
            __('Gatekeeper AI', 'gatekeeper-ai'),
            __('Gatekeeper AI', 'gatekeeper-ai'),
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
        if ($hook !== 'tools_page_' . self::SLUG) {
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
        
        // Footer with ki Kraft branding
        echo '<div class="gkai-admin-footer">';
        echo '<p>';
        /* translators: %s: Link to ki Kraft website */
        printf(
            esc_html__('Built by %s', 'gatekeeper-ai'),
            '<a href="' . esc_url('https://kikraft.at/') . '" target="_blank" rel="noopener">' . esc_html(GKAI_BRAND) . '</a>'
        );
        echo '</p>';
        echo '</div>';
        
        echo '</div>';
    }
}
