<?php
 // we get a list of groups from the LDAP;
 include_once ("../hooks/ldap_class.php");

/* Set the following debug flag to true for more debugging information
*/
$ldap_debug = true;
  
$ldapConf['host'] = $_GET['server'];
$ldapConf['port'] = $_GET['port'];
$ldapConf['basedn'] = $_GET['basedn'];
$ldapConf['addomain'] = $_GET['addomain'];
$ldapConf['groupcont'] = $_GET['groupcont'];
$ldapauth['ldaptype'] = $_GET['type'];
if (isset($_GET['rootdn'])) { $ldapauth['rootdn'] = $_GET['rootdn']; }
if (isset($_GET['rootpass'])) { $ldapauth['rootpass'] = $_GET['rootpass']; }

// Language settings
if (isset($_GET['lang_status_error'])) 
	{ 
		$lang['lang_status_error'] = $_GET['lang_status_error']; 
	} else {
		$lang['lang_status_error'] = 'Status error in';	
	}
if (isset($_GET['lang_server_error'])) 
	{ 
		$lang['lang_server_error'] = $_GET['lang_server_error']; 
	} else {
		$lang['lang_server_error'] = 'Server error';
	}
if (isset($_GET['lang_passed '])) 
	{ 
		$lang['lang_passed '] = $_GET['lang_passed ']; 
	} else {
		$lang['lang_passed '] = 'Passed';
	}
if (isset($_GET['lang_could_not_connect'])) 
	{ 
		$lang['lang_could_not_connect'] = $_GET['lang_could_not_connect']; 
	} else {
		$lang['lang_could_not_connect'] = 'Could not connect to LDAP server.';	
	}
if (isset($_GET['lang_could_not_bind'])) 
	{ 
		$lang['lang_could_not_bind'] = $_GET['lang_could_not_bind']; 
	} else {
		$lang['lang_could_not_bind'] = 'Could not bind to AD, please check credentials.';	
	}
if (isset($_GET['lang_test_passed'])) 
	{ 
		$lang['lang_test_passed'] = $_GET['lang_test_passed']; 
	} else {
		$lang['lang_test_passed'] = 'Tests passed, please save your settings and then return to set group mapping.';	
	}
if (isset($_GET['lang_test_failed'])) 
	{ 
		$lang['lang_test_failed'] = $_GET['lang_test_failed']; 
	} else {
		$lang['lang_test_failed'] = 'Tests failed, please check your settings and test again.';
	}


//if (isset($_GET[''])) { $lang[''] = $_GET['']; }

//global $lang;

$objLDAP = new ldapAuth($ldapConf);
if ($ldap_debug) { $objLDAP->ldap_debug = true; };

$returnMessage = array();
$errmsg = false;
$status = true;

if ($objLDAP->connect())
{

	$returnMessage['Connection Test'] = $lang['lang_passed '];
	// we need to check for the kind of LDAP we are talking to here!
	if ($ldapauth['ldaptype'] == 1 )
	{
	// we need to bind!
	if (!$objLDAP->auth($ldapauth['rootdn'],$ldapauth['rootpass'],1,$ldapConf['addomain'],true))
		{
		
		$returnMessage["auth"] = $lang['lang_could_not_bind'];
		$errmsg = true;
		$status = false;
		}
	else
		{
		$returnMessage["AD Bind"] = $lang['lang_passed '];
		}
	}
	
	if (!$errmsg)
	{
		// get the groups
		error_log( " ldapauth:ajax_test_login.php line 35 GOT TO THE GROUP CHECK");
		$ldapGroupList = $objLDAP->listGroups($_GET['type'],$_GET['groupcont']);
		if (is_array($ldapGroupList)) 
		{
			error_log (" ldapauth:ajax_test_login.php line 39 Found Groups");
			$returnMessage["Group check"] = $lang['lang_passed '];
			
		} else {
			error_log (" ldapauth:ajax_test_login.php line 43 NO Groups Found");
			$returnMessage["Group check"] = $ldapGroupList;
			$status = false;
		}
	}
	
			
} else {
	$returnMessage['Connection Test'] = $lang['lang_could_not_connect'];
	$status = false;
}
if ($status)
{
	$returnMessage['Status'] = $lang['lang_test_passed'];
} else {
	$returnMessage['Status'] = $lang['lang_test_failed'];
}

print_r($returnMessage);


?>