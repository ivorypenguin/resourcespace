<?php
$ldapauth['ldapserver'] = '10.177.177.19';
$ldapauth['port'] = NULL;
// ldap auth type, 0 = Open Directory or open ldap, 1 = Active Directory
$ldapauth['type'] = 0;

// root dn of the directory administrator
$ldapauth['rootdn']= 'uid=diradmin,cn=users,dc=example,dc=com';

/* name of the directory administrator
	ad = username@domain
	ldap = username
*/
$ldapauth['rootname'] = "admin";
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
$ldapauth['groupcontainer'] = "cn=groups, dc=example,dc=com";

/* login field overide, leave aas null for default
	Defaults are:
	ldap = uid
	ad = samaccountname
*/
$ldapauth['loginfield'] = "uid";

/* group field override to search within ldap groups for members, defaults are:
	ldap = memberuid
	ad = member
*/
$ldapauth['ldapgroupfield'] = "memberuid";

/* field type overide to search within groups:
	0 = Default for directory type.
	1 = User Name
	2 = RDN
*/
$ldapauth['memFieldType'] = 1;



?>