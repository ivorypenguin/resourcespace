<?php
ob_start(); // we will use output buffering to prevent any included files 
            // from outputting stray characters that will mess up the binary download
            // we will clear the buffer and start over right before we download the file
include_once dirname(__FILE__)."/../include/db.php";
include_once dirname(__FILE__)."/../include/general.php";
include_once dirname(__FILE__)."/../include/resource_functions.php";
include_once dirname(__FILE__)."/../include/search_functions.php";

ob_end_clean(); 

if($download_no_session_cache_limiter){session_cache_limiter(false);}

if(strlen(getvalescaped('direct',''))>0){$direct = true;} else { $direct = false;}

# if direct downloading without authentication is enabled, skip the authentication step entirely
if (!($direct_download_noauth && $direct)){
	# External access support (authenticate only if no key provided, or if invalid access key provided)
	$k=getvalescaped("k","");if (($k=="") || (!check_access_key(getvalescaped("ref","",true),$k))) {include dirname(__FILE__)."/../include/authenticate.php";}
}

$ref=getvalescaped("ref","",true);
$size=getvalescaped("size","");
$ext=getvalescaped("ext","");
if(!preg_match('/^[a-zA-Z0-9]+$/', $ext)){$ext="jpg";}

$alternative=getvalescaped("alternative",-1);
$page=getvalescaped("page",1);
$usage=getvalescaped("usage","-1");
$usagecomment=getvalescaped("usagecomment","");


// Is this a user specific download?
$userfiledownload=getvalescaped("userfile","");
if($userfiledownload!="")
	{
	$noattach="";
	$exiftool_write=false;
	$filedetails=explode("_",$userfiledownload);
	$ref=$filedetails[0];
	$downloadkey=strip_extension($filedetails[1]);
	$ext=substr($filedetails[1],strlen($downloadkey)+1);
	$path=get_temp_dir(false,'user_downloads') . "/" . $ref . "_" . md5($username . $downloadkey . $scramble_key) . "." . $ext;
	hook('modifydownloadpath');
	}
else
	{
	
	$resource_data=get_resource_data($ref);
	resource_type_config_override($resource_data["resource_type"]);
	if ($direct_download_noauth && $direct){
		# if this is a direct download and direct downloads w/o authentication are enabled, allow regardless of permissions
		$allowed = true;
	} else {
		# Permissions check
		$allowed=resource_download_allowed($ref,$size,$resource_data["resource_type"],$alternative);
	}

	if (!$allowed)
		{
			# This download is not allowed. How did the user get here?
			exit("Permission denied");
		}

	# additional access check, as the resource download may be allowed, but access restriction should force watermark.	
	$access=get_resource_access($ref);	
	$use_watermark=check_use_watermark($ref);

	# If no extension was provided, we fallback to JPG.
	if ($ext=="") {$ext="jpg";}

	$noattach=getval("noattach","");
	$path=get_resource_path($ref,true,$size,false,$ext,-1,$page,$use_watermark && $alternative==-1,"",$alternative);
	
	hook('modifydownloadpath');
        
	if (!file_exists($path) && $noattach!="")
		{
		# Return icon for file (for previews)
		$info=get_resource_data($ref);
		$path="../gfx/" . get_nopreview_icon($info["resource_type"],$ext,"thm");
		}

    // writing RS metadata to files: exiftool
    // Note: only for downloads (not previews)
    if('' == $noattach && -1 == $alternative && $exiftool_write)
        {
        $tmpfile = write_metadata($path, $ref);

        if(false !== $tmpfile && file_exists($tmpfile))
            {
            $path = $tmpfile;
            }
        }
    }


if (!file_exists($path))
    {
    //include dirname(__FILE__)."/../include/header.php";
    error_alert($lang["downloadfile_nofile"], true);
    exit();
    }
hook('modifydownloadfile');	
$filesize=filesize_unlimited($path);
header("Content-Length: " . $filesize);

# Log this activity (download only, not preview)
if ($noattach=="")
	{
	daily_stat("Resource download",$ref);
	resource_log($ref,'d',0,$usagecomment,"","",$usage,$size);
	
        hook('moredlactions');

	# update hit count if tracking downloads only
	if ($resource_hit_count_on_downloads) { 
		# greatest() is used so the value is taken from the hit_count column in the event that new_hit_count is zero to support installations that did not previously have a new_hit_count column (i.e. upgrade compatability).
		sql_query("update resource set new_hit_count=greatest(hit_count,new_hit_count)+1 where ref='$ref'");
	} 
	
	# We compute a file name for the download.
	$filename=get_download_filename($ref,$size,$alternative,$ext);

	if (!$direct)
		{
		# We use quotes around the filename to handle filenames with spaces.
		header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
		}
	}

# We assign a default mime-type, in case we can find the one associated to the file extension.
$mime="application/octet-stream";

if ($noattach=="")
	{
	$mime = get_mime_type($path);
	}
	
# We declare the downloaded content mime type.
header("Content-Type: $mime");

set_time_limit(0);

if (!hook("replacefileoutput"))
	{
	# New method
	$sent = 0;
	$handle = fopen($path, "r");

	// Now we need to loop through the file and echo out chunks of file data
	while($sent < $filesize)
		{
		echo fread($handle, $download_chunk_size);
		ob_flush();
		$sent += $download_chunk_size;
		}
	}

#Deleting Exiftool temp File:
if ($noattach=="" && $alternative==-1) # Only for downloads (not previews)
	{
	if (file_exists($tmpfile)){delete_exif_tmpfile($tmpfile);}
	}
hook('beforedownloadresourceexit');
exit();

