<?php
#  Stage 3 - The Recognizer

$start_3 = microtime(true);

require_once "../../../include/db.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";
require_once "../vendor/autoload.php";

$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
$ocr_psm = filter_input(INPUT_GET, 'ocr_psm');
$param_1 = filter_input(INPUT_GET, 'param_1');

if (is_session_started() === false)
    {
    session_start();
    }

# If Stage 2 not completed, exit
if ($_SESSION["ocr_stage_" . $ID] !== 2)
    {
    set_ocr_state($ID, 0);
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_stage_2'])));
    }

# Get number of pages
$resource = get_resource_data($ID);
$pg_num = get_page_count($resource, -1);

$ocr_temp_dir = $_SESSION['ocr_temp_dir'];

# Check for language override flag
if ($_SESSION["ocr_force_language_" . $ID] === 1)
    {
    if ($ocr_global_language == '')
        {
        $ocr_lang = 'eng'; # Something went wrong so choose English (tesseract standard)
        }
    else
        {
        $ocr_lang = trim($ocr_global_language);
        }
    }

# Determine which mode to use
if ($_SESSION["retry_on_preview_" . $ID] === true || $force_on_preview)
    {
    $mode = 'ocr_on_preview';
    }
elseif ($pg_num > 1)
    {
    $mode = 'multipage';
    }
elseif (($param_1 === 'pre_1' && $pg_num === '1') || ($_SESSION["ocr_force_processing_" . $ID] === 1 && $pg_num === '1'))
    {
    $mode = 'single_processed';
    }
elseif ($param_1 === 'none' && $_SESSION["ocr_force_processing_" . $ID] !== 1)
    {
    $mode = 'single_original';
    }

# Do the OCR processing
tesseract_processing($ID, $ocr_lang , $ocr_psm, $ocr_temp_dir, $mode, $pg_num);

# Stage 3 complete
$_SESSION["ocr_stage_" . $ID] = 3;

# Measure execution time for stage 3
$elapsed_3 = round((microtime(true) - $start_3), 3);
$_SESSION["ocr_stage_3_time"] = $elapsed_3;

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' .$ID. ' lang: ' .$ocr_lang. ' PSM: '.$ocr_psm . ' Time: ' . $elapsed_3);

echo json_encode(array($ID, $debug));
