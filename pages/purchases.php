<?php 
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; if (checkperm("b")){exit("Permission denied");}
#if (!checkperm("s")) {exit ("Permission denied.");}
include_once "../include/collections_functions.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";

$offset=getvalescaped("offset",0,true);
$find=getvalescaped("find",getvalescaped("saved_find",""));rs_setcookie('saved_find', $find);
$col_order_by=getvalescaped("col_order_by",getvalescaped("saved_col_order_by","created"));rs_setcookie('saved_col_order_by', $col_order_by);
$sort=getvalescaped("sort",getvalescaped("saved_col_sort","ASC"));rs_setcookie('saved_col_sort', $sort);
$revsort = ($sort=="ASC") ? "DESC" : "ASC";
# pager
$per_page=getvalescaped("per_page_list",$default_perpage_list,true);rs_setcookie('per_page_list', $per_page);

$collection_valid_order_bys=array("fullname","name","ref","count","public");
$modified_collection_valid_order_bys=hook("modifycollectionvalidorderbys");
if ($modified_collection_valid_order_bys){$collection_valid_order_bys=$modified_collection_valid_order_bys;}
if (!in_array($col_order_by,$collection_valid_order_bys)) {$col_order_by="created";} # Check the value is one of the valid values (SQL injection filter)

if (array_key_exists("find",$_POST)) {$offset=0;} # reset page counter when posting

$name=getvalescaped("name","");

$delete=getvalescaped("delete","");
if ($delete!="")
	{
	# Delete collection
	delete_collection($delete);

	# Get count of collections
	$c=get_user_collections($userref);
	
	# If the user has just deleted the collection they were using, select a new collection
	if ($usercollection==$delete && count($c)>0)
		{
		# Select the first collection in the dropdown box.
		$usercollection=$c[0]["ref"];
		set_user_collection($userref,$usercollection);
		}

	# User has deleted their last collection? add a new one.
	if (count($c)==0)
		{
		# No collections to select. Create them a new collection.
		$name=get_mycollection_name($userref);
		$usercollection=create_collection ($userref,$name);
		set_user_collection($userref,$usercollection);
		}

	refresh_collection_frame($usercollection);
	}

$remove=getvalescaped("remove","");
if ($remove!="")
	{
	# Remove someone else's collection from your My Collections
	remove_collection($userref,$remove);
	
	# Get count of collections
	$c=get_user_collections($userref);
	
	# If the user has just removed the collection they were using, select a new collection
	if ($usercollection==$remove && count($c)>0) {
		# Select the first collection in the dropdown box.
		$usercollection=$c[0]["ref"];
		set_user_collection($userref,$usercollection);
		}
	
	refresh_collection_frame();
	}

$reload=getvalescaped("reload","");
if ($reload!="")
	{
	# Refresh the collection frame (just edited a collection)
	refresh_collection_frame();
	}


include "../include/header.php";
?>
  <div class="BasicsBox">
    <h1><?php echo $lang["viewpurchases"]?></h1>
    <p class="tight"><?php echo $lang["viewpurchasesintro"]?></p><br>

<?php

$collections=get_user_collections($userref,$find,$col_order_by,$sort);

$results=count($collections);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$jumpcount=1;



$url=$baseurl_short."pages/my_purchases.php?paging=true&col_order_by=".$col_order_by."&sort=".$sort."&find=".urlencode($find)."";

	?>
  
<form method=post id="collectionform" action="<?php echo $baseurl_short?>pages/my_purchases.php">
<input type=hidden name="delete" id="collectiondelete" value="">
<input type=hidden name="remove" id="collectionremove" value="">
<input type=hidden name="add" id="collectionadd" value="">

<?php


// count how many collections are owned by the user versus just shared, and show at top
$mycollcount = 0;
$othcollcount = 0;
for($i=0;$i<count($collections);$i++){
	if ($collections[$i]['user'] == $userref){
		$mycollcount++;
	} else {
		$othcollcount++;
	}
}

$collcount = count($collections);
echo $collcount==1 ? $lang["total-collections-1"] : str_replace("%number", $collcount, $lang["total-collections-2"]);
# The number of collections should never be equal to zero.
?>
<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td><?php if ($col_order_by=="name") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/my_purchases.php?offset=0&col_order_by=name&sort=<?php echo $revsort?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["orderdate"]?></a><?php if ($col_order_by=="name") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td><?php if ($col_order_by=="ref") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/my_purchases.php?offset=0&col_order_by=ref&sort=<?php echo $revsort?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a><?php if ($col_order_by=="ref") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td><?php if ($col_order_by=="created") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/my_purchases.php?offset=0&col_order_by=created&sort=<?php echo $revsort?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["created"]?></a><?php if ($col_order_by=="created") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<td><?php if ($col_order_by=="count") {?><span class="Selected"><?php } ?><a href="<?php echo $baseurl_short?>pages/my_purchases.php?offset=0&col_order_by=count&sort=<?php echo $revsort?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["itemstitle"]?></a><?php if ($col_order_by=="count") {?><div class="<?php echo urlencode($sort)?>">&nbsp;</div><?php } ?></td>

<?php hook("beforecollectiontoolscolumnheader");?>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>
<form method="get" name="colactions" id="colactions" action="<?php echo $baseurl_short?>pages/my_purchases.php">
<?php

for ($n=$offset;(($n<count($collections)) && ($n<($offset+$per_page)));$n++)
	{
    $colusername=$collections[$n]['fullname'];

	?><tr <?php hook("collectionlistrowstyle");?>>
	<td><div class="ListTitle">
		<a <?php if ($collections[$n]["public"]==1 && (strlen($collections[$n]["theme"])>0)) { ?>style="font-style:italic;"<?php } ?> href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>" onClick="return CentralSpaceLoad(this);"><?php echo highlightkeywords(i18n_get_collection_name($collections[$n]),$find)?></a></div></td>
	<td><?php echo highlightkeywords($collection_prefix . $collections[$n]["ref"],$find)?></td>
	<td><?php echo nicedate($collections[$n]["created"],true)?></td>
	<td><?php echo $collections[$n]["count"]?></td>

<?php hook("beforecollectiontoolscolumn");?>
	<td>	
        <div class="ListTools">
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a>
	
	<?php if ($contact_sheet==true && $manage_collections_contact_sheet_link) { ?>
    &nbsp;<a href="<?php echo $baseurl_short?>pages/contactsheet_settings.php?ref=<?php echo $collections[$n]["ref"]?>" onClick="return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang["contactsheet"]?></a>
	<?php } ?>

	<?php hook("addcustomtool"); ?>
	
	</td>
	</tr><?php
}

	?>

</table>
</div>

</form>
<div class="BottomInpageNav"><?php pager(false); ?></div>
</div>


<?php		
include "../include/footer.php";
?>
