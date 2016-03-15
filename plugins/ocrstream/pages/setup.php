<?php

# Do the include and authorization checking ritual -- don't change this section.
include '../../../include/db.php';
include '../../../include/authenticate.php';
if (!checkperm('a')) {
    exit($lang['error-permissiondenied']);
}
include '../../../include/general.php';

require "../include/ocrstream_functions.php";

# Specify the name of this plugin, the heading to display for the page and the
# optional introductory text. Set $page_intro to "" for no intro text
# Change to match your plugin.
$plugin_name = 'ocrstream';
$page_heading = $lang['ocrstream_title'];
$page_intro = '<p>' . $lang['ocrstream_intro'] . '</p>';
$page_def = array();

if (is_tesseract_installed()) {
    $tesseract_version_output = get_tesseract_version();
    $tesseract_version = $tesseract_version_output[0];
    $leptonica_version = $tesseract_version_output[1];
    $tesseract_languages = get_tesseract_languages();
    $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_info']);
    $page_def[] = config_add_html("<div id = 'tesseract_version'><p style=font-size:14px;>Tesseract version: ". $tesseract_version . "<br> Leptonica version: " . $leptonica_version . "</p></div>");
    if ($tesseract_version_output[2]) {
        $page_def[] = config_add_html($lang['tesseract_old_version_info']);
        $page_def[] = config_add_html("<p><br></p>");
    }
    $page_def[] = config_add_single_select('ocr_global_language', $lang['ocrstream_language_select'], $tesseract_languages, false, 90);
} else { 
    $page_def[] = config_add_text_input('tesseract_path', $lang['tesseract_path_input']);
}
//$page_def[] = config_add_single_select('ocr_psm_global', $lang['ocr_psm'], $ocr_psm_array, true, 414); // not really needed here
$page_def[] = config_add_single_ftype_select('ocr_ftype_1', $lang['ocr_ftype_1']);
$page_def[] = config_add_boolean_select ('use_ocr_db_filter', $lang['ocr_db_filter'], '', 90);
$page_def[] = config_add_single_rtype_select('ocr_rtype', $lang['ocr_rtype'], 90);
$page_def[] = config_add_text_list_input('ocr_allowed_extensions', $lang['ocr_input_formats']);
$page_def[] = config_add_text_input('ocr_min_density', $lang['ocr_min_density'], false, 45);
$page_def[] = config_add_text_input('ocr_max_density', $lang['ocr_max_density'], false, 45);
$page_def[] = config_add_text_input('ocr_min_geometry', $lang['ocr_min_geometry'], false, 45);
$page_def[] = config_add_text_input('ocr_max_geometry', $lang['ocr_max_geometry'], false, 45);
## Disabled until properly implemented
//$page_def[] = config_add_boolean_select('ocr_cronjob_enabled', $lang['ocr_cronjob'], '', 90);
$page_def[] = config_add_section_header($lang['im_processing_header']);
$page_def[] = config_add_html("<div id= 'preset_1'><p style=font-size:18px;>Preset 1</p></div>");
$page_def[] = config_add_text_input('im_preset_1_density', $lang['im_preset_density'], false, 45);
$page_def[] = config_add_text_input('im_preset_1_geometry', $lang['im_preset_geometry'], false, 45);
$page_def[] = config_add_text_input('im_preset_1_quality', $lang['im_preset_quality'], false, 45);
$page_def[] = config_add_text_input('im_preset_1_deskew', $lang['im_preset_deskew'], false, 45);
$page_def[] = config_add_text_input('im_preset_1_sharpen_r', $lang['im_preset_sharpen_r'], false, 45);
$page_def[] = config_add_text_input('im_preset_1_sharpen_s', $lang['im_preset_sharpen_s'], false, 45);
$page_def[] = config_add_section_header($lang['im_advanced_options_header']);
$page_def[] = config_add_boolean_select('ocr_keep_tempfiles', $lang['ocr_keep_tempfiles'], '', 90);

