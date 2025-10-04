<?php
namespace AIPM\REST;

/**
 * REST API nonce validation utilities.
 */
class Nonces
{
    /**
     * Check if the current REST request has a valid nonce.
     * 
     * Checks both HTTP_X_WP_NONCE header and _wpnonce parameter.
     *
     * @return bool True if nonce is valid, false otherwise.
     */
    public static function check(): bool
    {
        // Try HTTP header first (used by wp.apiFetch)
        $nonce = $_SERVER['HTTP_X_WP_NONCE'] ?? '';
        
        // Fall back to request parameter (used by some clients)
        if (empty($nonce)) {
            $nonce = $_REQUEST['_wpnonce'] ?? '';
        }
        
        if (empty($nonce)) {
            return false;
        }
        
        return (bool) wp_verify_nonce($nonce, 'wp_rest');
    }
}
