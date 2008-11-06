<?

define("RUNNING_ASYNC", !isset($ffmpeg_preview));

if (RUNNING_ASYNC)
	{
	require dirname(__FILE__)."/db.php";
	require dirname(__FILE__)."/general.php";
	
	if (empty($_SERVER['argv'][1]) || $scramble_key!==$_SERVER['argv'][1]) {exit("Incorrect scramble_key");}
	
	if (empty($_SERVER['argv'][2])) {exit("Ref param missing");}
	$ref=$_SERVER['argv'][2];
	
	if (empty($_SERVER['argv'][3])) {exit("File param missing");}
	$file=$_SERVER['argv'][3];
	
	if (empty($_SERVER['argv'][4])) {exit("Target param missing");}
	$target=$_SERVER['argv'][4];
	
	if (!isset($_SERVER['argv'][5])) {exit("Previewonly param missing");}
	$previewonly=$_SERVER['argv'][5];
	
	$ffmpeg_path.="/ffmpeg";
	if (!file_exists($ffmpeg_path)) {$ffmpeg_path.=".exe";}
	$ffmpeg_path=escapeshellarg($ffmpeg_path);
	
	sql_query("UPDATE resource SET is_transcoding = 1 WHERE ref = '".escape_check($ref)."'");
	}
else 
	{
	global $qtfaststart_path, $qtfaststart_extensions;
	}
	
# Increase timelimit
set_time_limit(0);

# Create a preview video (FLV)
$targetfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension); 

$snapshotsize=getimagesize($target);
$width=$snapshotsize[0];
$height=$snapshotsize[1];

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

$output=shell_exec($ffmpeg_path . " -y -i " . escapeshellarg($file) . " $ffmpeg_preview_options -s {$width}x{$height} -t $ffmpeg_preview_seconds " . escapeshellarg($targetfile));

if($qtfaststart_path && file_exists($qtfaststart_path . "/qt-faststart") && in_array($ffmpeg_preview_extension, $qtfaststart_extensions) )
    {
	$targetfiletmp=$targetfile.".tmp";
	rename($targetfile, $targetfiletmp);
    $output=shell_exec($qtfaststart_path . "/qt-faststart " . escapeshellarg($targetfiletmp) . " " . escapeshellarg($targetfile));
    unlink($targetfiletmp);
    }

if (RUNNING_ASYNC)
	{
	sql_query("UPDATE resource SET is_transcoding = 0 WHERE ref = '".escape_check($ref)."'");
	
	if ($previewonly)
		{
		unlink($file);
		}
	}
    
?>