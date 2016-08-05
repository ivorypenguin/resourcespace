<?php
#
# image_text setup page
#

include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include '../../../include/general.php';

$identify_fullpath = get_utility_path("im-identify");
if ($identify_fullpath==false) {exit($lang['image_text_noim']);}

$identcommand = $identify_fullpath . ' -list font | grep Font:';
$identoutput=run_command($identcommand);

# Get a list of available fonts from IM 
$imfonts=explode("\n", $identoutput);
$imfontcount=count($imfonts);
for($n=0;$n<$imfontcount;$n++)
	{
	$imfonts[$n]=trim_spaces(str_replace("Font: ","",$imfonts[$n]));
	}
sort($imfonts, SORT_NATURAL);

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'image_text';
$plugin_page_heading = $lang['image_text_configuration'];

// Build the $page_def array of descriptions of each configuration variable the plugin uses.

$page_def[] = config_add_html($lang['image_text_summary']);
$page_def[] = config_add_single_ftype_select('image_text_field_select',$lang['image_text_field_select']);
$page_def[] = config_add_multi_rtype_select('image_text_restypes',$lang['image_text_restypes']);
$page_def[] = config_add_text_list_input('image_text_filetypes', $lang['image_text_filetypes']);
$page_def[] = config_add_multi_group_select('image_text_override_groups',$lang['image_text_override_groups']);
$page_def[] = config_add_text_input('image_text_default_text',$lang['image_text_default_text']);

$page_def[] = config_add_single_select('image_text_font',$lang['image_text_font'], array_filter($imfonts), false);
$page_def[] = config_add_single_select('image_text_position',$lang['image_text_position'], array("east","west","center"), false);
$page_def[] = config_add_single_select('image_text_banner_position',$lang['image_text_banner_position'], array("top","bottom"), false);
//$page_def[] = config_add_text_input('image_text_font',$lang['image_text_font']);

$page_def[] = config_add_text_input('image_text_height_proportion',$lang['image_text_height_proportion']);
$page_def[] = config_add_text_input('image_text_max_height',$lang['image_text_max_height']);
$page_def[] = config_add_text_input('image_text_min_height',$lang['image_text_min_height']);



// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';

config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);



include '../../../include/footer.php';
