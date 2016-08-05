<?php 

include "../include/db.php";
include_once "../include/general.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if ($k!=""){die();} 
include("../include/authenticate.php");
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/render_functions.php";

include_once "../include/collections_functions.php";

$ref=getvalescaped("ref","",true);
$collections=get_resource_collections($ref);

if (count($collections)!=0){
?>

        <div class="RecordBox">
        <div class="RecordPanel">
        <div id="AssociatedCollections"> 
        <div class="Title"><?php echo $lang['associatedcollections']?></div>

<div class="Listview nopadding" >
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td><?php echo $lang["collectionname"]?></td>
<td><?php echo $lang["owner"]?></td>
<td><?php echo $lang["id"]?></td>
<td><?php echo $lang["created"]?></td>
<td><?php echo $lang["itemstitle"]?></td>
<?php if (! $hide_access_column){ ?><td><?php echo $lang["access"]?></td><?php } ?>
	<?php hook("beforecollectiontoolscolumnheader");?>
<td><div class="ListTools"><?php echo $lang["actions"]?></div></td>
</tr>
<?php

for ($n=0;$n<count($collections);$n++)
	{	
	?><tr <?php hook("collectionlistrowstyle");?>>
	<td><div class="ListTitle">
    <a onClick="return CentralSpaceLoad(this,true);" <?php if ($collections[$n]["public"]==1 && (strlen($collections[$n]["theme"])>0)) { ?>style="font-style:italic;"<?php } ?> href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>"><?php echo i18n_get_collection_name($collections[$n])?></a></div></td>
	<td><?php echo htmlspecialchars($collections[$n]["fullname"])?></td>
	<td><?php echo $collection_prefix . $collections[$n]["ref"]?></td>
	<td><?php echo nicedate($collections[$n]["created"],true)?></td>
	<td><?php echo $collections[$n]["count"]?></td>
<?php if (! $hide_access_column){ ?>	<td><?php
# Work out the correct access mode to display
if ($collections[$n]["public"]==0)
	{
	echo $lang["private"];
	}
else
	{
	if (strlen($collections[$n]["theme"])>0)
		{
		echo $lang["theme"];
		}
	else
		{
		echo $lang["public"];
		}
	}
?></td><?php
}
?>
<?php hook('beforecollectiontoolscolumn'); ?>
	<td>
		<div class="ListTools">
		<?php
		hook('render_resource_collection_list_list_tools', '', array($collections[$n]));
		render_actions($collections[$n], false, false);
		?>
		</div>
	</td>
</tr>
<?php
}
?>
</table></div>
        </div>
        </div>
        
        </div>
<?php } ?>
