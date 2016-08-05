<?php

include_once dirname(__FILE__) . "/../include/utility.php";

function HookFormat_chooserAllGetdownloadurl($ref, $size, $ext, $page = 1, $alternative = -1)
	{
	global $baseurl_short;

	$path = get_resource_path($ref, true, $size, false, $ext, -1, $page,
			$size=="scr" && checkperm("w") && $alternative==-1, '', $alternative);
	if (file_exists($path))
		return false;

	return $baseurl_short . 'plugins/format_chooser/pages/convert.php?ref=' . $ref . '&size='
			. $size . '&ext=' . $ext . '&page=' . $page . '&alt=' . $alternative;
	}

?>
