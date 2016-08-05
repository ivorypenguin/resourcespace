<?php

include dirname(__FILE__) . "/../../../include/db.php";
include_once dirname(__FILE__) . "/../../../include/general.php";
include dirname(__FILE__) . "/../../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}

       
include dirname(__FILE__) . "/../../../include/admin_functions.php";


# Reordering capability

#  Check for the parameter and reorder as necessary.
$reorder=getvalescaped("reorder",false);
if ($reorder)
        {
        $neworder=json_decode(getvalescaped("order",false));
        update_resource_type_field_order($neworder);
        exit("SUCCESS");
        }
	
