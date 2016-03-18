<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}

include "../../include/header.php";

$ref=getval("ref","");
$offset=getval("offset",0);
$order_by=getval("orderby","");
$filter_by_parent=getval("filterbyparent","");
$find=getval("find","");
$filter_by_permissions=getval("filterbypermissions","");

$url_params=
	"?ref={$ref}" .
	($offset ? "&offset={$offset}" : "") .
	($order_by ? "&orderby={$order_by}" : "") .
	($filter_by_parent ? "&filterbyparent={$filter_by_parent}" : "") .
	($find ? "&find={$find}" : "") .
	($filter_by_permissions ? "&filterbypermissions={$filter_by_permissions}" : "");

if (getval("save","")!="")
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
	if (getval("other","")!="")
		{
		$perms[]=getvalescaped("other","");
		}

	log_activity(null,LOG_CODE_EDITED,join(",",$perms),'usergroup','permissions',$ref,null,null,null,true);
	sql_query("update usergroup set permissions='" . join(",",$perms) . "' where ref='$ref'");
	}

$group=get_usergroup($ref);
$permissions=trim_array(explode(",",$group["permissions"]));
$permissions_done=array();

function DrawOption($permission,$description,$reverse=false,$reload=false)
	{
	global $permissions,$permissions_done;
	$checked=(in_array($permission,$permissions));
	if ($reverse) {$checked=!$checked;}
	?>
	<input type="hidden" name="permission_<?php echo urlencode($permission)?>" value="<?php echo ($reverse)?"reverse":"normal" ?>">
	<tr>
		<td><?php if ($reverse) {?><i><?php } ?><?php echo $permission?><?php if ($reverse) {?></i><?php } ?></td>
		<td><?php echo $description?></td>
		<td><input type="checkbox" name="checked_<?php echo urlencode($permission) ?>" <?php 
			if ($checked) { ?> checked <?php } ?><?php if ($reload) { ?> onChange="CentralSpacePost(this.form,false);" <?php } ?>></td>
	</tr>
	<?php
	$permissions_done[]=$permission;
	}	
?>
<div class="BasicsBox"> 	
	<p>
		<a href="<?php echo $baseurl_short; ?>pages/admin/admin_group_management_edit.php<?php echo $url_params; ?>" onclick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php
		echo $lang['page-title_user_group_management_edit']; ?></a>
	</p>	
	<h1><?php echo $lang['page-title_user_group_permissions_edit'] ?></h1>
	<p><?php echo $lang['page-subtitle_user_group_permissions_edit']; ?></p>	

	<form method="post" id="permissions" action="<?php echo $baseurl_short; ?>pages/admin/admin_group_permissions.php<?php echo $url_params ?>" onsubmit="return CentralSpacePost(this,true);" >	
		<input type="hidden" name="save" value="1">		
<?php
	if ($offset) 
		{
?>			<input type="hidden" name="offset" value="<?php echo $offset; ?>">
<?php		}
	if ($order_by) 
		{
?>			<input type="hidden" name="order_by" value="<?php echo $order_by; ?>">
<?php		}
?>		<div class="Listview">
			<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["searching_and_access"] ?></td>
				</tr>
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

?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["metadatafields"] ?></td>
				</tr>
<?php

# ------------ View access to fields
DrawOption("f*", $lang["can_see_all_fields"], false, true);
$fields=sql_query("select * from resource_type_field order by order_by");
foreach ($fields as $field)
	{
	if (!in_array("f*",$permissions))
		{
		DrawOption("f" . $field["ref"], "&nbsp;&nbsp; - " . $lang["can_see_field"] . " '" . lang_or_i18n_get_translated($field["title"], "fieldtitle-") . "'" . (($field["name"]=="")?"":"<em> (" . htmlspecialchars($field["name"]) . ")</em>"));
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
		DrawOption("F-" . $field["ref"], "&nbsp;&nbsp; - " . $lang["can_edit_field"] . " '" . lang_or_i18n_get_translated($field["title"], "fieldtitle-") . "'"  . (($field["name"]=="")?"":"<em> (" . htmlspecialchars($field["name"]) . ")</em>"), false);
		}
	else
		{
		# Add it to the 'done' list so it is discarded.
		$permissions_done[]="F-" . $field["ref"];
		}
	}

?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["resourcetypes"] ?></td>
				</tr>
<?php

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

?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["resource_creation_and_management"] ?></td>
				</tr>
<?php

# ------------ Edit access to workflow states
for ($n=-2;$n<=3;$n++)
	{
	DrawOption("e" . $n, $lang["edit_access_to_workflow_state"] . " '" . $lang["status" . $n] . "'", false);
	}
foreach ($additional_archive_states as $additional_archive_state)
	{
	DrawOption("e" . $additional_archive_state, $lang["edit_access_to_workflow_state"] . " '" . (isset($lang["status" . $additional_archive_state])?$lang["status" . $additional_archive_state]:$additional_archive_state) . "'", false);
	}
for ($n=0;$n<=($custom_access?3:2);$n++)
    {
    DrawOption("ea" . $n, $lang["edit_access_to_access"]. " '".$lang["access" . $n]. "'", true);
    }

DrawOption("c", $lang["can_create_resources_and_upload_files-admins"]);
DrawOption("d", $lang["can_create_resources_and_upload_files-general_users"]);

DrawOption("D", $lang["can_delete_resources"], true);

DrawOption("i", $lang["can_manage_archive_resources"]);
DrawOption('A', $lang["can_manage_alternative_files"], true);
DrawOption("n", $lang["can_tag_resources_using_speed_tagging"]);


?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["themes_and_collections"] ?></td>
				</tr>
<?php

DrawOption("b", $lang["enable_bottom_collection_bar"], true);
DrawOption("h", $lang["can_publish_collections_as_themes"],false,true);
if(in_array('h', $permissions))
	{
	DrawOption('hdta', $lang['manage_all_dash_h'], true, false);
	DrawOption('hdt_ug', $lang['manage_user_group_dash_tiles'], false, false);
	}
else
	{
	DrawOption('dta', $lang['manage_all_dash'], false, false);
	}
DrawOption("dtu",$lang["manage_own_dash"],true,false);

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


# ---------- End of Dash Tiles

?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["administration"] ?></td>
				</tr>
<?php

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
?>				<tr class="ListviewTitleStyle">
					<td colspan=3 class="permheader"><?php echo $lang["other"] ?></td>
				</tr>
<?php
DrawOption("p", $lang["can_change_own_password"], true);
DrawOption("u", $lang["can_manage_users"]);
DrawOption("U", $lang["can_manage_users_in_children_groups"]);
DrawOption("E", $lang["can_email_resources_to_own_and_children_and_parent_groups"]);
DrawOption("x", $lang["allow_user_group_selection_for_access_when_sharing_externally"]);
DrawOption("noex", $lang["prevent_user_group_sharing_externally"]);

hook("additionalperms");
?>			</table>
		</div>  <!-- end of Listview -->
		
		<div class="Question">
			<label for="other"><?php echo $lang["custompermissions"]; ?></label>
			<textarea name="other" class="stdwidth" rows="3" cols="50"><?php echo join(",",array_diff($permissions,$permissions_done)); ?></textarea>			
			<div class="clearerleft"></div>
		</div>
		
		<div class="QuestionSubmit">
			<label for="buttons"> </label>			
			<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]; ?>&nbsp;&nbsp;">
		</div>

	</form>	
</div>  <!-- end of BasicsBox -->
	
<?php
include "../../include/footer.php";
