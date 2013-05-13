<?php
include "../include/db.php";
include "../include/authenticate.php"; 
include "../include/general.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";
include "../include/search_functions.php";
include "../include/image_processing.php";

# Editing resource or collection of resources (multiple)?
$ref=getvalescaped("ref","",true);

# Fetch search details (for next/back browsing and forwarding of search params)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

$archive=getvalescaped("archive",0,true);

$errors=array(); # The results of the save operation (e.g. required field messages)

# Disable auto save for upload forms - it's not appropriate.
if ($ref<0) { $edit_autosave=false; }

# next / previous resource browsing
$go=getval("go","");
if ($go!="")
	{
	# Re-run the search and locate the next and previous records.
	$result=do_search($search,$restypes,$order_by,$archive,240+$offset+1);
	if (is_array($result))
		{
		# Locate this resource
		$pos=-1;
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]==$ref) {$pos=$n;}
			}
		if ($pos!=-1)
			{
			if (($go=="previous") && ($pos>0)) {$ref=$result[$pos-1]["ref"];}
			if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($offset+72)) {$offset=$pos+1;}} # move to next page if we've advanced far enough
			}
		else
			{
			?>
			<script type="text/javascript">
			alert("<?php echo $lang["resourcenotinresults"] ?>");
			</script>
			<?php
			}
		}
	}

$collection=getvalescaped("collection","",true);
if ($collection!="") 
	{
	# If editing multiple items, use the first resource as the template
	$multiple=true;
	$edit_autosave=false; # Do not allow auto saving for batch editing.
	$items=get_collection_resources($collection);
	if (count($items)==0) {
		$error=$lang['error-cannoteditemptycollection'];
		error_alert($error);
		exit();
	}
	
	# check editability
	if (!allow_multi_edit($collection)){
		$error=$lang['error-permissiondenied'];
		error_alert($error);
		exit();
	}
	$ref=$items[0];
	}
else
	{
	$multiple=false;
	}

# Fetch resource data.
$resource=get_resource_data($ref);

# If upload template, check if the user has upload permission.
if ($ref<0 && !(checkperm("c") || checkperm("d")))
    {
    $error=$lang['error-permissiondenied'];
    error_alert($error);
    exit();
    }

# Check edit permission.
if (!get_edit_access($ref,$resource["archive"]))
    {
    # The user is not allowed to edit this resource or the resource doesn't exist.
    $error=$lang['error-permissiondenied'];
    error_alert($error);
    exit();
    }

if (getval("regen","")!="")
	{
	create_previews($ref,false,$resource["file_extension"]);
	}

if (getval("regenexif","")!="")
	{
	extract_exif_comment($ref);
	}

# Establish if this is a metadata template resource, so we can switch off certain unnecessary features
$is_template=(isset($metadata_template_resource_type) && $resource["resource_type"]==$metadata_template_resource_type);



# -----------------------------------
# 			PERFORM SAVE
# -----------------------------------
if ((getval("autosave","")!="") || (getval("tweak","")=="" && getval("submitted","")!="" && getval("resetform","")=="" && getval("copyfromsubmit","")==""))
	{
	hook("editbeforesave");			

	# save data
	if (!$multiple)
		{

		# Upload template: Change resource type
		$resource_type=getvalescaped("resource_type","");
		if ($resource_type!="")
			{
			update_resource_type($ref,$resource_type);
			$resource=get_resource_data($ref,false); # Reload resource data.
			}		

		$save_errors=save_resource_data($ref,$multiple);
		$no_exif=getval("no_exif",($metadata_read_default)?"":"yes");
		$autorotate = getval("autorotate","");

		if ($upload_collection_name_required){
			if (getvalescaped("entercolname","")=="" && getval("collection_add","")==-1){ 
				if (!is_array($save_errors)){$save_errors=array();}	
				$save_errors['collectionname']=$lang["requiredfield"];
			}
		}		
		
		if (($save_errors===true || $is_template)&&(getval("tweak","")==""))
			{
			if ($ref>0 && getval("save","")!="")
				{
				# Log this
				daily_stat("Resource edit",$ref);
				redirect($baseurl_short."pages/view.php?ref=" . $ref . "&search=" . urlencode($search) . "&offset=" . $offset . "&order_by=" . $order_by . "&sort=".$sort."&archive=" . $archive . "&refreshcollectionframe=true");
				}
			else
				{
				if ((getval("uploader","")!=""))
					{
					# Save button pressed? Move to next step.
					if (getval("save","")!="") {redirect($baseurl_short."pages/upload_" . getval("uploader","") . ".php?collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname",""))."&resource_type=".$resource_type . "&no_exif=" . $no_exif . "&autorotate=" . $autorotate . "&themestring=" . urlencode(getval('themestring','')) . "&public=" . getval('public',''));}
					}
				elseif (getval("local","")!="") // Test if fetching resource from local upload folder.
					{
					# Save button pressed? Move to next step.
					if (getval("save","")!="") {redirect($baseurl_short."pages/team/team_batch_select.php?use_local=yes&collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname",""))."&resource_type=".$resource_type . "&no_exif=" . $no_exif . "&autorotate=" . $autorotate);}
					}
                elseif (getval("single","")!="") // Test if single upload (archived or not).
					{
					# Save button pressed? Move to next step.
					if (getval("save","")!="") {redirect($baseurl_short."pages/upload.php?resource_type=".$resource_type . "&no_exif=" . $no_exif . "&autorotate=" . $autorotate . "&archive=" . $archive);}
					}    
				else // Hence fetching from ftp.
					{
					# Save button pressed? Move to next step.
					if (getval("save","")!="") {redirect($baseurl_short."pages/team/team_batch.php?collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname","")). "&resource_type=".$resource_type . "&no_exif=" . $no_exif . "&autorotate=" . $autorotate);}
					}
				}
			}
		elseif (getval("save","")!="")
			{
			$show_error=true;
            }
		}
	else
		{
		# Save multiple resources
		save_resource_data_multi($collection);
		if(!hook("redirectaftermultisave")){
			redirect($baseurl_short."pages/search.php?refreshcollectionframe=true&search=!collection" . $collection);
			}
		}
	
	# If auto-saving, no need to continue as it will only add to bandwidth usage to send the whole edit page back to the client. Send a simple 'SAVED' message instead.
	if (getval("autosave","")!="") {exit("SAVED");}
	}

