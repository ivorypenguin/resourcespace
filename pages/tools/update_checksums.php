<?php
#
#
# Script to update the file checksum for existing files.
# This should be executed once, when checksums do not exist on the resources in the database, e.g. when upgrading from
# version 1.4 (which did not have the checksum feature) to 1.5
#
# If you would like to recreate all checksums (useful after adjusting $file_checksums_50k) you can pass "recreate=true"
#
$cwd = dirname(__FILE__);
include "$cwd/../../include/db.php";
//include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}
include_once "$cwd/../../include/general.php";
include "$cwd/../../include/image_processing.php";
include "$cwd/../../include/resource_functions.php";

$recreate=getvalescaped("recreate","");

if ($recreate==true) {
	$resources=sql_query("select ref,file_extension from resource where length(file_extension)>0 order by ref ASC");
}
else {
	$resources=sql_query("select ref,file_extension from resource where length(file_extension)>0 and (file_checksum is null or file_checksum = '')");
}
for ($n=0;$n<count($resources);$n++)
	{
	if (generate_file_checksum($resources[$n]["ref"],$resources[$n]["file_extension"],true)){
		echo "Key for " . $resources[$n]["ref"] . " generated<br />\n";
	} else {
		echo "Key for " . $resources[$n]["ref"] . " NOT generated<br />\n";
	}
}
?>
