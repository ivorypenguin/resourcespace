<?php
include_once '../../../include/db.php';
include_once '../../../include/general.php';
include_once '../../../include/resource_functions.php';
include_once '../../../include/authenticate.php';
include_once '../../../include/image_processing.php';

$ref=getvalescaped("ref","", true);
if($ref<0 || $ref==""){$error=true;$message=$lang["video_tracks_invalid_resource"];}

$access=get_resource_access($ref);
if($access!=0){$error=true;$message=$lang["error-permissiondenied"];}

$uploadparams= array(
    'ref'          => $ref,
);

$generateurl=generateURL($baseurl . "/plugins/video_tracks/pages/create_video.php",$uploadparams);

$message="";
$video_tracks_output_formats=unserialize(base64_decode($video_tracks_output_formats_saved));
$resource=get_resource_data($ref);	
$edit_access=get_edit_access($ref,$resource["archive"]);	

$offline=($offline_job_queue && $resource["file_size"]>=($video_tracks_process_size_limit * 1024 * 1024));

$altfiles=get_alternative_files($ref);
$subtitle_alts=array();
$audio_alts=array();
foreach($altfiles as $altfile)	
	{
	if(in_array(mb_strtolower($altfile["file_extension"]),$video_tracks_subtitle_extensions)){$subtitle_alts[]=$altfile;}
	if(in_array(mb_strtolower($altfile["file_extension"]),$video_tracks_audio_extensions)){$audio_alts[]=$altfile;}
	}
		
