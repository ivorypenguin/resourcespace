<?php
#
# Healthcheck.php
#
#
# Performs some basic system checks. Useful for remote monitoring of ResourceSpace installations.
#

include "../../include/db.php";
include_once "../../include/general.php";

# Check database connectivity.
$check=sql_value("select count(*) value from resource_type",0);
if ($check<=0) exit("FAIL - SQL query produced unexpected result");

# Check write access to filestore
if (!is_writable($storagedir)) {exit("FAIL - $storagedir is not writeable.");}
$hash=md5(time());
$file=$storagedir . "/write_text.txt";
file_put_contents($file,$hash);$check=file_get_contents($file);unlink($file);
if ($check!==$hash) {exit("FAIL - test write to disk returned a different string ('$hash' vs '$check')");}


# Check free disk space is sufficient.
$avail=disk_total_space($storagedir);
$free=disk_free_space($storagedir);
if (($free/$avail)<0.1) {exit("FAIL - less than 10% disk space free.");} 


// Check write access to sql_log
if($mysql_log_transactions)
    {
    $mysql_log_dir=dirname($mysql_log_location);
    if(!is_writeable($mysql_log_dir) || (file_exists($mysql_log_location) && !is_writeable($mysql_log_location)))
	{
	exit("FAIL - invalid \$mysql_log_location specified in config file: " . $mysql_log_location); 
	}
    }
    
// Check write access to debug_log 
if($debug_log)
    {
    if (!isset($debug_log_location)){$debug_log_location=get_debug_log_dir() . "/debug.txt";}
    $debug_log_dir=dirname($debug_log_location);
    if(!is_writeable($debug_log_dir) || (file_exists($debug_log_location) && !is_writeable($debug_log_location)))
        {
        exit("FAIL - invalid \$debug_log_location specified in config file: " . $debug_log_location); 
        }        
    }

// Check that the cron process executed within the last 5 days (allows for a window of downtime, for migration, etc.).
$last_cron=strtotime(sql_value("select value from sysvars where name='last_cron'",""));
$diff_days=(time()-$last_cron) / (60 * 60 * 24);
if ($diff_days>5) {exit("FAIL - cron was executed " . round($diff_days,0) . " days ago.");}
exit("OK");
