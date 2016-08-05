<?php
# authenticate user based on cookie

$valid=true;
$autologgedout=false;
$nocookies=false;

if (!function_exists("ip_matches")){
function ip_matches($ip, $ip_restrict)
	{
	global $system_login;
	if ($system_login){return true;}	

	if (substr($ip_restrict, 0, 1)=='!')
		return @preg_match('/'.substr($ip_restrict, 1).'/su', $ip);

	# Allow multiple IP addresses to be entered, comma separated.
	$i=explode(",",$ip_restrict);

	# Loop through all provided ranges
	for ($n=0;$n<count($i);$n++)
		{
		$ip_restrict=trim($i[$n]);

		# Match against the IP restriction.
		$wildcard=strpos($ip_restrict,"*");

		if ($wildcard!==false)
			{
			# Wildcard
			if (substr($ip,0,$wildcard)==substr($ip_restrict,0,$wildcard))
				return true;
			}
		else
			{
			# No wildcard, straight match
			if ($ip==$ip_restrict)
				return true;
			}
		}
	return false;
	}
}

if (!isset($api)){$api=false;} // $api is set above inclusion of authenticate.php in remotely accessible scripts.

if ($api && $enable_remote_apis ){
	include_once "login_functions.php";
	if (getval("key","")==""){
		$ip=get_ip();
		if (isset($_SERVER['HTTP_REFERER'])){$referer=$_SERVER['HTTP_REFERER'];}else { $referer="";}
		$current_whitelists=sql_query("select u.username,u.fullname,w.* from api_whitelist w join user u on w.userref=u.ref order by u.username");
		$allowed_by_domain=false;
		foreach ($current_whitelists as $whitelist){
			if ($referer!="" && strpos($referer,$whitelist['ip_domain'])!==false){
				$allowed_by_domain=true;
			}
			if ($allowed_by_domain || ip_matches($ip,$whitelist['ip_domain'])){
				// IP matches. Log in as specified user
				$api_whitelisted_user=sql_query("select * from user where ref='".$whitelist['userref']."'");
				$_POST['key']=$_GET['key']=make_api_key($api_whitelisted_user[0]['username'],$api_whitelisted_user[0]['password']);
				$allowed_apis=explode(",",$whitelist['apis']);
				$api_plugin=explode("/",$_SERVER['REQUEST_URI']);
				$api_plugin=$api_plugin[count($api_plugin)-2];
				//echo $api_plugin;
				if (in_array("all",$allowed_apis)){break;}
				else if (in_array($api_plugin,$allowed_apis)){break;}
				else{
					header("HTTP/1.0 403 Access Denied");exit("Access denied for $api_plugin.");
				}
			}
		}
	}
	# if using API (RSS or API), send credentials to login.php, as if normally posting, to establish login
	if (getval("key","")==""){header("HTTP/1.0 403 Access Denied");exit("Access denied - no key.");}
	if (getval("key","") || (getval("username","")&& getval("password",""))){ // key is provided within the website when logged in (encrypted username and password)

        if (getval("username","")&& getval("password","")){
            $u_p_array[0]=getval("username","");$u_p_array[1]=getval("password","");
        }
        else {
            $u_p_array=decrypt_api_key(getval("key",""));
        }

        if (count($u_p_array)!=2)
			{
			unset($_COOKIE['user']);
			} 
		else
			{
			$username=$u_p_array[0];
			$password=$u_p_array[1];

			$result=perform_login();
			if ($result['valid'])
				{
				$_COOKIE['user']=$result['session_hash'];
				}
	        else
				{
				unset($_COOKIE['user']);
				}
			}
	}
}


