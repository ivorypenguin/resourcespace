<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("c")) {exit ("Permission denied.");}
include "../../include/resource_functions.php";
include_once "../../include/collections_functions.php";
include "../../include/image_processing.php";

set_time_limit(60*60*4);

$use_local = getvalescaped('use_local', '') !== '';
$collection = getvalescaped("collection","",true);
$alternative = getvalescaped("alternative","");

include "../../include/header.php";

# Define titles.
if ($use_local)
	{
	# Fetch from local upload folder.
	$titleh1 = $lang["addresourcebatchlocalfolder"];
	$titleh2 = $lang["uploadinprogress"];
	}
else
	{
	# Fetch from FTP server.
	$titleh1 = $lang["addresourcebatchftp"];
	$titleh2 = $lang["uploadinprogress"];
	}
?>

<div class="BasicsBox">
<?php
if($alternative!=='')
	{
	?>
	<p>
		<a href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo urlencode($alternative)?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtomanagealternativefiles"]?></a>
		<br / >
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($alternative)?>">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a>
	</p>
	<?php
	}
?>
<h1><?php echo $titleh1 ?></h1>
</div>

<div class="RecordBox">
<div class="RecordPanel"> 
<div class="RecordResouce">
<h2 id="heading2"><?php echo $titleh2 ?></h2>
<p id="uploadstatus"><b><?php echo $lang["donotmoveaway"]?></b><br/><br/></p>
<p id="uploadlog"></p>
<div class="clearerleft"> </div>
</div>
</div>
<div class="PanelShadow"></div>
</div>

<?php
include "../../include/footer.php";
flush();

# Download files
if (!array_key_exists("uploadfiles",$_POST))
	{
	?><script type="text/javascript">alert("<?php echo $lang["pleaseselectfiles"]?>");history.go(-1);</script><?php
	exit();
	}

if ($use_local) // Test if we fetch files from local upload folder.
	{
	# We compute the folder name from the upload folder option.
	$folder = getAbsolutePath($local_ftp_upload_folder, true);

	if ($groupuploadfolders) // Test if we are using sub folders assigned to groups.
		{
		$folder.= DIRECTORY_SEPARATOR . $usergroup;
		}
	} // Test if we fetch files from local upload folder.
    if ($useruploadfolders) // Test if we are using sub folders assigned to groups.
    	{
    	$udata=get_user($userref);
    	$folderadd=htmlspecialchars($udata["username"]);
    	
    	$folder.=   $folderadd;
    	}
    
