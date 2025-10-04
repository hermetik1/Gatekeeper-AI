<?php
namespace AIPM; class Activation { public static function run(){ if(!get_option('gatekeeper_ai_settings')) add_option('gatekeeper_ai_settings',[ 'policies'=>['global'=>['allow'=>[],'block'=>[]],'routes'=>[],'per_post'=>[]], 'c2pa'=>['enabled'=>false,'ai_assisted_default'=>false], 'logging'=>['enabled'=>true] ]); }}
