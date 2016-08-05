<?php

if(!(php_sapi_name() == 'cli')){exit ("Access denied"); }

include dirname(__FILE__) . "/../../../include/db.php";
include_once dirname(__FILE__) . "/../../../include/general.php";
include dirname(__FILE__) . "/../../../include/resource_functions.php";
include dirname(__FILE__) . "/../include/tms_link_functions.php";

$debug_log=false;

ob_end_clean();
set_time_limit(60*60*40);
if($tms_link_email_notify!=""){$email_notify=$tms_link_email_notify;}

// Check when this script was last run - do it now in case of permanent process locks
$scriptlastran=sql_value("select value from sysvars where name='last_tms_import'","");

$tms_link_script_failure_notify_seconds=intval($tms_link_script_failure_notify_days)*60*60*24;

if($scriptlastran=="" || time()>=(strtotime($scriptlastran)+$tms_link_script_failure_notify_seconds))
	{
	$tmsfailedsubject=(($tms_link_test_mode)?"TESTING MODE ":"") . "TMS Import script - WARNING";
	send_mail($email_notify,$tmsfailedsubject,"WARNING: The TMS Import Script has not completed since "  . (($scriptlastran!="")?date("l F jS Y @ H:i:s",strtotime($scriptlastran)):$lang["status-never"]) . ".\r\n You can safely ignore this warning only if you subsequently receive notification of a successful script completion.",$email_from);
	}


if (php_sapi_name() == 'cli' && $argc == 2)
	{
	if ( in_array($argv[1], array('--help', '-help', '-h', '-?')) )
		{
		echo "To clear the lock after a failed run, ";
  		echo "pass in '--clearlock', '-clearlock', '-c' or '--c'." . PHP_EOL;
  		exit("Bye!");
  		}
	else if ( in_array($argv[1], array('--clearlock', '-clearlock', '-c', '--c')) )
		{
		if ( is_process_lock("tms_link") )
			{
			clear_process_lock("tms_link");
			}
		}
	else
		{
		exit("Unknown argv: " . $argv[1]);
		}
	} 

# Check for a process lock
if (is_process_lock("tms_link")) 
	{
	echo 'TMS script lock is in place. Deferring.' . PHP_EOL;
	echo 'To clear the lock after a failed run use --clearlock flag.' . PHP_EOL;
	$tmsfailedsubject=(($tms_link_test_mode)?"TESTING MODE ":"") . "TMS Import script - FAILED";
	send_mail($email_notify,$tmsfailedsubject,"The TMS script failed to run because a process lock was in place. This indicates that the previous run did not complete. If you need to clear the lock after a failed run, run the script as follows:-\r\n\r\nphp tms_update_script.php --clearlock\r\n",$email_from);
	exit();
	}
set_process_lock("tms_link");

// Record the start time
$tms_script_start_time = microtime(true);

$tms_resources=tms_link_get_tms_resources();

$tmscount=count($tms_resources);
$tmsupdated=0;
$logtext="";
$tms_updated_array=array();
$tmserrors=array();

if(trim($tms_link_log_directory)!="")
	{
	if (!is_dir($tms_link_log_directory))
		{
		@mkdir($tms_link_log_directory, 0755, true);
		if (!is_dir($tms_link_log_directory))
			{
			echo 'Unable to create log directory: ' . htmlspecialchars($tms_link_log_directory) . PHP_EOL;
			}
		}
	else
		{
		// Valid log directory 	
		
		// clean up old files
		$iterator = new DirectoryIterator($tms_link_log_directory);
		$expirytime = $tms_script_start_time - (intval($tms_link_log_expiry) * 24 * 60 * 60) ;
		foreach ($iterator as $fileinfo)
			{
			if ($fileinfo->isFile()) 
				{
				$filename = $fileinfo->getFilename();			
				if (substr($filename,0,15)=="tms_import_log_" && $fileinfo->getMTime() < $expirytime)
					{
					// Attempt to delete file - it is a TMS log and is older than the log expiration period
					@unlink($fileinfo->getPathName());
					}
				}
			}
		
		$logfile=fopen($tms_link_log_directory . DIRECTORY_SEPARATOR . "tms_import_log_" . date("Y_m_d_H_i") . ".log","ab");
		}
	}
	
