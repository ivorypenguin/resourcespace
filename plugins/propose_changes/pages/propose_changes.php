<?php

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php';
include '../../../include/resource_functions.php';
include '../include/propose_changes_functions.php';


$ref=getvalescaped("ref","",true);

# Fetch search details (for next/back browsing and forwarding of search params)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

$archive=getvalescaped("archive",0,true);

$errors=array(); # The results of the save operation (e.g. required field messages)
$editaccess=get_edit_access($ref);

if(!$propose_changes_always_allow)
	{
	# Check user has permission.
	$proposeallowed=sql_value("select r.ref value from resource r left join collection_resource cr on r.ref='$ref' and cr.resource=r.ref left join user_collection uc on uc.user='$userref' and uc.collection=cr.collection left join collection c on c.ref=uc.collection where c.propose_changes=1","");
        if($proposeallowed=="" && $propose_changes_allow_open)
            {
            $proposeallowed=(get_resource_access($ref)==0)?$ref:"";
            }
	}
	
if(!$propose_changes_always_allow && $proposeallowed=="" && !$editaccess)
    {
    # The user is not allowed to edit this resource or the resource doesn't exist.
    $error=$lang['error-permissiondenied'];
    error_alert($error);
    exit();
    }
    
if($editaccess)
    {
    $userproposals= sql_query("select pc.user, u.username from propose_changes_data pc left join user u on u.ref=pc.user where resource='$ref' group by pc.user order by u.username asc");
    $view_user=getvalescaped("proposeuser",((count($userproposals)==0)?$userref:$userproposals[0]["user"]));
    $proposed_changes=get_proposed_changes($ref, $view_user);  
    }
else
    {
    $proposed_changes=get_proposed_changes($ref, $userref);
    }
	

# Fetch resource data.
$resource=get_resource_data($ref);

# Load resource data
$proposefields=get_resource_field_data($ref,false,true);

