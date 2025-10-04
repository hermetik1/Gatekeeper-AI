<?php
namespace AIPM\Admin;

/**
 * Admin service provider for backend functionality.
 */
class AdminServiceProvider
{
    /**
     * Register admin hooks and actions.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!is_admin()) {
            return;
        }

        // Register settings page
        add_action('admin_menu', [SettingsPage::class, 'menu']);
        
        // Register debug page
        add_action('admin_menu', [DebugPage::class, 'menu']);
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [SettingsPage::class, 'assets']);
        
        // Register post metaboxes
        add_action('add_meta_boxes', ['AIPM\\Admin\\MetaBoxes\\PostPolicyMetaBox', 'register']);
        
        // Save post metabox data
        add_action('save_post', ['AIPM\\Admin\\MetaBoxes\\PostPolicyMetaBox', 'save']);
        
        // Handle debug page AJAX actions
        DebugPage::handle_ajax();

        // Add admin footer text
        add_filter('admin_footer_text', [self::class, 'admin_footer_text']);
    }

    /**
     * Customize admin footer text on Gatekeeper AI pages.
     *
     * @param string $text Default footer text.
     * @return string Modified footer text.
     */
    public static function admin_footer_text(string $text): string
    {
        $screen = get_current_screen();
        
        // Only show on Gatekeeper AI pages
        if ($screen && (strpos($screen->id, 'gatekeeper-ai') !== false)) {
            $text = sprintf(
                __('Built by %s', 'gatekeeper-ai'),
                '<a href="https://kikraft.at/" target="_blank" rel="noopener noreferrer">ki Kraft</a>'
            );
        }
        
        return $text;
    }
}
