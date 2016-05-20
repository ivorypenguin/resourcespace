<?php
#  Stage 1 - The Checker

$start_1 = microtime(true);

require_once "../../../include/db.php";
require_once "../../../include/authenticate.php";
require_once "../../../include/general.php";
require_once "../../../include/resource_functions.php";
require_once "../include/ocrstream_functions.php";

$ID = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT);
$param_1 = filter_input(INPUT_GET, 'param_1');

if (is_session_started() === false)
    {
    session_start();
    }

# Check Resource ID
if (!is_resource_id_valid($ID))
    {
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_1'])));
    }

# Check if resource is locked
$resource_lock = is_resource_lock($ID);
if ($resource_lock[0])
    {
    $lock_time = round((time() - $resource_lock[1])/60, 1);
    exit(json_encode(array("error" => $lang["ocr_error_9"] . $lock_time . '(m)')));
    }

# Set ocr_state while processing and lock resource
set_ocr_state($ID, 2);
set_resource_lock($ID, false);

$ocr_temp_dir = get_ocr_temp_dir();
$_SESSION['ocr_temp_dir'] = $ocr_temp_dir;

# If any old tempfiles for this resource are present, delete them
array_map('unlink', glob("$ocr_temp_dir/*_$ID*"));

global $ocr_min_density;
global $ocr_max_density;
global $ocr_min_geometry;
global $ocr_max_geometry;
global $lang;

if (isset($_SESSION['ocr_lang']))
    {
    $ocr_lang = $_SESSION['ocr_lang'];
    }
else
    {
    $ocr_lang = filter_input(INPUT_GET, 'ocr_lang');
    }

$_SESSION["ocr_force_processing_" . $ID] = 0;
$_SESSION["ocr_force_language_" . $ID] = 0;
$_SESSION["ocr_stage_" . $ID] = 0;

# Get original file extension
$ext = get_file_extension($ID);
$_SESSION['ocr_file_extension_' . $ID] = $ext;

# Check if file extension is allowed for ocr processing
if (!in_array($ext, $ocr_allowed_extensions))
    {
    set_ocr_state($ID, 0);
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_2'])));
    }

# Check if resourcetype is allowed for ocr processing
if (get_res_type ($ID) != $ocr_rtype)
    {
    set_ocr_state($ID, 0);
    session_unset();
    exit(json_encode(array("error" => $lang['ocr_error_4'])));
    }

$resource_path = get_resource_path($ID, true, "", false, $ext); # get complete path to original file with extension
$_SESSION['ocr_resource_path_' . $ID] = $resource_path;

# Check if density (dpi) and geometry (px) is in margin for ocr processing,
# skip for pdf and images that will be processed
# Ignore 72 dpi values (Screen resolution)
if ($ext !== 'pdf' && $param_1 === 'none')
    {
    $density_array = get_image_density ($resource_path);
    if ($density_array[1] == 'PixelsPerCentimeter')
        {
        $density = $density_array[0] * 2.54;
        }
    else
        {
        $density = $density_array[0];
        }
    if (intval($density) < $ocr_min_density && intval($density) !== 72)
        {
        set_ocr_state($ID, 0);
        session_unset();
        exit(json_encode(array("error" => $lang['ocr_error_3'])));
        }
    if (intval($density) > $ocr_max_density)
        {
        $_SESSION["ocr_force_processing_" . $ID] = 1; # Force image procesing if density too high
        }
    $geometry = get_image_geometry ($ID);
    if (intval($geometry) < $ocr_min_geometry)
        {
        set_ocr_state($ID, 0);
        session_unset();
        exit(json_encode(array("error" => $lang['ocr_error_5'])));
        }
    if (intval($geometry) > $ocr_max_geometry)
        {
        $_SESSION["ocr_force_processing_" . $ID] = 1; # Force image procesing if width too high
        }
    }

# Check if pdf contains Fonts
# Force image processing if no Fonts are found
if ($ext === 'pdf')
    {
    $has_font = checkPDF($resource_path);
    if ($has_font === 1)
        {
        set_ocr_state($ID, 0);
        session_unset();
        exit(json_encode(array("error" => $lang['ocr_error_8'])));
        }
    else
        {
        $_SESSION["ocr_force_processing_" . $ID] = 1;
        }
    }

# If language parameter is not valid, choose global ocr language setting
$tesseract_languages = get_tesseract_languages();
if (array_search($ocr_lang, $tesseract_languages) == false)
    {
    $_SESSION["ocr_force_language_" . $ID] = 1;
    }

# Stage 1 completed
$_SESSION["ocr_stage_" . $ID] = 1;

# Measure execution time for stage 1
$elapsed_1 = round((microtime(true) - $start_1), 3);
$_SESSION["ocr_stage_1_time"] = $elapsed_1;

$debug = ('OCR Stage ' . $_SESSION["ocr_stage_" . $ID] . '/4 completed: ' .$ID. ' ext: ' .$ext . ' Time: ' . $elapsed_1);
echo json_encode(array($ID, $debug));