// Save data
if (getval("save","")!="")
	{
	if($editaccess)
		{
		// Set a list of the fields we actually want to change - otherwise any fields we don't submit will get wiped
		$acceptedfields=array();
		foreach($proposed_changes as $proposed_change)
			{
			if(getval("accept_change_" . $proposed_change["resource_type_field"],"")=="on" && !getval("delete_change_" . $proposed_change["resource_type_field"],"")=="on")
				{
				$acceptedfields[]=$proposed_change["resource_type_field"];
				}			
			}
		
		// Actually save the data		
		save_resource_data($ref,false,$acceptedfields);
		
		daily_stat("Resource edit",$ref);				
		
		// send email to change  proposer with link
		$acceptedchanges=array();
		$acceptedchangescount=0;
		$deletedchanges=array();
		$deletedchangescount=0;		
		
		$proposefields=get_resource_field_data($ref,false,true); // Get updated data after save so we can send email with values
		for ($n=0;$n<count($proposefields);$n++)
			{
			node_field_options_override($proposefields[$n]);

			# Has this field been accepted?
			if (getval("accept_change_" . $proposefields[$n]["ref"],"")!="")
				{	
				debug("propose_changes - accepted proposed change for field " . $proposefields[$n]["title"]);
				$acceptedchanges[$acceptedchangescount]["field"]=$proposefields[$n]["title"];
				$acceptedchanges[$acceptedchangescount]["value"]=$proposefields[$n]["value"];
				$acceptedchangescount++;
				// remove this from the list of proposed changes
				sql_query("delete from propose_changes_data where user='$view_user' and resource_type_field='" . $proposefields[$n]["ref"] . "'");
				}
			# Has this field been deleted?
			if (getval("delete_change_" . $proposefields[$n]["ref"],"")!="")
				{					
				debug("propose_changes - deleted proposed change for field " . $proposefields[$n]["title"]);
				foreach($proposed_changes as $proposed_change)
					{
					if($proposed_change["resource_type_field"]==$proposefields[$n]["ref"])
						{
						$deletedchanges[$deletedchangescount]["field"]=$proposefields[$n]["title"];
						$deletedchanges[$deletedchangescount]["value"]=htmlspecialchars($proposed_change["value"]);
						$deletedchangescount++;
						}                
					}					
					
				
				// remove this from the list of proposed changes
				sql_query("delete from propose_changes_data where user='$view_user' and resource_type_field='" . $proposefields[$n]["ref"] . "'");
				}
			}	
			
		$templatevars['ref'] = $ref;
		$message=$lang["propose_changes_proposed_changes_reviewed"] . $templatevars['ref'] . "<br>";
		
		$templatevars['changesummary']=$lang["propose_changes_summary_changes"] . "<br><br>";
		
		if($acceptedchangescount>0)
			{			
			$templatevars['changesummary'].=$lang["propose_changes_proposed_changes_accepted"] . "<br>";
			}
		for($n=0;$n<$acceptedchangescount;$n++)
			{
			$templatevars['changesummary'].= $acceptedchanges[$n]["field"] . " : " . $acceptedchanges[$n]["value"] . "<br>";
			}
			
		if($deletedchangescount>0)
			{			
			$templatevars['changesummary'].="<br>" . $lang["propose_changes_proposed_changes_rejected"] . "<br><br>";
			}
		for($n=0;$n<$deletedchangescount;$n++)
			{
			$templatevars['changesummary'].= $deletedchanges[$n]["field"] . " : " . htmlspecialchars($deletedchanges[$n]["value"]) . "<br>";
			}
		
		$templatevars['url'] = "<a href=\"" . $baseurl . "/plugins/propose_changes/pages/propose_changes.php?ref=" . $ref . "&proposeuser=" . $userref . "\">" . $lang["propose_changes_short"] .  "</a>";
		$message.= $templatevars['changesummary'] . $templatevars['url'];
		
		debug("propose_Changes: sending accepted email to user " . $view_user);
		$notifyuser=get_user($view_user);
		send_mail($notifyuser["email"],$applicationname . ": " . $lang["propose_changes_proposed_changes_reviewed"],$message,"","","emailproposedchangesreviewed",$templatevars);
			
		 
		redirect($baseurl_short."pages/view.php?ref=" . $ref . "&search=" . urlencode($search) . "&offset=" . $offset . "&order_by=" . $order_by . "&sort=".$sort."&archive=" . $archive . "&refreshcollectionframe=true");
		
		}
	else
		{
		// No edit access, save the proposed changes
		$save_errors=save_proposed_changes($ref);   
		$submittedchanges=array();
		$submittedchangescount=0;		
        if ($save_errors===true)
			{
			
			$proposed_changes=get_proposed_changes($ref, $userref);
			
			for ($n=0;$n<count($proposefields);$n++)
				{
				# Has a change to this field been proposed?
				foreach($proposed_changes as $proposed_change)
					{
					if($proposed_change["resource_type_field"]==$proposefields[$n]["ref"])
						{
						$submittedchanges[$submittedchangescount]["field"]=$proposefields[$n]["title"];
						$submittedchanges[$submittedchangescount]["value"]=htmlspecialchars($proposed_change["value"]);
						$submittedchangescount++;; 
						}                
					}
				}	
			
			// send email to admin/resource owner with link	
			
			$templatevars['changesummary']=$lang["propose_changes_summary_changes"] . "<br>";
			for($n=0;$n<$submittedchangescount;$n++)
				{
				$templatevars['changesummary'].= $submittedchanges[$n]["field"] . " : " . htmlspecialchars($submittedchanges[$n]["value"]) . "<br>";
				}
			
			$templatevars['proposer']=(($username=="") ? $username : $userfullname);
			$templatevars['url'] = "<a href=\"" . $baseurl . "/plugins/propose_changes/pages/propose_changes.php?ref=" . $ref . "&proposeuser=" . $userref . "\">" . $lang["propose_changes_review_proposed_changes"] .  "</a>";
			
			$message=$lang["propose_changes_proposed_changes_submitted"] . "<br>";
			$message.=$templatevars['changesummary'];
            $notification_message = $message;
			$message.=$templatevars['proposer'] . $lang["propose_changes_proposed_changes_submitted_text"] . $ref . "<br>";
			$message.= $templatevars['url'];
			
				
			if($propose_changes_notify_admin)
				{				
				debug("propose_changes: sending submitted message/email to admins");
				$admin_notify_emails = array();
                $admin_notify_users = array();
                $notify_users=get_notification_users("RESOURCE_ADMIN");
                foreach($notify_users as $notify_user)
                    {
                    get_config_option($notify_user['ref'],'user_pref_resource_notifications', $send_message);		  
                    if($send_message==false){$continue;}		
                    get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
                    if($send_email && $notify_user["email"]!="")
                        {
                        $admin_notify_emails[] = $notify_user['email'];				
                        }        
                    else
                        {
                        $admin_notify_users[]=$notify_user["ref"];
                        }
                    }
               }
			if($propose_changes_notify_contributor)
				{
				$notify_user=get_user($resource["created_by"]);
				if($notify_user)
					{
					debug("propose_changes: sending notification to resource contributor, " . $notify_user['username'] . "user id#" . $notify_user['ref'] . " (" . $notify_user['email'] . ")");
					get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
                    if($send_email && $notify_user["email"]!="")
                        {
                        $admin_notify_emails[] = $notify_user['email'];				
                        }        
                    else
                        {
                        $admin_notify_users[]=$notify_user["ref"];
                        }
					}
				}
             foreach($admin_notify_emails as $admin_notify_email)
                    {
                    send_mail($admin_notify_email,$applicationname . ": " . $lang["propose_changes_proposed_changes_submitted"],$message,"","","emailproposedchanges",$templatevars);    
                    }
                
                if (count($admin_notify_users)>0)
                    {
                    message_add($admin_notify_users,$notification_message,$baseurl . "/plugins/propose_changes/pages/propose_changes.php?ref=" . $ref . "&proposeuser=" . $userref);
                    }
            		
			foreach($propose_changes_notify_addresses as $propose_changes_notify_address)
				{
				if($propose_changes_notify_address!="")
					{	
					debug("propose_changes: sending submitted email to : ". $propose_changes_notify_address);
					send_mail($propose_changes_notify_address,$applicationname . ": " . $lang["propose_changes_proposed_changes_submitted"],$message,"","","emailproposedchanges",$templatevars);
					}
				}					
			$resulttext=$lang["propose_changes_proposed_changes_submitted"];			
			}			
		}
	}

