<?php

function HookAction_datesPagestoolscron_copy_hitcountAddplugincronjob()
	{
	global $lang, $action_dates_restrictfield,$action_dates_deletefield, $resource_deletion_state, $action_dates_reallydelete;
	
	
	$allowable_fields=sql_array("select ref as value from resource_type_field where type in (4,6,10)");
	# Check that this is a valid date field to use
	if(in_array($action_dates_restrictfield, $allowable_fields))
		{
		$restrict_resources=sql_query("select resource, value from resource_data where resource_type_field = '$action_dates_restrictfield'");
		
		foreach ($restrict_resources as $resource)
			{
			$ref=$resource["resource"];
			if (time()>=strtotime($resource["value"]))		
				{
				# Restrict access to the resource as date has been reached
				$existing_access=sql_value("select access as value from resource where ref='$ref'","");
				if($existing_access==0) # Only apply to resources that are currently open
					{
					echo "restricting resource " . $ref ."\r\n";
					sql_query("update resource set access=1 where ref='$ref'");
					resource_log($ref,'a','',$lang['action_dates_restrict_logtext'],$existing_access,1);		
					}
				}
			}
		}
	if(in_array($action_dates_deletefield, $allowable_fields))
		{
		$delete_resources=sql_query("select resource, value from resource_data where resource_type_field = '$action_dates_deletefield'");
		foreach ($delete_resources as $resource)
			{
			$ref=$resource["resource"];
			if (time()>=strtotime($resource["value"]))		
				{
				# Delete the resource as date has been reached
				echo "deleting resource " . $ref ."\r\n";
				if ($action_dates_reallydelete)
					{
					delete_resource($ref);
					}
				else
					{
					if (!isset($resource_deletion_state)){$resource_deletion_state=3;}
					sql_query("update resource set archive='" . $resource_deletion_state . "' where ref='" . $ref . "'");
					}
				# Remove the resource from any collections
				sql_query("delete from collection_resource where resource='$ref'");
				resource_log($ref,'x','',$lang['action_dates_delete_logtext']);			
				}	
			}
		}
	}
function HookAction_datesCron_copy_hitcountAddplugincronjob()
	{	
	HookAction_datesPagestoolscron_copy_hitcountAddplugincronjob();
	}
			