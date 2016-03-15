<?php

// Do the include and authorization checking ritual
include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'checkmail';
$page_heading = $lang['checkmail_configuration'];
$last_checkmail=sql_value("select value from sysvars where name='last_checkmail'",""); 
$now=sql_value("select now() value","");
if (!extension_loaded("imap")){$page_intro=$lang['checkmail_install_php_imap_extension']."<br /><br />";}
else if ($last_checkmail==""){
	$page_intro = $lang['checkmail_cronhelp']."<br /><br />";
} else {
	$page_intro=str_replace("[lastcheck]",nicedate($last_checkmail,true),$lang['checkmail_lastcheck']."<br /><br />");
	$timediff=strtotime($now)-strtotime($last_checkmail);if ($timediff>300){$page_intro.=$lang['checkmail_cronjobprob'];}
}

// Build configuration variable descriptions
$page_def[]= config_add_text_input("checkmail_imap_server",$lang["checkmail_imap_server"]);
$page_def[]= config_add_text_input("checkmail_email",$lang["checkmail_email"]);
$page_def[]= config_add_text_input("checkmail_password",$lang["checkmail_password"],true);

$page_def[]= config_add_multi_user_select("checkmail_users",$lang["checkmail_users"]);
$page_def[]= config_add_single_ftype_select("checkmail_subject_field",$lang["checkmail_subject_field"]);
$page_def[]= config_add_single_ftype_select("checkmail_body_field",$lang["checkmail_body_field"]);
$page_def[]= config_add_single_select("checkmail_default_access",$lang["checkmail_default_access"],array(2=>$lang["access2"],1=>$lang["access1"],0=>$lang["access0"]));

$page_def[]= config_add_single_select("checkmail_default_archive",$lang["checkmail_default_archive"],array(-2=>$lang["status-2"],-1=>$lang["status-1"],0=>$lang["status0"],1=>$lang["status1"],2=>$lang["status2"],3=>$lang["status3"]));
$page_def[]= config_add_boolean_select("checkmail_html",$lang["checkmail_html"]);
$page_def[]= config_add_boolean_select("checkmail_purge",$lang["checkmail_purge"]);
$page_def[]= config_add_boolean_select("checkmail_confirm",$lang["checkmail_confirm"]);

// extensions. This technique of dynamic config form generation (based on installation-specifics) might be generally useful.
$page_def[]= config_add_section_header($lang['checkmail_extension_mapping'],$lang['checkmail_extension_mapping_desc']);
$page_def[]= config_add_single_rtype_select("checkmail_default_resource_type", $lang['checkmail_default_resource_type']);
$resource_types=get_resource_types();
foreach ($resource_types as $resource_type){
	$safe_varname="resourcetype".$resource_type['ref'];
	if (!isset($$safe_varname)){
		$$safe_varname=$resource_type['allowed_extensions'];
		if ($$safe_varname==""){
			$page_def[]= config_add_text_input($safe_varname,$resource_type['name']);
		} else {
			$page_def[]= config_add_text_input($safe_varname,$resource_type['name']." ".$lang['checkmail_resource_type_population']);
		}
	} else {
		$page_def[]= config_add_text_input($safe_varname,$resource_type['name']);
	}
}

// Do the page generation ritual
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
include '../../../include/footer.php';

	

