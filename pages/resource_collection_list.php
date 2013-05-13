<?php 

include "../include/db.php";
include "../include/general.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if ($k!=""){die();} 
include("../include/authenticate.php");
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";

$ref=getvalescaped("ref","",true);
$collections=get_resource_collections($ref);

if (count($collections)!=0){
?>

        <div class="RecordBox">
        <div class="RecordPanel">  
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
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
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
<?php hook("beforecollectiontoolscolumn");?>
	<td><div class="ListTools">
    <?php if ($collections_compact_style){
    draw_compact_style_selector($collections[$n]["ref"]); } else { ?>
    <a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $collections[$n]["ref"])?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a>
        <?php if (!checkperm("b")) { ?>
            &nbsp;<a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $collections[$n]["ref"]; if ($autoshow_thumbs) {echo "&amp;thumbs=show";}?>" onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a>
    <?php } ?>

	<?php if (isset($zipcommand) || $collection_download) { ?>
	&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_download.php?collection=<?php echo $collections[$n]["ref"]?>"
	>&gt;&nbsp;<?php echo $lang["action-download"]?></a>
	<?php } ?>
	
	<?php if ($contact_sheet==true) { ?>
    &nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/contactsheet_settings.php?ref=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["contactsheet"]?></a>
	<?php } ?>

	<?php if ($allow_share && (checkperm("v") || checkperm ("g"))) { ?> &nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_share.php?ref=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["share"]?></a><?php } ?>
	
	<!--<?php if ($username!=$collections[$n]["username"])	{?>&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["removecollectionareyousure"]?>')) {document.getElementById('collectionremove').value='<?php echo $collections[$n]["ref"]?>';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang["action-remove"]?></a><?php } ?>-->

	<!--<?php if ((($username==$collections[$n]["username"]) || checkperm("h")) && ($collections[$n]["cant_delete"]==0)) {?>&nbsp;<a href="#" onclick="if (confirm('<?php echo $lang["collectiondeleteconfirm"]?>')) {document.getElementById('collectiondelete').value='<?php echo $collections[$n]["ref"]?>';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang["action-delete"]?></a><?php } ?>-->

	<?php if (($username==$collections[$n]["username"]) || (checkperm("h"))) {?>&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_edit.php?ref=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["action-edit"]?></a>&nbsp;<?php } ?>
    <?php     # If this collection is (fully) editable, then display an edit all link
    if (($collections[$n]["count"] >0) && allow_multi_edit($collections[$n]["ref"]) && $show_edit_all_link ) { ?>
    &nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?collection=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["action-editall"]?></a>&nbsp;<?php } ?>

	<?php if (($username==$collections[$n]["username"]) || (checkperm("h"))) {?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_log.php?ref=<?php echo $collections[$n]["ref"]?>">&gt;&nbsp;<?php echo $lang["log"]?></a><?php } ?>
	
	</td>
	</tr><?php
	}
}
?>
</table></div>
        </div>
        <div class="PanelShadow"></div>
        </div>
<?php } ?>
