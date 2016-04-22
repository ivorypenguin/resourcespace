function setLanguage_1(selectedLanguage) {
    var ocr_lang = selectedLanguage;
    return ocr_lang;
};
function setPsm_1(selectedPSM) {
    var ocr_psm = selectedPSM;
    return ocr_psm;
};
function setOCRStart() {
    var ocr_start = jQuery('#ocr_upload_start').attr('checked');
    if (ocr_start === 'checked') {
        jQuery('#ocr_cron').fadeOut(200);
    } else {
        jQuery('#ocr_cron').fadeIn(200);
    }
    return ocr_start;
};

function setOCRCron() {
    var ocr_cron = jQuery('#ocr_cron_start').attr('checked');
    if (ocr_cron === 'checked') {
        jQuery('#ocr_start').fadeOut(200);
        jQuery('#ocr_language_select').fadeOut(200);
        jQuery('#ocr_psm_select').fadeOut(200);
    } else {
        jQuery('#ocr_start').fadeIn(200);
        jQuery('#ocr_language_select').fadeIn(200);
        jQuery('#ocr_psm_select').fadeIn(200);
    }
    return ocr_cron;
};

var ocr_lang = jQuery('#ocr_lang :selected').text();
var ocr_psm = jQuery('#ocr_psm :selected').val();
var ocr_start = jQuery('#ocr_upload_start').attr('checked');
