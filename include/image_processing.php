<?php
/**
 * Image processing functions
 * 
 * Functions to allow upload and resizing of images.
 * 
 * @package ResourceSpace
 * @subpackage Includes
 * @todo Document
 */

if (!function_exists("upload_file")){
function upload_file($ref,$no_exif=false,$revert=false,$autorotate=false)
	{
	hook("beforeuploadfile","",array($ref));
	hook("clearaltfiles", "", array($ref)); // optional: clear alternative files before uploading new resource

	# revert is mainly for metadata reversion, removing all metadata and simulating a reupload of the file from scratch.
	
	hook ("removeannotations","",array($ref));

    global $lang;
    resource_log($ref,LOG_CODE_TRANSFORMED,'','','',$lang['upload_file']);

	$exiftool_fullpath = get_utility_path("exiftool");
	
	# Process file upload for resource $ref
	if ($revert==true){
		global $filename_field;
		$original_filename=get_data_by_field($ref,$filename_field);
		
		# Field 8 is used in a special way for staticsync, don't overwrite.
		$test_for_staticsync=get_resource_data($ref);
		if ($test_for_staticsync['file_path']!=""){$staticsync_mod=" and resource_type_field != 8";} else {$staticsync_mod="";}
		
		sql_query("delete from resource_data where resource=$ref $staticsync_mod");
		sql_query("delete from resource_keyword where resource=$ref $staticsync_mod");
		#clear 'joined' display fields which are based on metadata that is being deleted in a revert (original filename is reinserted later)
		$display_fields=get_resource_table_joins();
		if ($staticsync_mod!=""){
			$display_fields_new=array();
			for($n=0;$n<count($display_fields);$n++){
				if ($display_fields[$n]!=8){$display_fields_new[]=$display_fields[$n];}
			}
			$display_fields=$display_fields_new;
		}
		$clear_fields="";
		for ($x=0;$x<count($display_fields);$x++){ 
			$clear_fields.="field".$display_fields[$x]."=''";
			if ($x<count($display_fields)-1){$clear_fields.=",";}
			}	
		sql_query("update resource set ".$clear_fields." where ref=$ref");
		#also add the ref back into keywords:
		add_keyword_mappings($ref, $ref , -1);
		$extension=sql_value("select file_extension value from resource where ref=$ref","");
		$filename=get_resource_path($ref,true,"",false,$extension);
		$processfile['tmp_name']=$filename; }
	else{
		# Work out which file has been posted
		if (isset($_FILES['userfile'])) {$processfile=$_FILES['userfile'];} # Single upload (at least) needs this
		elseif (isset($_FILES['Filedata'])) {$processfile=$_FILES['Filedata'];} # Java upload (at least) needs this

		# Plupload needs this
		if (isset($_REQUEST['name'])) {
			$filename=$_REQUEST['name'];
			}
		else {$filename=$processfile['name'];}

		global $filename_field;
		if($no_exif && isset($filename_field)) {
			$user_set_filename            = get_data_by_field($ref, $filename_field);
			$user_set_filename_path_parts = pathinfo($user_set_filename);

			// $user_set_filename is for an already existing resource or when original filename is a visible field
			// on the upload form
			if(trim($user_set_filename) != '') {
				// Get extension of file just in case the user didn't provide one
				$path_parts = pathinfo($filename);
					
				$original_extension = $path_parts['extension'];

				if($original_extension == $user_set_filename_path_parts['extension'])
					{
					$filename = $user_set_filename;
					}

				// If the user filename doesn't have an extension add the original one
				$path_parts = pathinfo($filename);
				if(!isset($path_parts['extension'])) {
					$filename .= '.' . $original_extension;
				}
			}
		}
	}
    # Work out extension
	if (!isset($extension)){
		# first try to get it from the filename
		$extension=explode(".",$filename);
		if(count($extension)>1){
			$extension=escape_check(trim(strtolower($extension[count($extension)-1])));
			} 
		# if not, try exiftool	
		else if ($exiftool_fullpath!=false)
			{
            $cmd=$exiftool_fullpath." -filetype -s -s -s ".escapeshellarg($processfile['tmp_name']);
			$file_type_by_exiftool=run_command($cmd);
            resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $file_type_by_exiftool);
            if (strlen($file_type_by_exiftool)>0){$extension=str_replace(" ","_",trim(strtolower($file_type_by_exiftool)));$filename=$filename;}else{return false;}
			}
		# if no clue of extension by now, return false		
		else {return false;}	
	}
	
    # Banned extension?
    global $banned_extensions;
    if (in_array($extension,$banned_extensions)) {return false;}
    
    $status="Please provide a file name.";
    $filepath=get_resource_path($ref,true,"",true,$extension);

	if (!$revert){ 
    # Remove existing file, if present

    hook("beforeremoveexistingfile", "", array( "resourceId" => $ref ) );

    $old_extension=sql_value("select file_extension value from resource where ref='$ref'","");
    if ($old_extension!="")	
    	{
    	$old_path=get_resource_path($ref,true,"",true,$old_extension);
    	if (file_exists($old_path)) {unlink($old_path);}
    	}

	// also remove any existing extracted icc profiles
    	$icc_path=get_resource_path($ref,true,"",true,$extension.'.icc');
    	if (file_exists($icc_path)) {unlink($icc_path);}
    	global $pdf_pages;
    	$iccx=0; // if there is a -0.icc page, run through and delete as many as necessary.
    	$finished=false;
		$badicc_path=str_replace(".icc","-$iccx.icc",$icc_path);
		while (!$finished){
			if (file_exists($badicc_path)){unlink($badicc_path);$iccx++;$badicc_path=str_replace(".icc","-$iccx.icc",$icc_path);}
			else {$finished=true;}
		}
		$iccx=0;
	}	

	if (!$revert){
    if ($filename!="")
    	{
    	global $jupload_alternative_upload_location, $plupload_upload_location;
    	if (isset($plupload_upload_location))
    		{
    		# PLUpload - file was sent chunked and reassembled - use the reassembled file location
			$result=rename($plupload_upload_location, $filepath);
    		}
		elseif (isset($jupload_alternative_upload_location))
    		{
    		# JUpload - file was sent chunked and reassembled - use the reassembled file location
		    $result=rename($jupload_alternative_upload_location, $filepath);
    		}
		else
			{
			# Standard upload.
			if (!$revert){
		    $result=move_uploaded_file($processfile['tmp_name'], $filepath);
			} else {$result=true;}
		}
			
    	if ($result==false)
       	 	{
       	 	$status="File upload error. Please check the size of the file you are trying to upload.";
       	 	return false;
       	 	}
     	else
     		{
		
		global $camera_autorotation;
		global $ffmpeg_audio_extensions;
		if ($camera_autorotation){
			if ($autorotate && (!in_array($extension,$ffmpeg_audio_extensions))){
				AutoRotateImage($filepath);
			}
		}

     		chmod($filepath,0777);

		global $icc_extraction;
		global $ffmpeg_supported_extensions;
		if ($icc_extraction && $extension!="pdf" && !in_array($extension, $ffmpeg_supported_extensions)){
			extract_icc_profile($ref,$extension);
		}


		$status="Your file has been uploaded.";
    	 	}
    	}
    }	
    
	# Store extension in the database and update file modified time.
	if ($revert){$has_image="";} else {$has_image=",has_image=0";}
    sql_query("update resource set file_extension='$extension',preview_extension='jpg',file_modified=now() $has_image where ref='$ref'");

	# delete existing resource_dimensions
    sql_query("delete from resource_dimensions where resource='$ref'");
	# get file metadata 
    if(!$no_exif) {
    	extract_exif_comment($ref,$extension);
    } else {
    	
    	global $merge_filename_with_title, $lang;
		if($merge_filename_with_title) {

			$merge_filename_with_title_option = urlencode(getval('merge_filename_with_title_option', ''));
			$merge_filename_with_title_include_extensions = urlencode(getval('merge_filename_with_title_include_extensions', ''));
			$merge_filename_with_title_spacer = urlencode(getval('merge_filename_with_title_spacer', ''));

			$original_filename = '';
			if(isset($_REQUEST['name'])) {
				$original_filename = $_REQUEST['name'];
			} else {
				$original_filename = $processfile['name'];
			}

			if($merge_filename_with_title_include_extensions == 'yes') {
				$merged_filename = $original_filename;
			} else {
				$merged_filename = strip_extension($original_filename);
			}

			// Get title field:
			$resource = get_resource_data($ref);
			$read_from = get_exiftool_fields($resource['resource_type']);

			for($i = 0; $i < count($read_from); $i++) {
				
				if($read_from[$i]['name'] == 'title') {
					$oldval = get_data_by_field($ref, $read_from[$i]['ref']);

					if(strpos($oldval, $merged_filename) !== FALSE) {
						continue;
					}
					
					switch ($merge_filename_with_title_option) {
						case $lang['merge_filename_title_do_not_use']:
							// Do nothing since the user doesn't want to use this feature
							break;

						case $lang['merge_filename_title_replace']:
							$newval = $merged_filename;
							break;

						case $lang['merge_filename_title_prefix']:
							$newval = $merged_filename . $merge_filename_with_title_spacer . $oldval;
							if($oldval == '') {
								$newval = $merged_filename;
							}
							break;

						case $lang['merge_filename_title_suffix']:
							$newval = $oldval . $merge_filename_with_title_spacer . $merged_filename;
							if($oldval == '') {
								$newval = $merged_filename;
							}
							break;

						default:
							// Do nothing
							break;
					}

					update_field($ref, $read_from[$i]['ref'], $newval);
				
				}

			}

		}

    }
	
	# extract text from documents (e.g. PDF, DOC).
	global $extracted_text_field;
	if (isset($extracted_text_field) && !$no_exif) {
		if (isset($unoconv_path) && in_array($extension,$unoconv_extensions)){
			// omit, since the unoconv process will do it during preview creation below
			}
		else {
		extract_text($ref,$extension);
		}
	}

	# Store original filename in field, if set
	global $filename_field,$amended_filename;
	if (isset($filename_field))
		if(isset($amended_filename)){$filename=$amended_filename;}
		{
		if (!$revert){
			update_field($ref,$filename_field,$filename);
			}
		else {
			update_field($ref,$filename_field,$original_filename);
			}		
		}
    
   if (!$revert)
		{
		# Clear any existing FLV file or multi-page previews.
		global $pdf_pages;
		for ($n=2;$n<=$pdf_pages;$n++)
			{
			# Remove preview page.
			$path=get_resource_path($ref,true,"scr",false,"jpg",-1,$n,false);
			if (file_exists($path)) {unlink($path);}
			# Also try the watermarked version.
			$path=get_resource_path($ref,true,"scr",false,"jpg",-1,$n,true);
			if (file_exists($path)) {unlink($path);}
			}
		
		# Remove any FLV video preview (except if the actual resource is an FLV file).
		global $ffmpeg_preview_extension;
		if ($extension!=$ffmpeg_preview_extension)
			{
			$path=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension);
			if (file_exists($path)) {unlink($path);}
			}
		# Remove any FLV preview-only file
		$path=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
		if (file_exists($path)) {unlink($path);}
	
		
		# Remove any MP3 (except if the actual resource is an MP3 file).
		if ($extension!="mp3")
			{
			$path=get_resource_path($ref,true,"",false,"mp3");
			if (file_exists($path)) {unlink($path);}
			}	
		
		# Create previews
		global $enable_thumbnail_creation_on_upload,$file_upload_block_duplicates,$checksum;
		# Checksums are also normally created at preview generation time, but we may already have a checksum if $file_upload_block_duplicates is enabled
		$checksum_required=true;
		if($file_upload_block_duplicates && isset($checksum))
			{
			sql_query("update resource set file_checksum='" . escape_check($checksum) . "' where ref='$ref'");
			$checksum_required=false;
			}
		if ($enable_thumbnail_creation_on_upload)
			{ 
			create_previews($ref,false,$extension,false,false,-1,false,false,$checksum_required);
			}
		else
			{
			# Offline thumbnail generation is being used. Set 'has_image' to zero so the offline create_previews.php script picks this up.
			sql_query("update resource set has_image=0 where ref='$ref'");
			}
		}
	
	# Update file dimensions
	get_original_imagesize($ref,$filepath,$extension);
	
	hook("Uploadfilesuccess", "", array( "resourceId" => $ref ) );
	
	# Update disk usage
	update_disk_usage($ref);
	
	# Log this activity.
	$log_ref=resource_log($ref,"u",0);
	hook("upload_image_after_log_write","",array($ref,$log_ref));
	
    return $status;
    }}

