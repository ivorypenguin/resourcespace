<?php

# Quick script to check valid HTML
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/resource_functions.php";

echo "<pre>";

$text=getval("text","");

$html=trim($text);
$result=validate_html($html);
if ($result===true || $html=="")
    {
    echo "OK\n";
    }
else
    {
    echo "FAIL - $result \n";
    }
echo "</pre>";
?>
