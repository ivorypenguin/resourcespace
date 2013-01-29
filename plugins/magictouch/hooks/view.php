<?php

function HookMagictouchViewReplacerenderinnerresourcepreview()
        {
global $baseurl,$plugins,$lang,$search,$offset,$archive,$order_by,$sort,$plugins,$download_multisize,$k,$access,$ref,$resource,$watermark;
global $magictouch_account_id;
if ($magictouch_account_id==""){return false;}

// This hooks runs outside of the renderinnerresourcepreview hook,
// and if MTFAIL is defined, annotate will know not to include a Zoom link.
// annotate plugin compatibility
global $plugins;
if (in_array("annotatecr",$plugins)|| in_array("annotate",$plugins)){
    global $annotate_ext_exclude;
    global $annotate_rt_exclude;
    if (in_array($resource['file_extension'],$annotate_ext_exclude)){return false;}
    if (in_array($resource['resource_type'],$annotate_rt_exclude)){return false;}  
    if (getval("annotate","off")!='off'){
        return false;
    }
}


// exclusions    
global $magictouch_rt_exclude;
global $magictouch_ext_exclude;
if (in_array($resource['resource_type'],$magictouch_rt_exclude)){define("MTFAIL",true); return false;}
if (in_array($resource['file_extension'],$magictouch_ext_exclude)){define("MTFAIL",true); return false;}

        $download_multisize=true;

        if ($resource["has_image"]!=1)
                {define("MTFAIL",true);
                return false;
                }


// watermark check
$access=get_resource_access($ref);
$use_watermark=check_use_watermark($ref);

// paths
$imageurl=get_resource_path($ref,false,"pre",false,$resource["preview_extension"],-1,1,$use_watermark);

global $magictouch_view_page_sizes;
foreach ($magictouch_view_page_sizes as $mtpreviewsize){
    $largeurl=get_resource_path($ref,false,$mtpreviewsize,false,"jpg",-1,1,$use_watermark);
    $largeurl_path=get_resource_path($ref,true,$mtpreviewsize,false,"jpg",-1,1,$use_watermark);

    if (file_exists($largeurl_path)){break;}

}

if (!file_exists($largeurl_path)) {
    define("MTFAIL",true);
    return false; # Requires an original large JPEG file.
}  ?>

<div id="wrapper" style="display:block;clear:none;float:left;margin: 0px ;">
	
<div class="Picture">
<a href="<?php echo $largeurl?>" class="MagicTouch"><img src="<?php echo $imageurl?>" GALLERYIMG="no" id="previewimage" /></a>
</div>

<div style="clear:left;float:right;margin-right:10px;margin-top:5px;"> 
<?php
// annotate plugin compatibility
if ((in_array("annotatecr",$plugins)|| in_array("annotate",$plugins))&&$k==""){?><a href="<?php echo $baseurl?>/pages/view.php?annotate=true&ref=<?php echo $ref?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&k=<?php echo $k?>" onClick="document.cookie='annotate=on;';return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang['annotations']?></a><br /><br /><?php }
?>
</div>
</div>
<script type="text/javascript">if(typeof MagicTouch=="object") {MagicTouch.refresh();} else {console.log("MagicTouch not loaded.");}</script>

<?php
    return true;
}


