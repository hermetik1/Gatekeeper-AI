<?php
namespace AIPM\Support; class Utils{ public static function sanitize_text($v){ return is_string($v)?sanitize_text_field($v):'';} public static function sanitize_array($a): array { if(!is_array($a)) return []; foreach($a as $k=>$v){ $a[$k]=is_array($v)?self::sanitize_array($v):self::sanitize_text($v);} return $a; } }
