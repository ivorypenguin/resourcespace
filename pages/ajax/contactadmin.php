<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; #if (!checkperm("s")) {exit ("Permission denied.");}
include_once "../../include/collections_functions.php";
include "../../include/resource_functions.php";
include "../../include/search_functions.php";


# Is this an ajax call from the view page?
$insert=getvalescaped("insert","");
$ref=getvalescaped("ref","",true);

# Load access level
$access=get_resource_access($ref);
# check permissions (error message is not pretty but they shouldn't ever arrive at this page unless entering a URL manually)
if ($access==2) 
		{
		exit("This is a confidential resource.");
		}

# Fetch resource data
$resource=get_resource_data($ref);if ($resource===false) {exit($lang['resourcenotfound']);}

$imagename=i18n_get_translated($resource["field".$view_title_field]);


if (getval("send","")!="")
	{
	$messagetext=getvalescaped("messagetext","");	
	$templatevars['url']=$baseurl . "/?r=" . $ref;
	$templatevars['fromusername']=($userfullname=="" ? $username : $userfullname);
	$templatevars['resourcename']=$imagename;
	$templatevars['emailfrom']=$useremail;
	$subject=$templatevars['fromusername'] . $lang["contactadminemailtext"];
	$templatevars['message']=$messagetext;
	$message=$templatevars['fromusername'] . ($useremail!="" ? " (" . $useremail . ")" : "") . $lang["contactadminemailtext"] . "\n\n" . $messagetext . "\n\n" . $lang["clicktoviewresource"] . "\n\n" . $templatevars['url'];	
	$notification_message = $templatevars['fromusername'] . ($useremail!="" ? " (" . $useremail . ")" : "") . $lang["contactadminemailtext"] . "\n\n" . $messagetext . "\n\n" . $lang["clicktoviewresource"];
	
	global $watermark; 
	$templatevars['thumbnail']=get_resource_path($ref,true,"thm",false,"jpg",$scramble=-1,$page=1,($watermark)?(($access==1)?true:false):false);
	if (!file_exists($templatevars['thumbnail'])){
			$templatevars['thumbnail']="../gfx/".get_nopreview_icon($resource["resource_type"],$resource["file_extension"],false);
		}	
	
	# Build message and send.	
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
	foreach($admin_notify_emails as $admin_notify_email)
		{
		send_mail($admin_notify_email,$subject,$message,$applicationname,$email_from,"emailcontactadmin",$templatevars,$applicationname);
    	}
	
	if (count($admin_notify_users)>0)
		{
        message_add($admin_notify_users,$notification_message,$templatevars['url']);
		}
	
	
	
	exit("SUCCESS");	
	}

if ($insert=="")
	{
	# Fetch search details (for next/back browsing and forwarding of search params)
	$search=getvalescaped("search","");
	$order_by=getvalescaped("order_by","relevance");
	$offset=getvalescaped("offset",0,true);
	$default_sort="DESC";
	if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
	$sort=getval("sort",$default_sort);
	$archive=getvalescaped("archive",0,true);

	include "../../include/header.php";		
	?>
	<p><a href="<?php echo $baseurl ?>/pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>
	<h1><?php echo $lang["contactadmin"]?></h1>				
	<div>		
		
	<?php

	if ($resource["has_image"]==1)
		{
		?><img align="top" src="<?php echo get_resource_path($ref,false,($edit_large_preview?"pre":"thm"),false,$resource["preview_extension"],-1,1,checkperm("w"))?>" alt="<?php echo $imagename ?>" class="Picture"/><br />
		<?php
		}
	else
		{
		# Show the no-preview icon
		?>
		<img src="<?php echo $baseurl_short ?>gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],true)?>" alt="<?php echo $imagename ?>" class="Picture"/>
		<?php
		}?>
		
	</div>
	
	
	
	<?php }?>

<script>

function sendResourceMessage()
	{
        if (!jQuery('#messagetext').val()) {
		return false;
		}
	jQuery.ajax({
		type: "POST",
		data: jQuery('#contactadminform').serialize(),
		url: baseurl_short+"pages/ajax/contactadmin.php?ref="+<?php echo $ref ?>+"&insert=true&send=true",
		success: function(html){						
				//jQuery('#RecordDownload li:last-child').after(html);
				if(html=="SUCCESS")
					{alert('<?php echo $lang["emailsent"] ?>');}
				else
					{alert('<?php echo $lang["error"] ?>\n' + html);}
				jQuery('#messagetext').val("");
				jQuery('#contactadminbox').slideUp();
				},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('<?php echo $lang["error"] ?>\n' + textStatus);
			}
		});
	}

</script>
<div class="clearerleft"> </div>
<div id="contactadminbox">
<p><php echo $lang["contactadmin"] ?></p>
<form name="contactadminform" method=post id="contactadminform" action="<?php echo $baseurl_short?>pages/ajax/contactadmin.php?ref=<?php echo $ref ?>">
<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">

<div>
<p><?php echo $lang["contactadminintro"]?></p>
<textarea rows=6 name="messagetext" id="messagetext"></textarea>
<div class="clearerleft"> </div>

<div id="contactadminbuttons">
<input name="send" type="submit" class="contactadminbutton" value="&nbsp;&nbsp;<?php echo $lang["send"]?>&nbsp;&nbsp;" onClick="sendResourceMessage();return false;" />
<input name="cancel" type="submit" class="contactadminbutton" value="&nbsp;&nbsp;<?php echo $lang["cancel"]?>&nbsp;&nbsp;" onClick="jQuery('#contactadminbox').slideUp();return false;" />
</div>
</div>


</form>
</div>

<?php 
if ($insert=="")
	{
	include "../../include/footer.php";
	}
