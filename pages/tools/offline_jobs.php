<?php
include_once dirname(__FILE__) . "/../../include/db.php";
include_once dirname(__FILE__) . "/../../include/general.php";
include_once dirname(__FILE__) . "/../../include/reporting_functions.php";
include_once dirname(__FILE__) . "/../../include/resource_functions.php";
include_once dirname(__FILE__) . "/../../include/search_functions.php";
set_time_limit(0);

if($offline_job_queue)
    {
    # Run offline jobs (may be useful in the event a cron job hasn't yet been created for the new offline_jobs.php)
    $offlinejobs=job_queue_get_jobs("", STATUS_ACTIVE);
    foreach($offlinejobs as $offlinejob)
    	{
    	job_queue_run_job($offlinejob);	
    	}
    }
