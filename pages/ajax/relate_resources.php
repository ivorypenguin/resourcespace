<?php

include_once('../../include/db.php');
include_once('../../include/general.php');
include_once('../../include/authenticate.php');
include_once('../../include/resource_functions.php');

$ref = intval(getvalescaped('ref','',true));
$related = intval(getvalescaped('related','',true));
$add=(getvalescaped('action','add')=="add");


if(!get_edit_access($ref) || !get_edit_access($related)){exit($lang["error-permissiondenied"]);}
//echo $ref;
//echo $related;
//echo ($add)?"Adding":"removing";

$update=update_related_resource($ref,$related,$add);
if(!$update){exit ("error");}
exit("SUCCESS");

