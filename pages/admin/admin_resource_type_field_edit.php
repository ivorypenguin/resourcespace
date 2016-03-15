<?php
/**
 * User edit form display page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; 

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}

include "../../include/resource_functions.php";

$ref=getvalescaped("ref","",true);

$find=getvalescaped("find","");
$restypefilter=getvalescaped("restypefilter","",true);
$field_order_by=getvalescaped("field_order_by","ref");
$field_sort=getvalescaped("field_sort","asc");

$url_params = array("ref"=>$ref,
		    "restypefilter"=>$restypefilter,
		    "field_sort"=>$field_sort,
		    "find" =>$find);
$url=generateURL($baseurl . "/pages/admin/admin_resource_type_field_edit.php",$url_params);

		
$backurl=getvalescaped("backurl","");
if($backurl=="")
    {
    $backurl=$baseurl . "/pages/admin/admin_resource_type_fields.php?ref=" . urlencode($ref) . "&restypefilter=" . urlencode($restypefilter) . "&field_sort=" . urlencode($field_sort) . "&find=" . urlencode($find);
    }
	
function admin_resource_type_field_option($propertyname,$propertytitle,$helptext="",$type, $currentvalue)
	{
	global $ref,$lang, $baseurl_short;
	?>
	<div class="Question" >
		<label><?php echo ($propertytitle!="")?$propertytitle:$propertyname ?></label>
		<?php
		if($propertyname=="resource_type")
			{
			global $resource_types;
			?>
			<div class="tickset">
			  <select id="<?php echo $propertyname ?>" name="<?php echo $propertyname ?>" class="stdwidth">
				<option value="0"<?php if ($currentvalue == "0" || $currentvalue == "") { echo " selected"; } ?>><?php echo $lang["resourcetype-global_field"]; ?></option>
				
				<?php
				  for($n=0;$n<count($resource_types);$n++){
				?>
				<option value="<?php echo $resource_types[$n]["ref"]; ?>"<?php if ($currentvalue == $resource_types[$n]["ref"]) { echo " selected"; } ?>><?php echo i18n_get_translated($resource_types[$n]["name"]); ?></option>
				<?php
				  }
				?>
				
				<option value="999"<?php if ($currentvalue == "999") { echo " selected"; } ?>><?php echo $lang["resourcetype-archive_only"]; ?></option>
				</select>
            </div>
			<?php
			}
		elseif($propertyname=="type")
			{
			global $field_types;
			
			// Sort  so that the display order makes some sense
			//natsort($field_types);
			?>
			<div class="tickset">
			  <select id="<?php echo $propertyname ?>" name="<?php echo $propertyname ?>" class="stdwidth" onchange="CentralSpacePost(this.form);">
				
				<?php
				foreach($field_types as $field_type=>$field_type_description)
					{
					?>
					<option value="<?php echo $field_type ?>"<?php if ($currentvalue == $field_type) { echo " selected"; } ?>><?php echo $lang[$field_type_description] ; ?></option>
					<?php
					}
				?>
				</select>
			</div>
            <?php
            if (in_array($currentvalue, array(2, 3, 7, 9, 12)))
                {
                ?>
                <div class="clearerleft"></div>
                </div> <!-- end question -->

                <div class="Question">
                <label><?php echo $lang['options']; ?></label>
                <span><a href="<?php echo $baseurl_short ?>pages/admin/admin_manage_field_options.php?field=<?php echo $ref ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang['property-options_edit_link']; ?></a></span>
                <?php
                }
            ?>
			<?php
			}
		elseif($propertyname=="sync_field")
			{
			global $allfields, $resource_type_array;
			
			// Sort  so that the display order makes some sense
			
			?>
			<div class="tickset">
			  <select id="<?php echo $propertyname ?>" name="<?php echo $propertyname ?>" class="stdwidth">
				<option value="" <?php if ($currentvalue == "") { echo " selected"; } ?>><?php echo $lang["select"]; ?></option>
				<?php
				foreach($allfields as $field)
					{
					if($field["ref"]!=$ref && isset($resource_type_array[$field["resource_type"]])) // Don't show itself as an option to sync with
					    {?>
					    <option value="<?php echo $field["ref"] ?>"<?php if ($currentvalue == $field["ref"]) { echo " selected"; } ?>><?php echo i18n_get_translated($field["title"])  . "&nbsp;(" . (($field["name"]=="")?"":htmlspecialchars($field["name"]) . " - ") . i18n_get_translated($resource_type_array[$field["resource_type"]]) . ")"?></option>
					    <?php
					    }
					}
				?>				
				</select>
			  </div>
			<?php
			}
		elseif($type==1)
			{
			?>
			<input name="<?php echo $propertyname ?>" type="checkbox" value="1" <?php if ($currentvalue==1) { ?> checked="checked"<?php } ?>>
			<?php
			}
		elseif($type==2)
			{
			?>
			<textarea class="stdwidth" rows="8" id="<?php echo $propertyname ?>" name="<?php echo $propertyname ?>"><?php echo htmlspecialchars($currentvalue)?></textarea>
			<?php
			}
		else
			{
			?>
			<input name="<?php echo $propertyname ?>" type="text" class="stdwidth" value="<?php echo htmlspecialchars($currentvalue)?>">
			<?php
			}
		if($helptext!="")
				{
				?>
				<div class="FormHelp" style="padding:0;clear:left;" >
					<div class="FormHelpInner"><?php echo str_replace("%ref",$ref,$helptext) ?>
					</div>
				</div>
				<?php
				}
				?>
		<div class="clearerleft"> </div>
	</div>
	<?php
	}


// Define array of field properties containing title and associated lang help text, with a flag to indicate if it is a boolean value that we will save from POST data and boolean to indicate will be set with any 'synced' fields

// example field :-
// "name of table column"=>array(
// <language string for the friendly name of this property>,
// <lang string for the help text explaining what this property means>,
// <value to denote the field type(0=text,1=boolean,2=text area),
// < boolean value to indicate whether this is a field that is synchronised? 0=No 1=Yes > 
// )
// IMPORTANT - Make sure advanced field properties are listed after the 'partial_index' so that these will be hidden from users by default

$fieldcolumns=array("title"=>array($lang["property-title"],"",0,1),
					"resource_type"=>array($lang["property-resource_type"],"",0,0),
					"type"=>array($lang["property-field_type"],"",0,1),
					"name"=>array($lang["property-shorthand_name"],$lang["information-shorthand_name"],0,1),
					"required"=>array($lang["property-required"],"",1,1),
					"order_by"=>array($lang["property-order_by"],"",0,1),
					"keywords_index"=>array($lang["property-index_this_field"],$lang["information-if_you_enable_indexing_below_and_the_field_already_contains_data-you_will_need_to_reindex_this_field"],1,1),
					"display_field"=>array($lang["property-display_field"],"",1,1),
					"advanced_search"=>array($lang["property-enable_advanced_search"],"",1,1),
					"simple_search"=>array($lang["property-enable_simple_search"],"",1,1),
					"exiftool_field"=>array($lang["property-exiftool_field"],"",0,1),
					"use_for_similar"=>array($lang["property-use_for_find_similar_searching"],"",1,1),
					"hide_when_uploading"=>array($lang["property-hide_when_uploading"],"",1,1),
					"hide_when_restricted"=>array($lang["property-hide_when_restricted"],"",1,1),
					"help_text"=>array($lang["property-help_text"],"",2,1),
					"tooltip_text"=>array($lang["property-tooltip_text"],$lang["information-tooltip_text"],2,1),
					
					"partial_index"=>array($lang["property-enable_partial_indexing"],$lang["information-enable_partial_indexing"],1,1),
					"iptc_equiv"=>array($lang["property-iptc_equiv"],"",0,1),					
					"display_template"=>array($lang["property-display_template"],"",2,1),
					"display_condition"=>array($lang["property-display_condition"],$lang["information-display_condition"],2,1),
					"value_filter"=>array($lang["property-value_filter"],"",2,1),
					"regexp_filter"=>array($lang["property-regexp_filter"],$lang["information-regexp_filter"],2,1),
					"tab_name"=>array($lang["property-tab_name"],"",0,1),
					"smart_theme_name"=>array($lang["property-smart_theme_name"],"",0,1),
					"exiftool_filter"=>array($lang["property-exiftool_filter"],"",2,1),
					"display_as_dropdown"=>array($lang["property-display_as_dropdown"],$lang["information-display_as_dropdown"],1,1),
					"external_user_access"=>array($lang["property-external_user_access"],"",1,1),
					"autocomplete_macro"=>array($lang["property-autocomplete_macro"],"",2,1),
					"omit_when_copying"=>array($lang["property-omit_when_copying"],"",1,1),
					"sync_field"=>array($lang["property-sync_with_field"],"",0,0),
					"onchange_macro"=>array($lang["property-onchange_macro"],$lang["information-onchange_macro"],2,1)				
					);

# Remove some items if $execution_lockout is set to prevent code execution
if ($execution_lockout)
	{
	unset($fieldcolumns["autocomplete_macro"]);
	unset($fieldcolumns["exiftool_filter"]);
	unset($fieldcolumns["value_filter"]);
	unset($fieldcolumns["onchange_macro"]);
	}

$modify_resource_type_field_columns=hook("modifyresourcetypefieldcolumns","",array($fieldcolumns));
if($modify_resource_type_field_columns!=''){
        $fieldcolumns=$modify_resource_type_field_columns;
}				
if(getval("save","")!="" && getval("delete","")=="")
	{
	# Save field config
	//TODO 	sync field
	//__then__  sync_field='%3' or ref='[sync_with_field]' or sync_field='[sync_with_field]
	$sync_field=getvalescaped("sync_field",0);
	
	foreach ($fieldcolumns as $column=>$column_detail)		
		{		
		if ($column_detail[2]==1)
			{
			$val=getval($column,"0") ? "1" : "0";
			}		
		else
			{
			$val=getvalescaped($column,"");
			//echo "GOT VALUE " . $val . " for " . $column . "<br>"; 
			// Set shortnm if not already set
			if($column=="name" && $val==""){$val="field" . $ref;}
			}
		if (isset($sql))
			{
			$sql.=",";
			}
		else
			{
			$sql="update resource_type_field set ";
			}		
		$sql.="{$column}=" . (($val=="")?"NULL":"'{$val}'");
		log_activity(null,LOG_CODE_EDITED,$val,'resource_type_field',$column,$ref);

		// Add SQL to update synced fields if field is marked as a sync field
		if ($sync_field!="" && $sync_field>0 && $column_detail[3]==1)
			{
			if (isset($syncsql))
				{
				$syncsql.=",";
				}
			else
				{
				$syncsql="update resource_type_field set ";
				}
			$syncsql.="{$column}=" . (($val=="")?"NULL":"'{$val}'");
			}
		}
	$sql.=" where ref='{$ref}'";
	
	sql_query($sql);
	if($sync_field!="" && $sync_field>0)
		{
		$syncsql.=" where ref='$sync_field' or sync_field='$ref'";
		sql_query($syncsql);
		}
	
	$saved_text=$lang["saved"];
	//redirect($backurl);
	}

$confirm_delete=false;	
if (getval("delete","")!="")
	{	
	$confirmdelete=getvalescaped("confirmdelete","");
	# Check for resources of this  type
	$affected_resources=sql_array("select resource value from resource_data where resource>0 and resource_type_field='$ref'",0);
	$affected_resources_count=count($affected_resources);
	if($affected_resources_count==0 || $confirmdelete!="")
	    {	    
	     // Delete the resource type field
	    sql_query("delete from resource_type_field where ref='$ref'");
		log_activity(null,LOG_CODE_DELETED,null,'resource_type_field',null,$ref);

	    //Remove all data	    
	    sql_query("delete from resource_data where resource_type_field='$ref'");
	    //Remove all keywords	    
	    sql_query("delete from resource_keyword where resource_type_field='$ref'");
	    hook("after_delete_resource_type_field");
	    redirect(generateURL($baseurl . "/pages/admin/admin_resource_type_fields.php",$url_params,array("ref"=>"","deleted"=>urlencode($ref))));
	    }
        else
	    {	    
	    // User needs to confirm deletion as data wil be lost
	    $error_text=str_replace("%%AFFECTEDRESOURCES%%",$affected_resources_count,$lang["admin_delete_field_confirm"]);
	    // $affected_links="<br>";
	    // for ($a=0;$a<10 && $a<$affected_resources_count;$a++) // show links for up to 10 of the affected resources
			// {
			// if($a!=0){$affected_links.=",";}    
			// $affected_links.="<a target='_blank' href='" . $baseurl . "/?r=" . $affected_resources[$a] . "'>" . $affected_resources[$a] . "</a>";
			// } 
		//$error_text.=$affected_links;
		
		$error_text.="<br><a target=\"_blank\" href=\"" . $baseurl  . "/pages/search.php?search=!hasdata" . $ref . "\">" . $lang["show_resources"] . "</a>";
	    
	    $confirm_delete=true;
	    }
	
	
	}



# Fetch  data
$allfields=get_resource_type_fields();
$resource_types=sql_query("select ref, name from resource_type");
foreach($resource_types as $resource_type)
	{
	$resource_type_array[$resource_type["ref"]]=$resource_type["name"];
	}
$resource_type_array[0]=$lang["resourcetype-global_field"];
$resource_type_array[999]=$lang["resourcetype-archive_only"];

$fielddata=get_resource_type_field($ref);

include "../../include/header.php";

?>

<div class="BasicsBox">
    
    <p>
	
	<a href="<?php echo $backurl ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["back"]?></a>
    </p>
    
    <h1><?php echo $lang["admin_resource_type_field"] . ": " . i18n_get_translated($fielddata["title"]) ?></h1>
	

 

<form method=post class="FormWide" action="<?php echo $baseurl_short?>pages/admin/admin_resource_type_field_edit.php?ref=<?php echo $fielddata["ref"] . "&restypefilter=" . $restypefilter . "&field_order_by=" . $field_order_by . "&field_sort=" . $field_sort ."&find=" . urlencode($find); ?>" onSubmit="return CentralSpacePost(this,true);">

<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">


<?php
if (isset($error_text)) { ?><div class="PageInformal"><?php echo $error_text?></div><?php }
if (isset($saved_text)) { ?><div class="PageInformal"><?php echo $saved_text?></div> <?php }


if($confirm_delete)
    {
    ?>
    <input name="confirmdelete" id="confirmdelete" type="hidden" value="">
    <div class="textcenter">
	<input name="delete" type="button" value="&nbsp;&nbsp;<?php echo $lang["action-delete"]?>&nbsp;&nbsp;" onClick="jQuery('#delete').val('yes');jQuery('#confirmdelete').val('yes');this.form.submit();" />
	<input type="button" class="button" onClick="CentralSpaceLoad('<?php generateURL($baseurl_short . "/pages/admin/admin_resource_type_field_edit.php",$url_params,array("ref"=>"")); ?>',true);return false;" value="&nbsp;&nbsp;<?php echo $lang["cancel"] ?>&nbsp;&nbsp;" >
    </div>
     <?php	
    }
else
    {
    ?>
 
    <div class="Question"><label><?php echo $lang["property-field_id"] ?></label>
	<div class="Fixed"><?php echo  $fielddata["ref"] ?></div>
	<div class="clearerleft"> </div>
    </div>
    
    <?php
    
    foreach ($fieldcolumns as $column=>$column_detail)		
	    {
	    if ($column=="partial_index") // Start the hidden advanced section here
			{?>
			<h2 id="showhiddenfields" class="CollapsibleSectionHead collapsed" ><?php echo $lang["admin_advanced_field_properties"] ?></h2>
			<div class="CollapsibleSection" id="admin_hidden_field_properties" >	 
			<?php
			}
	    admin_resource_type_field_option($column,$column_detail[0],$column_detail[1],$column_detail[2],$fielddata[$column]);
	    }
    ?>
    
    </div><!-- End of hidden advanced section -->
    
    
    <div class="QuestionSubmit">
    <label for="buttons"> </label>			
    <input name="save" type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;&nbsp;&nbsp;" />&nbsp;&nbsp;
    <input type="button" class="button" onClick="CentralSpaceLoad('<?php echo $baseurl . "/pages/admin/admin_copy_field.php?ref=" . $ref . "&backurl=" . $url ?>',true);return false;" value="&nbsp;&nbsp;<?php echo $lang["copy-field"] ?>&nbsp;&nbsp;" >
    <input name="delete" type="button" value="&nbsp;&nbsp;<?php echo $lang["action-delete"]?>&nbsp;&nbsp;" onClick="if(confirm('<?php echo $lang["confirm-deletion"] ?>')){jQuery('#delete').val('yes');this.form.submit();}else{jQuery('#delete').val('');}" />

    </div>
    <?php
    }?>

<input type="hidden" name="save" id="save" value="yes"/>
<input type="hidden" name="delete" id="delete" value=""/>
</form>


</div><!-- End of Basics Box -->

<script>
   registerCollapsibleSections();
</script>



<?php


include "../../include/footer.php";
