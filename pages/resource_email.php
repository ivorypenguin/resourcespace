<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; 
include "../include/resource_functions.php";
include "../include/search_functions.php";
include_once "../include/collections_functions.php";

$ref=getvalescaped("ref","",true);
// Fetch resource data
$resource=get_resource_data($ref);if ($resource===false) {exit($lang['resourcenotfound']);}

// fetch the current search 
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);

$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getvalescaped("sort",$default_sort_direction);

$useraccess=get_resource_access($ref);

// Check if sharing permitted
if (!can_share_resource($ref,$useraccess)) {exit($lang["error-permissiondenied"]);}

$user_select_internal=checkperm("noex");

$errors="";
if (getval("save","")!="")
	{
	// Build a new list and insert
	$users=getvalescaped("users","");
	$message=getvalescaped("message","");
	$access=getvalescaped("access","");
	$add_internal_access=(getvalescaped("grant_internal_access","")!="");
	if (hook("modifyresourceaccess")){$access=hook("modifyresourceaccess");}
	$expires=getvalescaped("expires","");
	$group=getvalescaped("usergroup","");
	$list_recipients=getvalescaped("list_recipients",""); if ($list_recipients=="") {$list_recipients=false;} else {$list_recipients=true;}
	
	$use_user_email=getvalescaped("use_user_email",false);
	if ($use_user_email){$user_email=$useremail;} else {$user_email="";} // if use_user_email, set reply-to address
	if (!$use_user_email){$from_name=$applicationname;} else {$from_name=$userfullname;} // make sure from_name matches system name
	
	if (getval("ccme",false)){ $cc=$useremail;} else {$cc="";}
	
	$sharing_related=false;
	if(getval("sharerelatedresources","")!=""){$sharing_related=true;}
	if($sharing_related || $share_resource_as_collection)
		{
		if($sharing_related)
			{
			// User has chosen to include related resources, so treat as sharing a new collection
			$relatedshares=explode(",",getvalescaped("sharerelatedresources",""));
			}
		// Create new collection
		$allow_changes=(getval("allow_changes","")!=""?1:0);
		$sharedcollection=create_collection($userref,i18n_get_translated($resource["field".$view_title_field]) . " Share " . nicedate(date("Y-m-d H:i:s")),$allow_changes);
		
		add_resource_to_collection($ref,$sharedcollection);
		if($sharing_related)
			{
			foreach($relatedshares as $relatedshare)
				{
				add_resource_to_collection($relatedshare,$sharedcollection);
				}			
			}
			
		$errors=email_collection($sharedcollection,i18n_get_collection_name($sharedcollection),$userfullname,$users,$message,false,$access,$expires,$user_email,$from_name,$cc,false,"","",$list_recipients,$add_internal_access);
		// Hide from drop down by default
		show_hide_collection($sharedcollection, false, $userref);
		
		if ($errors=="")
			{
			// Log this	
			// fix for bomb on multiple collections, daily stat object ref must be a single number.
			$crefs=explode(",",$ref);
			foreach ($crefs as $cref){		
				daily_stat("E-mailed collection",$cref);
			}
			if (!hook("replacecollectionemailredirect")){
				redirect($baseurl_short."pages/done.php?text=collection_email");
				}
			}
		}
	else
		{
		// Email single resource
		$errors=email_resource($ref,i18n_get_translated($resource["field".$view_title_field]),$userfullname,$users,$message,$access,$expires,$user_email,$from_name,$cc,$list_recipients,$add_internal_access,$useraccess,$group);
		if ($errors=="")
			{
			// Log this			
			daily_stat("E-mailed resource",$ref);
			if (!hook("replaceresourceemailredirect")){
				redirect("pages/done.php?text=resource_email&resource=".urlencode($ref)."&search=".urlencode($search)."&offset=".urlencode($offset)."&order_by=".urlencode($order_by)."&sort=".urlencode($sort)."&archive=".urlencode($archive));
			}
			}
		}
	}

include "../include/header.php";
?>
<div class="BasicsBox">
<p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>
<h1><?php echo $lang["emailresourcetitle"]?></h1>

<p><?php echo text("introtext")?></p>

<form method=post id="resourceform" action="<?php echo $baseurl_short?>pages/resource_email.php?search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>">
<input type=hidden name=ref value="<?php echo htmlspecialchars($ref)?>">

