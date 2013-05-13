<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/collections_functions.php";

$ref=getvalescaped("ref","",true);

include "../include/header.php";
?>

<?php
# Fetch and translate collection name
$colinfo = get_collection($ref);
$colname = i18n_get_collection_name($colinfo);
if (!checkperm("b"))
    {
    # Add selection link to collection name.
    $colname = "<a href=\"" . $baseurl_short . "pages/collections.php?collection=" . $ref . "\" onClick=\"return CollectionDivLoad(this);\">" . $colname . "</a>";
    }
?>

<div class="BasicsBox">
<?php if ($back_to_collections_link != "") { ?><div style="float:right;"><a href="<?php echo $baseurl_short?>pages/collection_manage.php" onClick="return CentralSpaceLoad(this,true);"><strong><?php echo $back_to_collections_link ?></strong> </a></div> <?php } ?>
<h1><?php echo str_replace("%collection", $colname, $lang["collectionlogheader"]);?></h1>

</div>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["user"]?></td>
<td><?php echo $lang["action"]?></td>
<td><?php echo $lang["resourceid"]?></td>
<td><?php $field=get_fields(array($view_title_field)); echo lang_or_i18n_get_translated($field[0]["title"], "fieldtitle-");?></td>
</tr>

<?php
$log=get_collection_log($ref);
for ($n=0;$n<count($log);$n++)
	{
	?>
	<!--List Item-->
	<tr>
	<td><?php echo htmlspecialchars(nicedate($log[$n]["date"],true)) ?></td>
	<td><?php echo htmlspecialchars($log[$n]["username"]) ?> (<?php echo htmlspecialchars($log[$n]["fullname"])?>)</td>
	<td><?php 
		echo $lang["collectionlog-" . $log[$n]["type"]] ;
		if ($log[$n]["notes"] != "" ) { 
			##  notes field contains user IDs, collection references and /or standard texts
			##  Translate the standard texts
			$standard = array('#all_users', '#new_resource');
			$translated   = array($lang["all_users"], $lang["new_resource"]);
			$newnotes = str_replace($standard, $translated, $log[$n]["notes"]);
			echo $newnotes;
		}
		?></td>
	<td><?php if ($log[$n]['resource']!=0){?><a onClick="return CentralSpaceLoad(this,true);" href='<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($log[$n]["resource"]) ?>'><?php echo $log[$n]["resource"]?></a><?php } ?></td>
	<td><?php if ($log[$n]['resource']!=0){?><a onClick="return CentralSpaceLoad(this,true);" href='<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($log[$n]["resource"]) ?>'><?php echo i18n_get_translated($log[$n]["title"])?></a><?php } ?></td>
	</tr> 
<?php } ?>
</table>
</div>
<?php
include "../include/footer.php";
?>
