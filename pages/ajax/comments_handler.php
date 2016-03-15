<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/comment_functions.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") if (!empty($username)) comments_submit();	

$ref = (!empty ($_GET['ref'])) ? $_GET['ref'] : "";	
$collection_mode = (!empty ($_GET['collection_mode']));				
comments_show($ref, $collection_mode);				
		
?>
