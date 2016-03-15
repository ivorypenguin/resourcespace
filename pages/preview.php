<?php
include "../include/db.php";
include_once "../include/general.php";

# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key(getvalescaped("ref","",true),$k))) {include "../include/authenticate.php";}

include "../include/search_functions.php";
include_once "../include/collections_functions.php";
include "../include/resource_functions.php";

# Save Existing Thumb Cookie Status Then Hide the collection Bar 
# - Restores Status on Unload (See Foot of page)
$saved_thumbs_state = "";
$thumbs=getval("thumbs","unset");
if($thumbs != "unset" && $thumbs != "hide")
    {
    $saved_thumbs_state = "show";
    }
$thumbs = "hide";
rs_setcookie("thumbs", $thumbs, 1000,"","",false,false);

$ref=getvalescaped("ref","",true);
$search=getvalescaped("search","");
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
$starsearch=getvalescaped("starsearch","");
$page=getvalescaped("page",1);
$alternative=getvalescaped("alternative",-1);
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);
if($sort != 'ASC' || $sort != 'DESC') {$sort = $default_sort;}

# next / previous resource browsing
$go=getval("go","");
if ($go!="")
	{
    $origref = $ref; # Store the reference of the resource before we move, in case we need to revert this.

	# Re-run the search and locate the next and previous records.
	$modified_result_set=hook("modifypagingresult"); 
	if ($modified_result_set){
		$result=$modified_result_set;
	} else {
		$result=do_search($search,$restypes,$order_by,$archive,-1,$sort,false,$starsearch);
	}
	if (is_array($result))
		{
		# Locate this resource
		$pos=-1;
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]==$ref) {$pos=$n;}
			}
		if ($pos!=-1)
			{
			if (($go=="previous") && ($pos>0)) {$ref=$result[$pos-1]["ref"];}
			if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($offset+72)) {$offset=$pos+1;}} # move to next page if we've advanced far enough
			}
		}

    # Option to replace the key via a plugin (used by resourceconnect plugin).
    $newkey = hook("nextpreviewregeneratekey");
    if (is_string($newkey)) {$k = $newkey;}

    # Check access permissions for this new resource, if an external user.
    if ($k!="" && !check_access_key($ref, $k)) {$ref = $origref;} # Cancel the move.
	}


$resource=get_resource_data($ref);
$ext="jpg";

if ($ext!="" && $ext!="gif" && $ext!="jpg" && $ext!="png") {$ext="jpg";$border=false;} # Supports types that have been created using ImageMagick


# Load access level
$access=get_resource_access($ref);
$use_watermark=check_use_watermark($ref);

# check permissions (error message is not pretty but they shouldn't ever arrive at this page unless entering a URL manually)
if ($access==2) 
		{
		exit("This is a confidential resource.");
		}

hook('replacepreview');

# Next / previous page browsing (e.g. pdfs)
$previouspage=$page-1;
if (!file_exists(get_resource_path($ref,true,"scr",false,$ext,-1,$previouspage,$use_watermark,"",$alternative))&&!file_exists(get_resource_path($ref,true,"",false,$ext,-1,$previouspage,$use_watermark,"",$alternative))) {$previouspage=-1;}
$nextpage=$page+1;
if (!file_exists(get_resource_path($ref,true,"scr",false,$ext,-1,$nextpage,$use_watermark,"",$alternative))) {$nextpage=-1;}


# Locate the resource

$path=get_resource_path($ref,true,"scr",false,$ext,-1,$page,$use_watermark,"",$alternative);
/*$path_orig=get_resource_path($ref,true,"",false,$ext,-1,$page,$use_watermark,"",$alternative);*/

if (file_exists($path) && (resource_download_allowed($ref,"scr",$resource["resource_type"]) || $use_watermark ))
	{
	$url=get_resource_path($ref,false,"scr",false,$ext,-1,$page,$use_watermark,"",$alternative);
	}
/*elseif (file_exists($path_orig) && resource_download_allowed($ref,"",$resource["resource_type"]))
	{
	$url=get_resource_path($ref,false,"",false,$ext,-1,$page,$use_watermark,"",$alternative);
	}*/
else
	{
	$path=get_resource_path($ref,true,"pre",false,$ext,-1,$page,$use_watermark,"",$alternative);
	if (file_exists($path))
		{
		$url=get_resource_path($ref,false,"pre",false,$ext,-1,$page,$use_watermark,"",$alternative);
		}
	 }	
if (!isset($url))
	{
	$info=get_resource_data($ref);
	$url=$baseurl."/gfx/" . get_nopreview_icon($info["resource_type"],$info["file_extension"],false);
	$border=false;
	}

$resource=get_resource_data($ref);

// get mp3 paths if necessary and set $use_mp3_player switch
if (!(isset($resource['is_transcoding']) && $resource['is_transcoding']==1) && (in_array($resource["file_extension"],$ffmpeg_audio_extensions) || $resource["file_extension"]=="mp3") && $mp3_player){
		$use_mp3_player=true;
	}
	else {
		$use_mp3_player=false;
	}
if ($use_mp3_player){	
	$mp3realpath=get_resource_path($ref,true,"",false,"mp3");
	if (file_exists($mp3realpath)){
		$mp3path=get_resource_path($ref,false,"",false,"mp3");
	}
}

include "../include/header.php";
?>

