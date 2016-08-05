<?php
function auto_group_get_group_templates($groups){
	# gets all the usergroup info for each group in the array or list

	if(is_array($groups) && array_key_exists('0',$groups)){
		$grouplist='';
		for($n=0;$n<count($groups);$n++){
			if($n>0){$grouplist.=",".$groups[$n];}
			else{$grouplist.=$groups[$n];}
		}
	}
	else{
		$grouplist=$groups;
	}
			
	return sql_query("select * from usergroup where ref in($grouplist)");
}
function auto_group_create_new_group($group_name,$group_username,$copy_group,$group_parent,$send_user_email=false){
	# takes data from new_group.php and sets up new group user, new group group, adds the group name to the group field dropdown list.
	global $auto_group_field;
	# add group option to group field
	echo "adding new group name to ".$auto_group_field."<br/>";
	$wait=auto_group_add_option_to_node($auto_group_field, $group_name);
	# get shortname of group field
	$group_shortname=auto_group_get_group_field_shortname();
	# copy group
	$group_template=get_usergroup($copy_group);
	$sql_columns='';
	$sql_values='';
	$count=0;
	foreach($group_template as $column => $value){
		if($column=='ref'){continue;}
		else{
			if($column=='search_filter' || $column=='resource_defaults' || $column=='edit_filter'){
				$value=$group_shortname."=".$group_name;
			}
			if($column=='name'){
				$value=$group_name;
			}
			if($column=='parent'){
				$value=$group_parent;
			}
			if($count!=0){
				$sql_columns.=',';
				$sql_values.=',';
			}
			$sql_columns.=$column;
			$sql_values.="'".$value."'";
			$count++;
		}
	}			
	$wait=sql_query("insert into usergroup($sql_columns) values($sql_values)");
	# get the new group's ID
	$new_group_id=sql_insert_id();
	
	$username=$group_username;
	$usergroup=$new_group_id;
	
	# create a new user account for the group
	$wait=sql_query("insert into user (username,usergroup,approved) values ('" . $username . "','" . $usergroup . "',1)");
	$new_user_id=sql_insert_id();

	return $new_user_id;
}
function auto_group_add_option_to_node($field, $option){
	# adds option to dropdown list
	//include_once __DIR__ ."/../../../include/node_functions.php";
	# verify field exists and option isn't already there
	$exists=sql_array("select name value from node where resource_type_field=$field");
	if((!empty($exists) && !in_array($option,$exists)) || empty($exists))
		{
		set_node(NULL, $field, $option, '', '');
		}
}
function auto_group_get_group_field_shortname(){
	global $auto_group_field;
	return sql_value("select name value from resource_type_field where ref=$auto_group_field","");
}
function auto_group_get_groups(){
	$group_ids=auto_group_get_group_usergroups(true);
	if(empty($group_ids)){
		return false;
	}
	else{
		$group_ids_string='';
		foreach($group_ids as $id){
			$group_ids_string.=$id.",";
		}
	}
	$group_ids_string=rtrim($group_ids_string, ",");
	$groups=sql_query("select * from users where usergroup in($group_ids_string) order by username");
	return $groups;
}
function auto_group_get_group_usergroups($id_only=false){
	global $group_field;
	$group_names=sql_value("select options value from resource_type_field where ref=$group_field",'');
	if(empty($group_names)){
		return false;
	}
	else{
		$group_names=explode(",",$group_names);
		$auto_groups='';
		foreach($group_names as $group_name){
			if($group_name!=''){
				$group_name=ltrim($group_name);
				$auto_groups.="'".addslashes($group_name)."',";
			}
		}
		$auto_groups=rtrim($auto_groups, ",");
		if($id_only==true){
			$group_ids=sql_query("select ref from usergroup where name in($auto_groups)");
			if(empty($_group_ids)){
				return false;
			}
			else{
				$group_ids_string='';
				foreach($group_ids as $id){
					$ggroup_ids_string.=$id.",";
				}
			}
			$group_ids_string=rtrim($group_ids_string, ",");
			return $group_ids_string;
		}
		else{
			$group_data=sql_query("select * from usergroup where name in($auto_groups)");
			if(empty($group_data)){
				return false;
			}
			else{
				return $group_data;
			}
		}
	}
}
function auto_group_get_group_parents(){
	global $auto_group_parent;
	
	$parents=sql_query("select * from usergroup where ref='$auto_group_parent'");
	return $parents;
}
	
	
