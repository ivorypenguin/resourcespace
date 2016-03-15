<?php

$suppress_headers = true;

include "../../include/db.php";
include_once "../../include/general.php";

$size = getvalescaped('size', '');
$ref = getvalescaped('ref', 0, true);
$extension = getvalescaped('ext', 'jpg');
$k = getvalescaped('k', '');

# Check if we need to authorize this access
if (!empty($k))
	{
	# External access - check how this was shared.
	$externalAccess = sql_value("select access value from external_access_keys where resource="
			. $ref . " and access_key='" . escape_check($k) . "'", -1);
	if ($externalAccess != 0)
		{
		http_response_code(403);
		exit;
		}
	}
else if (!is_array($unauthenticated_image_sizes) || !in_array($size, $unauthenticated_image_sizes))
	include "../../include/authenticate.php";

if ($ref == 0 || $extension == 'php')
	{
	http_response_code(403);
	exit;
	}

$alternative = getvalescaped('alternative', -1, true);
$watermark = getvalescaped('watermark', false);
$page = getvalescaped('page', 1, true);

$path = get_resource_path($ref, true, $size, false, $extension, -1, $page, $watermark, "",
		$alternative, true);

if (!is_readable($path))
	{
	http_response_code(404);
	exit;
	}

$filename = basename($path);
$filename = substr($filename, 0, strpos($filename, '_')) . '.' . $extension;

header('Content-Transfer-Encoding: binary');
header('Content-Type: ' . get_mime_type($path, $extension));
header('Content-Length: ' . filesize($path));

ob_end_flush();
readfile($path);
