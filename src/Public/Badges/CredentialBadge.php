<?php
namespace AIPM\Public_\Badges;

use AIPM\C2PA\ManifestBuilder;

/**
 * Renders credential badge for content with C2PA manifest.
 */
class CredentialBadge
{
    /**
     * Get badge HTML for an attachment if manifest exists.
     *
     * @param int $attachment_id Attachment ID.
     * @return string Badge HTML or empty string.
     */
    public static function get_badge(int $attachment_id): string
    {
        $manifest = ManifestBuilder::get_manifest($attachment_id);
        
        if ($manifest === null) {
            return '';
        }

        $ai_assisted = !empty($manifest['ai_assisted']);
        $creator = esc_html($manifest['creator'] ?? '');
        $created_at = '';
        
        if (!empty($manifest['created_at'])) {
            $timestamp = strtotime($manifest['created_at']);
            if ($timestamp) {
                $created_at = date_i18n(get_option('date_format'), $timestamp);
            }
        }

        // Build badge HTML
        $badge = '<div class="gkai-c2pa-badge" style="display: inline-block; padding: 8px 12px; background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 12px; margin: 5px 0;">';
        $badge .= '<span class="dashicons dashicons-shield" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; vertical-align: middle;"></span>';
        $badge .= '<strong>' . esc_html__('Content Provenance', 'gatekeeper-ai') . '</strong><br>';
        
        if ($creator) {
            $badge .= '<span>' . sprintf(esc_html__('Creator: %s', 'gatekeeper-ai'), $creator) . '</span><br>';
        }
        
        if ($created_at) {
            $badge .= '<span>' . sprintf(esc_html__('Created: %s', 'gatekeeper-ai'), $created_at) . '</span><br>';
        }
        
        if ($ai_assisted) {
            $badge .= '<span style="color: #2271b1;">' . esc_html__('🤖 AI-Assisted', 'gatekeeper-ai') . '</span>';
        }
        
        $badge .= '</div>';

        return $badge;
    }

    /**
     * Display badge for an attachment.
     *
     * @param int $attachment_id Attachment ID.
     * @return void
     */
    public static function display_badge(int $attachment_id): void
    {
        echo self::get_badge($attachment_id);
    }

    /**
     * Get badge shortcode handler.
     * Usage: [gkai_badge id="123"]
     *
     * @param array $atts Shortcode attributes.
     * @return string Badge HTML.
     */
    public static function shortcode($atts): string
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, 'gkai_badge');

        $attachment_id = absint($atts['id']);
        
        if ($attachment_id <= 0) {
            return '';
        }

        return self::get_badge($attachment_id);
    }

    /**
     * Register shortcode.
     *
     * @return void
     */
    public static function register_shortcode(): void
    {
        add_shortcode('gkai_badge', [self::class, 'shortcode']);
    }
}
