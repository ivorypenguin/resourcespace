<?php
#  Stage 4 - The Texter

$start_4 = microtime(true);

require_once "../../../include/db.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);

if (is_session_started() === false) {
    session_start();
}

# If Stage 3 not completed, exit
if ($_SESSION["ocr_stage_" . $ID] !== 3) {
    set_ocr_state($ID, 0);
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_stage_3'])));
}

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];
$ocr_output_file = $ocr_temp_dir . '/ocr_output_file_' . $ID . '.txt';

# Check if tesseract produced any output file in Stage 3
if (!file_exists($ocr_output_file)) {
    set_ocr_state($ID, 0);
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_7'])));
}
$tess_content = trim(file_get_contents($ocr_output_file));

# Writing Keywords to database can take time so increase PHP timeout
set_time_limit(1800);

if ($use_ocr_db_filter == true) {
    # Filter extracted content using regular expressions by ocrstream
    $tess_content_f1 = preg_replace($ocr_db_filter_1, '$1', $tess_content);
    $tess_content_f2 = preg_replace($ocr_db_filter_2, '$1', $tess_content_f1);
    # Remove duplicate keywords
    $tess_content_array = preg_split('/\s+/', $tess_content_f2);
    $tess_content_array = array_keys(array_flip($tess_content_array));
    # Filter keywords using $stopwords array
    $stopwords = getStopWords();
    $tess_keywords = "";
    foreach ($tess_content_array as $value) {
        if (!in_array($value, $stopwords)) {
        $tess_keywords .= $value . " ";
        }
    }
    $tess_content = trim($tess_keywords);
}

update_field($ID, $ocr_ftype_1, $tess_content);

update_xml_metadump($ID);

# OCR processing finished for this resource (unlock)
set_ocr_state($ID, 3);
set_resource_lock($ID, true);

# Delete temp files
if (!$ocr_keep_tempfiles) {
    array_map('unlink', glob("$ocr_temp_dir/*_$ID*"));
}

# Stage 4 completed
$_SESSION['ocr_stage_' . $ID] = 4;

# Measure execution time for stage 4
$elapsed_4 = round((microtime(true) - $start_4), 3);
$_SESSION["ocr_stage_4_time"] = $elapsed_4;
$ocr_total_time = ($_SESSION["ocr_stage_1_time"]) + ($_SESSION["ocr_stage_2_time"]) + ($_SESSION["ocr_stage_3_time"]) + ($_SESSION["ocr_stage_4_time"]);
$_SESSION["ocr_total_time"] = $ocr_total_time;

# Check if there are files from upload not finished all stages yet 
$nextID = ($ID + 1);
if (isset($_SESSION['ocr_stage_' . $nextID])) {
    if (($_SESSION['ocr_stage_' . $nextID]) < 4) {
        $end_of_queue = false;
    } else {
        $end_of_queue = true;
    }
} else {
    $end_of_queue = true;
}

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' . $ID . ' Time: ' . $elapsed_4 . ' Total Time: ' . $ocr_total_time . ' End of Queue: ' . $end_of_queue);
echo json_encode(array($end_of_queue, $debug, $tess_content));

# Clear all SESSION Variables for single resource OCR or if all resources in queue are completed
if ($end_of_queue == true || !isset($_SESSION["ocr_start"])) {
    session_unset();
}