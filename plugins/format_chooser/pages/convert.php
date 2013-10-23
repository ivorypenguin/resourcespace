<?php

include '../../../include/db.php';
include '../../../include/authenticate.php';
include '../../../include/general.php';
include '../../../include/resource_functions.php';

include_once dirname(__FILE__) . "/../include/utility.php";

$ref = getvalescaped('ref', 0, true);
$size = getvalescaped('size', '');
$page = getvalescaped('page', 0, true);
$alternative = getvalescaped('alt', 0, true);

$resource = get_resource_data($ref);

if (!resource_download_allowed($ref, $size, $resource["resource_type"]))
	{
	# This download is not allowed.
	exit("Permission denied");
	}

$width = getvalescaped('width', 0, true);
$height = getvalescaped('height', 0, true);

if ($width == 0 && $height == 0)
	{
	$results = sql_query("select width, height from preview_size where id='" . escape_check($size) . "'");
	if (empty($results))
		die('Unknown size');
	$width = (int)$results[0]['width'];
	$height = (int)$results[0]['height'];
	}

$ext = getvalescaped('ext', getDefaultOutputFormat());

$baseDirectory = get_temp_dir() . '/format_chooser';
@mkdir($baseDirectory);

$target = $baseDirectory . '/' . getTargetFilename($ref, $ext, $size);

set_time_limit(0);

convertImage($resource, $page, $alternative, $target, $width, $height);
sendFile($target);
unlink($target);

?>
