<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}

if (getval("submit","")!="")
	{
	$resourcetype=getvalescaped("resourcetype","");
	$videosplice_parent_field_set=getvalescaped("videosplice_parent_field","");
	
	$f=fopen("../config/config.php","w");
	fwrite($f,"<?php \$videosplice_resourcetype='$resourcetype'; \$videosplice_parent_field=$videosplice_parent_field_set; ?>");
	fclose($f);
	redirect("pages/team/team_plugins.php");
	}

	
$plugin_name = 'video_splice';
$page_heading = $lang['videospliceconfiguration'];

$page_def[]= config_add_single_rtype_select("videosplice_resourcetype",$lang["video_resource_type"]);
$page_def[]= config_add_single_ftype_select("videosplice_parent_field",$lang["parent_resource_field"]);


// Do the page generation ritual
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);
include '../../../include/footer.php';

