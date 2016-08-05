<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/header.php";

$introtext=text("introtext");
?>
<div class="BasicsBox"> 
  <h1><?php echo $lang["myaccount"]?></h1>
  
  <?php if (trim($introtext)!="") { ?>
  <p><?php echo $introtext ?></p>
  <?php } ?>
  
	<div class="VerticalNav">
	<ul>
	
	<?php if ($allow_password_change && !checkperm("p")) { ?>
        <li><i class="fa fa-fw fa-key"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/user/user_change_password.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["changeyourpassword"]?></a></li>
        <?php } ?>
	
	<?php
      	if ($disable_languages==false && $show_language_chooser)
			{?>
			<li><i class="fa fa-fw fa-language"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/change_language.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["languageselection"]?></a></li>
			<?php
			} ?>
		
		<?php if (!(!checkperm("d")&&!(checkperm('c') && checkperm('e0')))) { ?>
		<li><i class="fa fa-fw fa-user-plus"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/contribute.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycontributions"]?></a></li>
		<?php } ?>
		<?php if (!checkperm("b")){?>
		<li><i class="fa fa-fw fa-shopping-bag"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/collection_manage.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycollections"]?></a></li>
		<?php } ?>
		<script>message_poll();</script>
		<li><i class="fa fa-fw fa-envelope"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/user/user_messages.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mymessages"]; 
		?></a><span style="display: none;" class="MessageCountPill Pill"></span></li>
		
		<?php
		if($home_dash && checkPermission_dashmanage())
			{ ?>
			<li><i class="fa fa-fw fa-th"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/user/user_dash_admin.php"	onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["manage_own_dash"];?></a></li>
			<?php
			}
		if($user_preferences)
			{ ?>
			<li>
			    <i class="fa fa-fw fa-cog"></i>&nbsp;<a href="<?php echo $baseurl_short?>pages/user/user_preferences.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["userpreferences"];?></a>
			</li>
			<?php
			} ?>

		<?php
			hook('user_home_additional_links');
		?>

	</ul>
	</div>

</div>

<?php
include "../../include/footer.php";
