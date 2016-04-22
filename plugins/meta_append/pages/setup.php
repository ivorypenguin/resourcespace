<?php
#
# meta_append setup page
#

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'meta_append';

$plugin_page_heading = $lang['meta_append_configuration'];

$page_def[] = config_add_single_ftype_select('meta_append_field_ref',$lang['meta_append_configuration_field_type']);
$page_def[] = config_add_text_input('meta_append_date_format',$lang['meta_append_configuration_date_format']);
$page_def[] = config_add_text_input('meta_append_prompt',$lang['meta_append_configuration_prompt']);


// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);

include '../../../include/footer.php';
