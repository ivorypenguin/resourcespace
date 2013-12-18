<?php

include "../../include/db.php";
include "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("a")) {exit ("Permission denied.");}

$b_callback = (getval("ajax","")!="");

$ref=getvalescaped("ref","");

if (getval("saveform","")!="")
	{
	$perms=array();
	foreach ($_POST as $key=>$value)
		{
		if (substr($key,0,11)=="permission_")
			{
			# Found a permisison.
			$reverse=($value=="reverse");
			$key=substr($key,11);

			if ((!$reverse && getval("checked_" . $key,"")!="") || ($reverse && !getval("checked_" . $key,"")!=""))
				{
				$perms[]=urldecode($key);
				}
			}
		}	
	if (getval("other","")!="") {$perms[]=getvalescaped("other","");}
	sql_query("update usergroup set permissions='" . join(",",$perms) . "' where ref='$ref'");
	}
		
function DrawOption($permission,$description,$reverse=false,$reload=false)
	{
	global $permissions,$permissions_done;
	$checked=(in_array($permission,$permissions));
	if ($reverse) {$checked=!$checked;}
	?>
	<input type="hidden" name="permission_<?php echo urlencode($permission)?>" value="<?php echo ($reverse)?"reverse":"normal" ?>">
	<tr>
	<td width="3%"><?php if ($reverse) {?><i><?php } ?><?php echo $permission?><?php if ($reverse) {?></i><?php } ?></td>
	<td><?php echo $description?></td>
	<td width="20%"><input type="checkbox" name="checked_<?php echo urlencode($permission) ?>" <?php if ($checked) { ?> checked <?php } ?><?php if ($reload) { ?> onChange="submitForm();" <?php } ?>></td>
	</tr>
	<?php

	$permissions_done[]=$permission;
	}

# Load group data / permissions
$group=get_usergroup($ref);
$permissions=trim_array(explode(",",$group["permissions"]));
$permissions_done=array();

