<?php
/**
 * Batch resource replace
 * 
 */
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";if (!checkperm("t")) {exit ("Permission denied.");}


// Get list of fields to allow selection of field containing file name to folder path
$allfields=get_resource_type_fields();
//print_r($allfields);
$no_exif = '';
if(!$metadata_read_default) {
	$no_exif = '&no_exif=yes';
}

include "../include/header.php";


?>

<h1><?php echo $lang["replaceresourcebatch"] ?></h1>

<p><?php echo $lang["batch_replace_filename_intro"] ?></p>

<form action="<?php echo $baseurl_short?>pages/upload_plupload.php">

<input type="hidden" name="replace" value="true" />
<input type="hidden" name="no_exif" value="<?php echo $no_exif; ?>" />

<div class="Question">
<label for="use_resourceid"><?php echo $lang["batch_replace_use_resourceid"]?></label>
<input type="checkbox" class="stdwidth" value="yes" name="use_resourceid" id="use_resourceid" onClick="if(this.checked){jQuery('#filename_field').attr('disabled','disabled');}else{jQuery('#filename_field').removeAttr('disabled');}" />
</div>

<div class="Question">
<label for="filename_field"><?php echo $lang["batch_replace_filename_field_select"]?></label>
<select  class="stdwidth" name="filename_field" id="filename_field">
<?php

foreach ($allfields as $metadatafield)
	{
	?>
	<option value="<?php echo $metadatafield["ref"] ?>">
	<?php echo i18n_get_translated($metadatafield["title"]) ?>	
	</option>    
	<?php
	}
?>
</select>
</div>



<div class="clearerleft"> </div>

<div class="Question">
<input type="submit" value="Upload" name="upload" id="upload_button" onClick="CentralSpacePost(this,true);" />
 </div>
</form>

<?php


include "../include/footer.php";
