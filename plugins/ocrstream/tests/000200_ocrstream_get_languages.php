<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}
include_once('../plugins/ocrstream/include/ocrstream_functions.php');

$langs = get_tesseract_languages();

return(in_array('eng', $langs));