<?php if(!hook("fullpreviewresultnav")){ ?>
<?php if (!hook("replacepreviewbacktoview")){?>
<p style="margin:7px 0 7px 0;padding:0;"><a class="enterLink" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&k=<?php echo urlencode($k)?>&<?php echo hook("viewextraurl") ?>">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a>
<?php } /*end hook replacepreviewbacktoview*/ ?>
<?php if ($k=="") { ?>

<?php if (!checkperm("b")) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo add_to_collection_link(htmlspecialchars($ref),htmlspecialchars($search))?>+&nbsp;<?php echo $lang["action-addtocollection"]?></a><?php } ?>
<?php if ($search=="!collection" . $usercollection) { ?>&nbsp;&nbsp;<?php echo remove_from_collection_link(htmlspecialchars($ref),htmlspecialchars($search))?>&minus;&nbsp;<?php echo $lang["action-removefromcollection"]?></a><?php } ?>
<?php } ?>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a class="prevLink" onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/preview.php?from=<?php echo urlencode(getval("from",""))?>&ref=<?php echo urlencode($ref) ?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&go=previous&<?php echo hook("nextpreviousextraurl") ?>">&lt;&nbsp;<?php echo $lang["previousresult"]?></a>
|
<a  class="upLink" onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?<?php if (strpos($search,"!")!==false) {?>search=<?php echo urlencode($search)?>&k=<?php echo urlencode($k)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php } ?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&<?php echo hook("searchextraurl") ?>"><?php echo $lang["viewallresults"]?></a>
|
<a class="nextLink" onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/preview.php?from=<?php echo urlencode(getval("from",""))?>&ref=<?php echo urlencode($ref) ?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&go=next&<?php echo hook("nextpreviousextraurl") ?>"><?php echo $lang["nextresult"]?>&nbsp;&gt;</a>


&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php

if (!hook("replacepreviewpager")){
	if (($nextpage!=-1 || $previouspage!=-1) && $nextpage!=-0){
	    $pagecount = get_page_count($resource,$alternative);
	    if ($pagecount!=null && $pagecount!=-2){
	    ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['page'];?>: <select class="ListDropdown" style="width:auto" onChange="CentralSpaceLoad('<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref) ?>&alternative=<?php echo urlencode($alternative)?>&ext=<?php echo urlencode($ext)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&page='+this.value);"><?php 
	    for ($n=1;$n<$pagecount+1;$n++)
	    	{
	        if ($n<=$pdf_pages)
	        	{
	            ?><option value="<?php echo $n?>" <?php if ($page==$n){?>selected<?php } ?>><?php echo $n?><?php
	            }
	        }
	    if ($pagecount>$pdf_pages){?><option value="1">...<?php } ?>
	    </select><?php
		}
	}
}
?>


</p>
<?php } ?>

<?php if (!hook("previewimage")) { ?>
<?php if (!hook("previewimage2")) { ?>
<table cellpadding="0" cellspacing="0">
<tr>

<td valign="middle"><?php if ($resource['file_extension']!="jpg" && $previouspage!=-1 &&resource_download_allowed($ref,"scr",$resource["resource_type"])) { ?><a onClick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref) ?>&alternative=<?php echo urlencode($alternative)?>&ext=<?php echo urlencode($ext)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&page=<?php echo urlencode($previouspage)?>" class="PDFnav  pagePrev">&lt;</a><?php } 
elseif ($nextpage!=-1 &&resource_download_allowed($ref,"scr",$resource["resource_type"]) ) { ?><a href="#" class="PDFnav pagePrev">&nbsp;&nbsp;&nbsp;</a><?php } ?></td>
<?php $flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);
if (!file_exists($flvfile)) {$flvfile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative);}
if (!(isset($resource['is_transcoding']) && $resource['is_transcoding']==1) && file_exists($flvfile) && (strpos(strtolower($flvfile),".".$ffmpeg_preview_extension)!==false))
	{
	# Include the Flash player if an FLV file exists for this resource.
	$download_multisize=false;
    if(!hook("customflvplay"))
        {
        include "video_player.php";
        }
    }
	elseif ($use_mp3_player && file_exists($mp3realpath) && hook("custommp3player")){
		// leave player to place image
		}	
    else{?>


<?php if (!hook("replacepreviewimage")) { ?> 
<td><a onClick="return CentralSpaceLoad(this);" href="<?php echo ((getval("from","")=="search")?$baseurl_short."pages/search.php?":$baseurl_short."pages/view.php?ref=" . urlencode($ref) . "&")?>search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&<?php echo hook("viewextraurl") ?>"><img class="Picture" src="<?php echo $url?>" alt=""/></a><?php hook('afterpreviewimage'); ?></td>
<?php } // end hook replacepreviewimage ?> 


<?php } ?>

<td valign="middle"><?php if ($nextpage!=-1 &&resource_download_allowed($ref,"scr",$resource["resource_type"])) { ?><a onClick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/preview.php?ref=<?php echo urlencode($ref) ?>&alternative=<?php echo urlencode($alternative)?>&ext=<?php echo urlencode($ext)?>&k=<?php echo urlencode($k)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?><?php if($saved_thumbs_state=="show"){?>&thumbs=show<?php } ?>&archive=<?php echo urlencode($archive)?>&page=<?php echo urlencode($nextpage)?>" class="PDFnav pageNext">&gt;</a><?php } ?></td>
</tr></table>

<?php } // end hook previewimage2 ?>
<?php } // end hook previewimage ?>

<?php

if ($show_resource_title_in_titlebar){
	$title =  htmlspecialchars(i18n_get_translated(get_data_by_field($ref,$view_title_field)));
	if (strlen($title) > 0){
		echo "<script language='javascript'>\n";
		echo "document.title = \"$applicationname - $title\";\n";
		echo "</script>";
	}
}

include "../include/footer.php";
?>