if(getval("generate","")!="")
	{
	$video_track_format=getvalescaped("video_track_format","");
	$video_subtitle_file=getvalescaped("video_subtitle_file","");
	$video_audio_file=getvalescaped("video_audio_file","");
    $savealt=false;
	$download=false;
	if($video_track_format!="")
		{		
		// Build up the ffmpeg command
		$ffmpeg_fullpath = get_utility_path("ffmpeg");
        $ffprobe_fullpath = get_utility_path("ffprobe");
		$filesource=get_resource_path($ref,true,"",false,$resource["file_extension"]);
		$randstring=md5(rand() . microtime());
		// Get the chosen ffmpeg command as set in the plugin config
		$video_track_command=$video_tracks_output_formats[$video_track_format];
		
		$shell_exec_cmd = $ffmpeg_fullpath . " " . $ffmpeg_global_options . " -i " . $filesource; 
		
		$probeout = run_command($ffprobe_fullpath . " -i " . escapeshellarg($filesource), true);    
		if(preg_match("/Duration: (\d+):(\d+):(\d+)\.\d+, start/", $probeout, $match))
			{
			$duration = $match[1]*3600+$match[2]*60+$match[3];
			$shell_exec_cmd .= " -t " . $duration;
            }
		
		if($video_audio_file!="")
			{
            $audio_info=get_alternative_file($ref,$video_audio_file);
			$audio_path=get_resource_path($ref,true,"",false,$audio_info["file_extension"],-1,1,false,"",$video_audio_file);
           
			$shell_exec_cmd .= " -i " . $audio_path;
			$shell_exec_cmd .= " -map 0:v -map 1:a";
			}
		
		if($video_subtitle_file!="")
			{
			$subtitle_info=get_alternative_file($ref,$video_subtitle_file);
			$subtitle_path=get_resource_path($ref,true,"",false,$subtitle_info["file_extension"],-1,1,false,"",$video_subtitle_file); 
			$shell_exec_cmd .= " -vf subtitles=" . $subtitle_path;
			}

		$shell_exec_cmd .= " %%TARGETFILE%%";
		
		// Video requirements have been defined. What does the user want to do with the video?	
		if(getval("video_track_save_alt","")!="" && $edit_access)
			{
			// Save as alternative file.
            $savealt=true;
			$origfilename=get_data_by_field($ref,$filename_field);
			$altname=$video_track_format;
			$description=getvalescaped("video_track_alt_desc","");			
			
			if($offline)
				{ 
				// Add this to the job queue for offline processing
				$job_data=array();
				$job_data["resource"]=$ref;
				$job_data["command"]=$shell_exec_cmd;
				$job_data["alt_name"]=$altname;
				$job_data["alt_description"]=$description;
				$job_data["alt_extension"]=$video_track_command["extension"];
                $job_code=$ref . $altname . md5($job_data["command"]); // unique code for this job, used to prevent duplicate job creation
                $job_success_lang="alternative_file_created" . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
				$job_failure_lang="alternative_file_creation_failed" . ": " . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
				$jobadded=job_queue_add("create_alt_file",$job_data,$userref,'',$job_success_lang,$job_failure_lang,$job_code);				
				 if($jobadded!==true)
                    {
                    $message =  $jobadded;  
                    }
                else
                    {
                    $message=$lang["video_tracks_offline_notice"];
                    }
				}
			else
				{				
				$newaltfile=add_alternative_file($ref,$altname,$description,str_replace("." . $resource["file_extension"],"." . $video_track_command["extension"],$origfilename),$video_track_command["extension"]);
				$targetfile=get_resource_path($ref,true,"",false, $video_track_command["extension"],-1,1,false,"",$newaltfile); 				
				}
			}
		elseif(getval("video_track_save_export","")!="")
			{
			// Save into export directory
			$filename=get_download_filename($ref,"","",$video_track_command["extension"]);
			$filename=remove_extension($filename) . "_" . safe_file_name($video_track_format) . "." . $video_track_command["extension"];
			$targetfile=$video_tracks_export_folder . $filename;
			if(file_exists($targetfile))
				{
				$filename=remove_extension($filename) . "_" . date("Ymd_Hi") . "." . $video_track_command["extension"];
				$targetfile=$video_tracks_export_folder . $filename;
				}
            
			$message=$lang["video_tracks_export_file_created"];
			if($offline)
				{ 
				$job_data=array();
				$job_success_lang=$lang["video_tracks_export_file_created"] . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
				$job_failure_lang=$lang["video_tracks_export_file_failed"] . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
                $job_data["resource"]=$ref;
				$job_data["command"]=$shell_exec_cmd;	
				$job_data["outputfile"]=$targetfile;
                $job_code=$ref . md5($job_data["command"]); // unique code for this job, used to prevent duplicate job creation
                if($video_tracks_download_export)
                    {
                    $job_data["url"]=$baseurl . "/pages/download.php?userfile=" . $ref . "_" . $randstring . "." . $video_track_command["extension"] . "&video_tracks_export=" . base64_encode(json_encode(array($userref,$filename)));         
                    }
                $jobadded=job_queue_add("create_download_file",$job_data,$userref,'',$job_success_lang,$job_failure_lang,$job_code);
				if($jobadded!==true)
                    {
                    $message =  $jobadded;  
                    }
                else
                    {
                    $message=$lang["video_tracks_offline_notice"];
                    }
				}
			}
		else
			{
			// Download	
			// Generate a path based on userref
			//$targetdir=get_temp_dir(false,'user_downloads';
			$targetfile = get_temp_dir(false,'user_downloads') . "/" . $ref . "_" . md5($username . $randstring . $scramble_key) . "." . $video_track_command["extension"];
			//$targetfile=$targetdir . "/" . $ref . "_" . md5($username . $randstring . $scramble_key) . "." . $video_track_command["extension"] ;	
			if($offline)
				{ 
				$job_data=array();
                $job_success_lang=$lang["download_file_created"]  . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
				$job_failure_lang=$lang["download_file_creation_failed"] . str_replace(array('%ref','%title'),array($ref,$resource['field' . $view_title_field]),$lang["ref-title"]);
                $job_data["resource"]=$ref;
				$job_data["command"]=$shell_exec_cmd;	 
				$job_data["outputfile"]=$targetfile;	
				$job_data["url"]=$baseurl . "/pages/download.php?userfile=" . $ref . "_" . $randstring . "." . $video_track_command["extension"];
				$job_data["lifetime"]=$download_file_lifetime;
                $job_code=$ref . $userref . md5($job_data["command"]); // unique code for this job, used to prevent duplicate job creation
				$jobadded=job_queue_add("create_download_file",$job_data,$userref,'',$job_success_lang,$job_failure_lang,$job_code);
                if($jobadded!==true)
                    {
                    $message =  $jobadded;  
                    }
				else
                    {
                    $message=$lang["video_tracks_offline_notice"];
                    }
				}
			else
				{	
				$filename=get_download_filename($ref,"","",$video_track_command["extension"]);
                $download=true;
				}
			}
			
		if(!$offline)
			{
			$shell_exec_cmd = str_replace("%%TARGETFILE%%",$targetfile,$shell_exec_cmd);
			if ($config_windows)
				{
				file_put_contents(get_temp_dir() . "/ffmpeg_" . $randstring . ".bat",$shell_exec_cmd);
				$shell_exec_cmd=get_temp_dir() . "/ffmpeg_" . $randstring . ".bat";
				}
            $output=run_command($shell_exec_cmd);
            if(file_exists($targetfile))
                {
                if($savealt)
                    {
                    // Save as alternative
                    $newfilesize=filesize_unlimited($targetfile);
                    sql_query("update resource_alt_files set file_size='" . $newfilesize ."' where resource='" . $ref . "' and ref='" . $newaltfile . "'");
                     if ($alternative_file_previews)
                        {create_previews($ref,false,$video_track_command["extension"],false,false,$newaltfile);}
                    $message = $lang["alternative_file_created"];
                    }
				elseif($download)
					{
                    // Download file
					$filesize=filesize_unlimited($targetfile);
                    ob_flush();
					
					header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
					header("Content-Length: " . $filesize);
					set_time_limit(0);

					$sent = 0;
					$handle = fopen($targetfile, "r");

					// Now we need to loop through the file and echo out chunks of file data
					while($sent < $filesize)
						{
						echo fread($handle, $download_chunk_size);
						ob_flush();
						$sent += $download_chunk_size;
						}
					#Delete File:
					unlink($targetfile);
					}
                else
                    {
                    // Exported file
                    $message.="<br/>" . $targetfile;    
                    }
                }
             else
                {
                $message=$lang["error"];
                }
			}
		
		}
	else
		{
		//  No audio or subtitle track selected
		$message=$lang["video_tracks_invalid_option"];
		}
	}

