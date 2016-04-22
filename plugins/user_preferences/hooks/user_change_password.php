<?php

function HookUser_preferencesuser_change_passwordReplaceuserpreferencesheader()
	{
	echo '<h1>' . $GLOBALS['lang']['user-preferences'] . '</h1>';
	return true;
	}

function HookUser_preferencesuser_change_passwordSaveadditionaluserpreferences()
	{
	global $user_preferences_change_username, $user_preferences_change_email,
			$user_preferences_change_name, $userref, $useremail, $username, $userfullname, $lang;

	$newUsername=trim(safe_file_name(getvalescaped('username', $username)));
	$newEmail=getvalescaped('email', $userfullname);
	$newFullname=getvalescaped('fullname', $userfullname);

	# Check if a user with that username already exists
	if ($user_preferences_change_username && $username != $newUsername)
		{
		$existing = sql_query('select ref from user where username=\'' . escape_check($newUsername) . '\'');
		if (!empty($existing))
			{
			$GLOBALS['errorUsername'] = $lang['useralreadyexists'];
			return false;
			}
		}

	# Check if a user with that email already exists
	if ($user_preferences_change_email && $useremail != $newEmail)
		{
		$existing = sql_query('select ref from user where email=\'' . escape_check($newEmail) . '\'');
		if (!empty($existing))
			{
			$GLOBALS['errorEmail'] = $lang['useremailalreadyexists'];
			return false;
			}
		}

	# Store changed values in DB, and update the global variables as header.php is included next
	if ($user_preferences_change_username && $username != $newUsername)
		{
		sql_query("update user set username='" . escape_check($newUsername) . "' where ref='" . $userref . "'");
		$username = $newUsername;
		}
	if ($user_preferences_change_email && $useremail != $newEmail)
		{
		sql_query("update user set email='" . escape_check($newEmail) . "' where ref='" . $userref . "'");
		$useremail = $newEmail;
		}
	if ($user_preferences_change_name && $userfullname != $newFullname)
		{
		sql_query("update user set fullname='" . escape_check($newFullname) . "' where ref='" . $userref . "'");
		$userfullname = $newFullname;
		}

	return (getvalescaped('currentpassword', '')=='') || (getvalescaped('password', '')=='')
			&& (getvalescaped('password2', '')=='');
	}

function HookUser_preferencesuser_change_passwordAdditionaluserpreferences()
	{
	global $user_preferences_change_username, $user_preferences_change_email,
			$user_preferences_change_name, $lang, $errorUsername, $errorEmail, $errorFullname;

	if ($user_preferences_change_username)
		{
		global $username;
	?>
	<div class="Question">
	<label for="username"><?php echo $lang["username"]?></label>
	<input type="text" class="stdwidth" name="username" id="username" value="<?php echo $username ?>"/>
	<div class="clearerleft"> </div>
	<?php if (isset($errorUsername)) { ?><div class="FormError">!! <?php echo $errorUsername ?> !!</div><?php } ?>
	</div>
	<?php
		}

	if ($user_preferences_change_email)
		{
		global $useremail;
	?>
	<div class="Question">
	<label for="email"><?php echo $lang["email"]?></label>
	<input type="text" class="stdwidth" name="email" id="email" value="<?php echo $useremail ?>"/>
	<div class="clearerleft"> </div>
	<?php if (isset($errorEmail)) { ?><div class="FormError">!! <?php echo $errorEmail ?> !!</div><?php } ?>
	</div>
	<?php
		}

	if ($user_preferences_change_name)
		{
		global $userfullname;
	?>
	<div class="Question">
	<label for="fullname"><?php echo $lang["fullname"]?></label>
	<input type="text" class="stdwidth" name="fullname" id="fullname" value="<?php echo $userfullname ?>"/>
	<div class="clearerleft"> </div>
	<?php if (isset($errorFullname)) { ?><div class="FormError">!! <?php echo $errorFullname ?> !!</div><?php } ?>
	</div>
	<?php
		}
	}

?>
