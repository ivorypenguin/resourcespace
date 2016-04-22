<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}


// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'resourceoftheday';
$plugin_page_heading = $lang['rotd-configuration'];

// Build the $page_def array of descriptions of each configuration variable the plugin uses.

$page_def[] = config_add_html("</br>" . $lang['specify-date-field'] . "</br></br>");
$page_def[] = config_add_single_ftype_select('rotd_field',$lang['rotd-field'],300,false,array(4,10));
$page_def[] = config_add_text_input('rotd_discount',$lang['rotd-discount']);

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading, $lang['intro-rotd-configuration']);


include '../../../include/footer.php';


