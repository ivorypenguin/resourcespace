<?php

include_once dirname(__FILE__) . "/../include/utility.php";

function HookFormat_chooserCollection_downloadReplaceuseoriginal()
	{
	global $format_chooser_output_formats, $format_chooser_profiles, $lang, $use_zip_extension;

	$disabled = '';
	$submitted = getvalescaped('submitted', null);
	if (!empty($submitted))
		$disabled = ' disabled="disabled"';

	# Replace the existing ajax_download() with our own that disables our widgets, too
	if ($use_zip_extension)
		{
		?><script>
			var originalDownloadFunction = ajax_download;
			ajax_download = function() {
				originalDownloadFunction();
				jQuery('#downloadformat').attr('disabled', 'disabled');
				jQuery('#profile').attr('disabled', 'disabled');
			}
		</script><?php
		}

	?><div class="Question">
	<input type=hidden name="useoriginal" value="yes" />
	<label for="downloadformat"><?php echo $lang["downloadformat"]?></label>
	<select name="ext" class="stdwidth" id="downloadformat"<?php echo $disabled ?>>
		<option value="" selected="selected"><?php echo $lang['format_chooser_keep_format'] ?></option>
	<?php
	foreach ($format_chooser_output_formats as $format)
		{
		?><option value="<?php echo $format ?>"><?php echo str_replace_formatted_placeholder("%extension", $format, $lang["field-fileextension"]) ?></option><?php
		}
	?></select>
	<div class="clearerleft"> </div></div><?php
	if (!empty($format_chooser_profiles))
		{
		?>
		<div class="Question">
		<label for="profile"><?php echo $lang['format_chooser_choose_profile']?></label>
		<?php showProfileChooser('stdwidth') ?>
		<div class="clearerleft"> </div></div><?php
		}
	return true;
	}

function HookFormat_chooserCollection_downloadSize_is_available($resource, $path, $size)
	{
	if (!supportsInputFormat($resource['file_extension']))
		{
		# Let the caller decide whether the file is available
		return false;
		}

	$sizes = get_all_image_sizes();

	# Filter out the largest one
	$maxSize = null;
	$maxWidth = 0;
	for ($n = 0; $n < count($sizes); $n++)
		{
		if ($maxWidth < (int)$sizes[$n]['width'])
			{
			$maxWidth = (int)$sizes[$n]['width'];
			$maxSize = $sizes[$n]['id'];
			}
		}
	return $size!=$maxSize;
	}

function HookFormat_chooserCollection_downloadReplacedownloadextension($resource, $extension)
	{
	global $format_chooser_output_formats;

	$inputFormat = $resource['file_extension'];

	if (!supportsInputFormat($inputFormat))
		{
		# Download the original file for this resource
		return $inputFormat;
		}

	$ext = strtoupper(getvalescaped('ext', getDefaultOutputFormat($inputFormat)));
	if (empty($ext) || !in_array($ext, $format_chooser_output_formats))
		return false;

	return strtolower($ext);
	}

function HookFormat_chooserCollection_downloadReplacedownloadfile($resource, $size, $ext,
		$fileExists)
	{
	if (!supportsInputFormat($resource['file_extension']))
		{
		# Do not replace files we do not support
		return false;
		}

	$profile = getProfileFileName(getvalescaped('profile', null));
	if ($profile === null && $fileExists)
		{
		# Just serve the original file
		return false;
		}

	$baseDirectory = get_temp_dir() . '/format_chooser';
	@mkdir($baseDirectory);

	$target = $baseDirectory . '/' . getTargetFilename($resource['ref'], $ext, $size);

	$format = getImageFormat($size);
	$width = (int)$format['width'];
	$height = (int)$format['height'];

	set_time_limit(0);
	convertImage($resource, 1, -1, $target, $width, $height, $profile);
	return $target;
	}

?>
