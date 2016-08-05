<?php

$suppress_headers = true;

include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";

$path = $_SERVER['REDIRECT_URL'];
if (strpos($path, $baseurl_short) == 0)
	$path = '../' . substr($path, strlen($baseurl_short));
else
	$path = '..' . $path;

if (!is_readable($path))
	{
	http_response_code(404);
	exit;
	}

$ext = pathinfo($path, PATHINFO_EXTENSION);

if ($ext == 'php')
	{
	http_response_code(403);
	exit;
	}

$filename = basename($path);
$filename = substr($filename, 0, strpos($filename, '_')) . '.' . $ext;

header('Content-Transfer-Encoding: binary');
header('Content-Type: ' . get_mime_type($path, $ext));
header('Content-Length: ' . filesize($path));

ob_end_flush();
readfile($path);
