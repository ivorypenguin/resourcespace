<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/header.php";

if (getval("allseen","")!="")
  {
  // Acknowledgement all messages
  message_seen_all($userref);
  }
?>
<div class="BasicsBox">
  <h1><?php echo $lang["mymessages"]?></h1>
  <p><?php echo $lang["mymessages_introtext"] ?></p>


<div class="VerticalNav">
<ul>
<li>
<a href="<?php echo $baseurl_short?>pages/user/user_preferences.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["userpreferences"];?></a>
</li>
<?php
	$messages=array();
	if (!message_get($messages,$userref,true,true))		// if no messages get out of here with a message
		{
		?>
		</ul>
		</div> <!-- End of VerticalNav -->
		</div> <!-- End of BasicsBox -->
		<?php
		echo $lang['mymessages_youhavenomessages'];
		include "../../include/footer.php";
		return;
		}
		
	$unread = false;

	foreach ($messages as $message)		// if there are unread messages show option to mark all as read
		{
		if ($message['seen']==0)
			{
			$unread=true;
			break;
			}
		}
	if ($unread)
		{
?><li>
  <a href="<?php echo $baseurl_short?>pages/user/user_messages.php?allseen=<?php echo $userref; ?>" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang['mymessages_markallread']; ?></a>
  </li>
<?php
		}
?>
</ul>
</div> <!-- End of VerticalNav -->

<div class="Listview" id="user_messages">
	<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
		<tr class="ListviewTitleStyle">
			<td><?php echo $lang["created"]; ?></td>
			<td><?php echo $lang["from"]; ?></td>
			<td><?php echo $lang["message"]; ?></td>
			<td><?php echo $lang["expires"]; ?></td>
			<td><?php echo $lang["seen"]; ?></td>
			<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
		</tr>
<?php
for ($n=0;$n<count($messages);$n++)
	{
	$message=escape_check($messages[$n]["message"]);
	$message=htmlspecialchars($message,ENT_QUOTES);
	$url_encoded=urlencode($messages[$n]["url"]);
	$unread_css = ($messages[$n]["seen"]==0 ? " class='MessageUnread'" : "");
	?>
		<tr>
			<td<?php echo $unread_css; ?>><?php echo nicedate($messages[$n]["created"],true); ?></td>
			<td<?php echo $unread_css; ?>><?php echo $messages[$n]["owner"]; ?></td>
			<td<?php echo $unread_css; ?>><a href="#Header" onclick="message_modal('<?php echo $message; ?>','<?php
				echo $url_encoded; ?>',<?php echo $messages[$n]["ref"]; ?>,'<?php echo $messages[$n]["owner"] ?>');"><?php
					echo nl2br(strip_tags($messages[$n]["message"],'<br><p>'));
					?></a></td>
			<td<?php echo $unread_css; ?>><?php echo nicedate($messages[$n]["expires"]); ?></td>
			<td<?php echo $unread_css; ?>><?php echo ($messages[$n]["seen"]==0 ? $lang['no'] : $lang['yes']); ?></td>
			<td>
				<div class="ListTools">
				<?php if ($messages[$n]["url"]!="") { ?>
					<a href="<?php echo $messages[$n]["url"]; ?>"><?php echo LINK_CARET ?><?php echo $lang["link"]; ?></a>
				<?php } ?>
				    
				    <a href="<?php echo $baseurl_short?>pages/user/user_messages.php" onclick="jQuery.get('<?php
							echo $baseurl; ?>/pages/ajax/message.php?<?php echo (($messages[$n]["seen"]==0)?"seen":"unseen") . "=" . $messages[$n]['ref'] ; ?>',function() { message_poll(); });
							return CentralSpaceLoad(this,true);
							"><?php echo LINK_CARET ?><?php echo (($messages[$n]["seen"]==0)?$lang["mymessages_markread"]:$lang["mymessages_markunread"]);?>
					</a>
						  
				</div>
			</td>
		</tr>
<?php
	}
?></table>
</div>
</div> <!-- End of BasicsBox -->
<?php

include "../../include/footer.php";
