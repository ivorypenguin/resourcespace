function ocr_crop() {
    jQuery(function ($) {
        jQuery.get(baseUrl + '/plugins/ocrstream/include/rest.php', {ref: resourceId, get_true_size: 1}, function (data) {
            trueSize = JSON.parse(data);
            if (trueSize.hasOwnProperty("error")) {
                console.log(trueSize["error"]); //debug
                return;
            }
            w_true = trueSize[0];
            h_true = trueSize[1];
            console.log('original temp image dimensions: ', w_true, h_true); //debug
            var jcrop_api;
            $('.ImageBorder').Jcrop({
                trueSize: [w_true, h_true],
                onChange: setCoords,
                onSelect: setCoords,
                onRelease: clearCoords
            }, function () {
                jcrop_api = this;
            });
            $('#coords').on('change', 'input', function (e) {
                var x1 = $('#x1').val(),
                    y1 = $('#y1').val();
                jcrop_api.setSelect([x1, y1]);
            });
        });
    });
}
function setCoords(c) {
    jQuery('#x1').val(Math.round(c.x));
    jQuery('#y1').val(Math.round(c.y));
    jQuery('#w').val(Math.round(c.w));
    jQuery('#h').val(Math.round(c.h));
    x = (Math.round(c.x));
    y = (Math.round(c.y));
    w = (Math.round(c.w));
    h = (Math.round(c.h));
};
function clearCoords() {
    jQuery('#coords input').val('');
};
function setLanguage(selectedLanguage) {
    ocr_lang = selectedLanguage;
    return ocr_lang;
};
function setPsm(selectedPSM) {
    ocr_psm = selectedPSM;
    return ocr_psm;
};
function setParams(selectedParam) {
    param_1 = selectedParam;
    if (param_1 === 'pre_1') {
        ocr_crop();
    }
    return param_1;
};

function setOCRCron(resourceId) {
    ocr_cron = jQuery('#ocr_cron_start').attr('checked');
    if (ocr_cron === 'checked') {
        ocr_state = '1';
        jQuery.get(baseUrl + '/plugins/ocrstream/include/rest.php', {ref: resourceId, ocr_state: ocr_state}, function (data) {
            var1 = data;
            console.log(var1); // debug
        });
    } else {
        ocr_state = '0';
        jQuery.get(baseUrl + '/plugins/ocrstream/include/rest.php', {ref: resourceId, ocr_state: ocr_state}, function (data) {
            var2 = data;
            console.log(var2); // debug
        });
    }
    console.log(ocr_cron);// debug
    return ocr_cron;
};

function showLoadingImage() {
    jQuery('#ocr_status_anim').show(400);
};
function hideLoadingImage() {
    jQuery('#ocr_status_anim').fadeOut(800);
};

jQuery(document).ready(function () {
    // Get ocr state of resource and set the cronjob checkbox
    jQuery.get(baseUrl + '/plugins/ocrstream/include/rest.php', {ref: resourceId, ocr_state_query: 1}, function (data) {
        ocr_state = JSON.parse(data);
        if (ocr_state === 1) {
            jQuery('#ocr_cron_start').prop('checked', true);
        }
        console.log('ocr_state: ', ocr_state); // debug
        return ocr_state;
    });
    ocr_lang = jQuery('#ocr_lang :selected').text();
    ocr_psm = jQuery('#ocr_psm :selected').val();
    param_1 = jQuery('#im_preset :selected').val();
    x = '0';
    y = '0';
    w = '0';
    h = '0';
    status = 'OCR Stage ';
    // Only show jCrop when image is going to be processed
    if (param_1 === 'pre_1') {
        ocr_crop();
    }
    // Send parameters to stage 1 - 4 for OCR processing
    jQuery('[name="ocr_start"]').on("click", ocrStart);
    function ocrStart (event) {
        jQuery( this ).off( event );
        showLoadingImage();
        console.log(status + '1/4 .'); // debug
        jQuery('#ocr_status_text').html(status + '1/4 .');
        jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_1.php', {ref: resourceId, ocr_lang: ocr_lang, param_1: param_1}, function (data) {
            stageOneOutput = JSON.parse(data);
            console.log(stageOneOutput[1]); // debug
            if (stageOneOutput.hasOwnProperty("error")) {
                hideLoadingImage();
                alert(stageOneOutput["error"]);
                return;
            }
            console.log(status + '2/4 ..'); // debug
            jQuery('#ocr_status_text').html(status + '2/4 ..');
            jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_2.php', {ref: resourceId, ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: param_1, w: w, h: h, x: x, y: y}, function (data) {
                stageTwoOutput = JSON.parse(data);
                console.log(stageTwoOutput[1]); // debug
                if (stageTwoOutput.hasOwnProperty("error")) {
                    hideLoadingImage();
                    alert(stageTwoOutput["error"]);
                    return;
                }
                console.log(status + '3/4 ...'); // debug
                jQuery('#ocr_status_text').html(status + '3/4 ...');
                jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_3.php', {ref: resourceId, ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: param_1}, function (data) {
                    stageThreeOutput = JSON.parse(data);
                    console.log(stageThreeOutput[1]); // debug
                    if (stageThreeOutput.hasOwnProperty("error")) {
                        hideLoadingImage();
                        alert(stageThreeOutput["error"]);
                        return;
                    }
                    console.log(status + '4/4 ....'); // debug
                    jQuery('#ocr_status_text').html(status + '4/4 ....');
                    jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_4.php', {ref: resourceId}, function (data) {
                        stageFourOutput = JSON.parse(data);
                        console.log(stageFourOutput[1]); // debug
                        if (stageFourOutput.hasOwnProperty("error")) {
                            hideLoadingImage();
                            alert(stageFourOutput["error"]);
                            return;
                        }
                        jQuery('#ocr_status_text').html(stageFourOutput[1]);
                        hideLoadingImage();
                        jQuery('[name="ocr_start"]').on("click", ocrStart);
                        jQuery('#field_' + fieldNr).html(stageFourOutput[2]);
                    });
                });
            });
        });
    };
});