# Build the $page_def array of descriptions of each configuration variable the plugin uses.
# Each element of $page_def describes one configuration variable. Each description is
# created by one of the config_add_xxxx helper functions. See their definitions and
# descriptions in include/plugin_functions for more information.
# Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
?>
<input type="submit" id="purge" value="<?php echo $lang['purge_config']?>" style="display: none;">
<?php
include '../../../include/footer.php';
?>
<link rel="stylesheet" href="../vendor/components/jqueryui/themes/base/jquery-ui.css">
<script src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script>
    jQuery(document).ready(function () {
        if (jQuery('#tesseract_version').length === 0) {
            jQuery('#ocr_ftype_1').parent().closest('div').hide();
            jQuery('#use_ocr_db_filter').parent().closest('div').hide();
            jQuery('#ocr_allowed_extensions').parent().closest('div').hide();
            jQuery('#ocr_min_density').parent().closest('div').hide();
            jQuery('#ocr_max_density').parent().closest('div').hide();
            jQuery('#ocr_min_geometry').parent().closest('div').hide();
            jQuery('#ocr_max_geometry').parent().closest('div').hide();
            jQuery('#ocr_cronjob_enabled').parent().closest('div').hide();
            jQuery('#im_processing_headerDIV').parent().closest('div').hide();
            jQuery('#preset_1').hide();
            jQuery('#im_preset_1_density').parent().closest('div').hide();
            jQuery('#im_preset_1_geometry').parent().closest('div').hide();
            jQuery('#im_preset_1_quality').parent().closest('div').hide();
            jQuery('#im_preset_1_deskew').parent().closest('div').hide();
            jQuery('#im_preset_1_sharpen_r').parent().closest('div').hide();
            jQuery('#im_preset_1_sharpen_s').parent().closest('div').hide();
            jQuery('#ocr_rtype').parent().closest('div').hide();
            jQuery('#im_advanced_options_headerDIV').parent().closest('div').hide();
            jQuery('#ocr_keep_tempfiles').parent().closest('div').hide();

        }
        jQuery('#save').parent().closest('div').append(jQuery('#purge').show());
        var pluginName = '<?php echo $plugin_name?>';
        var baseUrl = '<?php echo $baseurl?>';
        jQuery('#purge').click(function () {
            console.log('purging...'); // debug
            jQuery.get(baseUrl + '/plugins/ocrstream/include/rest.php', {name: pluginName, purge_config: 1}, function (data) {
                status = JSON.parse(data);
                console.log('status: ', status); // debug
                location.reload();
            });
        });
        jQuery('#use_ocr_db_filter').tooltip({
            items: "[name]",
            content: '<?php echo $lang['ocr_db_filter_help'] ?>'
        });
        jQuery('#ocr_max_density').tooltip({
            items: "[name]",
            content: '<?php echo $lang['ocr_max_density_help'] ?>'
        });
        jQuery('#ocr_min_geometry').tooltip({
            items: "[name]",
            content: '<?php echo $lang['ocr_min_geometry_help'] ?>'
        });
        jQuery('#ocr_max_geometry').tooltip({
            items: "[name]",
            content: '<?php echo $lang['ocr_max_geometry_help'] ?>'
        });
        jQuery('#ocr_cronjob_enabled').tooltip({
            items: "[name]",
            content: '<?php echo $lang['ocr_cronjob_help'] ?>'
        });
        jQuery('#im_processing_headerDIV').tooltip({
            items: "[id]",
            content: '<?php echo $lang['im_processing_help'] ?>'
        });
        jQuery('#im_preset_1_density').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_density_help'] ?>'
        });
        jQuery('#im_preset_1_geometry').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_geometry_help'] ?>'
        });
        jQuery('#im_preset_1_quality').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_quality_help'] ?>'
        });
        jQuery('#im_preset_1_deskew').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_deskew_help'] ?>'
        });
        jQuery('#im_preset_1_sharpen_r').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_sharpen_r_help'] ?>'
        });
        jQuery('#im_preset_1_sharpen_s').tooltip({
            items: "[name]",
            content: '<?php echo $lang['im_preset_sharpen_s_help'] ?>'
        });
    }); 
</script> <?php

?>
<script src="../lib/alphanum/jquery.alphanum.js"></script>
<script>
    jQuery("#ocr_min_density, \n\
            #ocr_max_density, \n\
            #ocr_min_geometry, \n\
            #ocr_max_geometry, \n\
            #im_preset_1_density,\n\
            #im_preset_1_geometry,\n\
            #im_preset_1_quality,\n\
            #im_preset_1_deskew,\n\
            #im_preset_1_sharpen_r,\n\
            #im_preset_1_sharpen_s").numeric("ocrstreamSetupNum");
</script>