function extract_exif_comment($ref,$extension="")
	{
	# Extract the EXIF comment from either the ImageDescription field or the UserComment
	# Also parse IPTC headers and insert
	# EXIF headers
	$exifoption=getval("no_exif",""); // This may have been set to a non-standard value if allowing per field selection
	if($exifoption=="yes"){$exifoption="no";} // Sounds odd but previously was no_exif so logic reversed						
	if($exifoption==""){$exifoption="yes";}
	
	$image=get_resource_path($ref,true,"",false,$extension);
	if (!file_exists($image)) {return false;}
	hook("pdfsearch");

	global $exif_comment,$exiftool_no_process,$exiftool_resolution_calc, $disable_geocoding, $embedded_data_user_select_fields,$filename_field,$lang;
    resource_log($ref,LOG_CODE_TRANSFORMED,'','','',$lang['exiftooltag']);

	$exiftool_fullpath = get_utility_path("exiftool");
	if (($exiftool_fullpath!=false) && !in_array($extension,$exiftool_no_process))
		{
		$resource=get_resource_data($ref);
		
		# Field 8 is used in a special way for staticsync; don't overwrite.
		if ($resource['file_path']!=""){$omit_title_for_staticsync=true;} else {$omit_title_for_staticsync=false;}
		
		hook("beforeexiftoolextraction");
		
		if ($exiftool_resolution_calc)
			{
			# see if we can use exiftool to get resolution/units, and dimensions here.
			# Dimensions are normally extracted once from the view page, but for the original file, it should be done here if possible,
			# and exiftool can provide more data. 
		
			$command = $exiftool_fullpath . " -s -s -s -t -composite:imagesize -xresolution -resolutionunit " . escapeshellarg($image);
			$dimensions_resolution_unit=explode("\t",run_command($command));
            resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $dimensions_resolution_unit);

            # if dimensions resolution and unit could be extracted, add them to the database.
			# they can be used in view.php to give more accurate data.
			if (count($dimensions_resolution_unit)==3)
				{
				$dru=$dimensions_resolution_unit;
				$filesize=filesize_unlimited($image); 
				$wh=explode("x",$dru[0]);
				$width=$wh[0];
				$height=$wh[1];
				$resolution=$dru[1];
				$unit=$dru[2];
				sql_query("insert into resource_dimensions (resource, width, height, resolution, unit, file_size) values ('$ref', '$width', '$height', '$resolution', '$unit', '$filesize')");  
				}
			}
		
		$read_from=get_exiftool_fields($resource['resource_type']);

		# run exiftool to get all the valid fields. Use -s -s option so that
		# the command result isn't printed in columns, which will help in parsing
		# We then split the lines in the result into an array
		$command = $exiftool_fullpath . " -s -s -f -m -d \"%Y-%m-%d %H:%M:%S\" -G " . escapeshellarg($image);
        $output=run_command($command);
        $metalines = explode("\n",$output);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $output);

        $metadata = array(); # an associative array to hold metadata field/value pairs
		
		# go through each line and split field/value using the first
		# occurrance of ": ".  The keys in the associative array is converted
		# into uppercase for easier lookup later
		foreach($metalines as $metaline)
			{
			# Use stripos() if available, but support earlier PHP versions if not.
			if (function_exists("stripos"))
				{
				$pos=stripos($metaline, ": ");
				}
			else
				{
				$pos=strpos($metaline, ": ");
				}

			if ($pos) #get position of first ": ", return false if not exist
				{
				# add to the associative array, also clean up leading/trailing space & single quote (on windows sometimes)
				
				# Extract group name and tag name.
				$s=explode("]",substr($metaline, 0, $pos));
				if (count($s)>1 && strlen($s[0])>1)
					{
					# Extract value
					$value=trim(substr($metaline,$pos+2));

					# Replace '..' with line feed - either Exiftool itself or Adobe Bridge replaces line feeds with '..'
					$value = str_replace('....', '\n\n', $value); // Two new line feeds in ExifPro are replaced with 4 dots '....'
					$value=str_replace('...','.\n',$value); # Three dots together is interpreted as a full stop then line feed, not the other way round
					$value=str_replace('..','\n',$value);
					
					# Extract group name and tag name
					$groupname=strtoupper(substr($s[0],1));
					$tagname=strtoupper(trim($s[1]));
					
					# Store both tag data under both tagname and groupname:tagname, to support both formats when mapping fields. 
					$metadata[$tagname] = $value;
					$metadata[$groupname . ":" . $tagname] = $value;
					debug("Exiftool: extracted field '$groupname:$tagname', value is '$value'",RESOURCE_LOG_APPEND_PREVIOUS);
					}
				}
			}

		// We try to fetch the original filename from database.
		$resources = sql_query("SELECT resource.file_path FROM resource WHERE resource.ref = " . $ref);

		if($resources)
			{
			$resource = $resources[0];
			if($resource['file_path'])
				{
				$metadata['FILENAME'] = mb_basename($resource['file_path']);
				}
			}


		if (isset($metadata['FILENAME'])) {$metadata['STRIPPEDFILENAME'] = strip_extension($metadata['FILENAME']);}

		# Geolocation Metadata Support
		if (!$disable_geocoding && isset($metadata['GPSLATITUDE']))
			{
			# Set vars
            $dec_long=0;$dec_lat=0;

            #Convert latititude to decimal.
            if (preg_match("/^(?<degrees>\d+) deg (?<minutes>\d+)' (?<seconds>\d+\.?\d*)\"/", $metadata['GPSLATITUDE'], $latitude)){
                $dec_lat = $latitude['degrees'] + $latitude['minutes']/60 + $latitude['seconds']/(60*60);
            }
            if (preg_match("/^(?<degrees>\d+) deg (?<minutes>\d+)' (?<seconds>\d+\.?\d*)\"/", $metadata['GPSLONGITUDE'], $longitude)){
                $dec_long = $longitude['degrees'] + $longitude['minutes']/60 + $longitude['seconds']/(60*60);           
            }
           
            if (strpos($metadata['GPSLATITUDE'],'S')!==false)
                $dec_lat = -1 * $dec_lat;
            if (strpos($metadata['GPSLONGITUDE'],'W')!==false) 
                $dec_long = -1 * $dec_long;

            if ($dec_long!=0 && $dec_lat!=0)
            	{
                sql_query("update resource set geo_long='" . escape_check($dec_long) . "',geo_lat='" . escape_check($dec_lat) . "' where ref='$ref'");
            	}
			}
        
        # Update portrait_landscape_field (when reverting metadata this was getting lost)
        update_portrait_landscape_field($ref);
        
		# now we lookup fields from the database to see if a corresponding value
		# exists in the uploaded file
		$exif_updated_fields=array();
		for($i=0;$i< count($read_from);$i++)
			{
			$field=explode(",",$read_from[$i]['exiftool_field']);
			foreach ($field as $subfield)
				{
				$subfield = strtoupper($subfield); // convert to upper case for easier comparision
				if (in_array($subfield, array_keys($metadata)) && $metadata[$subfield] != "-" && trim($metadata[$subfield])!="")
					{
					$read=true;
					$value=$metadata[$subfield];
					
					# Dropdown box or checkbox list?
					if ($read_from[$i]["type"]==2 || $read_from[$i]["type"]==3)
						{
						# Check that the value is one of the options and only insert if it is an exact match.
	
						# The use of safe_file_name and strtolower ensures matching takes place on alphanumeric characters only and ignores case.
						
						# First fetch all options in all languages
						$options=trim_array(explode(",",strtolower($read_from[$i]["options"])));
						for ($n=0;$n<count($options);$n++)	{$options[$n]=$options[$n];}

						# If not in the options list, do not read this value
						$s=trim_array(explode(",",$value));
						$value=""; # blank value
						for ($n=0;$n<count($s);$n++)
							{
							if (trim($s[0])!="" && (in_array(strtolower($s[$n]),$options))) {$value.="," . $s[$n];} 							
							}
						#echo($read_from[$i]["ref"] . " = " . $value . "<br>");
						}
					
					# Read the data.				
					if ($read) {
						$plugin=dirname(__FILE__)."/../plugins/exiftool_filter_" . $read_from[$i]['name'] . ".php";
						if ($read_from[$i]['exiftool_filter']!="")
							{
							eval($read_from[$i]['exiftool_filter']);
							}
						if (file_exists($plugin)) {include $plugin;}
		
						# Field 8 is used in a special way for staticsync; don't overwrite field 8 in this case
						if (!($omit_title_for_staticsync && $read_from[$i]['ref']==8))
							{				
							$exiffieldoption=$exifoption;
							
							if($exifoption=="custom"  || (isset($embedded_data_user_select_fields)  && in_array($read_from[$i]['ref'],$embedded_data_user_select_fields)))
								{									
								debug ("EXIF - custom option for field " . $read_from[$i]['ref'] . " : " . $exifoption,RESOURCE_LOG_APPEND_PREVIOUS);
								$exiffieldoption=getval("exif_option_" . $read_from[$i]['ref'],$exifoption);	
								}
							
							debug ("EXIF - option for field " . $read_from[$i]['ref'] . " : " . $exiffieldoption,RESOURCE_LOG_APPEND_PREVIOUS);
							
							if($exiffieldoption=="no")
								{continue;}
							
							elseif($exiffieldoption=="append")
								{
								$spacechar=($read_from[$i]["type"]==2 || $read_from[$i]["type"]==3)?", ":" ";
								$oldval = get_data_by_field($ref,$read_from[$i]['ref']);
								if(strpos($oldval, $value)!==false){continue;}
								$newval =  $oldval . $spacechar . iptc_return_utf8($value) ;									
								}
							elseif($exiffieldoption=="prepend")
								{
								$spacechar=($read_from[$i]["type"]==2 || $read_from[$i]["type"]==3)?", ":" ";
								$oldval = get_data_by_field($ref,$read_from[$i]['ref']);
								if(strpos($oldval, $value)!==false){continue;}
								$newval =  iptc_return_utf8($value) . $spacechar . $oldval;
								}							
							else
								{
								$newval =  iptc_return_utf8($value);	
								}

							global $merge_filename_with_title, $lang;
							if($merge_filename_with_title) {

								$merge_filename_with_title_option = urlencode(getval('merge_filename_with_title_option', ''));
								$merge_filename_with_title_include_extensions = urlencode(getval('merge_filename_with_title_include_extensions', ''));
								$merge_filename_with_title_spacer = urlencode(getval('merge_filename_with_title_spacer', ''));

								$original_filename = '';
								if(isset($_REQUEST['name'])) {
									$original_filename = $_REQUEST['name'];
								} else {
									$original_filename = $processfile['name'];
								}

								if($merge_filename_with_title_include_extensions == 'yes') {
									$merged_filename = $original_filename;
								} else {
									$merged_filename = strip_extension($original_filename);
								}

								$oldval = get_data_by_field($ref, $read_from[$i]['ref']);
								if(strpos($oldval, $value) !== FALSE) {
									continue;
								}
								
								switch ($merge_filename_with_title_option) {
									case $lang['merge_filename_title_do_not_use']:
										// Do nothing since the user doesn't want to use this feature
										break;

									case $lang['merge_filename_title_replace']:
										$newval = $merged_filename;
										break;

									case $lang['merge_filename_title_prefix']:
										$newval = $merged_filename . $merge_filename_with_title_spacer . $oldval;
										if($oldval == '') {
											$newval = $merged_filename;
										}
										break;
									case $lang['merge_filename_title_suffix']:
										$newval = $oldval . $merge_filename_with_title_spacer . $merged_filename;
										if($oldval == '') {
											$newval = $merged_filename;
										}
										break;

									default:
										// Do nothing
										break;
								}

							}
							
							update_field($ref,$read_from[$i]['ref'],$newval);
							$exif_updated_fields[]=$read_from[$i]['ref'];
							
							
							hook("metadata_extract_addition","all",array($ref,$newval,$read_from,$i));
							}
						}

					} else {

						// Process if no embedded title is found:
						global $merge_filename_with_title, $lang;
						if($merge_filename_with_title && $read_from[$i]['ref'] == 8) {

							$merge_filename_with_title_option = urlencode(getval('merge_filename_with_title_option', ''));
							$merge_filename_with_title_include_extensions = urlencode(getval('merge_filename_with_title_include_extensions', ''));
							$merge_filename_with_title_spacer = urlencode(getval('merge_filename_with_title_spacer', ''));

							$original_filename = '';
							if(isset($_REQUEST['name'])) {
								$original_filename = $_REQUEST['name'];
							} else {
								$original_filename = $processfile['name'];
							}

							if($merge_filename_with_title_include_extensions == 'yes') {
								$merged_filename = $original_filename;
							} else {
								$merged_filename = strip_extension($original_filename);
							}

							$oldval = get_data_by_field($ref, $read_from[$i]['ref']);
							if(strpos($oldval, $value) !== FALSE) {
								continue;
							}
							
							switch ($merge_filename_with_title_option) {
								case $lang['merge_filename_title_do_not_use']:
									// Do nothing since the user doesn't want to use this feature
									break;

								case $lang['merge_filename_title_replace']:
									$newval = $merged_filename;
									break;

								case $lang['merge_filename_title_prefix']:
									$newval = $merged_filename . $merge_filename_with_title_spacer . $oldval;
									if($oldval == '') {
										$newval = $merged_filename;
									}
									break;
								case $lang['merge_filename_title_suffix']:
									$newval = $oldval . $merge_filename_with_title_spacer . $merged_filename;
									if($oldval == '') {
										$newval = $merged_filename;
									}
									break;

								default:
									// Do nothing
									break;
							}
							
							update_field($ref,$read_from[$i]['ref'],$newval);
							$exif_updated_fields[]=$read_from[$i]['ref'];

						}

					}

				}
			}
		if(!in_array($filename_field,$exif_updated_fields)) // We have not found an embedded value for this field so we need to modify the $filename variable which will be used to set the data later in the upload_file function
			{
			$exiffilenameoption=getval("exif_option_" . $filename_field,$exifoption);			
			debug ("EXIF - custom option for filename field " . $filename_field . " : " . $exiffilenameoption,RESOURCE_LOG_APPEND_PREVIOUS);
			if ($exiffilenameoption!="yes") // We are not using the extracted filename as usual
				{
				$uploadedfilename=isset($_REQUEST['name'])?$_REQUEST['name']:$processfile['name'];
				
				global $userref, $amended_filename;
				$entered_filename=get_data_by_field(-$userref,$filename_field);
				debug("EXIF - got entered file name " . $entered_filename,RESOURCE_LOG_APPEND_PREVIOUS);
				if($exiffilenameoption=="no") //Use the entered value
				{
					$amended_filename = $entered_filename;

					if(trim($amended_filename) == '') {
						$amended_filename = $uploadedfilename;
					}

					if(strpos($amended_filename, $extension) === FALSE) {
						$amended_filename .= '.' . $extension;
					}

				}
				elseif($exiffilenameoption=="append")
					{
					$amended_filename =  $entered_filename . $uploadedfilename;
					}
				elseif($exiffilenameoption=="prepend")
					{					
					$amended_filename =  strip_extension($uploadedfilename) . $entered_filename . "." . $extension;
					}
				debug("EXIF - created new file name " . $amended_filename,RESOURCE_LOG_APPEND_PREVIOUS);
				}
			}
		}
	elseif (isset($exif_comment))
		{
		#
		# Exiftool is not installed. As a fallback we grab some predefined basic fields using the PHP function
		# exif_read_data()
		#		
		
		if (function_exists("exif_read_data"))
			{
			$data=@exif_read_data($image);
			}
		else
			{
			$data = false;
			}
		
		if ($data!==false)
			{
			$comment="";
			#echo "<pre>EXIF\n";print_r($data);exit();

			if (isset($data["ImageDescription"])) {$comment=$data["ImageDescription"];}
			if (($comment=="") && (isset($data["COMPUTED"]["UserComment"]))) {$comment=$data["COMPUTED"]["UserComment"];}
			if ($comment!="")
				{
				# Convert to UTF-8
				$comment=iptc_return_utf8($comment);
				
				# Save comment
				global $exif_comment;
				update_field($ref,$exif_comment,$comment);
				}
			if (isset($data["Model"]))
				{
				# Save camera make/model
				global $exif_model;
				update_field($ref,$exif_model,$data["Model"]);
				}
			if (isset($data["DateTimeOriginal"]))
				{
				# Save camera date/time
				global $exif_date;
				$date=$data["DateTimeOriginal"];
				# Reformat date to ISO standard
				$date=substr($date,0,4) . "-" . substr($date,5,2) . "-" . substr($date,8,11);
				update_field($ref,$exif_date,$date);
				}
			}
			
		# Try IPTC headers
		$size = getimagesize($image, $info);
		if (isset($info["APP13"]))
			{
			$iptc = iptcparse($info["APP13"]);
			#echo "<pre>IPTC\n";print_r($iptc);exit();

			# Look for iptc fields, and insert.
			$fields=sql_query("select * from resource_type_field where length(iptc_equiv)>0");
			for ($n=0;$n<count($fields);$n++)
				{
				$iptc_equiv=$fields[$n]["iptc_equiv"];
				if (isset($iptc[$iptc_equiv][0]))
					{
					# Found the field
					if (count($iptc[$iptc_equiv])>1)
						{
						# Multiple values (keywords)
						$value="";
						for ($m=0;$m<count($iptc[$iptc_equiv]);$m++)
							{
							if ($m>0) {$value.=", ";}
							$value.=$iptc[$iptc_equiv][$m];
							}
						}
					else
						{
						$value=$iptc[$iptc_equiv][0];
						}
						
					$value=iptc_return_utf8($value);
					
					# Date parsing
					if ($fields[$n]["type"]==4)
						{
						$value=substr($value,0,4) . "-" . substr($value,4,2) . "-" . substr($value,6,2);
						}
					
					if (trim($value)!="") {update_field($ref,$fields[$n]["ref"],$value);}
					}			
				}
			}
		}
	
	# Update the XML metadata dump file.
	update_xml_metadump($ref);
	
	# Auto fill any blank fields.
	autocomplete_blank_fields($ref);
	
	}

