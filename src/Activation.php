<?php
namespace AIPM;

/**
 * Plugin activation handler.
 */
class Activation
{
    /**
     * Run activation tasks.
     *
     * @return void
     */
    public static function run()
    {
        // Initialize default settings if they don't exist
        if (!get_option('gatekeeper_ai_settings')) {
            add_option('gatekeeper_ai_settings', [
                'policies' => [
                    'global' => [
                        'allow' => [],
                        'block' => []
                    ],
                    'routes' => [],
                    'per_post' => []
                ],
                'c2pa' => [
                    'enabled' => false,
                    'ai_assisted_default' => false
                ],
                'logging' => [
                    'enabled' => true
                ]
            ]);
        }
    }
}
