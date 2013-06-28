<?php

include_once dirname(__FILE__) . "/../include/utility.php";

function HookFancybox_previewAllAdditionalheaderjs()
	{
	global $baseurl_short, $css_reload_key;
	echo '<script src="' . $baseurl_short . 'lib/lightbox/js/jquery.lightbox-0.5.min.js" type="text/javascript" />';
	echo '<link type="text/css" href="' . $baseurl_short . 'lib/lightbox/css/jquery.lightbox-0.5.css?css_reload_key=' . $css_reload_key . '" rel="stylesheet">';
	}

?>
