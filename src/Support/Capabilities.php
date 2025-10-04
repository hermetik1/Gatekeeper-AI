<?php
namespace AIPM\Support; class Capabilities{ public static function can_manage(): bool { return current_user_can('manage_options'); } }
