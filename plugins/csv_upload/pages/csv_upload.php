<?php
/**
 * CSV upload * 
 * @package ResourceSpace
 */

include dirname(__FILE__)."/../../../include/db.php";
include_once dirname(__FILE__)."/../../../include/general.php";
include dirname(__FILE__)."/../../../include/authenticate.php";
include dirname(__FILE__)."/../../../include/resource_functions.php";

include_once (dirname(__FILE__)."/../include/meta_functions.php");

include_once (dirname(__FILE__)."/../include/csv_functions.php");
include dirname(__FILE__)."/../../../include/header.php";

?><div class="BasicsBox"> 
<?php

if (!checkperm("c"))
	{	
	echo $lang['csv_upload_error_no_permission'] . "</div>";	
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
	}
?><h1><?php echo $lang['csv_upload_nav_link']; ?></h1>
<?php



	
# contants
	
$fd="user_{$userref}_uploaded_meta";			// file descriptor for uploaded file					// TODO: push these to a config file?
$override_fields=array("status","access");		// user can set if empty or override these fields
$process_csv=(getvalescaped("process_csv","")!="");
$override=getvalescaped("override","");
$selected_resource_type=getvalescaped("resource_type","");
$add_to_collection=getvalescaped("add_to_collection","");

# ----- we do not have a successfully submitted csv, so show the upload form an exit -----

$resource_types=meta_get_resource_types();
$csvdir = get_temp_dir() . DIRECTORY_SEPARATOR . "csv_upload" . DIRECTORY_SEPARATOR . $session_hash;

if ((!isset($_FILES[$fd]) || $_FILES[$fd]['error']>0) && !$process_csv)
	{	
	echo $lang["csv_upload_intro"];
	echo $lang["csv_upload_encoding_notice"];
	echo "<ul>";
	$condition=1;
	while(isset($lang["csv_upload_condition" . $condition]))
		{
		 echo $lang["csv_upload_condition" . $condition];
		 $condition++;
		 }
	echo "</ul>";
	?>
	<form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data">		
		<div class="Question">
			<label for="<?php echo $fd; ?>"><?php echo $lang['csv_upload_file'] ?></label>
			<input type="file" id="<?php echo $fd; ?>" name="<?php echo $fd; ?>" onchange="if(this.value==null || this.value=='') { jQuery('.file_selected').hide(); } else { jQuery('.file_selected').show(); } ">	
		</div>
		<?php foreach ($override_fields as $s)
		{	
		?><div class="file_selected Question" style="display: none;">
			<label for="<?php echo $s; ?>"><?php echo $lang[$s]?></label>				
			<select name="<?php echo $s; ?>" id="<?php echo $s; ?>" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('#<?php echo $s; ?>_action').hide(); } else { jQuery('#<?php echo $s; ?>_action').show(); }" class="stdwidth">
				<option value="default"><?php echo $lang['csv_upload_default'] ?></option><?php	
	$i=0;	
	while (isset($lang[$s . $i]))
	{
		?><option value="<?php echo $i; ?>"><?php echo $lang[$s . $i]; ?></option>
	<?php
		$i++;
	}
	?>				</select>
			<select id="<?php echo $s; ?>_action" name="<?php echo $s; ?>_action" style="display: none;" class="stdwidth" >					
				<option value="1"><?php echo $lang['csv_upload_unspecified'] ?></option>		
				<option value="2"><?php echo $lang['csv_upload_override'] ?></option>	
			</select>			
			<div class="clearerleft"></div>		
		</div>
		<?php
		}
	?>		
		<div class="file_selected Question" style="display: none;">
			<label for="resource_type"><?php echo $lang["property-resource_type"] ?></label>
			<select id="resource_type" name="resource_type" class="stdwidth" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('.override').hide();jQuery('.override').attr('disabled','disabled'); } else { jQuery('.override').removeAttr('disabled');jQuery('.override').show(); }">					
				<option value="default"><?php echo $lang['csv_upload_automatic'] ?></option>
				<?php	
					foreach ($resource_types as $resource_type=>$resource_name)
						{
						?><option value="<?php echo $resource_type; ?>"><?php echo $resource_type . ":" . $resource_name; ?></option>					
						<?php
						}
				?>
			</select>
			
			<select name="override" class="override" style="display: none;" class="stdwidth" disabled="disabled">
				<option value="0"><?php echo $lang['csv_upload_filter'] ?></option>
				<option value="1"><?php echo $lang['csv_upload_override'] ?></option>			
			</select>
			
			<div class="FormHelp">
				<div class="FormHelpInner">
					<p><?php echo $lang["csv_upload_automatic_notes"] ?></p>
					<ul style="display: none;" class="override">
						<li><?php echo $lang["csv_upload_filter_notes"] ?></li>										
						<li><?php echo $lang["csv_upload_override_notes"] ?></li>
					</ul>
				</div>
			</div>
					
		</div>
		
		<div class="file_selected Question" style="display: none;">
			<label for="add_to_collection"><?php echo $lang['addtocollection'] ?></label>
			<input type="checkbox" id="add_to_collection" name="add_to_collection" >	
		</div>
		
	
		<label for="submit" class="file_selected" style="display: none;"></label>
		<input type="submit" id="submit" value="Next" class="file_selected" style="display: none;">  <?php // TODO localise this ?>
	</form><?php
	}
