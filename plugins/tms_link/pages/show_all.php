<?php
include '../../../include/db.php';
include "../../../include/authenticate.php";
if(!checkperm("t")){exit ("Access denied"); }
include_once "../../../include/general.php";
include "../../../include/resource_functions.php";
include "../include/tms_link_functions.php";



$tms_resources=tms_link_get_tms_resources();

include "../../../include/header.php";

echo "<h2>" . $lang["tms_link_tms_resources"] . "</h2>";
echo "<div class='Listview'>";
echo "<table style='border=1;'>";
echo "<tr>"; 
	echo "<td><strong>Resource ID</strong></td>";
	echo "<td><strong>" . $lang["tms_link_object_id"] . "</strong></td>";	
	echo "<td><strong>" . $lang["tms_link_checksum"] . "</td>";	
	echo "</tr>";

$tmscount=count($tms_resources);
if($tmscount!=0)
	{	
	for ($t=0;$t<$tmscount && $t<$tms_link_test_count;$t++)
		{
		echo "<tr>"; 
		echo "<td><a href='" . $baseurl . "/?r=" .  $tms_resources[$t]["resource"] . "' onClick='CentralSpaceload(this,true);return false;'>" . $tms_resources[$t]["resource"] . "</a></td>";
		echo "<td><a href='" . $baseurl . "/plugins/tms_link/pages/test.php?ref=" .  $tms_resources[$t]["resource"] . "' onClick='CentralSpaceload(this,true);return false;'>" . $tms_resources[$t]["objectid"] . "</a></td>";	
		echo "<td>" . $tms_resources[$t]["checksum"] . "</td>";	
		echo "</tr>";
		}
		
	}
else
	{
	echo "<td colspan='3'>" . $lang["tms_link_no_tms_resources"] . "</td>";	
	}

echo "</table>";
echo "</div>";	



	
include "../../../include/footer.php";