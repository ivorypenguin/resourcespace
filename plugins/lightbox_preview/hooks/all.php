<?php

function HookLightbox_previewAllAdditionalheaderjs()
	{
	global $baseurl_short, $css_reload_key;
	echo '<script src="' . $baseurl_short . 'lib/lightbox/js/jquery.lightbox-0.5.min.js" type="text/javascript" ></script>';
	echo '<link type="text/css" href="' . $baseurl_short . 'lib/lightbox/css/jquery.lightbox-0.5.css?css_reload_key=' . $css_reload_key . '" rel="stylesheet" />';

	?>
	<script>
	function closeModalOnLightBoxEnable()
		{
		setTimeout(function() {
			if(jQuery('#jquery-lightbox').is(':visible'))
				{
				ModalClose();
				}
		}, 10);
		}
	</script>
	<?php
	}

?>
