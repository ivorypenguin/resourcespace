<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}


$offset=getval("offset",0);
$order_by=getval("orderby","");
$filter_by_parent=getval("filterbyparent","");
$find=getval("find","");
$filter_by_permissions=getval("filterbypermissions","");

$url_params=
	($offset ? "&offset={$offset}" : "") .
	($order_by ? "&orderby={$order_by}" : "") .
	($filter_by_parent ? "&filterbyparent={$filter_by_parent}" : "") .
	($find ? "&find={$find}" : "") .
	($filter_by_permissions ? "&filterbypermissions={$filter_by_permissions}" : "");

# create new record from callback
$new_group_name=getvalescaped("newusergroupname","");
if ($new_group_name!="")
	{
	sql_query("insert into usergroup(name,request_mode) values('$new_group_name','1')");
	$ref=sql_insert_id();

	log_activity(null,LOG_CODE_CREATED,null,'usergroup',null,$ref);
	log_activity(null,LOG_CODE_CREATED,$new_group_name,'usergroup','name',$ref,null,'');
	log_activity(null,LOG_CODE_CREATED,'1','usergroup','request_mode',$ref,null,'');

	redirect($baseurl_short."pages/admin/admin_group_management_edit.php?ref={$ref}{$url_params}");	// redirect to prevent repost and expose of form data
	exit;
	}

$ref=getval("ref","");

if (!sql_value("select ref as value from usergroup where ref='{$ref}'",false))
	{
	redirect("{$baseurl_short}pages/admin/admin_group_management.php?{$url_params}");		// fail safe by returning to the user group management page if duff ref passed
	exit;
	}

$dependant_user_count=sql_value("select count(*) as value from user where usergroup='{$ref}'",0);
$dependant_groups=sql_value("select count(*) as value from usergroup where parent='{$ref}'",0);
$has_dependants=$dependant_user_count + $dependant_groups > 0;
	
if (!$has_dependants && getval("deleteme",false))
	{
	sql_query("delete from usergroup where ref='{$ref}'");
	log_activity('',LOG_CODE_DELETED,null,'usergroup',null,$ref);

	// No need to keep any records of language content for this user group
	sql_query('DELETE FROM site_text WHERE specific_to_group = "' . $ref . '";');

	redirect("{$baseurl_short}pages/admin/admin_group_management.php?{$url_params}");		// return to the user group management page
	exit;
	}	
	
if (getval("save",false))
	{

		$logo_dir="{$storagedir}/admin/groupheaderimg/";

		if (isset($_POST['removelogo']))
		{
			$logo_extension=sql_value("select group_specific_logo as value from usergroup where ref='{$ref}'", false);
			$logo_filename="{$logo_dir}/group{$ref}.{$logo_extension}";

			if ($logo_extension && file_exists($logo_filename) && unlink($logo_filename))
			{
				$logo_extension="";
			}
			else
			{
				unset ($logo_extension);
			}
		}

		if (isset ($_FILES['grouplogo']['tmp_name']) && is_uploaded_file($_FILES['grouplogo']['tmp_name']))
			{

			if(!(file_exists($logo_dir) && is_dir($logo_dir)))
				{
				mkdir($logo_dir,0777,true);
				}

			$logo_pathinfo=pathinfo($_FILES['grouplogo']['name']);
			$logo_extension=$logo_pathinfo['extension'];
			$logo_filename="{$logo_dir}/group{$ref}.{$logo_extension}";

            if(in_array($logo_extension, $banned_extensions))
                {
                trigger_error('You are not allowed to upload "' . $logo_extension . '" files to the system!');
                }

			if (!move_uploaded_file($_FILES['grouplogo']['tmp_name'], $logo_filename))		// this will overwrite if already existing
				{
				unset ($logo_extension);
				}
			}

		if (isset($logo_extension))
			{
			sql_query("update usergroup set group_specific_logo='{$logo_extension}' where ref='{$ref}'");
			log_activity(null,null,null,'usergroup','group_specific_logo',$ref);
			}

	foreach (array("name","permissions","parent","search_filter","edit_filter","derestrict_filter",
					"resource_defaults","config_options","welcome_message","ip_restrict","request_mode","allow_registration_selection") as $column)		
		
		{
		if ($column=="allow_registration_selection")
			{
			$val=getval($column,"0") ? "1" : "0";
			}
		elseif($column=="parent")
			{
			$val=getval($column,0,true);
			}			
		elseif($column=="request_mode")
			{
			$val=getval($column, 1, true);
			}			
		else
			{
			$val=getvalescaped($column,"");
			}
			
		if ($execution_lockout && $column=="config_options") {$val="";} # Do not allow config overrides if $execution_lockout is set.
		
		if (isset($sql))
			{
			$sql.=",";
			}
		else
			{
			$sql="update usergroup set ";
			}		
		$sql.="{$column}='{$val}'";
		log_activity(null,LOG_CODE_EDITED,$val,'usergroup',$column,$ref);
		}
	$sql.=" where ref='{$ref}'";
	sql_query($sql);
	
	hook("usergroup_edit_add_form_save","",array($ref));
	
	redirect("{$baseurl_short}pages/admin/admin_group_management.php?{$url_params}");		// return to the user group management page
	exit;
	}

