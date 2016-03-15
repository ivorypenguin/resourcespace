<?php
include dirname(__FILE__) . "/../../../include/db.php";
include_once dirname(__FILE__) . "/../../../include/general.php";
include dirname(__FILE__) . "/../../../include/authenticate.php";

include_once dirname(__FILE__) . "/../../../include/node_functions.php";

$field=getvalescaped("field","");
$keyword=getval("term","");

$fielddata=get_resource_type_field($field);
node_field_options_override($fielddata);

$readonly=getval("readonly","");

# Return matches
$first=true;
$exactmatch=false;

$results=array();

for ($m=0;$m<count($fielddata['node_options']);$m++)
	{
	$trans=i18n_get_translated($fielddata['node_options'][$m]);
	if ($trans!="" && substr(strtolower($trans),0,strlen($keyword))==strtolower($keyword))
		{
		if (strtolower($trans)==strtolower($keyword)) {$exactmatch=true;}
		$results[]= $trans;
		}
	}
	
if (!$exactmatch && !$readonly)
	{
	$results[] = htmlspecialchars($lang["createnewentryfor"] . " " . $keyword);
	}
elseif($readonly && empty($results)){
	$results[] = htmlspecialchars($lang["noentryexists"] . " " . $keyword);
}

echo json_encode($results);
exit();
