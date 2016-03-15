<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("a")) {exit ("Permission denied.");}

$ref=getvalescaped("ref","");
$copied='';
$title=sql_value("select title value from resource_type_field where ref='$ref'","");

# Perform copy
if (getval("saveform","")!="")
	{
	$sync=getvalescaped("sync","");
	if ($sync==1) {$sync="'" . $ref . "'";} else {$sync="null";}
	
	sql_query("insert into resource_type_field
	(
		name,
		title,
		type,
		options,
		order_by,
		keywords_index,
		partial_index,
		resource_type,
		resource_column,
		display_field,
		use_for_similar,
		iptc_equiv,
		display_template,
		required,
		smart_theme_name,
		exiftool_field,
		advanced_search,
		simple_search,
		help_text,
		display_as_dropdown,
		external_user_access,
		autocomplete_macro,
		hide_when_uploading,
		hide_when_restricted,
		value_filter,
		exiftool_filter,
		omit_when_copying,
		tooltip_text,
		regexp_filter,
                display_condition,
                onchange_macro,
		sync_field
	)
	
	select
	
		name,
		title,
		type,
		options,
		9999,
		keywords_index,
		partial_index,
		'" . getvalescaped("resource_type","") . "',
		resource_column,
		display_field,
		use_for_similar,
		iptc_equiv,
		display_template,
		required,
		smart_theme_name,
		exiftool_field,
		advanced_search,
		simple_search,
		help_text,
		display_as_dropdown,
		external_user_access,
		autocomplete_macro,
		hide_when_uploading,
		hide_when_restricted,
		value_filter,
		exiftool_filter,
		omit_when_copying,
		tooltip_text,
		regexp_filter,
                display_condition,
                onchange_macro,
		" . $sync . "	
	
		from resource_type_field where ref='$ref'
		
		
		");	
	$copied=sql_insert_id();
        redirect($baseurl_short . "pages/admin/admin_resource_type_field_edit.php?ref=" . $copied);
	}
        
if ($copied!='')
    {
    $saved_text=str_replace("?",$copied,$lang["copy-completed"]);    
    }
	


include "../../include/header.php";

?>
<div class="BasicsBox">
    
<p>
    <a href="<?php echo $baseurl . "/pages/admin/admin_home.php" ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["systemsetup"]?></a><br>
    <a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_fields.php" ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["admin_resource_type_fields"]?></a><br>
    <a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_field_edit.php?ref=" . $ref ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["admin_resource_type_field"] .": " . i18n_get_translated($title) ?></a></p>
</p>

<h1><?php echo $lang["copy-field"] . ":&nbsp;" . i18n_get_translated($title) ?></h1>
<?php if (isset($saved_text)) { ?><div class="PageInformal"><?php echo $saved_text?></div><?php } ?>

<form method="post" action="field_copy.php">
<input type="hidden" name="saveform" value="true">
<input type="hidden" name="ref" value="<?php echo $ref ?>">

<p><?php echo $lang["copy-to-resource-type"] ?><br />

<select name="resource_type" style="width:100%;">
<?php
$types=get_resource_types();
for ($n=0;$n<count($types);$n++)
	{
	?><option value="<?php echo $types[$n]["ref"]?>"><?php echo $types[$n]["name"]?></option><?php
	}
?></select>
</p>

<p>
<?php echo $lang["synchronise-changes-with-this-field"] ?><br />
<select name="sync" style="width:100%;">
<option value="0"><?php echo $lang["no"] ?></option>
<option value="1"><?php echo $lang["yes"] ?></option>
</select>
</p>


<p align="right"><input type="submit" name="copy" value="<?php echo $lang["copy"] ?>" style="width:100px;"></p>
</form>

</div><!--End of BasicsBox -->
<?php


include "../../include/footer.php";
	