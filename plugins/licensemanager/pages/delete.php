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
	sql_query("delete from resource_license where ref='$ref' and resource='$resource'");
	
	resource_log($resource,"","",$lang["delete_license"] . " " . $ref);
	
	redirect("pages/view.php?ref=" . $resource);
	}
		
include "../../../include/header.php";
?>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $resource ?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>

<h1><?php echo $lang["delete_license"] ?></h1>

<form method="post" action="<?php echo $baseurl_short?>plugins/licensemanager/pages/delete.php" onSubmit="return CentralSpacePost(this,true);">
<input type=hidden name="submitted" value="true">
<input type=hidden name="ref" value="<?php echo $ref?>">
<input type=hidden name="resource" value="<?php echo $resource?>">

<div class="Question"><label><?php echo $lang["resourceid"]?></label><div class="Fixed"><?php echo htmlspecialchars($resource)?></div>
<div class="clearerleft"> </div></div>

<div class="Question"><label><?php echo $lang["license_id"]?></label><div class="Fixed"><?php echo htmlspecialchars($ref)?></div>
<div class="clearerleft"> </div></div>


<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="delete" type="submit" value="&nbsp;&nbsp;<?php echo $lang["action-delete"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../../../include/footer.php";
?>
