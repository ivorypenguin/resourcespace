<?php

/** Configuration file for ResourceSpace plugin "ocrstream"
 *
 * These are the default values. They can be overridden by using /plugins/ocrstream/pages/setup.php
 * which is invoked by choosing Team Centre > Manage Plugins and then clicking on Options for the
 * ocrstream plugin once it has been activated.
 */
$ocr_global_language = 'eng';
$tesseract_path = '/usr/bin';
$ocr_allowed_extensions = array('tif', 'tiff', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'pdf');
$ocr_rtype = 2;
$ocr_min_density = '300';
$ocr_max_density = '600';
$ocr_min_geometry = '1000';
$ocr_max_geometry = '2500';
$ocr_ftype_1 = 72;
$use_ocr_db_filter = false;
$im_preset_1_density = '300';
$im_preset_1_geometry = '1024';
$im_preset_1_quality = '90';
$im_preset_1_deskew = '40';
$im_preset_1_sharpen_r = '0';
$im_preset_1_sharpen_s = '1';
$ocr_keep_tempfiles = false;

/**
 * Additional configuration
 *
 * These default values can only be changed here and not via the ocrstream options page
 */
$ocr_im_ext = 'jpg';
$ocr_db_filter_1 = "/[^a-zA-Züäöß\\s]/miu";
$ocr_db_filter_2 = "/(^|\\s)[a-zÜüäÄöÖß0-9](\\s|$)/miu";
$ocr_psm_global = 3;
$ocr_psm_array = array(
//    0 => 'Orientation and script detection (OSD) only.',
    1 => '1 - Automatic page segmentation with OSD.',
    2 => '2 - Automatic page segmentation, but no OSD, or OCR',
    3 => '3 - Fully automatic page segmentation, but no OSD. (Default)',
    4 => '4 - Assume a single column of text of variable sizes.',
    5 => '5 - Assume a single uniform block of vertically aligned text.',
    6 => '6 - Assume a single uniform block of text.',
    7 => '7 - Treat the image as a single text line.',
    8 => '8 - Treat the image as a single word.',
    9 => '9 - Treat the image as a single word in a circle.',
    10 => '10 - Treat the image as a single character.');

## Specify stopword lists to be loaded from /ocrstream/include/stopwords
## Get more lists here https://code.google.com/p/stop-words/
$ocr_load_stopwords = array ('stop-words_german_1_de', 'stop-words_english_1_en');

## Treshold for [fx:mean] to determine if the temp image is black
$image_mean_treshold = 0.02;

## If the temp image is corrupted try ocr on the preview created by RS
$retry_on_preview = false;

## Option to force OCR processing on preview created by RS without additional image processing
$force_on_preview = false;

## Disabled for now, not properly implemented yet!
$ocr_cronjob_enabled = false;

## Max time for a resource to be locked (default: 10 minutes)
$ocr_locks_max_seconds = 60*10;
