<?php
require_once "../include/db.php";
require_once "../include/authenticate.php";if (!checkperm("d")&&!(checkperm('c') && checkperm('e0'))) {exit ("Permission denied.");}
require_once "../include/general.php";

include "../include/header.php";
?>


<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["mycontributions"]?></h1>
  <p><?php echo text("introtext")?></p>

	<div class="VerticalNav">
	<ul>
	
	<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=-<?php echo urlencode($userref) ?>&uploader=plupload"><?php echo $lang["addresourcebatchbrowser"];?></a></li>
	
	<?php if (checkperm("e-2")) { ?>
	<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=!contributions<?php echo urlencode($userref) ?>&archive=-2"><?php echo $lang["viewcontributedps"]?></a></li>
	<?php } ?>
	
	<?php if (checkperm("e-1")) { ?>
	<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=!contributions<?php echo urlencode($userref) ?>&archive=-1"><?php echo $lang["viewcontributedpr"]?></a></li>
	<?php } ?>
	
	<?php if ($show_user_contributed_resources) { ?>
	<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=!contributions<?php echo urlencode($userref) ?>&archive=0"><?php echo $lang["viewcontributedsubittedl"]?></a></li>
	<?php } ?>
	
	</ul>
	</div>
	
  </div>

<?php
include "../include/footer.php";
?>
