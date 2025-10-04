<?php
namespace AIPM\Support;

/**
 * Plugin options management.
 */
class Options
{
    const KEY = 'gatekeeper_ai_settings';

    /**
     * Get plugin settings with defaults.
     *
     * @return array Settings array.
     */
    public static function get(): array
    {
        $defaults = [
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
        ];

        $options = get_option(self::KEY, []);
        
        if (!is_array($options)) {
            $options = [];
        }

        return array_replace_recursive($defaults, $options);
    }

    /**
     * Update plugin settings.
     *
     * @param array $values New settings values.
     * @return bool True on success.
     */
    public static function update(array $values): bool
    {
        // Sanitize and validate the input
        $sanitized = self::sanitize_settings($values);
        
        // Merge with defaults to ensure structure
        $defaults = self::get();
        $merged = array_replace_recursive($defaults, $sanitized);
        
        return update_option(self::KEY, $merged);
    }

    /**
     * Deep sanitization for settings array.
     *
     * @param array $settings Raw settings input.
     * @return array Sanitized settings.
     */
    private static function sanitize_settings(array $settings): array
    {
        $sanitized = [];

        // Sanitize policies
        if (isset($settings['policies']) && is_array($settings['policies'])) {
            $sanitized['policies'] = self::sanitize_policies($settings['policies']);
        }

        // Sanitize C2PA settings
        if (isset($settings['c2pa']) && is_array($settings['c2pa'])) {
            $sanitized['c2pa'] = [
                'enabled' => (bool) ($settings['c2pa']['enabled'] ?? false),
                'ai_assisted_default' => (bool) ($settings['c2pa']['ai_assisted_default'] ?? false)
            ];
        }

        // Sanitize logging settings
        if (isset($settings['logging']) && is_array($settings['logging'])) {
            $sanitized['logging'] = [
                'enabled' => (bool) ($settings['logging']['enabled'] ?? true)
            ];
        }

        // Sanitize debug settings
        if (isset($settings['debug']) && is_array($settings['debug'])) {
            $sanitized['debug'] = [
                'enabled' => (bool) ($settings['debug']['enabled'] ?? false)
            ];
        }

        return $sanitized;
    }

    /**
     * Sanitize policies section.
     *
     * @param array $policies Raw policies input.
     * @return array Sanitized policies.
     */
    private static function sanitize_policies(array $policies): array
    {
        $sanitized = [];

        // Sanitize global policies
        if (isset($policies['global']) && is_array($policies['global'])) {
            $sanitized['global'] = [
                'allow' => self::sanitize_bot_list($policies['global']['allow'] ?? []),
                'block' => self::sanitize_bot_list($policies['global']['block'] ?? [])
            ];
        }

        // Sanitize routes
        if (isset($policies['routes']) && is_array($policies['routes'])) {
            $sanitized['routes'] = self::sanitize_routes($policies['routes']);
        }

        // Sanitize per-post policies
        if (isset($policies['per_post']) && is_array($policies['per_post'])) {
            $sanitized['per_post'] = self::sanitize_per_post($policies['per_post']);
        }

        return $sanitized;
    }

    /**
     * Sanitize bot list (allow/block lists).
     *
     * @param mixed $bots Raw bot list.
     * @return array Sanitized bot list.
     */
    private static function sanitize_bot_list($bots): array
    {
        if (!is_array($bots)) {
            return [];
        }

        $sanitized = [];
        foreach ($bots as $bot) {
            if (is_string($bot)) {
                $clean = sanitize_text_field($bot);
                if (!empty($clean)) {
                    $sanitized[] = $clean;
                }
            }
        }

        return array_unique($sanitized);
    }

    /**
     * Sanitize routes.
     *
     * @param array $routes Raw routes input.
     * @return array Sanitized routes.
     */
    private static function sanitize_routes(array $routes): array
    {
        $sanitized = [];
        
        foreach ($routes as $route) {
            if (!is_array($route)) {
                continue;
            }

            $clean_route = [
                'pattern' => isset($route['pattern']) ? sanitize_text_field($route['pattern']) : '',
                'allow' => self::sanitize_bot_list($route['allow'] ?? []),
                'block' => self::sanitize_bot_list($route['block'] ?? [])
            ];

            if (!empty($clean_route['pattern'])) {
                $sanitized[] = $clean_route;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize per-post policies.
     *
     * @param array $per_post Raw per-post policies.
     * @return array Sanitized per-post policies.
     */
    private static function sanitize_per_post(array $per_post): array
    {
        $sanitized = [];

        foreach ($per_post as $post_id => $policy) {
            $clean_id = absint($post_id);
            if ($clean_id <= 0 || !is_array($policy)) {
                continue;
            }

            $sanitized[$clean_id] = [
                'policy' => isset($policy['policy']) ? sanitize_text_field($policy['policy']) : 'default',
                'allow' => self::sanitize_bot_list($policy['allow'] ?? []),
                'block' => self::sanitize_bot_list($policy['block'] ?? [])
            ];
        }

        return $sanitized;
    }
}
