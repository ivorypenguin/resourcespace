<?php
#
# propose_changes setup page
#

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

global $baseurl;
// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'propose_changes';
$plugin_page_heading = $lang['propose_changes_configuration'];

// Build the $page_def array of descriptions of each configuration variable the plugin uses.


$page_def[] = config_add_boolean_select('propose_changes_always_allow',$lang['propose_changes_always_allow']);
$page_def[] = config_add_boolean_select('propose_changes_allow_open',$lang['propose_changes_allow_open']);
$page_def[] = config_add_boolean_select('propose_changes_notify_admin',$lang['propose_changes_notify_admin']);
$page_def[] = config_add_boolean_select('propose_changes_notify_contributor',$lang['propose_changes_notify_contributor']);
$page_def[] = config_add_text_list_input('propose_changes_notify_addresses', $lang['propose_changes_notify_addresses']);


// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);

include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);

include '../../../include/footer.php';
