<?php

function HookVideo_tracksViewHtml5videoextra()
	{
    global $ref, $context, $display,$video_altfiles;
    $video_altfiles=get_alternative_files($ref);
	
    foreach ($video_altfiles as $video_altfile)
      {
	  $converted_vtt=array();
      if(mb_strtolower($video_altfile["file_extension"]) =="vtt")
		{
		$video_altfile["path"] = get_resource_path($ref, false, '', true, $video_altfile["file_extension"], -1, 1, false, '',  $video_altfile["ref"]);
		?>
		<track class="videojs_alt_track" kind="subtitles" src="<?php echo $video_altfile["path"] ?>" label="<?php echo $video_altfile["description"]; ?>" ></track>
		<?php
		$converted_vtt[$video_altfile["description"]]=$video_altfile["ref"];
		}	  
	  }
	}

function HookVideo_tracksViewModifydownloadbutton()
	{
	global $video_altfiles,$n, $alt_access;   
	if(!isset($alt_access) || !isset($video_altfiles[$n]) || mb_strtolower($video_altfiles[$n]["file_extension"])!="vtt")
        {echo " colspan=2 ";}	
	}
	
	
function HookVideo_tracksViewDownloadbuttonreplace()
	{    
    global $context,$display,$ref,$video_altfiles,$n, $alt_access, $lang;
    if(!isset($alt_access) || !isset($video_altfiles[$n]) || mb_strtolower($video_altfiles[$n]["file_extension"])!="vtt")
        {return false;}    
    $video_altfiles[$n]["path"] = get_resource_path($ref, false, '', true, $video_altfiles[$n]["file_extension"], -1, 1, false, '',  $video_altfiles[$n]["ref"]);
    echo "<a href=\"#\" onclick=\"jQuery('.videojs_alt_track').remove();enable_video_track_" . $video_altfiles[$n]["ref"] . "();\">" . $lang["preview"] . "</a>";
    ?>
    <script>
	var sub_<?php echo $video_altfiles[$n]["ref"] ?> = "<track class='videojs_alt_track' id='videojs_track_<?php echo $video_altfiles[$n]["ref"] ?>' label='<?php echo $video_altfiles[$n]["description"]; ?>' kind='subtitles' src='<?php echo $video_altfiles[$n]["path"] ?>' default></track>";
    function enable_video_track_<?php echo $video_altfiles[$n]["ref"]; ?> (){ 
        if(!jQuery('#videojs_track_<?php echo $video_altfiles[$n]["ref"] ?>').is(':visible')){
			jQuery('#<?php echo $context; ?>_<?php echo $display; ?>_introvideo<?php echo $ref; ?>_html5_api').append(sub_<?php echo $video_altfiles[$n]["ref"] ?>);
        }
		document.getElementById("<?php echo $context; ?>_<?php echo $display; ?>_introvideo<?php echo $ref; ?>_html5_api").textTracks[0].mode = "showing";	
		//document.getElementById('videojs_track_<?php echo $video_altfiles[$n]["ref"] ?>').addEventListener("load", function() { 
          //  this.mode = "showing"; 
            //     document.getElementById("<?php echo $context; ?>_<?php echo $display; ?>_introvideo<?php echo $ref; ?>_html5_api").textTracks[0].mode = "showing";
             //});
         }

    </script>
    <?php
	echo "</td><td class=\"DownloadButton\">";
	return false;
    }

function HookVideo_tracksViewAfterresourceactions()
	{    
    global $altfiles, $ref, $resource, $video_tracks_permitted_video_extensions, $baseurl_short, $order_by, $sort,$search, $offset,$archive, $lang, $access;
	if($access==0 && in_array(mb_strtolower($resource["file_extension"]),$video_tracks_permitted_video_extensions) && count($altfiles)>0)
		{
		?>
		<li><a href="<?php echo $baseurl_short?>plugins/video_tracks/pages/create_video.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>" onClick="if(typeof modalurl != 'undefined' && modalurl.href!=window.location.href){jQuery('#CentralSpace').html('');CentralSpaceLoad(modalurl);}return ModalLoad(this,true,true);">
			<?php echo "<i class='fa fa-video-camera'></i>&nbsp;" . $lang["video_tracks_create_video_link"] ?>
			</a></li>
		<?php
		}
	
  	}
	
