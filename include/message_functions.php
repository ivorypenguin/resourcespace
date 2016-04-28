<?php

// enumerated types of message.  Note the base two offset for binary combination.
DEFINE ("MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN",1);
DEFINE ("MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL",2);
DEFINE ("MESSAGE_ENUM_NOTIFICATION_TYPE_RESERVED_1",4);
DEFINE ("MESSAGE_ENUM_NOTIFICATION_TYPE_RESERVED_2",8);
DEFINE ("MESSAGE_ENUM_NOTIFICATION_TYPE_RESERVED_3",16);

DEFINE ("MESSAGE_DEFAULT_TTL_SECONDS",60 * 60 * 24 * 7);		// 7 days

// ------------------------------------------------------------------------------------------------------------------------

// gets messages for a given user (return true if there are messages, if not false)
// note that messages are passed by reference.
function message_get(&$messages,$user,$get_all=false,$sort_desc=false)
	{
	$messages=sql_query("SELECT user_message.ref, user.username AS owner, user_message.seen, message.created, message.expires, message.message, message.url " .
		"FROM `user_message`
		INNER JOIN `message` ON user_message.message=message.ref " .
		"LEFT OUTER JOIN `user` ON message.owner=user.ref " .
		"WHERE user_message.user='{$user}'" .
		($get_all ? " " : " AND message.expires > NOW()") .
		($get_all ? " " : " AND user_message.seen='0'") .
		" ORDER BY user_message.ref " . ($sort_desc ? "DESC" : "ASC"));
	return(count($messages) > 0);
	}

// ------------------------------------------------------------------------------------------------------------------------

// add a message.
function message_add($users,$text,$url="",$owner=null,$notification_type=MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,$ttl_seconds=MESSAGE_DEFAULT_TTL_SECONDS, $related_activity=0, $related_ref=0)
	{
	global $userref,$applicationname,$lang;

	$text = escape_check($text);
	$url = escape_check($url);

	if (!is_array($users))
		{
		$users=array($users);
		}

	if(is_null($owner))
		{
		$owner=$userref;
		}

	sql_query("INSERT INTO `message` (`owner`, `created`, `expires`, `message`, `url`, `related_activity`, `related_ref`) VALUES ('{$owner}', NOW(), DATE_ADD(NOW(), INTERVAL {$ttl_seconds} SECOND), '{$text}', '{$url}', '{$related_activity}', '{$related_ref}' )");
	$message_ref = sql_insert_id();

	foreach($users as $user)
		{
		sql_query("INSERT INTO `user_message` (`user`, `message`) VALUES ($user,$message_ref)");
		
		// send an email if the user has notifications and emails setting and the message hasn't already been sent via email
		if($notification_type!=MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL && $notification_type!=(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN))
			{
			get_config_option($user,'email_and_user_notifications', $send_email);
			if($send_email)
				{
				$email_to=sql_value("select email value from user where ref={$user}","");
				if($email_to!=='')
					{
					send_mail($email_to,$applicationname . ": " . $lang['notification_email_subject'],$text . "<br/><br/>" . $url);
					}
				}
			}
		}

	}

// ------------------------------------------------------------------------------------------------------------------------

// remove a message from message table and associated user_messages
function message_remove($message)
	{
	sql_query("DELETE FROM user_message WHERE message='{$message}'");
	sql_query("DELETE FROM message WHERE ref='{$message}'");	
	}

// ------------------------------------------------------------------------------------------------------------------------

function message_seen($message,$seen_type=MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN)
	{
	sql_query("UPDATE `user_message` SET seen=seen | {$seen_type} WHERE `ref`='{$message}'");
	}
    
// ------------------------------------------------------------------------------------------------------------------------

function message_unseen($message)
	{
	sql_query("UPDATE `user_message` SET seen='0' WHERE `ref`='{$message}'");
	}

// ------------------------------------------------------------------------------------------------------------------------

// flags all non-read messages as read for given user and seen type
function message_seen_all($user,$seen_type=MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN)
	{
	$messages = array();
	if (message_get($messages,$user,true))
		{
		foreach($messages as $message)
			{             
			message_seen($message['ref']);
			}
		}
	}

