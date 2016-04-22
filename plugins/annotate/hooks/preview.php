<?php
function HookAnnotatePreviewReplacepreviewbacktoview(){
	global $baseurl,$lang,$ref,$search,$offset,$order_by,$sort,$archive,$k;?>
<p style="margin:7px 0 7px 0;padding:0;"><a class="enterLink" href="<?php echo $baseurl?>/pages/view.php?<?php if (getval("annotate","")=="true"){?>annotate=true&<?php } ?>ref=<?php echo $ref?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&k=<?php echo $k?>">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a>
<?php return true;
} 

function HookAnnotatePreviewPreviewimage2 (){
global $ajax,$ext,$baseurl,$ref,$k,$search,$offset,$order_by,$sort,$archive,$lang,
       $download_multisize,$baseurl_short,$url,$path,$path_orig,$annotate_ext_exclude,
       $annotate_rt_exclude,$annotate_public_view,$annotate_pdf_output,$nextpage,
       $previouspage, $alternative;
    
$resource=get_resource_data($ref);

if (in_array($resource['file_extension'],$annotate_ext_exclude)){return false;}
if (in_array($resource['resource_type'],$annotate_rt_exclude)){return false;}
if (!($k=="") && !$annotate_public_view){return false;}

if (!file_exists($path) && !file_exists($path_orig)){return false;}
if (!file_exists($path)){
	$sizes = getimagesize($path_orig);}
else{
	$sizes = getimagesize($path);
}

$w = $sizes[0];
$h = $sizes[1];

?>

<script type="text/javascript">
    button_ok = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["ok"])) ?>";
    button_cancel = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["cancel"])) ?>";
    button_delete = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-delete"])) ?>";
    button_add = "&gt&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-add_note"])) ?>";		
    button_toggle = "&gt;&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-toggle-on"])) ?>";
    button_toggle_off = "&gt;&nbsp;<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["action-toggle-off"])) ?>";
    error_saving = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["error-saving"])) ?>";
    error_deleting = "<?php echo preg_replace("/\r?\n/", "\\n", addslashes($lang["error-deleting"])) ?>";
</script>
<script>
     jQuery.noConflict();
</script>

<div id="wrapper" style="display:block;clear:none;float:left;margin: 0px;">
    <table cellpadding="0" cellspacing="0">
    <tr>
    <?php
    if($resource['file_extension'] != "jpg" && $previouspage != -1 && resource_download_allowed($ref, "scr", $resource["resource_type"])) { ?>
        <td valign="middle">
            <a onClick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref) ?>&alternative=<?php echo urlencode($alternative)?>&ext=<?php echo urlencode($ext)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&page=<?php echo urlencode($previouspage)?>" class="PDFnav  pagePrev">&lt;</a>
        </td>
    <?php 
    } else if($nextpage !=-1 && resource_download_allowed($ref, "scr", $resource["resource_type"])) { ?>
        <td valign="middle">
            <a href="#" class="PDFnav pagePrev">&nbsp;&nbsp;&nbsp;</a>
        </td>
    <?php
    } ?>
<div>
		<td>
            <img id="toAnnotate" onload="annotate(<?php echo $ref?>,'<?php echo $k?>','<?php echo $w?>','<?php echo $h?>',<?php echo getvalescaped("annotate_toggle",true)?>,<?php echo getvalescaped('page', 1); ?>);" src="<?php echo $url?>" id="previewimage" class="Picture" GALLERYIMG="no" style="display:block;"   />
        </td>
    <?php
    if($nextpage != -1 && resource_download_allowed($ref, "scr", $resource["resource_type"])) { ?>
        <td valign="middle">
            <a onClick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref) ?>&alternative=<?php echo urlencode($alternative)?>&ext=<?php echo urlencode($ext)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&page=<?php echo urlencode($nextpage)?>" class="PDFnav pageNext">&gt;</a>
        </td>
    <?php 
    } ?>
	</div>

<div style="padding-top:5px;">



     <?php
     // MAGICTOUCH PLUGIN COMPATIBILITY
     global $magictouch_account_id;
     if ($magictouch_account_id!=""){
        global $plugins;global $magictouch_rt_exclude;global $magictouch_ext_exclude;if (in_array("magictouch",$plugins)&& !in_array($resource['resource_type'],$magictouch_rt_exclude) && !in_array($resource['file_extension'],$magictouch_ext_exclude) && !defined("MTFAIL")){?>&nbsp;&nbsp;<a style="display:inline;float:right;" href="<?php echo ((getval("from","")=="search")?"search.php?":"preview.php?ref=" . $ref . "&")?>search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&k=<?php echo $k?>" onClick="document.cookie='annotate=off;';return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang['zoom']?></a><?php }
     }
     ///////////////
     ?>	
     <?php if ($annotate_pdf_output){?>
     &nbsp;&nbsp;<a style="display:inline;float:right;margin-right:10px;" href="<?php echo $baseurl?>/plugins/annotate/pages/annotate_pdf_config.php?ref=<?php echo $ref?>&ext=<?php echo $resource["preview_extension"]?>&k=<?php echo $k?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>" >&gt;&nbsp;<?php echo $lang["pdfwithnotes"]?></a> &nbsp;&nbsp;
     <?php } ?>
     	</div>
    </tr></table>
</div>

     
     
     <?php

return true;	
}


