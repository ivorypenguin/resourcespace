<?php

function HookCheckmailAllAddteamresourcetool(){
	global $checkmail_users;
	global $userref;
	global $target;
	global $bullet;
	global $lang;
	global $baseurl;
	if (in_array($userref,$checkmail_users)){?>
	<li><a onClick="return CentralSpaceLoad(this,true);" target="<?php echo $target?>" href="<?php echo $baseurl?>/plugins/checkmail/pages/upload.php"><?php echo $lang['addresourcesviaemail'];?></a></li>
	<?php
	}
}
