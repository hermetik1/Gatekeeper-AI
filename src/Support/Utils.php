<?php
namespace AIPM\Support;

/**
 * Utility functions for sanitization and validation.
 */
class Utils
{
    /**
     * Sanitize text field value.
     *
     * @param mixed $value Value to sanitize.
     * @return string Sanitized string.
     */
    public static function sanitize_text($value)
    {
        return is_string($value) ? sanitize_text_field($value) : '';
    }

    /**
     * Recursively sanitize array values.
     *
     * @param mixed $array Array to sanitize.
     * @return array Sanitized array.
     */
    public static function sanitize_array($array): array
    {
        if (!is_array($array)) {
            return [];
        }

        foreach ($array as $key => $value) {
            $array[$key] = is_array($value) 
                ? self::sanitize_array($value) 
                : self::sanitize_text($value);
        }

        return $array;
    }
}