if (getval("tweak","")!="")
	{
	$tweak=getval("tweak","");
	switch($tweak)
		{
		case "rotateclock":
		tweak_preview_images($ref,270,0,$resource["preview_extension"]);
		break;
		case "rotateanti":
		tweak_preview_images($ref,90,0,$resource["preview_extension"]);
		break;
		case "gammaplus":
		tweak_preview_images($ref,0,1.3,$resource["preview_extension"]);
		break;
		case "gammaminus":
		tweak_preview_images($ref,0,0.7,$resource["preview_extension"]);
		break;
		case "restore":
		if ($enable_thumbnail_creation_on_upload)
			{
			create_previews($ref,false,$resource["file_extension"]);
			refresh_collection_frame();
			}
		else
			{
			sql_query("update resource set preview_attempts=0, has_image=0 where ref='$ref'");
			}
		break;
		}

        hook("moretweakingaction", "", array($tweak, $ref, $resource));

	# Reload resource data.
	$resource=get_resource_data($ref,false);
	}
	
# Simulate reupload (preserving filename and thumbs, but otherwise resetting metadata).
if (getval("exif","")!="")
	{
	upload_file($ref,$no_exif=false,true);
	resource_log($ref,"r","");
	}	

# If requested, refresh the collection frame (for redirects from saves)
if (getval("refreshcollectionframe","")!="")
	{
	refresh_collection_frame();
	}

include "../include/header.php";
?>
<script type="text/javascript">
function ShowHelp(field)
	{
	// Show the help box if available.
	if (document.getElementById('help_' + field))
		{
		jQuery('#help_' + field).fadeIn();
		}
	}
function HideHelp(field)
	{
	// Hide the help box if available.
	if (document.getElementById('help_' + field))
		{
		document.getElementById('help_' + field).style.display='none';
		}
	}

	jQuery(document).ready(function() {
		jQuery('#collection_add').change(function (){
			if(jQuery('#collection_add').val()==-1){
				jQuery('#collectioninfo').fadeIn();
			} 
			else {
				jQuery('#collectioninfo').fadeOut();
			}
		});
		jQuery('#collection_add').change();
	}); 

<?php
# Function to automatically save the form on field changes, if configured.
if ($edit_autosave) { ?>
function AutoSave(field)
	{
	jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["saving"] ?>');
	jQuery('#AutoSaveStatus' + field).show();

	
	jQuery.post(jQuery('#mainform').attr('action') + '&autosave=true',jQuery('#mainform').serialize(),
	
	function(data)
	  	{
	  	if (data=="SAVED")
	  		{
		  		jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["saved"] ?>');
		  		jQuery('#AutoSaveStatus' + field).fadeOut('slow');
		  	}
		else
			{
		  		jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["save-error"] ?>' + data);				
			}
		});
	}
<?php } 

# Resource next / back browsing.
function EditNav() # Create a function so this can be repeated at the end of the form also.
	{
	global $baseurl_short,$ref,$search,$offset,$order_by,$sort,$archive,$lang;
	?>
	<div class="TopInpageNav">
	<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;go=previous">&lt;&nbsp;<?php echo $lang["previousresult"]?></a>
	|
	<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php<?php if (strpos($search,"!")!==false) {?>?search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?><?php } ?>"><?php echo $lang["viewallresults"]?></a>
	|
	<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;go=next"><?php echo 		$lang["nextresult"]?>&nbsp;&gt;</a>
	</div>
	<?php
	}

?>
</script>

