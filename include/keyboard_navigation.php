<?php global $k;?>
<script type="text/javascript">

jQuery(document).ready(function() {
 jQuery.fn.reverse = [].reverse;
 jQuery(document).keyup(function (e)
  { 
    if(jQuery("input,textarea").is(":focus"))
    {
       // don't listen to keyboard arrows when focused on form elements
       <?php hook("keyboardnavtextfocus");?>
    }
    else
    { 
        var share='<?php echo htmlspecialchars($k) ?>';
        var modAlt=e.altKey;
        var modShift=e.shiftKey;
        var modCtrl=e.ctrlKey;
        var modMeta=e.metaKey;
        var modOn=(modAlt || modShift || modCtrl || modMeta);
        
         switch (e.which) 
         {
			 
		    <?php hook ("addhotkeys"); //this comes first so overriding the below is possible ?>
            // left arrow
            case <?php echo $keyboard_navigation_prev; ?>: if ((jQuery('.prevLink').length > 0)<?php if ($pagename=="view") { ?>&&(jQuery("#fancybox-content").html()=='')<?php } ?>) {jQuery('.prevLink').click();break;}
              if (<?php if ($keyboard_navigation_pages_use_alt) echo "modAlt&&"; ?>(jQuery('.prevPageLink').length > 0)) jQuery('.prevPageLink').click();
              
                     <?php 
                     if (($pagename=="preview_all") && $keyboard_scroll_jump) { ?>
                     currentX=jQuery(window).scrollLeft();
                     jQuery('.ResourceShel_').reverse().each(function(index) {
                         offset = jQuery(this).offset();
                         if (offset.left-20<currentX) {
                            jQuery(window).scrollLeft(offset.left-20)
                            return false;
                         }
                     });                     
                     <?php } ?>
                     break;
            // right arrow
            case <?php echo $keyboard_navigation_next; ?>: if ((jQuery('.nextLink').length > 0)<?php if ($pagename=="view") { ?>&&(jQuery("#fancybox-content").html()=='')<?php } ?>) {jQuery('.nextLink').click();break;}
              if (<?php if ($keyboard_navigation_pages_use_alt) echo "modAlt&&"; ?>(jQuery('.nextPageLink').length > 0)) jQuery('.nextPageLink').click();
                     <?php 
                     if (($pagename=="preview_all") && $keyboard_scroll_jump) { ?>
                     currentX=jQuery(window).scrollLeft();
                     jQuery('.ResourceShel_').each(function(index) {
                         offset = jQuery(this).offset();
                         if (offset.left-40>currentX) {
                            jQuery(window).scrollLeft(offset.left-20)
                            return false;
                         }
                     });                     
                     <?php } ?>
                     break;   
            case <?php echo $keyboard_navigation_add_resource; ?>: if (jQuery('.addToCollection').length > 0) jQuery('.addToCollection').click();
                     break;
            case <?php echo $keyboard_navigation_remove_resource; ?>: if (jQuery('.removeFromCollection').length > 0) jQuery('.removeFromCollection').click();
                     break;  
            case <?php echo $keyboard_navigation_prev_page; ?>: if (jQuery('.pagePrev').length > 0) jQuery('.pagePrev').click();
                     break;
            case <?php echo $keyboard_navigation_next_page; ?>: if (jQuery('.pageNext').length > 0) jQuery('.pageNext').click();
                     break;
            case <?php echo $keyboard_navigation_all_results; ?>: if (jQuery('.upLink').length > 0) jQuery('.upLink').click();
                     break;
            case <?php echo $keyboard_navigation_toggle_thumbnails; ?>: if (jQuery('#toggleThumbsLink').length > 0) jQuery('#toggleThumbsLink').click();
                     break;
            case <?php echo $keyboard_navigation_zoom; ?>: if (jQuery('.enterLink').length > 0) window.location=jQuery('.enterLink').attr("href");
                     break;
            case <?php echo $keyboard_navigation_close; ?>: ModalClose();
                     break;
            case <?php echo $keyboard_navigation_view_all; ?>: CentralSpaceLoad('<?php echo $baseurl;?>/pages/search.php?search=!collection'+document.getElementById("currentusercollection").innerHTML+'&k='+share,true);
                     break;
            <?php if(($pagename=='search' && $keyboard_navigation_video_search) || ($pagename=='view' && $keyboard_navigation_video_view) || (($pagename=='preview' || $pagename=='preview_all') && $keyboard_navigation_video_preview)){?>
				<?php if($video_playback_backwards){ ?>
					case <?php echo $keyboard_navigation_video_search_backwards?>:
						//console.log("backwards button pressed");
						//console.log("Player is "+vidActive);
						curPlayback=vidActive.playbackRate();
						//console.log("Current playback rate is "+curPlayback);
						if(playback=='backward'){
							newPlayback=curPlayback+1;
						}
						else{
							newPlayback=1;
						}
						//console.log("New playback rate is "+newPlayback);
						playback='backward';
						videoRewind(newPlayback);
						break;
				<?php } ?>
				
				case <?php echo $keyboard_navigation_video_search_play_pause?>:
					<?php if($pagename=='view' || $pagename=='preview'){ ?>
						vidActive=document.getElementById('introvideo<?php echo $ref?>');
					<?php } 
					else{ ?>
						vidActive=document.getElementById('introvideo'+vidActiveRef);
					<?php } ?>
					//console.log("active="+vidActive);
					videoPlayPause(vidActive);
					break;
					
				case <?php echo $keyboard_navigation_video_search_forwards?>:
					//console.log("forward button pressed");
					//console.log("Player is "+vidActive);
					// clear
					clearInterval(intervalRewind);
					// get current playback rate
					curPlayback=vidActive.playbackRate();
					//console.log("Current playback rate is "+curPlayback);
					if(playback=='forward'){
						newPlayback=curPlayback+1;
					}
					else{
						newPlayback=1;
					}
					playback='forward';
					//console.log("New playback rate is "+newPlayback);
					vidActive.playbackRate(newPlayback);
					break;
				<?php } ?>
         }
         
     }
 });
});
</script>