function iptc_return_utf8($text)
	{
	# For the given $text, return the utf-8 equiv.
	# Used for iptc headers to auto-detect the character encoding.
	global $iptc_expectedchars,$mysql_charset;
	
	# No inconv library? Return text as-is
	if (!function_exists("iconv")) {return $text;}
	
	# No expected chars set? Return as is
	if ($iptc_expectedchars=="" || $mysql_charset=="utf8") {return $text;}
	
	$try=array("UTF-8","ISO-8859-1","Macintosh","Windows-1252");
	for ($n=0;$n<count($try);$n++)
		{
		if ($try[$n]=="UTF-8") {$trans=$text;} else {$trans=@iconv($try[$n], "UTF-8", $text);}
		for ($m=0;$m<strlen($iptc_expectedchars);$m++)
			{
			if (strpos($trans,substr($iptc_expectedchars,$m,1))!==false) {return $trans;}
			}
		}
	return $text;
	}
 
function create_previews($ref,$thumbonly=false,$extension="jpg",$previewonly=false,$previewbased=false,$alternative=-1,$ignoremaxsize=false,$ingested=false,$checksum_required=true)
	{
    global $keep_for_hpr,$imagemagick_path, $preview_generate_max_file_size,$autorotate_no_ingest, $previews_allow_enlarge,$lang;
   
    // keep_for_hpr will be set to true if necessary in preview_preprocessing.php to indicate that an intermediate jpg can serve as the hpr.
    // otherwise when the file extension is a jpg it's assumed no hpr is needed.

    resource_log($ref,LOG_CODE_TRANSFORMED,'','','',$lang['createpreviews']);

	# Debug
	debug("create_previews(ref=$ref,thumbonly=$thumbonly,extension=$extension,previewonly=$previewonly,previewbased=$previewbased,alternative=$alternative,ingested=$ingested,checksum_required=$checksum_required)",RESOURCE_LOG_APPEND_PREVIOUS);

	if (!$previewonly) {
		// make sure the extension is the same as the original so checksums aren't done for previews
		$o_ext=sql_value("select file_extension value from resource where ref={$ref}","");
		if($extension==$o_ext && $checksum_required)
			{
			debug("create_previews - generate checksum for $ref",RESOURCE_LOG_APPEND_PREVIOUS);
			generate_file_checksum($ref,$extension);
			}
	}
	# first reset preview tweaks to 0
	sql_query("update resource set preview_tweaks = '0|1' where ref = '$ref'");

	// for compatibility with transform plugin, remove any
        // transform previews for this resource when regenerating previews
        $tpdir = get_temp_dir() . "/transform_plugin";
        if(is_dir($tpdir) && file_exists("$tpdir/pre_$ref.jpg")){
            unlink("$tpdir/pre_$ref.jpg");
        }

	# pages/tools/update_previews.php?previewbased=true
	# use previewbased to avoid touching original files (to preserve manually-uploaded preview images
	# when regenerating previews (i.e. for watermarks)
	if($previewbased || ($autorotate_no_ingest && !$ingested))
		{
		$file=get_resource_path($ref,true,"lpr",false,"jpg",-1,1,false,"",$alternative);	
		if (!file_exists($file))
			{
			$file=get_resource_path($ref,true,"scr",false,"jpg",-1,1,false,"",$alternative);		
			if (!file_exists($file))
				{
				$file=get_resource_path($ref,true,"pre",false,"jpg",-1,1,false,"",$alternative);		
				if(!file_exists($file) && $autorotate_no_ingest && !$ingested)
					{
					$file=get_resource_path($ref,true,"",false,$extension,-1,1,false,"",$alternative);
					}
				}
			}
		}
	else if (!$previewonly)
		{
		$file=get_resource_path($ref,true,"",false,$extension,-1,1,false,"",$alternative);
		}
	else
		{
		# We're generating based on a new preview (scr) image.
		$file=get_resource_path($ref,true,"tmp",false,"jpg");	
		}
	
	# Debug
	debug("File source is $file",RESOURCE_LOG_APPEND_PREVIOUS);
	# Make sure the file exists, if not update preview_attempts so that we don't keep trying to generate a preview
	if (!file_exists($file)) 
		{
		sql_query("update resource set preview_attempts=ifnull(preview_attempts,0) + 1 where ref='$ref'");
		return false;
		}
					

	
	# If configured, make sure the file is within the size limit for preview generation
	if (isset($preview_generate_max_file_size) && !$ignoremaxsize)
		{
		$filesize = filesize_unlimited($file)/(1024*1024);# Get filesize in MB
		if ($filesize>$preview_generate_max_file_size) {return false;}
		}
	
	# Locate imagemagick.
    $convert_fullpath = get_utility_path("im-convert");
    if ($convert_fullpath==false) {debug("ERROR: Could not find ImageMagick 'convert' utility at location '$imagemagick_path'",RESOURCE_LOG_APPEND_PREVIOUS); return false;}

	# Handle alternative image file generation.
	global $image_alternatives;
	if(isset($image_alternatives) && $alternative == -1)
        {
        for($n = 0; $n < count($image_alternatives); $n++)
            {
            $exts = explode(',', $image_alternatives[$n]['source_extensions']);
            if(in_array($extension, $exts))
                {
                # Remove any existing alternative file(s) with this name.
                $existing = sql_query("SELECT ref FROM resource_alt_files WHERE resource = '$ref' AND name = '" . escape_check($image_alternatives[$n]['name']) . "'");
                for($m = 0; $m < count($existing); $m++)
                    {
                    delete_alternative_file($ref, $existing[$m]['ref']);
                    }

                # Create the alternative file.
                $aref  = add_alternative_file($ref, $image_alternatives[$n]['name']);
                $apath = get_resource_path($ref, true, '', true, $image_alternatives[$n]['target_extension'], -1, 1, false, '', $aref);

                $source_profile = '';
                if($image_alternatives[$n]['icc'] === true)
                    {
                    $iccpath = get_resource_path($ref, true, '', false, $extension) . '.icc';
                    
                    global $icc_extraction, $ffmpeg_supported_extensions;
                    
                    if(!file_exists($iccpath) && $extension != 'pdf' && !in_array($extension, $ffmpeg_supported_extensions))
                        {
                        // extracted profile doesn't exist. Try extracting.
                        extract_icc_profile($ref, $extension);
                        }

                    if(file_exists($iccpath))
                        {
                        $source_profile = ' -strip -profile ' . $iccpath;
                        }
                    }

                $source_params = ' ';
                if(isset($image_alternatives[$n]['source_params']) && '' !== trim($image_alternatives[$n]['source_params']))
                    {
                    $source_params = ' ' . $image_alternatives[$n]['source_params'] . ' ';
                    }

                # Process the image
                $version = get_imagemagick_version();
                if($version[0] > 5 || ($version[0] == 5 && $version[1] > 5) || ($version[0] == 5 && $version[1] == 5 && $version[2] > 7 ))
                    {
                    // Use the new imagemagick command syntax (file then parameters)
                    $command = $convert_fullpath . $source_params . escapeshellarg($file) . (($extension == 'psd') ? '[0] +matte' : '') . $source_profile . ' ' . $image_alternatives[$n]['params'] . ' ' . escapeshellarg($apath);
                    }
                else
                    {
                    // Use the old imagemagick command syntax (parameters then file)
                    $command = $convert_fullpath . $source_profile . ' ' . $image_alternatives[$n]['params'] . ' ' . escapeshellarg($file) . ' ' . escapeshellarg($apath);
                    }

                $output = run_command($command);
                resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $output);

                if(file_exists($apath))
                    {
                    # Update the database with the new file details.
                    $file_size = filesize_unlimited($apath);
                    sql_query("UPDATE resource_alt_files SET file_name = '" . escape_check($image_alternatives[$n]['filename'] . '.' . $image_alternatives[$n]['target_extension']) . "', file_extension = '" . escape_check($image_alternatives[$n]['target_extension']) . "', file_size = '" . $file_size . "',creation_date=now() WHERE ref = '$aref'");
                    }
                }
            }
        }	

	
		
	if (($extension=="jpg") || ($extension=="jpeg") || ($extension=="png") || ($extension=="gif"))
	# Create image previews for built-in supported file types only (JPEG, PNG, GIF)
		{
		if (isset($imagemagick_path))
			{
			create_previews_using_im($ref,$thumbonly,$extension,$previewonly,$previewbased,$alternative,$ingested);
			}
		else
			{
			# ----------------------------------------
			# Use the GD library to perform the resize
			# ----------------------------------------


			# For resource $ref, (re)create the various preview sizes listed in the table preview_sizes
			# Only create previews where the target size IS LESS THAN OR EQUAL TO the source size.
			# Set thumbonly=true to (re)generate thumbnails only.

			$sizes="";
			if ($thumbonly) {$sizes=" where id='thm' or id='col'";}
			if ($previewonly) {$sizes=" where id='thm' or id='col' or id='pre' or id='scr'";}

			# fetch source image size, if we fail, exit this function (file not an image, or file not a valid jpg/png/gif).
			if ((list($sw,$sh) = @getimagesize($file))===false) {return false;}
		
			$ps=sql_query("select * from preview_size $sizes");
			for ($n=0;$n<count($ps);$n++)
				{
				# fetch target width and height
				$tw=$ps[$n]["width"];$th=$ps[$n]["height"];
				$id=$ps[$n]["id"];
			
				# Find the target path 
				$path=get_resource_path($ref,true,$ps[$n]["id"],false,"jpg",-1,1,false,"",$alternative);
				if (file_exists($path) && !$previewbased) {unlink($path);}
				# Also try the watermarked version.
				$wpath=get_resource_path($ref,true,$ps[$n]["id"],false,"jpg",-1,1,true,"",$alternative);
				if (file_exists($wpath)) {unlink($wpath);}

	      # only create previews where the target size IS LESS THAN OR EQUAL TO the source size.
				# or when producing a small thumbnail (to make sure we have that as a minimum)
				if ($previews_allow_enlarge || $sw>$tw || $sh>$th || $id=="thm" || $id=="col")
					{
					# Calculate width and height.
					if ($sw>$sh) {$ratio = ($tw / $sw);} # Landscape
					else {$ratio = ($th / $sh);} # Portrait
					$tw=floor($sw*$ratio);
					$th=floor($sh*$ratio);

					# ----------------------------------------
					# Use the GD library to perform the resize
					# ----------------------------------------
				
					$target = imagecreatetruecolor($tw,$th);
				
					if ($extension=="png")
						{
						$source = @imagecreatefrompng($file);
						if ($source===false) {return false;}
						}
					elseif ($extension=="gif")
						{
						$source = @imagecreatefromgif($file);
						if ($source===false) {return false;}
						}
					else
						{
						$source = @imagecreatefromjpeg($file);
						if ($source===false) {return false;}
						}
					
					imagecopyresampled($target,$source,0,0,0,0,$tw,$th,$sw,$sh);
					imagejpeg($target,$path,90);

					if ($ps[$n]["id"]=="thm") {extract_mean_colour($target,$ref);}
					imagedestroy($target);
					}
				elseif (($id=="pre") || ($id=="thm") || ($id=="col"))	
					{
					# If the source is smaller than the pre/thm/col, we still need these sizes; just copy the file
					copy($file,get_resource_path($ref,true,$id,false,$extension,-1,1,false,"",$alternative));
					if ($id=="thm") {
						sql_query("update resource set thumb_width='$sw',thumb_height='$sh' where ref='$ref'");
						}
					}
				}
			# flag database so a thumbnail appears on the site
			if ($alternative==-1) # not for alternatives
				{
				sql_query("update resource set has_image=1,preview_extension='jpg',preview_attempts=0,file_modified=now() where ref='$ref'");
				}
			}
		}
	else
		{
		# If using ImageMagick, call preview_preprocessing.php which makes use of ImageMagick and other tools
		# to attempt to extract a preview.
		global $no_preview_extensions;
		if (isset($imagemagick_path) && !in_array(strtolower($extension),$no_preview_extensions))
			{
      		include(dirname(__FILE__)."/preview_preprocessing.php");
			}
		}
	return true;
	}

