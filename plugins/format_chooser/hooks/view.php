<?php

function show_table_headers($showprice)
	{
	global $lang;
	if(!hook("replacedownloadspacetableheaders")){
	?><tr><td><?php echo $lang["fileinformation"]?></td>
	<td><?php echo $lang["filetype"]?></td>
	<?php if ($showprice) { ?><td><?php echo $lang["price"] ?></td><?php } ?>
	<td class="textcenter"><?php echo $lang["options"]?></td>
	</tr>
	<?php
	} # end hook("replacedownloadspacetableheaders")
	}

function HookFormat_chooserViewReplacedownloadoptions()
	{
	global $resource, $ref, $counter, $headline, $lang, $download_multisize, $showprice, $save_as,
			$direct_link_previews, $hide_restricted_download_sizes, $format_chooser_output_formats,
			$baseurl_short, $search, $offset, $k, $order_by, $sort, $archive, $direct_download;

	$inputFormat = $resource['file_extension'];

	if ($resource["has_image"] != 1 || !$download_multisize || $save_as
			|| !supportsInputFormat($inputFormat))
		return false;

	$defaultFormat = getDefaultOutputFormat($inputFormat);
	$tableHeadersDrawn = false;

	?><table cellpadding="0" cellspacing="0"><?php
	hook("formatchooserbeforedownloads");
	$sizes = get_image_sizes($ref, false, $resource['file_extension'], false);
	$downloadCount = 0;
	$originalSize = -1;

	# Show original file download
	for ($n = 0; $n < count($sizes); $n++)
		{
		$downloadthissize = resource_download_allowed($ref, $sizes[$n]["id"], $resource["resource_type"]);
		$counter++;

		if ($sizes[$n]['id'] != '') {
			if ($downloadthissize)
				$downloadCount++;
			continue;
		}

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
		<td class="DownloadFileName"><h2><?php echo $headline?></h2><p><?php
		echo $sizes[$n]["filesize"];
		if (is_numeric($sizes[$n]["width"]))
			echo preg_replace('/^<p>/', ', ', get_size_info($sizes[$n]), 1);

		?></p><td class="DownloadFileFormat"><?php echo str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["field-fileextension"]) ?></td><?php

		if ($showprice)
			{
			?><td><?php echo get_display_price($ref, $sizes[$n]) ?></td><?php
			}
		add_download_column($ref, $sizes[$n], $downloadthissize);
		}

	# Add drop down for all other sizes
	$closestSize = 0;
	if ($downloadCount > 0)
		{
		if (!$tableHeadersDrawn)
			show_table_headers($showprice);

		?><tr class="DownloadDBlend">
		<td class="DownloadFileSizePicker"><select id="size"><?php

		$sizes = get_all_image_sizes();

		# Filter out all sizes that are larger than our image size, but not the closest one
		for ($n = 0; $n < count($sizes); $n++)
			{
			if (intval($sizes[$n]['width']) >= intval($originalSize['width'])
					&& intval($sizes[$n]['height']) >= intval($originalSize['height'])
					&& ($closestSize == 0 || $closestSize > (int)$sizes[$n]['width']))
				$closestSize = (int)$sizes[$n]['width'];
			}
		for ($n = 0; $n < count($sizes); $n++)
			{
			if (intval($sizes[$n]['width']) != $closestSize
					&& intval($sizes[$n]['width']) > intval($originalSize['width'])
					&& intval($sizes[$n]['height']) > intval($originalSize['height']))
				unset($sizes[$n]);
			}
		foreach ($sizes as $n => $size)
			{
			# Only add choice if allowed
			$downloadthissize = resource_download_allowed($ref, $size["id"], $resource["resource_type"]);
			if (!$downloadthissize)
				continue;

			$name = $size['name'];
			if ($size['width'] == $closestSize)
				$name = $lang['format_chooser_original_size'];
			?><option value="<?php echo $n ?>"><?php echo $name ?></option><?php
			}
		?></select><p id="sizeInfo"></p></td><?php
		if ($showprice)
			{
			?><td>-</td><?php
			}
		?><td class="DownloadFileFormatPicker" style="vertical-align: top;"><select id="format"><?php

		foreach ($format_chooser_output_formats as $format)
			{
			?><option value="<?php echo $format ?>" <?php if ($format == $defaultFormat) {
				?>selected="selected"<?php } ?>><?php echo str_replace_formatted_placeholder("%extension", $format, $lang["field-fileextension"]) ?></option><?php
			}

		?></select><?php showProfileChooser(); ?></td>
		<td class="DownloadButton"><a id="convertDownload" onClick="return CentralSpaceLoad(this,true);"><?php
			echo $lang['action-download'] ?></a></td>
		</tr><?php
		}
	?></table><?php
	hook("formatchooseraftertable");
	if ($downloadCount > 0)
		{
		?><script type="text/javascript">
			// Store size info in JavaScript array
			var sizeInfo = {
				<?php
				foreach ($sizes as $n => $size)
					{
					if ($size['width'] == $closestSize)
						$size = $originalSize;
				?>
				<?php echo $n ?>: {
					'info': '<?php echo get_size_info($size, $originalSize) ?>',
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
				var profile = jQuery('select#profile').find(":selected").val();
				if (profile)
					profile = "&profile=" + profile;
				else
					profile = '';

				basePage = 'pages/download_progress.php?ref=<?php echo $ref ?>&ext='
						+ selectedFormat.toLowerCase() + profile + '&size=' + sizeInfo[index]['id']
						+ '&search=<?php echo urlencode($search) ?>&offset=<?php echo $offset ?>'
						+ '&k=<?php echo $k ?>&archive=<?php echo $archive ?>&sort='
						+ '<?php echo $sort?>&order_by=<?php echo $order_by ?>';

				jQuery('a#convertDownload').attr('href', '<?php echo $baseurl_short;
							if (!$direct_download)
								{
								echo 'pages/terms.php?ref=' . $ref . '&search=' . $search . '&k='
										. $k . '&url=';
								}
						?>' + <?php echo $direct_download ? 'basePage' : 'encodeURIComponent(basePage)' ?>
						);
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
			jQuery('select#profile').change(function() {
				updateDownloadLink();
			});
		</script>
		<?php
		}
		global $access,$alt_types_organize,$alternative_file_previews,$userrequestmode;
	# Alternative files listing
$alt_access=hook("altfilesaccess");
if ($access==0) $alt_access=true; # open access (not restricted)
if ($alt_access) 
	{
	$alt_order_by="";$alt_sort="";
	if ($alt_types_organize){$alt_order_by="alt_type";$alt_sort="asc";} 
	$altfiles=get_alternative_files($ref,$alt_order_by,$alt_sort);
	hook("processaltfiles");
	$last_alt_type="-";
	?>
	<table>
	<?php
	for ($n=0;$n<count($altfiles);$n++)
		{
		$alt_type=$altfiles[$n]['alt_type'];
		if ($alt_types_organize){
			if ($alt_type!=$last_alt_type){
				$alt_type_header=$alt_type;
				if ($alt_type_header==""){$alt_type_header=$lang["alternativefiles"];}
				hook("viewbeforealtheader");
				?>
				<tr class="DownloadDBlend">
				<td colspan="3" id="altfileheader"><h2><?php echo $alt_type_header?></h2></td>
				</tr>
				<?php
			}
			$last_alt_type=$alt_type;
		}	
		else if ($n==0)
			{
			hook("viewbeforealtheader");
			?>
			<tr>
			<td colspan="3" id="altfileheader"><?php echo $lang["alternativefiles"]?></td>
			</tr>
			<?php
			}	
		$alt_thm="";$alt_pre="";
		if ($alternative_file_previews)
			{
			$alt_thm_file=get_resource_path($ref,true,"col",false,"jpg",-1,1,false,"",$altfiles[$n]["ref"]);
			if (file_exists($alt_thm_file))
				{
				# Get web path for thumb (pass creation date to help cache refresh)
				$alt_thm=get_resource_path($ref,false,"col",false,"jpg",-1,1,false,$altfiles[$n]["creation_date"],$altfiles[$n]["ref"]);
				}
			$alt_pre_file=get_resource_path($ref,true,"pre",false,"jpg",-1,1,false,"",$altfiles[$n]["ref"]);
			if (file_exists($alt_pre_file))
				{
				# Get web path for preview (pass creation date to help cache refresh)
				$alt_pre=get_resource_path($ref,false,"pre",false,"jpg",-1,1,false,$altfiles[$n]["creation_date"],$altfiles[$n]["ref"]);
				}
			}
		?>
		<tr class="DownloadDBlend" <?php if ($alt_pre!="" && isset($alternative_file_previews_mouseover) && $alternative_file_previews_mouseover) { ?>onMouseOver="orig_preview=jQuery('#previewimage').attr('src');orig_width=jQuery('#previewimage').width();jQuery('#previewimage').attr('src','<?php echo $alt_pre ?>');jQuery('#previewimage').width(orig_width);" onMouseOut="jQuery('#previewimage').attr('src',orig_preview);"<?php } ?>>
		<td class="DownloadFileName">
		<?php if(!hook("renderaltthumb")): ?>
		<?php if ($alt_thm!="") { ?><a href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref)?>&alternative=<?php echo $altfiles[$n]["ref"]?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&<?php echo hook("previewextraurl") ?>"><img src="<?php echo $alt_thm?>" class="AltThumb"></a><?php } ?>
		<?php endif; ?>
		<h2 class="breakall"><?php echo htmlspecialchars($altfiles[$n]["name"])?></h2>
		<p><?php echo htmlspecialchars($altfiles[$n]["description"])?></p>
		</td>
		<td class="DownloadFileSize"><?php echo formatfilesize($altfiles[$n]["file_size"])?></td>
		
		<?php if ($userrequestmode==2 || $userrequestmode==3) { ?><td></td><?php } # Blank spacer column if displaying a price above (basket mode).
		?>
		
		<?php if ($access==0){?>
		<td class="DownloadButton">
		<?php 		
		if (!$direct_download || $save_as)
			{
			if(!hook("downloadbuttonreplace"))
				{
				?><a <?php if (!hook("downloadlink","",array("ref=" . $ref . "&alternative=" . $altfiles[$n]["ref"] . "&k=" . $k . "&ext=" . $altfiles[$n]["file_extension"]))) { ?>href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search) ?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $altfiles[$n]["file_extension"] . "&k=" . $k . "&alternative=" . $altfiles[$n]["ref"] . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"<?php } ?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a><?php 
				}
			}
		else { ?>
			<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $altfiles[$n]["file_extension"]?>&k=<?php echo urlencode($k)?>&alternative=<?php echo $altfiles[$n]["ref"]?>')"><?php echo $lang["action-download"]?></a>
		<?php } // end if direct_download ?></td></td>
		<?php } else { ?>
		<td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td>
		<?php } ?>
		</tr>
		<?php	
		}
        hook("morealtdownload");
       ?>
   	</table>
   	<?php
	}
# --- end of alternative files listing
	return true;
	}

?>
