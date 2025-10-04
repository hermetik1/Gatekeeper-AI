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
        return update_option(self::KEY, $values);
    }
}
