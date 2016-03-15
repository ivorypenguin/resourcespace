<?php
use Symfony\Component\Process\Process;

/**
 * Get temp dir for ocrstream, create one if none exists
 * 
 * @return string OCRStream plugin temp directory
 */
function get_ocr_temp_dir() {
    $temp_dir = get_temp_dir();
    if(!is_dir($temp_dir . "/ocrstream_plugin")){
        mkdir($temp_dir . "/ocrstream_plugin",0777);        
    }
    $ocr_temp_dir = $temp_dir . "/ocrstream_plugin";
    return ($ocr_temp_dir);
}

/**
 * Checks if OS is Windows
 * 
 * @return boolean
 */
function is_windows() {
    $os = php_uname('s');
    if (stristr($os, 'win')) {
        if (stristr($os, 'Darwin')) {
            $is_windows = false;
        } else {
            $is_windows = true;
        }
    } else {
        $is_windows = false;
    }
    return $is_windows;
}

/**
 * Get path to tesseract executable
 * 
 * @global string $tesseract_path Tesseract directory path
 * @return string
 */
function get_tesseract_fullpath() {
    global $tesseract_path;
    if (is_windows()) {
        $tesseract_fullpath = $tesseract_path . '\tesseract.exe';
    } else {
        $tesseract_fullpath = $tesseract_path . '/tesseract';
    }
    return $tesseract_fullpath;
}

/**
 * Checks if tesseract executable exists
 * 
 * @return boolean 
 */
function is_tesseract_installed() {
    $tesseract_fullpath = get_tesseract_fullpath();
    if (file_exists($tesseract_fullpath)) {
        $tesseract_installed = true;
    } else {
        $tesseract_installed = false;
    }
    return $tesseract_installed;
}

/**
 * Get tesseract and leptonica version information
 * 
 * @return array tesseract version, leptonica version, tesseract_is_old
 */
function get_tesseract_version() {
    // Check if version information is already in plugin_config
    $plugin_config = get_plugin_config('ocrstream');
    if (isset($plugin_config['tesseract_version']) && isset($plugin_config['leptonica_version'])) {
        // Read version from config and return array
        $tesseract_version = $plugin_config['tesseract_version'];
        $leptonica_version = $plugin_config['leptonica_version'];
    } else {
        $tesseract_fullpath = get_tesseract_fullpath();
        $tesseract_version_command = run_command(escapeshellarg($tesseract_fullpath) . ' -v', true);
        $tesseract_version_output = explode("\n", $tesseract_version_command);
        if (stristr($tesseract_version_output [0], 'libtiff.so.5')) { // Skipping error output in first line if libftiff/liblept version mismatch
            $tesseract_version = $tesseract_version_output[1];
            $leptonica_version = $tesseract_version_output[2];
        } else {
            $tesseract_version = $tesseract_version_output[0];
            $leptonica_version = $tesseract_version_output[1];
        }
        $version_regex = "/([0-9.]+)/mi"; 
        preg_match_all($version_regex, $tesseract_version, $tess_matches);
        preg_match_all($version_regex, $leptonica_version, $lept_matches);
        $tesseract_version = trim($tess_matches[0][0]);
        $leptonica_version = trim($lept_matches[0][0]);
        // Add version to plugin_config and write it back to db
        $plugin_config['tesseract_version'] = $tesseract_version;
        $plugin_config['leptonica_version'] = $leptonica_version;
        set_plugin_config('ocrstream', $plugin_config);
    }
    if (version_compare($tesseract_version, '3.03') >= 0) {
        $tesseract_version_is_old = false;
    } else {
        $tesseract_version_is_old = true;
    }       
    return array($tesseract_version, $leptonica_version, $tesseract_version_is_old);
}

/**
 * Get available languages for tesseract-ocr
 * 
 * Returns an array of languages that are currently installed into the /TESSDATA directory.
 * Language codes use ISO 639-2 standard.
 * 
 * @link https://code.google.com/p/tesseract-ocr/downloads/list tesseract language data downloads
 * @return array 
 */