// Get field mapping configuration
$tms_link_field_mappings=unserialize(base64_decode($tms_link_field_mappings_saved));

$tmspointer=0;
if(!$tms_link_test_mode || !is_numeric($tms_link_test_count)){$tms_link_test_count=999999999;}

//exit(print_r($tms_resources));
while ($tmspointer<$tmscount && $tmspointer<$tms_link_test_count)
	{
	unset($tms_query_ids);
	$tms_query_ids=array();		
	for($t=$tmspointer;$t<($tmspointer + $tms_link_query_chunk_size) && (($tmspointer + $t)<$tms_link_test_count) && $t<$tmscount;$t++)
		{
		if($tms_resources[$t]["objectid"] != "" && is_numeric($tms_resources[$t]["objectid"]) && strpos($tms_resources[$t]["objectid"],".")===false)
			{
			$tms_query_ids[]=$tms_resources[$t]["objectid"];
			}
		else
			{
			$logmessage = "Invalid TMS data stored in ResourceSpace: " . $tms_resources[$t]["objectid"];
			$tmserrors[$tms_resources[$t]["resource"]] = $logmessage;
			//echo $logmessage;
			fwrite($logfile,$logmessage);
			}
		}
		
				
	fwrite($logfile,"Retrieving data from TMS system\r\n");
	$tmsresults=tms_link_get_tms_data("", $tms_query_ids);	
	
	if(!is_array($tmsresults) || count($tmsresults)==0)
		{
		echo "No TMS data received, continuing" . PHP_EOL;
		$tmspointer = $tmspointer+$tms_link_query_chunk_size;
		continue;
		}
	
	// Go through this set of resources and update db/show data/do something else
	for($ri=$tmspointer;$ri<($tmspointer + $tms_link_query_chunk_size) && (($tmspointer + $ri)<$tms_link_test_count) && $ri<$tmscount;$ri++)
		{
		$tms_data_found=false;
		//print_r($tms_resources[$ri]);
		foreach($tmsresults as $tmsresult)
			{
			//print_r($tmsresult);
			if($tms_resources[$ri]["objectid"]==$tmsresult["ObjectID"])
				{
				$tms_data_found=true;
				debug("TMS_LINK - Checking resource: "  . $tms_resources[$ri]["resource"]  . ". Object ID: " . $tms_resources[$ri]["objectid"]);
				$logmessage= "Checking resource: "  . $tms_resources[$ri]["resource"]  . ". Object ID: " . $tms_resources[$ri]["objectid"] . "\r\n";
				echo $logmessage;
				fwrite($logfile,$logmessage);
				//exit();
				
				// Check checksum
				if(isset($tmsresult["RowChecksum"]) && $tms_resources[$ri]["checksum"]==$tmsresult["RowChecksum"])
					{
					debug("TMS_LINK ---- Checksum matches for resource #" .  $tms_resources[$ri]["resource"] . ". Skipping...\r\n");
					$logmessage = "-- Checksum matches. Skipping...\r\n";
					echo $logmessage;
					fwrite($logfile,$logmessage);
					}
				else
					{
					//print_r($tmsresult);
					debug("TMS_LINK ---- UPDATE! Checksum differs for resource #" .  $tms_resources[$ri]["resource"] . "\r\n");
					$logmessage = "-- Checksum differs.(CURRENT: " . $tms_resources[$ri]["checksum"] . " vs NEW: " . (isset($tmsresult["RowChecksum"])?$tmsresult["RowChecksum"]:"EMPTY") . ") Updating.\r\n";
					echo $logmessage;
					fwrite($logfile,$logmessage);
					
					$updatedok=false;
					// Update fields if necessary
					foreach($tms_link_field_mappings as $tms_link_column_name=>$tms_link_field_id)
						{
						if($tms_link_field_id!="" && $tms_link_field_id!=0 && isset($tmsresult[$tms_link_column_name]))
							{	
							$existingval=get_data_by_field($tms_resources[$ri]["resource"],$tms_link_field_id);																		
							if ($existingval!== $tmsresult[$tms_link_column_name])
								{
								if(!$tms_link_test_mode)
									{
									$logmessage = "---- Updating RS field " . $tms_link_field_id . " from column " . $tms_link_column_name . ". VALUE: " . $tmsresult[$tms_link_column_name] . "\r\n";
									echo $logmessage;
									fwrite($logfile,$logmessage);
									update_field($tms_resources[$ri]["resource"],$tms_link_field_id,escape_check($tmsresult[$tms_link_column_name]));
									}
								if($tms_link_field_id!=$tms_link_checksum_field){$updatedok=true;} // Don't record as successful - if it is only the checksum that has changed then this has not really been worth processing 
								}
							}								
						}
					if($updatedok)
						{
						$tmsupdated++;
						$tms_updated_array[$tms_resources[$ri]["resource"]]=$tms_resources[$ri]["objectid"];
						
						fwrite($logfile,"Resource " . $tms_resources[$ri]["resource"] . " : Updated successfully \r\n");
						}
					else
						{
						$logmessage="Checksum differs but no changes were found when comparing ResourceSpace data with TMS data.\r\n";
						$tmserrors[$tms_resources[$ri]["resource"]]=$logmessage;
						echo $logmessage;
						fwrite($logfile,"Resource " . $tms_resources[$ri]["resource"] . " : " . $logmessage);
						}
					}
				}
			
			
			}
		if(!$tms_data_found && !isset($tmserrors[$tms_resources[$ri]["resource"]]))
			{
			$tmserrors[$tms_resources[$ri]["resource"]]="No TMS data found for resource - ObjectID : " . $tms_resources[$ri]["objectid"];
			}
		}
	
	// Update pointer and go onto next set of resources
	$tmspointer = $tmspointer+$tms_link_query_chunk_size;
	
	}
	
