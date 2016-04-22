<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("a")) {exit("Access denied.");}

$rev=getval("rev","");

if (is_numeric($rev)){
	svn_update($storagedir."/../",$rev);
}

redirect("plugins/svn/pages/svn.php");