<div class="Question">
<label><?php echo $lang["resourcetitle"]?></label><div class="Fixed"><?php echo htmlspecialchars(i18n_get_translated($resource["field".$view_title_field]))?></div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label><?php echo $lang["resourceid"]?></label><div class="Fixed"><?php echo $resource["ref"]?></div>
<div class="clearerleft"> </div>
</div>



<?php
// -------- Related Resources (must be able to search for this to work)
if ($share_resource_include_related && $enable_related_resources && checkperm("s") && ($k==""))
	{
		
	$result=do_search("!related" . $ref);
	if (count($result)>0) 
		{
		?>
		<div class="Question" id="sharerelatedresources">
		<label><?php echo $lang["sharerelatedresources"]?></label>
		<input type="hidden" name="sharerelatedresources" id="sharerelatedresourcesfield"  value="" >
		<div class="sharerelatedresources">
		<?php
	
			for ($n=0;$n<count($result);$n++)
				{
				$related_restype=$result[$n]["resource_type"];
				$related_restypes[]=$related_restype;
				}
			//reduce array to unique values
			$related_restypes=array_unique($related_restypes);
			$count_restypes=0;
			foreach($related_restypes as $rtype)
				{
				?>
				<div class="sharerelatedtype">
				<?php
				$restypename=sql_value("select name as value from resource_type where ref = '$rtype'","");
				$restypename = lang_or_i18n_get_translated($restypename, "resourcetype-", "-2");
				?><!--Panel for related resources-->
				
				<div class="Title"><?php echo $restypename ?></div>
				<?php
				// loop and display the results by resource type
				for ($n=0;$n<count($result);$n++)			
					{	
					if ($result[$n]["resource_type"]==$rtype){
						$rref=$result[$n]["ref"];
						$title=$result[$n]["field".$view_title_field];

						// swap title fields if necessary

						if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
							{
							if ($result[$n]['resource_type']==$metadata_template_resource_type)
								{
								$title=$result[$n]["field".$metadata_template_title_field];
								}	
							}	
								
						?>
						
						<!--Resource Panel-->
						<div class="ResourcePanelShellSmall">
						<table border="0" class="ResourceAlignSmall"><tr><td>
						<a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>&search=<?php echo urlencode("!related" . $ref)?>" onClick="return CentralSpaceLoad(this,true);"><?php if ($result[$n]["has_image"]==1) { ?><img border=0 src="<?php echo get_resource_path($rref,false,"col",false,$result[$n]["preview_extension"],-1,1,checkperm("w"),$result[$n]["file_modified"])?>" class="CollectImageBorder"/><?php } else { ?><img border=0 src="../gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true)?>"/><?php } ?></a></td>
						</tr></table>
						<div class="ResourcePanelInfo"><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $rref?>" onClick="return CentralSpaceLoad(this,true);"><?php echo tidy_trim(i18n_get_translated($title),15)?></a>&nbsp;</div>
						<div class="ResourcePanelIcons"><input type="checkbox" id="share<?php echo $rref ?>" class="checkselect" onChange="UpdateRelatedField(this,<?php echo $rref ?>);"></div>
						
						</div>
						<?php		
						}
					}
					?>
					</div><!-- end of sharerelatedtype -->
					<div class="clearerleft"> </div>
					<?php
				} //end of display loop by resource extension
		
		?>
		</div>
		<div class="clearerleft"> </div>
		</div>
		
		<div class="Question">
		<label for="allow_changes"><?php echo $lang["sharerelatedresourcesaddremove"]?></label><input type=checkbox id="allow_changes" name="allow_changes" >
		<div class="clearerleft"> </div>
		</div>
		
		<script>
		
		sharerelated=new Array();
		
		function UpdateRelatedField(checkbox, ref)
			{
			if(checkbox.checked)
				{
				sharerelated.push(ref);
				}
			else
				{				
				sharerelated.splice(jQuery.inArray(ref, sharerelated), 1 );
				}
			jQuery('#sharerelatedresourcesfield').val(sharerelated);
			}		
		</script>
		<?php
		} 
	// -------- End Related Resources
	}
	


 hook("resemailmoreinfo"); ?>

<div class="Question">
<label for="message"><?php echo $lang["message"]?></label><textarea class="stdwidth" rows=6 cols=50 name="message" id="message"></textarea>
<div class="clearerleft"> </div>
</div>

<?php if(!hook("replaceemailtousers")){?>
<div class="Question">
	<label for="users">
	<?php echo ($user_select_internal)?$lang["emailtousers_internal"]:$lang["emailtousers"]; ?>
	</label>

	<?php

include "../include/user_select.php"; ?>
<div class="clearerleft"> </div>
<?php if ($errors!="") { ?><div class="FormError">!! <?php echo $errors?> !!</div><?php } ?>
</div>
<?php } ?>

