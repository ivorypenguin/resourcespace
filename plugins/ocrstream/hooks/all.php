<?php

function HookOcrstreamAllAdditionalheaderjs()
    {
    global $baseurl;
    ?>
    <link rel="stylesheet" href="<?php echo $baseurl;?>/plugins/ocrstream/assets/css/ocrstream.css" type="text/css" />
    <script src="<?php echo $baseurl;?>/plugins/ocrstream/include/js/ocrstream.upload.js"></script>
    <?php
    }
