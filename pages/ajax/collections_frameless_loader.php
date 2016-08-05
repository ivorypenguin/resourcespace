<?php
include "../../include/db.php";
include "../../include/general.php";
include "../../include/collections_functions.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("collection","",true),$k))) {include "../../include/authenticate.php";}

include "../../include/research_functions.php";
include "../../include/resource_functions.php";
include "../../include/search_functions.php";

if(!hook("colframelessloader")):
include "../../pages/collections.php";
endif;
