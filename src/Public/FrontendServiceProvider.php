<?php
namespace AIPM\Public_;

use AIPM\Public_\Output\MetaTags;
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
    }
}
