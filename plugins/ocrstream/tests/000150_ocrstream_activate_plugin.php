<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

return activate_plugin('ocrstream');
