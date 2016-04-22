function showLoadingImage() {
    jQuery('#ocr_status_anim').show(400);
}
;
function hideLoadingImage() {
    jQuery('#ocr_status_anim').hide(800);
}
;
function ocrUpload () {
    jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_1.php', {ref: resourceId, ocr_lang: ocr_lang, param_1: 'none'}, function (data) {
        stageOneOutput = JSON.parse(data);
        console.log(stageOneOutput[1]); // debug
        if (stageOneOutput.hasOwnProperty("error")) {
            hideLoadingImage();
            alert(stageOneOutput["error"]);
            return;
        }
        jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_2.php', {ref: stageOneOutput[0], ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: 'none', w: 0, h: 0, x: 0, y: 0}, function (data) {
            stageTwoOutput = JSON.parse(data);
            console.log(stageTwoOutput[1]); // debug
            if (stageTwoOutput.hasOwnProperty("error")) {
                hideLoadingImage();
                alert(stageTwoOutput["error"]);
                return;
            }
            jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_3.php', {ref: stageTwoOutput[0], ocr_lang: ocr_lang, ocr_psm: ocr_psm, param_1: 'none'}, function (data) {
                stageThreeOutput = JSON.parse(data);
                console.log(stageThreeOutput[1]); // debug
                if (stageThreeOutput.hasOwnProperty("error")) {
                    thideLoadingImage();
                    alert(stageThreeOutput["error"]);
                    return;
                }
                jQuery.get(baseUrl + '/plugins/ocrstream/include/stage_4.php', {ref: stageThreeOutput[0]}, function (data) {
                    stageFourOutput = JSON.parse(data);
                    console.log(stageFourOutput[1]); // debug
                    if (stageFourOutput.hasOwnProperty("error")) {
                        hideLoadingImage();
                        alert(stageFourOutput["error"]);
                        return;
                    }
                    if ((stageFourOutput[0]) === true) {
                        hideLoadingImage();
                    }
                });
            });
        });
        showLoadingImage();
    });
};