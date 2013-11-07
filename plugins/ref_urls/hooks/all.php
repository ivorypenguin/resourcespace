<?php

function HookRef_urlsAllGet_resource_path_override($ref, $getfilepath, $size, $generate,
		$extension, $scramble, $page, $watermarked, $file_modified, $alternative, $includemodified)
	{
	global $baseurl_short;

	if ($getfilepath)
		return false;

	$url = $baseurl_short . "plugins/ref_urls/file.php?ref=$ref&size=$size&ext=$extension";
	if ($page != 1)
		$url .= "&page=$page";
	if ($alternative != -1)
		$url .= "&alternative=$alternative";
	if ($watermarked)
		$url .= "&wm=$watermarked";

	return $url;
	}

?>
