<?php
function getDownloadTitle()
	{
	$headline=$sizes[$n]['id']=='' ? str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["originalfileoftype"]): $sizes[$n]["name"];
	$newHeadline=hook('replacesizelabel', '', array($ref, $resource, $sizes[$n]));
	if (!empty($newHeadline)){$headline=$newHeadline;}
	if ($direct_link_previews && $downloadthissize){$headline=make_download_preview_link($ref, $sizes[$n],$headline);}
		/* Start Row */
		?>
		<td class="DownloadFileName"><h2><?php echo $headline?></h2><?php
		if (is_numeric($sizes[$n]["width"]))
			{
			echo get_size_info($sizes[$n]);
			}
		?>
		</td>
		<td class="DownloadFileSize">
			<?php echo $sizes[$n]["filesize"]?>
		</td>
		<?php
	}

function getShortDownload() 
	{
	global $save_as, $direct_download, $order_by, $lang, $baseurl_short, $baseurl, $k, $search, $request_adds_to_collection, $offset, $archive, $sort;
	if ($downloadthissize)
		{	
		?><li class="DownloadOptionLink"><?php	
		if (!$direct_download || $save_as)
			{
			global $size_info_array;
			$size_info_array = $size_info;
			if(!hook("downloadbuttonreplace"))
				{
				?><a id="downloadlink" <?php
				if (!hook("downloadlink","",array("ref=" . $ref . "&k=" . $k . "&size=" . $size_info["id"]
						. "&ext=" . $size_info["extension"])))
					{
					?>href="<?php echo $baseurl ?>/pages/terms.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php
							echo urlencode($search) ?>&amp;k=<?php echo urlencode($k)?>&amp;url=<?php
							echo urlencode("pages/download_progress.php?ref=" . $ref . "&size=" . $size_info["id"]
									. "&ext=" . $size_info["extension"] . "&k=" . $k . "&search=" . urlencode($search)
									. "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by="
									. urlencode($order_by))?>"<?php
					}
					?> onClick="return CentralSpaceLoad(this,true);">
				<?php 
				//REPLACE THIS
				echo $lang["action-download"]

				?>

				</a><?php
				}
			}
		else
			{
			?><a id="downloadlink" href="#" onclick="directDownload('<?php
					echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref) ?>&size=<?php
					echo $size_info['id']?>&ext=<?php echo $size_info['extension']?>&k=<?php
					echo urlencode($k)?>')">
					<?php 
					//REPLACE THIS
					echo $lang["action-download"]
					?>
					</a><?php
			}
			unset($size_info_array);
		}
	else if (checkperm("q"))
		{
		?><li class="DownloadOptionLink"><?php
		if (!hook("resourcerequest"))
			{
			if ($request_adds_to_collection)
				{
				echo add_to_collection_link($ref,$search,"alert('" . $lang["requestaddedtocollection"] . "');",$size_info["id"]);
				}
			else
				{
				?><a href="<?php echo $baseurl_short?>pages/resource_request.php?ref=<?php echo urlencode($ref)?>&k=<?php echo getval("k","")?>" onClick="return CentralSpaceLoad(this,true);"><?php
				}
			//REPLACE THIS
			echo $lang["action-request"]?>
			</a><?php
			}
		}
	else
		{
		# No access to this size, and the request functionality has been disabled. Show just 'restricted'.
		?><li class="DownloadOptionLink DownloadDisabled">
		<?php 
		// REPLACE THIS
		echo $lang["access1"]?><?php
		}
	?></li><?php
	}
