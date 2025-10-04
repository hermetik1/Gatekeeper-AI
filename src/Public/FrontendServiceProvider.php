<?php
namespace AIPM\Public_; use AIPM\Public_\Output\MetaTags; class FrontendServiceProvider{ public static function register(): void { add_action('wp_head',[MetaTags::class,'output'],0); } }
