<?php
#
# swf_preview setup page
#

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'swf_preview';
$plugin_page_heading = $lang['swf_preview_configuration'];
$plugin_page_introtext=$lang['swf_preview_introtext'] . "<br /><br />";



// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_multi_rtype_select('swf_preview_resource_types', $lang['swf_preview_resource_types']);
$page_def[] = config_add_boolean_select('swf_preview_use_native_size', $lang['swf_preview_use_native_size']);



// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading,$plugin_page_introtext);
include '../../../include/footer.php';
