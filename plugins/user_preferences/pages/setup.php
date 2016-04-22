<?php

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
	exit($lang['error-permissiondenied']);

// Specify the name of this plugin, the heading to display for the page.
$plugin_name = 'user_preferences';
$page_heading = $lang['user_preferences_configuration'];

$choices = array($lang['no'], $lang['yes']);

// Build the config page
$page_def[] = config_add_boolean_select('user_preferences_change_username',
		$lang['user_preferences_change_username'], $choices);
$page_def[] = config_add_boolean_select('user_preferences_change_email',
		$lang['user_preferences_change_email'], $choices);
$page_def[] = config_add_boolean_select('user_preferences_change_name',
		$lang['user_preferences_change_name'], $choices);

$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);

include '../../../include/footer.php';
