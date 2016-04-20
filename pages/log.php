<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";
include "../include/search_functions.php";
include "../include/collections_functions.php";

$ref=getvalescaped("ref","",true);
$k=getvalescaped("k","");

// Logs can sometimes contain confidential information and the user looking at them must have admin permissions set
if(!checkperm('v'))
{
	die($lang['log-adminpermissionsrequired']);
}

# fetch the current search (for finding simlar matches)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$search_offset=getvalescaped("search_offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);
$starsearch=getvalescaped("starsearch","");
$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

$offset=getvalescaped("offset",0);
$per_page=getvalescaped("per_page_list",15);rs_setcookie('per_page_list', $per_page);



# next / previous resource browsing
$go=getval("search_go","");
if ($go!="")
	{
	$origref=$ref; # Store the reference of the resource before we move, in case we need to revert this.
	
	# Re-run the search and locate the next and previous records.
	$modified_result_set=hook("modifypagingresult"); 
	if ($modified_result_set){
		$result=$modified_result_set;
	} else {
		$result=do_search($search,$restypes,$order_by,$archive,240+$search_offset+1,$sort,false,$starsearch);
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
			if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($search_offset+72)) {$search_offset=$pos+1;}} # move to next page if we've advanced far enough
			}
		else
			{
			?>
			<script type="text/javascript">
			alert('<?php echo $lang["resourcenotinresults"] ?>');
			</script>
			<?php
			}
		}
	# Check access permissions for this new resource, if an external user.
	$newkey=hook("nextpreviewregeneratekey");
	if (is_string($newkey)) {$k=$newkey;}
	if ($k!="" && !check_access_key($ref,$k)) {$ref=$origref;} # cancel the move.
	}


include "../include/header.php";
?>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($search_offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>


<div class="backtoresults">
<a href="<?php echo $baseurl_short?>pages/log.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&search_offset=<?php echo urlencode($search_offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k) ?>&search_go=previous&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["previousresult"]?></a>
<?php 
hook("viewallresults");
if ($k=="") { ?>
|
<a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($search_offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["viewallresults"]?></a>
<?php } ?>
|
<a href="<?php echo $baseurl_short?>pages/log.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&search_offset=<?php echo urlencode($search_offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&k=<?php echo urlencode($k)?>&search_go=next&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["nextresult"]?>&nbsp;&gt;</a>
</div>

<h1><?php echo $lang["resourcelog"] . " : " . $lang["resourceid"] . " " .  htmlspecialchars($ref) ?></h1>

</div>

<?php
# Fetch the log.
$log=get_resource_log($ref);

# Calculate pager vars.
$results=count($log);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;

$url=$baseurl_short."pages/log.php?ref=" . urlencode($ref) . "&search=" . urlencode($search) . "&search_offset=" . urlencode($search_offset) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&archive=" . urlencode($archive) . "&k=" . urlencode($k) . hook("nextpreviousextraurl");
?>

<div class="TopInpageNav"><!--<?php pager(false); ?></div>-->
<div class="InpageNavLeftBlock"><?php echo $lang["resultsdisplay"]?>:
	<?php 
	for($n=0;$n<count($list_display_array);$n++){?>
	<?php if ($per_page==$list_display_array[$n]){?><span class="Selected"><?php echo $list_display_array[$n]?></span><?php } else { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $url; ?>&per_page_list=<?php echo $list_display_array[$n]?>"><?php echo $list_display_array[$n]?></a><?php } ?>&nbsp;|
	<?php } ?>
	<?php if ($per_page==99999){?><span class="Selected"><?php echo $lang["all"]?></span><?php } else { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $url; ?>&per_page_list=99999"><?php echo $lang["all"]?></a><?php } ?>
	</div> <?php pager(false); ?></div>


<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td width="10%"><?php echo $lang["date"]?></td>
<td width="10%"><?php echo $lang["user"]?></td>
<td width="10%"><?php echo $lang["action"]?></td>
<td width="10%"><?php echo $lang["field"]?></td>
<td><?php echo $lang["difference"]?></td>
<?php hook("log_extra_columns_header") ?>
</tr>

<?php
for ($n=$offset;(($n<count($log)) && ($n<($offset+$per_page)));$n++)
	{
	if (!isset($lang["log-".$log[$n]["type"]])){$lang["log-".$log[$n]["type"]]="";}
	?>
	<!--List Item-->
	<tr>
	<td nowrap><?php echo nicedate($log[$n]["date"],true,true)?></td>
	<td nowrap><?php echo $log[$n]["access_key"]!=""?$lang["externalusersharing"] . ": " . $log[$n]["access_key"] . " " . $lang["viauser"] . " " . $log[$n]["shared_by"]:$log[$n]["fullname"]?></td>
	<td><?php echo $lang["log-" . $log[$n]["type"]]." ".$log[$n]["notes"]?></td>
	<td><?php echo htmlspecialchars($log[$n]["title"])?></td>
	<td><?php
    if($log[$n]["diff"]!=="")
        {
        echo nl2br(format_string_more_link(htmlspecialchars($log[$n]["diff"])));
        }
    if ($log[$n]["usageoption"]!="-1")
        {
        // if usageoption is set to -1 when logging, you can avoid the usage description here
        echo (($log[$n]["notes"]=="" || $log[$n]["notes"]=="-1")? "" :
            $lang["usage"] . ": " . nl2br(htmlspecialchars($log[$n]["notes"])) . "<br>" . $lang["indicateusagemedium"] . ": " . @$download_usage_options[$log[$n]["usageoption"]]);
	    }

	# For purchases, append size and price
	if ($log[$n]["type"]=="p")
        {
        echo " (" . ($log[$n]["purchase_size"]==""?$lang["collection_download_original"]:$log[$n]["purchase_size"]) . ", " . $currency_symbol . number_format($log[$n]["purchase_price"],2) . ")";
        }
	
	# For downloads, add size 
	if ($log[$n]["type"]=="d")
        {
        echo " (" . ($log[$n]["size"]==""?$lang["collection_download_original"]:$log[$n]["size"]) . ")";
        }

        hook("log_diff_td_extra","",array($ref));
	?></td>
	<?php hook("log_extra_columns_row") ?>
	</tr>
	<?php
	}
?>
</table>
</div>

<div class="BottomInpageNav"><?php pager(false); ?></div>

<?php
include "../include/footer.php";
?>
