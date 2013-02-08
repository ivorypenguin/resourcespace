<?php

function HookResourceconnectCollectionsThumblistextra()
	{
	global $usercollection, $lang, $baseurl;
	$thumbs=sql_query("select * from resourceconnect_collection_resources where collection='$usercollection' order by date_added desc");

	foreach ($thumbs as $thumb)
		{	
		?>
		<!--Resource Panel--> 
		<div class="CollectionPanelShell"> 

		<table border="0" class="CollectionResourceAlign"><tr><td> 
		<a href="<?php echo $baseurl ?>/plugins/resourceconnect/pages/view.php?url=<?php echo urlencode($thumb["url"]) ?>&k=<?php echo getval("k","") ?>&col=<?php echo $usercollection ?>" onClick="return CentralSpaceLoad(this,true);"><img border=0 src="<?php echo $thumb["thumb"] ?>" class="CollectImageBorder" /></a></td> 
		</tr></table> 

		<div class="CollectionPanelInfo"><a href="<?php echo $baseurl ?>/plugins/resourceconnect/pages/view.php?url=<?php echo urlencode($thumb["url"]) ?>&k=<?php echo getval("k","") ?>&col=<?php echo $usercollection ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo tidy_trim(i18n_get_translated($thumb["title"]),15) ?></a>&nbsp;</div> 

		<div class="CollectionPanelInfo"> 
		<a href="<?php echo $baseurl ?>/pages/collections.php?resourceconnect_remove=<?php echo $thumb["ref"] ?>&nc=<?php echo time() ?>" onClick="return CollectionDivLoad(this,false);">x <?php echo $lang["action-remove"]?></a></div>
		</div>
		<?php
		}
	}

function HookResourceconnectCollectionsCountresult($collection,$count)
	{
	return $count+sql_value("select count(*) value from resourceconnect_collection_resources where collection='$collection'",0);

	}

function HookResourceconnectCollectionsProcessusercommand()
	{
	if (getval("resourceconnect_remove","")!="")
		{
		sql_query("delete from resourceconnect_collection_resources where ref='" . getvalescaped("resourceconnect_remove","") . "'");
		}

	}
