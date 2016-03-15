<?php

function HookFeedbackAllToptoolbaradder()
	{
	global $target,$baseurl,$feedback_questions,$feedback_prompt_text,$pagename,$lang;
	
	?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/plugins/feedback/pages/feedback.php"><?php echo $lang["feedback_user_survey"]?></a></li>
	<?php }
	
function HookFeedbackAllHeadertop(){	
	global $target,$baseurl,$feedback_questions,$feedback_prompt_text,$pagename,$lang;
	if ($pagename=="setup" || $pagename=="feedback" || $pagename=="login" || $pagename=="user_request" || $pagename=="user_password") {return true;} # Do not appear on the setup page or during giving feedback, or on non-logged in pages
	
	# Form a check key based on the feedback form, so that form changes prompt a new message.
	# $check=md5(serialize($feedback_questions));
	
	if (getval("feedback_completed","")=="")
		{
		?>
		<div id="feedback_prompt" style="border:1px solid #BBB;border-bottom-width:3px;border-bottom-color:#bbb;background-color:white;width:300px;height:auto;position:absolute;top:100px;left:300px;text-align:left;padding:10px;color:black;z-index:99999;">
		<?php echo $feedback_prompt_text; ?>
		
		<div style="text-align:right;">
		<input type="button" value="<?php echo $lang["yes"]?>" onClick="SetCookie('feedback_completed','yes',30);jQuery('#feedback_prompt').remove();CentralSpaceLoad('<?php echo $baseurl?>/plugins/feedback/pages/feedback.php',true);">
		<input type="button" value="<?php echo $lang["no"]?>" onClick="SetCookie('feedback_completed','yes',30);document.getElementById('feedback_prompt').style.display='none';">
		<input type="button" value="<?php echo $lang["feedback_remind_me_later"]?>" onClick="SetCookie('feedback_completed','yes',0.5);document.getElementById('feedback_prompt').style.display='none';">
		</div>
		</div>
		<?php
		}
	}