if (array_key_exists("user",$_COOKIE) || array_key_exists("user",$_GET) || isset($anonymous_login) || hook('provideusercredentials'))
    {
	if (!$api){
    $username="";
	if (array_key_exists("user",$_GET))
		{
	    $session_hash=escape_check($_GET["user"]);
		}
	elseif (array_key_exists("user",$_COOKIE))
  		{
	  	$session_hash=escape_check($_COOKIE["user"]);
	  	}
	elseif (isset($anonymous_login))
		{
		$username=$anonymous_login;
		$session_hash="";
		}
	}
	if (!$api){ $user_select_sql="and u.session='$session_hash'"; } else { $user_select_sql="and u.username='$username'"; }
	if (isset($anonymous_login) && ($username==$anonymous_login)) {$user_select_sql="and u.username='$username'";} # Automatic anonymous login, do not require session hash.
	hook('provideusercredentials');

    $userdata=sql_query("select u.ref, u.username, g.permissions, g.fixed_theme, g.parent, u.usergroup, u.current_collection, u.last_active, timestampdiff(second,u.last_active,now()) idle_seconds,u.email, u.password, u.fullname, g.search_filter, g.edit_filter, g.ip_restrict ip_restrict_group, g.name groupname, u.ip_restrict ip_restrict_user, resource_defaults, u.password_last_change,g.config_options,g.request_mode from user u,usergroup g where u.usergroup=g.ref $user_select_sql and u.approved=1 and (u.account_expires is null or u.account_expires='0000-00-00 00:00:00' or u.account_expires>now())");//print_r($userdata);
    if (count($userdata)>0)
        {
        $valid=true;
        $userref=$userdata[0]["ref"];
        $username=$userdata[0]["username"];
		
		# Hook to modify user permissions
		if (hook("userpermissions")){$userdata[0]["permissions"]=hook("userpermissions");} 
		
		# Create userpermissions array for checkperm() function
		$userpermissions=array_merge(explode(",",trim($global_permissions)),explode(",",trim($userdata[0]["permissions"]))); 
	
		$usergroup=$userdata[0]["usergroup"];
		$usergroupname=$userdata[0]["groupname"];
        $usergroupparent=$userdata[0]["parent"];
        $useremail=$userdata[0]["email"];
        $userpassword=$userdata[0]["password"];
        $userfullname=$userdata[0]["fullname"];
		if (!isset($userfixedtheme)) {$userfixedtheme=$userdata[0]["fixed_theme"];} # only set if not set in config.php

        $ip_restrict_group=trim($userdata[0]["ip_restrict_group"]);
        $ip_restrict_user=trim($userdata[0]["ip_restrict_user"]);
        
        $usercollection=$userdata[0]["current_collection"];
        if ($usercollection==0 || !is_numeric($usercollection))
        	{
       		# Create a collection for this user
			global $lang;
			include_once "collections_functions.php"; # Make sure collections functions are included before create_collection
			# The collection name is translated when displayed!
			$usercollection=create_collection($userref,"My Collection",0,1); # Do not translate this string!
			# set this to be the user's current collection
			sql_query("update user set current_collection='$usercollection' where ref='$userref'");
        	}
        
        $usersearchfilter=$userdata[0]["search_filter"];
        $usereditfilter=$userdata[0]["edit_filter"];
        $userresourcedefaults=$userdata[0]["resource_defaults"];
        $userrequestmode=trim($userdata[0]["request_mode"]);
    	
        # Some alternative language choices for basket mode / e-commerce
        if ($userrequestmode==2 || $userrequestmode==3)
			{
			$lang["addtocollection"]=$lang["addtobasket"];
			$lang["action-addtocollection"]=$lang["addtobasket"];
			$lang["addtocurrentcollection"]=$lang["addtobasket"];
			$lang["requestaddedtocollection"]=$lang["buyitemaddedtocollection"];
			$lang["action-request"]=$lang["addtobasket"];
			$lang["managemycollections"]=$lang["viewpurchases"];
			$lang["mycollection"]=$lang["yourbasket"];
			$lang["action-removefromcollection"]=$lang["removefrombasket"];
			$lang["total-collections-0"] = $lang["total-orders-0"];
			$lang["total-collections-1"] = $lang["total-orders-1"];
			$lang["total-collections-2"] = $lang["total-orders-2"];
			
			# The request button (renamed "Buy" by the line above) should always add the item to the current collection.
			$request_adds_to_collection=true;
			}        
    
	
        # Apply config override options
        $config_options=trim($userdata[0]["config_options"]);
        if ($config_options!="") {eval($config_options);}
        

        if ($password_expiry>0 && !checkperm("p") && $allow_password_change && $pagename!="user_preferences" && $pagename!="index" && $pagename!="collections" && strlen(trim($userdata[0]["password_last_change"]))>0)
        	{
        	# Redirect the user to the password change page if their password has expired.
	        $last_password_change=time()-strtotime($userdata[0]["password_last_change"]);
			if ($last_password_change>($password_expiry*60*60*24))
				{
				redirect("pages/user_preferences?expired=true");
				}
        	}
        
        if (strlen(trim($userdata[0]["last_active"]))>0)
        	{
	        if ($userdata[0]["idle_seconds"]>($session_length*60))
	        	{
          	    # Last active more than $session_length mins ago?
				$al="";if (isset($anonymous_login)) {$al=$anonymous_login;}
				
				if ($session_autologout && $username!=$al) # If auto logout enabled, but this is not the anonymous user, log them out.
					{
					# Reached the end of valid session time, auto log out the user.
					
					# Remove session
					sql_query("update user set logged_in=0,session='' where ref='$userref'");
			
					# Blank cookie / var
					rs_setcookie("user", "", time() - 3600, "", "", substr($baseurl,0,5)=="https", true);
					unset($username);
		
					if (isset($anonymous_login))
						{
						# If the system is set up with anonymous access, redirect to the home page after logging out.
						redirect("pages/" . $default_home_page);
						}
					else
						{
						$valid=false;
						$autologgedout=true;
						}
					}
				else
	        		{
		        	# Session end reached, but the user may still remain logged in.
			        # This is a new 'session' for the purposes of statistics.
					daily_stat("User session",$userref);
					}
				}
			}
        }
        else {$valid=false;}
    }