<?php if ($list_recipients){?>
<div class="Question">
<label for="list_recipients"><?php echo $lang["list-recipients-label"]; ?></label><input type=checkbox id="list_recipients" name="list_recipients">
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php if($useraccess==0)
	{
	$resourcedata=get_resource_data($ref,true);
	if(get_edit_access($ref,$resource['archive'],false,$resource))
		{?>
		<div class="Question">
		<label for="grant_internal_access"><?php echo $lang["internal_share_grant_access"] ?></label>
		<input type=checkbox id="grant_internal_access" name="grant_internal_access" onClick="if(this.checked){jQuery('#question_internal_access').slideDown();}else{jQuery('#question_internal_access').slideUp()};">
		<div class="clearerleft"> </div>
		</div>
		<?php
		}
	}?>


<?php 

if(!$user_select_internal)
	{

	if(!hook("replaceemailaccessselector")){?>
	<div class="Question" id="question_access">
	<label for="access"><?php echo $lang["externalselectresourceaccess"]?></label>
	<select class="stdwidth" name="access" id="access">
	<?php
	// List available access levels. The highest level must be the minimum user access level.
	for ($n=$useraccess;$n<=1;$n++)  { ?>
	<option value="<?php echo $n?>"><?php echo $lang["access" . $n]?></option>
	<?php } ?>
	</select>
	<div class="clearerleft"> </div>
	</div>
	<?php } ?>



	<?php if(!hook("replaceemailexpiryselector")){?>
	<div class="Question">
	<label><?php echo $lang["externalselectresourceexpires"]?></label>
	<select name="expires" class="stdwidth">
	<?php if($resource_share_expire_never) { ?><option value=""><?php echo $lang["never"]?></option><?php }
					
	for ($n=1;$n<=150;$n++)
		{
		$date = time()+(60*60*24*$n);
		$d    = date("D",$date);
		$option_class = '';
		if (($d == "Sun") || ($d == "Sat"))
			{
			$option_class = 'optionWeekend';
			} ?>
		<option class="<?php echo $option_class ?>" value="<?php echo date("Y-m-d",$date)?>"><?php echo nicedate(date("Y-m-d",$date),false,true)?></option>
		<?php
		}
	?>
	</select>
	<div class="clearerleft"> </div>
	</div>
	<?php } ?>


	<?php if (checkperm("x")) {
	# Allow the selection of a user group to inherit permissions from for this share (the default is to use the current user's user group).
	?>
	<div class="Question">
	<label for="groupselect"><?php echo $lang["externalshare_using_permissions_from_user_group"] ?></label>
	<select id="groupselect" name="usergroup" class="stdwidth">
    <?php
    $grouplist = get_usergroups(true);
    foreach($grouplist as $group)
        {
        if(!empty($allowed_external_share_groups) && !in_array($group['ref'], $allowed_external_share_groups))
            {
            continue;
            }
        ?>
        <option value="<?php echo $group["ref"] ?>" <?php if ($usergroup==$group["ref"]) { ?>selected<?php } ?>><?php echo $group["name"] ?></option>
        <?php
        }
        ?>
	</select>
	<div class="clearerleft"> </div>
	</div>
	<?php } 
	}
	?>

<?php hook("resourceemailafterexternal");?>

<?php if ($email_from_user && !$always_email_from_user){?>
<?php if ($useremail!="") { // Only allow this option if there is an email address available for the user.
?>
<div class="Question">
<label for="use_user_email"><?php echo $lang["emailfromuser"].$useremail.". ".$lang["emailfromsystem"].$email_from ?></label><input type=checkbox checked id="use_user_email" name="use_user_email">
<div class="clearerleft"> </div>
</div>
<?php } ?>
<?php } ?>

<?php if ($cc_me && $useremail!=""){?>
<div class="Question">
<label for="ccme"><?php echo str_replace("%emailaddress", $useremail, $lang["cc-emailaddress"]); ?></label><input type=checkbox checked id="ccme" name="ccme">
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php hook("additionalemailfield");?>

<?php if(!hook("replaceemailsubmitbutton")){?>
<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["emailresourcetitle"]?>&nbsp;&nbsp;" />
</div>
<?php } // end replaceemailsubmitbutton ?>

</form>
</div>

<?php		
include "../include/footer.php";
?>
