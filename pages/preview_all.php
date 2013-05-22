<?php
include "../include/db.php";
include "../include/general.php";

# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key(getvalescaped("ref","",true),$k))) {include "../include/authenticate.php";}

include "../include/search_functions.php";
include "../include/collections_functions.php";
include "../include/resource_functions.php";

$backto=getval("backto","");
$col_order_by=getval("col_order_by","");

$colref=getval("ref","");
$collection=getval("ref","");
$ext="jpg";
$height=getval("height",600);
$vertical=getval("vertical",$preview_all_default_orientation);
# Load collection info.
$cinfo=get_collection($usercollection);
$skip=false;
# Check to see if the user can edit this collection.
$allow_reorder=false;

# Fetch and set the values
$search=getvalescaped("search","");
if (strpos($search,"!")===false) {setcookie("search",$search);} # store the search in a cookie if not a special search
$offset=getvalescaped("offset",0);if (strpos($search,"!")===false) {setcookie("saved_offset",$offset);}
if ((!is_numeric($offset)) || ($offset<0)) {$offset=0;}
$order_by=getvalescaped("order_by",$default_sort);if (strpos($search,"!")===false) {setcookie("saved_order_by",$order_by);}
if ($order_by=="") {$order_by=$default_sort;}
$per_page=getvalescaped("per_page",$default_perpage);setcookie("per_page",$per_page);
$archive=getvalescaped("archive",0);if (strpos($search,"!")===false) {setcookie("saved_archive",$archive);}
$jumpcount=0;

# Most sorts such as popularity, date, and ID should be descending by default,
# but it seems custom display fields like title or country should be the opposite.
$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);setcookie("saved_sort",$sort);
$revsort = ($sort=="ASC") ? "DESC" : "ASC";


if ($order_by=="relevance" && $k=="" && (($userref==$cinfo["user"]) || ($cinfo["allow_changes"]==1) || (checkperm("h"))))
	{
	$allow_reorder=true;
	}
if ($allow_reorder || $infobox)
	{
	
	# Also check for the parameter and reorder as necessary.
	$reorder=getvalescaped("reorder","");
	if ($reorder!="")
		{
		$r=explode("-",$reorder);
		swap_collection_order($r[0],$r[1],$usercollection);
		}
	}	

$border=true;

$search='!collection'.$colref;
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","relevance");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
$page=getvalescaped("page",1);
$alternative=getvalescaped("alternative",-1);
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);
$headerinsert="
	 <!--[if lt IE 7]><link rel='stylesheet' type='text/css' href='../css/ie.css'><![endif]-->
";

if ($allow_reorder){
$result=do_search("!collection".$colref);
}
else{
$result=do_search("!collection" . $colref,'',$order_by,$archive,-1,$sort);
}

include "../include/header.php";

if (substr($search,0,11)=="!collection")
	{
	$collection=substr($search,11);
	$collection=explode(",",$collection);
	$collection=$collection[0];
	$collectiondata=get_collection($collection);
	if (!$collectiondata){?>
		<script>alert('<?php echo $lang["error-collectionnotfound"];?>');document.location='<?php echo $baseurl_short?>pages/home.php'</script>
	<?php } 
	if ($allow_reorder)
		{
	# Check to see if this user can edit (and therefore reorder) this resource
		if (($userref==$collectiondata["user"]) || ($collectiondata["allow_changes"]==1) || (checkperm("h")))
			{
			$allow_reorder=true;
			}
		}
	}



$display="";
include ("../include/search_title_processing.php");


?>
<script type="text/javascript">
function ReorderResources(id1,id2)
    {
    CentralSpaceLoad('<?php echo $baseurl_short?>pages/preview_all.php?reorder=' + id1 + '-' + id2+'&ref=<?php echo urlencode($colref)?>&vertical=<?php echo urlencode($vertical)?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&sort=<?php echo urlencode($sort)?>');
    CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?ref=<?php echo urlencode($colref)?>');
    }
