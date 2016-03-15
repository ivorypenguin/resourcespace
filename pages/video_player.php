<?php
# Video player - plays the preview file created to preview video resources.

global $alternative,$css_reload_key,$display,$video_search_play_hover,$video_view_play_hover,$video_preview_play_hover,$video_player_thumbs_view_alt,$video_player_thumbs_view_alt_name,$keyboard_navigation_video_search,$keyboard_navigation_video_view,$keyboard_navigation_video_preview;

# Check for search page and the use of an alt file for video playback
$use_video_alts=false;
if($video_player_thumbs_view_alt && isset($video_player_thumbs_view_alt_name) && $pagename=='search' && $display!='list'){
	$use_video_alts=true;
	#  get the alt ref
	$alternative=sql_value("select ref value from resource_alt_files where resource={$ref} and name='{$video_player_thumbs_view_alt_name}'","");
}

# First we look for a preview video with the expected extension.
$flashfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);
if (file_exists($flashfile))
	{
	$flashpath=get_resource_path($ref,false,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative,false);
	}
elseif ($ffmpeg_preview_extension!="flv")
	{
	# Still no file. For legacy systems that are not using MP4 previews, next we look for an FLV preview.
	$flashfile=get_resource_path($ref,true,"pre",false,"flv",-1,1,false,"",$alternative);
	$flashpath=get_resource_path($ref,false,"pre",false,"flv",-1,1,false,"",$alternative,false);
	}

if (!file_exists($flashfile) || $video_preview_original)
        {
	# Back out to playing the source file direct (not a preview). For direct MP4/FLV upload support - the file itself is an FLV/MP4. Or, with the preview functionality disabled, we simply allow playback of uploaded video files.
	$origvideofile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);
	if(file_exists($origvideofile))
	  {
	  $flashpath=get_resource_path($ref,false,"",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative,false);
	  }
	else
		{
		$flashpath='';
		}
	}

$flashpath_raw=$flashpath;     
$flashpath=urlencode($flashpath);

if($use_video_alts){
	# blank alt variable to use proper preview image
	$alternative='';
}

$thumb=get_resource_path($ref,false,"pre",false,"jpg",-1,1,false,"",$alternative); 
$thumb_raw=$thumb;
$thumb=urlencode($thumb);

# Choose a colour based on the theme.
$theme=(isset($userfixedtheme) && $userfixedtheme!="")?$userfixedtheme:getval("colourcss","greyblu");
$color="505050";$bgcolor1="666666";$bgcolor2="111111";$buttoncolor="999999";
if ($theme=="greyblu") {$color="446693";$bgcolor1="6883a8";$bgcolor2="203b5e";$buttoncolor="adb4bb";}	
if ($theme=="whitegry") {$color="ffffff";$bgcolor1="ffffff";$bgcolor2="dadada";$buttoncolor="666666";}	
if ($theme=="black") {$bgcolor1="666666";$bgcolor2="111111";$buttoncolor="999999";}	

$width=$ffmpeg_preview_max_width;
$height=$ffmpeg_preview_max_height;

$preload='auto';
// preview size adjustments for search
if ($pagename=="search"){
	switch($display){
		case "xlthumbs":
			$width="350";
			$height=350/$ffmpeg_preview_max_width*$ffmpeg_preview_max_height;
			break;
		case "thumbs":
			$width="150";
			$height=150/$ffmpeg_preview_max_width*$ffmpeg_preview_max_height;
			break;
		case "smallthumbs":
			$width="75";
			$height=75/$ffmpeg_preview_max_width*$ffmpeg_preview_max_height;
			break;
	}
}
// play video on hover?
$play_on_hover=false;
if(($pagename=='search' && $video_search_play_hover) || ($pagename=='view' && $video_view_play_hover) || (($pagename=='preview' || $pagename=='preview_all') && $video_preview_play_hover)){
	$play_on_hover=true;
}
// using keyboard hotkeys?
$playback_hotkeys=false;
if(($pagename=='search' && $keyboard_navigation_video_search) || ($pagename=='view' && $keyboard_navigation_video_view) || (($pagename=='preview' || $pagename=='preview_all') && $keyboard_navigation_video_preview)){
	$playback_hotkeys=true;
}

