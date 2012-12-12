<?php
 // we get a list of groups from the LDAP;
 include_once ("../hooks/ldap_class.php");
  
$ldapConf['host'] = $_GET['server'];
$ldapConf['basedn'] = $_GET['basedn'];
	
$objLDAP = new ldapAuth($ldapConf);

$returnMessage = array();
$errmsg = false;
$status = true;

if ($objLDAP->connect())
{
	$returnMessage['Connection Test'] = $lang['posixldapauth_passed'];
	// we need to check for the kind of LDAP we are talking to here!
	if ($ldapauth['ldaptype'] == 1 )
	{
		// we need to bind!
		if (!$objLDAP->auth($ldapauth['rootdn'],$ldapauth['rootpass'],1,$ldapauth['addomain']))
		{
			$returnMessage["auth"] = $lang['posixldapauth_could_not_bind_to_ad_check_credentials'];
			$errmsg = true;
			$status = false;
		} else {
			$returnMessage["AD Bind"] = $lang['posixldapauth_passed'];
		}
	}
	
	if (!$errmsg)
	{
		// get the groups
		error_log( " ldapauth:setup.php line 94 GOT TO THE GROUP SELECT ");
		$ldapGroupList = $objLDAP->listGroups($_GET['type'],$_GET['groupcont']);
		if (is_array($ldapGroupList)) 
		{
			$returnMessage["Group check"] = $lang['posixldapauth_passed'];
			
		} else {
			$returnMessage["Group check"] = $ldapGroupList;
			$status = false;
		}
	}
	
			
} else {
	$returnMessage['Connection Test'] = $lang['posixldapauth_connection_to_ldap_server_failed'];
	$status = false;
}
if ($status)
{
	$returnMessage['Status'] = $lang['posixldapauth_tests_passed_save_settings_and_set_group_mapping'];
} else {
	$returnMessage['Status'] = $lang['posixldapauth_tests_failed_check_settings_and_test_again'];
}

print_r($returnMessage);


?>