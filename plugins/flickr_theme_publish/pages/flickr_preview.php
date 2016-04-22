<?php
// grabs preview image to show while publishing
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/resource_functions.php";

$ref=getvalescaped("ref","");
if($ref!=''){
	$path=get_resource_path($ref,false,"thm",false);
	
	$title=sql_value("select value from resource_data where resource_type_field=$view_title_field and resource=$ref","");
	$title=i18n_get_translated($title);
	
	$description=sql_value("select value from resource_data where resource_type_field=$flickr_caption_field and resource=$ref","");
	$keywords=sql_value("select value from resource_data where resource_type_field=$flickr_keywords_field and resource=$ref","");
	$photoid=sql_value("select flickr_photo_id value from resource where ref=$ref","");
	
	$results=array($path,$title,$description,$keywords,$photoid);
	echo json_encode($results);
}
?>