// ------------------------------------------------------------------------------------------------------------------------

// remove all messages from message and user_message tables that have expired (regardless of read).  This will be called
// from a cron job.
function message_purge()
	{
	sql_query("DELETE FROM user_message WHERE message IN (SELECT ref FROM message where expires < NOW())");
	sql_query("DELETE FROM message where expires < NOW()");
	}

// ------------------------------------------------------------------------------------------------------------------------

// Send a summary of all unread notifications as an email
// from the standard cron_copy_hitcount

function message_send_unread_emails()
	{
	global $lang, $applicationname;
	$lastrun = sql_value("select value from sysvars where name='daily_digest'",'');
	
	# Exit if already sent in last 24 hours;
	if ($lastrun!="" && time()-strtotime($lastrun)<(60*60*24)) {return false;}
	
	# Get all unreads notifications. 
	$unreadmessages=sql_query("select u.ref as userref, u.email, m.ref as messsageref, m.message, m.created, m.url from user_message um join user u on u.ref=um.user join message m on m.ref=um.message where um.seen=0 and u.email<>'' and m.created>'" . $lastrun . "' order by userref, m.created asc");
		
	$lastuseremail="";
	
	foreach($unreadmessages as $unreadmessage)
		{
		$currentuseremail = $unreadmessage["email"];	
		if($currentuseremail != $lastuseremail)
			{
			// Send the last email if we have one and start a new email for the next user
			if($lastuseremail!='')
				{
				echo "Checking config for user: " . $lastuseremail . "\r\n";
	            get_config_option($lastuserref,'user_pref_daily_digest', $send_message);		  
				if($send_message)
					{
					echo "Sending summary\r\n";
					$message .= "</table>";
					// Send mail
					send_mail($lastuseremail,$applicationname . ": " . $lang["email_daily_digest_subject"],$message); 

					get_config_option($lastuserref,'user_pref_daily_digest_mark_read', $mark_read);
					if($mark_read)
						{
						sql_query("update user_message set seen='" . MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL . "' where message in ('" . implode("','",$messagerefs) . "') and user = '" . $lastuserref . "'");
						}
					}
				}
			// Start the new email
			$messagerefs=array();
			$message = $lang['email_daily_digest_text'] . "<br /><br />";
			$message .= "<style>.InfoTable td {padding:5px; margin: 0px;border: 1px solid #000;}</style><table class='InfoTable'>";
			$message .= "<tr><th>" . $lang["columnheader-date_and_time"] . "</th><th>" . $lang["message"] . "</th><th></th></tr>";
			$lastuseremail = $currentuseremail;	
			$lastuserref = $unreadmessage["userref"];			
			}
		$message .= "<tr><td>" . nicedate($unreadmessage["created"], true) . "</td><td>" . $unreadmessage["message"] . "</td><td><a href='" . $unreadmessage["url"] . "'>" . $lang["link"] . "</a></td></tr>";
		$messagerefs[]=$unreadmessage["messsageref"];
		}
		
	sql_query("delete from sysvars where name='daily_digest'");
	sql_query("insert into sysvars(name,value) values ('daily_digest',now())");
	}

    
// ------------------------------------------------------------------------------------------------------------------------
// Remove all messages related to a certain activity (e.g. resource request or resource submission) matching the given ref(s)
function message_remove_related($remote_activity=0,$remote_refs=array())
	{
	if($remote_activity==0 || $remote_refs==0 || count($remote_refs)==0 ){return false;}
	if(!is_array($remote_refs)){$remote_refs=array($remote_refs);}
    $relatedmessages = sql_array("select ref value from message where related_activity='$remote_activity' and related_ref in (" . implode(',',$remote_refs) . ");","");
    if(count($relatedmessages)>0)
        {            
        sql_query("DELETE FROM message WHERE ref in (" . implode(',',$relatedmessages) . ");");
        sql_query("DELETE FROM user_message WHERE message in (" . implode(',',$relatedmessages) . ");");
        }
	}
