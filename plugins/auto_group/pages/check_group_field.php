<?php

include '../../../include/db.php';
include '../../../include/general.php';
include '../../../include/resource_functions.php';

$field_id=$_POST['data'];

# check the field to make sure it's properly set up for multi-tennant use

$field_data=get_field($field_id);

$error_list='';
if($field_data['resource_type']!='0'){
	$error_list.=$lang['auto_group_field_errors_indent'].$lang['auto_group_field_not_global']."<br/>";
}
if(($field_data['type']!='3') && ($field_data['type']!='2')){
	$error_list.=$lang['auto_group_field_errors_indent'].$lang['auto_group_field_not_dropdown']."<br/>";
}
if($field_data['keywords_index']!=1){
	$error_list.=$lang['auto_group_field_errors_indent'].$lang['auto_group_field_not_indexed']."<br/>";
}
if($field_data['name']==''){
	$error_list.=$lang['auto_group_field_errors_indent'].$lang['auto_group_field_no_shortname'];
}

if($error_list!=''){
	# add a starting line to the list of issues with the chosen field
	die($lang['auto_group_field_errors']."<br/>".$error_list);
}
else{
	echo 'true';
}