function create_previews_using_im($ref,$thumbonly=false,$extension="jpg",$previewonly=false,$previewbased=false,$alternative=-1,$ingested=false)
	{
	global $keep_for_hpr,$imagemagick_path,$imagemagick_preserve_profiles,$imagemagick_quality,$imagemagick_colorspace,$default_icc_file,$autorotate_no_ingest,$always_make_previews,$lean_preview_generation,$previews_allow_enlarge,$alternative_file_previews;

	$icc_transform_complete=false;
	debug("create_previews_using_im(ref=$ref,thumbonly=$thumbonly,extension=$extension,previewonly=$previewonly,previewbased=$previewbased,alternative=$alternative,ingested=$ingested)",RESOURCE_LOG_APPEND_PREVIOUS);

	if (isset($imagemagick_path))
		{

		# ----------------------------------------
		# Use ImageMagick to perform the resize
		# ----------------------------------------

		# For resource $ref, (re)create the various preview sizes listed in the table preview_sizes
		# Set thumbonly=true to (re)generate thumbnails only.
		if($previewbased || ($autorotate_no_ingest && !$ingested))
			{
			$file=get_resource_path($ref,true,"lpr",false,"jpg",-1,1,false,""); 
			if (!file_exists($file))
				{
				$file=get_resource_path($ref,true,"scr",false,"jpg",-1,1,false,"");		
				if (!file_exists($file))
					{
					$file=get_resource_path($ref,true,"pre",false,"jpg",-1,1,false,"");		
					/* staged, but not needed in testing
					if(!file_exists($file) && $autorotate_no_ingest && !$ingested)
						{
						$file=get_resource_path($ref,true,"",false,$extension,-1,1,false,"",$alternative);
						}*/
					}
				}
			if ($autorotate_no_ingest && !$ingested && !$previewonly)
				{
					# extra check for !previewonly should there also be ingested resources in the system
					$file=get_resource_path($ref,true,"",false,$extension,-1,1,false,"",$alternative);
				}
			}
		else if (!$previewonly)
			{
			$file=get_resource_path($ref,true,"",false,$extension,-1,1,false,"",$alternative);	
			}
		else
			{
			# We're generating based on a new preview (scr) image.
			$file=get_resource_path($ref,true,"tmp",false,"jpg");	
			}

		$hpr_path=get_resource_path($ref,true,"hpr",false,"jpg",-1,1,false,"",$alternative);	
		if (file_exists($hpr_path) && !$previewbased) {unlink($hpr_path);}	
		$lpr_path=get_resource_path($ref,true,"lpr",false,"jpg",-1,1,false,"",$alternative);	
		if (file_exists($lpr_path) && !$previewbased) {unlink($lpr_path);}	
		$scr_path=get_resource_path($ref,true,"scr",false,"jpg",-1,1,false,"",$alternative);	
		if (file_exists($scr_path) && !$previewbased) {unlink($scr_path);}
		$scr_wm_path=get_resource_path($ref,true,"scr",false,"jpg",-1,1,true,"",$alternative);	
		if (file_exists($scr_wm_path) && !$previewbased) {unlink($scr_wm_path);}
		
		$prefix = '';
		# Camera RAW images need prefix
		if (preg_match('/^(dng|nef|x3f|cr2|crw|mrw|orf|raf|dcr)$/i', $extension, $rawext)) { $prefix = $rawext[0] .':'; }

		# Locate imagemagick.
        $identify_fullpath = get_utility_path("im-identify");
        if ($identify_fullpath==false) {debug("ERROR: Could not find ImageMagick 'identify' utility at location '$imagemagick_path'.",RESOURCE_LOG_APPEND_PREVIOUS); return false;}

		# Get image's dimensions.
		$identcommand = $identify_fullpath . ' -format %wx%h '. escapeshellarg($prefix . $file) .'[0]';
		$identoutput=run_command($identcommand);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$identcommand . ":\n" . $identoutput);

        if($lean_preview_generation){
			$all_sizes=false;
			if(!$thumbonly && !$previewonly){
				// seperate width and height
				$all_sizes=true;
				if(!empty($identoutput)){
					$wh=explode("x",$identoutput);
					$o_width=$wh[0];
					$o_height=$wh[1];
				}
			}
		}
		
		preg_match('/^([0-9]+)x([0-9]+)$/ims',$identoutput,$smatches);
				if ((@list(,$sw,$sh) = $smatches)===false) { return false; }


		$sizes="";
		if ($thumbonly) {$sizes=" where id='thm' or id='col'";}
		if ($previewonly) {$sizes=" where id='thm' or id='col' or id='pre' or id='scr'";}

		$ps=sql_query("select * from preview_size $sizes order by width desc, height desc");
		if($lean_preview_generation && $all_sizes){
			$force_make=array("pre","thm","col");
			if($extension!="jpg" || $extension!="jpeg"){
				array_push($force_make,"hpr","scr");
			}
			$count=count($ps)-1;
			$oversized=0;
			for($s=$count;$s>0;$s--){
				if(!in_array($ps[$s]['id'],$force_make) && !in_array($ps[$s]['id'],$always_make_previews) && (isset($o_width) && isset($o_height) && $ps[$s]['width']>$o_width && $ps[$s]['height']>$o_height) && !$previews_allow_enlarge){
					$oversized++;
				}
				if($oversized>0){
					unset($ps[$s]);
				}
			}
			$ps = array_values($ps);
		}
		$created_count=0;
		for ($n=0;$n<count($ps);$n++)
			{ 
			# If this is just a jpg resource, we avoid the hpr size because the resource itself is an original sized jpg. 
			# If preview_preprocessing indicates the intermediate jpg should be kept as the hpr image, do that. 
			if ($keep_for_hpr && $ps[$n]['id']=="hpr"){
				rename($file,$hpr_path); // $keep_for_hpr is switched to false below
			}
			
			# If we've already made the LPR or SCR then use those for the remaining previews.
			# As we start with the large and move to the small, this will speed things up.
			if ($extension!="png" && $extension!="gif"){
			if(file_exists($hpr_path)){$file=$hpr_path;}
			if(file_exists($lpr_path)){$file=$lpr_path;}
			if(file_exists($scr_path)){$file=$scr_path;}
			}

			# Locate imagemagick.
            $convert_fullpath = get_utility_path("im-convert");
            if ($convert_fullpath==false) {debug("ERROR: Could not find ImageMagick 'convert' utility at location '$imagemagick_path'.",RESOURCE_LOG_APPEND_PREVIOUS); return false;}

			if( $prefix == "cr2:" || $prefix == "nef:" || $extension=="png" || $extension=="gif") {
			    $flatten = "";
			} else {
			    $flatten = "-flatten";
			}

            // Extensions for which the alpha/ matte channel should not be set to Off (i.e. +matte option)
            $extensions_no_alpha_off = array('png', 'gif', 'tif');
			
			$preview_quality=get_preview_quality($ps[$n]['id']);
			
            $command = $convert_fullpath . ' '. escapeshellarg($file) . (!in_array($extension, $extensions_no_alpha_off) ? '[0] +matte ' : ' ') . $flatten . ' -quality ' . $preview_quality;

			# fetch target width and height
			$tw=$ps[$n]["width"];$th=$ps[$n]["height"];
			$id=$ps[$n]["id"];

			# Debug
			debug("Contemplating " . $ps[$n]["id"] . " (sw=$sw, tw=$tw, sh=$sh, th=$th, extension=$extension)",RESOURCE_LOG_APPEND_PREVIOUS);

			# Find the target path
			if ($extension=="png" || $extension=="gif"){$target_ext=$extension;} else {$target_ext="jpg";}
			$path=get_resource_path($ref,true,$ps[$n]["id"],false,$target_ext,-1,1,false,"",$alternative);
			
			# Delete any file at the target path. Unless using the previewbased option, in which case we need it.			
            if(!hook("imagepskipdel") && !$keep_for_hpr)
				{
				if (!$previewbased)
					{
					if (file_exists($path))
						{unlink($path);}
					}
				}
			if ($keep_for_hpr){$keep_for_hpr=false;}
                    
			# Also try the watermarked version.
			$wpath=get_resource_path($ref,true,$ps[$n]["id"],false,$target_ext,-1,1,true,"",$alternative);
				if (file_exists($wpath))
					{unlink($wpath);}
			
			# Always make a screen size for non-JPEG extensions regardless of actual image size
			# This is because the original file itself is not suitable for full screen preview, as it is with JPEG files.
			#
			# Always make preview sizes for smaller file sizes.
			#
			# Always make pre/thm/col sizes regardless of source image size.
			if (($id == "hpr" && !($extension=="jpg" || $extension=="jpeg")) || $previews_allow_enlarge || ($id == "scr" && !($extension=="jpg" || $extension=="jpeg")) || ($sw>$tw) || ($sh>$th) || ($id == "pre") || ($id=="thm") || ($id=="col") || in_array($id,$always_make_previews))
				{			
				# Debug
				debug("Generating preview size " . $ps[$n]["id"] . " to " . $path,RESOURCE_LOG_APPEND_PREVIOUS);
	
				# EXPERIMENTAL CODE TO USE EXISTING ICC PROFILE IF PRESENT
				global $icc_extraction, $icc_preview_profile, $icc_preview_options,$ffmpeg_supported_extensions;
				if ($icc_extraction){
					$iccpath = get_resource_path($ref,true,'',false,$extension).'.icc';
					if (!file_exists($iccpath) && !isset($iccfound) && $extension!="pdf" && !in_array($extension,$ffmpeg_supported_extensions)) {
						// extracted profile doesn't exist. Try extracting.
						if (extract_icc_profile($ref,$extension)){
							$iccfound = true;
						} else {
							$iccfound = false;
						}
					}
				}

				if($icc_extraction && file_exists($iccpath) && !$icc_transform_complete){
					global $icc_preview_profile_embed;
					// we have an extracted ICC profile, so use it as source
					$targetprofile = dirname(__FILE__) . '/../iccprofiles/' . $icc_preview_profile;
					$profile  = " -strip -profile $iccpath $icc_preview_options -profile $targetprofile".($icc_preview_profile_embed?" ":" -strip ");
					// consider ICC transformation complete, if one of the sizes has been rendered that will be used for the smaller sizes
                    if ($id == 'hpr' || $id == 'lpr' || $id == 'scr') $icc_transform_complete=true;
				} else {
					// use existing strategy for color profiles
					# Preserve colour profiles? (omit for smaller sizes)
					if ($imagemagick_preserve_profiles && $id!="thm" && $id!="col" && $id!="pre" && $id!="scr")
						$profile="";
					else if (!empty($default_icc_file))
						$profile="-profile $default_icc_file ";
					else
						{
						# By default, strip the colour profiles ('+' is remove the profile, confusingly)
						$profile="-strip -colorspace ".$imagemagick_colorspace;
						}
				}

				$runcommand = $command ." ".(($extension!="png" && $extension!="gif")?" +matte $profile ":"")." -resize " . $tw . "x" . $th . (($previews_allow_enlarge && $id!="hpr")?" ":"\">\" ") .escapeshellarg($path);
                                if(!hook("imagepskipthumb")):
				$output=run_command($runcommand);
                resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$runcommand . ":\n" . $output);

                $created_count++;
				# if this is the first file generated for non-ingested resources check rotation
				if($autorotate_no_ingest && $created_count==1 && !$ingested){
					# first preview created for non-ingested file...auto-rotate
					if($id=="thm" || $id=="col" || $id=="pre" || $id=="scr"){AutoRotateImage($path,$ref);}
					else{AutoRotateImage($path);}
				}
                                endif;
				
				// checkerboard
				if ($extension=="png" || $extension=="gif"){
					global $transparency_background;
				$transparencyreal=dirname(__FILE__) ."/../" . $transparency_background;

                    $cmd=str_replace("identify","composite",$identify_fullpath)."  -compose Dst_Over -tile ".escapeshellarg($transparencyreal)." ".escapeshellarg($path)." ".escapeshellarg(str_replace($extension,"jpg",$path));
                    $wait=run_command($cmd, true);
                    resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $wait);

                unlink($path);
					$path=str_replace($extension,"jpg",$path);
				}               

				# Add a watermarked image too?
				global $watermark, $watermark_single_image;
				
				if (!hook("replacewatermarkcreation","",array($ref,$ps,$n,$alternative))){
				if (($alternative==-1 || ($alternative!==-1 && $alternative_file_previews)) && isset($watermark) && ($ps[$n]["internal"]==1 || $ps[$n]["allow_preview"]==1))
					{
					$wmpath=get_resource_path($ref,true,$ps[$n]["id"],false,"jpg",-1,1,true,'',$alternative);
					if (file_exists($wmpath)) {unlink($wmpath);}
					
					$watermarkreal=dirname(__FILE__) ."/../" . $watermark;
					
					$runcommand = $command ." +matte $profile -resize " . $tw . "x" . $th . "\">\" -tile ".escapeshellarg($watermarkreal)." -draw \"rectangle 0,0 $tw,$th\" ".escapeshellarg($wmpath); 
					
					// alternate command for png/gif using the path from above, and omitting resizing
					if ($extension=="png" || $extension=="gif"){
						$runcommand = $convert_fullpath . ' '. escapeshellarg($path) .(($extension!="png" && $extension!="gif")?'[0] +matte ':'') . $flatten . ' -quality ' . $preview_quality ." -tile ".escapeshellarg($watermarkreal)." -draw \"rectangle 0,0 $tw,$th\" ".escapeshellarg($wmpath); 
					}

                    // Generate the command for a single watermark instead of a tiled one
                    if(isset($watermark_single_image))
                        {
                        $wm_scale = $watermark_single_image['scale'];

                        $wm_scaled_width  = $tw * ($wm_scale / 100);
                        $wm_scaled_height = $th * ($wm_scale / 100);

                        // Command example: convert input.jpg watermark.png -gravity Center -geometry 40x40+0+0 -resize 1100x800 -composite wm_version.jpg
                        $runcommand = sprintf('%s %s %s -gravity %s -geometry %sx%s+0+0 -resize %sx%s -composite %s',
                            $convert_fullpath,
                            escapeshellarg($file),
                            escapeshellarg($watermarkreal),
                            escapeshellarg($watermark_single_image['position']),
                            escapeshellarg($wm_scaled_width),
                            escapeshellarg($wm_scaled_height),
                            escapeshellarg($tw),
                            escapeshellarg($th),
                            escapeshellarg($wmpath)
                        );
                        }

					$output = run_command($runcommand);
                    resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$runcommand . ":\n" . $output);

                    }
				}// end hook replacewatermarkcreation
				} 
			}
		# For the thumbnail image, call extract_mean_colour() to save the colour/size information
		$target=@imagecreatefromjpeg(get_resource_path($ref,true,"thm",false,"jpg",-1,1,false,"",$alternative));
		if ($target && $alternative==-1) # Do not run for alternative uploads 
			{
			extract_mean_colour($target,$ref);
			# flag database so a thumbnail appears on the site
			sql_query("update resource set has_image=1,preview_extension='jpg',preview_attempts=0,file_modified=now() where ref='$ref'");
			}
		else
			{
			if(!$target)
				{
				sql_query("update resource set preview_attempts=ifnull(preview_attempts,0) + 1 where ref='$ref'");
				}
			}
		return true;
		}
	else
		{
		return false;
		}
	}

