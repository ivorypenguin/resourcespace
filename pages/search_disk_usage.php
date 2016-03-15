<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/resource_functions.php"; //for checking scr access
include "../include/search_functions.php";
include_once "../include/collections_functions.php";
include "../include/authenticate.php";

$search=getvalescaped("search","");
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
$starsearch=getvalescaped("starsearch","");
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

$results=do_search(getval("search",""),getvalescaped("restypes",""),"relevance",getval("archive",""),-1,"desc",false,$starsearch,false,true,getvalescaped("daylimit",""));
$disk_usage=$results[0]["total_disk_usage"];
$count=$results[0]["total_resources"];

include ("../include/header.php");

?>

<h1><?php echo $lang["searchitemsdiskusage"] ?></h1>

<div class="Question">
<label><?php echo $lang["matchingresourceslabel"] ?></label>
<div class="Fixed"><?php echo number_format($count)  ?></div>
<div class="clearerleft"></div>
</div>

<div class="Question">
<label><?php echo $lang["diskusage"] ?></label>
<div class="Fixed"><strong> <?php echo formatfilesize($disk_usage) ?></strong></div>
<div class="clearerleft"></div>
</div>


<?php


include ("../include/footer.php");
