<?php
include(dirname(__FILE__) . "/../include/db.php");
include_once(dirname(__FILE__) . "/../include/general.php");
include(dirname(__FILE__) . "/../include/image_processing.php");
include(dirname(__FILE__) . "/../include/resource_functions.php");

# Fetch expired resources
$expired=sql_query('select r.ref,r.field8 as title from resource r join resource_data rd on r.ref=rd.resource join resource_type_field rtf on rd.resource_type_field=rtf.ref and rtf.type=6 where 
r.expiry_notification_sent<>1 and rd.value<>"" and rd.value<=now()');

if (count($expired)>0)
	{
	# Send notifications
	$refs=array();
	$body=$lang["resourceexpirymail"] . "\n";
	foreach ($expired as $resource)
		{
		$refs[]=$resource["ref"];
		echo "<br>Sending expiry notification for: " . $resource["ref"] . " - " . $resource["title"];
		
		$body.="\n" . $resource["ref"] . " - " . $resource["title"];
		$body.="\n" . $baseurl . "/?r=" . $resource["ref"] . "\n";
		}
	
	$url = $baseurl . "/pages/search.php?search=!list" . implode(":",$refs);
	
	$admin_notify_emails = array();
	$admin_notify_users = array();
	if (isset($expiry_notification_mail))
		{
		$admin_notify_emails[] = $expiry_notification_mail;	
		}
	else
		{
		$notify_users=get_notification_users("RESOURCE_ADMIN");
		foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_notifications', $send_message);		  
			if($send_message==false){$continue;}		
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
				echo "Sending email to " . $notify_user["email"] . "\r\n";
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
			# Send mail
			send_mail($admin_notify_email,$lang["resourceexpiry"],$body);
			}
			
	if (count($admin_notify_users)>0)
		{
		echo "Sending notification to user refs: " . implode(",",$admin_notify_users) . "\r\n";
		message_add($admin_notify_users,$lang["resourceexpirymail"],$url,0);
		}	

	# Update notification flag so an expiry is not sent again until the expiry field(s) is edited.
	sql_query("update resource set expiry_notification_sent=1 where ref in (" . join(",",$refs) . ")");
	}
else
	{
	echo "Nothing to do.";
	}




?>
