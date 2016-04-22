<?php
#
# search_tiles setup page
#

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
	
// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'search_tiles';
$plugin_page_heading = $lang['search_tiles_title'];

$page_def[] = config_add_boolean_select('search_tiles_text_shadow', $lang['search_tiles_text_shadow'],array(0=>$lang["no"],1=>$lang["yes"]));
$page_def[] = config_add_boolean_select('search_tiles_collection_count', $lang['search_tiles_collection_count'], array(0=>$lang["no"],1=>$lang["yes"]));

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