<form method="post" action="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&amp;uploader=<?php echo getval("uploader","") ?>&amp;single=<?php echo getval("single","") ?>&amp;local=<?php echo getval("local","") ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;collection=<?php echo $collection ?>&amp;metadatatemplate=<?php echo getval("metadatatemplate","") ?>" id="mainform">
<div class="BasicsBox">
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["next"]?>&nbsp;&nbsp;" class="defaultbutton" />
<input type="hidden" name="submitted" value="true">
<?php 
if ($multiple) { ?>
<h1 id="editmultipleresources"><?php echo $lang["editmultipleresources"]?></h1>
<p><?php $qty = count($items);
echo ($qty==1 ? $lang["resources_selected-1"] : str_replace("%number", $qty, $lang["resources_selected-2"])) . ". ";
# The script doesn't allow editing of empty collections, no need to handle that case here.
echo text("multiple"); ?></p>

<?php } elseif ($ref>0) { ?>
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&lt; <?php echo $lang["backtoresourceview"]?></a></p>

<h1 id="editresource"><?php echo $lang["editresource"]?></h1>

<?php
# Draw nav
if (!$multiple) { EditNav(); }
?>


<div class="Question" id="resource_ref_div" style="border-top:none;">
<label><?php echo $lang["resourceid"]?></label>
<div class="Fixed"><?php echo urlencode($ref) ?></div>
<div class="clearerleft"> </div>
</div>

<?php if (!$is_template && !checkperm("F*")) { ?>
<div class="Question" id="question_file">
<label><?php echo $lang["file"]?></label>
<div class="Fixed">
<?php
if ($resource["has_image"]==1)
	{
	?><img align="top" src="<?php echo get_resource_path($ref,false,($edit_large_preview?"pre":"thm"),false,$resource["preview_extension"],-1,1,checkperm("w"))?>" class="ImageBorder" style="margin-right:10px;"/><br />
	<?php
	}
else
	{
	# Show the no-preview icon
	?>
	<img src="../gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],true)?>" />
	<br />
	<?php
	}
if ($resource["file_extension"]!="") { ?><strong><?php echo str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["cell-fileoftype"]) . " (" . formatfilesize(@filesize_unlimited(get_resource_path($ref,true,"",false,$resource["file_extension"]))) . ")" ?></strong><br /><?php } ?>

	<?php if ($resource["has_image"]!=1) { ?>
	<a href="<?php echo $baseurl_short?>pages/upload.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>&upload_a_file=true" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["uploadafile"]?></a>
	<?php } else { ?>
	<a href="<?php echo $baseurl_short?>pages/upload_<?php echo $top_nav_upload_type ?>.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>&replace_resource=<?php echo urlencode($ref)  ?>&resource_type=<?php echo $resource['resource_type']?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["replacefile"]?></a>
	<?php hook("afterreplacefile"); ?>
	<?php } ?>
	<?php if (! $disable_upload_preview) { ?><br />
	<a href="<?php echo $baseurl_short?>pages/upload_preview.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["uploadpreview"]?></a><?php } ?>
	<?php if (! $disable_alternative_files) { ?><br />
	<a href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>"  onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["managealternativefiles"]?></a><?php } ?>
	<?php if ($allow_metadata_revert){?><br />
	<a href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&exif=true&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return confirm('<?php echo $lang["confirm-revertmetadata"]?>');">&gt; 
	<?php echo $lang["action-revertmetadata"]?></a><?php } ?>
	<?php hook("afterfileoptions"); ?>
</div>
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php if (!checkperm("F*")) { ?>
<div class="Question" id="question_imagecorrection">
<label><?php echo $lang["imagecorrection"]?><br/><?php echo $lang["previewthumbonly"]?></label><select class="stdwidth" name="tweak" id="tweak" onChange="CentralSpacePost(document.getElementById('mainform'),true);">
<option value=""><?php echo $lang["select"]?></option>
<?php if ($resource["has_image"]==1) { ?>
<?php
# On some PHP installations, the imagerotate() function is wrong and images are turned incorrectly.
# A local configuration setting allows this to be rectified
if (!$image_rotate_reverse_options)
	{
	?>
	<option value="rotateclock"><?php echo $lang["rotateclockwise"]?></option>
	<option value="rotateanti"><?php echo $lang["rotateanticlockwise"]?></option>
	<?php
	}
else
	{
	?>
	<option value="rotateanti"><?php echo $lang["rotateclockwise"]?></option>
	<option value="rotateclock"><?php echo $lang["rotateanticlockwise"]?></option>
	<?php
	}
?>
<?php if ($tweak_allow_gamma){?>
<option value="gammaplus"><?php echo $lang["increasegamma"]?></option>
<option value="gammaminus"><?php echo $lang["decreasegamma"]?></option>
<?php } ?>
<option value="restore"><?php echo $lang["recreatepreviews"]?></option>
<?php } else { ?>
<option value="restore"><?php echo $lang["retrypreviews"]?></option>
<?php } ?>
<?php hook("moretweakingopt"); ?>
</select>
<div class="clearerleft"> </div>
</div>
<?php } ?>


<?php } else { # Upload template: (writes to resource with ID [negative user ref])

# Define the title h1:
if (getval("uploader","")=="plupload") {$titleh1 = $lang["addresourcebatchbrowser"];} # Add Resource Batch - In Browser
elseif (getval("uploader","")=="java") {$titleh1 = $lang["addresourcebatchbrowserjava"];} # Add Resource Batch - In Browser - Java (Legacy)
elseif (getval("single","")!="")
	{
	if (getval("archive","")=="2")
		{
		$titleh1 = $lang["newarchiveresource"]; # Add Single Archived Resource
		}
	else
		{
		$titleh1 = $lang["addresource"]; # Add Single Resource
		}
	}
elseif (getval("local","")!="") {$titleh1 = $lang["addresourcebatchlocalfolder"];} # Add Resource Batch - Fetch from local upload folder
else $titleh1 = $lang["addresourcebatchftp"]; # Add Resource Batch - Fetch from FTP server

# Define the subtitle h2:
$titleh2 = str_replace(array("%number","%subtitle"), array("1", $lang["specifydefaultcontent"]), $lang["header-upload-subtitle"]);
?>

<h1><?php echo $titleh1 ?></h1>
<h2><?php echo $titleh2 ?></h2>
<p><?php echo $lang["intro-batch_edit"] ?></p>

<?php
# Upload template: Show the save / clear buttons at the top too, to avoid unnecessary scrolling.
?>
<div class="QuestionSubmit">
<input name="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["next"]?>&nbsp;&nbsp;" /><br />
<div class="clearerleft"> </div>
</div>

<?php } ?>

<?php hook("editbefresmetadata"); ?>

<?php if (!$multiple){?>
<div class="Question" id="question_resourcetype">
<label for="resourcetype"><?php echo $lang["resourcetype"]?></label>
<select name="resource_type" id="resourcetype" class="stdwidth" 
onChange="<?php if ($ref>0) { ?>if (confirm('<?php echo $lang["editresourcetypewarning"]; ?>')){<?php } ?>CentralSpacePost(document.getElementById('mainform'),true);<?php if ($ref>0) { ?>}else {return}<?php } ?>">
<?php
$types=get_resource_types();
for ($n=0;$n<count($types);$n++)
	{
	?><option value="<?php echo $types[$n]["ref"]?>" <?php if ($resource["resource_type"]==$types[$n]["ref"]) {?>selected<?php } ?>><?php echo htmlspecialchars($types[$n]["name"])?></option><?php
	}
?></select>
<div class="clearerleft"> </div>
</div>
<?php } else {
# Multiple method of changing resource type.
 ?>
<h1><?php echo $lang["resourcetype"] ?></h1>
<div><input name="editresourcetype" id="editresourcetype" type="checkbox" value="yes" onClick="var q=document.getElementById('editresourcetype_question');if (this.checked) {q.style.display='block';alert('<?php echo $lang["editallresourcetypewarning"] ?>');} else {q.style.display='none';}">&nbsp;<label for="editresourcetype"><?php echo $lang["resourcetype"] ?></label></div>
<div class="Question" style="display:none;" id="editresourcetype_question">
<label for="resourcetype"><?php echo $lang["resourcetype"]?></label>
<select name="resource_type" id="resourcetype" class="stdwidth">
<?php
$types=get_resource_types();
for ($n=0;$n<count($types);$n++)
	{
	?><option value="<?php echo $types[$n]["ref"]?>" <?php if ($resource["resource_type"]==$types[$n]["ref"]) {?>selected<?php } ?>><?php echo htmlspecialchars($types[$n]["name"])?></option><?php
	}
?></select>
<div class="clearerleft"> </div>
</div>
<?php } ?>
<?php
$lastrt=-1;

# "copy data from" feature
if ($enable_copy_data_from && !$multiple && !checkperm("F*"))
	{ 
	?>
	<div class="Question" id="question_copyfrom">
	<label for="copyfrom"><?php echo $lang["batchcopyfrom"]?></label>
	<input class="stdwidth" type="text" name="copyfrom" id="copyfrom" value="" style="width:80px;">
	<input type="submit" name="copyfromsubmit" value="<?php echo $lang["copy"]?>">
	</div>
	<?php
	}

if (isset($metadata_template_resource_type) && !$multiple && !checkperm("F*"))
	{
	# Show metadata templates here
	?>
	<div class="Question" id="question_metadatatemplate">
	<label for="metadatatemplate"><?php echo $lang["usemetadatatemplate"]?></label>
	<select name="metadatatemplate" class="medwidth">
	<option value=""><?php echo (getval("metadatatemplate","")=="")?$lang["select"]:$lang["undometadatatemplate"] ?></option>
	<?php
	$templates=get_metadata_templates();
	foreach ($templates as $template)
		{
		?>
		<option value="<?php echo $template["ref"] ?>"><?php echo htmlspecialchars($template["field$metadata_template_title_field"]) ?></option>
		<?php	
		}
	?>
	</select>
	<input type="submit" class="medcomplementwidth" name="copyfromsubmit" value="<?php echo $lang["action-select"]?>">
	</div>
	<?php
	}

	if ($edit_upload_options_at_top){include '../include/edit_upload_options.php';}


$use=$ref;

# Resource aliasing.
# 'Copy from' or 'Metadata template' been supplied? Load data from this resource instead.
$originalref=$use;

if (getval("copyfrom","")!="")
	{
	# Copy from function
	$copyfrom=getvalescaped("copyfrom","");
	$copyfrom_access=get_resource_access($copyfrom);
	
	# Check access level
	if ($copyfrom_access!=2) # Do not allow confidential resources (or at least, confidential to that user) to be copied from
		{
		$use=$copyfrom;
		$original_fields=get_resource_field_data($ref,$multiple,true);
		}
	}

if (getval("metadatatemplate","")!="")
	{
	$use=getvalescaped("metadatatemplate","");
	}

# Load resource data
$fields=get_resource_field_data($use,$multiple,true,$originalref);



# if this is a metadata template, set the metadata template title field at the top
if (isset($metadata_template_resource_type)&&(isset($metadata_template_title_field)) && $resource["resource_type"]==$metadata_template_resource_type){
	# recreate fields array, first with metadata template field
	$x=0;
	for ($n=0;$n<count($fields);$n++){
		if ($fields[$n]["resource_type"]==$metadata_template_resource_type){
			$newfields[$x]=$fields[$n];
			$x++;
		}
	}
	# then add the others
	for ($n=0;$n<count($fields);$n++){
		if ($fields[$n]["resource_type"]!=$metadata_template_resource_type){
			$newfields[$x]=$fields[$n];
			$x++;
		}
	}
	$fields=$newfields;
}
?><br /><h1><?php echo $lang["resourcemetadata"]?></h1><?php
$required_fields_exempt=array(); # new array to contain required fields that have not met the display condition 
for ($n=0;$n<count($fields);$n++)
	{
	# Should this field be displayed?
	if (!
		(
			# Field is an archive only field
			(($resource["archive"]==0) && ($fields[$n]["resource_type"]==999))
		||
			# Field has write access denied
			(checkperm("F*") && !checkperm("F-" . $fields[$n]["ref"])
			&& !($ref<0 && checkperm("P" . $fields[$n]["ref"])) # Upload only field
			)
		||			
			checkperm("F" . $fields[$n]["ref"])
		||
			($ref<0 && $fields[$n]["hide_when_uploading"] && $fields[$n]["required"]==0)		))
		
		{

	$name="field_" . $fields[$n]["ref"];
	$value=$fields[$n]["value"];
	$value=trim($value);
	
	if ($fields[$n]["omit_when_copying"] && $use!=$ref)
		{
		# Omit when copying - return this field back to the value it was originally, instead of using the current value which has been fetched from the new resource.
		reset($original_fields);
		foreach ($original_fields as $original_field)
			{
			if ($original_field["ref"]==$fields[$n]["ref"]) {$value=$original_field["value"];}
			}
		}
		
	#Check if field has a display condition set
	$displaycondition=true;
	if ($fields[$n]["display_condition"]!="")
		{
		$s=explode(";",$fields[$n]["display_condition"]);
		$condref=0;
		foreach ($s as $condition) # Check each condition
			{
			$displayconditioncheck=false;
			$s=explode("=",$condition);
			for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
				{
				if ($s[0]==$fields[$cf]["name"]) # this field needs to be checked
					{
					$scriptconditions[$condref]["field"] = $fields[$cf]["ref"];  # add new jQuery code to check value
					
					$checkvalues=$s[1];
					$validvalues=explode("|",strtoupper($checkvalues));
					$scriptconditions[$condref]["valid"]= "\"";
					$scriptconditions[$condref]["valid"].= implode("\",\"",$validvalues);
					$scriptconditions[$condref]["valid"].= "\"";
					$v=trim_array(explode(",",strtoupper($fields[$cf]["value"])));
					foreach ($validvalues as $validvalue)
						{
						if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value						
						}
					if (!$displayconditioncheck) {$displaycondition=false;$required_fields_exempt[]=$fields[$n]["ref"];}
					#add jQuery code to update on changes
						if ($fields[$cf]["type"]==2) # add onchange event to each checkbox field
							{
							# construct the value from the ticked boxes
							$val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
							$options=trim_array(explode(",",$fields[$cf]["options"]));
							?><script type="text/javascript">
							jQuery(document).ready(function() {<?php
							
							
							
								for ($m=0;$m<count($options);$m++)
									{
									$name=$fields[$cf]["ref"] . "_" . md5($options[$m]);
									echo "
									jQuery('input[name=\"" . $name . "\"]').change(function (){
										checkDisplayCondition" . $fields[$n]["ref"] . "();
										});";
									}
									
									
									
									
									?>
								}); 
							</script><?php							
							}	
						else
							{
							?>
							<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery('#field_<?php echo $fields[$cf]["ref"];?>').change(function (){
								
								checkDisplayCondition<?php echo $fields[$n]["ref"];?>();
									
								});
							}); 
							</script>
						<?php	
							}
					}
					
				} # see if next field needs to be checked
							
			$condref++;
			} # check next condition
		
		?>
		<script type="text/javascript">
		function checkDisplayCondition<?php echo $fields[$n]["ref"];?>() 
			{
			<?php echo "field" . $fields[$n]["ref"] . "status=jQuery('#question_" . $n . "').css('display');
			"; 
			echo "newfield" . $fields[$n]["ref"] . "status='none';
			"; 
			echo "newfield" . $fields[$n]["ref"] . "provisional=true;
			"; 
			
			foreach ($scriptconditions as $scriptcondition)
				{
				echo "newfield" . $fields[$n]["ref"] . "provisionaltest=false;
				";
				echo "if (jQuery('#field_" . $scriptcondition["field"] . "').length!=0)
					{";
					echo "
					fieldcheck" . $scriptcondition["field"] . "=jQuery('#field_" . $scriptcondition["field"] . "').val().toUpperCase();
					";
					echo "fieldvalues" . $scriptcondition["field"] . "=fieldcheck" . $scriptcondition["field"] . ".split(',');
					//alert(fieldvalues" . $scriptcondition["field"] . ");
					}";
					
				echo "
				else
					{
					";
					echo "fieldvalues" . $scriptcondition["field"] . "=new Array();
					";
					echo "checkedvals" . $scriptcondition["field"] . "=jQuery('input[name^=" . $scriptcondition["field"] . "_]')
					";
					echo "jQuery.each(checkedvals" . $scriptcondition["field"] . ",function(){
						if (jQuery(this).is(':checked'))
							{
							checktext" . $scriptcondition["field"] . "=jQuery(this).parent().next().text().toUpperCase();
							checktext" . $scriptcondition["field"] . " = jQuery.trim(checktext" . $scriptcondition["field"] . ");
							fieldvalues" . $scriptcondition["field"] . ".push(checktext" . $scriptcondition["field"] . ");
							//alert(fieldvalues" . $scriptcondition["field"] . ");
							}
						
						
						})
					}";
					
				echo "fieldokvalues" . $scriptcondition["field"] . "=new Array();
				";
				echo "fieldokvalues" . $scriptcondition["field"] . "=[" . $scriptcondition["valid"] . "];
				";
				echo "jQuery.each(fieldvalues" . $scriptcondition["field"] . ",function(f,v){
						//alert(\"checking value \" + fieldvalues" . $scriptcondition["field"] . " + \" against \" + fieldokvalues" . $scriptcondition["field"] . ");
						//alert(jQuery.inArray(fieldvalues" . $scriptcondition["field"] . ",fieldokvalues" . $scriptcondition["field"] . "));
						if ((jQuery.inArray(v,fieldokvalues" . $scriptcondition["field"] . "))>-1 || (fieldvalues" . $scriptcondition["field"] . " ==fieldokvalues" . $scriptcondition["field"] ." ))
							{
							newfield" . $fields[$n]["ref"] . "provisionaltest=true;
							}
						});
					
					if (newfield" . $fields[$n]["ref"] . "provisionaltest==false)
						{newfield" . $fields[$n]["ref"] . "provisional=false;}
					";
				}
				
			echo "
				exemptfieldsval=jQuery('#exemptfields').val();
				exemptfieldsarr=exemptfieldsval.split(',');
				if (newfield" . $fields[$n]["ref"] . "provisional==true)
					{
					if (jQuery.inArray(" . $fields[$n]["ref"] . ",exemptfieldsarr))
						{
						exemptfieldsarr.splice(jQuery.inArray(" . $fields[$n]["ref"] . ", exemptfieldsarr), 1 );
						}
					newfield" . $fields[$n]["ref"] . "status='block'
					}
				else
					{
					
				
					if ((jQuery.inArray(" . $fields[$n]["ref"] . ",exemptfieldsarr))==-1)
						{
						exemptfieldsarr.push(" . $fields[$n]["ref"] . ")
						}
					}
				jQuery('#exemptfields').val(exemptfieldsarr.join(","));
				
				
				";
			
			echo "if (newfield" . $fields[$n]["ref"] . "status!=field" . $fields[$n]["ref"] . "status)
					{
					jQuery('#question_" . $n . "').slideToggle();
					if (jQuery('#question_" . $n . "').css('display')=='block')
						{jQuery('#question_" . $n . "').css('border-top','');}
					else
						{jQuery('#question_" . $n . "').css('border-top','none');}
					}				
					
					";
			?>}
		</script>
	<?php		
		
		}
	
		
	if ($multilingual_text_fields)
		{
		# Multilingual text fields - find all translations and display the translation for the current language.
		$translations=i18n_get_translations($value);
		if (array_key_exists($language,$translations)) {$value=$translations[$language];} else {$value="";}
		}
	
	if ($multiple) {$value="";} # Blank the value for multi-edits.
	
	if (($fields[$n]["resource_type"]!=$lastrt)&& ($lastrt!=-1))
		{
		?><br /><h1 id="resource_type_properties"><?php echo htmlspecialchars(get_resource_type_name($fields[$n]["resource_type"]))?> <?php echo $lang["properties"]?></h1><?php
		}
	$lastrt=$fields[$n]["resource_type"];
	
	# Blank form if 'reset form' has been clicked.
	if (getval("resetform","")!="") {$value="";}

	# If config option $blank_edit_template is set, always show a blank form for user edit templates.
	if ($ref<0 && $blank_edit_template && getval("submitted","")=="") {$value="";}

	?>
	<?php if ($multiple && !hook("replace_edit_all_checkbox","",array($fields[$n]["ref"]))) { # Multiple items, a toggle checkbox appears which activates the question
	?><div><input name="editthis_<?php echo htmlspecialchars($name)?>" id="editthis_<?php echo $n?>" type="checkbox" value="yes" onClick="var q=document.getElementById('question_<?php echo $n?>');var m=document.getElementById('modeselect_<?php echo $n?>');var f=document.getElementById('findreplace_<?php echo $n?>');if (this.checked) {q.style.display='block';m.style.display='block';} else {q.style.display='none';m.style.display='none';f.style.display='none';document.getElementById('modeselectinput_<?php echo $n?>').selectedIndex=0;}">&nbsp;<label for="editthis<?php echo $n?>"><?php echo htmlspecialchars($fields[$n]["title"])?></label></div><?php } ?>

	<?php
	if ($multiple && !hook("replace_edit_all_mode_select","",array($fields[$n]["ref"])))
		{
		# When editing multiple, give option to select Replace All Text or Find and Replace
		?>
		<div class="Question" id="modeselect_<?php echo $n?>" style="display:none;padding-bottom:0;margin-bottom:0;">
		<label for="modeselectinput"><?php echo $lang["editmode"]?></label>
		<select id="modeselectinput_<?php echo $n?>" name="modeselect_<?php echo $fields[$n]["ref"]?>" class="stdwidth" onChange="var fr=document.getElementById('findreplace_<?php echo $n?>');var q=document.getElementById('question_<?php echo $n?>');if (this.value=='FR') {fr.style.display='block';q.style.display='none';} else {fr.style.display='none';q.style.display='block';}">
		<option value="RT"><?php echo $lang["replacealltext"]?></option>
		<?php if (in_array($fields[$n]["type"], array("0","1","5","8"))) {
		# Find and replace appies to text boxes only.
		?>
		<option value="FR"><?php echo $lang["findandreplace"]?></option>
		<?php } ?>
		<?php 
		if ($fields[$n]["type"]==0 || $fields[$n]["type"]==1 || $fields[$n]["type"]==5) { 
		# Prepend applies to text boxes only.
		?>
		<option value="PP"><?php echo $lang["prependtext"]?></option>
		<?php } 
		if (in_array($fields[$n]["type"], array("0","1","2","3","5","7","8","9"))) { 
		# Append applies to text boxes, checkboxes ,category tree and dropdowns only.
		?>
		<option value="AP"><?php echo $lang["appendtext"]?></option>
		<?php } 
		if ($fields[$n]["type"]==0 || $fields[$n]["type"]==1 || $fields[$n]["type"]==5 || $fields[$n]["type"]==2 || $fields[$n]["type"]==3) { ?>
		<!--- Remove applies to text boxes, checkboxes and dropdowns only. -->
		<option value="RM"><?php echo $lang["removetext"]?></option>
		<?php } ?>
		</select>
		</div>
		
		<div class="Question" id="findreplace_<?php echo $n?>" style="display:none;border-top:none;">
		<label>&nbsp;</label>
		<?php echo $lang["find"]?> <input type="text" name="find_<?php echo $fields[$n]["ref"]?>" class="shrtwidth">
		<?php echo $lang["andreplacewith"]?> <input type="text" name="replace_<?php echo $fields[$n]["ref"]?>" class="shrtwidth">
		</div>
		<?php
		}
	?>

	<div class="Question" id="question_<?php echo $n?>" <?php if ($multiple || !$displaycondition) {?>style="display:none;border-top:none;"<?php } ?>>
	<label for="<?php echo htmlspecialchars($name)?>"><?php if (!$multiple) {?><?php echo htmlspecialchars($fields[$n]["title"])?> <?php if (!$is_template && $fields[$n]["required"]==1) { ?><sup>*</sup><?php } ?><?php } ?></label>

	<?php
	# Autosave display
	if ($edit_autosave) { ?>
	<div class="AutoSaveStatus" id="AutoSaveStatus<?php echo $fields[$n]["ref"] ?>" style="display:none;"></div>
	<?php } ?>


	<?php
	# Define some Javascript for help actions (applies to all fields)
	$help_js="onBlur=\"HideHelp(" . $fields[$n]["ref"] . ");return false;\" onFocus=\"ShowHelp(" . $fields[$n]["ref"] . ");return false;\"";
	
	#hook to modify field type in special case. Returning zero (to get a standard text box) doesn't work, so return 1 for type 0, 2 for type 1, etc.
	$modified_field_type="";
	$modified_field_type=(hook("modifyfieldtype"));
	if ($modified_field_type){$fields[$n]["type"]=$modified_field_type-1;}

	hook("addfieldextras");
	# ----------------------------  Show field -----------------------------------
	$type=$fields[$n]["type"];
	if ($type=="") {$type=0;} # Default to text type.
	$field=$fields[$n];
	if (!hook("replacefield","",array($fields[$n]["type"],$fields[$n]["ref"],$n))) {include "edit_fields/" . $type . ".php";}
	# ----------------------------------------------------------------------------

	# Display any error messages from previous save
	if (array_key_exists($fields[$n]["ref"],$errors))
		{
		?>
		<div class="FormError">!! <?php echo $errors[$fields[$n]["ref"]]?> !!</div>
		<?php
		}

	if (trim($fields[$n]["help_text"]!=""))
		{
		# Show inline help for this field.
		# For certain field types that have no obvious focus, the help always appears.
		?>
		<div class="FormHelp" style="padding:0;<?php if (!in_array($fields[$n]["type"],array(2,4,6,7,10))) { ?> display:none;<?php } else { ?> clear:left;<?php } ?>" id="help_<?php echo $fields[$n]["ref"]?>"><div class="FormHelpInner"><?php echo nl2br(trim(htmlspecialchars(i18n_get_translated($fields[$n]["help_text"]))))?></div></div>
		<?php
		}

	# If enabled, include code to produce extra fields to allow multilingual free text to be entered.
	if ($multilingual_text_fields && ($fields[$n]["type"]==0 || $fields[$n]["type"]==1 || $fields[$n]["type"]==5))
		{
		include "../include/multilingual_fields.php";
		}
	?>			
	<div class="clearerleft"> </div>
	</div>
	<?php
	}
}

