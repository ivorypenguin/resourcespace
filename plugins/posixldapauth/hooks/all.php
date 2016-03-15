<?php

function HookPosixldapauthAllExternalauth($uname, $pword)
{
	/* Set the following debug flag to true for more debugging information
	*/
	$ldap_debug = true;
	
	include_once "include/collections_functions.php";

	include_once "plugins/posixldapauth/config/config.default.php";
	if (file_exists("plugins/posixldapauth/config/config.php"))
	{
        	include_once("plugins/posixldapauth/config/config.php");
	}
	include_once "plugins/posixldapauth/hooks/ldap_class.php";
	global $username;
	global $password;
	global $password_hash,$use_plugins_manager,$ldapauth;
	$debugMode = false;
        
    if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Starting Debug") ; }    
        
        
	if ($use_plugins_manager==true)
	{
		$ldapauth = get_plugin_config("posixldapauth");
		

		if ($ldapauth==null || $ldapauth['enable']==false) 
		{
			return false;
		}
		if (!isset($ldapauth['ldapgroupcontainer']))
		{
			$ldapauth['ldapgroupcontainer'] = "";
		}
		if (!isset($ldapauth['port']))
		{
			$ldapauth['port'] = 389;
		}
		if (!isset($ldapauth['ldapmemberfield']))
		{
			$ldapauth['ldapmemberfield'] = "";	
		}
			if (!isset($ldapauth['ldapmemberfieldtype']))
		{
			$ldapauth['ldapmemberfieldtype'] = 0;	
		}
		
		if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Configuration") ; }
		
		if ($ldap_debug) {
			foreach ( $ldapauth as $key => $value ) {
				if ($key == "groupmap") {
					foreach ($ldapauth['groupmap'] as $ldapGrpName => $arrLdapGrp) {	
						if ($arrLdapGrp['enabled'])	{
							error_log( $ldapGrpName . " is enabled and mapped to " . $arrLdapGrp['rsGroup']);
							
						}
					} 	
				} else {
					error_log( $key . " = " . $value);	
				}  
 	
			} 		
		}
	}
	
	if ($uname != "" && $pword != "") 
	{
		// pass the config to the class
		$ldapConf['host'] = $ldapauth['ldapserver'];
		$ldapConf['basedn'] = $ldapauth['basedn'];
		$ldapConf['addomain']	= $ldapauth['addomain'];
		$ldapConf['port']	= $ldapauth['port'];
		
		if ($ldapauth['adusesingledomain']) {
			$singleDomain=true;
		} else {
			$singleDomain=false;
		}
		
		$objLdapAuth = new ldapAuth($ldapConf);	
		if ($ldap_debug) { $objLdapAuth->ldap_debug = true; };
		
		// connect to the ldap
		if ($objLdapAuth->connect())
		{
			
			// see if we can bind with the username and password.
			if($objLdapAuth->auth($uname,$pword,$ldapauth['ldaptype'],$ldapauth['ldapusercontainer'],$singleDomain))
			{
				if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . " auth to ldap server is successful ") ; }
			
				$auth = true;
				// get the user info etc	
				$userDetails = $objLdapAuth->getUserDetails($uname);
				//print_r($userDetails);
				if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  cn=" . $userDetails["cn"]) ; }
				if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  dn=" . $userDetails["dn"]) ; }
				
				
				$user_cn = $userDetails["cn"];
				$user_dn = $userDetails["dn"];
				
				/* 	Now we have the user details, we need to figure out if the user exists in the 
					RS database allready, in which case we'll update the passsword, or if it's
					a new user and create users is set, then we create a new user.
					
					Maybe w should also check groups as well? So if group membership has changed the user will be updated!
				*/
				
				$uexists=sql_query('select ref from user where username="'.$uname.$ldapauth['usersuffix'].'"');
				if (count($uexists)>=1) 
				{
					if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  User has allready been added to RS, updating password") ; }
					// if we get here, the user has already been added to RS.
					$username=$uname.$ldapauth['usersuffix'];
					$password_hash = hash('sha256', md5('RS' . $username . $password));
					sql_query('update user set password="'.$password_hash.'" where username="'.$username.'"');
					//          $password=sql_value('select password value from user where username="'.$uname.$ldapauth['usersuffix'].'"',"");
					return true;
				}
				elseif ($ldapauth['createusers']) 
				{
					
					if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Create Users is Enabled") ; }
					// else, is we have specified to create users from the LDAP, we need to get info about the user
					// to add them to resource space.
					$nuser = array();
					// Start Populating User Fields from LDAP
					$nuser['username']=$uname.$ldapauth['usersuffix'];
					$nuser['fullname']=$user_cn;
					if (isset($userDetails["mail"]))
					{
						$nuser['email']=$userDetails["mail"];
					} else {
						$nuser['email']="$uname@mail";
					}
					$nuser['password'] = hash('sha256', md5('RS' . $nuser['username'] . $password));
					
					// Set a var so that we can keep track of the group level as we scan the access groups.
					$currentGroupLevel = 0;
					
				
					
					if ($ldapauth['groupbased'])
					{
						if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Group Based is Enabled, checking Groups") ; }
						// set match to false as default"
						$match = false;						
						/* 	At this point we want to do a switch on the type of directory we are authenticing against
							so that we can use group matching for the different types of directory layout:
							ie, AD uses memberof, OD doesn't!
							We also need to check for higher numbered groups, ie if a user is amember of staff, and of admin users,
							we need to give them the highest access!
						*/
						if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Group Based is Enabled, checking Groups") ; }
						
						// set the uid, ie the username...
						$objLdapAuth->userName = $uname;
							
						// now we cycle through the config array to check groups!
						foreach ($ldapauth['groupmap'] as $ldapGrpName => $arrLdapGrp)
						{
							// check to see if we are allowing users in this group to log in?
							if ($arrLdapGrp['enabled'])
							{
								if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Checking Group " . $ldapGrpName) ; }
								// get the group name and check group membership	
								if ($objLdapAuth->checkGroupByName($ldapGrpName,$ldapauth['ldaptype'],$ldapauth['ldapgroupcontainer'],$ldapauth['ldapmemberfield'],$ldapauth['ldapmemberfieldtype']))
								{
									if ( $match )
									{
										if ($currentGroupLevel < $arrLdapGrp['rsGroup'])
										{
											$nuser['usergroup'] = $arrLdapGrp['rsGroup'];
											$currentGroupLevel = $arrLdapGrp['rsGroup'];
										}
									} else {	
										$match = true;
									
										$nuser['usergroup'] = $arrLdapGrp['rsGroup'];
										$currentGroupLevel = $arrLdapGrp['rsGroup'];
									} 
									if ($ldap_debug) { error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Match found in group " . $ldapGrpName) ; }
								}
							}	
						}
						
						
							// if we haven't managed to find a group match that is allowed to log into RS, then
							// we return false!	- we ned to modify this to use the group set if group based is not enabled!
							if (!($match)) return false;
							// Create the user
							if ($ldap_debug) { error_log(  __METHOD__ . " " . __LINE__ . "  Creating User: " . $nuser['username']) ; }
							
							// create the user and get a reference number back.
							$ref=new_user($nuser['username']);
							
							if ($ldap_debug) { error_log(  __METHOD__ . " " . __LINE__ . "  User Ref: " . $ref) ; }
							if (!$ref) 
							{
								if ($ldap_debug) { 
									error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  Group based User creation ref NOT RETURNED, SOMETHING WEIRD HAPPENED!"); 
									}
								return false; # Shouldn't ever get here.  Something strange happened
							}
							// Update with information from LDAP
							sql_query('update user set password="'.$nuser['password'].
								'", fullname="'.$nuser['fullname'].'", email="'.$nuser['email'].'", usergroup="'.
								$nuser['usergroup'].'", comments="Auto create from LDAP" where ref="'.$ref.'"');
								
							$username=$nuser['username'];
							$password=$nuser['password'];
							$password_hash=$nuser['password'];
	
							// now unbind
							$objLdapAuth->unBind();	
							
							if ($ldap_debug) { error_log(  __METHOD__ . " " . __LINE__ . "  returning true : successful user creation!") ; }
							return true;
						
					} else {
							// non group based user creation.
		                    $ref=new_user($nuser['username']);
		                   	if (!$ref) 
							{
								if ($ldap_debug) { 
									error_log( __FILE__ . " " . __METHOD__ . " " . __LINE__ . "  NON Group based User creation ref NOT RETURNED, SOMETHING WEIRD HAPPENED!"); 
								} 
								return false; # Shouldn't ever get here.  Something strange happened
							}
		                    // Update with information from LDAP
		                    sql_query('update user set password="'.$nuser['password'].
		                            '", fullname="'.$nuser['fullname'].'", email="'.$nuser['email'].'", usergroup="'.
		                            $ldapauth['newusergroup'].'", comments="Auto create from LDAP" where ref="'.$ref.'"');
		
		                    $username=$nuser['username'];
		                    $password=$nuser['password'];
					}	
				}		
			} else {					
				// username / password is wrong!
				return false;
			}		
		}	
		return false;		
	}
}

function HookPosixldapauthAllAdditionalheaderjs(){
    global $baseurl,$baseurl_short;?>
    <script type="text/javascript" src="<?php echo $baseurl?>/plugins/posixldapauth/pages/ldap_functions.js" language="javascript"></script>
    
<?php
}