<?php

include dirname(__FILE__) . "/../include/utility.php";

function HookLightbox_previewViewAfterpermissionscheck()
	{
	addLightBoxHeader();
	}

function HookLightbox_previewViewRenderbeforerecorddownload()
	{
	global $resource, $title_field;

	$url = getPreviewURL($resource);
	if ($url === false)
		return;

	addLightBoxToLink('#previewimagelink', $url, get_data_by_field($resource['ref'], $title_field));
	addLightBoxToLink('#previewlink', $url, get_data_by_field($resource['ref'], $title_field));
	}

?>
