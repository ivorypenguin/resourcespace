<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/resource_functions.php";

$uniqid=getvalescaped("id","");
$user=getvalescaped("user",""); // Need to get this from query string since we haven't authenticated
$usertempdir=get_temp_dir(false,"rs_" . $user . "_" . $uniqid);
$progress_file=$usertempdir . "/progress_file.txt";
//$progress_file=get_temp_dir(false,$uniqid) . "/progress_file.txt";

if (!file_exists($progress_file)){
	touch($progress_file);
}

$content= file_get_contents($progress_file);
if ($content==""){echo $lang['preparingzip'];}

else if ($content=="zipping"){
	$files=scandir($usertempdir);
	echo "Zipping ";
		foreach ($files as $file){
			//echo $file;
			if (strpos($file,".zip")!==false){
				echo formatfilesize(filesize($usertempdir."/".$file));
			} 
		} 
	}

else {
	ob_start();echo $content;ob_flush();exit();} // echo whatever the script has placed here.
