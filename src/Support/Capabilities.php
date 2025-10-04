<?php
namespace AIPM\Support;

/**
 * Capability checks for plugin functionality.
 */
class Capabilities
{
    /**
     * Check if current user can manage plugin settings.
     *
     * @return bool True if user has manage_options capability.
     */
    public static function can_manage(): bool
    {
        return current_user_can('manage_options');
    }
}
