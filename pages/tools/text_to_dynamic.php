<?php
#
# text_to_dynamic.php
#
#
# Converts a text field to a dynamic dropdown field and automatically populates the options.
# Useful for migrating a text "keywords" field that has been used for free text keywords to options
#
# IMPORTANT -reindex the field afterwards.
#
#

include "../../include/db.php";
include "../../include/authenticate.php";
include_once "../../include/general.php";
include "../../include/resource_functions.php";
include "../../include/image_processing.php";

set_time_limit(0);
echo "<pre>";
echo "..";

function normalize_values($values)
    {
    # Return a "Standardised" list of keywords from a string as users are likely to have entered / separated / spaced them differently.
    $separators=array(".",",","/","\n","\r");
    return array_filter(array_unique(array_map('trim',explode(",",ucwords(str_replace($separators,",",$values))))));
    }
    
$field=getvalescaped("field","");
if ($field=="") {exit("Please specify a field with the parameter ?field=[id]");}

# Work out a master list of values
$values=sql_array("select distinct trim(value) value from resource_data where resource_type_field='$field' and resource>0");
$values=join(", ",$values);
$values=normalize_values($values); # Reconnect every item and trim them.

print_r($values);

# Update the field with the master values list.
//sql_query("update resource_type_field set options='" . escape_check(join(", ",$values)) . "',type=9 where ref='$field'");

foreach ($values as $value)
    {
    set_node(null,$field,escape_check(trim($value)),null,null);
    }

# For each stored value, update to the normalised value to ensure matches.
$dataset=sql_query("select resource,value from resource_data where resource_type_field='$field' and resource>0");
foreach ($dataset as $data)
    {
    $value=join(",",normalize_values($data["value"]));
    # Update the value. Note the leading comma is important as it identifies this as a fixed list field.
    sql_query("update resource_data set value='," . escape_check($value) . "' where resource_type_field='$field' and resource='" . $data["resource"] . "' limit 1");
    echo "Updated " . $data["resource"]  . ": " . $value ."\n";
    ob_flush();
    }
    
echo "Done.";