function is_field_displayed($field)
	{
	global $ref, $resource, $editaccess;
	return !(
		# Field is an archive only field
		($resource["archive"]==0 && $field["resource_type"]==999)
		# User has no read access
		|| !((checkperm("f*") || checkperm("f" . $field["ref"])) && !checkperm("f-" . $field["ref"]) )
		
		# User has edit access to resource but not to this field
		|| ($editaccess && checkperm("F*") && checkperm("F-" . $field["ref"]))
		);
	}


# Allows language alternatives to be entered for free text metadata fields.
function display_multilingual_text_field($n, $field, $translations)
	{
	global $language, $languages, $lang;
	?>
	<p><a href="#" class="OptionToggle" onClick="l=document.getElementById('LanguageEntry_<?php echo $n?>');if (l.style.display=='block') {l.style.display='none';this.innerHTML='<?php echo $lang["showtranslations"]?>';} else {l.style.display='block';this.innerHTML='<?php echo $lang["hidetranslations"]?>';} return false;"><?php echo $lang["showtranslations"]?></a></p>
	<table class="OptionTable" style="display:none;" id="LanguageEntry_<?php echo $n?>">
	<?php
	reset($languages);
	foreach ($languages as $langkey => $langname)
		{
		if ($language!=$langkey)
			{
			if (array_key_exists($langkey,$translations)) {$transval=$translations[$langkey];} else {$transval="";}
			?>
			<tr>
			<td nowrap valign="top"><?php echo htmlspecialchars($langname)?>&nbsp;&nbsp;</td>

			<?php
			if ($field["type"]==0)
				{
				?>
				<td><input type="text" class="stdwidth" name="multilingual_<?php echo $n?>_<?php echo $langkey?>" value="<?php echo htmlspecialchars($transval)?>"></td>
				<?php
				}
			else
				{
				?>
				<td><textarea rows=6 cols=50 name="multilingual_<?php echo $n?>_<?php echo $langkey?>"><?php echo htmlspecialchars($transval)?></textarea></td>
				<?php
				}
			?>
			</tr>
			<?php
			}
		}
	?></table><?php
	}

