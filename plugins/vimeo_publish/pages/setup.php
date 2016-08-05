<?php
// vimeo_publish setup page
include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

$plugin_name         = 'vimeo_publish';
$plugin_page_heading = $lang['vimeo_publish_configuration'];

// Build Insructions from language strings:
$vimeo_api_instructions = '<ul>';
$vimeo_api_instruction_conditions = 1;
while(isset($lang['vimeo_api_instructions_condition_' . $vimeo_api_instruction_conditions]))
    {
    $vimeo_api_instructions .= '<li>' . $lang['vimeo_api_instructions_condition_' . $vimeo_api_instruction_conditions] . '</li>';
    $vimeo_api_instruction_conditions++;
    }
$vimeo_api_instructions .= '</ul>';



$page_def[] = config_add_html("<p><strong>{$lang['vimeo_publish_base']}:</strong> {$baseurl}<br>");
$page_def[] = config_add_html("<strong>{$lang['vimeo_publish_callback_url']}:</strong> {$vimeo_callback_url}</p>");

if(1 < $vimeo_api_instruction_conditions)
    {
    $page_def[] = config_add_section_header($lang['vimeo_publish_vimeo_instructions']);
    $page_def[] = config_add_html($vimeo_api_instructions);
    }

// OAuth 2.0 - Authentication credentials
$page_def[] = config_add_section_header($lang['vimeo_publish_authentication']);
$page_def[] = config_add_text_input('vimeo_publish_client_id', $lang['vimeo_publish_oauth2_client_id']);
$page_def[] = config_add_text_input('vimeo_publish_client_secret', $lang['vimeo_publish_oauth2_client_secret']);

// ResourceSpace - metadata mappings
$page_def[] = config_add_section_header($lang['vimeo_publish_rs_field_mappings']);
$page_def[] = config_add_single_ftype_select('vimeo_publish_vimeo_link_field', $lang['vimeo_publish_vimeo_link']);
$page_def[] = config_add_single_ftype_select('vimeo_publish_video_title_field', $lang['vimeo_publish_video_title']);
$page_def[] = config_add_single_ftype_select('vimeo_publish_video_description_field', $lang['vimeo_publish_video_description']);
$page_def[] = config_add_multi_rtype_select('vimeo_publish_restypes', $lang['vimeo_publish_resource_types_to_include']);



// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';