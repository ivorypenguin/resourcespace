<?php

/**
 * Performs the login using the global $username, and $password. Since the "externalauth" hook
 * is allowed to change the credentials later on, the $password_hash needs to be global as well.
 *
 * @return array Containing the login details ('valid' determines whether or not the login succeeded).
 */

if (!function_exists('hash'))
    {
    include dirname(__FILE__) . "/hash_functions.php";
    }
    
function perform_login()
	{
	global $api, $scramble_key, $enable_remote_apis, $lang, $max_login_attempts_wait_minutes, $max_login_attempts_per_ip, $max_login_attempts_per_username, $global_cookies, $username, $password, $password_hash;
	
        
    if (!$api && (strlen($password)==32 || strlen($password)==64) && getval("userkey","")!=md5($username . $scramble_key))
		{
		exit("Invalid password."); # Prevent MD5s being entered directly while still supporting direct entry of plain text passwords (for systems that were set up prior to MD5 password encryption was added). If a special key is sent, which is the md5 hash of the username and the secret scramble key, then allow a login using the MD5 password hash as the password. This is for the 'log in as this user' feature.
		}
		
	if (strlen($password)!=64)
		{
		# Provided password is not a hash, so generate a hash.
		//$password_hash=md5("RS" . $username . $password);
		$password_hash=hash('sha256', md5("RS" . $username . $password));						
		}
	else
		{
		$password_hash=$password;
		}

	// ------- Automatic migration of md5 hashed or plain text passwords to SHA256 hashed passwords ------------
	// This is necessary because older systems being upgraded may still have passwords stored using md5 hashes or even possibly stored in plain text.
	// Updated March 2015 - select password_reset_hash to force dbstruct that will update password column varchar(100) if not already
	$accountstoupdate=sql_query("select username, password, password_reset_hash from user where length(password)<>64");
	foreach($accountstoupdate as $account)
		{
		$oldpassword=$account["password"];
		if(strlen($oldpassword)!=32){$oldpassword=md5("RS" . $account["username"] . $oldpassword);} // Needed if we have a really old password, or if password has been manually reset in db for some reason
		$new_password_hash=hash('sha256', $oldpassword);
        sql_query("update user set password='" . $new_password_hash . "' where username='".escape_check($account["username"]) . "'");
		}
	$ip=get_ip();

	# This may change the $username, $password, and $password_hash
        $externalresult=hook("externalauth","",array($username, $password)); #Attempt external auth if configured

	# Generate a new session hash.
	$session_hash=generate_session_hash($password_hash);

    # Check the provided credentials
	$valid=sql_query("select ref,usergroup,account_expires from user where username='".escape_check($username)."' and password='".escape_check($password_hash)."'");

	# Prepare result array
	$result=array();
	$result['valid']=false;

	if (count($valid)>=1)
		{
		# Account expiry
		$userref=$valid[0]["ref"];
		$usergroup=$valid[0]["usergroup"];
		$expires=$valid[0]["account_expires"];

		if ($expires!="" && $expires!="0000-00-00 00:00:00" && strtotime($expires)<=time())
			{
			$result['error']=$lang["accountexpired"];
			return $result;
			}

		$result['valid']=true;
		$result['session_hash']=$session_hash;
		$result['password_hash']=$password_hash;
		$result['ref']=$userref;

		# Update the user record.
		# Omit updating session has if using an API, because we don't want API usage to log users out, and there is no 'session' to remember in such a case.
		if ($api){$session_hash_sql="";} else {$session_hash_sql="session='".escape_check($session_hash)."',";}
		sql_query("update user set $session_hash_sql last_active=now(),login_tries=0,lang='".getvalescaped("language","")."' where ref='$userref'");

		# Log this
		daily_stat("User session",$userref);
		if (!$api)
			{
			/* sql_query("insert into resource_log(date,user,resource,type) values (now()," . (($userref!="")?"'$userref'":"null") . ",0,'l')"); */
			log_activity(null,LOG_CODE_LOGGED_IN,$ip,"user","ref",($userref!="" ? $userref :"null"),null,'',($userref!="" ? $userref :"null"));
			}

		# Blank the IP address lockout counter for this IP
		sql_query("delete from ip_lockout where ip='" . escape_check($ip) . "'");

		return $result;
		}

	# Invalid login
	if(isset($externalresult["error"])){$result['error']=$externalresult["error"];} // We may have been given a better error to display
        else {$result['error']=$lang["loginincorrect"];}

  hook("loginincorrect");

	# Add / increment a lockout value for this IP
	$lockouts=sql_value("select count(*) value from ip_lockout where ip='" . escape_check($ip) . "' and tries<'" . $max_login_attempts_per_ip . "'","");

	if ($lockouts>0)
		{
		# Existing row with room to move
		$tries=sql_value("select tries value from ip_lockout where ip='" . escape_check($ip) . "'",0);
		$tries++;
		if ($tries==$max_login_attempts_per_ip)
			{
			# Show locked out message.
			$result['error']=str_replace("?",$max_login_attempts_wait_minutes,$lang["max_login_attempts_exceeded"]);
			}
		# Increment
		sql_query("update ip_lockout set last_try=now(),tries=tries+1 where ip='" . escape_check($ip) . "'");
		}
	else
		{
		# New row
		sql_query("delete from ip_lockout where ip='" . escape_check($ip) . "'");
		sql_query("insert into ip_lockout (ip,tries,last_try) values ('" . escape_check($ip) . "',1,now())");
		}

	# Increment a lockout value for any matching username.
	$ulocks=sql_query("select ref,login_tries,login_last_try from user where username='".escape_check($username)."'");
	if (count($ulocks)>0)
		{
		$tries=$ulocks[0]["login_tries"];
		if ($tries=="") {$tries=1;} else {$tries++;}
		if ($tries>$max_login_attempts_per_username) {$tries=1;}
		if ($tries==$max_login_attempts_per_username)
			{
			# Show locked out message.
			$result['error']=str_replace("?",$max_login_attempts_wait_minutes,$lang["max_login_attempts_exceeded"]);
			}
		sql_query("update user set login_tries='$tries',login_last_try=now() where username='$username'");
		}

	return $result;
	}

	
function generate_session_hash($password_hash)
	{
	# Generates a unique session hash
	global $randomised_session_hash,$scramble_key;
	
	if ($randomised_session_hash)
		{
		# Completely randomised session hashes. May be more secure, but allows only one user at a time.
		while (true)
			{
			$session=md5(rand() . microtime());
			if (sql_value("select count(*) value from user where session='" . escape_check($session) . "'",0)==0) {return $session;} # Return a unique hash only.
			}	
		}
	else
		{
		# Session hash is based on the password hash and the date, so there is one new session hash each day. Allows two users to use the same login.
		$suffix="";
		while (true)
			{
			$session=md5($scramble_key . $password_hash . date("Ymd") . $suffix);
			if (sql_value("select count(*) value from user where session='" . escape_check($session) . "' and password<>'" . escape_check($password_hash) . "'",0)==0) {return $session;} # Return a unique hash only.
			$suffix.="."; # Extremely unlikely case that this was not a unique session (hash collision) - alter the string slightly and try again.
			}	
		}	
		
	}


