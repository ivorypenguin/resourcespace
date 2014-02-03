<?php
include "include/db.php";
include "include/general.php";
include "include/resource_functions.php";
include "include/collections_functions.php";
include "include/login_functions.php";

$url=getval("url","index.php");
$api=getval("api","");
$language=getval("language","");

# process log in
$error=getval("error","");
if ($error!="") $error=$lang[$error];

# Auto logged out? Set error message.
if (getval("auto","")!="") {$error=str_replace("30",$session_length,$lang["sessionexpired"]);}

# Display a no-cookies message
if (getval("nocookies","")!="" && getval("cookiecheck","")=="") {$error=$lang["nocookies"];}

if (!hook("replaceauth")) {
# First check that this IP address has not been locked out due to excessive attempts.
$ip=get_ip();
$lockouts=sql_value("select count(*) value from ip_lockout where ip='" . escape_check($ip) . "' and tries>='" . $max_login_attempts_per_ip . "' and date_add(last_try,interval " . $max_login_attempts_wait_minutes . " minute)>now()",0);

# Also check that the username provided has not been locked out due to excessive login attempts.
$ulockouts=sql_value("select count(*) value from user where username='" . getvalescaped("username","") . "' and login_tries>='" . $max_login_attempts_per_username . "' and date_add(login_last_try,interval " . $max_login_attempts_wait_minutes . " minute)>now()",0);

if ($lockouts>0 || $ulockouts>0)
	{
	$error=str_replace("?",$max_login_attempts_wait_minutes,$lang["max_login_attempts_exceeded"]);
	}

# Process the submitted login
elseif (array_key_exists("username",$_POST) && getval("langupdate","")=="")
    {
    $username=trim(getvalescaped("username",""));
    $password=trim(getvalescaped("password",""));

	$result=perform_login();
	if ($result['valid'])
		{
	 	$expires=0;
       	if (getval("remember","")!="") {$expires = 100;} # remember login for 100 days

		# Store language cookie
        rs_setcookie("language", $language, 1000); # Only used if not global cookies
        rs_setcookie("language", $language, 1000, $baseurl_short . "pages/");

		# Set the session cookie.
        rs_setcookie("user", "", 0);

		# Set user cookie, setting secure only flag if a HTTPS site, and also setting the HTTPOnly flag so this cookie cannot be probed by scripts (mitigating potential XSS vuln.)
        rs_setcookie("user", $result['session_hash'], $expires, "", "", substr($baseurl,0,5)=="https", true);

        # Set default resource types
        setcookie("restypes",$default_res_types);

		# If the redirect URL is the collection frame, do not redirect to this as this will cause
		# the collection frame to appear full screen.
		if (strpos($url,"pages/collections.php")!==false) {$url="index.php";}

        $accepted=sql_value("select accepted_terms value from user where username='$username' and (password='$password' or password='".$result['password_hash']."')",0);
		if (($accepted==0) && ($terms_login) && !checkperm("p")) {redirect ("pages/terms.php?noredir=true&url=" . urlencode("pages/user_preferences.php"));} else {redirect($url);}
        }
    else
        {
        sleep($password_brute_force_delay);
        
		$error=$result['error'];
        hook("dispcreateacct");
        }
    }
}

if ((getval("logout","")!="") && array_key_exists("user",$_COOKIE))
    {
    #fetch username and update logged in status
    $session=escape_check($_COOKIE["user"]);
    sql_query("update user set logged_in=0,session='' where session='$session'");
        
    #blank cookie
    rs_setcookie("user", "", time() - 3600);

    # Also blank search related cookies
    setcookie("search","");	
    setcookie("saved_offset","");	
    setcookie("saved_archive","");	
    
    unset($username);
    
    if (isset($anonymous_login))
    	{
    	# If the system is set up with anonymous access, redirect to the home page after logging out.
    	redirect("pages/".$default_home_page);
    	}
    }

if (getval("langupdate","")!="")
	{
	# Update language while remaining on this page.
    rs_setcookie("language", $language, 1000); # Only used if not global cookies
    rs_setcookie("language", $language, 1000, $baseurl_short . "pages/");    
	redirect("login.php?username=" . urlencode(getval("username","")));
	}




include "include/header.php";
if (!hook("replaceloginform")) {
?>

  <h1><?php echo text("welcomelogin")?></h1>
  <p><?php echo text(getvalescaped("text","defaultintro"))?></p>
  <p>
  <?php if ($allow_account_request) { ?><a href="pages/user_request.php">&gt; <?php echo $lang["nopassword"]?> </a><?php } ?>
  <?php if ($allow_password_reset) { ?><br/><a href="pages/user_password.php">&gt; <?php echo $lang["forgottenpassword"]?></a><?php } ?>
  <?php hook("loginformlink") ?> 
  </p>
  
  
  <?php if ($error!="") { ?><div class="FormIncorrect"><?php echo $error?></div><?php } ?>
  <form id="loginform" method="post" <?php if (!$login_autocomplete) { ?>AUTOCOMPLETE="OFF"<?php } ?>>
  <input type="hidden" name="langupdate" id="langupdate" value="">  
  <input type="hidden" name="url" value="<?php echo htmlspecialchars($url)?>">
		<div class="Question">
			<label for="username"><?php echo $lang["username"]?> </label>
			<input type="text" name="username" id="username" class="stdwidth" <?php if (!$login_autocomplete) { ?>AUTOCOMPLETE="OFF"<?php } ?> value="<?php echo htmlspecialchars(getval("username","")) ?>" />
			<div class="clearerleft"> </div>
		</div>
		
		<div class="Question">
			<label for="pass"><?php echo $lang["password"]?> </label>
			<input type="password" name="password" id="password" class="stdwidth" <?php if (!$login_autocomplete) { ?>AUTOCOMPLETE="OFF"<?php } ?> />
			 <div id="capswarning"><?php echo $lang["caps-lock-on"]; ?></div>
			<div class="clearerleft"> </div>
		</div>
<?php if ($disable_languages==false) { ?>	
		<div class="Question">
			<label for="pass"><?php echo $lang["language"]?> </label>
			<select class="stdwidth" name="language" onChange="document.getElementById('langupdate').value='YES';document.getElementById('loginform').submit();">
			<?php reset ($languages); foreach ($languages as $key=>$value) { ?>
			<option value="<?php echo $key?>" <?php if ($language==$key) { ?>selected<?php } ?>><?php echo $value?></option>
			<?php } ?>
			</select>
			<div class="clearerleft"> </div>
		</div> 
<?php } ?>
	
		<?php if ($allow_keep_logged_in) { ?>
		<div class="Question">
			<label for="remember"><?php echo $lang["keepmeloggedin"]?></label>
			<input style="margin-top: 0.5em;" name="remember" id="remember" type="checkbox" value="yes" checked="checked">
			<div class="clearerleft"> </div>
		</div>
		<?php } ?>
		
		<div class="QuestionSubmit">
			<label for="buttons"> </label>			
			<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["login"]?>&nbsp;&nbsp;" />
		</div>
	</form>
  <p>&nbsp;</p>

<?php
# Javascript to default the focus to the username box
?>
<script type="text/javascript">
document.getElementById('username').focus();
</script>
<?php

}
include "include/footer.php";
?>
