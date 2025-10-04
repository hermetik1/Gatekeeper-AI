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
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [SettingsPage::class, 'assets']);
        
        // Register post metaboxes
        add_action('add_meta_boxes', ['AIPM\\Admin\\MetaBoxes\\PostPolicyMetaBox', 'register']);
        
        // Save post metabox data
        add_action('save_post', ['AIPM\\Admin\\MetaBoxes\\PostPolicyMetaBox', 'save']);
    }
}