if (!$b_callback) {

include "include/header.php";

?>

<body style="background-position:0px -85px;margin:0;padding:10px;">
<script type="text/javascript">	
		function submitForm() {			
			var obj = document.forms['permform'];		
			jQuery.post(
				"permissions.php?ajax=true&ref=<?php echo $ref; ?>",
				jQuery(obj).serialize(),
				function(data)
				{				
				jQuery(".permissionstable").html(data);
				}
			);
		}	
	</script>
	
<style>
.permissionstable {border-collapse: collapse;}
.permissionstable td {border:1px solid #999;padding:4px;}
.permheader, .permheader td {background-color:#ddd;font-weight:bold;}
</style>

<div class="proptitle"><?php echo $lang["permissionsmanager"] . ": " . $group["name"] ?></div>

<div class="propbox" id="propbox">
<p><a href="<?php echo $baseurl?>/pages/admin/properties.php?id=-1-4:<?php echo $ref?>&parent=8&gparent=1&name=<?php echo urlencode($group["name"])?>">&lt;&nbsp;<?php echo $lang["backtogroupmanagement"] ?></a></p>

<form method="post" id="permform">
<input type="hidden" name="saveform" value="true">


<?php } // end of callback check ?>	

<table width="100%" class="permissionstable">

<tr><td colspan=3 class="permheader"><?php echo $lang["searching_and_access"] ?></td></tr>

<?php
DrawOption("s", $lang["searchcapability"]);
DrawOption("v", $lang["access_to_restricted_and_confidential_resources"], false);

# ------------ View access to workflow states

for ($n=-2;$n<=3;$n++)
	{
	DrawOption("z" . $n, $lang["hide_view_access_to_workflow_state"] . " '" . $lang["status" . $n] . "'", false);
	}

foreach ($additional_archive_states as $additional_archive_state)
	{
	DrawOption("z" . $additional_archive_state, $lang["hide_view_access_to_workflow_state"] . " '" . (isset($lang["status" . $additional_archive_state])?$lang["status" . $additional_archive_state]:$additional_archive_state) . "'", false);
	}
	
DrawOption("g", $lang["restrict_access_to_all_available_resources"], true);
DrawOption("q", $lang["can_make_resource_requests"], false);
DrawOption("w", $lang["show_watermarked_previews_and_thumbnails"]);

?><tr><td colspan=3 class="permheader"><?php echo $lang["metadatafields"] ?></td></tr><?php

# ------------ View access to fields
DrawOption("f*", $lang["can_see_all_fields"], false, true);
$fields=sql_query("select * from resource_type_field order by order_by");
foreach ($fields as $field)
	{
	if (!in_array("f*",$permissions))
		{
		DrawOption("f" . $field["ref"], "&nbsp;&nbsp; - " . $lang["can_see_field"] . " '" . lang_or_i18n_get_translated($field["title"], "fieldtitle-") . "'");
		}
	else
		{
		# Add it to the 'done' list so it is discarded.
		$permissions_done[]="f" . $field["ref"];
		}
	}

DrawOption("F*", $lang["can_edit_all_fields"], true, true);
$fields=sql_query("select * from resource_type_field order by order_by");
foreach ($fields as $field)
	{
	if (in_array("F*",$permissions))	
		{
		DrawOption("F-" . $field["ref"], "&nbsp;&nbsp; - " . $lang["can_edit_field"] . " '" . lang_or_i18n_get_translated($field["title"], "fieldtitle-") . "'", false);
		}
	else
		{
		# Add it to the 'done' list so it is discarded.
		$permissions_done[]="F-" . $field["ref"];
		}
	}

?><tr><td colspan=3 class="permheader"><?php echo $lang["resourcetypes"] ?></td></tr><?php

# ------------ View access to resource types
$rtypes=sql_query("select * from resource_type order by name");
foreach ($rtypes as $rtype)
	{
	DrawOption("T" . $rtype["ref"], $lang["can_see_resource_type"] ." '" . lang_or_i18n_get_translated($rtype["name"], "resourcetype-") . "'", true);
	}

# ------------ Restricted access to resource types
foreach ($rtypes as $rtype)
	{
	DrawOption("X" . $rtype["ref"], $lang["restricted_access_only_to_resource_type"] . " '" . lang_or_i18n_get_translated($rtype["name"], "resourcetype-") . "'", false);
	}

# ------------ Restricted upload for resource of type
foreach ($rtypes as $rtype)
	{
	DrawOption("XU" . $rtype["ref"], $lang["restricted_upload_for_resource_of_type"] . " '" . lang_or_i18n_get_translated($rtype["name"], "resourcetype-") . "'", false);
	}

?><tr><td colspan=3 class="permheader"><?php echo $lang["resource_creation_and_management"] ?></td></tr><?php

# ------------ Edit access to workflow states
for ($n=-2;$n<=3;$n++)
	{
	DrawOption("e" . $n, $lang["edit_access_to_workflow_state"] . " '" . $lang["status" . $n] . "'", false);
	}
foreach ($additional_archive_states as $additional_archive_state)
	{
	DrawOption("e" . $additional_archive_state, $lang["edit_access_to_workflow_state"] . " '" . (isset($lang["status" . $additional_archive_state])?$lang["status" . $additional_archive_state]:$additional_archive_state) . "'", false);
	}

DrawOption("c", $lang["can_create_resources_and_upload_files-admins"]);
DrawOption("d", $lang["can_create_resources_and_upload_files-general_users"]);

DrawOption("D", $lang["can_delete_resources"], true);

DrawOption("i", $lang["can_manage_archive_resources"]);
DrawOption('A', $lang["can_manage_alternative_files"], true);
DrawOption("n", $lang["can_tag_resources_using_speed_tagging"]);


?><tr><td colspan=3 class="permheader"><?php echo $lang["themes_and_collections"] ?></td></tr><?php

DrawOption("b", $lang["enable_bottom_collection_bar"], true);
DrawOption("h", $lang["can_publish_collections_as_themes"]);

# ------------ Access to theme categories

DrawOption("j*", $lang["can_see_all_theme_categories"], false, true);
if (!in_array("j*",$permissions))	// by default is checked	
	{
	include_once "../../include/theme_permission_functions.php";
	$theme_paths = getThemePathPerms();	
	foreach ($theme_paths as $path=>$bPerm)
		{	
		$level = substr_count ($path,"|");				
		if ($level == 0)
			{
			DrawOption("j${path}",  "${lang['can_see_theme_category']} '${path}'", false, true);	// always show the top level theme
			}
		else
			{
			$parent = substr ($path, 0, strrpos($path,"|"));
			$skip =(!$theme_paths[$parent]);		// check if parent theme permission has been set
			$permission = "j-" . $path;
			if ($skip)
				{
				$permissions_done[] = $permission;		// stop any hidden perms appearing in the "custom permissions" if not showing (because parent is not set)
				}
			else
				{
				$nicename = substr ($path, strrpos ($path,"|") + 1);
				DrawOption($permission, str_pad("", $level*7, "&mdash;") . " " . $lang["can_see_theme_sub_category"] . " '" . i18n_get_translated($nicename) . "'", true, true);
				}
			}
		}
	}	
DrawOption("J", $lang["display_only_resources_within_accessible_themes"]);

# ---------- end of theme categories

?><tr><td colspan=3 class="permheader"><?php echo $lang["administration"] ?></td></tr><?php

DrawOption("t", $lang["can_access_team_centre"], false, true);
if (in_array("t",$permissions))
	{
	# Team Centre options	
	DrawOption("r", $lang["can_manage_research_requests"]);
	DrawOption("R", $lang["can_manage_resource_requests"], false, true);
	if (in_array("R",$permissions))	
		{
		DrawOption("Ra", $lang["can_assign_resource_requests"]);
		DrawOption("Rb", $lang["can_be_assigned_resource_requests"]);
		}
	DrawOption("o", $lang["can_manage_content"]);
	DrawOption("m", $lang["can_bulk-mail_users"]);
	DrawOption("u", $lang["can_manage_users"]);
	DrawOption("k", $lang["can_manage_keywords"]);
	DrawOption("a", $lang["can_access_system_setup"]);
	}
else
	{
	$permissions_done[]="r";
	$permissions_done[]="R";
	$permissions_done[]="o";
	$permissions_done[]="m";
	$permissions_done[]="u";
	$permissions_done[]="k";
	$permissions_done[]="a";	
	}


?><tr><td colspan=3 class="permheader"><?php echo $lang["other"] ?></td></tr><?php

DrawOption("p", $lang["can_change_own_password"], true);
DrawOption("U", $lang["can_manage_users_in_children_groups"]);
DrawOption("E", $lang["can_email_resources_to_own_and_children_and_parent_groups"]);
hook("additionalperms");
?>
</table>

<?php if (!$b_callback) { ?>

<p><?php echo $lang["custompermissions"] . ":" ?></p>
<?php $not_handled=array_diff($permissions,$permissions_done); ?>
<textarea name="other" style="width:100%;"><?php echo join(",",$not_handled) ?></textarea>

<p align="right"><input type="submit" name="save" value="<?php echo $lang["save"] ?>" style="width:100px;"></p>
</form>

</div>
</div>

</body>
</html>

<?php }  // end of callback check ?>