$record = sql_query("select * from usergroup where ref={$ref}");
$record = $record[0];

# prints out an option tag per config.default.php file and moves any comments to the label attribute.
function dump_config_default_options()
	{	
	global $baseurl_short;
	
	$config_defaults = file_get_contents("../../include/config.default.php");
	$config_defaults = preg_replace("/\<\?php|\?\>/s","",$config_defaults);		// remove php open and close tags
	$config_defaults = preg_replace("/\/\*.*?\*\//s","",$config_defaults);		// remove multi-line comments

	preg_match_all("/\n(\S*?)(\\$.*?\=.*?\;)(.*?)\n/s",$config_defaults,$matches);

	for ($i=0; $i<count($matches[0]); $i++)
		{		
		$matches[1][$i]=preg_replace('/\#|(\/\/)/s','',$matches[1][$i]);		// hashes and double forward slash comments
		$matches[1][$i]=preg_replace('/\n\s+/s',"\n",$matches[1][$i]);		// white space at the start of new lines
		$matches[1][$i]=preg_replace('/^\s*/s','',$matches[1][$i]);		// leading white space
		$matches[1][$i]=preg_replace('/\s*$/s','',$matches[1][$i]);		// trailing white space
		
		$matches[3][$i]=preg_replace('/\#|(\/\/)/s','',$matches[3][$i]);		// hashes and double forward slash comments
		$matches[3][$i]=preg_replace('/\n\s+/s',"\n",$matches[3][$i]);		// white space at the start of new lines
		$matches[3][$i]=preg_replace('/^\s*/s','',$matches[3][$i]);		// leading white space
		$matches[3][$i]=preg_replace('/\s*$/s','',$matches[3][$i]);		// trailing white space		
		
		if ($matches[1][$i]!="" && $matches[3][$i]!="") $matches[1][$i].="\n";
			
		echo "<option value=\"" . nl2br (htmlentities ($matches[1][$i] . $matches[3][$i],ENT_COMPAT)) . "\">" . htmlentities ($matches[2][$i]) . "</option>\n";
		}
	}

include "../../include/header.php";

