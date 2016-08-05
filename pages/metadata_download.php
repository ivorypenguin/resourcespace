<?php


include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";

$ref=getvalescaped ("ref","",true);
# fetch the current search (for finding similar matches)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);
$starsearch=getvalescaped("starsearch","");
$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);
$metadata=get_resource_field_data($ref);
$filename=$ref;
$download=getval("download","")!="";

if ($download)

		{
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=" . $lang["metadata"]."_". $filename . ".txt");

		foreach ($metadata as $metadata_entry) // Go through each entry
		     {
		     echo $metadata_entry['title']; // This is the field title - the function got this by joining to the resource_type_field in the sql query
		     echo ": ";
		     echo tidylist($metadata_entry['value']); // This is the value for the field from the resource_data table
		     echo "\n";   		
		     }	
     
 ob_flush();
 exit();
		
		}
	
include "../include/header.php";
?>

<body>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>

<h1><?php echo $lang["downloadingmetadata"]?></h1>

<p><?php echo $lang["file-contains-metadata"]?></p>


<div><a href="<?php echo $baseurl?>/pages/metadata_download.php?ref=<?php echo urlencode($ref)?>&download=true;">&gt; <?php echo $lang["textfile"] ?></a></div>


</body>



<?php
include "../include/footer.php";
?>