<?php /* -------- Date ---------------------------- */ 

global $reset_date_upload_template, $reset_date_field, $blank_date_upload_template;

# Start with a null date
$dy="";
$dm=$dd=$dh=$di=-1;

if(!$blank_date_upload_template || $ref>0) {
	

if (($ref<0) && $reset_date_upload_template && $reset_date_field==$field["ref"])
	{
	# Upload template: always reset to today's date (if configured).
	$dy=date("Y");$dm=intval(date("m"));$dd=intval(date("d"));
	$dh=intval(date("H"));$di=intval(date("i"));
	}
elseif ($value!="")
	{
    #fetch the date parts from the value
    $sd=explode(" ",$value);
    if (count($sd)>=2)
    	{
    	# Attempt to extract hours and minutes from second part.
    	$st=explode(":",$sd[1]);
    	if (count($st)>=2)
    		{
    		$dh=intval($st[0]);
    		$di=intval($st[1]);
    		} 
    	}
    $value=$sd[0];
    $sd=explode("-",$value);    
	if (count($sd)>=1) $dy=$sd[0];
	if (count($sd)>=2) $dm=intval($sd[1]);
    if (count($sd)>=3) $dd=intval($sd[2]);
    }  
    
}  
?>
<select name="<?php echo $name?>-d"
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"]?>');"<?php } ?>
><option value=""><?php echo $lang["day"]?></option>
<?php for ($m=1;$m<=31;$m++) {?><option <?php if($m==$dd){echo " selected";}?>><?php echo sprintf("%02d",$m)?></option><?php } ?>
</select>
    
<select name="<?php echo $name?>-m"
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"]?>');"<?php } ?>
><option value=""><?php echo $lang["month"]?></option>
<?php for ($m=1;$m<=12;$m++) {?><option <?php if($m==$dm){echo " selected";}?> value="<?php echo sprintf("%02d",$m)?>"><?php echo $lang["months"][$m-1]?></option><?php } ?>
</select>
   
<input type=text size=5 name="<?php echo $name?>-y" value="<?php echo $dy?>" <?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"]?>');"<?php } ?>>

<?php if ($field["type"]!=10) { ?>
<!-- Time (optional) -->
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select name="<?php echo $name?>-h"
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"]?>');"<?php } ?>
><option value=""><?php echo $lang["hour-abbreviated"]?></option>
<?php for ($m=0;$m<=23;$m++) {?><option <?php if($m==$dh){echo " selected";}?>><?php echo sprintf("%02d",$m)?></option><?php } ?>
</select>

<select name="<?php echo $name?>-i"
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"]?>');"<?php } ?>
><option value=""><?php echo $lang["minute-abbreviated"]?></option>
<?php for ($m=0;$m<=59;$m++) {?><option <?php if($m==$di){echo " selected";}?>><?php echo sprintf("%02d",$m)?></option><?php } ?>
</select>
<?php } ?>

