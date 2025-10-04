<?php
namespace AIPM\REST; class Nonces{ public static function check(): bool { return wp_verify_nonce($_SERVER['HTTP_X_WP_NONCE']??'','wp_rest'); } }