function display_field($n, $field)
	{
	
	global $ref, $original_fields, $multilingual_text_fields, $is_template, $language, $lang,  $errors, $proposed_changes, $editaccess;
	
    $edit_autosave=false;
	$name="field_" . $field["ref"];
	$value=$field["value"];
	$value=trim($value);
    $proposed_value="";            
	# is there a proposed value set for this field?
	foreach($proposed_changes as $proposed_change)
		{
		if($proposed_change["resource_type_field"]==$field["ref"])
			{
			$proposed_value=$proposed_change["value"]; 
			}                
		}

	// Don't show this if user is an admin viewing proposed changes, needs to be on form so that form is still submitted with all data
	if ($editaccess && $proposed_value=="")
		{
		?>
		<div style="display:none" >
		<?php
		}
		
	
	if ($multilingual_text_fields)
		{
		# Multilingual text fields - find all translations and display the translation for the current language.
		$translations=i18n_get_translations($value);
		if (array_key_exists($language,$translations)) {$value=$translations[$language];} else {$value="";}
		}
	
	?>
        
	<div class="Question ProposeChangesQuestion" id="question_<?php echo $n?>">
                
	<div class="ProposeChangesLabel" ><?php echo htmlspecialchars($field["title"])?></div>
        
	
	<?php 
	# Define some Javascript for help actions (applies to all fields)
	$help_js="onBlur=\"HideHelp(" . $field["ref"] . ");return false;\" onFocus=\"ShowHelp(" . $field["ref"] . ");return false;\"";

	#hook to modify field type in special case. Returning zero (to get a standard text box) doesn't work, so return 1 for type 0, 2 for type 1, etc.
	$modified_field_type="";
	$modified_field_type=(hook("modifyfieldtype"));
	if ($modified_field_type){$field["type"]=$modified_field_type-1;}

	hook("addfieldextras");
	
	// ------------------------------
	// Show existing value so can edit
	
	$value=preg_replace("/^,/","",$field["value"]);
    $realvalue=$value; // Store this in case it gets changed by view processing
	if ($value!="")
            {
            # Draw this field normally.			
            
            # value filter plugin should be used regardless of whether a display template is used.
            if ($field['value_filter']!=""){
                    eval($field['value_filter']);
            }
                    else if ($field["type"]==4 || $field["type"]==6) { 
                            $value=NiceDate($value,false,true);
                    }
            
            ?><div class="propose_changes_current ProposeChangesCurrent"><?php echo $value ?></div><?php                        
                                             
            }
                        
        else
            {
            ?><div class="propose_changes_current ProposeChangesCurrent"><?php echo $lang["propose_changes_novalue"] ?></div>    
            <?php
            }
            ?>
           
        
        <?php
        if(!$editaccess && $proposed_value=="")
            {
            ?>
            <div class="propose_change_button" id="propose_change_button_<?php echo $field["ref"] ?>">
            <input type="submit" value="<?php echo $lang["propose_changes_buttontext"] ?>" onClick="ShowProposeChanges(<?php echo $field["ref"] ?>);return false;" />
            </div>
            <?php
            }?>  
        
	<div class="proposed_change proposed_change_value proposed ProposeChangesProposed" <?php if($proposed_value==""){echo "style=\"display:none;\""; } ?> id="proposed_change_<?php echo $field["ref"] ?>">
    <input type="hidden" id="propose_change_<?php echo $field["ref"] ?>" name="propose_change_<?php echo $field["ref"] ?>" value="true" <?php if($proposed_value==""){echo "disabled=\"disabled\""; } ?> />                                                          
        <?php                                                            
	# ----------------------------  Show field -----------------------------------

	# Checkif we have a proposed value for this field
	if ($proposed_value!="")
		{
		$value=$proposed_value;
		}
	else
		{
		$value = $realvalue;   
		}
        
	$type=$field["type"];
	if ($type=="") {$type=0;} # Default to text type.
	if (!hook("replacefield","",array($field["type"],$field["ref"],$n)))
		{
		global $auto_order_checkbox, $auto_order_checkbox_case_insensitive;
		include dirname(__FILE__) . "/../../../pages/edit_fields/" . $type . ".php";
		}
	# ----------------------------------------------------------------------------
        ?>
        </div><!-- close proposed_change_<?php echo $field["ref"] ?> -->
        <?php
        if($editaccess)
            {
            ?>     
			<div class="ProposeChangesAccept ProposeChangesAcceptDeleteColumn">
            <table>
			<tr>
			<td><input class="ProposeChangesAcceptCheckbox" type="checkbox" id="accept_change_<?php echo $field["ref"] ?>" name="accept_change_<?php echo $field["ref"] ?>" onchange="UpdateProposals(this,<?php echo $field["ref"] ?>);" checked ></input><?php echo $lang["propose_changes_accept_change"] ?></td>
            <td>
			<input class="ProposeChangesDeleteCheckbox" type="checkbox" id="delete_change_<?php echo $field["ref"] ?>" name="delete_change_<?php echo $field["ref"] ?>" onchange="DeleteProposal(this,<?php echo $field["ref"] ?>);" ></input><?php echo $lang["action-delete"] ?></td>
            </tr>
			</table>
			</div>
			<?php    
            }
        
	if (trim($field["help_text"]!=""))
		{
		# Show inline help for this field.
		# For certain field types that have no obvious focus, the help always appears.
		?>
		<div class="FormHelp" style="padding:0;<?php if (!in_array($field["type"],array(2,4,6,7,10))) { ?> display:none;<?php } else { ?> clear:left;<?php } ?>" id="help_<?php echo $field["ref"]?>"><div class="FormHelpInner"><?php echo nl2br(trim(htmlspecialchars(i18n_get_translated($field["help_text"],false))))?></div></div>
		<?php
		}

	# If enabled, include code to produce extra fields to allow multilingual free text to be entered.
	if ($multilingual_text_fields && ($field["type"]==0 || $field["type"]==1 || $field["type"]==5))
		{
		display_multilingual_text_field($n, $field, $translations);
		}
	?>
	<div class="clearerleft"> </div>
	</div><!-- end of question_<?php echo $n?> div -->
	<?php
	
	
	// Don't show this if user is an admin viewing proposed changes
	if ($editaccess && $proposed_value=="")
		{
		?>
		</div><!-- End of hidden field -->
		<?php
		}
	
	}
	
