<?php

function HookSimpleldapTeam_user_editAdditionaluserfields(){
	global $user, $lang;
	if (isset($user["telephone"]))
		{
		?>		
		<div class="Question"><label><?php echo $lang["simpleldap_telephone"]?></label><div class="fixed"><?php echo $user["telephone"]?></div><div class="clearerleft"> </div></div>
		<?php
		}
	}