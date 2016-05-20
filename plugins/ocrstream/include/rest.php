<?php
require_once "../../../include/db.php";
include_once "../../../include/authenticate.php";
require_once "../../../include/general.php";

$ref_filter_options = ["options" =>['min_range' => 1]];
$ref = filter_input(INPUT_GET, 'ref', FILTER_VALIDATE_INT, $ref_filter_options);
$ocr_state_filter_options = ["options" =>['min_range' => 0, 'max_range' => 2]];
$ocr_state = filter_input(INPUT_GET, 'ocr_state', FILTER_VALIDATE_INT, $ocr_state_filter_options);
$ocr_state_query = filter_input(INPUT_GET, 'ocr_state_query', FILTER_VALIDATE_BOOLEAN);
$get_true_size = filter_input(INPUT_GET, 'get_true_size', FILTER_VALIDATE_BOOLEAN);
$purge_config = filter_input(INPUT_GET, 'purge_config', FILTER_VALIDATE_BOOLEAN);
$plugin_name = filter_input(INPUT_GET, 'name');

# Set ocr_state flag in database
if (isset($ref) && isset($ocr_state))
    {
    sql_query("UPDATE resource SET ocr_state =  '$ocr_state' WHERE ref = '$ref'");
    }

# Get ocr_state flag from database and return
# If empty return 0 (default)
if (isset($ref) && isset($ocr_state_query))
    {
    $ocr_db_state =  sql_value("SELECT ocr_state value FROM resource WHERE ref = '$ref'", '');
    $ocr_db_state === '' ? $ocr_state = 0 : $ocr_state = $ocr_db_state;
    echo json_encode(intval($ocr_state));
    }

# Return true size image dimensions for jCrop
if (isset($ref) && isset($get_true_size))
    {
    global $im_preset_1_geometry;
    $w_thumb = sql_value("SELECT thumb_width value FROM resource WHERE ref = '$ref'", '');
    $h_thumb = sql_value("SELECT thumb_height value FROM resource WHERE ref = '$ref'", '');
    if (!$w_thumb || !$h_thumb)
        {
        exit(json_encode(array("error" => 'No thumbnail found!')));
        }
    $ar = ($w_thumb / $h_thumb);
    $w = intval($im_preset_1_geometry);
    $h = intval($w / $ar);
    echo json_encode([$w, $h]);
    }

if (isset($purge_config) && $purge_config && isset($plugin_name))
    {
    purge_plugin_config($plugin_name);
    //echo json_encode('config purged');
    }
