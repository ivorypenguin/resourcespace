<?php
#
# Swedish Language File for the PosixLDAPAuth Plugin
# Updated by Henrik Frizén 20131024 for svn r4998
# -------
#
#
$lang['posixldapauth_plugin_heading'] = 'Posixldapauth – inställningar';
$lang['posixldapauth_enabled'] = 'Aktiverat:';
$lang['posixldapauth_ldap_server'] = 'Ldap-server:';
$lang['posixldapauth_ldap_information'] = 'Ldap-information';
$lang['posixldapauth_ldap_type'] = 'Ldap-typ:';
$lang['posixldapauth_open_directory'] = 'Open Directory';
$lang['posixldapauth_active_directory'] = 'Active Directory';
$lang['posixldapauth_ad_admin'] = 'Administratör (AD):';
$lang['posixldapauth_ad_password'] = 'Lösenord (AD):';
$lang['posixldapauth_ad_domain'] = 'Domän (AD):';
$lang['posixldapauth_base_dn'] = 'Bas-DN:';
$lang['posixldapauth_user_container'] = 'Användarbehållare:';
$lang['posixldapauth_this_is_added_to_base_dn'] = 'Denna läggs till, till bas-DN.';
$lang['posixldapauth_group_container'] = 'Grupp DN:';
$lang['posixldapauth_leave_blank_for_default_osx_server_mapping'] = 'Lämna tomt för standardknytningar i OSX Server.';
$lang['posixldapauth_member_field'] = 'Medlemsfält:';
$lang['posixldapauth_use_to_overide_group_containers_member_field'] = 'Använd om du vill åsidosätta gruppbehållarens medlemsfält.';
$lang['posixldapauth_member_field_type'] = 'Medlemsfälttyp:';
$lang['posixldapauth_default'] = 'Standard';
$lang['posixldapauth_user_name'] = 'Användarnamn';
$lang['posixldapauth_rdn'] = 'RDN';
$lang['posixldapauth_use_to_change_content_of_group_member_field'] = 'Använd om du vill ändra innehållet i gruppmedlemsfältet.';
$lang['posixldapauth_login_field'] = 'Inloggningsfält:';
$lang['posixldapauth_test_connection'] = 'Testa anslutning:';
$lang['posixldapauth_test'] = 'Testa';
$lang['posixldapauth_resourcespace_configuration'] = 'Inställningar för ResourceSpace';
$lang['posixldapauth_user_suffix'] = 'Användarsuffix:';
$lang['posixldapauth_create_users'] = 'Skapa användare:';
$lang['posixldapauth_group_based_user_creation'] = 'Gruppbaserat skapande av användare:';
$lang['posixldapauth_new_user_group'] = 'Grupp för nya användare:';
$lang['posixldapauth_group_mapping'] = 'Gruppknytning';
$lang['posixldapauth_group_name'] = 'Gruppnamn';
$lang['posixldapauth_map_to'] = 'Knyt till';
$lang['posixldapauth_enable_group'] = 'Aktivera grupp';
$lang['posixldapauth_could_not_bind_to_ad_check_credentials'] = 'Kunde inte binda till AD, kontrollera autentiseringsuppgifterna.';
$lang['posixldapauth_connection_to_ldap_server_failed'] = 'Anslutningen till ldap-servern misslyckades.';
$lang['posixldapauth_error-msg'] = 'Fel: %msg%'; # %msg% will be replaced, e.g. Error: Could not bind to AD, please check credentials.

$lang['posixldapauth_passed'] = 'Okej';
$lang['posixldapauth_tests_passed_save_settings_and_set_group_mapping'] = 'Testen lyckades, spara inställningarna och återvänd sedan och ställ in gruppknytningen.';
$lang['posixldapauth_tests_failed_check_settings_and_test_again'] = 'Testen misslyckades, kontrollera inställningarna och testa igen.';

$lang['posixldapauth_status_error_in'] = 'Statusfel i';
$lang['posixldapauth_server_error'] = 'Serverfel';

$lang['posixldapauth_could_not_connect_to_ldap_server'] = 'Kunde inte ansluta till ldap-servern.';
$lang['posixldapauth_unable_to_search_ldap_server'] = 'Kan inte söka på ldap-servern.';
$lang['posixldapauth_ldap_call_failed_please_check_settings'] = '%call% misslyckades, kontrollera inställningarna.'; # %call% will be replaced, e.g. ldap_search([used parameters]) failed, please check settings.
$lang['posixldapauth_ldap_search_successfull_but_no_groups_found'] = 'Ldap-sökningen lyckades, men inga grupper hittades.';