?>
<script>
var video_tracks_offline=<?php echo ($offline)?"true":"false"; ?>;
</script>
<div class="BasicsBox">

<h1><?php echo $lang["video_tracks_create_video_link"];?> </h1>
<?php
if ($message!="")
    {
    echo "<div class=\"PageInformal\">" . $message . "</div>";
    }
?>
<form id="video_tracks_create_form" action="<?php echo $baseurl . "/plugins/video_tracks/pages/create_video.php" ;?>">

<input name="ref" type="hidden" value="<?php echo $ref; ?>">
<input type="hidden" name="generate" value="yes" />
<div class="Question" id="question_video_track_format">
	<label><?php echo $lang["video_tracks_select_output"] ?></label>
	<select class="stdwidth" name="video_track_format" id="video_track_format" >
	<?php
	foreach ($video_tracks_output_formats as $video_tracks_output_format=>$video_tracks_output_command)
		{
		echo "<option value='" . htmlspecialchars(trim($video_tracks_output_format)) . "' >" . htmlspecialchars(trim($video_tracks_output_format)) . "</option>";
		}
	?>
	</select>
	<div class="clearerleft"> </div>
</div>

<?php 
if(count($subtitle_alts)>0)
	{?>
	<!-- Select subtitle file -->
	<div class="Question" id="question_video_subtitles">
	   <label><?php echo $lang["video_tracks_select_subtitle"] ?></label>
		<select class="stdwidth" name="video_subtitle_file" id="video_subtitle_file" >
		<?php
		foreach ($subtitle_alts as $subtitle_alt)
			{
			if(in_array(mb_strtolower($subtitle_alt["file_extension"]),$video_tracks_subtitle_extensions))
				{
				echo "<option value='" . $subtitle_alt["ref"] . "' >" . htmlspecialchars(trim($subtitle_alt["description"])) . " (" . $subtitle_alt["name"] . ")</option>";
				}	  
			}
		?>
		</select>
		<div class="clearerleft"> </div>
	</div>
	<?php
	}
	