# Add required_fields_exempt so it is submitted with POST
echo " <input type=hidden name=\"exemptfields\" id=\"exemptfields\" value=\"" . implode(",",$required_fields_exempt) . "\">";	

# Work out the correct archive status.
if ($ref<0) # Upload template.
    {
    $modified_defaultstatus = hook("modifydefaultstatusmode");
    if ($archive==2)
        {
        if (checkperm("e2")) {$status = 2;} # Set status to Archived - if the user has the required permission.
        elseif ($modified_defaultstatus) {$status = $modified_defaultstatus;}  # Set the modified default status - if set.
        elseif (checkperm("e" . $resource["archive"])) {$status = $resource["archive"];} # Else, set status to the status stored in the user template - if the user has the required permission.
        elseif (checkperm("c")) {$status = 0;} # Else, set status to Active - if the user has the required permission.
        elseif (checkperm("d")) {$status = -2;} # Else, set status to Pending Submission.
        }
    else
        {
        if ($modified_defaultstatus) {$status = $modified_defaultstatus;}  # Set the modified default status - if set.
        elseif ($resource["archive"]!=2 && checkperm("e" . $resource["archive"])) {$status = $resource["archive"];} # Set status to the status stored in the user template - if the status is not Archived and if the user has the required permission.
        elseif (checkperm("c")) {$status = 0;} # Else, set status to Active - if the user has the required permission.
        elseif (checkperm("d")) {$status = -2;} # Else, set status to Pending Submission.   
        }

    if ($show_status_and_access_on_upload==false)
        {
        # Hide the dropdown, and set the default status.
        ?>
        <input type=hidden name="archive" id="archive" value="<?php echo htmlspecialchars($status)?>"><?php
        }
    }
