<div class="Question">
<label for="groupselect"><?php echo $lang["group"]?></label><select id="groupselect" name="groupselect" class="stdwidth"
onchange="if (this.value=='viewall') {document.getElementById('groupselector').style.display='none';}
else {document.getElementById('groupselector').style.display='block';}">
<?php if (!checkperm("U")) { ?><option <?php if ($groupselect=="viewall") { ?>selected<?php } ?> value="viewall"><?php echo $lang["allgroups"]?></option><?php } ?>
<option <?php if ($groupselect=="select") { ?>selected<?php } ?> value="select"><?php echo $lang["select"]?></option>
</select>
<div class="clearerleft"> </div>
	<table id="groupselector" cellpadding=3 cellspacing=3 style="padding-left:150px;<?php if ($groupselect=="viewall") { ?>display:none;<?php } ?>">
	<?php
	$grouplist=get_usergroups(true);
	for ($n=0;$n<count($grouplist);$n++)
		{
		?>
		<tr>
		<td valign=middle nowrap><?php echo htmlspecialchars($grouplist[$n]["name"])?>&nbsp;&nbsp;</td>
		<td width=10 valign=middle><input type=checkbox name="groups[]" value="<?php echo $grouplist[$n]["ref"]?>" <?php if (in_array($grouplist[$n]["ref"],$groups)) { ?>checked<?php } ?>></td>
		</tr>
		<?php
		}
	?></table>
	<div class="clearerleft"> </div>
</div>
