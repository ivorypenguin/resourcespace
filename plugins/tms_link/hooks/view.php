<?php
function HookTms_linkViewRenderfield($field)
	{
	if(!checkperm("a")){return false;}
	global $baseurl,$tms_link_object_id_field,$search, $ref;
	if ($field["ref"]==$tms_link_object_id_field)
		{			
		$tmsid=$field["value"];
		$value=highlightkeywords($tmsid,$search,$field["partial_index"],$field["name"],$field["keywords_index"]);
		$title=htmlspecialchars($field["title"]);	
		?><div class="itemNarrow"><h3><?php echo $title?></h3><p><a href="<?php echo $baseurl ?>/plugins/tms_link/pages/tms_details.php?ref=<?php echo $ref ?>&tmsid=<?php echo $tmsid ?>"><?php echo $value?></a></p></div><?php
		return true;
		}
	return false;

	}