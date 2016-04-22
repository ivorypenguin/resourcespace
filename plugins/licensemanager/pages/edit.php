<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("r")) {exit ("Permission denied.");}
include "../../../include/resource_functions.php";

$ref=getvalescaped("ref","");
$resource=getvalescaped("resource","");

# Check access
$edit_access=get_edit_access($resource);
if (!$edit_access) {exit("Access denied");} # Should never arrive at this page without edit access

if (getval("submitted","")!="")
	{
	# Save license data
	
	# Construct expiry date
	$expires=getvalescaped("expires_year","") . "-" . getvalescaped("expires_month","") . "-" . getvalescaped("expires_day","");
	
	# Construct usage
	$license_usage="";
	if (isset($_POST["license_usage"])) {$license_usage=escape_check(join(", ",$_POST["license_usage"]));}
	
	if ($ref=="new")
		{
		# New record 
		sql_query("insert into resource_license (resource,outbound,holder,license_usage,description,expires) values ('" . getvalescaped("resource","") . "', '" . getvalescaped("outbound","") . "', '" . getvalescaped("holder","") . "', '$license_usage', '" . getvalescaped("description","") . "', '$expires')");	
		$ref=sql_insert_id();
		
		resource_log($resource,"","",$lang["new_license"] . " " . $ref);
		}
	else
		{
		# Existing record	
		sql_query("update resource_license set outbound='" . getvalescaped("outbound","") . "',holder='" . getvalescaped("holder","") . "', license_usage='$license_usage',description='" . getvalescaped("description","") . "',expires='$expires' where ref='$ref' and resource='$resource'");
		
		resource_log($resource,"","",$lang["edit_license"] . " " . $ref);
		}

	redirect("pages/view.php?ref=" . $resource);
	}

# Fetch license data
if ($ref=="new")
	{
	# Set default values for the creation of a new record.
	$license=array(
		"resource"=>$resource,
		"outbound"=>1,
		"holder"=>"",		
		"license_usage"=>"",
		"description"=>"",
		"expires"=>date("Y-m-d")
		);
	}
else
	{
	$license=sql_query("select * from resource_license where ref='$ref'");
	if (count($license)==0) {exit("License not found.");}
	$license=$license[0];
	$resource=$license["resource"];
	}
		
include "../../../include/header.php";
?>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $resource ?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>

<h1><?php echo ($ref=="new"?$lang["new_license"]:$lang["edit_license"]) ?></h1>

<form method="post" action="<?php echo $baseurl_short?>plugins/licensemanager/pages/edit.php" onSubmit="return CentralSpacePost(this,true);">
<input type=hidden name="submitted" value="true">
<input type=hidden name="ref" value="<?php echo $ref?>">
<input type=hidden name="resource" value="<?php echo $resource?>">

<div class="Question"><label><?php echo $lang["resourceid"]?></label><div class="Fixed"><?php echo htmlspecialchars($license["resource"])?></div>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["license_id"]?></label><div class="Fixed"><?php echo ($ref=="new"?$lang["licensemanager_new"]:htmlspecialchars($ref))?></div>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["type"]?></label>
<div class="Fixed">
<input type=radio name="outbound" id="outbound_1" value="1" <?php if ($license["outbound"]==1) { ?>checked<?php } ?> /> <strong><?php echo $lang["outbound"] ?></strong> <?php echo $lang["outbound_license_description"] ?>
&nbsp;&nbsp;
<input type=radio name="outbound" id="outbound_0" value="0" <?php if ($license["outbound"]==0) { ?>checked<?php } ?> /> <strong><?php echo $lang["inbound"] ?></strong> <?php echo $lang["inbound_license_description"] ?>
</div>
<div class="clearerleft"> </div></div>


<div class="Question"><label><?php echo $lang["licensor_licensee"]?></label><input type=text class="stdwidth" name="holder" id="holder" value="<?php echo htmlspecialchars($license["holder"])?>" />
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["indicateusagemedium"]?></label>
<?php
$s=trim_array(explode(",",$license["license_usage"]));
foreach ($license_usage_mediums as $medium)
	{
	?>
	<input type="checkbox" name="license_usage[]" value="<?php echo $medium ?>" <?php if (in_array($medium, $s)) { ?>checked<?php } ?>>&nbsp;<?php echo lang_or_i18n_get_translated($medium, "license_usage-") ?>
	&nbsp;
	&nbsp;
	&nbsp;
	<?php
	}
?>


<div class="clearerleft"> </div></div>



<div class="Question"><label><?php echo $lang["description"]?></label><textarea rows="4" class="stdwidth" name="description" id="description"><?php echo htmlspecialchars($license["description"]) ?></textarea>
<div class="clearerleft"> </div></div>


<div class="Question"><label><?php echo $lang["fieldtitle-expiry_date"]?></label>
	<select name="expires_day" class="SearchWidth" style="width:98px;">
	  <?php
	  for ($n=1;$n<=31;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($license["expires"],8,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
		}
	  ?>
	</select>

	<select name="expires_month" class="SearchWidth" style="width:98px;">
	  <?php
	  for ($n=1;$n<=12;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($license["expires"],5,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$n-1]?></option><?php
		}
	  ?>
	</select>
	
	<select name="expires_year" class="SearchWidth" style="width:98px;">
	  <?php
	  $y=date("Y")+30;
	  for ($n=$minyear;$n<=$y;$n++)
		{
		?><option <?php if ($n==substr($license["expires"],0,4)) { ?>selected<?php } ?>><?php echo $n?></option><?php
		}
	  ?>
	</select>
<div class="clearerleft"> </div></div>




<?php /*
<div class="Question"><label><?php echo $lang["status"]?></label>
<div class="tickset">
<?php for ($n=0;$n<=2;$n++) { ?>
<div class="Inline"><input type="radio" name="status" value="<?php echo $n?>" <?php if ($research["status"]==$n) { ?>checked <?php } ?>/><?php echo $lang["requeststatus" . $n]?></div>
<?php } ?>
</div>
<div class="clearerleft"> </div></div>
*/ ?>


<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../../../include/footer.php";
?>