if(count($audio_alts)>0)
	{?>
	<!-- Select audio file -->
	<div class="Question" id="question_video_audio">
	   <label><?php echo $lang["video_tracks_select_audio"] ?></label>
		<select class="stdwidth" name="video_audio_file" id="video_subtitle_file" >
		<?php
		foreach ($audio_alts as $audio_alt)
			{
			if(in_array(mb_strtolower($audio_alt["file_extension"]),$video_tracks_audio_extensions))
				{
				echo "<option value='" . $audio_alt["ref"] . "' >" . htmlspecialchars(trim($audio_alt["description"])) . " (" . $audio_alt["name"] . ")</option>";
				}	  
			}
		?>
		</select>
		<div class="clearerleft"> </div>
	</div>
	<?php
	}

?>

<div class="Question" id="question_video_save_to">
	
	<label><?php echo $lang["video_tracks_save_to"] ?></label>
	<?php if($edit_access)
		{
		?>
		<input type="radio" class="Inline video_track_save_option" id="video_track_save_alt" name="video_track_save_alt" value="yes" onClick="jQuery('#video_track_download').removeAttr('checked');jQuery('#video_track_save_export').removeAttr('checked');jQuery('#question_alternative_description').slideDown();"/>
		<label class="customFieldLabel Inline" for="video_track_save_alt" ><?php echo $lang["video_tracks_save_alternative"]; ?></label>
		<?php
		}
		?>		
	<input type="radio" class="Inline video_track_save_option" id="video_track_save_export" name="video_track_save_export" value="yes" onClick="jQuery('#video_track_save_alt').removeAttr('checked');jQuery('#video_track_download').removeAttr('checked');jQuery('#question_alternative_description').slideUp();"/>
	<label class="customFieldLabel Inline" for="video_track_download" ><?php echo $lang["video_tracks_save_export"]; ?></label>
	
    <input type="radio" class="Inline video_track_save_option" id="video_track_download" name="video_track_download" value="yes" onClick="jQuery('#video_track_save_export').removeAttr('checked');jQuery('#video_track_save_alt').removeAttr('checked');jQuery('#question_alternative_description').slideUp();"/>
	<label class="customFieldLabel Inline" for="video_track_download" ><?php echo $lang["download"]; ?></label>
   
	
	<div class="clearerleft"> </div>
</div>

<div class="Question" id="question_alternative_description" style="display:none;">
	<label for="video_track_alt_desc" ><?php echo $lang["description"]; ?></label>
	<input type="text" class="stdwidth" id="video_track_alt_desc" name="video_track_alt_desc" value="" />
	<div class="clearerleft"> </div>
</div>
<div class="video_tracks_buttons">
	<input type="submit" name="submit" class="video_tracks_button" value="<?php echo $lang["video_tracks_generate"]; ?>" onClick="if(jQuery('#video_track_download').is(':checked') && !video_tracks_offline){this.form.submit;}else{ModalPost(this.form,false,true);return false;}"/>
	<input type="submit" name="submit" class="video_tracks_button" value="<?php echo $lang["close"]; ?>" onClick="ModalClose();return false;"/>
</div>

</form>

</div><!--End of BasicsBox -->