if(!hook("swfplayer"))
	{
	if (!$videojs) 
		{ ?>
		<object type="application/x-shockwave-flash" data="<?php echo $baseurl_short?>lib/flashplayer/player_flv_maxi.swf?t=<?php echo time() ?>" width="<?php echo $width?>" height="<?php echo $height?>" class="Picture">
		     <param name="allowFullScreen" value="true" />
		     <param name="movie" value="<?php echo $baseurl_short?>lib/flashplayer/player_flv_maxi.swf" />
		     <param name="FlashVars" value="flv=<?php echo $flashpath?>&amp;width=<?php echo $width?>&amp;height=<?php echo $height?>&amp;margin=0&amp;showvolume=1&amp;volume=200&amp;showtime=2&amp;autoload=1&amp;<?php if ($pagename!=="search"){?>showfullscreen=1<?php } ?>&amp;showstop=1&amp;buttoncolor=<?php echo $buttoncolor?>&playercolor=<?php echo $color?>&bgcolor=<?php echo $color?>&bgcolor1=<?php echo $bgcolor1?>&bgcolor2=<?php echo $bgcolor2?>&startimage=<?php echo $thumb?>&playeralpha=75&autoload=1&buffermessage=&buffershowbg=0" />
		</object>
		<?php 
		} 
	else 
		{ 
		global $ffmpeg_preview_extension,$css_reload_key,$context;
		?>
		<link href="<?php echo $baseurl_short?>lib/videojs/video-js.min.css?r=<?=$css_reload_key?>" rel="stylesheet">
        <script src="<?php echo $baseurl_short?>lib/videojs/video.min.js?r=<?=$css_reload_key?>"></script>
		<script src="<?php echo $baseurl_short?>lib/js/videojs-extras.js?r=<?=$css_reload_key?>"></script>
		<!-- START VIDEOJS -->
		<div class="videojscontent">
		<video 
			id="<?php echo $context ?>_<?php echo $display ?>_introvideo<?php echo $ref?>"
			controls
			data-setup='{ 
				<?php if($play_on_hover){?>
					"loadingSpinner" : false,
					"children": { 
						"bigPlayButton":false, 
						<?php if($pagename=='search' && $display=='smallthumbs'){?>
							"controlBar": false
						<?php }
						else{ ?>
							"controlBar": { 
								"children": { 
									"playToggle": false, 
									"volumeControl":false
								}
							}
						<?php } ?>
					}
				<?php } ?> 
			}'
			preload="<?php echo $preload?>"
			width="<?php echo $width?>" 
			height="<?php echo $height?>" 
			class="video-js vjs-default-skin vjs-big-play-centered <?php if($pagename=='search'){echo "video-$display";}?>" 
			poster="<?php echo $thumb_raw?>"
			<?php if($play_on_hover){ ?>
				onmouseout="videojs_<?php echo $context ?>_<?php echo $display ?>_introvideo<?php echo $ref ?>[0].pause();"
				onmouseover="videojs_<?php echo $context ?>_<?php echo $display ?>_introvideo<?php echo $ref ?>[0].play();"
			<?php } ?>
		>
		    <source src="<?php echo $flashpath_raw?>" type="video/<?php echo $ffmpeg_preview_extension?>" >
		    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
		</video>
		
		<?php if($play_on_hover){ ?>	
				<script>
				var videojs_<?php echo $context ?>_<?php echo $display ?>_introvideo<?php echo $ref ?> = jQuery('#<?php echo $context ?>_<?php echo $display ?>_introvideo<?php echo $ref ?>');
				</script>
		<?php } ?>
		</div>
		<!-- END VIDEOJS -->
		<?php
		}
	}
