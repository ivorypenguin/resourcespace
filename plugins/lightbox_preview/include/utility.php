<?php

function getPreviewURLForType($resource, $type)
	{
	global $alternative, $use_watermark;

	$path = get_resource_path($resource['ref'], true, $type, false, $resource["preview_extension"],
			-1, 1, $use_watermark, "", $alternative);
	if (!file_exists($path))
		return false;

	return get_resource_path($resource['ref'], false, $type, false, $resource["preview_extension"],
			-1, 1, $use_watermark, "", $alternative);
	}

function getPreviewURL($resource)
	{
	if ($resource['has_image'] != 1)
		return false;

	// Try 'pre' first
	$url = getPreviewURLForType($resource, 'scr');
	if ($url == false)
		{
		// and then 'pre'
		$url = getPreviewURLForType($resource, 'pre');
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
						containerResizeSpeed: 250,
						txtImage: '<?php echo $lang["lightbox-image"]?>',
						txtOf: '<?php echo $lang["lightbox-of"]?>'
					});
		});
		</script>
	<?php
	}

function setLink($selector, $url, $title)
	{
	?>
		<script>
		jQuery(document).ready(function() {
			jQuery('<?php echo $selector ?>')
					.attr('href', '<?php echo $url ?>')
					.attr('title', '<?php echo htmlspecialchars(i18n_get_translated($title)) ?>')
					.attr('rel', 'lightbox')
		});
		</script>
	<?php
	}

function addLightBoxToLink($selector, $url, $title)
	{
	setLink($selector, $url, $title);
	addLightBox($selector);
	}

?>
