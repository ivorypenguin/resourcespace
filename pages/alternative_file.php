<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";
include "../include/image_processing.php";

$ref=getvalescaped("ref","",true);

$search=getvalescaped("search","");
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

$resource=getvalescaped("resource","",true);

# Fetch resource data.
$resourcedata=get_resource_data($resource);
# Load the configuration for the selected resource type. Allows for alternative notification addresses, etc.
resource_type_config_override($resourcedata["resource_type"]);

# Not allowed to edit this resource?
if ((!get_edit_access($resource, $resourcedata["archive"],false,$resourcedata) || checkperm('A')) && $resource>0) {exit ("Permission denied.");}

hook("pageevaluation");

# Fetch alternative file data
$file=get_alternative_file($resource,$ref);if ($file===false) {exit("Alternative file not found.");}

if (getval("name","")!="")
	{
	hook("markmanualupload");
	# Save file data
	save_alternative_file($resource,$ref);
	// Check to see if we need to notify users of this change							
	if($notify_on_resource_change_days!=0)
		{								
		notify_resource_change($resource);
		}
	hook ("savealternatefiledata");
	redirect ($baseurl_short."pages/alternative_files.php?ref=$resource&search=".urlencode($search)."&offset=$offset&order_by=$order_by&sort=$sort&archive=$archive");
	}

	
include "../include/header.php";
?>
<div class="BasicsBox">
<p>
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo $resource?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtomanagealternativefiles"]?></a>
</p>
	
<h1><?php echo $lang["editalternativefile"]?></h1>


<form method="post" class="form" id="fileform" onsubmit="return CentralSpacePost(this,true);" action="<?php echo $baseurl_short?>pages/alternative_file.php?search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>">

<input type=hidden name=ref value="<?php echo htmlspecialchars($ref) ?>">
<input type=hidden name=resource value="<?php echo htmlspecialchars($resource) ?>">


<div class="Question">
<label><?php echo $lang["resourceid"]?></label><div class="Fixed"><?php echo htmlspecialchars($resource) ?></div>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name"><?php echo $lang["name"]?></label><input type=text class="stdwidth" name="name" id="name" value="<?php echo htmlspecialchars($file["name"]) ?>" maxlength="100">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="name"><?php echo $lang["description"]?></label><input type=text class="stdwidth" name="description" id="description" value="<?php echo htmlspecialchars($file["description"]) ?>" maxlength="200">
<div class="clearerleft"> </div>
</div>

<?php
	// if the system is configured to support a type selector for alt files, show it
	if (isset($alt_types) && count($alt_types) > 1){
		echo "<div class='Question'>\n<label for='alt_type'>".$lang["alternatetype"]."</label><select name='alt_type' id='alt_type'>";
		foreach($alt_types as $thealttype){
			//echo "thealttype:$thealttype: / filealttype:" . $file['alt_type'].":";
			if ($thealttype == $file['alt_type']){$alt_type_selected = " selected='selected'"; } else { $alt_type_selected = ''; }
			$thealttype = htmlspecialchars($thealttype,ENT_QUOTES);
			echo "\n   <option value='$thealttype' $alt_type_selected >$thealttype</option>";
		}
		echo "\n</select>\n<div class='clearerleft'> </div>\n</div>";
	}
?>

<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../include/footer.php";
?>