</script>
<br/>
<table id="preview_all_table" style="width:100%;">
<tr><p style="margin:7px 0 7px 0;padding:0;"><a class="enterLink" href="<?php if ($backto!=''){echo urlencode($backto);} else { echo $baseurl_short.'pages/search';}?>.php?search=%21collection<?php echo urlencode($colref)?>&order_by=<?php echo urlencode($order_by)?>&col_order_by=<?php echo urlencode($col_order_by)?>&sort=<?php echo urlencode($sort)?>&k=<?php echo urlencode($k)?>">&lt; <?php echo $lang["backtoresults"]?></a>
&nbsp;&nbsp;<a href="<?php echo $baseurl_short?>pages/preview_all.php?backto=<?php echo urlencode($backto)?>&ref=<?php echo urlencode($colref)?>&vertical=h&offset=<?php echo urlencode($offset)?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&col_order_by=<?php echo urlencode($col_order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>">&gt; <?php echo $lang["horizontal"]; ?> </a>
&nbsp;&nbsp;<a href="<?php echo $baseurl_short?>pages/preview_all.php?backto=<?php echo urlencode($backto)?>&ref=<?php echo urlencode($colref)?>&vertical=v&offset=<?php echo urlencode($offset)?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&col_order_by=<?php echo urlencode($col_order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>">&gt; <?php echo $lang["vertical"]; ?> </a>
</tr>

<?php if (!$collections_compact_style){
        echo $search_title;
        }
    else {
    echo $search_title;
    ?><?php if (substr($search,0,11)=="!collection" && $k==""){
        $cinfo=get_collection(substr($search,11));
        $feedback=$cinfo["request_feedback"];
        $count_result=count($result);
        if (!$search_titles){?><br/><?php }
        draw_compact_style_selector($cinfo['ref']);
        ?><?php if ($vertical=="v"){?><br/><br/><?php } ?>
    <?php } /*end if a collection search and compact_style - action selector*/ ?>    
    <?php } ?>
