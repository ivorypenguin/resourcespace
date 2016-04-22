<?php
#
# Unindex_field.php
#
#
# Removes Indexes for a field
#

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}
include "../../include/resource_functions.php";
include "../../include/image_processing.php";

set_time_limit(0);

# Unindex a single field
$field=getvalescaped("field","");
if ($field=="") {exit("Specify field with ?field=");}

# Unindex only resources in specified collection
$collectionid=getvalescaped("col", "");

# Fetch field info
$fieldinfo=sql_query("select * from resource_type_field where ref='$field'");$fieldinfo=$fieldinfo[0];

if (getval("submit","")!="")
	{
	echo "<pre>";
	
	$joinkeyword="";
	$joindata="";
	$condition = "";
	$conditionand = "";
	if ($collectionid != "")
			{
			$joinkeyword=" inner join collection_resource on collection_resource.resource=resource_keyword.resource "; 
			$joindata=" inner join collection_resource on collection_resource.resource=resource_data.resource "; 
			$condition = "where collection_resource.collection = '$collectionid' ";
			$conditionand = "and collection_resource.collection = '$collectionid' ";
			}
	
	
	# Delete existing keywords index for this field
	sql_query("delete resource_keyword.* from resource_keyword $joinkeyword where resource_type_field='$field' $conditionand");
	echo "Complete";
	}
else
	{
	$extratext="";
	if ($collectionid != "")
		{
		$collectionname=sql_value("select name as value from collection where ref='$collectionid'",'');
		$extratext=" for collection '" . $collectionname .  "'";
		}
	?>
	<form method="post" action="unindex_field.php">
	<input type="hidden" name="field" value="<?php echo $field ?>">
	<input type="hidden" name="col" value="<?php echo $collectionid ?>">
	<input type="submit" name="submit" value="Un-Index field '<?php echo $fieldinfo["title"] . "'" . $extratext ?>">
	</form>
	<?php
	}	
?>
