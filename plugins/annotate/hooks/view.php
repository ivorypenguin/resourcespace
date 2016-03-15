<?php


function HookAnnotateViewRenderinnerresourcepreview(){
	global $baseurl_short,$ajax,$ref,$ffmpeg_preview_extension,$resource,$k,$search,$offset,$order_by,$sort,$archive,$lang,$download_multisize,$baseurl,$annotate_ext_exclude,$annotate_rt_exclude,$annotate_public_view,$annotate_pdf_output;

if (in_array($resource['file_extension'],$annotate_ext_exclude)){return false;}
if (in_array($resource['resource_type'],$annotate_rt_exclude)){return false;}

if (!($k=="") && !$annotate_public_view){return false;}
$download_multisize=true;

$flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
if (file_exists($flvfile)){return false;}

if ($resource["has_image"]==1)
	{
	?>
	<script type="text/javascript">
		button_ok = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["ok"])) ?>";
		button_cancel = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["cancel"])) ?>";
		button_delete = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-delete"])) ?>";
		button_add = "&gt;&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-add_note"])) ?>";
	    button_toggle = "&gt;&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-toggle-on"])) ?>";
		button_toggle_off = "&gt;&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-toggle-off"])) ?>";
		error_saving = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["error-saving"])) ?>";
		error_deleting = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["error-deleting"])) ?>";
	</script>
	<script>
		jQuery.noConflict();

	</script><?php
	$use_watermark=check_use_watermark($resource['ref']);
	$imagepath=get_resource_path($ref,true,"pre",false,$resource["preview_extension"],-1,1,$use_watermark);
	if (!file_exists($imagepath))
		{
        $imagepath=get_resource_path($ref,true,"thm",false,$resource["preview_extension"],-1,1,$use_watermark);    
		$imageurl=get_resource_path($ref,false,"thm",false,$resource["preview_extension"],-1,1,$use_watermark);
		}
	else
		{
		$imageurl=get_resource_path($ref,false,"pre",false,$resource["preview_extension"],-1,1,$use_watermark);
		}
	if (!file_exists($imagepath)){return false;}	
	$sizes = getimagesize($imagepath);

	$w = $sizes[0];
	$h = $sizes[1];	

	if (file_exists($imagepath))
		{ 	
			$page_count = get_page_count($resource);

			$multipage_document = FALSE;

			if($page_count > 1) {
				$multipage_document = TRUE;
			}	
		?>	


<div id="wrapper" class="annotate-view-wrapper">
<div>
<img id="toAnnotate" onload="annotate(<?php echo $ref?>,'<?php echo $k?>','<?php echo $w?>','<?php echo $h?>',<?php echo getvalescaped("annotate_toggle",true)?>);" src="<?php echo $imageurl?>" id="previewimage" class="Picture" GALLERYIMG="no" style="display:block;"   />
</div>
	
<div class="annotate-view-preview-links" >
<a class="enterLink" href="<?php echo $baseurl_short?>pages/preview.php?<?php if (getval("annotate","")!=""){?>annotate=true&<?php } ?>ref=<?php echo $ref?>&amp;ext=<?php echo $resource["preview_extension"]?>&amp;k=<?php echo $k?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo $offset?>&amp;order_by=<?php echo $order_by?>&amp;sort=<?php echo $sort?>&amp;archive=<?php echo $archive?><?php if($multipage_document) { echo '&amp;page=1'; } ?>" title="<?php echo $lang["fullscreenpreview"]?>">&gt;&nbsp;<?php echo $lang["fullscreenpreview"]?></a>
    
     <?php
     // MAGICTOUCH PLUGIN COMPATIBILITY
     global $magictouch_account_id;
     if ($magictouch_account_id!=""){
        global $plugins;global $magictouch_rt_exclude;global $magictouch_ext_exclude;if (in_array("magictouch",$plugins)&& !in_array($resource['resource_type'],$magictouch_rt_exclude) && !in_array($resource['file_extension'],$magictouch_ext_exclude) && !defined("MTFAIL")){?>&nbsp;<a style="display:inline;" href="<?php echo ((getval("from","")=="search")?$baseurl_short."pages/search.php?":$baseurl_short."pages/view.php?ref=" . $ref . "&")?>search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&k=<?php echo $k?>" onClick="document.cookie='annotate=off';return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang['zoom']?></a><?php }
     }
     ///////////////
     ?>
     
<?php if ($annotate_pdf_output){?>
&nbsp;&nbsp;<a style="display:inline;float:right;" class="nowrap" href="<?php echo $baseurl_short?>plugins/annotate/pages/annotate_pdf_config.php?ref=<?php echo $ref?>&ext=<?php echo $resource["preview_extension"]?>&k=<?php echo $k?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>" onClick="return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang["pdfwithnotes"]?></a>
<?php } ?>

</div>
</div>

	
<?php 
		} 
	?><?php
	}
else
	{
	?>
	<img src="<?php echo $baseurl?>/gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],false)?>" alt="" class="Picture" style="border:none;" id="previewimage" />
	<?php
	}
	

	
	
return true;	
}

?>