// End of functions, start rendering the page

include "../../../include/header.php";


if (isset($resulttext))
	{
	echo "<div class=\"PageInformal \">" . $resulttext . "</div>";
	}

	
?>

<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoresourceview"]?></a></p>

<div class="BasicsBox" id="propose_changes_box">
<h1 id="editresource">
<?php
if(!$editaccess)
	{ 
	echo $lang['propose_changes_short'];
	}
else
	{
	echo $lang['propose_changes_review_proposed_changes'];
	}
?>
</h1>
<p>
<?php
if(!$editaccess)
	{
	echo $lang['propose_changes_text'];
	}
?>
</p>    
    <?php
	if ($resource["has_image"]==1)
		{
		?><img src="<?php echo get_resource_path($ref,false,"thm",false,$resource["preview_extension"],-1,1,checkperm("w"))?>" class="ImageBorder" style="margin-right:10px;"/>
		<?php
		}
	else
		{
		# Show the no-preview icon
		?>
		<img src="<?php echo $baseurl_short ?>gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],true)?>" />
		<?php
		}
	?>
	
	<div class="Question" id="resource_ref_div" style="border-top:none;">
		<label><?php echo $lang["resourceid"]?></label>
		<div class="Fixed"><?php echo urlencode($ref) ?></div>	
		<div class="clearerleft"> </div>	
	</div>
	<?php
			
	if($editaccess && count($userproposals)>0)
		{
		?>
		<div class="Question" id="ProposeChangesUsers">
		<form id="propose_changes_select_user_form" method="post" action="<?php echo $baseurl_short . "plugins/propose_changes/pages/propose_changes.php" . "?ref=" . urlencode($ref) . "&amp;search=" . urlencode($search) . "&amp;offset=" . urlencode($offset) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive)?>"
			<label><?php echo $lang["propose_changes_view_user"]; ?>
			</label>
			<select class="stdwidth" name="proposeuser" id="proposeuser" onchange="CentralSpacePost(document.getElementById('propose_changes_form'),false);">
			<?php 
			foreach ($userproposals as $userproposal)
				{
				echo  "<option value=" . $userproposal["user"] . " " . (($view_user==$userproposal["user"])?"selected":"") . ">" . $userproposal["username"] . "</option>";				
				}	
			?>
			</select>
			</form>
		</div>
		<?php
		}	

	$display_any_fields=false;
	$fieldcount=0;
	for ($n=0;$n<count($proposefields);$n++)
		{
		node_field_options_override($proposefields[$n]);

		if (is_field_displayed($proposefields[$n]))
			{
			$display_any_fields=true;
			break;
			}
		}
	if ($display_any_fields)
		{
		?>
		
	<form id="propose_changes_form" method="post" action="<?php
    echo $baseurl_short . "plugins/propose_changes/pages/propose_changes.php" . "?ref=" . urlencode($ref) . "&amp;search=" . urlencode($search) . "&amp;offset=" . urlencode($offset) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive) ;
    ?>">
	<h2 id="ProposeChangesHead"><?php echo $lang["propose_changes_proposed_changes"] ?></h2><?php
		?><div id="ProposeChangesSection">
                <div class="Question ProposeChangesQuestion" id="propose_changes_field_header" >
                        
                <div class="ProposeChangesTitle ProposeChangesLabel" ><?php echo $lang["propose_changes_field_name"] ?></div>                
                <div class="ProposeChangesTitle ProposeChangesCurrent"><?php echo $lang["propose_changes_current_value"] ?></div>
                <div class="ProposeChangesTitle ProposeChangesProposed" ><?php echo $lang["propose_changes_proposed_value"] ?></div>
                
                <?php
                if($editaccess)
                    {
                    ?> 
					<div class="ProposeChangesTitle ProposeChangesAcceptDeleteColumn" id="ProposeChangesAcceptDeleteColumn">
					<table>
                    <tr>
					<td>
					<input id="ProposeChangesAcceptAllCheckbox" class="ProposeChangesAcceptCheckbox" type="checkbox" name="accept_all_changes" onClick="ProposeChangesUpdateAll(this);" checked ><?php echo $lang["propose_changes_accept_change"] ?>
					</td>
					<td>
					<input id="ProposeChangesDeleteAllCheckbox" class="ProposeChangesDeleteCheckbox" type="checkbox" name="delete_all_changes" onClick="ProposeChangesDeleteAll(this);" ><?php echo $lang["action-delete"] ?>
                   </td>
				   </tr>
				   </table>
				   </div>
				   <?php    
                    }
                ?>              
                <div class="clearerleft"> </div>
                </div><!-- End of propose_changes_field_header -->
                
                <?php
                
		}

	for ($n=0;$n<count($proposefields);$n++)
		{
		node_field_options_override($proposefields[$n]);

		# Should this field be displayed?
		if (is_field_displayed($proposefields[$n]))
			{	
			$fieldcount++;
			display_field($n, $proposefields[$n]);
			}
		}	

	// Let admin know there are no proposed changes anymore for this reosurces
	// Can happen when another admin already reviewed the changes.
	$changes_to_review_counter = 0;
	foreach($proposefields as $propose_field)
		{

		foreach($proposed_changes as $proposed_change)
			{
			if($proposed_change['resource_type_field'] == $propose_field['ref'])
				{
				$changes_to_review_counter++;
				}
			}

		}

	if($editaccess && empty($propose_changes) && $changes_to_review_counter == 0)
		{
		?>
		<div id="message" class="Question ProposeChangesQuestion">
			<?php echo $lang['propose_changes_no_changes_to_review']; ?>
		</div>
		<?php
		}
	?>

	<div class="QuestionSubmit">
	<input name="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
        <?php if($editaccess)
            {?>
			<input name="submitted" type="hidden" value="true" />
            <input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["propose_changes_save_changes"]?>&nbsp;&nbsp;" /><br />            
            <?php
            }
        else
            {?>
            <input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" /><br />
            <?php
            }
            ?>
	<div class="clearerleft"> </div>
	</div>