$uploadfiles=$_POST["uploadfiles"];
if (!hook("alternativebatchupload")) {
$done=0;$failed=0;
$refs=array();
for ($n=0;$n<count($uploadfiles);$n++)
	{
	if (!$use_local)
		{
		# Connect to FTP server
		$ftp=ftp_connect(getval("ftp_server",""));
		ftp_login($ftp,getval("ftp_username",""),getval("ftp_password",""));
		ftp_pasv($ftp,true);
		}

    $ftp_folder = getval("ftp_folder","");
    $ftp_folder_stripped = rtrim($ftp_folder);
    $ftp_folder_stripped = rtrim($ftp_folder_stripped, '/');
    $path = $ftp_folder_stripped . DIRECTORY_SEPARATOR . $uploadfiles[$n];
	
	if($alternative!=='')
		{
		# Upload an alternative file

        # Add a new alternative file
        $ref=add_alternative_file($alternative,$uploadfiles[$n]);
        
        $extension=explode(".",$uploadfiles[$n]);
		$extension=trim(strtolower($extension[count($extension)-1]));
                            
        # Find the path for this resource.
        $localpath=get_resource_path($alternative, true, "", true, $extension, -1, 1, false, "", $ref);
                            
        # Move the sent file to the alternative file location
        $result='';
		error_reporting(0);
		                 
        # PLUpload - file was sent chunked and reassembled - use the reassembled file location
        $result=copy($folder . DIRECTORY_SEPARATOR . $uploadfiles[$n], $localpath);

        if ($result===false) 
			{
			$status = str_replace("%path%", $path, $lang["upload_failed_for_path"]);
			sleep(2);
			$failed++;
			?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $status?></br>";</script><?php
			flush();
			}
		else
			{
			$status = str_replace(array("%file%", "%filestotal%", "%path%"), array($n+1, count($uploadfiles), $path), $lang["uploadedstatus"]);
			?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $status?></br>";</script><?php
			flush();

			chmod($path,0777);
			$file_size = @filesize_unlimited($localpath);
							
			# Save alternative file data.
			sql_query("update resource_alt_files set file_name='" . escape_check($uploadfiles[$n]) . "',file_extension='" . escape_check($extension) . "',file_size='" . $file_size . "',creation_date=now() where resource='$alternative' and ref='$ref'");
							
			if ($alternative_file_previews_batch)
				{
				create_previews($alternative,false,$extension,false,false,$ref);
				
				# Show thumb?
				$rd = get_alternative_file($alternative,$ref);
				$thumb = get_resource_path($alternative, true, "thm", false, $rd["preview_extension"]);
				if (file_exists($thumb))
					{
					$previewstatus.= "<br/><img src='" . get_resource_path($alternative, false, "thm", false, $rd["preview_extension"]) . "'><br/><br/>";
					}
				else {$previewstatus.= "<br/><br/>";}
				?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $previewstatus ?>";</script><?php
				flush();
				}
			hook('after_alt_upload','',array($alternative,array("ref"=>$ref,"file_size"=>$file_size,"extension"=>$extension,"name"=>$uploadfiles[$n],"altdescription"=>"","path"=>$localpath,"basefilename"=>str_ireplace("." . $extension, '', $uploadfiles[$n]))));
							
			// Check to see if we need to notify users of this change							
			if($notify_on_resource_change_days!=0)
				{								
				// we don't need to wait for this..
				ob_flush();flush();
				notify_resource_change($alternative);
				}
									
			# Update disk usage
			update_disk_usage($alternative);
			
			$done++;
			}
		}
	else
		{
		# Copy the resource
		$ref=copy_resource(0-$userref);
	
		# Find and store extension in the database
		$extension=explode(".",$uploadfiles[$n]);
		$extension=trim(strtolower($extension[count($extension)-1]));
		sql_query("update resource set file_extension='$extension',preview_extension='$extension' where ref='$ref'");


		$localpath=get_resource_path($ref,true,"",true,$extension);

		$result=false;
		error_reporting(0);

		if ($use_local)
			{
			$result=copy($folder . DIRECTORY_SEPARATOR . $uploadfiles[$n],$localpath);
			}
		else
			{
			$result=ftp_get($ftp,$localpath,$path,FTP_BINARY);
			}

		if (!$result) 
			{
			$status = str_replace("%path%", $path, $lang["upload_failed_for_path"]);
			sleep(2);
			$failed++;
			?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $status?></br>";</script><?php
			flush();
			}
		else
			{
			$status = str_replace(array("%file%", "%filestotal%", "%path%"), array($n+1, count($uploadfiles), $path), $lang["uploadedstatus"]);
			?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $status?></br>";</script><?php
			flush();

			if($enable_thumbnail_creation_on_upload) // Test if thumbnail creation is allowed during upload
				{
				# Create previews
				create_previews($ref, false, $extension);
				$previewstatus = str_replace(array("%file%", "%filestotal%"), array(($n+1), count($uploadfiles)), $lang["previewstatus"]);

				# Show thumb?
				$rd = get_resource_data($ref);
				$thumb = get_resource_path($ref, true, "thm", false, $rd["preview_extension"]);
				if (file_exists($thumb))
					{
					$previewstatus.= "<br/><img src='" . get_resource_path($ref, false, "thm", false, $rd["preview_extension"]) . "'><br/><br/>";
					}
				else {$previewstatus.= "<br/><br/>";}
				?><script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo $previewstatus ?>";</script><?php
				flush();

				} // Test if thumbnail creation is allowed during upload

			# Store original filename in field, if set
			if (isset($filename_field))
				{
				$filename = $uploadfiles[$n];
				if ($use_local)
					{
					$filename = mb_basename($filename);
					}
				update_field($ref,$filename_field, $filename);
				}

			# get file metadata 
			if (getval("no_exif","")=="") {extract_exif_comment($ref,$extension);}
		
			# extract text from documents (e.g. PDF, DOC).
			global $extracted_text_field;
			if (isset($extracted_text_field) && !$no_exif) {extract_text($ref,$extension);}

			$done++;

			# Add to collection?
			if ($collection!="")
				{
				$refs[] = $ref;
				}

			# Log this
			daily_stat("Resource upload",$ref);
			resource_log($ref,'u',0);

			}

		}
	}

if (!$use_local)
	{
	ftp_close($ftp);
	}

switch ($done)
    {
    case 0:
        $summary_ok = $lang["resources_uploaded-0"];
        break;
    case 1:
        $summary_ok = $lang["resources_uploaded-1"];
        break;
    default:
        $summary_ok = str_replace("%done%", $done, $lang["resources_uploaded-n"]);
        break;
    }
switch ($failed)
    {
    case 0:
        $summary_failed = $lang["resources_failed-0"];
        break;
    case 1:
        $summary_failed = $lang["resources_failed-1"];
        break;
    default:
        $summary_failed = str_replace("%done%", $failed, $lang["resources_failed-n"]);
        break;
    }

?>

<script type="text/javascript">document.getElementById('heading2').innerHTML="<?php echo $lang['uploadcomplete']; ?>";</script>
<script type="text/javascript">document.getElementById('uploadstatus').innerHTML="";</script>
<script type="text/javascript">document.getElementById('uploadlog').innerHTML+="<?php echo '<h3>' . $lang['upload_summary'] . '</h3><p>' . $summary_ok . '</br>' . $summary_failed; ?></p>";</script><?php

# Add to collection?
if ($collection!="")
	{
	foreach($refs as $ref)
		{
		?><script type="text/javascript">CollectionDivLoad('<?php echo $baseurl . "/pages/collections.php?add=" . urlencode($ref) . "&nc=" . time() . "&search=" . urlencode($search)?>');</script><?php
		}
	}
}
