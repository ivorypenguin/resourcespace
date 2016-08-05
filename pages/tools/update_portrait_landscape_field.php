<?php

# This script is for updating the $portrait_landscape_field 

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}
include "../../include/resource_functions.php";
include "../../include/image_processing.php";

set_time_limit(60*60*40);

echo "Updating portrait_landscape_field (field $portrait_landscape_field)...";

$rd=sql_query("select ref,file_extension from resource where has_image=1 ");
for ($n=0;$n<count($rd);$n++)
	{
	$ref=$rd[$n]['ref'];
	echo "<br />" . $ref;
	update_portrait_landscape_field($ref);flush();ob_flush();
	}
echo "...done.";

?>
