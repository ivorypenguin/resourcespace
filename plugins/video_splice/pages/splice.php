<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php";
include_once "../../../include/collections_functions.php";
include "../../../include/search_functions.php";
include "../../../include/resource_functions.php";
include "../../../include/image_processing.php";

if (getval("data","")!="")
	{
	$vars=array();
	parse_str(getval("data",""),$vars);

	$n=0;
	foreach($vars["splice_reel"] as $vid)
		{
		$n++;
		# Update the timestamp to reorder the collection. This is a bit hacky but changing how collections are ordered is probably quite a long winded task.
		sql_query("update collection_resource set date_added='" . date("Y-m-d H:i:s",time()-$n) . "' where collection='" . $usercollection . "' and resource='" . escape_check($vid) . "'");
		}
	?>
	top.collections.location.href="<?php echo $baseurl ?>/pages/collections.php?nc=<?php echo time() ?>";
	<?php
	exit();
	}

# Fetch videos
$videos=do_search("!collection" . $usercollection);

if (getval("splice","")!="" && count($videos)>1)
	{
	$ref=copy_resource($videos[0]["ref"]);	# Base new resource on first video (top copy metadata).

	# Set parent resource field details.
	global $videosplice_parent_field;
	$resources="";
	for ($n=0;$n<count($videos);$n++)
		{
		if ($n>0) {$resources.=", ";}
		$crop_from=get_data_by_field($videos[$n]["ref"],$videosplice_parent_field);
		$resources.=$videos[$n]["ref"] . ($crop_from!="" ? " " . str_replace("%resourceinfo", $crop_from, $lang["cropped_from_resource"]) : "");
		}
	$history = str_replace("%resources", $resources, $lang["merged_from_resources"]);
	update_field($ref,$videosplice_parent_field,$history);

	# Establish FFMPEG location.
	$ffmpeg_fullpath = get_utility_path("ffmpeg");

	$vidlist="";
	# Create FFMpeg syntax to merge all additional videos.
	for ($n=0;$n<count($videos);$n++)
		{
		# Work out source/destination
		global $ffmpeg_preview_extension;
		
		if (file_exists(get_resource_path($videos[$n]["ref"],true,"",false,$videos[$n]["file_extension"])))
			{
			$source=get_resource_path($videos[$n]["ref"],true,"",false,$videos[$n]["file_extension"],-1,1,false,"",-1,false);
			}
		else 
			{
			exit(str_replace(array("%resourceid", "%filetype"), array($videos[$n]["ref"], $videos[$n]["file_extension"]), $lang["error-no-ffmpegpreviewfile"]));
			}
		# Encode intermediary
		$intermediary = get_temp_dir() . "/video_splice_temp_" . $videos[$n]["ref"] . ".mpg";
		if ($config_windows) {$intermediary = str_replace("/", "\\", $intermediary);}
		$shell_exec_cmd = $ffmpeg_fullpath . " -y -i " . escapeshellarg($source);
		$shell_exec_cmd .= ($ffmpeg_use_qscale)? " -target ntsc-vcd " : " -sampleq ";
		$shell_exec_cmd .= escapeshellarg($intermediary);
		$output = exec($shell_exec_cmd);
		$vidlist.= " " . escapeshellarg($intermediary);
		}
	$vidlist = trim($vidlist);
	
	# Target is the first file.
	$targetmpg = get_resource_path($ref,true,"",true,"mpg",-1,1,false,"",-1,false);
	# Combine all MPEGS to make one file (this doesn't work for FLV, we had to convert to MPEG first)
	if ($config_windows)
		{
		$shell_exec_cmd = "copy/b " . str_replace(array(" ", "/"), array("+", "\\"), $vidlist) . " " . escapeshellarg($targetmpg);
		}
	else
		{
		$shell_exec_cmd = "cat $vidlist > " . escapeshellarg($targetmpg);
		}
	$output = exec($shell_exec_cmd);


	# Remove the temporary files.
	for ($n=0;$n<count($videos);$n++)
		{
		$intermediary = get_temp_dir() . "/video_splice_temp_" . $videos[$n]["ref"] . ".mpg";
		if ($config_windows) {$intermediary = str_replace("/", "\\", $intermediary);}
		unlink($intermediary);
		}

	# Update the file extension & date.
	$result = sql_query("update resource set file_extension = 'mpg', creation_date = now() where ref = '$ref' limit 1");

	# Create previews.
	create_previews($ref,false,"mpg");
	redirect("pages/view.php?ref=" . $ref);
	}

include "../../../include/header.php";
?>

<h1><?php echo $lang["splice"]?></h1>
<p><?php echo $lang["intro-splice"]?></p>
<p><?php echo $lang["drag_and_drop_to_rearrange"]?></p>
<div id="splice_scroll">
<div id="splice_reel" style="width:<?php echo ((count($videos)+1) * 80);?>px">
<?php
foreach ($videos as $video)
	{
	if ($video["has_image"])
		{
		$img=get_resource_path($video["ref"],false,"col",false,$video["preview_extension"],-1,1,false,$video["file_modified"]);
		}
	else
		{
		$img="../../../gfx/" . get_nopreview_icon($video["resource_type"],$video["file_extension"],true);
		}
	?><img src="<?php echo $img ?>" id="splice_<?php echo $video["ref"] ?>" class="splice_item"><?php
	}
?>
</div></div>

<script type="text/javascript">
	function ReorderResourcesInCollectionSplice(idsInOrder){
		var newOrder = [];
		jQuery.each(idsInOrder, function() {
			newOrder.push(this.substring(7));
			}); 
		
		jQuery.ajax({
		  type: 'POST',
		  url: '<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo urlencode($usercollection) ?>&reorder=true',
		  data: {order:JSON.stringify(newOrder)},
		  success: function() {
		    var results = new RegExp('[\\?&amp;]' + 'search' + '=([^&amp;#]*)').exec(window.location.href);
		    var ref = new RegExp('[\\?&amp;]' + 'ref' + '=([^&amp;#]*)').exec(window.location.href);
		    if ((ref==null)&&(results!== null)&&('<?php echo urlencode("!collection" . $usercollection); ?>' === results[1])) CentralSpaceLoad('<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>',true);
		  }
		});		
	}
	jQuery("#splice_reel").sortable();
	jQuery(document).ready(function() {
		var collection = <?php echo $usercollection; ?>;
		var k = <?php echo $k? $k : "''"; ?>;
		jQuery('#splice_reel').sortable({
			helper:"clone",
			items: ".splice_item",
			stop: function(event, ui) {
				var idsInOrder = jQuery('#splice_reel').sortable("toArray");
				ReorderResourcesInCollectionSplice(idsInOrder);
				ChangeCollection(collection,k);
			}
		});
		jQuery('.CollectionPanelShell').disableSelection();
		jQuery("#CollectionDiv").on("click",".CollectionResourceRemove",function() {
			var splice_id = "#splice_"+jQuery(this).closest(".CollectionPanelShell").attr("id").replace(/[^0-9]/gi,"");
			jQuery(splice_id).remove();
		});
	});
	
</script>

<form method="post">
<input type="submit" onClick="CentralSpaceShowLoading();" name="splice" value="<?php echo $lang["action-splice"]?>" style="width:150px;">
</form>

<?php

include "../../../include/footer.php";

?>
