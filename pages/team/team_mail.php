<?php
/**
 * Create bulk mail page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("m")) {exit ("Permission denied.");}

$message_type = intval(getval("message_type",MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL));

if (getval("send","")!="")
	{
	$result=bulk_mail(getvalescaped("users",""),getvalescaped("subject",""),getvalescaped("text",""),getval("html","")=="yes",$message_type,getval("url",""));
	if ($result=="")
		{
		switch($message_type)
			{
			case MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN:
				$error=$lang["emailandmessagesent"];
				break;
			case MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL:
				$error=$lang["emailsent"];
				break;
			case MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN:
				$error=$lang["message_sent"];
				break;
			}
		log_activity($error,LOG_CODE_SYSTEM);
		}
	else
		{
		$error="!! " . $result . " !!";
		}
	}
$headerinsert.="
<script src=\"$baseurl/lib/js/jquery.validate.min.js\" type=\"text/javascript\"></script><script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery('#myform').validate({ 
		errorPlacement: function(error, element) {
		element.after('<span class=\"FormError\">'+error.html()+'</span>');
		},
   wrapper: 'div'});
});
</script>";

include "../../include/header.php";
switch($message_type){
	case MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN:
		$title=$lang["sendbulkmailandmessage"];
		break;
	case MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL:
		$title=$lang["sendbulkmail"];
		break;
	case MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN:
		$title=$lang["sendbulkmessage"];
		break;
}
?>
<div class="BasicsBox">
<h1><?php echo $title; ?></h1>
<form id="myform" method="post" action="<?php echo $baseurl_short?>pages/team/team_mail.php">

<?php if (isset($error)) { ?><div class="FormError"><?php echo $error?></div><?php } ?>

<div class="Question"><label><?php echo $lang["emailrecipients"]?></label><?php include "../../include/user_select.php"; ?>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["type"]?></label>

	<input type="radio" id="message_type_<?php echo MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL; ?>" name="message_type" value="<?php
		echo MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL; ?>" onclick="jQuery('h1').closest('h1').html('<?php echo $lang["sendbulkmail"]; ?>');
		jQuery('#message_email').slideDown(); jQuery('#message_screen').slideUp();" <?php
			if($message_type==MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL) { ?> checked='checked'<?php }?>><?php echo $lang['email']; ?>

	<input type="radio" id="message_type_<?php echo MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN; ?>" name="message_type" value="<?php
		echo MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN; ?>" onclick="jQuery('h1').closest('h1').html('<?php echo $lang["sendbulkmessage"]; ?>');
		jQuery('#message_email').slideUp(); jQuery('#message_screen').slideDown();"<?php
			if($message_type==MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN) { ?> checked='checked'<?php }?>><?php echo $lang['screen']; ?>
	
	<input type="radio" id="message_type_<?php echo MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN | MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL; ?>" name="message_type" value="<?php
		echo MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN | MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL; ?>" onclick="jQuery('h1').closest('h1').html('<?php echo $lang["sendbulkmailandmessage"]; ?>');
		jQuery('#message_email').slideDown(); jQuery('#message_screen').slideDown();"<?php
			if($message_type==(MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN | MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL)) { ?> checked='checked'<?php }?>><?php echo $lang['email_and_screen']; ?>

	<div class="clearerleft"></div>
</div>

<div id="message_screen" style="<?php if($message_type!=MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN && $message_type!=(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN)) {?>display:none;<?php } ?>">
	<div class="Question"><label><?php echo $lang["message_url"]?></label><input name="url" type="text" class="stdwidth Inline required" value="<?php echo getval("url",""); ?>"><div class="clearerleft"></div></div>
</div>

<div id="message_email" style="<?php if($message_type!==MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL && $message_type!=(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN)) {?>display:none;<?php } ?>">
	<div class="Question"><label><?php echo $lang["emailhtml"]?></label><input name="html" type="checkbox" value="yes" <?php if (getval("html","")=="yes") { ?>checked<?php } ?>><div class="clearerleft"> </div></div>
	<div class="Question"><label><?php echo $lang["emailsubject"]?></label><input name="subject" type="text" class="stdwidth Inline required" value="<?php echo getval("subject",$applicationname)?>"><div class="clearerleft"> </div></div>
</div>

<div class="Question"><label><?php echo $lang["text"]?></label><textarea name="text" class="stdwidth Inline required" rows=25 cols=50><?php echo htmlspecialchars(getval("text",""))?></textarea><div class="clearerleft"> </div></div>

<?php hook("additionalemailfield");?>

<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="send" type="submit" value="&nbsp;&nbsp;<?php echo $lang["send"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../../include/footer.php";
?>