function get_tesseract_languages() {
    // Check if languages are already in plugin_config
    $plugin_config = get_plugin_config('ocrstream');
    if (isset($plugin_config['tesseract_languages'])) {
        // Read languages from config and return array
        $tesseract_languages = $plugin_config['tesseract_languages'];
    } else {
        // Get languages via tesseract cli
        $tesseract_fullpath = get_tesseract_fullpath();
        $tesseract_language_command = run_command(escapeshellarg($tesseract_fullpath) . ' --list-langs', true);
        $tesseract_languages = explode("\n", $tesseract_language_command);
        if (stristr($tesseract_languages [0], 'libtiff.so.5')) { // Skipping additional line if libftiff version does not match liblept version
            array_shift($tesseract_languages);
            array_shift($tesseract_languages);
        } else {
            array_shift($tesseract_languages); // Skipping first line output ("Available languages...")
        }
        array_walk($tesseract_languages, 'trim_value');
        // Add langauges to plugin_config and write it back to db
        $plugin_config['tesseract_languages'] = $tesseract_languages;
        set_plugin_config('ocrstream', $plugin_config);        
    }
    return $tesseract_languages;
}

/**
 * Trim value, use for arrays
 * 
 * @param string $value
 */
function trim_value(&$value) {
    $value = trim($value);
}

/**
 * Check if PHP Session is started
 * 
 * @return boolean
 */
function is_session_started() {
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
    return true;
}

/**
 * Get the file extension from the original resource file
 * 
 * @param int $ref
 * @return string file extension
 */
function get_file_extension ($ref) {
    $ext = sql_value("select file_extension value from resource where ref = '$ref'", '');
    return $ext;
}

/**
 * Check if Resource ID is valid INTEGER and exists in database
 * 
 * @param int $ID
 * @return boolean
 */
function is_resource_id_valid ($ID) {
    if ($ID === null || $ID < 1 || $ID > sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')) {
    return false;
    } else {
    return true;
    }
}

/**
 * Get Image density and Units
 * 
 * @global string $imagemagick_path
 * @param string $resource_path
 * @return array Density value and units
 */
function get_image_density ($resource_path) {
    $convert_fullpath = get_utility_path("im-convert");
    $density_output = run_command("{$convert_fullpath} -format %y,%U {$resource_path} info:");
    $density_array = explode(",", $density_output);
    return $density_array;
}

/**
 * Get image geometry in pixels
 * 
 * @param int $ID Resource ID
 * @return int Image geometry (px)
 */
function get_image_geometry ($ID) {
    $geometry_array = sql_query("SELECT width, height FROM resource_dimensions WHERE resource ='$ID'", '');
    if ($geometry_array[0]['width'] < $geometry_array[0]['height']) {
        $geometry = $geometry_array[0]['width'];
    } else {
        $geometry = $geometry_array[0]['height'];
    }
    return $geometry;
}

/**
 * Get Resource Type
 * 
 * @param int $ID Resource ID
 * @return int Resource type
 */
function get_res_type ($ID) {
    $res_type = sql_value("select resource_type value from resource where ref ='$ID'", '');
    return $res_type;
}

/**
 * Set the OCR state flag for a Resource
 * 
 * ocr_state = 1 : file flagged for ocr processing
 * ocr_state = 3 : ocr on this file has been completed
 * 
 * @param int $ID Resource ID
 * @param int $ocr_state OCR state flag
 * @return string Error message
 */
