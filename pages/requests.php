<?php
/**
 * View my own requests
 * 
 * @package ResourceSpace
 */
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/request_functions.php";
include "../include/collections_functions.php";

$offset=getvalescaped("offset",0);

include "../include/header.php";
?>


<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["myrequests"]?></h1>
  <p><?php echo text("introtext")?></p>
 
<?php 
$requests=get_user_requests();

# pager
$per_page=10;
$results=count($requests);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$url="requests.php?";
$jumpcount=1;

?><div class="TopInpageNav"><?php pager();	?></div>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td><?php echo $lang["requestorderid"]?></td>
<td><?php echo $lang["description"]?></td>
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["itemstitle"]?></td>
<td><?php echo $lang["status"]?></td>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
$statusname=array("","","","");
$requesttypes=array("","","","");

for ($n=$offset;(($n<count($requests)) && ($n<($offset+$per_page)));$n++)
	{
	?>
	<tr>
	<td><?php echo $requests[$n]["ref"]?></td>
	<td><?php echo $requests[$n]["comments"] ?></td>
	<td><?php echo nicedate($requests[$n]["created"],true)?></td>
	<td><?php echo $requests[$n]["c"] ?></td>
	<td><?php echo $lang["resourcerequeststatus" . $requests[$n]["status"]] ?></td>
	<td>
	<div class="ListTools">
	<?php if ($requests[$n]["collection_id"] > 0) // only show tools if the collection still exists
        {?>
        <a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $requests[$n]["collection"])?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-view"]?></a>
        <?php if (!checkperm("b"))
            {?>
            &nbsp;<a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $requests[$n]["collection"]; if ($autoshow_thumbs) {echo "&amp;thumbs=show";}?>" onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a><?php
            }
        } // end of if collection still exists ?>
	</div>
	</td>
	</tr>
	<?php
	}
?>

</table>
</div><!--end of Listview -->
<div class="BottomInpageNav"><?php pager(false); ?>
</div>
</div><!-- end of BasicsBox -->




<?php
include "../include/footer.php";
?>
