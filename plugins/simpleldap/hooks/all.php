<?php

include_once(dirname(__FILE__) . "/../include/simpleldap_functions.php");

function HookSimpleldapAllExternalauth($uname, $pword){
	if (!function_exists('ldap_connect')){return false;}
	global $simpleldap;
	global $username;
	global $password_hash, $email_attribute, $phone_attribute;
	
	// oops - the password is getting escaped earlier in the process, and we don't want that 
    // when it goes to the ldap server. So remove the slashes for this purpose.
    $pword = stripslashes($pword);
	
	$auth = false;
	$authreturn=array();
	if ($uname != "" && $pword != "") {
		$userinfo = simpleldap_authenticate($uname, $pword);
		//print_r($userinfo);
		if ($userinfo) { $auth = true; }
	} 


		
	if ($auth) {

		$usersuffix = $simpleldap['usersuffix'];
		$addsuffix=($usersuffix=="")?"":"." . $usersuffix;
		$username=escape_check($uname . $addsuffix);
		$password_hash= md5("RS".$username.$pword);
		$userid = sql_value("select ref value from user where username='".$uname . $addsuffix ."'",0);
		$email=escape_check($userinfo["email"]);
		$phone=escape_check($userinfo["phone"]);
		$displayname=escape_check($userinfo['displayname']);
		debug ("LDAP - got user details email: " . $email . ", telephone: " . $phone);
		// figure out group
		$group = $simpleldap['fallbackusergroup'];
		$groupmatch="";
		$grouplist = sql_query("select * from simpleldap_groupmap");
		if (count($grouplist)>0 && $userinfo['group']!=""){
			for ($i = 0; $i < count($grouplist); $i++){
				if (($userinfo['group'] == $grouplist[$i]['ldapgroup']) && is_numeric($grouplist[$i]['rsgroup'])){
					$group = $grouplist[$i]['rsgroup'];
					$groupmatch=$userinfo['group'];
				}
			}
		}
					

		if ($userid > 0){
			// user exists, so update info
			if($simpleldap['update_group'])
				{
				sql_query("update user set password = '$password_hash', usergroup = '$group', fullname='$displayname', email='$email', telephone='$phone' where ref = '$userid'");
				
				}
			else
				{
				sql_query("update user set password = '$password_hash', fullname='$displayname', email='$email', telephone='$phone' where ref = '$userid'");
				}
			return true;
		} else {
			// user authenticated, but does not exist, so create if necessary
			if ($simpleldap['createusers']){	
				
				$email_matches=sql_query("select ref, username, fullname from user where email='" . $email . "'");				
												
				if(count($email_matches)>0)
					{				
					if(count($email_matches)==1 && $simpleldap['create_new_match_email'])
						{
						// We want adopt this matching account - update the username and details to match the new login credentials
						debug("LDAP - user authenticated with matching email for existing user . " . $email . ", updating user account " . $email_matches[0]["username"] . " to new username " . $username);
						if($simpleldap['update_group'])
							{
							sql_query("update user set username='$username', password='$password_hash', fullname='$displayname',email='$email',telephone='$phone',usergroup='$group',comments=concat(comments,'\n" . date("Y-m-d") . " Updated to LDAP user by SimpleLDAP.') where ref='" . $email_matches[0]["ref"] . "'");
							}
						else
							{
							sql_query("update user set username='$username', password='$password_hash', fullname='$displayname',email='$email',telephone='$phone',comments=concat(comments,'\n" . date("Y-m-d") . " Updated to LDAP user by SimpleLDAP.') where ref='" . $email_matches[0]["ref"] . "'");
							}
						return true;
						}
						
					if (isset($simpleldap['notification_email']) && $simpleldap['notification_email']!="")
						{
						// Already account(s) with this email address, notify the administrator
						global $lang, $baseurl, $email_from;
						debug("LDAP - user authenticated with matching email for existing users: " . $email);
						$emailtext=$lang['simpleldap_multiple_email_match_text'] . " " . $email . "<br /><br />";
						$emailtext.="<table class=\"InfoTable\" border=1>";
						$emailtext.="<tr><th>" . $lang["property-name"] . "</th><th>" . $lang["property-reference"] . "</th><th>" . $lang["username"] . "</th></tr>";
						foreach($email_matches as $email_match)
							{
							$emailtext.="<tr><td><a href=\"" . $baseurl . "/?u=" . $email_match["ref"] .  "\" target=\"_blank\">" . $email_match["fullname"] . "</a></td><td><a href=\"" . $baseurl . "/?u=" . $email_match["ref"] .  "\" target=\"_blank\">" . $email_match["ref"] . "</a></td><td>" . $email_match["username"] . "</td></tr>\n";
							}
						
						$emailtext.="</table>";
						send_mail($simpleldap['notification_email'],$lang['simpleldap_multiple_email_match_subject'],$emailtext,$email_from);
						}
							
				
					if(!$simpleldap['allow_duplicate_email'])
						{
						// We are blocking accounts with the same email
						$authreturn["error"]=$lang['simpleldap_duplicate_email_error'];
						return $authreturn;
						}										
					}
			
				// Create the user
				$ref=new_user($username);
				if (!$ref) { echo "returning false!"; exit; return false;} // this shouldn't ever happen
				
				if($groupmatch=="" && isset($simpleldap['notification_email']) && $simpleldap['notification_email']!="")
					{
					global $lang, $baseurl, $email_from;
					// send email advising that a new user has been created but that there is no mapping for the groups
					debug("LDAP - new user but no mapping configured");
					$emailtext=$lang['simpleldap_no_group_match'] . "<br /><br />";
					$emailtext.= "<a href=\"" . $baseurl . "/?u=" . $ref .  "\" target=\"_blank\">" . $displayname . " (" . $email . ")</a><br /><br />";
					$emailtext.= $lang['simpleldap_usermemberof'] . "<br /><br />";
					if(is_array($userinfo["memberof"]))
						{
						$emailtext.="<ul>";
						foreach($userinfo["memberof"] as $memberofgroup)
							{
							$emailtext.= "<li>" . $memberofgroup . "</li>";
							}	
						$emailtext.="</ul>";
						}
					send_mail($simpleldap['notification_email'],$lang['simpleldap_no_group_match_subject'],$emailtext,$email_from);
					}
				
				
				// Update with information from LDAP	
				$rsgroupname=sql_value("select name value from usergroup where ref='$group'",'');
				sql_query("update user set password='$password_hash', fullname='$displayname',email='$email',telephone='$phone',usergroup='$group',comments='Auto create from SimpleLDAP." . (($groupmatch!="")?"\r\nLDAP group: " . escape_check($groupmatch):"") . "\r\nAdded to RS group " . escape_check($rsgroupname) . "(" . $group . ")' where ref='$ref'");
						
				
				return true;
			} else {
				// user creation is disabled, so return false
				return false;
			}

		}
	

	} else {
		// user is not authorized
		return false;
	}


}
		
?>
