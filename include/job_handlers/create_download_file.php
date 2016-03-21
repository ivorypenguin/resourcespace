<?php

/*
Run a command that will create an output file, optionally specifying a download URL that can be sent to the user
Requires the following:-

$job_data["resource"] - Resource ID
$job_data["title"] -  Download title/description
$job_data["command"] - command to run
$job_data["outputfile"] - target output file
$job_data["lifetime"] - [optional]  length of time for which file will be available before automatic deletion
$job_data["url"] - [optional] URL to send to the user

If a file is to be created for only a specific user to download you can create a random string e.g. $randomstring and set the path and url for the job as below:-

	$job_data["outputfile"] = get_temp_dir(false,'user_downloads') . "/" . $ref . "_" . $randomstring . $scramble_key) . ".<file extension here>";
	$job_data["url"]=$baseurl . "/pages/download.php?userfile=" . $ref . "_" . $randomstring . ".<file extension here>;
	
*/

include_once dirname(__FILE__) . "/../image_processing.php";
				
$shell_exec_cmd = str_replace("%%TARGETFILE%%",$job_data["outputfile"],$job_data["command"]);
global $config_windows;
if ($config_windows)
	{
	file_put_contents(get_temp_dir() . "/create_alt_" . $randstring . ".bat",$shell_exec_cmd);
	$shell_exec_cmd=get_temp_dir() . "/create_alt_" . $randstring . ".bat";
	}
echo "Running command " . $shell_exec_cmd . PHP_EOL;
$output=run_command($shell_exec_cmd);

 if(file_exists($job_data["outputfile"]))
	{
	global $lang, $baseurl, $download_file_lifetime, $offline_job_delete_completed;
	$url=(isset($job_data["url"]))?$job_data["url"]:(isset($job_data["resource"])?$baseurl . "/?r=" . $job_data["resource"]:"");
    $message=$job_success_text!=""?$job_success_text:$lang["download_file_created"]  . ": " . str_replace(array('%ref','%title'),array($job_data['resource'],$resource['field' . $view_title_field]),$lang["ref-title"]) . "(" . $job_data["alt_name"] . "," . $job_data["alt_description"] . ")";
	message_add($job["user"],$message,$url,0);
	if($offline_job_delete_completed)
		{
		job_queue_delete($jobref);
		}
	else
		{
		job_queue_update($jobref,$job_data,STATUS_COMPLETE);
		}
	if(isset($job_data["lifetime"]))
		{
		$delete_job_data=array();
		$delete_job_data["file"]=$job_data["outputfile"];
		$delete_date = date('Y-m-d H:i:s',time()+(60*60*24*$download_file_lifetime));
		$job_code=md5($job_data["outputfile"]); 
		job_queue_add("delete_file",$delete_job_data,"",$delete_date,"","",$job_code);
		}
	}
else
	{
	// Job failed, upate job queue
	job_queue_update($jobref,$job_data,STATUS_ERROR);
    $message=$job_failure_text!=""?$job_failure_text:$lang["download_file_creation_failed"]  . ": " . str_replace(array('%ref','%title'),array($job_data['resource'],$resource['field' . $view_title_field]),$lang["ref-title"]) . "(" . $job_data["alt_name"] . "," . $job_data["alt_description"] . ")";
    $url=$baseurl . "/?r=" . $job_data["resource"];
	}
		
		
