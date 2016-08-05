<?php
include "../../../include/db.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}
include "../../../include/general.php";


$simpleldap['domain'] = getvalescaped('domain','');
$simpleldap['ldapserver'] = getvalescaped('ldapserver','');
$simpleldap['ldapuser'] = getvalescaped('ldapuser','');
$simpleldap['ldappassword'] = getvalescaped('ldappassword','');
$userdomain = getvalescaped('userdomain','');
$simpleldap['port'] = getvalescaped('port','');
$simpleldap['ldaptype'] = getvalescaped('ldaptype',1);
$simpleldap['basedn']= getvalescaped('basedn','');
$simpleldap['loginfield'] = getvalescaped('loginfield','');
$simpleldap['ldapgroupfield'] = getvalescaped('ldapgroupfield','');
$simpleldap['email_attribute'] = getvalescaped('email_attribute','');
$simpleldap['phone_attribute'] = getvalescaped('phone_attribute','');

// Test we can connect to domain
$bindsuccess=false;	
$ds = ldap_connect( $simpleldap['ldapserver'],$simpleldap['port'] );	
if(!isset($simpleldap['ldaptype']) || $simpleldap['ldaptype']==1) 
	{
	$binduserstring = $simpleldap['ldapuser'] . "@" . $userdomain;
	debug("LDAP - Attempting to bind to AD server as : " . $binduserstring);
	$login = @ldap_bind( $ds, $binduserstring, $simpleldap['ldappassword'] );
	if ($login)
		{
		debug("LDAP - Success binding to AD server as : " . $binduserstring);
		$bindsuccess=true;
		}
	else
		{
		debug("LDAP - Failed binding to AD server as : " . $binduserstring);
		}
	}
else
	{
	$searchdns=explode(";",$simpleldap['basedn']);
	foreach($searchdns as $searchdn)
		{
		$binduserstring = $simpleldap['loginfield'] . "=" . $simpleldap['ldapuser'] . "," . $searchdn;
		debug("LDAP - Attempting to bind to LDAP server as : " . $binduserstring);
		$login = @ldap_bind( $ds, $binduserstring, $simpleldap['ldappassword'] );
		if (!$login){continue;}else{$bindsuccess=true;break;}
		}
	}			
	
$response['bindsuccess']=$bindsuccess?$lang["status-ok"]:$lang["status-fail"];	
$response['memberof'] = array();

$userdetails=simpleldap_authenticate($simpleldap['ldapuser'],$simpleldap['ldappassword']);

if($userdetails)
	{
	$response['success'] = true;
	$response['message'] = $lang["status-ok"];
	$response['domain'] = $userdetails['domain'];
	$response['binduser'] = $userdetails['binduser'];
	$response['username'] = $userdetails['username'];
	$response['displayname'] = $userdetails['displayname'];
	$response['group'] = $userdetails['group'];
	$response['email'] = $userdetails['email'];
	$response['phone'] = $userdetails['phone'];
	$response['memberof'] = $userdetails['memberof'];
	}
else
	{
	$response['success'] = false;
	$response['message'] = $lang["status-fail"];
	}

$response['complete'] = true;

echo json_encode($response);
exit();