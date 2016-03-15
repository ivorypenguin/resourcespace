<?php

function HookSwf_previewViewReplacerenderinnerresourcepreview()
    {
    global $ref, $baseurl, $resource,$swf_preview_resource_types;
    if(!in_array($resource["resource_type"],$swf_preview_resource_types)){return false;}
    
     $swffile=get_resource_path($ref,true,"",false,"swf");
    if(!file_exists($swffile))
        {return false;}
	global $swf_preview_use_native_size;
	list($swfWidth, $swfHeight)= getimagesize($swffile);
    $swffile=get_resource_path($ref,false,"",false,"swf");
	if($swf_preview_use_native_size){$preview_width=$swfWidth;$preview_height=$swfHeight;}
	else
		{
		global $ffmpeg_preview_max_width;
		$preview_width=$ffmpeg_preview_max_width;		
		$preview_height =  ($ffmpeg_preview_max_width * $swfHeight) / $swfWidth;		
		}
    ?>
	<div id="previewimagewrapper">
		<div id="swfcontainer" style="width:<?php echo $preview_width?>px;height:<?php echo $preview_height?>px;">
		This content requires Adobe Flash Player Version 9 or higher.
		</div>
    </div>
    <script src="<?php echo $baseurl ?>/plugins/swf_preview/lib/swfobject.js" type="text/javascript"></script>
    <script>
    jQuery(document).ready(function(){
        if (swfobject.hasFlashPlayerVersion("9.0.0")) {
                swfobject.embedSWF("<?php echo $swffile; ?>",
                                   "swfcontainer",
                                   "<?php echo $preview_width?>",
                                   "<?php echo $preview_height?>",
                                   "9.0.0",
                                   "expressInstall.swf"
                                   );
             }else{console.log("Flash player must be version 9.0.0 or above");}
         
        });
         
    </script>
    <noscript>
            <p><b>Please enable Javascript!</b></p>
    </noscript>
                <?php
    return true;
    }