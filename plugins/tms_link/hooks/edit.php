<?php


include dirname(__FILE__) . "/../include/tms_link_functions.php";

function HookTms_linkEditEditbeforesectionhead()
	{
	global $lang,$baseurl,$tms_link_object_id_field, $ref,$resource,$tms_confirm_upload,$tms_link_resource_types;
	if($ref<0 && in_array($resource["resource_type"],$tms_link_resource_types))
		{	
		?>
		<div class="Question" id="question_tms_link">
			<label for="question_tms_link"><?php echo $lang["tms_link_upload_tms_field"]?></label>
			<input type="text" id="field_<?php echo $tms_link_object_id_field ?>" name="field_<?php echo $tms_link_object_id_field ?>" value="<?php echo htmlspecialchars(sql_value("select value from resource_data where resource='" . $ref . "' and resource_type_field='" . $tms_link_object_id_field . "'","")) ?>">
			
			<div class="clearerleft"> </div>
		</div>
		<?php
		
		if(isset($tms_confirm_upload) && $tms_confirm_upload)
			{
			?>
			<div class="Question FieldSaveError" id="tms_confirm_upload">
				<label for="tms_confirm_upload"><?php echo $lang["tms_link_confirm_upload_nodata"] ?></label>
				<input type="checkbox" id="tms_confirm_upload" name="tms_confirm_upload" value="true">
				<div class="clearerleft"> </div>
			</div>
			<?php
			}
			
		}
	}
	
function HookTMS_linkEditEdithidefield($field)
	{
	global $tms_link_object_id_field,$ref,$resource,$tms_link_resource_types;
	if ($field["ref"]==$tms_link_object_id_field && $ref<0 && in_array($resource["resource_type"],$tms_link_resource_types))
		{
		return true;
		}
	return false;
	}


function HookTms_linkAllAdditionalvalcheck($fields, $fieldsitem)
	{
	global $ref,$val,$tms_link_object_id_field,$resource,$tms_link_resource_types,$lang;
	
	//if($ref>0 || !in_array($resource["resource_type"],$tms_link_resource_types)){return false;}
	if(!in_array($resource["resource_type"],$tms_link_resource_types)){return false;}
	
	if($fieldsitem["ref"]==$tms_link_object_id_field)
		{
		$tms_form_post_id=getval("field_" . $tms_link_object_id_field ,"",true);
		$tms_object_id=intval($tms_form_post_id);
		
		
		//exit("ID: " . $tms_object_id);
		
		global $tmsdata;
		$tmsdata=tms_link_get_tms_data("",$tms_object_id);
		
		// Make sure we actually do save this data, even if we return an error
		update_field($ref,$tms_link_object_id_field,escape_check(getvalescaped("field_" . $tms_link_object_id_field ,"")));
				
		if(!is_array($tmsdata) && $ref<0)
			{			
			// We can't get any data from TMS for this new resource. Need to show warning if user has not already accepted this
			if(getval("tms_confirm_upload","")=="")
				{
				global $tms_confirm_upload, $lang;
				$tms_confirm_upload=true;
				$error=$lang["tms_link_upload_nodata"] . $tms_form_post_id . " " . $lang["tms_link_confirm_upload_nodata"];
				
				//exit(print_r($tmsdata));
				return $error;						
				}
			}
		else
			{
			global $tms_link_import;
			$tms_link_import=true;
			return false;
			}
		}
	return false;		
	}
	
function HookTms_linkEditSaveextraresourcedata($list)
	{
	// Multi edit - set flag to update TMS data if necessary
	global $val,$tms_link_object_id_field,$resource,$tms_link_resource_types,$lang;
	
	$tms_object_id=getval("field_" . $tms_link_object_id_field ,"",true);
		
	if($tms_object_id!="") // The TMS ID field has been updated
		{		
		global $tmsdata;
		$tmsdata=tms_link_get_tms_data("",$tms_object_id);			
		if(is_array($tmsdata))
			{			
			// Only set if we have got data from TMS for this new resource. 
			global $tms_link_import;
			$tms_link_import=true;
			global $tmsupdatelist;
			$tmsupdatelist=$list;
			return false;
			}
		}
	return false;		
	}	
	
function HookTms_linkEditAftersaveresourcedata()
	{		
	global $tms_link_import;

	if(isset($tms_link_import) && $tms_link_import)
		{
		// Update Resource with TMS data
		global $tms_link_field_mappings_saved,$ref,$tmsdata,$tms_link_object_id_field,$tmsupdatelist;
		
		$tms_link_field_mappings=unserialize(base64_decode($tms_link_field_mappings_saved));
			
		if(!is_array($tmsupdatelist) ) // Not a batch edit, make up the $list array so we can pretend it is 
			{
			$tmsupdatelist=array();
			$tmsupdatelist[0]=$ref;
			}
		
		foreach($tmsupdatelist as $resourceref)
			{
			debug("tms_link: updating resource id #" . $resourceref);
			
			foreach($tms_link_field_mappings as $tms_link_column_name=>$tms_link_field_id)
				{
				if($tms_link_field_id!="" && $tms_link_field_id!=0 && isset($tmsdata[$tms_link_column_name]) && ($tms_link_field_id!=$tms_link_object_id_field))
					{	
					update_field($resourceref,$tms_link_field_id,escape_check($tmsdata[$tms_link_column_name]));						
					}
				elseif(getvalescaped("field_" . $tms_link_object_id_field ,"")=="" && $resourceref>0) // TMS object ID field is empty, remove the TMS data from the fields
					{
					update_field($resourceref,$tms_link_field_id,"");						
					}
				}	
			}
		}	
	}
	