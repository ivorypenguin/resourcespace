<?php

# Setup page for track_field_history plugin

# Do the include and authorization checking ritual.
include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

# Specify the name of this plugin, the heading to display for the page.
$plugin_name = 'track_field_history';
$page_heading = "Track Field History Configuration";

# Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_multi_ftype_select('track_fields', $lang['track_fields']);


# Do the page generation ritual
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);
include '../../../include/footer.php';

?>
