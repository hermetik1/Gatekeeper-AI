<?php
namespace AIPM; class Plugin { public static function init(): void { add_action('init',[\AIPM\Admin\AdminServiceProvider::class,'register']); add_action('init',[\AIPM\Public_\FrontendServiceProvider::class,'register']); add_action('rest_api_init',[\AIPM\REST\Routes::class,'register']); add_action('do_robots',[\AIPM\Policies\RobotsTxtGenerator::class,'output']); }}
