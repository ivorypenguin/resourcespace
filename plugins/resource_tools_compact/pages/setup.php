<?php
#
# resource_tools_compact Setup
#
// Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include_once '../../../include/general.php';

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'resource_tools_compact';
$plugin_page_heading = $lang["resource_tools_compact_configtitle"];

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_single_ftype_select('r_tools_captionfield',$lang['resource_tools_compact_captionfield']);

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
