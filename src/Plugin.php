<?php
namespace AIPM;

/**
 * Main plugin initialization class.
 */
class Plugin
{
    /**
     * Initialize the plugin.
     *
     * @return void
     */
    public static function init(): void
    {
        // Register admin functionality
        add_action('init', [\AIPM\Admin\AdminServiceProvider::class, 'register']);
        
        // Register frontend functionality
        add_action('init', [\AIPM\Public_\FrontendServiceProvider::class, 'register']);
        
        // Register REST API routes
        add_action('rest_api_init', [\AIPM\REST\Routes::class, 'register']);
        
        // Register robots.txt handler
        add_action('do_robots', [\AIPM\Policies\RobotsTxtGenerator::class, 'output']);
        
        // Register C2PA media attachment handler
        add_action('init', [\AIPM\C2PA\MediaAttachment::class, 'register']);
    }
}
