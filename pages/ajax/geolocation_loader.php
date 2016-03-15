<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/resource_functions.php";

$ref=getvalescaped("ref","",true);
$k=getvalescaped("k","");if (($k=="") || (!check_access_key($ref,$k))) {include "../../include/authenticate.php";}


//Get resource info and access, would usually be available as included in view.php 

# Load resource data
$resource=get_resource_data($ref);
if ($resource===false) {exit($lang['resourcenotfound']);}

# Load resource field data
$fields=get_resource_field_data($ref,false,!hook("customgetresourceperms"),-1,$k!="",$use_order_by_tab_view);

$edit_access=get_edit_access($ref,$resource["archive"],$fields,$resource);
if ($k!="") {$edit_access=0;}

$geolocation_panel_only=true; // If we are here we have specifically requested it so make sure it is displayed
include "../../include/geocoding_view.php";