function set_ocr_state($ID, $ocr_state) {
    $ID_filter_options = ["options" =>['min_range' => 1, 'max_range' => sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')]];
    $ID_filtered = filter_var($ID, FILTER_VALIDATE_INT, $ID_filter_options);
    $ocr_state_filter_options = ["options" =>['min_range' => 0, 'max_range' => 3]];
    $ocr_state_filtered = filter_var($ocr_state, FILTER_VALIDATE_INT, $ocr_state_filter_options);
    if (!$ID_filtered || (!$ocr_state_filtered && $ocr_state_filtered !== 0)) {
        $error_msg = "Error setting OCR state ($ocr_state) for Resource ID ($ID).";
        debug("OCRStream: $error_msg");
        return($error_msg);
    }
    sql_query("UPDATE resource SET ocr_state =  '$ocr_state_filtered' WHERE ref = '$ID_filtered'");
}


function get_ocr_state($ID) {
    $ocr_db_state = sql_value("SELECT ocr_state value FROM resource WHERE ref = '$ID'", '');
    $ocr_db_state === '' ? $ocr_state = 0 : $ocr_state = $ocr_db_state;
    return($ocr_state);
}

function is_resource_lock($ID) {
    global $ocr_locks_max_seconds;
    $resource_lock_time = sql_value("SELECT UNIX_TIMESTAMP(ocr_lock_date) value FROM resource WHERE ref = '$ID'", '');
    if ($resource_lock_time == '' || (time()-$resource_lock_time)>$ocr_locks_max_seconds) {
        return array(false, $resource_lock_time);        
    } else {
        return array(true, $resource_lock_time);
    }
}

function set_resource_lock($ID, $clear) {
    $ID_filter_options = ["options" => ['min_range' => 1, 'max_range' => sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')]];
    $ID_filtered = filter_var($ID, FILTER_VALIDATE_INT, $ID_filter_options);
    if (!$ID_filtered) {
        $error_msg = "Error setting resource lock date for Resource ID ($ID).";
        debug("OCRStream: $error_msg");
        return($error_msg);
    }
    if ($clear) {
        sql_query("UPDATE resource SET ocr_lock_date =  NULL WHERE ref = '$ID_filtered'");
    } else {
        $time = time();
        sql_query("UPDATE resource SET ocr_lock_date =  FROM_UNIXTIME($time) WHERE ref = '$ID_filtered'");
    }
}

/**
 * Build Preset for Imagemagick options
 * 
 * @global string $im_preset_1_density
 * @global string $im_preset_1_geometry
 * @global string $im_preset_1_quality
 * @global string $im_preset_1_deskew
 * @global string $im_preset_1_sharpen_r
 * @global string $im_preset_1_sharpen_s
 * @param string $im_preset_1_crop_w
 * @param string $im_preset_1_crop_h
 * @param string $im_preset_1_crop_x
 * @param string $im_preset_1_crop_y
 * @return array IM Options array
 */
function build_im_preset_1 ($im_preset_1_crop_w, $im_preset_1_crop_h, $im_preset_1_crop_x, $im_preset_1_crop_y) {
    global $im_preset_1_density, $im_preset_1_geometry, $im_preset_1_quality, $im_preset_1_deskew, $im_preset_1_sharpen_r, $im_preset_1_sharpen_s;
    $im_preset_1 = array(
    'colorspace' => ('-colorspace gray'),
    'type' => ('-type grayscale'),
    'density' => ("-density {$im_preset_1_density}"),
    'geometry' => ("-geometry {$im_preset_1_geometry}"),
    'crop' => ("-crop {$im_preset_1_crop_w}x{$im_preset_1_crop_h}+{$im_preset_1_crop_x}+{$im_preset_1_crop_y}"),
    'quality' => ("-quality {$im_preset_1_quality}"),
    'trim' => ('-trim'),
    'deskew' => ("-deskew {$im_preset_1_deskew}%"),
    'normalize' => ('-normalize'),
    'sharpen' => ("-sharpen {$im_preset_1_sharpen_r}x{$im_preset_1_sharpen_s}"),
    //'depth'     => ('-depth 8'),
    );
    return $im_preset_1;
}

/**
 * Image pre-processing for OCR
 * 
 * Using Symfony Process component for executing convert
 * 
 * @param int $ID Resource ID
 * @param array $im_preset Array of imagemagick options for image processing
 * @param string $ocr_temp_dir OCR temp directory
 */
function ocr_image_processing($ID, $im_preset, $ocr_temp_dir) {
    global $ocr_im_ext;
    set_time_limit(1800);
    $convert_fullpath = get_utility_path("im-convert");
    $ext = get_file_extension($ID);
    $resource_path = get_resource_path($ID, true, "", false, $ext);
    $im_ocr_cmd = ("{$convert_fullpath} " . implode(' ', $im_preset) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg("{$ocr_temp_dir}/im_tempfile_{$ID}-%04d.{$ocr_im_ext}"));
    debug("CLI command: $im_ocr_cmd");
    $process = new Process($im_ocr_cmd);
    $process->setTimeout(3600);
    $process->run();
    debug("CLI output: " . $process->getOutput());
    debug("CLI errors: " . trim($process->getErrorOutput()));
}

/**
 * Tesseract processing
 * 
 * Generates and execute tesseract command for given usecase
 * 
 * @param int $ID Resource ID
 * @param string $ocr_lang Language for OCR processing
 * @param int $ocr_psm Tesseract page segmentation mode
 * @param string $ocr_temp_dir Temp directory
 * @param string $mode Resource OCR mode
 * @param int $pg_num Number of pages 
 */
function tesseract_processing($ID, $ocr_lang, $ocr_psm, $ocr_temp_dir, $mode, $pg_num) {
    global $ocr_im_ext;
    $tesseract_fullpath = get_tesseract_fullpath();
    $tesseract_version_array = get_tesseract_version();
    // Case [1]: multiple pages and tesseract version is >= 3.03
    // Will create a text file (im_ocr_file_[n]) with a list of filepaths for tesseract to run in batch mode
    if ($mode === 'multipage' && $tesseract_version_array[2] === false) {
        $n = 0;
        set_time_limit(1800);
        while ($n < $pg_num) {
            file_put_contents("{$ocr_temp_dir}/im_ocr_file_{$ID}", trim("{$ocr_temp_dir}/im_tempfile_{$ID}-" . sprintf('%04d',$n) . ".{$ocr_im_ext}") . PHP_EOL, FILE_APPEND);
            $n++;
        }
        $tess_cmd = ("{$tesseract_fullpath} {$ocr_temp_dir}/im_ocr_file_{$ID} " . escapeshellarg("{$ocr_temp_dir}/ocr_output_file_{$ID}") . " -l {$ocr_lang} -psm {$ocr_psm}");
        run_tess_cmd($tess_cmd, $ID);
        // Case [2]: multiple pages and tesseract version is < 3.03
        // Tesseract will be initialized and run for each page  
    } elseif ($mode === 'multipage' && $tesseract_version_array[2] === true) {
        $i = 0;
        set_time_limit(1800);
        while ($i < $pg_num) {
            $ocr_input_file = ("{$ocr_temp_dir}/im_tempfile_{$ID}-" . sprintf('%04d',$i) . ".{$ocr_im_ext}");
            $tess_cmd = ("{$tesseract_fullpath} {$ocr_input_file} " . escapeshellarg("{$ocr_temp_dir}/ocrtempfile_{$ID}") . " -l {$ocr_lang} -psm {$ocr_psm}");
            run_tess_cmd($tess_cmd, $ID);
            file_put_contents("{$ocr_temp_dir}/ocr_output_file_{$ID}.txt", file_get_contents("{$ocr_temp_dir}/ocrtempfile_{$ID}.txt"), FILE_APPEND);
            $i ++;
        }
        // Case [3]: Single page (processed temp image) 
    } elseif ($mode === 'single_processed') {
        $ocr_input_file = ("{$ocr_temp_dir}/im_tempfile_{$ID}-0000.{$ocr_im_ext}");
        $tess_cmd = ("{$tesseract_fullpath} {$ocr_input_file} " . escapeshellarg("{$ocr_temp_dir}/ocr_output_file_{$ID}") . " -l {$ocr_lang} -psm {$ocr_psm}");
        run_tess_cmd($tess_cmd, $ID);
        // Case [4]: Single page (original resource)
    } elseif ($mode === 'single_original' || $mode === 'ocr_on_preview') {
        if ($mode === 'ocr_on_preview') {
            $ext = 'jpg';
        } else {
            $ext = get_file_extension($ID);
        }
        $resource_path = get_resource_path($ID, true, "", false, $ext);
        $tess_cmd = (escapeshellarg($tesseract_fullpath) . ' ' . escapeshellarg($resource_path) . ' ' . escapeshellarg("{$ocr_temp_dir}/ocr_output_file_{$ID}") . " -l {$ocr_lang} -psm {$ocr_psm}");
        run_tess_cmd($tess_cmd, $ID);
    }
}

/**
 * Run and log tesseract command using symfony process
 * 
 * @param string $tess_cmd tesseract command string to execute
 * @param string $ID Resource ID
 */
function run_tess_cmd($tess_cmd, $ID) {
    debug("CLI command: $tess_cmd");
    $process = new Process($tess_cmd);
    $process->setTimeout(3600);
    $process->setIdleTimeout(600);
    $process->start();
    $_SESSION["ocr_tess_pid_" . $ID] = $process->getPid();
    $process->wait();
    debug("CLI output: " . $process->getOutput());
    debug("CLI errors: " . trim($process->getErrorOutput()));
}

## @TODO: Remove unused function when cronjob is implemented
function set_ocronjob_field () {
    global $lang;
    $ocronjob_fieldname = 'ocronjob';
    $fieldnames = sql_array("SELECT name value FROM resource_type_field", '');
    // Check if field is already there, initialize if not
    if (in_array($ocronjob_fieldname, $fieldnames)) {
        return;
    } else {
        $last_field_number = sql_value("SELECT ref value FROM resource_type_field ORDER BY ref DESC LIMIT 1", '');
        $last_field_number++;
        $options = $lang['ocronjob_enabled'];
        sql_query("INSERT INTO resource_type_field "
                . "(ref, name, title, type, options, resource_type, display_field) "
                . "VALUES "
                . "($last_field_number, 'ocronjob', 'OCR Cronjob', '2', '$options', '2', '0')", '');
    }
}

function get_ocronjob_resources() {
    $ocronjob_field_array = sql_query("select ref,options from resource_type_field where name = 'ocronjob'", '');
    $ocronjob_field_array = $ocronjob_field_array[0];
    $ocr_flagged = sql_array("select resource value from resource_data where resource_type_field = $ocronjob_field_array[ref] and value = ',$ocronjob_field_array[options]'", '');
    return $ocr_flagged;
}

function set_ocronjob($ID, $ocr_state) {
    $ID_filter_options = ["options" =>['min_range' => 1, 'max_range' => sql_value("SELECT ref value FROM resource ORDER BY ref DESC LIMIT 1", '')]];
    $ID_filtered = filter_var($ID, FILTER_VALIDATE_INT, $ID_filter_options);
    $ocr_state_filter_options = ["options" =>['min_range' => 0, 'max_range' => 3]];
    $ocr_state_filtered = filter_var($ocr_state, FILTER_VALIDATE_INT, $ocr_state_filter_options);
    if (!$ID_filtered || !$ocr_state_filtered) {
        $error_msg = "Error setting cronjob: OCR state ($ocr_state) Resource ID ($ID).";
        debug("OCRStream: $error_msg");
        return($error_msg);
    }
    $ocronjob_field = sql_value("select ref value from resource_type_field where name = 'ocronjob'", '');
    if ($ocr_state === 2) {
        // Reset ocronjob field
        sql_query("UPDATE resource_data SET value =  '' WHERE resource = '$ID_filtered' AND resource_type_field = '$ocronjob_field'");
    } elseif ($ocr_state === 1) {
        // Set ocronjob field
        $ocronjob_options = sql_value("select options value from resource_type_field where name = 'ocronjob'", '');
        sql_query("UPDATE resource_data SET value =  ',$ocronjob_options' WHERE resource = '$ID_filtered' AND resource_type_field = '$ocronjob_field'");
    }
}

/**
 * Quick and dirty check for fonts in the first 1000 bytes of a pdf
 * Fonts most probably indicate a non-scanned pdf
 * 
 * @param string $filename
 * @return boolean 
 */
function checkPDF($filename) {
    $contents = file_get_contents($filename, NULL, NULL, 0, 1000);
    $has_font = preg_match("/Font/m", $contents);
    return $has_font;
}

function checkImage($filename) {
    global $image_mean_treshold;
    $convert_fullpath = get_utility_path("im-convert");
    $check_cmd = ($convert_fullpath . ' ' . escapeshellarg($filename) . ' -format "%[fx:mean]" info:');
    debug("CLI command: $check_cmd");
    $process = new Process($check_cmd);
    $process->run();
    debug("CLI output: " . $process->getOutput());
    debug("CLI errors: " . trim($process->getErrorOutput()));
    if ($process->getOutput() < $image_mean_treshold) {
        $is_black = true;
    } else {
        $is_black = false;
    }
    return $is_black;
}

/**
 * Get stopwords from files and return stopwords array
 * 
 * @global array $ocr_load_stopwords
 * @return array $stopwords 
 */
function getStopWords() {
    global $ocr_load_stopwords;
    $stopwords = array();
    foreach ($ocr_load_stopwords as $n) {
        $string = file_get_contents(__DIR__ . '/stopwords/' . $n . '.txt');
        $array = array_map('trim', explode(PHP_EOL, $string));
        $stopwords = array_merge($stopwords, $array);
    }
    return $stopwords;
}
