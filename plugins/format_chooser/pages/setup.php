<?php

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
	exit($lang['error-permissiondenied']);

// Specify the name of this plugin, the heading to display for the page.
$plugin_name = 'format_chooser';
$page_heading = $lang['format_chooser_configuration'];

// Build the config page
$page_def[] = config_add_text_list_input('format_chooser_input_formats', $lang['format_chooser_input_formats']);
$page_def[] = config_add_text_list_input('format_chooser_output_formats', $lang['format_chooser_output_formats']);

$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);
echo '<p>Please consult config.php directly in order to change the color profile settings.</p>';

include '../../../include/footer.php';