function extract_mean_colour($image,$ref)
	{
	# for image $image, calculate the mean colour and update this to the image_red, image_green, image_blue tables
	# in the resources table.
	# Also - we insert the height and width of the thumbnail at this stage as all information is available and we
	# are already performing an update on the resource record.
	
	$width=imagesx($image);$height=imagesy($image);
	$totalred=0;
	$totalgreen=0;
	$totalblue=0;
	$total=0;
	
	for ($y=0;$y<20;$y++)
		{
		for ($x=0;$x<20;$x++)
			{
			$rgb = imagecolorat($image, $x*($width/20), $y*($height/20));
			$red = ($rgb >> 16) & 0xFF;
			$green = ($rgb >> 8) & 0xFF;
			$blue = $rgb & 0xFF;

			# calculate deltas (remove brightness factor)
			$cmax=max($red,$green,$blue);
			$cmin=min($red,$green,$blue);if ($cmax==$cmin) {$cmax=10;$cmin=0;} # avoid division errors
			if (abs($cmax-$cmin)>=20) # ignore gray/white/black
				{
				$red=floor((($red-$cmin)/($cmax-$cmin)) * 1000);
				$green=floor((($green-$cmin)/($cmax-$cmin)) * 1000);
				$blue=floor((($blue-$cmin)/($cmax-$cmin)) * 1000);

				$total++;
				$totalred+=$red;
				$totalgreen+=$green;
				$totalblue+=$blue;
				}
			}
		}
	if ($total==0) {$total=1;}
	$totalred=floor($totalred/$total);
	$totalgreen=floor($totalgreen/$total);
	$totalblue=floor($totalblue/$total);
	
	$colkey=get_colour_key($image);

	update_portrait_landscape_field($ref,$image);

	sql_query("update resource set image_red='$totalred', image_green='$totalgreen', image_blue='$totalblue',colour_key='$colkey',thumb_width='$width', thumb_height='$height' where ref='$ref'");
	}