<?php
$n=0;
for ($x=0;$x<count($result);$x++){
# Load access level
$ref=$result[$x]['ref'];
$resource_data=get_resource_data($ref);

$access=get_resource_access($result[$x]);

// get mp3 paths if necessary and set $use_mp3_player switch
if (!(isset($result[$x]['is_transcoding']) && $result[$x]['is_transcoding']==1) && (in_array($result[$x]["file_extension"],$ffmpeg_audio_extensions) || $result[$x]["file_extension"]=="mp3") && $mp3_player){
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

# check permissions (error message is not pretty but they shouldn't ever arrive at this page unless entering a URL manually)
if ($access==2) 
		{
		exit("Confidential resource.");
		}

# Locate the resource
$path="";
$url="";
	if ($access==1&&(checkperm('w')|| ($k!="" && isset($watermark)))){$watermark=true;} else {$watermark=false;}
$path=get_resource_path($ref,true,"scr",false,$ext,-1,$page,$watermark,$result[$x]["file_modified"],$alternative,-1,false);

if (file_exists($path) && resource_download_allowed($result[$x],"scr",$resource_data["resource_type"]))
	{
	$url=get_resource_path($ref,false,"scr",false,$ext,-1,$page,$watermark,$result[$x]["file_modified"],$alternative,-1,false);
	}
else
	{
	$path=get_resource_path($ref,true,"pre",false,$ext,-1,$page,$watermark,$result[$x]["file_modified"],$alternative,-1,false);
	if (file_exists($path))
		{
		$url=get_resource_path($ref,false,"pre",false,$ext,-1,$page,$watermark,$result[$x]["file_modified"],$alternative,-1,false);
		}
	 }	
if (!file_exists($path))
	{
	$info=get_resource_data($ref);
	$url="../gfx/" . get_nopreview_icon($info["resource_type"],$info["file_extension"],false);
	$path=$url;
	$border=false;
	}

?>
    
<?php if ($vertical=="v"){
if (!hook("replacepreviewalltitle")){ ?><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $result[$x]['ref']?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&sort=<?php echo urlencode($sort)?>">&nbsp;<?php echo i18n_get_translated($result[$x]['field'.$view_title_field])?></a><?php } /* end hook replacepreviewalltitle */?></tr><tr><?php }else { ?>
<td style="padding:10px;"><?php } ?>
	
	<div class="ResourceShel_" id="ResourceShel_<?php echo htmlspecialchars($ref)?>">
	<?php if ($vertical=="h"){?>&nbsp;<?php if (!hook("replacepreviewalltitle")){ ?><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $result[$x]['ref']?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&sort=<?php echo urlencode($sort)?>"><?php echo i18n_get_translated($result[$x]['field'.$view_title_field])?></a><?php } /* end hook replacepreviewalltitle */?><br/><?php } ?>
	<?php $imageinfo = getimageSize( $path ); 
	$imageheight=$imageinfo[1];?>
    <?php $flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
if (!file_exists($flvfile)) {$flvfile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension);}
if (!(isset($resource['is_transcoding']) && $resource['is_transcoding']==1) && file_exists($flvfile) && (strpos(strtolower($flvfile),".".$ffmpeg_preview_extension)!==false))
	{
	# Include the Flash player if an FLV file exists for this resource.
	$download_multisize=false;
    if(!hook("customflvplay"))
        {
        include "flv_play.php";?><br /><br /><?php
        }
    } 
	elseif ($use_mp3_player && file_exists($mp3realpath) && hook("custommp3player")){
		// leave preview to the custom mp3 player
		}	
    else{?>
<?php if (!$allow_reorder){?><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $result[$x]['ref']?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&sort=<?php echo urlencode($sort)?>"><?php } //end if !reorder?><img class="Picture<?php if (!$border){?>Doc<?php } ?>" id="image<?php echo htmlspecialchars($ref)?>" imageheight="<?php echo $imageheight?>" src="<?php echo $url?>" alt="" style="height:<?php echo $height?>px;" /><?php if (!$allow_reorder){?></a><?php } //end if !reorder?><br/><br/>
<?php } ?>
<?php if ($search_titles){$heightmod=150;} else {$heightmod=120;}
if ($collections_compact_style){$heightmod=$heightmod+20;}?>
<script type="text/javascript">
var maxheight=window.innerHeight-<?php echo $heightmod?>;
if (isNaN(maxheight)){maxheight=document.documentElement.clientHeight-<?php echo $heightmod?>;}
if (maxheight><?php echo $imageheight?>){
	
	document.getElementById('image<?php echo htmlspecialchars($ref)?>').style.height='<?php echo $imageheight?>px';}
	else { document.getElementById('image<?php echo htmlspecialchars($ref)?>').style.height=maxheight+'px';} </script>
</div></div>
<?php if ($allow_reorder) { 
		# Javascript drag/drop enabling.
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#ResourceShel_<?php echo htmlspecialchars($ref)?>').draggable({ handle: ".ResourceShel_", revert: true });
				jQuery('#ResourceShel_<?php echo htmlspecialchars($ref)?>').droppable({
					accept: ".ResourceShel_",
					hoverclass: 'ReorderHover',
					drop: function(event, ui) {
						ReorderResources(ui.draggable.attr("id").substring(13),<?php echo htmlspecialchars($ref)?>,1);
					}
				});
			});
		</script>
		<?php } ?>
<?php if ($vertical=="v"){?><tr><?php } else  { ?></td> <?php } ?>
<?php } ?>
<?php $n++;
?>


</tr>
</table>

<script type="text/javascript">
<?php if ($preview_all_hide_collections){ ?>
	CollectionDivLoad("<?php echo $baseurl ?>/pages/collections.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&sort=<?php echo urlencode($sort)?>&thumbs=hide");
<?php } ?>

	window.onresize=function(event){
	var maxheight=window.innerHeight-<?php echo $heightmod?>;
    if (isNaN(maxheight)){maxheight=document.documentElement.clientHeight-<?php echo $heightmod?>;}
	jQuery('.Picture').each(function () {
		if (maxheight> jQuery(this).attr("imageheight").replace(/px,*\)*/g,"")){jQuery(this).height(jQuery(this).attr("imageheight")+'px'); }
		else { jQuery(this).height(maxheight+'px');} } );}
</script>
</form>
<?php
include "../include/footer.php";
?>
