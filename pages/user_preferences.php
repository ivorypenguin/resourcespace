<?php
include "../include/db.php";
include "../include/authenticate.php"; if (checkperm("p")) {exit("Not allowed.");}
include "../include/general.php";

hook("preuserpreferencesform");

if (getval("save","")!="")
	{
	if (md5("RS" . $username . getvalescaped("currentpassword",""))!=$userpassword)
		{
		$error3=$lang["wrongpassword"];
		}
	else {
        if (getval("password","")!=getval("password2","")) {$error2=true;}
    	else
	    	{
		    $message=change_password(getvalescaped("password",""));
    		if ($message===true)
	    		{
		    	redirect($baseurl_short."pages/" . ($use_theme_as_home?$baseurl_short.'pages/themes.php':$default_home_page));
			    }
    		else
	    		{
		    	$error=true;
			    }
		    }
		}
	}
include "../include/header.php";
?>
<div class="BasicsBox"> 
	<?php if ($userpassword=="b58d18f375f68d13587ce8a520a87919"){?><div class="FormError" style="margin:0;"><?php echo $lang['secureyouradminaccount'];?></div><p></p><?php } ?>
	<h1><?php echo $lang["changeyourpassword"]?></h1>

    <p><?php echo text("introtext")?></p>

	<?php if (getval("expired","")!="") { ?><div class="FormError">!! <?php echo $lang["password_expired"]?> !!</div><?php } ?>

	<form method="post" action="<?php echo $baseurl_short?>pages/user_preferences.php">
	<input type="hidden" name="expired" value="<?php echo htmlspecialchars(getvalescaped("expired",""))?>">
	<div class="Question">
	<label for="password"><?php echo $lang["currentpassword"]?></label>
	<input type=password class="stdwidth" name="currentpassword" id="currentpassword" value="<?php if ($userpassword=="b58d18f375f68d13587ce8a520a87919"){?>admin<?php } ?>"/>
	<div class="clearerleft"> </div>
	<?php if (isset($error3)) { ?><div class="FormError">!! <?php echo $error3?> !!</div><?php } ?>
	</div>
	<div class="Question">
	<label for="password"><?php echo $lang["newpassword"]?></label>
	<input type="password" name="password" id="password" class="stdwidth">
	<?php if (isset($error)) { ?><div class="FormError">!! <?php echo $message?> !!</div><?php } ?>
	<div class="clearerleft"> </div>
	</div>

	<div class="Question">
	<label for="password2"><?php echo $lang["newpasswordretype"]?></label>
	<input type="password" name="password2" id="password2" class="stdwidth">
	<?php if (isset($error2)) { ?><div class="FormError">!! <?php echo $lang["passwordnotmatch"]?> !!</div><?php } ?>
	<div class="clearerleft"> </div>
	</div>



	<div class="QuestionSubmit">
	<label for="buttons"> </label>
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" /><div class="clearerleft"> </div>
	</div>
	</form>

<?php hook("afteruserpreferencesform");?>
</div>
<?php
include "../include/footer.php";
?>
