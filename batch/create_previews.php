#!/usr/bin/php
<?php
include(dirname(__FILE__) . "/../include/db.php");
include_once(dirname(__FILE__) . "/../include/general.php");
include(dirname(__FILE__) . "/../include/image_processing.php");
include(dirname(__FILE__) . "/../include/resource_functions.php");


$ignoremaxsize=false;
$noimage=false;
if ($argc >= 2)
	{
	$validargs=false;
	if ( in_array($argv[1], array('--help', '-help', '-h', '-?')) )
		{
		echo "To clear the lock after a failed run, ";
  		echo "pass in '-clearlock'\n";
		echo "To ignore the maximum preview size configured ($preview_generate_max_file_size), ";
  		echo "pass in '-ignoremaxsize'.\n";
  		exit("Bye!");
		}
	if (in_array('-ignoremaxsize',$argv) )
		{
		$ignoremaxsize=true;
		$validargs=true;
		}
	if (in_array('-noimage',$argv) )
		{
		$noimage=true; # 
		$validargs=true;
		}
	if (in_array('-clearlock',$argv))
		{
		if ( is_process_lock("create_previews") )
			{
			clear_process_lock("create_previews");
			}
		$validargs=true;
		}
	if(!$validargs)
		{
		exit("Unknown argv: " . $argv[1]);
		}
	} 


# Check for a process lock
if (is_process_lock("create_previews")) {exit("Process lock is in place. Deferring.");}
set_process_lock("create_previews");

if (function_exists("pcntl_signal")) {$multiprocess=true;} else {$multiprocess=false;}

// We store the start date.
$global_start_time = microtime(true);

// We define the number of threads.
$max_forks = 3;

$lock_directory = '.';

// We create an array to store children pids.
$children = array();

/**
 * This function clean up the list of children pids.
 * This allow to detect the freeing of a thread slot.
 */
function reap_children()
  {
  global $children;

  $tmp = array();

  foreach ($children as $pid)
    {
    if (pcntl_waitpid($pid, $status, WNOHANG) != $pid)
      {
      array_push($tmp, $pid);
      }
    // else
    // {
    //   echo "[SIGCHLD] child $pid reaped.\n";
    // }
    }

  $children = $tmp;

  return count($tmp);
  } // reap_children()



/**
 * This function is used to process SIGALRM signal.
 * This is usefull when the parent process is killed.
 */
function sigalrm_handler()
  {
  die("[SIGALRM] hang in thumbnails creation ?\n");
  }



/**
 * This function is used to process SIGCHLD signal.
 * 
 */
function sigchld_handler($signal)
  {
  $running_jobs = reap_children();

  // echo "[SIGCHLD] jobs left: $running_jobs\n";

  pcntl_waitpid(-1, $status, WNOHANG);
  }



/**
 * This function is used to process SIGINT signal.
 * 
 */
function sigint_handler()
  {
  //unlink($lock_directory . "/update_daemon.lock");
  die("[SIGINT] exiting.\n");
  }


// We define the functions to use for signal handling.
if ($multiprocess)
	{
	pcntl_signal(SIGALRM, 'sigalrm_handler');
	pcntl_signal(SIGCHLD, 'sigchld_handler');
	}


// We fetch the list of resources to process.
global  $no_preview_extensions;
$exclude="'" . implode("','",$no_preview_extensions) . "'";
$condition="resource.has_image=0 and";
if ($noimage) {$condition="";}
$resources=sql_query("SELECT resource.ref, resource.file_extension, ifnull(resource.preview_attempts,1) preview_attempts, creation_date FROM resource WHERE $condition resource.ref>0 and (resource.preview_attempts<5 or resource.preview_attempts is NULL) and file_extension is not null and length(file_extension)>0 and lower(file_extension) not in (" . $exclude  . ")");

foreach($resources as $resource) // For each resources
  {

  // We wait for a fork emplacement to be freed.
  if ($multiprocess)
	{
	  	while(count($children) >= $max_forks)
	    {
	    // We clean children list.
	    reap_children();
	    sleep(1);
	    }
	}
	
  if (!$multiprocess || count($children) < $max_forks) // Test if we can create a new fork.
    {
    
    // fork
    if (!$multiprocess) {$pid=false;} else {$pid = pcntl_fork();}
    
    if ($pid == -1)
      {
      die("fork failed!\n");
      }
    else if ($pid)
      {
      array_push($children, $pid);
      // echo sprintf("[MASTER] spawned client %d [PID:%d]...\n", count($children), $pid);
      }
    else
      {
      if ($multiprocess)
      	{
	      pcntl_signal(SIGCHLD, SIG_IGN);
	      pcntl_signal(SIGINT, SIG_DFL);
	    }

      // Processing resource.
      echo sprintf("Processing resource id " . $resource['ref'] . " - preview attempt #" . $resource['preview_attempts'] . "\n");

      $start_time = microtime(true);

      // For each fork, we need a new connection to database.
	if ($use_mysqli){
			$db=mysqli_connect($mysql_server,$mysql_username,$mysql_password,$mysql_db);
		} else {
			mysql_connect($mysql_server,$mysql_username,$mysql_password);
			mysql_select_db($mysql_db);
		}

      // If $mysql_charset is defined, we use it
      // else, we use the default charset for mysql connection.
		if(isset($mysql_charset))
			{
			if($mysql_charset)
				{
				if ($use_mysqli){
					global $db;
					mysqli_set_charset($db,$mysql_charset);
					}
				else {
					mysql_set_charset($mysql_charset);
					}
				}
			}
		
		# Below added to catch an issue with previews failing when large video files were taking a long time to copy to StaticSync location
		echo "Created at: " . $resource['creation_date'] . "\nTime now: " . date("Y-m-d H:i:s") . "\n";
		$resourceage = time() - strtotime($resource['creation_date']);		
		if ($resource['preview_attempts']>3 && $resourceage<1000){echo "Just added so may not have finished copying, resetting attempts \n";sql_query("UPDATE resource SET preview attempts=0 WHERE ref='" . $resource['ref'] . "'");continue;} 
		
		#check whether resource already has mp3 preview in which case we set preview_attempts to 5
		if ($resource['file_extension']!="mp3" && file_exists(get_resource_path($resource['ref'],true,"",false,"mp3")))	
			{
			$ref=$resource['ref'];
			echo "Resource already has mp3 preview\n";
			sql_query("update resource set preview_attempts=5 where ref='$ref'");
			}
			
		elseif ($resource['preview_attempts']<5 and $resource['file_extension']!="") 
			{
			if(!empty($resource['file_path'])){$ingested=false;}
			else{$ingested=true;}

			# Increment the preview count.
			sql_query("update resource set preview_attempts=ifnull(preview_attempts,1) + 1 where ref='" . $resource['ref'] . "'");

			create_previews($resource['ref'], false, $resource['file_extension'],false,false,-1,$ignoremaxsize,$ingested);
			echo sprintf("Processed resource %d in %01.2f seconds.\n", $resource['ref'], microtime(true) - $start_time);
			}

	  if ($multiprocess)
	  	{
	      // We exit in order to avoid fork bombing.
	      exit(0);
	    }
      }
    } // Test if we can create a new fork
  } // For each resources

// We wait for all forks to exit.
if ($multiprocess)
	{
	while(count($children))
	  {
	  // We clean children list.
	  reap_children();
	  sleep(1);
	  }
	}
	
echo sprintf("Completed in %01.2f seconds.\n", microtime(true) - $global_start_time);

clear_process_lock("create_previews");
