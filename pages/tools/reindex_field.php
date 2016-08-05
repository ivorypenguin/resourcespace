<?php
#
# Reindex_field.php
#
#
# Reindexes the resource metadata for a single field
#

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}
include "../../include/resource_functions.php";
include "../../include/image_processing.php";

set_time_limit(0);

# Reindex a single field
$field=getvalescaped("field","");
if ($field=="") {exit("Specify field with ?field=");}

# Reindex only resources in specified collection
$collectionid=getvalescaped("col", "");

# Fetch field info
$fieldinfo=sql_query("select * from resource_type_field where ref='$field'");$fieldinfo=$fieldinfo[0];
if (!$fieldinfo["keywords_index"]) {exit("Field is not set to be indexed.");}

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
	
	# Index fields
	$data=sql_query("select * from resource_data $joindata where resource_type_field='$field' and length(value)>0 $conditionand and value is not null");
	$n=0;
	$total=count($data);
	foreach ($data as $row)
		{
		$n++;
		$ref=$row["resource"];
		$value=$row["value"];
	
		if ($fieldinfo["type"]==3 || $fieldinfo["type"]==2 || $fieldinfo["type"]==9)
			{
			# Prepend a comma when indexing dropdowns to ensure full value is also indexed
			$value="," . $value;
			}
		
		# Date field? These need indexing differently.
		$is_date=($fieldinfo["type"]==4 || $fieldinfo["type"]==6);
		
		$is_html=($fieldinfo["type"]==8);	
		
	    # function add_keyword_mappings($ref,$string,$resource_type_field,$partial_index=false,$is_date=false)		
		add_keyword_mappings($ref,i18n_get_indexable($value),$field,$fieldinfo["partial_index"],$is_date,'','',$is_html);		
	
		hook("reindexfieldtooladditional","",array($ref,$value,$fieldinfo));
		
		echo "Done $ref - " . htmlspecialchars(substr($value,0,50)) . "... ($n/$total)\n";
		
		if (($n / 20 == floor($n/20)) || $n==$total) # Scroll down every now and again, and at the end.
			{
			?><script>window.scroll(0,document.height);</script><?php
			}
		flush();
		}
	echo "Reindex complete\n\n\n";
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
	<form method="post" action="reindex_field.php">
	<input type="hidden" name="field" value="<?php echo $field ?>">
	<input type="hidden" name="col" value="<?php echo $collectionid ?>">
	<input type="submit" name="submit" value="Reindex field '<?php echo $fieldinfo["title"] . "'" . $extratext ?>">
	</form>
	<?php
	}	
?>