else # Edit Resource(s).
    {
    $status = $resource["archive"];
    }

# Status / Access / Related Resources
if (!checkperm("F*")) # Only display Status / Access / Related Resources if full write access field access has been granted.
    {
    if(!hook("replacestatusandrelationshipsheader"))
        {
        if ($ref>0 || $show_status_and_access_on_upload==true)
        	{
	        if ($enable_related_resources && ($multiple || $ref>0)) # Showing relationships
	        	{
	        	?><br><h1><?php echo $lang["statusandrelationships"]?></h1><?php
		        }
		    else
		    	{
	        	?><br><h1><?php echo $lang["status"]?></h1><?php # Not showing relationships
		    	}
		    }
		    
        } /* end hook replacestatusandrelationshipsheader */

    hook("statreladdtopfields");

    # Status
    if ($ref>0 || $show_status_and_access_on_upload==true)
        {
        if(!hook("replacestatusselector"))
            {
            if ($multiple)
                { ?>
                <div id="editmultiple_status"><input name="editthis_status" id="editthis_status" value="yes" type="checkbox" onClick="var q=document.getElementById('question_status');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label id="editthis_status_label" for="editthis<?php echo $n?>"><?php echo $lang["status"]?></label></div><?php
                } ?>
            <div class="Question" id="question_status" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
            <label for="archive"><?php echo $lang["status"]?></label><?php
            
            # Autosave display
            if ($edit_autosave)
                { ?>
                <div class="AutoSaveStatus" id="AutoSaveStatusStatus" style="display:none;"></div><?php
                } ?>

            <select class="stdwidth" name="archive" id="archive" <?php if ($edit_autosave) {?>onChange="AutoSave('Status');"<?php } ?>><?php
            for ($n=-2;$n<=3;$n++)
                {
                if (checkperm("e" . $n)) { ?><option value="<?php echo $n?>" <?php if ($status==$n) { ?>selected<?php } ?>><?php echo $lang["status" . $n]?></option><?php }
                } ?>
            </select>
            <div class="clearerleft"> </div>
            </div><?php
            } /* end hook replacestatusselector */
        }

    # Access
    hook("beforeaccessselector");
    if (!hook("replaceaccessselector"))
        {
        if ($ref<0 && $show_status_and_access_on_upload==false)
            { 
            # Upload template and the status and access fields are configured to be hidden on uploads.
            ?>
            <input type=hidden name="access" value="<?php echo htmlspecialchars($resource["access"])?>"><?php
            }
        else
            {
            if ($multiple) { ?><div><input name="editthis_access" id="editthis_access" value="yes" type="checkbox" onClick="var q=document.getElementById('question_access');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editthis<?php echo $n?>"><?php echo $lang["access"]?></label></div><?php } ?>

            <div class="Question" id="question_access" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
            <label for="archive"><?php echo $lang["access"]?></label><?php

            # Autosave display
            if ($edit_autosave) { ?><div class="AutoSaveStatus" id="AutoSaveStatusAccess" style="display:none;"></div><?php } ?>

            <select class="stdwidth" name="access" id="access" onChange="var c=document.getElementById('custom_access');if (this.value==3) {c.style.display='block';} else {c.style.display='none';}<?php if ($edit_autosave) {?>AutoSave('Access');<?php } ?>"><?php

            for ($n=0;$n<=($custom_access?3:2);$n++)
                {
                if ($n==2 && checkperm("v"))
                    { ?>
                    <option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php
                    } 
                else if ($n!=2)
                    { ?>
                    <option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php
                    }
                } ?>
            </select>

            <div class="clearerleft"> </div>
            <table id="custom_access" cellpadding=3 cellspacing=3 style="padding-left:150px;<?php if (!$custom_access || $resource["access"]!=3) { ?>display:none;<?php } ?>"><?php

            $groups=get_resource_custom_access($ref);
            for ($n=0;$n<count($groups);$n++)
                {
                $access=2;$editable=true;
                if ($groups[$n]["access"]!="") {$access=$groups[$n]["access"];}
                $perms=explode(",",$groups[$n]["permissions"]);

                if (in_array("v",$perms)) {$access=0;$editable=false;} ?>
                    
                <tr>
                <td valign=middle nowrap><?php echo htmlspecialchars($groups[$n]["name"])?>&nbsp;&nbsp;</td>

                <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="0" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==0) { ?>checked <?php }
                if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                <td align=left valign=middle><?php echo $lang["access0"]?></td>

                <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="1" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==1) { ?>checked <?php }
                if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                <td align=left valign=middle><?php echo $lang["access1"]?></td><?php

                if (checkperm("v"))
                    { ?>
                    <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="2" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==2) { ?>checked <?php }
                    if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                    <td align=left valign=middle><?php echo $lang["access2"]?></td><?php
                    } ?>
                </tr><?php
                } ?>
            </table>
            <div class="clearerleft"> </div>
            </div><?php
            }
        } /* end hook replaceaccessselector */

    # Related Resources
    if ($enable_related_resources && ($multiple || $ref>0)) # Not when uploading
        {
        if ($multiple) { ?><div><input name="editthis_related" id="editthis_related" value="yes" type="checkbox" onClick="var q=document.getElementById('question_related');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editthis<?php echo $n?>"><?php echo $lang["relatedresources"]?></label></div><?php } ?>

        <div class="Question" id="question_related" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
        <label for="related"><?php echo $lang["relatedresources"]?></label><?php

        # Autosave display
        if ($edit_autosave) { ?><div class="AutoSaveStatus" id="AutoSaveStatusRelated" style="display:none;"></div><?php } ?>

        <textarea class="stdwidth" rows=3 cols=50 name="related" id="related"<?php
        if ($edit_autosave) {?>onChange="AutoSave('Related');"<?php } ?>><?php

        echo ((getval("resetform","")!="")?"":join(", ",get_related_resources($ref)))?></textarea>

        <div class="clearerleft"> </div>
        </div><?php
        } 
    }

