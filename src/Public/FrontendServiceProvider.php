<?php
namespace AIPM\Public_;

use AIPM\Public_\Output\MetaTags;
use AIPM\Public_\Badges\CredentialBadge;
use AIPM\Policies\HeadersGenerator;

/**
 * Frontend service provider for public-facing functionality.
 */
class FrontendServiceProvider
{
    /**
     * Register frontend hooks and actions.
     *
     * @return void
     */
    public static function register(): void
    {
        // Output meta robots tags
        add_action('wp_head', [MetaTags::class, 'output'], 0);
        
        // Send X-Robots-Tag headers
        add_action('template_redirect', [HeadersGenerator::class, 'send_headers'], 0);
        
        // Register C2PA badge shortcode
        add_action('init', [CredentialBadge::class, 'register_shortcode']);
    }
}