else
    {
    $valid=false;
    $nocookies=true;
    
    # Set a cookie that we'll check for again on the login page after the redirection.
    # If this cookie is missing, it's assumed that cookies are switched off or blocked and a warning message is displayed.
    setcookie("cookiecheck","true",0,'/');
    }

if (!$valid && !$api)
    {
	$_SERVER['REQUEST_URI'] = ( isset($_SERVER['REQUEST_URI']) ?
	$_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] . (( isset($_SERVER
	['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')));
    $path=$_SERVER["REQUEST_URI"];
    $path=str_replace("ajax","ajax_disabled",$path);# Disable forwarding of the AJAX parameter if this was an AJAX load, otherwise the redirected page will be missing the header/footer.
	?>
	<script type="text/javascript">
	<?php 
	if (isset($anonymous_login)) 
	    {
	    ?>    
	    top.location.href="<?php echo $baseurl?>/login.php?logout=true<?php if ($autologgedout) { ?>&auto=true<?php } ?><?php if ($nocookies) { ?>&nocookies=true<?php } ?>";
	    </script>
	    <?php
        exit();
        }
    else 
        {
        ?>    
        top.location.href="<?php echo $baseurl?>/login.php?url=<?php echo urlencode($path)?><?php if ($autologgedout) { ?>&auto=true<?php } ?><?php if ($nocookies) { ?>&nocookies=true<?php } ?>";
        </script>
        <?php
        exit();
        }
    }
if (!$valid && $api){echo "invalid login";exit();}    

# Handle IP address restrictions
$ip=get_ip();
$ip_restrict=$ip_restrict_group;
if ($ip_restrict_user!="") {$ip_restrict=$ip_restrict_user;} # User IP restriction overrides the group-wide setting.
if ($ip_restrict!="")
	{
	$allow=false;

	if (!hook('iprestrict'))
		{
		$allow=ip_matches($ip, $ip_restrict);
		}

	if (!$allow)
		{
		if ($iprestrict_friendlyerror)
			{
			exit("Sorry, but the IP address you are using to access the system (" . $ip . ") is not in the permitted list. Please contact an administrator.");
			}
		header("HTTP/1.0 403 Access Denied");
		exit("Access denied.");
		}
	}

#update activity table
global $pagename;
$terms="";if (($pagename!="login") && ($pagename!="terms")) {$terms=",accepted_terms=1";} # Accepted terms
if (!$api){
	if (isset($_SERVER["HTTP_USER_AGENT"])){
	$last_browser=escape_check(substr($_SERVER["HTTP_USER_AGENT"],0,250));
	} else { 
	$last_browser="unknown";
	}
} else {
	$last_browser="API Client";
}

// don't update this table if the System is doing it's own operations
if (!isset($system_login)){
	sql_query("update user set lang='$language', last_active=now(),logged_in=1,last_ip='" . get_ip() . "',last_browser='" . $last_browser . "'$terms where ref='$userref'");
}

# Add group specific text (if any) when logged in.
if (hook("replacesitetextloader"))
	{
	# this hook expects $site_text to be modified and returned by the plugin	 
	$site_text=hook("replacesitetextloader");
	}
else
	{
	if (isset($usergroup))
		{
		$results=sql_query("select language,name,text from site_text where (page='$pagename' or page='all') and specific_to_group='$usergroup'");
		for ($n=0;$n<count($results);$n++) {$site_text[$results[$n]["language"] . "-" . $results[$n]["name"]]=$results[$n]["text"];}
		}
	}	/* end replacesitetextloader */

# Load group specific plugins
$active_plugins = (sql_query("SELECT name,enabled_groups, config, config_json FROM plugins WHERE inst_version>=0 AND length(enabled_groups)>0"));
foreach($active_plugins as $plugin)
	{ 
	# Check group access, only enable for global access at this point
	$s=explode(",",$plugin['enabled_groups']);
	if (in_array($usergroup,$s))
		{
		include_plugin_config($plugin['name'],$plugin['config'],$plugin['config_json']);
		register_plugin($plugin['name']);
		$plugins[]=$plugin['name'];
		}
	}

if ($userpassword=="b58d18f375f68d13587ce8a520a87919" && $pagename!="user_preferences"  && $pagename!="collections"){?>
<script>
	top.location.href="<?php echo $baseurl_short?>pages/user_preferences.php";
</script>
<?php } 