?><form method="post" enctype="multipart/form-data" action="<?php echo $baseurl_short; ?>pages/admin/admin_group_management_edit.php?ref=<?php echo $ref . $url_params ?>" id="mainform" class="FormWide">
	<?php /* xonSubmit="return CentralSpacePost(this,true);" > */ // central space post submit of form containing file upload is currently not supported ?>

	<div class="BasicsBox">

	<p>
		<a href="" onclick="return CentralSpaceLoad('<?php echo $baseurl_short; ?>pages/admin/admin_group_management.php?<?php echo $url_params; ?>',true);"><?php echo LINK_CARET_BACK ?><?php echo $lang['page-title_user_group_management']; ?></a>
	</p>

	<h1><?php echo $lang['page-title_user_group_management_edit'] ?></h1>
	<p><?php echo $lang['page-subtitle_user_group_management_edit']; ?></p>

		<input type="hidden" name="save" value="1">

		<div class="Question">
			<label for="reference"><?php echo $lang["property-reference"]; ?></label>
			<div class="Fixed"><?php echo $ref; ?></div>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="name"><?php echo $lang["property-name"]; ?></label>
			<input name="name" type="text" class="stdwidth" value="<?php echo $record['name']; ?>">	
			<div class="clearerleft"></div>
		</div>

		<div class="Question">									
			<label for="dependants"><?php echo $lang["property-contains"]; ?></label>
			<div class="Fixed"><?php echo $dependant_user_count; ?>&nbsp;<?php echo $lang['users']; ?>, <?php echo $dependant_groups; ?>&nbsp;<?php echo $lang['property-groups']; ?></div>
			<div class="clearerleft"></div>		
		</div>		

		<div class="Question">			
			<label for="permissions"><?php echo $lang["property-permissions"]; ?></label>
			<input type="button" class="stdwidth" onclick="return CentralSpaceLoad('<?php echo $baseurl_short; ?>pages/admin/admin_group_permissions.php?ref=<?php echo $ref . $url_params; ?>',true);" value="<?php echo $lang["launchpermissionsmanager"]; ?>"></input>						
			<div class="clearerleft"></div>			
			<label></label>
			<textarea name="permissions" class="stdwidth" rows="5" cols="50"><?php echo $record['permissions']; ?></textarea>
			<div class="clearerleft"></div>
			<label></label>
			<div><a href="../../documentation/permissions.txt" target="_blank"><?php echo $lang["documentation-permissions"]; ?></div>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="parent"><?php echo $lang["property-parent"]; ?></label>
			<select name="parent" class="stdwidth">
				<option value="0" ><?php if ($record['parent']) echo $lang["property-user_group_remove_parent"]; ?></option>
				<?php
				$groups=sql_query("select ref, name from usergroup order by name");

				foreach	($groups as $group)
				{
					if ($group['ref']==$ref) continue;		// not allowed to be the parent of itself

					?>				<option <?php if ($record['parent']==$group['ref']) { ?> selected="true" <?php } ?>value="<?php echo $group['ref']; ?>"><?php echo $group['name']; ?></option>
				<?php
				}
				?>			</select>
			<div class="clearerleft"></div>
		</div>

	<?php hook("usergroup_edit_add_form",'',array($record));?>

	</div>

	<h2 class="CollapsibleSectionHead collapsed"><?php echo $lang["fieldtitle-advanced_options"]; ?></h2>

	<div class="CollapsibleSection" style="display:none;">

		<p><?php echo $lang["action-title_see_wiki_for_advanced_options"]; ?></p>


		<div class="Question">
			<label for="search_filter"><?php echo $lang["property-search_filter"]; ?></label>
			<textarea name="search_filter" class="stdwidth" rows="3" cols="50"><?php echo $record['search_filter']; ?></textarea>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="edit_filter"><?php echo $lang["property-edit_filter"]; ?></label>
			<textarea name="edit_filter" class="stdwidth" rows="3" cols="50"><?php echo $record['edit_filter']; ?></textarea>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="derestrict_filter"><?php echo $lang["fieldtitle-derestrict_filter"]; ?></label>
			<textarea name="derestrict_filter" class="stdwidth" rows="3" cols="50"><?php echo $record['derestrict_filter']; ?></textarea>
			<div class="clearerleft"></div>
		</div>

		<div class="FormHelp">
			<div class="FormHelpInner"><?php echo $lang["information-derestrict_filter"]; ?></div>
		</div>

		<div class="Question">
			<label for="resource_defaults"><?php echo $lang["property-resource_defaults"]; ?></label>
			<textarea name="resource_defaults" class="stdwidth" rows="3" cols="50"><?php echo $record['resource_defaults']; ?></textarea>
			<div class="clearerleft"></div>
		</div>

		<?php if (!$execution_lockout) { ?>
		<div class="Question">
			<label for="config_options"><?php echo $lang["property-override_config_options"]; ?></label>
			<textarea name="config_options" id="configOptionsBox" class="stdwidth" rows="12" cols="50"><?php echo $record['config_options']; ?></textarea>
			<div class="clearerleft"></div>
<?php
	if (
		isset($system_architect_user_names) &&
		is_array($system_architect_user_names) &&
		in_array($userfullname,$system_architect_user_names)
	)
		{
		?><label></label>
			<select id="configOverrideSelector" class="stdwidth" onchange="document.getElementById('FormHelpConfigOverride').innerHTML=
				this.options[this.selectedIndex].value ? this.options[this.selectedIndex].value : '<?php echo $lang["fieldhelp-no_config_override_help"]; ?>'
			">
				<option value="<?php echo $lang["fieldhelp-add_to_config_override"]; ?>"></option>
				<?php dump_config_default_options(); ?>
			</select>
			<div class="clearerleft"></div>

			<div class="FormHelp">
				<div class="FormHelpInner" id="FormHelpConfigOverride" readonly="true"><?php echo $lang['fieldhelp-add_to_config_override']; ?></div>
			</div>

			<label></label>
			<input type="button" class="stdwidth" onclick="document.getElementById('configOptionsBox').innerHTML+= document.getElementById('configOverrideSelector').options[document.getElementById('configOverrideSelector').selectedIndex].label;" value="<?php
			echo $lang['fieldtitle-add_to_config_override'];
			?>"></input>
			<div class="clearerleft"></div>
<?php
		}
?>		</div>
		<?php } ?>

		<div class="Question">
			<label for="welcome_message"><?php echo $lang["property-email_welcome_message"]; ?></label>
			<textarea name="welcome_message" class="stdwidth" rows="12" cols="50"><?php echo $record['welcome_message']; ?></textarea>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="ip_restrict"><?php echo $lang["property-ip_address_restriction"]; ?></label>
			<input name="ip_restrict" type="text" class="stdwidth" value="<?php echo $record['ip_restrict']; ?>">
			<div class="clearerleft"></div>
		</div>

		<div class="FormHelp">
			<div class="FormHelpInner"><?php echo $lang["information-ip_address_restriction"]; ?></div>
		</div>

		<div class="Question">
			<label for="request_mode"><?php echo $lang["property-request_mode"]; ?></label>
			<select name="request_mode" class="stdwidth">
