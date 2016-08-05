<?php
include "../include/db.php";
include_once "../include/general.php";
if (!$allow_password_reset) {exit("Password requests have been disabled.");} # User should never see this.

if (getval("save","")!="")
	{
	if (email_reset_link(getvalescaped("email","")) || $hide_failed_reset_text)
		{
		redirect("pages/done.php?text=user_password_link_sent");
		}
	else
		{
		$error=true;
		}
	}
include "../include/header.php";

if($login_background)
	{
    $backimageurl = "";
    $dir = dirname(__FILE__) . "/../" . $homeanim_folder;
    $d = scandir($dir);    
	sort($d, SORT_NUMERIC);
    foreach ($d as $f) 
		{ 
		if(preg_match("/[0-9]+\.(jpg)$/",$f))
            {
            $backimageurl= $baseurl_short . $homeanim_folder . "/" . $f;  
            break;    
            }
        }
	?>
	<style>
	#UICenter {
		background-image: url('<?php echo $backimageurl; ?>');
		}
	</style>
	<div id="login_box">
	<?php
	}
    
?>

    <h1><?php echo $lang["requestnewpassword"]?></h1>
    <p><?php echo text("introtextreset")?></p>
	
	  
	<form method="post" action="<?php echo $baseurl_short?>pages/user_password.php">  
	<div class="Question">
	<label for="email"><?php echo $lang["youremailaddress"]?></label>
	<input type=text name="email" id="email" class="stdwidth" value="<?php echo htmlspecialchars(getval("email",""))?>">
	<?php if (isset($error) && !$hide_failed_reset_text) { ?><div class="FormError">!! <?php echo $lang["emailnotfound"]?> !!</div><?php hook("userpasswdextramsg"); ?><?php } ?>
	<div class="clearerleft"> </div>
	</div>
	
	<div class="QuestionSubmit">
	<label for="buttons"> </label>			
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["sendnewpassword"]?>&nbsp;&nbsp;" />
	</div>
	</form>
	

<?php

if($login_background)
	{
	?>
	<div> <!-- end of login_box -->
	<?php
	}
include "../include/footer.php";
?>