function update_portrait_landscape_field($ref,$image=null){
	# updates portrait_landscape_field

	global $portrait_landscape_field,$lang;
	if (isset($portrait_landscape_field)){
		if (!$image){ 
			$thumbfile=get_resource_path($ref,true,"thm",false,"jpg");
			if (!file_exists($thumbfile)){ 
				return; 
			}
			$image=@imagecreatefromjpeg($thumbfile);
			}
		
		$width=imagesx($image);$height=imagesy($image);
	
		# Write 'Portrait' or 'Landscape' to the appropriate field.
		if ($width>$height) {
			$portland=$lang["landscape"];
			} 
		else if ($height>$width){
			$portland=$lang["portrait"];
			}
		else if ($height==$width){
			$portland=$lang["square"];
		}	
		update_field($ref,$portrait_landscape_field,$portland);
		}
	}

function get_colour_key($image)
	{
	# Extracts a colour key for the image, like a soundex.
	$width=imagesx($image);$height=imagesy($image);
	$colours=array(
	"K"=>array(0,0,0), 			# Black
	"W"=>array(255,255,255),	# White
	"E"=>array(200,200,200),	# Grey
	"E"=>array(140,140,140),	# Grey
	"E"=>array(100,100,100),	# Grey
	"R"=>array(255,0,0),		# Red
	"R"=>array(128,0,0),		# Dark Red
	"R"=>array(180,0,40),		# Dark Red
	"G"=>array(0,255,0),		# Green
	"G"=>array(0,128,0),		# Dark Green
	"G"=>array(80,120,90),		# Faded Green
	"G"=>array(140,170,90),		# Pale Green
	"B"=>array(0,0,255),		# Blue
	"B"=>array(0,0,128),		# Dark Blue
	"B"=>array(90,90,120),		# Dark Blue
	"B"=>array(60,60,90),		# Dark Blue
	"B"=>array(90,140,180),		# Light Blue
	"C"=>array(0,255,255),		# Cyan
	"C"=>array(0,200,200),		# Cyan
	"M"=>array(255,0,255),		# Magenta
	"Y"=>array(255,255,0),		# Yellow
	"Y"=>array(180,160,40),		# Yellow
	"Y"=>array(210,190,60),		# Yellow
	"O"=>array(255,128,0),		# Orange
	"O"=>array(200,100,60),		# Orange
	"P"=>array(255,128,128),	# Pink
	"P"=>array(200,180,170),	# Pink
	"P"=>array(200,160,130),	# Pink
	"P"=>array(190,120,110),	# Pink
	"N"=>array(110,70,50),		# Brown
	"N"=>array(180,160,130),	# Pale Brown
	"N"=>array(170,140,110),	# Pale Brown
	);
	$table=array();
	$depth=50;
	for ($y=0;$y<$depth;$y++)
		{
		for ($x=0;$x<$depth;$x++)
			{
			$rgb = imagecolorat($image, $x*($width/$depth), $y*($height/$depth));
			$red = ($rgb >> 16) & 0xFF;
			$green = ($rgb >> 8) & 0xFF;
			$blue = $rgb & 0xFF;
			# Work out which colour this is
			$bestdist=99999;$bestkey="";
			reset ($colours);
			foreach ($colours as $key=>$value)
				{
				$distance=sqrt(pow(abs($red-$value[0]),2)+pow(abs($green-$value[1]),2)+pow(abs($blue-$value[2]),2));
				if ($distance<$bestdist) {$bestdist=$distance;$bestkey=$key;}
				}
			# Add this colour to the colour table.
			if (array_key_exists($bestkey,$table)) {$table[$bestkey]++;} else {$table[$bestkey]=1;}
			}
		}
	asort($table);reset($table);$colkey="";
	foreach ($table as $key=>$value) {$colkey.=$key;}
	$colkey=substr(strrev($colkey),0,5);
	return($colkey);
	}

function tweak_preview_images($ref,$rotateangle,$gamma,$extension="jpg")
	{
	# Tweak all preview images
	# On the edit screen, preview images can be either rotated or gamma adjusted. We keep the high(original) and low resolution print versions intact as these would be adjusted professionally when in use in the target application.

	# Use the screen resolution version for processing
	global $tweak_all_images;
	if ($tweak_all_images){
		$file=get_resource_path($ref,true,"hpr",false,$extension);$top="hpr";
		if (!file_exists($file)) {
			$file=get_resource_path($ref,true,"lpr",false,$extension);$top="lpr";
			if (!file_exists($file)) {
				$file=get_resource_path($ref,true,"scr",false,$extension);$top="scr";
				if (!file_exists($file)) {
					$file=get_resource_path($ref,true,"pre",false,$extension);$top="pre";
				}
			}
		}
	}
	else {
		$file=get_resource_path($ref,true,"scr",false,$extension);$top="scr";
		if (!file_exists($file)) {
			# Some images may be too small to have a scr.  Try pre:
			$file=get_resource_path($ref,true,"pre",false,$extension);$top="pre";
		}
	}
	
	if (!file_exists($file)) {return false;}
    $source = imagecreatefromjpeg($file);
	# Apply tweaks
	if ($rotateangle!=0)
		{
		# Use built-in function if available, else use function in this file
		if (function_exists("imagerotate"))
			{
			$source=imagerotate($source,$rotateangle,0);
			}
		else
			{
			$source=AltImageRotate($source,$rotateangle);
			}
		}
		
	if ($gamma!=0) {imagegammacorrect($source,1.0,$gamma);}

	# Save source image and fetch new dimensions

    imagejpeg($source,$file,95);
		

    list($tw,$th) = @getimagesize($file);	
    
	# Save all images
	if ($tweak_all_images){
		$ps=sql_query("select * from preview_size where id<>'$top'");
	}
	else {
		$ps=sql_query("select * from preview_size where (internal=1 or allow_preview=1) and id<>'$top'");
	}
	for ($n=0;$n<count($ps);$n++)
		{
		# fetch target width and height
	    $file=get_resource_path($ref,true,$ps[$n]["id"],false,$extension);		
	    if (file_exists($file)){
			list($sw,$sh) = @getimagesize($file);
	    
			if ($rotateangle!=0) {$temp=$sw;$sw=$sh;$sh=$temp;}
		
			# Rescale image
			$target = imagecreatetruecolor($sw,$sh);
			imagecopyresampled($target,$source,0,0,0,0,$sw,$sh,$tw,$th);
			if ($extension=="png")
				{
				imagepng($target,$file);
				}
			elseif ($extension=="gif")
				{
				imagegif($target,$file);
				}
			else
				{
				imagejpeg($target,$file,95);
				}
			}
		}

	if ($rotateangle!=0)
		{
		# Swap thumb heights/widths
		$ts=sql_query("select thumb_width,thumb_height from resource where ref='$ref'");
		sql_query("update resource set thumb_width='" . $ts[0]["thumb_height"] . "',thumb_height='" . $ts[0]["thumb_width"] . "' where ref='$ref'");
		
		global $portrait_landscape_field,$lang;
		if (isset($portrait_landscape_field))
			{
			# Write 'Portrait' or 'Landscape' to the appropriate field.
			if ($ts[0]["thumb_height"]>=$ts[0]["thumb_width"]) {$portland=$lang["landscape"];} else {$portland=$lang["portrait"];}
			update_field($ref,$portrait_landscape_field,$portland);
			}
		
		}
	# Update the modified date to force the browser to reload the new thumbs.
	sql_query("update resource set file_modified=now() where ref='$ref'");
	
	# record what was done so that we can reconstruct later if needed
	# current format is rotation|gamma. Additional could be tacked on if more manipulation options are added
	$current_preview_tweak = sql_value("select preview_tweaks value from resource where ref = '$ref'","");
	if (strlen($current_preview_tweak) == 0)
		{
			$oldrotate = 0;
			$oldgamma = 1;
		} else {
			list($oldrotate,$oldgamma) = explode('|',$current_preview_tweak);
		}
		$newrotate = $oldrotate + $rotateangle;
		if ($newrotate > 360){
			$newrotate = $newrotate - 360;
		}elseif ($newrotate < 0){
			$newrotate = 360 + $newrotate;
		}elseif ($newrotate == 360){
			$newrotate = 0;
		}
		if ($gamma > 0){
			$newgamma = $oldgamma +  $gamma -1;
		} else {
			$newgamma = $oldgamma;
		}
        global $watermark;
        if ($watermark){
            tweak_wm_preview_images($ref,$rotateangle,$gamma);
        }
        
        sql_query("update resource set preview_tweaks = '$newrotate|$newgamma' where ref = $ref");
        
	}

