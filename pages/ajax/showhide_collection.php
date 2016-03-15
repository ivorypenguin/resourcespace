<?php
# AJAX ratings save

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/resource_functions.php";
include_once "../../include/collections_functions.php";

if(getvalescaped("action","")=="showcollection")
	{
	show_hide_collection(getvalescaped("collection","",true), true, $userref);
	exit("UNHIDDEN");
	}
	
if(getvalescaped("action","")=="hidecollection")
	{
	show_hide_collection(getvalescaped("collection","",true), false, $userref);
	exit("HIDDEN");
	}
	
exit("no action specified");

?>
