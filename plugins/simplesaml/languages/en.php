<?php

$lang['simplesaml_configuration']="SimpleSAML configuration";
$lang['simplesaml_main_options']="Usage options";
$lang['simplesaml_site_block']="Use SAML to block access to site completely, if set to true then no one can access site, even anonymously, without authenticating";
$lang['simplesaml_allow_public_shares']="If blocking site, allow public shares to bypass SAML authentication?";
$lang['simplesaml_allowedpaths']="List of additional allowed paths that can bypass SAML requirement";
$lang['simplesaml_allow_standard_login']="Allow users to log in with standard accounts as well as using SAML SSO?";
$lang["simplesaml_use_sso"]="Use SSO to log in";
$lang['simplesaml_idp_configuration']="IdP configuration";
$lang['simplesaml_idp_configuration_description']="Use the following to configure the plugin to work with your IdP";
$lang["simplesaml_username_attribute"]="Attribute to use for username";
$lang["simplesaml_fullname_attribute"]="Attribute to use for full name. If multiple attributes are to be joined e.g. firstname and lastname enter them separated by a semi-colon";
$lang["simplesaml_email_attribute"]="Attribute to use for email address";
$lang["simplesaml_group_attribute"]="Attribute to use to determine group membership";
$lang["simplesaml_username_suffix"]="Suffix to add to created usernames to distinguish them from standard ResourceSpace accounts";
$lang["simplesaml_update_group"]="Update user group at each logon. If not using SSO group attribute to determine access then set this to false so that users can be manually moved between groups ";
$lang['simplesaml_groupmapping'] = "SAML - ResourceSpace Group Mapping";
$lang['simplesaml_fallback_group']="Default user group that will be used for newly created users";
$lang['simplesaml_samlgroup'] = "SAML group";
$lang['simplesaml_rsgroup'] = "ResourceSpace Group";
$lang['simplesaml_priority']="Priority (higher number will take precedence)";
$lang['simplesaml_addrow'] = "Add mapping";
$lang['simplesaml_service_provider'] = "Name of local service provider (SP)";
$lang['simplesaml_prefer_standard_login'] = "Prefer standard login (redirect to login page by default)";
