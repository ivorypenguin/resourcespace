<?php

/**
 * Returns the default output file format to use given an optional input format.
 */
function getDefaultOutputFormat($inputFormat = null)
	{
	global $format_chooser_default_output_format, $format_chooser_output_formats;

	if (!empty($format_chooser_default_output_format))
		return $format_chooser_default_output_format;

	$inputFormat = strtoupper($inputFormat);

	# Use resource format by default if none given
	if (empty($inputFormat) || !in_array($inputFormat, $format_chooser_output_formats))
		return $format_chooser_output_formats[0];

	return $inputFormat;
	}

/**
 * Returns the filename to be used for a specific file.
 * @param type $ref The resource for which the name should be built.
 * @param type $ext The new filename suffix to be used.
 * @param type $size A short name for the target file format, for example 'hpr'.
 */
function getTargetFilename($ref, $ext, $size)
	{
	global $filename_field, $view_title_field;

	# Get filename - first try title, then original filename, and finally use the resource ID
	$filename = get_data_by_field($ref, $view_title_field);
	if (empty($filename))
		{
		$filename = get_data_by_field($ref, $filename_field);
		if (!empty($filename))
			{
			$originalSuffix = pathinfo($filename, PATHINFO_EXTENSION);
			$filename = mb_basename($filename, $originalSuffix);
			}
		else
			$filename = strval($ref);
		}

	# Remove potentially problematic characters, and make sure it's not too long
	$filename = preg_replace("/[*:<>?\\/|]/", '_', $filename);
	$filename = substr($filename, 0, 240);

	return $filename . (empty($size) ? '' : '-' . strtolower($size)) . '.'
			. strtolower($ext);
	}

/**
 * Converts the file of the given resource to the new target file with the specified size. The
 * target file format is determined from the suffix of the target file.
 * The original colorspace of the image is retained.
 */
function convertImage($resource, $page, $alternative, $target, $width, $height)
	{
	$command = get_utility_path("im-convert");
	if (!$command)
		die("Could not find ImageMagick 'convert' utility.");

	$originalPath = get_resource_path($resource['ref'], true, '', false,
			$resource['file_extension'], -1, $page, false, '', $alternative);

	$command .= " \"$originalPath\"[0] -auto-orient";

	# Apply resize ('>' means: never enlarge)
	$command .= " -resize $width";
	if ($height > 0)
		$command .= "x$height";
	$command .= '>';

	$command .= " \"$target\"";

	run_command($command);
	}

function sendFile($filename)
	{
	$suffix = pathinfo($filename, PATHINFO_EXTENSION);
	$size = filesize_unlimited($filename);

	header('Content-Transfer-Encoding: binary');
	header('Content-Disposition: attachment; filename="' . mb_basename($filename) . '"');
	header('Content-Type: ' . get_mime_type($filename, $suffix));
	header('Content-Length: ' . $size);
	header("Content-Type: application/octet-stream");

	ob_end_flush();

	readfile($filename);
	}

?>