if ($multiple && !$disable_geocoding)
	{
	# Multiple method of changing location.
	 ?>
	<br /><h1 id="location_title"><?php echo $lang["location-title"] ?></h1>
	<div><input name="editlocation" id="editlocation" type="checkbox" value="yes" onClick="var q=document.getElementById('editlocation_question');if (this.checked) {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editlocation"><?php echo $lang["location"] ?></label></div>
	<div class="Question" style="display:none;" id="editlocation_question">
	<label for="location"><?php echo $lang["latlong"]?></label>
	<input type="text" name="location" id="location" class="stdwidth">
	<div class="clearerleft"> </div>
	</div>
	<div><input name="editmapzoom" id="editmapzoom" type="checkbox" value="yes" onClick="var q=document.getElementById('editmapzoom_question');if (this.checked) {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editmapzoom"><?php echo $lang["mapzoom"] ?></label></div>
	<div class="Question" style="display:none;" id="editmapzoom_question">
	<label for="mapzoom"><?php echo $lang["mapzoom"]?></label>
	<select name="mapzoom" id="mapzoom">
		<option value=""><?php echo $lang["select"]?></option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12">12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
		<option value="17">17</option>
		<option value="18">18</option>
	</select>
	<div class="clearerleft"> </div>
	</div>
	<?php
	hook("locationextras");
	} 
	
	
	if (!$edit_upload_options_at_top){include '../include/edit_upload_options.php';}
	?>
		
	
	
	<div class="QuestionSubmit">
	<input name="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
	<input <?php if ($multiple) { ?>onclick="return confirm('<?php echo $lang["confirmeditall"]?>');"<?php } ?> name="save" type="submit" value="&nbsp;&nbsp;<?php echo ($ref>0)?$lang["save"]:$lang["next"]?>&nbsp;&nbsp;" /><br><br>
	<div class="clearerleft"> </div>
	</div>

	
<?php 
# Duplicate navigation
if (!$multiple && $ref>0) {EditNav();}
?>


</form>
<?php if (!$is_template) { ?><p><sup>*</sup> <?php echo $lang["requiredfield"]?></p><?php } ?>

<?php if (isset($show_error) && isset($save_errors)){
	foreach ($save_errors as $save_error_field=>$save_error_message)
		{	
		?>
	    <script type="text/javascript">
	    alert('<?php echo htmlspecialchars($save_error_message) ?>');
	    </script><?php
	    }
    }

hook("autolivejs");

include "../include/footer.php";
?>
