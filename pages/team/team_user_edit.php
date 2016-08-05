<?php
/**
 * User edit form display page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; 


$backurl=getval("backurl","");
$url=$baseurl_short."pages/team/team_user_edit.php?ref=" .getvalescaped("ref","",true) . "&backurl=" . urlencode($backurl);
if (!checkperm("u")) {redirect($baseurl_short ."login.php?error=error-permissions-login&url=".urlencode($url));}

$ref=getvalescaped("ref","",true);


if (getval("unlock","")!="")
	{
	# reset user lock
	sql_query("update user set login_tries='0' where ref='$ref'");
	}
elseif(getval("suggest","")!="")
	{
	echo make_password();
	exit();
	}
elseif (getval("save","")!="")
	{
	# Save user data
	$result=save_user($ref);
	if ($result===false)
		{
		$error=$lang["useralreadyexists"];
		}
	elseif ($result!==true)
		{
		$error=$result;
		}
	else
		{
		hook('aftersaveuser');
		if (getval("save","")!="") {$backurl=getval("backurl",$baseurl_short ."pages/team/team_user.php?nc=" . time());redirect ($backurl);}
		}
	}

# Fetch user data
$user=get_user($ref);
if (($user["usergroup"]==3) && ($usergroup!=3)) {redirect($baseurl_short ."login.php?error=error-permissions-login&url=".urlencode($url));}

if (!checkperm_user_edit($user))
	{
	redirect($baseurl_short ."login.php?error=error-permissions-login&url=".urlencode($url));
	exit;
}

include "../../include/header.php";

# Log in as this user?
if (getval("loginas","")!="")
	{
	# Log in as this user
	# A user key must be generated to enable login using the MD5 hash as the password.
	?>
	<form method="post" action="<?php echo $baseurl_short?>login.php" id="autologin">
	<input type="hidden" name="username" value="<?php echo $user["username"]?>">
	<input type="hidden" name="password" value="<?php echo $user["password"]?>">
	<input type="hidden" name="userkey" value="<?php echo md5($user["username"] . $scramble_key)?>">
	<noscript><input type="submit" value="<?php echo $lang["login"]?>"></noscript>
	</form>
	<script type="text/javascript">
	document.getElementById("autologin").submit();
	</script>
	<?php
	exit();
	}

?>
<div class="BasicsBox">
<p><a href="<?php echo $backurl?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK ?><?php echo $lang["manageusers"]?></a></p>
<h1><?php echo $lang["edituser"]?> <?php global $display_useredit_ref; echo $display_useredit_ref ? $ref : ""; ?></h1>
<?php if (isset($error)) { ?><div class="FormError">!! <?php echo $error?> !!</div><?php } ?>

<form method=post action="<?php echo $baseurl_short?>pages/team/team_user_edit.php">
<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">
<input type=hidden name=backurl value="<?php echo getval("backurl", $baseurl_short . "pages/team/team_user.php?nc=" . time())?>">
<input name="save" type="submit" style="display:none;" value="save" /><!-- to capture default action -->

<?php
if (($user["login_tries"]>=$max_login_attempts_per_username) && (strtotime($user["login_last_try"]) > (time() - ($max_login_attempts_wait_minutes * 60))))
 {?>
	<div class="Question"><label><strong><?php echo $lang["accountlockedstatus"]?></strong></label>
		<input class="medcomplementwidth" type=submit name="unlock" value="<?php echo $lang["accountunlock"]?>" />
	</div>

	<div class="clearerleft"> </div>
<?php } ?>

<div class="Question"><label><?php echo $lang["username"]?></label><input name="username" type="text" class="stdwidth" value="<?php echo form_value_display($user,"username") ?>"><div class="clearerleft"> </div></div>

<?php if (!hook("password")) { ?>
<div class="Question"><label><?php echo $lang["password"]?></label><input name="password" id="password" type="text" class="medwidth" value="<?php echo (strlen($user["password"])==64 || strlen($user["password"])==32)?$lang["hidden"]:$user["password"]?>">&nbsp;<input class="medcomplementwidth" type=submit name="suggest" value="<?php echo $lang["suggest"]?>" onclick="jQuery.get(this.form.action + '?suggest=true', function(result) {jQuery('#password').val(result);});return false;" /><div class="clearerleft"> </div></div>
<?php } ?>

<?php if (!hook("replacefullname")){?>
<div class="Question"><label><?php echo $lang["fullname"]?></label><input name="fullname" type="text" class="stdwidth" value="<?php echo form_value_display($user,"fullname") ?>"><div class="clearerleft"> </div></div>
<?php } ?>

<div class="Question"><label><?php echo $lang["group"]?></label>
<?php if (!hook("replaceusergroups")) { ?>
<select class="stdwidth" name="usergroup">
<?php
	$groups=get_usergroups(true);
	for ($n=0;$n<count($groups);$n++)
		{
		if (($groups[$n]["ref"]==3) && ($usergroup!=3))
			{
			#Do not show
			}
		else
			{
			?>
			<option value="<?php echo $groups[$n]["ref"]?>" <?php if (getval("usergroup",$user["usergroup"])==$groups[$n]["ref"]) {?>selected<?php } ?>><?php echo $groups[$n]["name"]?></option>	
			<?php
			}
		}
?>
</select>
<?php } ?>
<div class="clearerleft"> </div></div>
<?php hook("additionalusergroupfields"); ?>

<div class="Question"><label><?php echo $lang["emailaddress"]?></label><input name="email" type="text" class="stdwidth" value="<?php echo form_value_display($user,"email") ?>"><div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["accountexpiresoptional"]?><br/><?php echo $lang["format"] . ": " . $lang["yyyy-mm-dd"]?></label><input name="account_expires" type="text" class="stdwidth" value="<?php echo form_value_display($user,"account_expires")?>"><div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["ipaddressrestriction"]?><br/><?php echo $lang["wildcardpermittedeg"]?> 194.128.*</label><input name="ip_restrict" type="text" class="stdwidth" value="<?php echo form_value_display($user,"ip_restrict") ?>"><div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["searchfilteroverride"]?></label><input name="search_filter_override" type="text" class="stdwidth" value="<?php echo form_value_display($user,"search_filter_override")?>"><div class="clearerleft"> </div></div>

<?php hook("additionaluserfields");?>
<?php if (!hook("replacecomments")) { ?>
<div class="Question"><label><?php echo $lang["comments"]?></label><textarea name="comments" class="stdwidth" rows=5 cols=50><?php echo form_value_display($user,"comments")?></textarea><div class="clearerleft"> </div></div>
<?php } ?>
<div class="Question"><label><?php echo $lang["created"]?></label>
<div class="Fixed"><?php echo nicedate($user["created"],true) ?></div>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["lastactive"]?></label>
<div class="Fixed"><?php echo nicedate($user["last_active"],true) ?></div>
<div class="clearerleft"> </div></div>


<div class="Question"><label><?php echo $lang["lastbrowser"]?></label>
<div class="Fixed"><?php echo resolve_user_agent($user["last_browser"],true)?></div>
<div class="clearerleft"> </div></div>


<div class="Question">
<label><?php echo $lang["team_user_contributions"]?></label>
<div class="Fixed"><a href="<?php echo $baseurl_short?>pages/search.php?search=!contributions<?php echo $ref?>"><?php echo LINK_CARET ?><?php echo $lang["team_user_view_contributions"] ?></a></div>
<div class="clearerleft"> </div></div>



<?php 
# Only allow sending of password when this is not an MD5 string (i.e. only when first created or 'Suggest' is used).

if (!hook("ticktoemailpassword")) 
	{
	if($allow_password_email) // Let's hope this is not enabled
		{
		?>
		<div class="Question"><label><?php echo $lang["ticktoemail"]?></label>
		<?php if (strlen($user["password"])!=64) { ?>
		<input name="emailme" type="checkbox" value="yes" <?php if ($user["approved"]==0) { ?>checked<?php } ?>>
		<?php } else { ?>
		<div class="Fixed"><?php echo $lang["cannotemailpassword"]?></div>
		<?php } ?><?php hook('emailpassword'); ?>
		<div class="clearerleft"> </div></div>
		<?php 
		} 
	else
		{
		?>
		<div class="Question"><label><?php echo $lang["ticktoemaillink"]?></label>
		<input name="emailresetlink" type="checkbox" value="yes" <?php if ($user["approved"]==0) { ?>checked<?php } ?>>
		<div class="clearerleft"> </div></div>
		<?php
		}
	}?> 
	
<div class="Question"><label><?php echo $lang["approved"]?></label><input name="approved" type="checkbox"  value="yes" <?php if ($user["approved"]==1) { ?>checked<?php } ?>>
<?php if ($user["approved"]==0) { ?><div class="FormError">!! <?php echo $lang["ticktoapproveuser"]?> !!</div><?php } ?>

<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["ticktodelete"]?></label><input name="deleteme" type="checkbox"  value="yes"><div class="clearerleft"> </div></div>
<?php hook("additionaluserlinks");?>
<?php if ($user["approved"]==1 && !hook("loginasuser")) { ?>

<div class="Question"><label><?php echo $lang["log"]?></label>
<div class="Fixed"><a href="<?php echo $baseurl_short ?>pages/admin/admin_system_log.php?actasuser=<?php echo $ref ?>&backurl=<?php echo urlencode($url) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["clicktoviewlog"]?></a></div>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["login"]?></label>
<div class="Fixed"><a href="<?php echo $baseurl_short?>pages/team/team_user_edit.php?ref=<?php echo $ref?>&loginas=true"><?php echo LINK_CARET ?><?php echo $lang["clicktologinasthisuser"]?></a></div>
<div class="clearerleft"> </div></div>
<?php } ?>

<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../../include/footer.php";
?>
