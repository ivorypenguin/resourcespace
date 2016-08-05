<!-- Period select -->
<div class="Question">
<label for="period"><?php echo $lang["period"]?></label><select id="period" name="period" class="stdwidth" onChange="
if (this.value==-1) {document.getElementById('DateRange').style.display='block';} else {document.getElementById('DateRange').style.display='none';}
if (this.value==0) {document.getElementById('SpecificDays').style.display='block';} else {document.getElementById('SpecificDays').style.display='none';}
if (this.value!=-1) {document.getElementById('EmailMe').style.display='block';} else {document.getElementById('EmailMe').style.display='none';}
// Copy reporting period to e-mail period
if (document.getElementById('period').value==0)
	{
	// Copy from specific day box
	document.getElementById('email_days').value=document.getElementById('period_days').value;
	}
else
	{
	document.getElementById('email_days').value=document.getElementById('period').value;		
	}
">
<?php
foreach ($reporting_periods_default as $period_default)
	{
	?><option value="<?php echo $period_default?>" <?php if ($period_init==$period_default) { ?>selected<?php } ?>><?php echo str_replace("?",$period_default,$lang["lastndays"])?></option><?php
	}
?>
<option value="0" <?php if ($period_init==0) { ?>selected<?php } ?>><?php echo $lang["specificdays"]?></option>
<option value="-1" <?php if ($period_init==-1) { ?>selected<?php } ?>><?php echo $lang["specificdaterange"]?></option>
</select>
<div class="clearerleft"> </div>
</div>



<!-- Specific Days Selector -->
<div id="SpecificDays" <?php if ($period_init!=0) { ?>style="display:none;"<?php } ?>>
<div class="Question">
<label for="period_days"><?php echo $lang["specificdays"]?></label>
<?php
$textbox="<input type=\"text\" id=\"period_days\" name=\"period_days\" size=\"4\" value=\"" . getval("period_days","") . "\">";
echo str_replace("?",$textbox,$lang["lastndays"]);
?>
<div class="clearerleft"> </div>
</div>
</div>


<!-- Specific Date Range Selector -->
<div id="DateRange" <?php if ($period_init!=-1) { ?>style="display:none;"<?php } ?>>
<div class="Question">
<label><?php echo $lang["fromdate"]?><br/><?php echo $lang["inclusive"]?></label>
<?php
$name="from";
$dy=getval($name . "-y",2000);
$dm=getval($name . "-m",1);
$dd=getval($name . "-d",1);
?>
<select name="<?php echo $name?>-d">
<?php for ($m=1;$m<=31;$m++) {?><option <?php if($m==$dd){echo " selected";}?>><?php echo sprintf("%02d",$m)?></option><?php } ?>
</select>
<select name="<?php echo $name?>-m">
<?php for ($m=1;$m<=12;$m++) {?><option <?php if($m==$dm){echo " selected";}?> value="<?php echo sprintf("%02d",$m)?>"><?php echo $lang["months"][$m-1]?></option><?php } ?>
</select>
<input type=text size=5 name="<?php echo $name?>-y" value="<?php echo $dy?>">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label><?php echo $lang["todate"]?><br/><?php echo $lang["inclusive"]?></label>
<?php
$name="to";
$dy=getval($name . "-y",date("Y"));
$dm=getval($name . "-m",date("m"));
$dd=getval($name . "-d",date("d"));
?>
<select name="<?php echo $name?>-d">
<?php for ($m=1;$m<=31;$m++) {?><option <?php if($m==$dd){echo " selected";}?>><?php echo sprintf("%02d",$m)?></option><?php } ?>
</select>
<select name="<?php echo $name?>-m">
<?php for ($m=1;$m<=12;$m++) {?><option <?php if($m==$dm){echo " selected";}?> value="<?php echo sprintf("%02d",$m)?>"><?php echo $lang["months"][$m-1]?></option><?php } ?>
</select>
<input type=text size=5 name="<?php echo $name?>-y" value="<?php echo $dy?>">
<div class="clearerleft"> </div>
</div>
</div>
<!-- end of Date Range Selector -->