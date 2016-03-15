<?php

if (!defined("RUNNING_ASYNC")) {define("RUNNING_ASYNC", !isset($ffmpeg_preview));}

if (!RUNNING_ASYNC)
	{
	global $qtfaststart_path, $qtfaststart_extensions;
	}
else
	{
	require dirname(__FILE__)."/db.php";
	require_once dirname(__FILE__)."/general.php";
	require dirname(__FILE__)."/resource_functions.php";
	
	if (empty($_SERVER['argv'][1]) || $scramble_key!==$_SERVER['argv'][1]) {exit("Incorrect scramble_key");}
	
	if (empty($_SERVER['argv'][2])) {exit("Ref param missing");}
	$ref=$_SERVER['argv'][2];
	
	if (empty($_SERVER['argv'][3])) {exit("File param missing");}
	$file=$_SERVER['argv'][3];
	
	if (empty($_SERVER['argv'][4])) {exit("Target param missing");}
	$target=$_SERVER['argv'][4];
	
	if (!isset($_SERVER['argv'][5])) {exit("Previewonly param missing");}
	$previewonly=$_SERVER['argv'][5];
	
	if (!isset($_SERVER['argv'][6])) {exit("Snapshottime param missing");}
	$snapshottime=$_SERVER['argv'][6];

	if (!isset($_SERVER['argv'][7])) {exit("Alternative param missing");}
	$alternative=$_SERVER['argv'][7];

	debug ("Starting ffmpeg_processing.php async with parameters: ref=$ref, file=$file, target=$target, previewonly=$previewonly, snapshottime=$snapshottime, alternative=$alternative");

	# SQL Connection may have hit a timeout
	sql_connect();
	sql_query("UPDATE resource SET is_transcoding = 1 WHERE ref = '".escape_check($ref)."'");
	}

# Increase timelimit
set_time_limit(0);

$ffmpeg_fullpath = get_utility_path("ffmpeg");

# Create a preview video (FLV)
$targetfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",$alternative); 

$snapshotsize=getimagesize($target);
$width=$snapshotsize[0];
$height=$snapshotsize[1];
$sourcewidth=$width;
$sourceheight=$height;

global $config_windows, $ffmpeg_get_par;
if ($ffmpeg_get_par) {
  $par = 1;
  # Find out the Pixel Aspect Ratio
  $shell_exec_cmd = $ffmpeg_fullpath . " -i " . escapeshellarg($file) . " 2>&1";

  if (isset($ffmpeg_command_prefix))
    {$shell_exec_cmd = $ffmpeg_command_prefix . " " . $shell_exec_cmd;}
  
  if ($config_windows)
  	{
  	# Windows systems have a hard time with the long paths used for video generation. This work-around creates a batch file containing the command, then executes that.
  	file_put_contents(get_temp_dir() . "/ffmpeg.bat",$shell_exec_cmd);
  	$shell_exec_cmd=get_temp_dir() . "/ffmpeg.bat";
  	}

  $output=run_command($shell_exec_cmd);
  
  preg_match('/PAR ([0-9]+):([0-9]+)/m', $output, $matches);
  if (@intval($matches[1]) > 0 && @intval($matches[2]) > 0) {
    $par = $matches[1] / $matches[2];
    if($par < 1) {
      $width = ceil($width * $par);
    }
    elseif($par > 1) {
      $height = ceil($height / $par);
    }
  }  
}



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
	
# Frame size must be a multiple of two
if ($width % 2){$width++;}
if ($height % 2) {$height++;}

/* Plugin hook to modify the output W & H before running ffmpeg. Better way to return both W and H at the same is appreciated.  */
$tmp = hook("ffmpegbeforeexec", "", array($ffmpeg_fullpath, $file));
if (is_array($tmp) and $tmp) {list($width, $height) = $tmp;}

if (!hook("replacetranscode","",array($file,$targetfile,$ffmpeg_global_options,$ffmpeg_preview_options,$width,$height))){
	
} else { 
	exit(); // Do not proceed, replacetranscode hook intends to avoid everything below
	}	
	
	$shell_exec_cmd = $ffmpeg_fullpath . " $ffmpeg_global_options -y -i " . escapeshellarg($file) . " $ffmpeg_preview_options -t $ffmpeg_preview_seconds -s {$width}x{$height} " . escapeshellarg($targetfile);


	if (isset($ffmpeg_command_prefix))
		{$shell_exec_cmd = $ffmpeg_command_prefix . " " . $shell_exec_cmd;}

	$tmp = hook("ffmpegmodpreparams", "", array($shell_exec_cmd, $ffmpeg_fullpath, $file));
	if ($tmp) {$shell_exec_cmd = $tmp;}

	if ($config_windows)
		{
		# Windows systems have a hard time with the long paths used for video generation. This work-around creates a batch file containing the command, then executes that.
		file_put_contents(get_temp_dir() . "/ffmpeg.bat",$shell_exec_cmd);
		$shell_exec_cmd=get_temp_dir() . "/ffmpeg.bat";
		}

	$output=run_command($shell_exec_cmd);


if ($ffmpeg_get_par && (isset($snapshotcheck) && $snapshotcheck==false)) {
  if ($par > 0 && $par <> 1) {
    # recreate snapshot with correct PAR
    $width=$sourcewidth;
    $height=$sourceheight;
    if($par < 1) {
      $width = ceil($sourcewidth * $par);
    }
    elseif($par > 1) {
      $height = ceil($sourceheight / $par);
    }
    # Frame size must be a multiple of two
    if ($width % 2){$width++;}
    if ($height % 2) {$height++;}
    $shell_exec_cmd = $ffmpeg_fullpath . "  $ffmpeg_global_options -y -i " . escapeshellarg($file) . " -s {$width}x{$height} -f image2 -vframes 1 -ss ".$snapshottime." " . escapeshellarg($target);
    $output = run_command($shell_exec_cmd);
  }
}

