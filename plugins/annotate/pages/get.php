<?php


include_once "../../../include/db.php";
include_once "../../../include/general.php";
$k=getvalescaped("k","");if (($k=="") || (!check_access_key(getvalescaped("ref",""),$k))) {include_once "../../../include/authenticate.php";}

$ref=getval("ref","");
if ($ref==""){die("no");}
$preview_width=getval("pw",0);
$preview_height=getval("ph",0);
$page = getval('page', 1);

// Get notes based on page:
$sql_and = '';

if($page >= 1) {
	$sql_and = ' AND page = ' . $page;
}

$notes=sql_query("select * from annotate_notes where ref='$ref'" . $sql_and);
sql_query("update resource set annotation_count=".count($notes)." where ref=$ref");
// check if display size is different from original preview size, and if so, modify coordinates


$json="[";
$notes_array=array();
for ($x=0;$x<count($notes);$x++){
	

		
	$ratio=$preview_width/$notes[$x]['preview_width'];
			
	$notes[$x]['width']=$ratio*$notes[$x]['width'];
	$notes[$x]['height']=$ratio*$notes[$x]['height'];
	$notes[$x]['top_pos']=$ratio*$notes[$x]['top_pos'];
	$notes[$x]['left_pos']=$ratio*$notes[$x]['left_pos'];
	$notes[$x]['note'] = str_replace(chr(13). chr(10), '<br />\n', $notes[$x]['note']);
	$notes[$x]['note'] = str_replace(chr(13), '<br />\n', $notes[$x]['note']);
	$notes[$x]['note'] = str_replace(chr(10), '<br />\n', $notes[$x]['note']);
		if (!$annotate_show_author) # Don't display author unless set in config
			{$notes[$x]['note'] = substr(strstr($notes[$x]['note'],": "),2);}

	
	if ($x>0){$json.=",";}
	$json.="{";
	$json.='"top":'.round($notes[$x]['top_pos']).', ';
	$json.='"left":'.round($notes[$x]['left_pos']).', ';
	$json.='"width":'.round($notes[$x]['width']).', ';
	$json.='"height":'.round($notes[$x]['height']).', ';
	$json.='"text":"'.config_encode($notes[$x]['note']).'", ';
	$json.='"id":"'.$notes[$x]['note_id'].'", ';
	if (isset($userref) && (($notes[$x]['user']==$userref) || in_array($usergroup,$annotate_admin_edit_access)))
		{
		$json.='"editable":true';
		} 
	else 
		{
		$json.='"editable":false';	
		}
	$json.="}";
}
$json.="]";
echo $json;