</form><!-- End of propose_changes_form -->

</div><!-- End of propose_changes_box -->
</div><!-- End of BasicsBox -->
<script>

function ShowHelp(field)
{
    // Show the help box if available.
    if (document.getElementById('help_' + field))
    {
       jQuery('#help_' + field).fadeIn();
    }
 }
 
 function HideHelp(field)
 {
    // Hide the help box if available.
    if (document.getElementById('help_' + field))
    {
       document.getElementById('help_' + field).style.display='none';
    }
 }
 
function ShowProposeChanges(fieldref)
	{
	//fieldid="#proposed_change_" + fieldref;
	jQuery('#proposed_change_' + fieldref).show();        
	jQuery('#propose_change_button_' + fieldref).hide();
	return false;
	}
        
function UpdateProposals(checkbox, fieldref)
    {
    if (checkbox.checked)
        {
        jQuery('#field_' + fieldref).removeAttr('disabled'); 
		jQuery('#propose_change_' + fieldref).removeAttr('disabled')
		checkprefix="input[id^=" + fieldref + "_]";		
		jQuery(checkprefix).removeAttr('disabled');//enable checkboxes
        }
    else
        {        
        jQuery('#field_' + fieldref).attr('disabled','disabled'); 
        jQuery('#propose_change_' + fieldref).attr('disabled','disabled');      
        }
    }
    
