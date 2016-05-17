<?php /* -------- Check box list ------------------ */ 

if(!hook("customchkboxes","",array($field))):

# Translate all options

$modified_options=hook("modify_field_options","",array($field));
if($modified_options!=""){$field['node_options']=$modified_options;}

$option_trans=array();
$option_trans_simple=array();
for ($m=0;$m<count($field['node_options']);$m++)
	{
	$trans=i18n_get_translated($field['node_options'][$m]);
	if ($trans!=""){
		$option_trans[$field['node_options'][$m]]=$trans;
		$option_trans_simple[]=$trans;
	}
	}

if ($auto_order_checkbox && !hook("ajust_auto_order_checkbox","",array($field))) {
	if($auto_order_checkbox_case_insensitive){natcasesort($option_trans);}
	else{natsort($option_trans);}
}
$field['node_options']=array_keys($option_trans); # Set the options array to the keys, so it is now effectively sorted by translated string	
$field['node_options']=array_diff($field['node_options'], array(''));
//$set=trim_array(explode(",",$value));
$set=array_unique(preg_split('/,|\~\w+\:/',$value));        // this will remove language variants such as "~en:my option in english"
$wrap=0;

# Work out an appropriate number of columns based on the average length of the options.
$l=average_length($option_trans_simple);
switch ($l)
	{
	case ($l>40): 	$cols=1; break;	
	case ($l>25): 	$cols=2; break;
	case ($l>15): 	$cols=3; break;
	case ($l>10): 	$cols=4; break;
	case ($l>5): 	$cols=5; break;
	default: 	$cols=10;
	}

$height=ceil(count($field['node_options'])/$cols);

if ($edit_autosave) { ?>
	<script type="text/javascript">
		// Function to allow checkboxes to save automatically when $edit_autosave from config is set: 
		function checkbox_allow_save() {
			preventautosave=false;
			
			setTimeout(function () {
		        preventautosave=true;
		    }, 500);
		}
	</script>
<?php }

array_filter($field['node_options']);
array_filter($option_trans);

global $checkbox_ordered_vertically;
if ($checkbox_ordered_vertically)
	{
	if(!hook('rendereditchkboxes')):
	# ---------------- Vertical Ordering (only if configured) -----------
	?>
	<fieldset class="customFieldset" name="<?php echo $field['title']; ?>">
		<legend class="accessibility-hidden"><?php echo $field['title']; ?></legend>
		<table cellpadding=2 cellspacing=0><tr><?php
	for ($y=0;$y<$height;$y++)
		{
		for ($x=0;$x<$cols;$x++)
			{
			# Work out which option to fetch.
			$o=($x*$height)+$y;
			if ($o<count($field['node_options']))
				{
				$option=$field['node_options'][$o];
				$trans=$option_trans[$option];

				$name=$field["ref"] . "_" . md5($option);
				if ($option!="")
					{
					/*if(!hook("replace_checkbox_vertical_rendering","",array($name,$option,$ref=$field["ref"],$set))){*/
						?>
						<td width="1"><input type="checkbox" id="<?php echo $name; ?>" name="<?php echo $name?>" value="yes" <?php if (in_array($option,$set)) {?>checked<?php } ?> 
						<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');" onmousedown="checkbox_allow_save();"<?php } ?>
						/></td><td><label class="customFieldLabel" for="<?php echo $name; ?>" <?php if($edit_autosave) { ?>onmousedown="checkbox_allow_save();" <?php } ?>><?php echo htmlspecialchars($trans)?></label></td>
						<?php
						/*} # end hook("replace_checkbox_vertical_rendering")*/
					}
				}
			}
		?></tr><tr><?php
		}
	?></tr></table></fieldset><?php
	endif;
	}
else
	{				
	# ---------------- Horizontal Ordering (Standard) ---------------------				
	?>
	<table cellpadding=2 cellspacing=0><tr>
	<?php

	foreach ($option_trans as $option=>$trans)
		{
		$name=$field["ref"] . "_" . md5($option);
		$wrap++;if ($wrap>$cols) {$wrap=1;?></tr><tr><?php }
		?>
		<td width="1"><input type="checkbox" name="<?php echo $name?>" value="yes" <?php if (in_array($option,$set)) {?>checked<?php } ?>
		<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>
		 /></td><td><?php echo htmlspecialchars($trans)?>&nbsp;</td>
		<?php
		}
	?></tr></table><?php
	}
	
endif;
