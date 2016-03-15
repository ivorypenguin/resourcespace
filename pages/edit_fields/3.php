<?php /* -------- Drop down list ------------------ */ 

# Translate all options

$modified_options=hook("modify_field_options","",array($field));
if($modified_options!=""){$field['node_options']=$modified_options;}
$adjusted_dropdownoptions=hook("adjustdropdownoptions","",array($field,$field['node_options']));
if ($adjusted_dropdownoptions){$field['node_options']=$adjusted_dropdownoptions;}

$option_trans=array();
for ($m=0;$m<count($field['node_options']);$m++)
	{
	$option_trans[$field['node_options'][$m]]=i18n_get_translated($field['node_options'][$m]);
	}
if ($auto_order_checkbox && !hook("ajust_auto_order_checkbox","",array($field))) {
	if($auto_order_checkbox_case_insensitive){natcasesort($option_trans);}
	else{asort($option_trans);}	
}
$adjusted_dropdownoptiontrans=hook("adjustdropdownoptiontrans","edit",array($field,$option_trans));
if ($adjusted_dropdownoptiontrans){$option_trans=$adjusted_dropdownoptiontrans;}

// strip the leading comma if it exists
if(substr($value, 0, 1) == ',')
    {
    $value = substr($value, 1);
    }

?>
<select class="stdwidth" name="<?php echo $name?>" id="<?php echo $name?>" <?php echo $help_js; hook("additionaldropdownattributes","",array($field)); ?>
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>>

<?php
if(!hook('replacedropdowndefault', '', array($field)))
    {
    ?>
    <option value=""></option>
    <?php
    }

foreach ($option_trans as $option=>$trans)
	{
	if (trim($option)!="")
		{
		?>
		<option value="<?php echo htmlspecialchars(trim($option))?>" <?php if (trim($option)==trim($value)) {?>selected<?php } ?>><?php echo htmlspecialchars(trim($trans))?></option>
		<?php
		}
	}
?></select><?php