if (!file_exists($targetfile))
    {
    error_log("FFmpeg failed: ".$shell_exec_cmd);
    }

if (isset($qtfaststart_path) && file_exists($qtfaststart_path . "/qt-faststart") && in_array($ffmpeg_preview_extension, $qtfaststart_extensions))
    {
	$targetfiletmp=$targetfile.".tmp";
	rename($targetfile, $targetfiletmp);
    $output=run_command($qtfaststart_path . "/qt-faststart " . escapeshellarg($targetfiletmp) . " " . escapeshellarg($targetfile));
    unlink($targetfiletmp);
    }

# Handle alternative files.
global $ffmpeg_alternatives;
if (isset($ffmpeg_alternatives))
	{
	$ffmpeg_alt_previews=array();
	for($n=0;$n<count($ffmpeg_alternatives);$n++)
		{
		$generate=true;
		if (isset($ffmpeg_alternatives[$n]["lines_min"]))
			{
			# If this alternative size is larger than the source, do not generate.
			if ($ffmpeg_alternatives[$n]["lines_min"]>$sourceheight)
				{
				$generate=false;
				}
			
			}

        $tmp = hook("preventgeneratealt", "", array($file));
        if ($tmp===true) {$generate = false;}

		if ($generate) # OK to generate this alternative?
			{

			if(!hook("removepreviousalts", "", array($ffmpeg_alternatives, $file, $n))):

			# Remove any existing alternative file(s) with this name.
			# SQL Connection may have hit a timeout
			sql_connect();
			$existing=sql_query("select ref from resource_alt_files where resource='$ref' and name='" . escape_check($ffmpeg_alternatives[$n]["name"]) . "'");
			for ($m=0;$m<count($existing);$m++)
				{
				delete_alternative_file($ref,$existing[$m]["ref"]);
				}
			
			endif;

			$alt_type = '';
			if(isset($ffmpeg_alternatives[$n]['alt_type'])) {
				$alt_type = $ffmpeg_alternatives[$n]["alt_type"];
			}

			# Create the alternative file.
			$aref=add_alternative_file($ref,$ffmpeg_alternatives[$n]["name"],'', '', '', 0, $alt_type);
			$apath=get_resource_path($ref,true,"",true,$ffmpeg_alternatives[$n]["extension"],-1,1,false,"",$aref);
			
			# Process the video 
            $shell_exec_cmd = $ffmpeg_fullpath . "  $ffmpeg_global_options -y -i " . escapeshellarg($file) . " " . $ffmpeg_alternatives[$n]["params"] . " " . escapeshellarg($apath);

            $tmp = hook("ffmpegmodaltparams", "", array($shell_exec_cmd, $ffmpeg_fullpath, $file, $n, $aref));
            if($tmp) {$shell_exec_cmd = $tmp;}
            
            $output = run_command($shell_exec_cmd);

	    if(isset($qtfaststart_path))
			{
			if($qtfaststart_path && file_exists($qtfaststart_path . "/qt-faststart") && in_array($ffmpeg_alternatives[$n]["extension"], $qtfaststart_extensions) ){
				$apathtmp=$apath.".tmp";
				rename($apath, $apathtmp);
				$output=run_command($qtfaststart_path . "/qt-faststart " . escapeshellarg($apathtmp) . " " . escapeshellarg($apath)." 2>&1");
				unlink($apathtmp);
				}
			}
			if (file_exists($apath))
				{
				# Update the database with the new file details.
				$file_size = filesize_unlimited($apath);
				# SQL Connection may have hit a timeout
				sql_connect();
				sql_query("update resource_alt_files set file_name='" . escape_check($ffmpeg_alternatives[$n]["filename"] . "." . $ffmpeg_alternatives[$n]["extension"]) . "',file_extension='" . escape_check($ffmpeg_alternatives[$n]["extension"]) . "',file_size='" . $file_size . "',creation_date=now() where ref='$aref'");
				// add this filename to be added to resource.ffmpeg_alt_previews
				if (isset($ffmpeg_alternatives[$n]['alt_preview']) && $ffmpeg_alternatives[$n]['alt_preview']==true){
					$ffmpeg_alt_previews[]=basename($apath);
					}
				}

				if(!file_exists($apath) && file_exists($targetfile) && RUNNING_ASYNC) {
					error_log('FFmpeg alternative failed: ' . $shell_exec_cmd);
					# SQL Connection may have hit a timeout
					sql_connect();
					# Change flag as the preview was created and that is the most important of them all
					sql_query("UPDATE resource SET is_transcoding = 0 WHERE ref = '" . escape_check($ref) . "'");
				}
			}
		/*// update the resource table with any ffmpeg_alt_previews	
		if (count($ffmpeg_alt_previews)>0){
			$ffmpeg_alternative_previews=implode(",",$ffmpeg_alt_previews);
			sql_query("update resource set ffmpeg_alt_previews='".escape_check($ffmpeg_alternative_previews)."' where ref='$ref'");
		}
		*/
	}
}



if (RUNNING_ASYNC)
	{
	# SQL Connection may have hit a timeout
	sql_connect();
	sql_query("UPDATE resource SET is_transcoding = 0 WHERE ref = '".escape_check($ref)."'");
	
	if ($previewonly)
		{
		unlink($file);
		}
	}