<?php
	for ($i=0; $i<4; $i++)
		{
?>				<option <?php if ($record['request_mode']==$i) { ?> selected="true" <?php } ?>value="<?php echo $i; ?>"><?php echo $lang["resourcerequesttype{$i}"]; ?></option>
<?php
		}
?>		        </select>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="allow_registration_selection"><?php echo $lang["property-allow_registration_selection"]; ?></label>
			<input name="allow_registration_selection" type="checkbox" value="1" <?php if ($record['allow_registration_selection']==1) { ?> checked="checked"<?php } ?>>
			<div class="clearerleft"></div>
		</div>

<?php
	if ($record['group_specific_logo'])
		{
		?>
		<div class="Question">
			<label for="removelogo"><?php echo $lang["action-title_remove_user_group_logo"]; ?></label>
			<input name="removelogo" type="checkbox" value="1">
			<div class="clearerleft"></div>
		</div>
<?php
		}
?>		<div class="Question">
			<label for="grouplogo"><?php echo $lang["fieldtitle-group_logo"]; ?></label>
			<input name="grouplogo" type="file">
			<div class="clearerleft"></div>
		</div>

	</div>		<!-- end of advanced options -->

	<div class="BasicsBox">

		<div class="Question">
			<label><?php echo $lang["fieldtitle-tick_to_delete_group"]?></label>
			<input id="delete_user_group" name="deleteme" type="checkbox" value="yes" <?php if($has_dependants) { ?> disabled="disabled"<?php } ?>>
			<div class="clearerleft"></div>
		</div>
		
		<div class="FormHelp">
			<div class="FormHelpInner"><?php echo $lang["fieldhelp-tick_to_delete_group"]; ?></div>
		</div>
		
		<div class="QuestionSubmit">
			<label for="buttonsave"></label>
			<input name="buttonsave" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]; ?>&nbsp;&nbsp;">
		</div>

	</div>

</form>

<script>
	registerCollapsibleSections();

	jQuery('#delete_user_group').click(function () {
		<?php
		$language_specific_results = sql_value('SELECT count(*) AS `value` FROM site_text WHERE specific_to_group = "' . $ref . '";', 0);
		$alert_message = str_replace('%%RECORDSCOUNT%%', $language_specific_results, $lang["delete_user_group_checkbox_alert_message"]);
		?>

		if(<?php echo $language_specific_results; ?> > 0 && jQuery('#delete_user_group').is(':checked'))
			{
			alert('<?php echo $alert_message; ?>');
			}
	});
</script>

<?php
include "../../include/footer.php";
