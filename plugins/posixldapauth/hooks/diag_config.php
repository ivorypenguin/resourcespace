<?php
$ldapauth['ldapserver'] = '10.177.177.20';
$ldapauth['port'] = NULL;
// ldap auth type, 0 = Open Directory or open ldap, 1 = Active Directory
$ldapauth['type'] = 1;

// root dn of the directory administrator
$ldapauth['rootdn']= 'uid=administrator,cn=users,dc=example,dc=com';

/* name of the directory administrator
	ad = username@domain
	ldap = username
*/
$ldapauth['rootname'] = "administrator@example.com";
// password
$ldapauth['rootpass']= 'pass';

// base dn od the ldap tree
$ldapauth['basedn']= 'dc=example,dc=com';

/* user container
	This should be something like 
		cn=users
*/
$ldapauth['usercontainer'] = "cn=users";

// container for groups, leave as null to use default cn=groups, basedn
$ldapauth['groupcontainer'] = NULL;

/* login field overide, leave aas null for default
	Defaults are:
	ldap = uid
	ad = samaccountname
*/
$ldapauth['loginfield'] = NULL;

/* group field override to search within ldap groups for members, defaults are:
	ldap = memberuid
	ad = member
*/
$ldapauth['ldapgroupfield'] = NULL;

/* field type overide to search within groups:
	0 = Default for directory type.
	1 = User Name
	2 = RDN
*/
$ldapauth['memFieldType'] = 0;



?>