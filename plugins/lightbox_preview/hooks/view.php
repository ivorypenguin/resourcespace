<?php

include dirname(__FILE__) . "/../include/utility.php";

function HookLightbox_previewViewRenderbeforerecorddownload()
	{
	global $resource, $title_field;

	$url = getPreviewURL($resource);
	if ($url === false)
		return;

	$title = get_data_by_field($resource['ref'], $title_field);
	setLink('#previewimagelink', $url, $title);
	setLink('#previewlink', $url, $title, 'lightbox-other');
	}

function HookLightbox_previewViewRenderaltthumb()
	{
	global $baseurl_short, $ref, $resource, $alt_thm, $altfiles, $n, $k, $search,
			$offset, $sort, $order_by, $archive;

	$url = getPreviewURL($resource, $altfiles[$n]['ref']);
	if ($url === false)
		return false;

	# Replace the link to add the 'altlink' ID
	?>
	<a id="altlink_<?php echo $n; ?>" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php
			echo urlencode($ref)?>&alternative=<?php echo $altfiles[$n]['ref']?>&k=<?php
			echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo
			urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo
			urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&<?php
			echo hook("previewextraurl") ?>">
		<img src="<?php echo $alt_thm; ?>" class="AltThumb">
	</a>
	<?php
	setLink('#altlink_' . $n, $url, $altfiles[$n]['name']);

	return true;
	}

function HookLightbox_previewViewRenderbeforeresourcedetails()
	{
	addLightBox('a[rel="lightbox"]');
	addLightBox('a[rel="lightbox-other"]');
	}

?>
