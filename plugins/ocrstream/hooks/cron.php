<?php
require_once (dirname(__FILE__) . "../../include/ocrstream_functions.php");
require_once (dirname(__FILE__) . "../../vendor/autoload.php");

function HookOcrstreamCronAddplugincronjob() {
    global $ocr_cronjob_enabled;
    global $ocr_min_density;
    global $ocr_max_density;
    global $ocr_min_geometry;
    global $ocr_max_geometry;
    global $ocr_allowed_extensions;
    global $ocr_global_language;
    global $ocr_psm_global;
    global $use_ocr_db_filter;
    global $ocr_db_filter_1;
    global $ocr_db_filter_2;
    global $ocr_ftype_1;
    global $ocr_rtype;
    global $lang;
    if ($ocr_cronjob_enabled == true) {        
        echo PHP_EOL . "OCRStream Cronjob" . PHP_EOL;
        $start_ocron_total = microtime(true);
        $ocr_flagged = get_ocronjob_resources();
        $n = count($ocr_flagged);
        $i = 0;
        if ($n === 0) {
            exit ('No resources in queue' . PHP_EOL);
        }
        foreach($ocr_flagged as $ID) {
            $start_ocron_resource = microtime(true);
            $ext = get_file_extension($ID);
            $resource_path = get_resource_path($ID, true, "", false, $ext);
            $im_preset_1_crop_w = 0;
            $im_preset_1_crop_h = 0;
            $im_preset_1_crop_x = 0;
            $im_preset_1_crop_y = 0;
            $param_1 = 'none';
            $error = ''; 
            // Check file extension
            if (!in_array($ext, $ocr_allowed_extensions)){
                $error .= ($lang['ocr_error_2']. PHP_EOL);
            }
            // Check resource type
            if (get_res_type ($ID) != $ocr_rtype){
                $error .= ($lang['ocr_error_4']. PHP_EOL);
            }
            // Check density and geometry for images
            if ($ext !== 'pdf' && in_array($ext, $ocr_allowed_extensions)) {
                $density_array = get_image_density ($resource_path);
                if ($density_array[1] == 'PixelsPerCentimeter') {
                    $density = $density_array[0] * 2.54;
                } else {
                    $density = $density_array[0];
                }
                if (intval($density) < $ocr_min_density && intval($density) !== 72) {
                    $error .= (['ocr_error_3']. PHP_EOL);
                }
                if (intval($density) > $ocr_max_density) {
                    $param_1 = 'pre_1';
                }
                $geometry = get_image_geometry ($ID);
                if (intval($geometry) < $ocr_min_geometry) {
                    $error .= ($lang['ocr_error_5']. PHP_EOL);
                }
                if (intval($geometry) > $ocr_max_geometry) {
                    $param_1 = 'pre_1';
                }
            }
            // Force image processing if filetype is pdf
            if ($ext === 'pdf') {
                $param_1 = 'pre_1';
            }
            // If checks returned errors skip processing for this resource
            if ($error !== '') {
                echo ("Resource ID: $ID" . ' '. $error);
            } else {
                $im_preset_1 = build_im_preset_1 ($im_preset_1_crop_w, $im_preset_1_crop_h, $im_preset_1_crop_x, $im_preset_1_crop_y);
                $ocr_temp_dir = get_ocr_temp_dir();
                // Image pre-processing with preset 1
                if ($param_1 === 'pre_1') {
                    ocr_image_processing ($ID, $im_preset_1, $ocr_temp_dir);
                    // If image creation failed then abort all 
                    if (!file_exists($ocr_temp_dir . '/im_tempfile_' . $ID . '.jpg') && !file_exists($ocr_temp_dir . '/im_tempfile_' . $ID . '-0.jpg')) {
                        exit("Resource ID: $ID".' '.$lang['ocr_error_6']. PHP_EOL);
                    }
                }
                $resource = get_resource_data($ID);
                $pg_num = get_page_count($resource, -1);
                $ocr_lang = trim($ocr_global_language);
                $ocr_psm = trim($ocr_psm_global);
                // OCR multi pages, processed, tesseract > v3.0.3
                if ($pg_num > 1) {
                    $mode = 'multipage';
                }
                // OCR single page processed
                elseif ($param_1 === 'pre_1' && $pg_num === '1') {
                    $mode = 'single_processed';
                }
                // OCR single page original
                elseif ($param_1 === 'none') {
                    $mode = 'single_original';
                }
                tesseract_processing($ID, $ocr_lang , $ocr_psm, $ocr_temp_dir, $mode, $pg_num);
                $ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ID . '.txt';
                if (!file_exists($ocr_output_file)) {
                    exit("Resource ID: $ID".' '.$lang['ocr_error_7']. PHP_EOL);
                }
                $tess_content = trim(file_get_contents($ocr_output_file));
                set_time_limit(1800);
                if ($use_ocr_db_filter == true) {
                // Filter extracted content
                    $filter1 = preg_replace($ocr_db_filter_1, '$1', $tess_content);
                    $tess_content = preg_replace($ocr_db_filter_2, '$1', $filter1);
                    update_field($ID, $ocr_ftype_1, $tess_content);
                } else {
                    update_field($ID, $ocr_ftype_1, $tess_content);
                }
                update_xml_metadump($ID);
                $ocr_state = 2;
                set_ocr_state($ID, $ocr_state);
                set_ocronjob($ID, $ocr_state);
                // Delete temp files
                array_map('unlink', glob("$ocr_temp_dir/ocr_output_file_$ID.txt"));
                array_map('unlink', glob("$ocr_temp_dir/ocrtempfile_$ID.txt"));
                array_map('unlink', glob("$ocr_temp_dir/im_tempfile_$ID*.*"));
                $elapsed_ocron_resource = round((microtime(true) - $start_ocron_resource), 3);
                echo "Resource ID $ID: OK (Time: $elapsed_ocron_resource)" . PHP_EOL;
                $i++;
            }
        }
        $elapsed_ocron_total = round((microtime(true) - $start_ocron_total), 3);
        echo "OCR Processing successful for $i from $n queued resources. Total time $elapsed_ocron_total (s)." . PHP_EOL;
    }        
}