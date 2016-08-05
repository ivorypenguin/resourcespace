<?php

include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/resource_functions.php";
include "../inc/flickr_functions.php";

$uniqid=getvalescaped("id","");

$progress_folder=get_temp_dir(false,$uniqid);
$progress_file=$progress_folder . "/progress_file.txt";

if (!file_exists($progress_file)){
	touch($progress_file);
}

$content=file_get_contents($progress_file);
// echo whatever the script has placed here.
ob_start();
echo $content;
//if($content==$lang["done"]){
if (strpos($content,$lang["done"]) !== FALSE){
	unlink($progress_file);
	rmdir($progress_folder);
}
ob_flush();
exit();
