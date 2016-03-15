<?php

function getPreviewURLForType($resource, $type, $alternative = -1)
	{
	global $use_watermark;

	$path = get_resource_path($resource['ref'], true, $type, false, $resource["preview_extension"],
			-1, 1, $use_watermark, "", $alternative);
	if (!file_exists($path))
		return false;

	return get_resource_path($resource['ref'], false, $type, false, $resource["preview_extension"],
			-1, 1, $use_watermark, "", $alternative);
	}

function getPreviewURL($resource, $alternative = -1)
	{
	if ($resource['has_image'] != 1)
		return false;

	// Try 'pre' first
	$url = getPreviewURLForType($resource, 'scr', $alternative);
	if ($url == false)
		{
		// and then 'pre'
		$url = getPreviewURLForType($resource, 'pre', $alternative);
		}

	return $url;
	}

function addLightBox($selector)
	{
	global $baseurl_short, $lang;
	?>
		<script>
		jQuery(document).ready(function() {
			jQuery('<?php echo $selector ?>')
					.lightBox({
						imageLoading: '<?php echo $baseurl_short?>gfx/lightbox/loading.gif',
						imageBtnClose: '<?php echo $baseurl_short?>gfx/lightbox/close.gif',
						imageBtnPrev: '<?php echo $baseurl_short?>gfx/lightbox/previous.png',
						imageBtnNext: '<?php echo $baseurl_short?>gfx/lightbox/next.png',
						containerResizeSpeed: 250,
						txtImage: '<?php echo $lang["lightbox-image"]?>',
						txtOf: '<?php echo $lang["lightbox-of"]?>'
					});
		});
		</script>
	<?php
	}

function setLink($selector, $url, $title, $rel = 'lightbox')
	{
	?>
		<script>
		jQuery(document).ready(function() {
			jQuery('<?php echo $selector ?>')
					.attr('href', '<?php echo $url ?>')
					.attr('title', '<?php echo addslashes(htmlspecialchars(i18n_get_translated($title))) ?>')
					.attr('rel', '<?php echo $rel ?>')
					.attr('onmouseup', 'closeModalOnLightBoxEnable();');
		});
		</script>
	<?php
	}

function addLightBoxToLink($selector, $url, $title, $rel = 'lightbox')
	{
	setLink($selector, $url, $title, $rel);
	addLightBox($selector);
	}

?>
