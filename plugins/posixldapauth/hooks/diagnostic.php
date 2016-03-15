<?php
include_once "ldap_class.php";
include_once "diag_config.php";

//date_default_timezone_set();
if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
@date_default_timezone_set(@date_default_timezone_get());
echo " ================================================== \r\n";
echo " Ldap plugin diagnostic test: run on " . date('l jS \of F Y h:i:s A'). "\r\n";
echo " ================================================== \r\n";
echo " Config: \r\n";
print_r($ldapauth);
echo "\r\n";
 
  
  
$ldapConf['host']   = $ldapauth['ldapserver'];
$ldapConf['port']   = $ldapauth['port'];
$ldapConf['basedn'] = $ldapauth['basedn'];

//global $lang;

$objLDAP = new ldapAuth($ldapConf);
$objLDAP->ldap_debug = true;

echo " ================================================== \r\n";
echo " Attempting to connect to ldap server ".$ldapConf['host']." \r\n"; 
echo " ================================================== \r\n";
if ($objLDAP->connect())
{
	echo "Succesful connection to ldap server \r\n";
} else {
	echo "Connection to ldap server failed, please check you configuration! \r\n";
	exit;
}
	
echo "\r\n";
echo " ================================================== \r\n";	
echo " Attempting to bind to ldap with user ".$ldapauth['rootname']." \r\n";
echo " ================================================== \r\n";
if (!$objLDAP->auth($ldapauth['rootname'],$ldapauth['rootpass'],$ldapauth['type'],$ldapauth['usercontainer']))
{ 
	echo "BIND failed, please check your config! \r\n";
	exit;
} else {
	echo "BIND was succesful \r\n";
}

echo "\r\n";
echo " ================================================== \r\n";
echo " Getting user details for ".$ldapauth['rootname']." from directory \r\n";
echo " ================================================== \r\n";
$details = $objLDAP->getUserDetails($ldapauth['rootname']);
foreach ($details as $key => $value)
{
	echo $key ."=" .$value . "\r\n";
}

echo "\r\n";
echo " ================================================== \r\n";
echo " Attempting to find groups,  group container = ";
if (($ldapauth['groupcontainer'] != "") && ($ldapauth['groupcontainer'] != null) && ($ldapauth['groupcontainer'] != " "))
{
	echo $ldapauth['groupcontainer'];	
} else {
	echo " Default Values ";	
}
echo "\r\n";
echo " ================================================== \r\n";

$ldapGroupList = $objLDAP->listGroups($ldapauth['type'],$ldapauth['groupcontainer']);
if (is_array($ldapGroupList)) 
{
	echo "The following groups and members were found \r\n";
	foreach ($ldapGroupList as $group)
	{
		echo "cn = " . $group['cn'] ."\r\n";	
		// function checkGroupByName($groupName, $ldapType=0,$groupContainer="",$memField="",$memFieldType=0)
		$members = $objLDAP->checkGroupByName($group['cn'],$ldapauth['type'],$ldapauth['groupcontainer'],$ldapauth['ldapgroupfield'],$ldapauth['memFieldType']);
		echo "\r\n";
	}	
} else {
	echo "NO Groups were Found, please check your config \r\n";
}


$objLDAP->unbind();





?>