$logtext.=sprintf("TMS Script completed in %01.2f seconds.\n", microtime(true) - $tms_script_start_time) . "\r\n";

if($tmscount==0)
	{
	$tmsstatustext="Completed with errors";
	fwrite($logfile,$tmsstatustext);
	$logtext.="No Resources found with TMS IDs. Please check the tms_link plugin configuration.";	
	fwrite($logfile,"No Resources found with TMS IDs. Please check the tms_link plugin configuration.");
	}
else
	{
	$logtext.="Processed " . $tmscount .  " resource(s) with TMS Object IDs.\r\n\r\n";
	
	
	$logtext.="Successfully updated " . $tmsupdated .  " resource(s).\r\n\r\n";
	if($tmsupdated>0)
		{$logtext .= "Resource ID :  TMS ObjectID \r\n";}
	foreach($tms_updated_array as $success_ref=>$success_tmsid)
		{
		$logtext .=  $success_ref . " : " . $success_tmsid . "\r\n";
		}
		
	
	if(count($tmserrors)!=0)
		{
		$tmsstatustext="\r\nCompleted with errors";
		$logtext.="\r\n\r\nFailed to update " . count($tmserrors) .  " resource(s).\r\n";

		
		$logtext .= "\r\nError summary: -\r\n"; 
		foreach($tmserrors as $errorresource=>$tmserror)
			{
			$logtext .= "Resource ID " . $errorresource . " " . $tmserror . "\r\n";		
			}
		}	
	else
		{
		$tmsstatustext="Success!";
		}
	
	fwrite($logfile,$tmsstatustext);
	}
	
$tmssubject=(($tms_link_test_mode)?"TESTING MODE - ":"") . "TMS Import script - " . $tmsstatustext;
send_mail($email_notify,$tmssubject,$logtext,$email_from);

echo $logtext;
fclose($logfile);

clear_process_lock("tms_link");
sql_query("delete from sysvars where name='last_tms_import'");
sql_query("insert into sysvars values('last_tms_import', now())");
