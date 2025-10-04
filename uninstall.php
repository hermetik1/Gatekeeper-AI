<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
if (get_option('gatekeeper_ai_delete_data_on_uninstall')) { delete_option('gatekeeper_ai_settings'); }