function tweak_wm_preview_images($ref,$rotateangle,$gamma,$extension="jpg"){

    $ps=sql_query("select * from preview_size where (internal=1 or allow_preview=1)");
    for ($n=0;$n<count($ps);$n++)
        {
        $wm_file=get_resource_path($ref,true,$ps[$n]["id"],false,$extension,-1,1,true);
        if (!file_exists($wm_file)) {return false;}
        list($sw,$sh) = @getimagesize($wm_file);
        
        $wm_source = imagecreatefromjpeg($wm_file);
        
        # Apply tweaks
        if ($rotateangle!=0)
            {
            # Use built-in function if available, else use function in this file
            if (function_exists("imagerotate"))
                {
                $wm_source=imagerotate($wm_source,$rotateangle,0);
                }
            else
                {
                $wm_source=AltImageRotate($wm_source,$rotateangle);
                }
            }
            
        if ($gamma!=0) {imagegammacorrect($wm_source,1.0,$gamma);}
        imagejpeg($wm_source,$wm_file,95);
	            list($tw,$th) = @getimagesize($wm_file);
        if ($rotateangle!=0) {$temp=$sw;$sw=$sh;$sh=$temp;}
		
        # Rescale image
        $wm_target = imagecreatetruecolor($sw,$sh);
        imagecopyresampled($wm_target,$wm_source,0,0,0,0,$sw,$sh,$tw,$th);
        imagejpeg($wm_target,$wm_file,95);
    }
}


function AltImageRotate($src_img, $angle) {

	if ($angle==270) {$angle=-90;}

    $src_x = imagesx($src_img);
    $src_y = imagesy($src_img);
    if ($angle == 90 || $angle == -90) {
        $dest_x = $src_y;
        $dest_y = $src_x;
    } else {
        $dest_x = $src_x;
        $dest_y = $src_y;
    }

    $rotate=imagecreatetruecolor($dest_x,$dest_y);
    imagealphablending($rotate, false);

    switch ($angle) {
        case 90:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) {
                    $color = imagecolorat($src_img, $x, $y);
                    imagesetpixel($rotate, $dest_x - $y - 1, $x, $color);
                }
            }
            break;
        case -90:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) {
                    $color = imagecolorat($src_img, $x, $y);
                    imagesetpixel($rotate, $y, $dest_y - $x - 1, $color);
                }
            }
            break;
        case 180:
            for ($y = 0; $y < ($src_y); $y++) {
                for ($x = 0; $x < ($src_x); $x++) { 
                    $color = imagecolorat($src_img, $x, $y); 
                    imagesetpixel($rotate, $dest_x - $x - 1, $dest_y - $y - 1, $color);
                }
            }
            break;
        default: $rotate = $src_img;
    };
    return $rotate;
}

function base64_to_jpeg( $imageData, $outputfile ) {

 $jpeg = fopen( $outputfile, "wb" ) or die ("can't open");
 fwrite( $jpeg, base64_decode( $imageData ) );
 fclose( $jpeg );
 
}

/**
* Extracts JPG previews from INDD files when these have been set with a preview
* Note: it requires ExifTool >= 9.50
* 
* @param string $filename
* 
* @return array|bool
*/
function extract_indd_pages($filename)
    {
    $exiftool_fullpath = get_utility_path('exiftool');
    if ($exiftool_fullpath)
        {
        $cmd=$exiftool_fullpath.' -b -j -pageimage ' . escapeshellarg($filename);
        $array = run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $array);
        $array = json_decode($array);
        if(isset($array[0]->PageImage))
            {
            if(is_array($array[0]->PageImage))
                {
                return $array[0]->PageImage;
                }
            else
                {
                return array($array[0]->PageImage);
                }
            }
        }

    return false;
    }

function generate_file_checksum($resource,$extension,$anyway=false)
	{
	global $file_checksums;
    global $file_checksums_50k;
	global $file_checksums_offline;
	$generated = false;

	if (($file_checksums && !$file_checksums_offline)||$anyway) // do it if file checksums are turned on, or if requestor said do it anyway
		{
		# Generates a unique checksum for the given file, based either on the first 50K and the file size or the full file.

		$path=get_resource_path($resource,true,"",false,$extension);
		if (file_exists($path))
			{

                        # Generate the ID
                        if ($file_checksums_50k){
                            # Fetch the string used to generate the unique ID
                            $use=filesize_unlimited($path) . "_" . file_get_contents($path,null,null,0,50000);
                            $checksum=md5($use);
                        } else {
                            $checksum=md5_file($path);
                        }

                        # Generate store.
			sql_query("update resource set file_checksum='" . escape_check($checksum) . "' where ref='$resource'");
			$generated = true;
			}
		}

		if ($generated){
			return true;
		} else {
			# if we didn't generate a new file checksum, clear any existing one so that it will not be incorrect
			# The lack of checksum will also be used as the trigger for the offline process
			clear_file_checksum($resource);
			return false;
		}
	}

function clear_file_checksum($resource){
    if (strlen($resource) > 0 && is_numeric($resource)){
    	sql_query("update resource set file_checksum='' where ref='$resource'");
    	return true;
    } else {
	return false;
    }
}

if (!function_exists("upload_preview")){
function upload_preview($ref)
	{
		
	hook ("removeannotations","",array($ref));		
		
	# Upload a preview image only.
	$processfile=$_FILES['userfile'];
    $filename=strtolower(str_replace(" ","_",$processfile['name']));
    
    # Work out extension
    $extension=explode(".",$filename);$extension=trim(strtolower($extension[count($extension)-1]));
    if ($extension=="jpeg"){$extension="jpg";}

	# Move uploaded file into position.	
    $filepath=get_resource_path($ref,true,"tmp",true,$extension);
    $result=move_uploaded_file($processfile['tmp_name'], $filepath);
   	if ($result!=false) {chmod($filepath,0777);}
    
	# Create previews
	create_previews($ref,false,$extension,true);

	# Delete temporary file, if not transcoding.
	if(!sql_value("SELECT is_transcoding value FROM resource WHERE ref = '".escape_check($ref)."'", false))
		{
		unlink($filepath);
		}

    return true;
    }}
 
function extract_text($ref,$extension,$path="")
	{
	# path can be set to use an alternate file, for example, in the case of unoconv	
	# Extract text from the resource and save to the configured field.
	global $extracted_text_field,$antiword_path,$pdftotext_path,$zip_contents_field,$lang;

    resource_log($ref,LOG_CODE_TRANSFORMED,'','','',$lang['embedded_metadata_extract_option']);

	$text="";
	if ($path==""){$path=get_resource_path($ref,true,"",false,$extension);}
	
	# Microsoft Word extraction using AntiWord.
	if ($extension=="doc" && isset($antiword_path))
		{
		$command=$antiword_path . "/antiword";
		if (!file_exists($command)) {$command=$antiword_path . "\antiword.exe";}
		if (!file_exists($command)) {debug("ERROR: Antiword executable not found at '$antiword_path'",RESOURCE_LOG_APPEND_PREVIOUS); return false;}

        $cmd=$command . " -m UTF-8 \"" . $path . "\"";
        $text=run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);
        }
	
       # Microsoft OfficeOpen (docx,xlsx) extraction
       # This is not perfect and needs some work, but does at least extract indexable content.
       if ($extension=="docx"||$extension=="xlsx")
		{	
		$path=escapeshellarg($path);
		
		 # DOCX files are zip files and the content is in word/document.xml.
               # We extract this then remove tags.
               switch($extension){
               case "xlsx":
                   $cmd="unzip -p $path \"xl/sharedStrings.xml\"";
                   $text=run_command($cmd);
                   resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);
                   break;

               case "docx":
                   $cmd="unzip -p $path \"word/document.xml\"";
                   $text=run_command($cmd);
                   resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);
                   break;
               }
               
		# Remove tags, but add newlines as appropriate (without this, separate text blocks are joined together with no spaces).
		$text=str_replace("<","\n<",$text);
		$text=trim(strip_tags($text));
		while (strpos($text,"\n\n")!==false) {$text=str_replace("\n\n","\n",$text);} # condense multiple line breaks
		}

	# OpenOffice Text (ODT)
	if ($extension=="odt"||$extension=="ods"||$extension=="odp")
		{	
		$path=escapeshellarg($path);
		
		# ODT files are zip files and the content is in content.xml.
		# We extract this then remove tags.
		$cmd="unzip -p $path \"content.xml\"";
        $text=run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);

        # Remove tags, but add newlines as appropriate (without this, separate text blocks are joined together with no spaces).
		$text=str_replace("<","\n<",$text);
		$text=trim(strip_tags($text));
		while (strpos($text,"\n\n")!==false) {$text=str_replace("\n\n","\n",$text);} # condense multiple line breaks
		}
	
	# PDF extraction using pdftotext (part of the XPDF project)
	if (($extension=="pdf" || $extension=="ai") && isset($pdftotext_path))
		{
		$command=$pdftotext_path . "/pdftotext";
		if (!file_exists($command)) {$command=$pdftotext_path . "\pdftotext.exe";}
		if (!file_exists($command)) {debug("ERROR: pdftotext executable not found at '$pdftotext_path'",RESOURCE_LOG_APPEND_PREVIOUS); return false;}

        $cmd=$command . " -enc UTF-8 \"" . $path . "\" -";
        $text=run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);

        }
	
	# HTML extraction
	if ($extension=="html" || $extension=="htm")
		{
		$text=strip_tags(file_get_contents($path));
		}

	# TXT extraction
	if ($extension=="txt")
		{
		$text=file_get_contents($path);
		}

	if ($extension=="zip")
		{
		# Zip files - map the field
		$path=escapeshellarg($path);

        $cmd="unzip -l $path";
        $text=run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $text);

        global $zip_contents_field_crop;
		if ($zip_contents_field_crop>0)
			{
			# Remove the first few lines according to $zip_contents_field_crop in config.
			$text=explode("\n",$text);
			for ($n=0;$n<count($zip_contents_field_crop);$n++) {array_shift($text);}
			$text=join("\n",$text);
			}
		
		if (isset($zip_contents_field))
			{
			$extracted_text_field=$zip_contents_field;
			}
		}
	
	hook("textextraction", "all", array($extension,$path));
		
	# Save the extracted text.
	if ($text!="")
		{
		$modified_text=hook("modifiedextractedtext",'',array($text));
		if(!empty($modified_text)){$text=$modified_text;}
		
		# Save text
		update_field($ref,$extracted_text_field,$text);
		
		# Update XML metadata dump file.
		update_xml_metadump($ref);
		}
	
	}
	