function buildRedesignDownloadLinks()
	{
	global $ref,$resource,$lang;
	$table_headers_drawn=false;
	$nodownloads=false;$counter=0;$fulldownload=false;
	$showprice=$userrequestmode==2 || $userrequestmode==3;
	hook("additionalresourcetools");
	if ($resource["has_image"]==1 && $download_multisize)
		{
		# Restricted access? Show the request link.

		# List all sizes and allow the user to download them
		$sizes=get_image_sizes($ref,false,$resource["file_extension"]);
		for ($n=0;$n<count($sizes);$n++)
			{
			# Is this the original file? Set that the user can download the original file
			# so the request box does not appear.
			$fulldownload=false;
			if ($sizes[$n]["id"]=="") {$fulldownload=true;}
			
			$counter++;

			# Should we allow this download?
			# If the download is allowed, show a download button, otherwise show a request button.
			$downloadthissize=resource_download_allowed($ref,$sizes[$n]["id"],$resource["resource_type"]);

			getShortDownload();

			if (!hook("previewlinkbar")){
				if ($downloadthissize && $sizes[$n]["allow_preview"]==1)
					{ 
					# Add an extra line for previewing
					?> 
					<tr class="DownloadDBlend"><td class="DownloadFileName"><h2><?php echo $lang["preview"]?></h2><p><?php echo $lang["fullscreenpreview"]?></p></td><td class="DownloadFileSize"><?php echo $sizes[$n]["filesize"]?></td>
					<?php if ($userrequestmode==2 || $userrequestmode==3) { ?><td></td><?php } # Blank spacer column if displaying a price above (basket mode).
					?>
					<td class="DownloadButton">
					<a class="enterLink" id="previewlink" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref)?>&amp;ext=<?php echo $resource["file_extension"]?>&amp;k=<?php echo urlencode($k)?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&<?php echo hook("previewextraurl") ?>"><?php echo $lang["action-view"]?></a>
					</td>
					</tr>
					<?php
					} 
				}
			} /* end hook previewlinkbar */
		}
	elseif (strlen($resource["file_extension"])>0 && !($access==1 && $restricted_full_download==false))
		{
		# Files without multiple download sizes (i.e. no alternative previews generated).
		$counter++;
		$path=get_resource_path($ref,true,"",false,$resource["file_extension"]);
		if (file_exists($path))
			{
			hook("beforesingledownloadsizeresult");
				if(!hook("origdownloadlink")):
			?>
			<tr class="DownloadDBlend">
			<td class="DownloadFileName"><h2><?php echo (isset($original_download_name)) ? str_replace_formatted_placeholder("%extension", $resource["file_extension"], $original_download_name, true) : str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["originalfileoftype"]); ?></h2></td>
			<td class="DownloadFileSize"><?php echo formatfilesize(filesize_unlimited($path))?></td>
			<td class="DownloadButton">
			<?php if (!$direct_download || $save_as){ ?>
				<a <?php if (!hook("downloadlink","",array("ref=" . $ref . "&k=" . $k . "&ext=" . $resource["file_extension"] ))) { ?>href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>&search=<?php echo $search ?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $resource["file_extension"] . "&k=" . $k . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"<?php } ?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a>
			<?php } else { ?>
				<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $resource['file_extension']?>&k=<?php echo urlencode($k)?>')"><?php echo $lang["action-download"]?></a>
			<?php } // end if direct_download ?>
			</td>
			</tr>
			<?php
				endif; # hook origdownloadlink
			}
		} 
	else
		{
		$nodownloads=true;
		}
	
	if (($nodownloads || $counter==0) && !checkperm("T" . $resource["resource_type"] . "_"))
		{
		hook("beforenodownloadresult");
		# No file. Link to request form.
		?>
		<tr class="DownloadDBlend">
		<td class="DownloadFileName"><h2><?php echo ($counter==0)?$lang["offlineresource"]:$lang["access1"]?></h2></td>
		<td class="DownloadFileSize"><?php echo $lang["notavailableshort"]?></td>

		<?php if (checkperm("q"))
			{
			?>
			<?php if(!hook("resourcerequest")){?>
			<td class="DownloadButton"><a href="<?php echo $baseurl_short?>pages/resource_request.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k) ?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-request"]?></a></td>
			<?php } ?>
			<?php
			}
		else
			{
			?>
			<td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td>
			<?php
			}
		?>
		</tr>
		<?php
		}
	
	if (isset($flv_download) && $flv_download)
		{
		# Allow the FLV preview to be downloaded. $flv_download is set when showing the FLV preview video above.
		?>
		<tr class="DownloadDBlend">
		<td class="DownloadFileName"><h2><?php echo (isset($ffmpeg_preview_download_name)) ? $ffmpeg_preview_download_name : str_replace_formatted_placeholder("%extension", $ffmpeg_preview_extension, $lang["cell-fileoftype"]); ?></h2></td>
		<td class="DownloadFileSize"><?php echo formatfilesize(filesize_unlimited($flvfile))?></td>
		<td class="DownloadButton">
		<?php if (!$direct_download || $save_as){?>
			<a href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&search=<?php echo $search ?>&k=<?php echo urlencode($k)?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $ffmpeg_preview_extension . "&size=pre&k=" . $k . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a>
		<?php } else { ?>
			<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $ffmpeg_preview_extension?>&size=pre&k=<?php echo urlencode($k)?>')"><?php echo $lang["action-download"]?></a>
		<?php } // end if direct_download ?></td>
		</tr>
		<?php
		}

		hook("additionalresourcetools2");
	
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
		for ($n=0;$n<count($altfiles);$n++)
			{
			$alt_type=$altfiles[$n]['alt_type'];
			if ($alt_types_organize)
				{
				if ($alt_type!=$last_alt_type)
					{
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
			<tr class="DownloadDBlend" <?php if ($alt_pre!="" && $alternative_file_previews_mouseover) { ?>onMouseOver="orig_preview=jQuery('#previewimage').attr('src');orig_width=jQuery('#previewimage').width();jQuery('#previewimage').attr('src','<?php echo $alt_pre ?>');jQuery('#previewimage').width(orig_width);" onMouseOut="jQuery('#previewimage').attr('src',orig_preview);"<?php } ?>>
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
			
			<?php if ($access==0)
				{?>
				<td class="DownloadButton">
				<?php 		
				if (!$direct_download || $save_as)
					{
					if(!hook("downloadbuttonreplace"))
						{
						?><a <?php if (!hook("downloadlink","",array("ref=" . $ref . "&alternative=" . $altfiles[$n]["ref"] . "&k=" . $k . "&ext=" . $altfiles[$n]["file_extension"]))) { ?>href="<?php echo $baseurl_short?>pages/terms.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search) ?>&url=<?php echo urlencode("pages/download_progress.php?ref=" . $ref . "&ext=" . $altfiles[$n]["file_extension"] . "&k=" . $k . "&alternative=" . $altfiles[$n]["ref"] . "&search=" . urlencode($search) . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by=" . urlencode($order_by))?>"<?php } ?> onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-download"] ?></a><?php 
						}
					}
				else 
					{ ?>
					<a href="#" onclick="directDownload('<?php echo $baseurl_short?>pages/download_progress.php?ref=<?php echo urlencode($ref)?>&ext=<?php echo $altfiles[$n]["file_extension"]?>&k=<?php echo urlencode($k)?>&alternative=<?php echo $altfiles[$n]["ref"]?>')"><?php echo $lang["action-download"]?></a>
					<?php 
					} // end if direct_download 
					?></td></td>
					<?php 
				} 
			else 
				{ ?>
				<td class="DownloadButton DownloadDisabled"><?php echo $lang["access1"]?></td>
				<?php 
				} ?>
			</tr>
			<?php	
			}
	        hook("morealtdownload");
		}
	# --- end of alternative files listing
	if ($use_mp3_player && file_exists($mp3realpath) && $access==0)
		{
		include "mp3_play.php";
		}
		?>
	</table>
	<?php
	hook("additionalresourcetools3");
 	} 
	

