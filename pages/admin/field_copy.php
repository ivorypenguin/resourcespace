<?php
include "../../include/db.php";
include "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("a")) {exit ("Permission denied.");}

$ref=getvalescaped("ref","");
$copied='';

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
		" . $sync . "	
	
		from resource_type_field where ref='$ref'
		
		
		");	
	$copied=sql_insert_id();
	}
	


include "include/header.php";
?>
<body style="background-position:0px -85px;margin:0;padding:10px;">
<div class="proptitle"><?php echo $lang["copy-field"] ?></div>

<div class="propbox" id="propbox">

<?php if ($copied!='') { ?>
<table width=100% style="border:1px solid black;">
<tr><td width=40><img src="gfx/icons/apply.gif" width=32 height=32></td><td valign=middle align=left><?php echo str_replace("?",$copied,$lang["copy-completed"]) ?></td></tr>
</table>
<?php } ?>

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

</div>
</div>
</body>
