<?php
# English
# Language File for the Checkmail Plugin
# -------
#
#
$lang['checkmail_configuration']="Checkmail Configuration";
$lang['checkmail_install_php_imap_extension']="Step One: Install php imap extension.";
$lang['checkmail_cronhelp']="This plugin requires some special setup for the system to log in to an e-mail account dedicated to receiving files intended for upload.<br /><br />Make sure that IMAP is enabled on the account. If you are using a Gmail account you enable IMAP in Settings->POP/IMAP->Enable IMAP<br /><br />
On initial setup, you may find it most helpful to run plugins/checkmail/pages/cron_check_email.php manually on the command line to understand how it works.
Once you are connecting properly and understand how the script works, you must set up a cron job to run it every minute or two.<br />It will scan the mailbox and read one unread e-mail per run.<br /><br />
An example cron job which runs every two minutes:<br />
*/2 * * * * cd /var/www/resourcespace/plugins/checkmail/pages; php ./cron_check_email.php >> /var/log/cron.log 2>&1<br /><br />";
$lang['checkmail_lastcheck']="Your IMAP account was last checked on [lastcheck].";
$lang['checkmail_cronjobprob']="Your checkmail cronjob may not be running properly, because it has been more than 5 minutes since it ran last.<br /><br />
An example cron job which runs every minute:<br />
* * * * * cd /var/www/resourcespace/plugins/checkmail/pages; php ./cron_check_email.php >> /var/log/cron.log 2>&1<br /><br />";
$lang['checkmail_imap_server']="Imap Server<br />(gmail=\"imap.gmail.com:993/ssl\")";
$lang['checkmail_email']="Email";
$lang['checkmail_password']="Password";
$lang['checkmail_extension_mapping']="Resource Type via File Extension Mapping";
$lang['checkmail_default_resource_type']="Default Resource Type";
$lang['checkmail_extension_mapping_desc']="After the Default Resource Type selector, there is one input below for each of your Resource Types. <br />To force uploaded files of different types into a specific Resource Type, add comma separated lists of file extensions (ex. jpg,gif,png).";
$lang['checkmail_resource_type_population']="<br />(from allowed_extensions)";
$lang['checkmail_subject_field']="Subject Field";
$lang['checkmail_body_field']="Body Field";
$lang['checkmail_purge']="Purge e-mails after upload?";
$lang['checkmail_confirm']="Send confirmation e-mails?";
$lang['checkmail_users']="Allowed Users";
$lang['checkmail_default_access']="Default Access";
$lang['checkmail_default_archive']="Default Status";
$lang['checkmail_html']="Allow HTML Content? (experimental, not recommended)";
$lang['checkmail_mail_skipped']="Skipped e-mail";

$lang['addresourcesviaemail']="Add via E-mail";
$lang['uploadviaemail']="Add via E-mail";
$lang['uploadviaemail-intro']="<br /><br />To upload via e-mail, attach your file(s) and address the e-mail to <b><a href='mailto:[toaddress]'>[toaddress]</a></b>.</p> <p>Be sure to send it from <b>[fromaddress]</b>, or it will be ignored.</p><p>Note that anything in the SUBJECT of the e-mail will go into the [subjectfield] field in $applicationname. </p><p> Also note that anything in the BODY of the e-mail will go into the [bodyfield] field in $applicationname. </p>  <p>Multiple files will be grouped into a collection. Your resources will default to an Access level <b>'[access]'</b>, and Archive status <b>'[archive]'</b>.</p><p> [confirmation]";
$lang['checkmail_confirmation_message']="You will receive a confirmation e-mail when your e-mail is successfully processed. If your e-mail is programmatically skipped for any reason (such as if it is sent from the wrong address), the administrator will be notified that there is an e-mail requiring attention.";
$lang['yourresourcehasbeenuploaded']="Your resource has been uploaded";
$lang['yourresourceshavebeenuploaded']="Your resources have been uploaded";
