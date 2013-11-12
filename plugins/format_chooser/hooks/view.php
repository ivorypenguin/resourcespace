<?php

function show_table_headers($showprice)
	{
	global $lang;
	?><tr><td><?php echo $lang["fileinformation"]?></td>
	<td><?php echo $lang["filetype"]?></td>
	<?php if ($showprice) { ?><td><?php echo $lang["price"] ?></td><?php } ?>
	<td class="textcenter"><?php echo $lang["options"]?></td>
	</tr>
	<?php
	}

function HookFormat_chooserViewReplacedownloadoptions()
	{
	global $resource, $ref, $counter, $headline, $lang, $download_multisize, $showprice, $save_as,
			$direct_link_previews, $hide_restricted_download_sizes, $format_chooser_output_formats,
			$baseurl_short, $search, $offset, $k, $order_by, $sort, $archive;

	$inputFormat = $resource['file_extension'];

	if ($resource["has_image"] != 1 || !$download_multisize || $save_as
			|| !supportsInputFormat($inputFormat))
		return false;

	$defaultFormat = getDefaultOutputFormat($inputFormat);
	$tableHeadersDrawn = false;

	?><table cellpadding="0" cellspacing="0"><?php

	$sizes = get_image_sizes($ref, false, $resource['file_extension'], false);
	$downloadCount = 0;
	$originalSize = -1;

	# Show original file download
	for ($n = 0; $n < count($sizes); $n++)
		{
		$downloadthissize = resource_download_allowed($ref, $sizes[$n]["id"], $resource["resource_type"]);
		if ($downloadthissize)
			$downloadCount++;
		$counter++;

		if ($sizes[$n]['id'] != '')
			continue;

		# Is this the original file? Set that the user can download the original file
		# so the request box does not appear.
		$fulldownload = false;
		if ($sizes[$n]["id"] == "")
			$fulldownload = true;

		$originalSize = $sizes[$n];

		$headline = $lang['collection_download_original'];
		if ($direct_link_previews && $downloadthissize)
			$headline = make_download_preview_link($ref, $sizes[$n]);
		if ($hide_restricted_download_sizes && !$downloadthissize && !checkperm("q"))
			continue;

		if (!$tableHeadersDrawn)
			{
			show_table_headers($showprice);
			$tableHeadersDrawn = true;
			}

		?><tr class="DownloadDBlend" id="DownloadBox<?php echo $n?>">
		<td><h2><?php echo $headline?></h2><p><?php
		echo $sizes[$n]["filesize"];
		if (is_numeric($sizes[$n]["width"]))
			echo preg_replace('/^<p>/', ', ', get_size_info($sizes[$n]), 1);

		?></p><td><?php echo str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["field-fileextension"]) ?></td><?php

		if ($showprice)
			{
			?><td><?php echo get_display_price($ref, $sizes[$n]) ?></td><?php
			}
		add_download_column($ref, $sizes[$n], $downloadthissize);
		}

	# Add drop down for all other sizes
	$maxSize = 0;
	if ($downloadCount > 1)
		{
		if (!$tableHeadersDrawn)
			show_table_headers($showprice);

		?><tr class="DownloadDBlend">
		<td><select id="size"><?php

		$sizes = get_all_image_sizes();

		# Filter out all sizes that are larger than our image size, but not the largest one
		for ($n = 0; $n < count($sizes); $n++)
			{
			if ($maxSize < (int)$sizes[$n]['width'])
				$maxSize = (int)$sizes[$n]['width'];
			}
		for ($n = 0; $n < count($sizes); $n++)
			{
			if ($sizes[$n]['width'] != $maxSize && $sizes[$n]['width'] > $originalSize['width'])
				unset($sizes[$n]);
			}
		foreach ($sizes as $n => $size)
			{
			# Only add choice if allowed
			$downloadthissize = resource_download_allowed($ref, $size["id"], $resource["resource_type"]);
			if (!$downloadthissize)
				continue;

			$name = $size['name'];
			if ($size['width'] == $maxSize)
				$name = $lang['format_chooser_original_size'];
			?><option value="<?php echo $n ?>"><?php echo $name ?></option><?php
			}
		?></select><p id="sizeInfo"></p></td><?php
		if ($showprice)
			{
			?><td>-</td><?php
			}
		?><td style="vertical-align: top;"><select id="format"><?php

		foreach ($format_chooser_output_formats as $format)
			{
			?><option value="<?php echo $format ?>" <?php if ($format == $defaultFormat) {
				?>selected="selected"<?php } ?>><?php echo str_replace_formatted_placeholder("%extension", $format, $lang["field-fileextension"]) ?></option><?php
			}

		?></select></td>
		<td class="DownloadButton"><a id="convertDownload" onClick="return CentralSpaceLoad(this,true);"><?php
			echo $lang['action-download'] ?></a></td>
		</tr><?php
		}
	?></table><?php
	if ($downloadCount > 1)
		{
	?><script type="text/javascript">
		// Store size info in JavaScript array
		var sizeInfo = {
			<?php
			foreach ($sizes as $n => $size)
				{
				if ($size['width'] == $maxSize)
					$size = $originalSize;
			?>
			<?php echo $n ?>: {
				'info': '<?php echo get_size_info($size) ?>',
				'id': '<?php echo $size['id'] ?>',
			},
			<?php } ?>
		};
		function updateSizeInfo() {
			var selected = jQuery('select#size').find(":selected").val();
			jQuery('#sizeInfo').html(sizeInfo[selected]['info']);
		}
		function updateDownloadLink() {
			var index = jQuery('select#size').find(":selected").val();
			var selectedFormat = jQuery('select#format').find(":selected").val();
			jQuery('a#convertDownload').attr('href', '<?php echo $baseurl_short
					?>pages/download_progress.php?ref=<?php echo $ref
					?>&ext=' + selectedFormat.toLowerCase() + '&size=' + sizeInfo[index]['id']
							+ '&search=<?php echo urlencode($search) ?>&offset=<?php echo $offset ?>'
							+ '&k=<?php echo $k ?>&archive=<?php echo $archive ?>&sort='
							+ '<?php echo $sort?>&order_by=<?php echo $order_by ?>');
		}
		jQuery(document).ready(function() {
			updateSizeInfo();
			updateDownloadLink();
		});
		jQuery('select#size').change(function() {
			updateSizeInfo();
			updateDownloadLink();
		});
		jQuery('select#format').change(function() {
			updateDownloadLink();
		});
	</script><?php
		}
	return true;
	}

?>