function HookResource_tools_compactViewRenderinnerresourcedownloadspace() 
	{
	global $context;
	?><a id="<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>_view-download-button" class="view-download-button" href="#">Download</a><?php
	return false;
	}

function HookResource_tools_compactViewCustomdetailstitle() 
	{
	global $context;
	?>
	<a id="<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>_resource-details"  class="resource-details" href="#">More Information</a>
	
	<?php
	return true;
	}
function HookResource_tools_compactViewAdditionalresourcetools3() 
	{
	global $context;
	global $allow_share,$access,$restricted_share,$hide_resource_share_link,$lang,$baseurl_short,$ref,$search,$offset,$order_by,$sort,$archive;
	if ($allow_share && ($access==0 || ($access==1 && $restricted_share)) && !$hide_resource_share_link) 
		{ 
		?>
		<a id="<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>_share-resource-button" class="share-resource-button resource-tools-button" href="#" >
			<?php echo $lang["share"]?>
		</a>
		<ul id="<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>_ResourceShareContainer" class="ResourceShareContainer" style="display: none;">
			<li>
				<a href="<?php echo $baseurl_short?>pages/resource_email.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">
				<?php echo $lang["email"];?>
				</a>
			</li>
			<li>
				<a href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&generateurl=true" onClick="return CentralSpaceLoad(this,true);">
				<?php echo $lang["generateurl"];?>
				</a>
			</li>

		</ul>
		<?php 
		hook('aftersharelink', '', array($ref, $search, $offset, $order_by, $sort, $archive));
		}
	$allow_share=false;
	
	// PROJECT
	hook("resourceprojecttools");
	// OPTIONS
	?><a id="<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>_view-resource-tools" class="view-resource-tools" href="#">Options</a><?php
	}
	
function HookResource_tools_compactViewRenderbeforeresourcedetails()
	{
	?>
	<div id="VideoCaptionContainer">
	<?php
	global $fields,$r_tools_captionfield;
	$fieldcount =count($fields);
	for($i=0; $i<$fieldcount; $i++)
		{
		if($fields[$i]["ref"]==$r_tools_captionfield)
			{
			$displaycondition = check_view_display_condition($fields, $i);

			if($displaycondition) 
				{
				echo $fields[$i]["value"];
				array_splice($fields,$i,1);
				break;
				}
			}
		}

	?>
	</div>
	<?php
	}

function HookResource_tools_compactViewReplacedownloadspacetableheaders() 
	{
	return true;
	}

function HookResource_tools_compactViewBefore_footer_always() 
	{
	global $context;
	?>
	<script>
	registerResourcetoolsSlide('<?php echo ($context=="Modal"?"modal":"CentralSpace") ?>');	
	</script>
	<?php
	}








	