function get_image_orientation($file)
    {
    $exiftool_fullpath = get_utility_path('exiftool');
    if ($exiftool_fullpath == false)
        {
        return 0;
        }

    $cmd=$exiftool_fullpath . ' -s -s -s -orientation ' . escapeshellarg($file);
    $orientation = run_command($cmd);
    resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $orientation);
    $orientation = str_replace('Rotate', '', $orientation);
    
    if (strpos($orientation, 'CCW'))
        {
        $orientation = trim(str_replace('CCW', '', 360-$orientation));
        } 
    else 
        {
        $orientation = trim(str_replace('CW', '', $orientation));
        }
    return $orientation;
    }

function AutoRotateImage($src_image, $ref = false) 
    {
    # use $ref to pass a resource ID in case orientation data needs to be taken
    # from a non-ingested image to properly rotate a preview image
    global $imagemagick_path, $camera_autorotation_ext, $camera_autorotation_gm;
    
    if (!isset($imagemagick_path)) 
        {
        return false;
        # for the moment, this only works for imagemagick
        # note that it would be theoretically possible to implement this
        # with a combination of exiftool and GD image rotation functions.
        }

    # Locate imagemagick.
    $convert_fullpath = get_utility_path("im-convert");
    if ($convert_fullpath == false) 
        {
        return false;
        }
    
    $exploded_src = explode('.', $src_image);
    $ext = $exploded_src[count($exploded_src) - 1];
    $triml = strlen($src_image) - (strlen($ext) + 1);
    $noext = substr($src_image, 0, $triml);
    
    if (count($camera_autorotation_ext) > 0 && (!in_array(strtolower($ext), $camera_autorotation_ext))) 
        {
        # if the autorotation extensions are set, make sure it is allowed for this extension
        return false;
        }

    $exiftool_fullpath = get_utility_path("exiftool");
    $new_image = $noext . '-autorotated.' . $ext;
    
    if ($camera_autorotation_gm) 
        {
        $orientation = get_image_orientation($src_image);
        if ($orientation != 0) 
            {
            $command = $convert_fullpath . ' ' . escapeshellarg($src_image) . ' -rotate +' . $orientation . ' ' . escapeshellarg($new_image);
            $output=run_command($command);
            resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $output);
            }
        $command = $exiftool_fullpath . ' Orientation=1 ' . escapeshellarg($new_image);
        } 
    else
        {
        if ($ref != false) 
            {
            # use the original file to get the orientation info
            $extension = sql_value("select file_extension value from resource where ref=$ref", '');
            $file = get_resource_path($ref, true, "", false, $extension, -1, 1, false, "", -1);
            # get the orientation
            $orientation = get_image_orientation($file);
            if ($orientation != 0) 
                {
                $command = $convert_fullpath . ' -rotate +' . $orientation . ' ' . escapeshellarg($src_image) . ' ' . escapeshellarg($new_image);
                $output=run_command($command);
                resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $output);

                # change the orientation metadata
                $command = $exiftool_fullpath . ' Orientation=1 ' . escapeshellarg($new_image);
                }
            } 
        else
            {
            $command = $convert_fullpath . ' ' . escapeshellarg($src_image) . ' -auto-orient ' . escapeshellarg($new_image);
            $output=run_command($command);
            resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$command . ":\n" . $output);
            }
        }

    if (!file_exists($new_image)) 
        {
        return false;
        }

    if (!$ref) 
        {
        # preserve custom metadata fields with exiftool        
        # save the new orientation
        # $new_orientation=run_command($exiftool_fullpath.' -s -s -s -orientation -n '.$new_image);
        $cmd=$exiftool_fullpath . ' -s -s -s -orientation -n ' . escapeshellarg($src_image);
        $old_orientation = run_command($cmd);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $old_orientation);
        $exiftool_copy_command = $exiftool_fullpath . " -TagsFromFile " . escapeshellarg($src_image) . " -all:all " . escapeshellarg($new_image);

        $output=run_command($exiftool_copy_command);
        resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$exiftool_copy_command . ":\n" . $output);

        # If orientation was empty there's no telling if rotation happened, so don't assume.
        # Also, don't go through this step if the old orientation was set to normal
        if ($old_orientation != '' && $old_orientation != 1) 
            {
            $fix_orientation = $exiftool_fullpath . ' Orientation=1 -n ' . escapeshellarg($new_image);
            $output=run_command($fix_orientation);
            resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$fix_orientation . ":\n" . $output);
            }
        }
    
    unlink($src_image);
    rename($new_image, $src_image);
    return true; 
    }

function extract_icc_profile ($ref,$extension){
	// this is provided for compatibility. However, we are now going to rely on the caller to tell us the
	// path of the file. extract_icc() is where the real work will happen.
	$infile=get_resource_path($ref,true,"",true,$extension);	
	if (extract_icc($infile)){
		return true;
	} else {
		return false;
	}
}
	

function extract_icc($infile) {
   global $config_windows;

   # Locate imagemagick, or fail this if it isn't installed
   $convert_fullpath = get_utility_path("im-convert");
   if ($convert_fullpath==false) {return false;}

   if ($config_windows){ $stderrclause = ''; } else { $stderrclause = '2>&1'; }

   //$outfile=get_resource_path($ref,true,"",false,$extension.".icc");
   //new, more flexible approach: we will just create a file for anything the caller hands to us.
   //this makes things work with alternatives, the deepzoom plugin, etc.
   $path_parts = pathinfo($infile);
   $outfile = $path_parts['dirname'] . '/' . $path_parts['filename'] .'.'. $path_parts['extension'] .'.icc';
   
   if (file_exists($outfile)){
      // extracted profile already existed. We'll remove it and start over
      unlink($outfile);
   }

    $cmd="$convert_fullpath $infile" . '[0]' . " $outfile $stderrclause";
    $cmdout = run_command($cmd);
    resource_log(RESOURCE_LOG_APPEND_PREVIOUS,LOG_CODE_TRANSFORMED,'','','',$cmd . ":\n" . $cmdout);

   if ( preg_match("/no color profile is available/",$cmdout) || !file_exists($outfile) ||filesize_unlimited($outfile) == 0){
   // the icc profile extraction failed. So delete file.
   if (file_exists($outfile)){ unlink ($outfile); };
   return false;
   }

   if (file_exists($outfile)) { return true; } else { return false; }

}

function get_imagemagick_version($array=true){
	// return version number of ImageMagick, or false if it is not installed or cannot be determined.
	// will return an array of major/minor/version/patch if $array is true, otherwise just the version string

    # Locate imagemagick, or return false if it isn't installed
    $convert_fullpath = get_utility_path("im-convert");
    if ($convert_fullpath==false) {return false;}

    $versionstring = run_command($convert_fullpath . " --version");
	// example: 
	//          Version: ImageMagick 6.5.0-0 2011-02-18 Q16 http://www.imagemagick.org
        //          Copyright: Copyright (C) 1999-2009 ImageMagick Studio LLC

	if (preg_match("/^Version: +ImageMagick (\d+)\.(\d+)\.(\d+)-(\d+) /",$versionstring,$matches)){
		$majorver = $matches[1];
		$minorver = $matches[2];
		$revision = $matches[3];
		$patch = $matches[4];
		if ($array){
			return array($majorver,$minorver,$revision,$patch);
		} else {
			return "$majorver.$minorver.$revision-$patch";
		}
	} else {
		return false;
	}
}


## Sizing calculations
function do_contactsheet_sizing_calculations(){
global $sheetstyle,$deltay,$add_contactsheet_logo,$pageheight,$pagewidth,$column,$config_sheetthumb_fields,$config_sheetthumb_include_ref,$leading,$refnumberfontsize,$imagesize,$columns,$rowsperpage,$cellsize,$logospace,$page,$rowsperpage,$contact_sheet_logo_resize,$contact_sheet_custom_footerhtml,$footerspace,$contactsheet_header,$config_sheetsingle_fields,$config_sheetsingle_include_ref,$orientation;


if ($sheetstyle=="thumbnails")
	{
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
	{$logospace=$pageheight/9;}

	$columns=$column;
	#calculating sizes of cells, images, and number of rows:
	$cellsize[0]=$cellsize[1]=($pagewidth-1.7)/$columns;
	$imagesize=$cellsize[0]-.3;
	# estimate rows per page based on config lines
	$extralines=(count($config_sheetthumb_fields)!=0)?count($config_sheetthumb_fields):0;
	if ($contact_sheet_custom_footerhtml!=''){$footerspace=$pageheight*.05;}
	if ($config_sheetthumb_include_ref){$extralines++;}
	$rowsperpage=($pageheight-.5-$logospace-$footerspace-($cellsize[1]+($extralines*(($refnumberfontsize+$leading)/72))))/($cellsize[1]+($extralines*(($refnumberfontsize+$leading)/72)));
	$page=1;	
	}
else if ($sheetstyle=="list")
	{ 
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
	{$logospace=$pageheight/9;}
	#calculating sizes of cells, images, and number of rows:
	$columns=1;
	$imagesize=1.0;
	$cellsize[0]=$pagewidth-1.7;
	$cellsize[1]=1.2;
	if ($contact_sheet_custom_footerhtml!=''){$footerspace=$pageheight*.05;}
	$rowsperpage=($pageheight-1.2-$logospace-$footerspace-$cellsize[1])/$cellsize[1];
	$page=1;
	}
else if ($sheetstyle=="single")
	{
	$extralines=(count($config_sheetsingle_fields)!=0)?count($config_sheetsingle_fields):0;
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
		{
		if ($orientation=="L"){$logospace=$pageheight/11;if ($contactsheet_header){$extralines=$extralines + 2;}} else {$logospace=$pageheight/9;}
		}
	$columns=$column;	
	if ($config_sheetsingle_include_ref){$extralines++;}
	
	# calculate size of single cell per page, allowing for extra lines. Needs to be smaller if landscape.
	if ($orientation=="L")
		{
		$cellsize[0]=$cellsize[1]=($pageheight*0.65)-($extralines*(($refnumberfontsize+$leading)/72));
		}
	else 
		{
		$cellsize[0]=$cellsize[1]=($pagewidth*0.8);
		}
	$imagesize=$cellsize[0]-0.3;
	$rowsperpage=1;
	$page=1;
	$columns=1;
	}
}