elseif ($process_csv && file_exists($csvdir . DIRECTORY_SEPARATOR  . "csv_upload.csv"))
	{
	// We already have the validated uploaded file, process it
	echo "PROCESSING FILE<br>";
	$processcsv=true;
	$meta=meta_get_map();
	
	//$resource_types=array(1=>"Photo");	// debug
	if($override==1){$filtered_resource_types=array();$filtered_resource_types[$selected_resource_type]= $resource_types[$selected_resource_type];}
	if(isset($filtered_resource_types)){$resource_types = $filtered_resource_types;}
	
	$messages=array();
	

	csv_upload_process($csvdir . DIRECTORY_SEPARATOR  . "csv_upload.csv",$meta,$resource_types,$messages,$override,1,$processcsv);
	?><textarea rows="20" cols="100"><?php 
		foreach ($messages as $message)
			{
			echo $message . PHP_EOL;
			}
	?></textarea><?php
	}
else
	{
	// Validate the submitted file
	?>

	<form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
		<input type="submit" value="Back" onClick="CentralSpacePost(this,true);return false;">
	</form><br/>
	
	<?php  // TODO remove
		//echo "<pre>debug:";
		//print_r ($_POST);
		//echo "</pre>";	
	?>
	
	<b><?php echo $_FILES[$fd]['name']; ?></b><br/><br/>
	<?php
	
	$meta=meta_get_map();
	
	//$resource_types=array(1=>"Photo");	// debug
	if($override==1){$filtered_resource_types=array();$filtered_resource_types[$selected_resource_type]= $resource_types[$selected_resource_type];}
	if(isset($filtered_resource_types)){$resource_types = $filtered_resource_types;}
	
	$messages=array();
	//foreach(array_keys($meta) as $metadata)
	//	{echo $metadata . " ";}
		
	#print_r($resource_types);
		
	//exit($override);
	$processcsv=false;
	$validated=csv_upload_process($_FILES[$fd]['tmp_name'],$meta,$resource_types,$messages,$override,100,$processcsv);
	
	?><textarea rows="20" cols="100"><?php 
		foreach ($messages as $message)
			{
			echo $message . PHP_EOL;
			}
	?></textarea>
	
	<?php
	
	// at this stage we have valid data, so process (maybe only do this if "process" checkbox set?
	if (!$validated)
		{
		?>
		<h2>Error(s) found - aborting</h2>
		<?php
		}
	else
		{
		// We have a valid CSV, save it to a temporary location		
		// Create target dir if necessary
		if (!file_exists($csvdir))
			{
		    mkdir($csvdir,0777,true);
		    }
	    
		$result=move_uploaded_file($_FILES[$fd]['tmp_name'], $csvdir . DIRECTORY_SEPARATOR  . "csv_upload.csv");
		?>	
		<form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">		
		
		<?php foreach ($override_fields as $s)
			{	
			?>
			<input type="hidden" id="<?php echo $s ?>"  name="<?php echo $s ?>" value="<?php echo htmlspecialchars(getvalescaped($s,"")) ?>" > 
			<input type="hidden" id="<?php echo $s ?>_action"  name="<?php echo $s ?>_action" value="<?php echo htmlspecialchars(getvalescaped($s."_action","")) ?>" > 
			<?php
			}
			?>	
			
		<input type="hidden" id="override"  name="override" value="<?php echo htmlspecialchars($override) ?>" > 
		<input type="hidden" id="resource_type"  name="resource_type" value="<?php echo htmlspecialchars($selected_resource_type) ?>" > 

		<input type="hidden" id="add_to_collection" name="add_to_collection" value="<?php echo htmlspecialchars($add_to_collection) ?>">			
		<input type="hidden" id="process_csv"  name="process_csv" value="1" > 			
		<input type="submit" value="Process CSV" name="process_csv" onClick="CentralSpacePost(this,true);return false;">
		</form>
		<?php
	
		
		}

	}


?>
</div><!-- end of BasicsBox -->
<?php

include dirname(__FILE__)."/../../../include/footer.php";

