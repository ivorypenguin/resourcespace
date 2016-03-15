<?php
include dirname(__FILE__) . '/../../include/db.php';
include_once dirname(__FILE__) . '/../../include/general.php';
include dirname(__FILE__) . '/../../include/authenticate.php';
include dirname(__FILE__) . '/../../include/resource_functions.php';

$resource = getvalescaped('resource', '');
$ref = getvalescaped('ref', '');
$type = getvalescaped('type','');

$resource_data = get_resource_data($resource);

// User should have edit access to this resource!
if(!get_edit_access($resource, $resource_data['archive'], false, $resource_data)) {
	exit ('Permission denied.');
}

if($type=='user')
	{
	// Delete the user record from the database
	$query = sprintf('
			DELETE FROM resource_custom_access 
				  WHERE resource = "%s"
					AND user = "%s";
		',
		$resource,
		$ref
	);
	}
elseif($type=='usergroup')
	{
	// Delete the user record from the database
	$query = sprintf('
			DELETE FROM resource_custom_access 
				  WHERE resource = "%s"
					AND usergroup = "%s";
		',
		$resource,
		$ref
	);
	}
else
	{
	exit('No type');
	}
sql_query($query);