function DeleteProposal(checkbox, fieldref)
    {
	if (checkbox.checked)
        {            
        jQuery('#field_' + fieldref).attr('disabled','disabled'); 
		checkprefix="input[id^=" + fieldref + "_]";
		jQuery(checkprefix).attr('disabled','disabled'); //disable checkboxes
        jQuery('#accept_change_' + fieldref).removeAttr('checked');    
        jQuery('#accept_change_' + fieldref).attr('disabled','disabled'); 
        }
    else
        {  
        jQuery('#accept_change_' + fieldref).removeAttr('disabled');              
        }
    }
    
        
function ProposeChangesUpdateAll(checkbox)
    {
    if (checkbox.checked)
        {
        jQuery('.ProposeChangesAcceptCheckbox').attr('checked','checked'); 		
        jQuery('.ProposeChangesDeleteCheckbox').removeAttr('checked'); 
        jQuery('.ProposeChangesAcceptCheckbox').removeAttr('disabled');
        }
    else
        {  
        jQuery('.ProposeChangesAcceptCheckbox').removeAttr('checked'); 
        }
    
    jQuery('.ProposeChangesAcceptCheckbox').trigger('change');
    }
	
function ProposeChangesDeleteAll(checkbox)
    {
    if (checkbox.checked)
        {
        jQuery('.ProposeChangesDeleteCheckbox').attr('checked','checked');  
        jQuery('.ProposeChangesAcceptCheckbox').removeAttr('checked');  
        jQuery('.ProposeChangesAcceptCheckbox').attr('disabled','disabled'); 		
        }
    else
        {  
        jQuery('.ProposeChangesDeleteCheckbox').removeAttr('checked'); 
        jQuery('.ProposeChangesAcceptCheckbox').removeAttr('disabled');    
        }
    
    jQuery('.ProposeChangesAcceptCheckbox').trigger('change');
    }


</script>


<?php 


include "../../../include/footer.php";

