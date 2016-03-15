<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php'; if(!checkperm('a')) { exit('Permission denied.'); }
include_once '../../include/config_functions.php';


$enable_disable_options = array($lang['userpreference_disable_option'], $lang['userpreference_enable_option']);
$yes_no_options         = array($lang['no'], $lang['yes']);


// System section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead">' . $lang['systemsetup'] . '</h3><div id="SystemConfigSystemSection" class="CollapsibleSection">');
$page_def[] = config_add_text_input('applicationname', $lang['setup-applicationname'], false, 300, false, '', true);
$page_def[] = config_add_file_input(
    'linkedheaderimgsrc',
    $lang['systemconfig_linkedheaderimgsrc_label'],
    $baseurl . '/pages/admin/admin_system_config.php',
    300
);
$page_def[] = config_add_text_input('email_from', $lang['setup-emailfrom'], false, 300, false, '', true);
$page_def[] = config_add_text_input('email_notify', $lang['setup-emailnotify'], false, 300, false, '', true);
$page_def[] = config_add_html('</div>');


// Multilingual section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['systemconfig_multilingual'] . '</h3><div id="SystemConfigMultilingualSection" class="CollapsibleSection">');
$page_def[] = config_add_single_select('defaultlanguage', $lang['systemconfig_default_language_label'], $languages, true, 300, '', true);
$page_def[] = config_add_boolean_select('disable_languages', $lang['disable_languages'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('browser_language', $lang['systemconfig_browser_language_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// Search section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['searchcapability'] . '</h3><div id="SystemConfigSearchSection" class="CollapsibleSection">');
$page_def[] = config_add_single_select(
    'default_sort',
    $lang['userpreference_default_sort_label'],
    array(
        'relevance'  => $lang['relevance'],
        'resourceid' => $lang['resourceid'],
        'popularity' => $lang['popularity'],
        'rating'     => $lang['rating'],
        'date'       => $lang['date'],
        'colour'     => $lang['colour'],
    ),
    true,
    300,
    '',
    true
);
$page_def[] = config_add_single_select('default_perpage', $lang['userpreference_default_perpage_label'], array(24, 48, 72, 120, 240), false, 300, '', true);
$page_def[] = config_add_single_select(
    'default_display',
    $lang['userpreference_default_display_label'],
    array(
        'smallthumbs' => $lang['smallthumbstitle'],
        'thumbs'      => $lang['largethumbstitle'],
        'xlthumbs'    => $lang['xlthumbstitle'],
        'list'        => $lang['listtitle']
    ),
    true,
    300,
    '',
    true
);
$page_def[] = config_add_boolean_select('archive_search', $lang['stat-archivesearch'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('use_checkboxes_for_selection', $lang['userpreference_use_checkboxes_for_selection_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('display_resource_id_in_thumbnail', $lang['systemconfig_display_resource_id_in_thumbnail_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// Navigation section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['systemconfig_navigation'] . '</h3><div id="SystemConfigNavigationSection" class="CollapsibleSection">');
$page_def[] = config_add_boolean_select('help_link', $lang['systemconfig_help_link_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('recent_link', $lang['systemconfig_recent_link_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('mycollections_link', $lang['systemconfig_mycollections_link_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('myrequests_link', $lang['systemconfig_myrequests_link_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('research_link', $lang['systemconfig_research_link_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('enable_themes', $lang['themes'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('themes_navlink', $lang['systemconfig_themes_navlink_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('use_theme_as_home', $lang['systemconfig_use_theme_as_home_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('use_recent_as_home', $lang['systemconfig_use_recent_as_home_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// User interface section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['userpreference_user_interface'] . '</h3><div id="SystemConfigUserInterfaceSection" class="CollapsibleSection">');
$page_def[] = config_add_single_select('thumbs_default', $lang['userpreference_thumbs_default_label'], array('show' => $lang['showthumbnails'], 'hide' => $lang['hidethumbnails']), true, 300, '', true);
$page_def[] = config_add_boolean_select('resource_view_modal', $lang['userpreference_resource_view_modal_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('basic_simple_search', $lang['userpreference_basic_simple_search_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// Workflow section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['systemconfig_workflow'] . '</h3><div id="SystemConfigWorkflowSection" class="CollapsibleSection">');
$page_def[] = config_add_boolean_select('research_request', $lang['researchrequest'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// Metadata section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['metadata'] . '</h3><div id="SystemConfigMetadataSection" class="CollapsibleSection">');
$page_def[] = config_add_boolean_select('metadata_report', $lang['metadata-report'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select(
    'metadata_read_default',
    $lang['embedded_metadata'],
    array($lang['embedded_metadata_donot_extract_option'], $lang['embedded_metadata_extract_option']),
    300,
    '',
    true
);
$page_def[] = config_add_html('</div>');


// User accounts section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['systemconfig_user_accounts'] . '</h3><div id="SystemConfigUserAccountsSection" class="CollapsibleSection">');
$page_def[] = config_add_boolean_select('allow_account_request', $lang['systemconfig_allow_account_request_label'], $yes_no_options, 300, '', true);
$page_def[] = config_add_boolean_select('terms_download', $lang['systemconfig_terms_download_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('terms_login', $lang['systemconfig_terms_login_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_boolean_select('user_rating', $lang['systemconfig_user_rating_label'], $enable_disable_options, 300, '', true);
$page_def[] = config_add_html('</div>');


// Security section
$page_def[] = config_add_html('<h3 class="CollapsibleSectionHead collapsed">' . $lang['systemconfig_security'] . '</h3><div id="SystemConfigSecuritySection" class="CollapsibleSection">');
$page_def[] = config_add_single_select(
    'password_min_length',
    $lang['systemconfig_password_min_length_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'password_min_alpha',
    $lang['systemconfig_password_min_alpha_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'password_min_numeric',
    $lang['systemconfig_password_min_numeric_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'password_min_uppercase',
    $lang['systemconfig_password_min_uppercase_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'password_min_special',
    $lang['systemconfig_password_min_special_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);  
$page_def[] = config_add_single_select(
    'password_expiry',
    $lang['systemconfig_password_expiry_label'],
    array_merge(array(0 => $lang['never']), range(1, 90)),
    true,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'max_login_attempts_per_ip',
    $lang['systemconfig_max_login_attempts_per_ip_label'],
    range(10, 50),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'max_login_attempts_per_username',
    $lang['systemconfig_max_login_attempts_per_username_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'max_login_attempts_wait_minutes',
    $lang['systemconfig_max_login_attempts_wait_minutes_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_single_select(
    'password_brute_force_delay',
    $lang['systemconfig_password_brute_force_delay_label'],
    range(0, 30),
    false,
    300,
    '',
    true
);
$page_def[] = config_add_html('</div>');

// Let plugins hook onto page definition and add their own configs if needed
// or manipulate the list
$plugin_specific_definition = hook('add_system_config_page_def', '', array($page_def));
if(is_array($plugin_specific_definition) && !empty($plugin_specific_definition))
    {
    $page_def = $plugin_specific_definition;
    }


// Process autosaving requests
// Note: $page_def must be defined by now in order to make sure we only save options that we've defined
if('true' === getval('ajax', '') && 'true' === getval('autosave', ''))
    {
    $response['success'] = true;
    $response['message'] = '';

    $autosave_option_name  = getvalescaped('autosave_option_name', '');
    $autosave_option_value = getvalescaped('autosave_option_value', '');

    // Search for the option name within our defined (allowed) options
    // if it is not there, error and don't allow saving it
    $page_def_option_index = array_search($autosave_option_name, array_column($page_def, 1));
    if(false === $page_def_option_index)
        {
        $response['success'] = false;
        $response['message'] = $lang['systemconfig_option_not_allowed_error'];

        echo json_encode($response);
        exit();
        }

    if(!set_config_option(null, $autosave_option_name, $autosave_option_value))
        {
        $response['success'] = false;
        }

    echo json_encode($response);
    exit();
    }


config_process_file_input($page_def, 'system/config', $baseurl . '/pages/admin/admin_system_config.php');
process_config_options();

include '../../include/header.php';
?>
<div class="BasicsBox">
    <h1><?php echo $lang['systemconfig']; ?></h1>
    <p><?php echo $lang['systemconfig_description']; ?></p>
    <div class="CollapsibleSections">
    <?php
    config_generate_html($page_def);
    ?>
    </div>
    <script>registerCollapsibleSections(false);</script>
    <?php config_generate_AutoSaveConfigOption_function($baseurl . '/pages/admin/admin_system_config.php'); ?>
</div>
<?php
include '../../include/footer.php';