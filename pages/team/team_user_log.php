<?php
/**
 * User log display page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include "../../include/authenticate.php";
include "../../include/general.php";
include "../../include/resource_functions.php";

$offset=getvalescaped("offset",0);
$ref=getvalescaped("ref","",true);
$userdata=get_user($ref);
$backurl=getval("backurl","");

# pager
$per_page=getvalescaped("per_page_list_log",15);setcookie("per_page_list_log",$per_page);

include "../../include/header.php";
$log=get_user_log($ref, $offset+$per_page);
$results=count($log);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;

$url=$baseurl . "/pages/team/team_user_log.php?ref=" . $ref . "&backurl=" . urlencode($backurl);
$jumpcount=1;

?>
<div class="BasicsBox">
<p><a href="<?php echo $backurl?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["manageusers"]?></a></p>
<h1><?php echo $lang["userlog"] . ": " . $userdata["fullname"]?></h1>
<div class="TopInpageNav">
<div class="InpageNavLeftBlock"><?php echo $lang["resultsdisplay"]?>:
	<?php 
	for($n=0;$n<count($list_display_array);$n++){?>
	<?php if ($per_page==$list_display_array[$n]){?><span class="Selected"><?php echo $list_display_array[$n]?></span><?php } else { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $url; ?>&per_page_list_log=<?php echo $list_display_array[$n]?>"><?php echo $list_display_array[$n]?></a><?php } ?>&nbsp;|
	<?php } ?>
	<?php if ($per_page==99999){?><span class="Selected"><?php echo $lang["all"]?></span><?php } else { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $url; ?>&per_page_list_log=99999"><?php echo $lang["all"]?></a><?php } ?>
	</div> <?php pager(false); ?></div>


<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["resourceid"]?></td>
<td><?php $field=get_fields(array($view_title_field)); if(isset($field[0])){echo lang_or_i18n_get_translated($field[0]["title"], "fieldtitle-");}?></td>
<td><?php echo $lang["action"]?></td>
<td><?php echo $lang["field"]?></td>
</tr>

<?php

for ($n=$offset;(($n<count($log))&& ($n<($offset+$per_page)));$n++)
	{
	if (!isset($lang["log-".$log[$n]["type"]])){$lang["log-".$log[$n]["type"]]="";}	
	?>
	<!--List Item-->
	<tr>
	<td><?php echo $log[$n]["date"]?></td>
	<td><a onClick="return CentralSpaceLoad(this,true);" href='<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $log[$n]["resourceid"]?>'><?php echo $log[$n]["resourceid"]?></a></td>
	<td><a onClick="return CentralSpaceLoad(this,true);" href='<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $log[$n]["resourceid"]?>'><?php echo i18n_get_translated($log[$n]["resourcetitle"])?></a></td>
	<td><?php echo $lang["log-" . $log[$n]["type"]];

	# For emailed items, append email address from the 'notes' column
	if ($log[$n]["type"]=="E") { echo " " . $log[$n]["notes"]; }
	
	# For purchases, append size and price
	if ($log[$n]["type"]=="p") {echo " (" . ($log[$n]["purchase_size"]==""?$lang["collection_download_original"]:$log[$n]["purchase_size"]) . ", " . $currency_symbol . number_format($log[$n]["purchase_price"],2) . ")";}

	?></td>
	<td><?php echo $log[$n]["title"] ?></td>
	</tr>
	<?php
	}
?>
</table>
</div> <!-- End of Listview -->

<div class="BottomInpageNav">
<?php pager(false); ?></div>

</div> <!-- End of BasicsBox -->

<?php
include "../../include/footer.php";
?>
