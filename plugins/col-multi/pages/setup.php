<?php
#
# Responsive Setup
#
// Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include_once '../../../include/general.php';
include '../languages/en.php';

global $slimheader;
// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'col-multi';
$plugin_page_heading = $lang["multi_configtitle"];

#Currently only Slimheader config available.
if(!$slimheader)
	{
	include '../../../include/header.php';
	?>
	<h2><?php echo $lang["multi_configtitle"]; ?></h2>
	<br />
	<p><?php echo $lang["no-options-available"];?></p>
	<?php
	include '../../../include/footer.php';
	exit;
	}

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_text_input('linkedheaderimgsrc',$lang['linkedheaderimgsrc']);
if(empty($_POST["linkedheaderimgsrc"]))
	{
	$_POST["linkedheaderimgsrc"]="";
	}
	
$page_def[] = config_add_boolean_select('slimheader_darken',$lang["slimheader_darken"]);

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
