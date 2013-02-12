<?php

function HookImagestreamUpload_pluploadInitialuploadprocessing()
	{
				
	#Support for uploading multi files as zip	
	global $config_windows, $id, $targetDir, $resource_type, $imagestream_restypes, $imagestream_transitiontime, $zipcommand, $use_zip_extension, $userref,  $session_hash, $filename, $filename_field, $collection_add, $archiver, $zipcommand, $ffmpeg_fullpath, $ffmpeg_preview_extension, $ffmpeg_preview_options, $ffmpeg_preview_min_height, $ffmpeg_preview_max_height, $ffmpeg_preview_min_width, $ffmpeg_preview_max_width,$lang,$collection_download_settings,$archiver_listfile_argument;
	$ffmpeg_fullpath = get_utility_path("ffmpeg");
			
	debug ("DEBUG: Imagestream - checking restype: " . $resource_type . $imagestream_restypes);
	
	if (in_array($resource_type,$imagestream_restypes))
		{
		
		debug ("DEBUG: Imagestream - uploading file");
		#Check that we have an archiver configured
		$archiver_fullpath = get_utility_path("archiver");
			if (!isset($zipcommand) && !$use_zip_extension)
				{				
				if ($archiver_fullpath==false) {exit($lang["archiver-utility-not-found"]);}
				}
		echo print_r($_POST) . print_r($_GET);		
		if (getval("lastqueued","")) # Now we have all the files, do the processing
			{
			debug ("DEBUG: Imagestream - last queued file");
			$ref=copy_resource(0-$userref); # Copy from user template
			debug ("DEBUG: Imagestream - creating resource: " . $ref);
			# Create the zip file			
			$imagestreamzippath=get_resource_path($ref,true,"",true,"zip");			
			
			if ($use_zip_extension){
				$zip = new ZipArchive();
				$zip->open($imagestreamzippath, ZIPARCHIVE::CREATE);
			}
			$deletion_array=array();
			debug("DEBUG: opening directory: " . $targetDir);
			$imagestream_files = opendir($targetDir);
			$imagestream_workingfiles= get_temp_dir() . DIRECTORY_SEPARATOR . "plupload" . DIRECTORY_SEPARATOR . $session_hash . "workingfiles";
			if (!file_exists($imagestream_workingfiles)){			
				if ($config_windows){@mkdir($imagestream_workingfiles);}
				else{@mkdir($imagestream_workingfiles,0777, true);}
				}
			$filenumber = 000;
			$imagestream_filelist = array();
			while ($imagestream_filelist[] = readdir($imagestream_files)) {
			sort($imagestream_filelist);}
			closedir($imagestream_files);
			$imageindex=1;
				foreach($imagestream_filelist as $imagestream_file){
					if($imagestream_file != '.' && $imagestream_file != '..'){
												
						$filenumber = sprintf("%03d", $filenumber);
						$deletion_array[]=$targetDir . DIRECTORY_SEPARATOR . $imagestream_file;					
						if (!$use_zip_extension){
							$imagestreamcmd_file = get_temp_dir(false,$id) . "/imagestreamzipcmd" . $imagestream_file . ".txt";
							$fh = fopen($imagestreamcmd_file, 'w') or die("can't open file");
							fwrite($fh, $targetDir . DIRECTORY_SEPARATOR . $imagestream_file . "\r\n");
							fclose($fh);
							$deletion_array[]=$imagestreamcmd_file;
						}
						if ($use_zip_extension){
							debug ("DEBUG: Imagestream - adding filename: " . $imagestream_file);		
							debug ("DEBUG: using zip PHP extension, set up zip at : " . $imagestreamzippath);
							$zip->addFile($imagestream_file);
							debug(" Added files number : " . $zip->numFiles);
							$wait=$zip->close();
							debug("DEBUG: closed zip");
						}
						else if ($archiver_fullpath)	{
							debug ("DEBUG: using archiver, running command: \r\n" . $archiver_fullpath . " " . $collection_download_settings[0]["arguments"] . " " . escapeshellarg($imagestreamzippath) . " " . $archiver_listfile_argument . escapeshellarg($imagestream_file));
							run_command($archiver_fullpath . " " . $collection_download_settings[0]["arguments"] . " " . escapeshellarg($imagestreamzippath) . " " . $archiver_listfile_argument . escapeshellarg($imagestreamcmd_file));						
						}
						else if (!$use_zip_extension){			
							if ($config_windows)
								# Add the command file, containing the filenames, as an argument.
								{
								debug ("DEBUG: using zip command: . $zipcommand " . escapeshellarg($imagestreamzippath) . " @" . escapeshellarg($imagestreamcmd_file));
								exec("$zipcommand " . escapeshellarg($imagestreamzippath) . " @" . escapeshellarg($imagestreamcmd_file));
								}
							else
								{
								# Pipe the command file, containing the filenames, to the executable.
								exec("$zipcommand " . escapeshellarg($imagestreamzippath) . " -@ < " . escapeshellarg($imagestreamcmd_file));
								}
						}
					
					#Create a JPEG if not already in that format
					$imagestream_file_parts = explode('.',$imagestream_file);
					$imagestream_file_ext = $imagestream_file_parts[count($imagestream_file_parts)-1];
					$imagestream_file_noext = basename($imagestream_file, $imagestream_file_ext) ;
						global $imagemagick_path,$imagemagick_quality;
						$icc_transform_complete=false;
						
						# Camera RAW images need prefix
						if (preg_match('/^(dng|nef|x3f|cr2|crw|mrw|orf|raf|dcr)$/i', $imagestream_file_ext, $rawext)) { $prefix = $rawext[0] .':'; }
						
						# Locate imagemagick.
						$convert_fullpath = get_utility_path("im-convert");
						if ($convert_fullpath==false) {exit("Could not find ImageMagick 'convert' utility at location '$imagemagick_path'.");}
						
						$prefix = '';
						if( $prefix == "cr2:" || $prefix == "nef:" ) {
							$flatten = "";
						} else {
							$flatten = "-flatten";
						}
						
						$command = $convert_fullpath . ' '. escapeshellarg($targetDir . DIRECTORY_SEPARATOR . $imagestream_file) .' +matte ' . $flatten . ' -quality ' . $imagemagick_quality;
						
						# EXPERIMENTAL CODE TO USE EXISTING ICC PROFILE IF PRESENT
						global $icc_extraction, $icc_preview_profile, $icc_preview_options,$ffmpeg_supported_extensions;
						if ($icc_extraction){
							$iccpath = $targetDir . DIRECTORY_SEPARATOR . $imagestream_file .'.icc';
							if (!file_exists($iccpath) && !isset($iccfound) && $extension!="pdf" && !in_array($imagestream_file_ext,$ffmpeg_supported_extensions)) {
								// extracted profile doesn't exist. Try extracting.
								if (extract_icc_profile($ref,$imagestream_file_ext)){
									$iccfound = true;
								} else {
									$iccfound = false;
								}
							}
						}

						if($icc_extraction && file_exists($iccpath) && !$icc_transform_complete){
							// we have an extracted ICC profile, so use it as source
							$targetprofile = dirname(__FILE__) . '/../iccprofiles/' . $icc_preview_profile;
							$profile  = " +profile \"*\" -profile $iccpath $icc_preview_options -profile $targetprofile +profile \"*\" ";
							$icc_transform_complete=true;
						} else {
							// use existing strategy for color profiles
							# Preserve colour profiles? (omit for smaller sizes)   
							$profile="+profile \"*\" -colorspace RGB"; # By default, strip the colour profiles ('+' is remove the profile, confusingly)
							#if ($imagemagick_preserve_profiles && $id!="thm" && $id!="col" && $id!="pre" && $id!="scr") {$profile="";}
						}

						$runcommand = $command ." +matte $profile ".escapeshellarg($imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $filenumber . ".jpg");
						$deletion_array[]= $imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $filenumber . ".jpg";
						$output=run_command($runcommand);
						
						debug ("processed file" . $filenumber . ": " . $imagestream_file . "\r\n");
						debug ("Image index: " . $imageindex . ". file count: " . count($imagestream_filelist)); 
						if ($filenumber==000) {
							$snapshotsize=getimagesize($imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $filenumber . ".jpg");
							list($width, $height) = $snapshotsize;
							# Frame size must be a multiple of two 
							if ($width % 2){$width++;}
							if ($height % 2) {$height++;}
						}






						if ($imageindex == (count($imagestream_filelist)-1)){
						$additionalfile = $filenumber+1;
						$additionalfile = sprintf("%03d", $additionalfile);
							copy($imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $filenumber . ".jpg", $imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $additionalfile . ".jpg");
							$deletion_array[]= $imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream" . $additionalfile . ".jpg";
						}
												
					$filenumber++;
					
				}	#end of loop for each uploadedfile
				
				$imageindex++;	
			}
			#Add the resource and move this zip file, set extension 
			# Add to collection?
			if ($collection_add!="")
				{
				add_resource_to_collection($ref,$collection_add);
				}
				
			# Log this			
			daily_stat("Resource upload",$ref);
			resource_log($ref,"u",0);
			
			#Change this!!!!!!!!!!!
			
			#$status=upload_file($ref,true,false,false));
			
			if (!$config_windows){@chmod($imagestreamzippath,0777);}
			# Store extension in the database and update file modified time.
			sql_query("update resource set file_extension='zip',preview_extension='zip',file_modified=now(), has_image=0 where ref='$ref'");
			
			#update_field($ref,$filename_field,$filename);
			update_disk_usage($ref);
					
			# create the mp4 version
			# Add a new alternative file			
			
			$aref=add_alternative_file($ref,"MP4 version");	
			$imagestreamqtfile=get_resource_path($ref,true,"",false,"mp4", -1, 1, false, "", $aref); 		
			$shell_exec_cmd = $ffmpeg_fullpath . " -loglevel panic -y -r " . $imagestream_transitiontime . " -i " . $imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream%3d.jpg -r " . $imagestream_transitiontime . " -s {$width}x{$height} " . $imagestreamqtfile;
			echo ("Running command: " . $shell_exec_cmd);
			if ($config_windows)
				{
				$shell_exec_cmd = $ffmpeg_fullpath . " -loglevel panic -y -r " . $imagestream_transitiontime . " -i " . $imagestream_workingfiles . DIRECTORY_SEPARATOR . "imagestream%%3d.jpg -r " . $imagestream_transitiontime . " -s {$width}x{$height} " . $imagestreamqtfile;
				file_put_contents(get_temp_dir() . DIRECTORY_SEPARATOR . "imagestreammp4" . $session_hash . ".bat",$shell_exec_cmd);
				$shell_exec_cmd=get_temp_dir() . DIRECTORY_SEPARATOR . "imagestreammp4" . $session_hash . ".bat";
				$deletion_array[]=$shell_exec_cmd;
				}
			run_command($shell_exec_cmd);
			debug("DEBUG created slideshow MP4 video");
			if (!$config_windows){@chmod($imagestreamqtfile,0777);}
			$file_size = @filesize_unlimited($imagestreamqtfile);
			# Save alternative file data.
			sql_query("update resource_alt_files set file_name='quicktime.mp4',file_extension='mp4',file_size='" . $file_size . "',creation_date=now() where resource='$ref' and ref='$aref'");
			
			
			#create the FLV preview as per normal video processing if possible?
			if($height<$ffmpeg_preview_min_height)
				{
				$height=$ffmpeg_preview_min_height;
				}

			if($width<$ffmpeg_preview_min_width)
				{
				$width=$ffmpeg_preview_min_width;
				}

			if($height>$ffmpeg_preview_max_height)
				{
				$width=ceil($width*($ffmpeg_preview_max_height/$height));
				$height=$ffmpeg_preview_max_height;
				}
				
			if($width>$ffmpeg_preview_max_width)
				{
				$height=ceil($height*($ffmpeg_preview_max_width/$width));
				$width=$ffmpeg_preview_max_width;
				}
			$flvzippreviewfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension); 		
			$shell_exec_cmd = $ffmpeg_fullpath . " -loglevel panic -y -i " . $imagestreamqtfile . " $ffmpeg_preview_options -s {$width}x{$height} " . $flvzippreviewfile;
			debug ("Running command: " . $shell_exec_cmd);
			if ($config_windows)
				{
				file_put_contents(get_temp_dir() . DIRECTORY_SEPARATOR . "imagestreamflv" . $session_hash . ".bat",$shell_exec_cmd);
				$shell_exec_cmd=get_temp_dir() . DIRECTORY_SEPARATOR . "imagestreamflv" . $session_hash . ".bat";
				$deletion_array[]=$shell_exec_cmd;
				}
			run_command($shell_exec_cmd);
			debug("DEBUG created slideshow FLV video");
			
			if (!$config_windows){@chmod($flvzippreviewfile,0777);}
			#Tidy up
			rcRmdir($imagestream_workingfiles);
			rcRmdir($targetDir);
			foreach($deletion_array as $tmpfile) {
				debug("\r\nDEBUG: Deleting: " . $tmpfile);
				delete_exif_tmpfile($tmpfile);
			 }
			
					
			echo "SUCCESS";
			
			#return true;
			exit();
			
			
			
		}
		else # Create/add to the zip file here? NO
			{
			echo "SUCCESS";
			exit();
		}
		

		
		return true;	
		}
	else
		{
		return false;
	}
}		
			