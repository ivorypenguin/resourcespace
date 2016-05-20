<?php
/**
 * Database functions, data manipulation functions
 * and generic post/get handling
 * 
 * @author Dan Huby <dan@montala.net> for Oxfam, April 2006
 * @package ResourceSpace
 * @subpackage Includes
 */
#
# db.php - Database functions, data manipulation functions
# and generic post/get handling
#
# Dan Huby (dan@montala.net) for Oxfam, April 2006

# ensure no caching (dynamic site)


// Include core functions:
// Functions used for debugging via System Console
include_once 'debug_functions.php';

// Functions used for activity logging
include_once 'log_functions.php';
include_once 'file_functions.php';


# Switch on output buffering.
ob_start(null,4096);

$pagetime_start = microtime();
$pagetime_start = explode(' ', $pagetime_start);
$pagetime_start = $pagetime_start[1] + $pagetime_start[0];

if (!isset($suppress_headers) || !$suppress_headers)
	{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	}

# Error handling
function errorhandler($errno, $errstr, $errfile, $errline)
    {
    global $baseurl, $pagename, $show_report_bug_link, $email_errors, $show_error_messages;
    if (!error_reporting()) 
        {
        return true;
        }

    $error_note = "Sorry, an error has occurred. ";
    $error_info  = "$errfile line $errline: $errstr";

    if (substr(PHP_SAPI, 0, 3) == 'cli')
        {
        echo $error_note;
        if ($show_error_messages) 
            {
            echo $error_info;
            }
        echo PHP_EOL;
        }
    else
        {
        ?>
        </select></table></table></table>
        <div style="box-shadow: 3px 3px 20px #666;font-family:ubuntu,arial,helvetica,sans-serif;position:absolute;top:150px;left:150px; background-color:white;width:450px;padding:20px;font-size:15px;color:#fff;border-radius:5px;">
            <div style="font-size:30px;background-color:red;border-radius:50%;min-width:35px;float:left;text-align:center;font-weight:bold;">!</div>
            <span style="font-size:30px;color:black;padding:14px;"><?php echo $error_note; ?></span>
            <p style="font-size:14px;color:black;margin-top:20px;">Please <a href="#" onClick="history.go(-1)">go back</a> and try something else.</p>
            <?php 
            if ($show_error_messages) 
                { ?>
                <p style="font-size:14px;color:black;">You can <a href="<?php echo $baseurl?>/pages/check.php">check</a> your installation configuration.</p>
                <hr style="margin-top:20px;">
                <p style="font-size:11px;color:black;"><?php echo htmlspecialchars($error_info); ?></p>
                <?php 
                } ?>
        </div>
        <?php
        }
    if ($email_errors)
        {
        global $email_notify, $email_from, $email_errors_address, $applicationname;
        if ($email_errors_address == "") 
            { 
            $email_errors_address = $email_notify; 
            }
        send_mail($email_errors_address, "$applicationname Error", $error_info, $email_from, $email_from, "", null, "Error Reporting");
        }
    hook('after_error_handler', '', array($errno, $errstr, $errfile, $errline));
    exit();
    }

error_reporting(E_ALL);
set_error_handler("errorhandler");

# *** LOAD CONFIG ***
# Load the default config first, if it exists, so any new settings are present even if missing from config.php
if (file_exists(dirname(__FILE__)."/config.default.php")) {include dirname(__FILE__) . "/config.default.php";}
# Load the real config
if (!file_exists(dirname(__FILE__)."/config.php")) {header ("Location: pages/setup.php" );die(0);}
include (dirname(__FILE__)."/config.php");

if($system_down_redirect && getval('show', '') === '') {
	redirect($baseurl . '/pages/system_down.php?show=true');
}

# Set time limit
set_time_limit($php_time_limit);

# Set the storage directory and URL if not already set.
if (!isset($storagedir)) {$storagedir=dirname(__FILE__)."/../filestore";}
if (!isset($storageurl)) {$storageurl=$baseurl."/filestore";}

$db = null;
function sql_connect() 
    {
    global $use_mysqli,$db,$mysql_server,$mysql_username,$mysql_password,$mysql_db,$mysql_charset,$mysql_force_strict_mode;
	# *** CONNECT TO DATABASE ***
	if ($use_mysqli)
	    {
	    $db=mysqli_connect($mysql_server,$mysql_username,$mysql_password,$mysql_db);
	    } 
	else 
	    {
	    mysql_connect($mysql_server,$mysql_username,$mysql_password);
	    mysql_select_db($mysql_db);
	    }
	    // If $mysql_charset is defined, we use it
	    // else, we use the default charset for mysql connection.
	if(isset($mysql_charset))
	    {
		if($mysql_charset)
		    {
			if ($use_mysqli)
			    {
			    mysqli_set_charset($db,$mysql_charset);
				}
			else 
			    {
				mysql_set_charset($mysql_charset);
			    }
			}
		}
    # Set MySQL Strict Mode (if configured)    
    if ($mysql_force_strict_mode)    
        {
        sql_query("SET SESSION sql_mode='STRICT_ALL_TABLES'",false,-1,true,0);	
        }
    else
        {
        # Determine MySQL version
        $mysql_version = sql_query('select LEFT(VERSION(),3) as ver');
        # Set sql_mode for MySQL 5.7+
        if (version_compare($mysql_version[0]['ver'], '5.6', '>')) 
            {
             $sql_mode_current = sql_query('select @@SESSION.sql_mode');
             $sql_mode_string = implode(" ", $sql_mode_current[0]);
             $sql_mode_array_new = array_diff(explode(",",$sql_mode_string), array("ONLY_FULL_GROUP_BY", "NO_ZERO_IN_DATE", "NO_ZERO_DATE"));
             $sql_mode_string_new = implode (",", $sql_mode_array_new);
             sql_query("SET SESSION sql_mode = '$sql_mode_string_new'");           
             }
        }    
    }
sql_connect();

#if (function_exists("set_magic_quotes_runtime")) {@set_magic_quotes_runtime(0);}

# Automatically set a HTTPS URL if running on the SSL port.
if(isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"]==443)
    {
    $baseurl=str_replace("http://","https://",$baseurl);
    }

# Set a base URL part consisting of the part after the server name, i.e. for absolute URLs and cookie paths.
$baseurl=str_replace(" ","%20",$baseurl);
$bs=explode("/",$baseurl);
$bs=array_slice($bs,3);
$baseurl_short="/" . join("/",$bs) . (count($bs)>0?"/":"");


# statistics
$querycount=0;
$querytime=0;
$querylog=array();

# -----------LANGUAGES AND PLUGINS-------------------------------

# Setup plugin configurations
if ($use_plugins_manager)
	{
	include 'config_functions.php';
	include "plugin_functions.php";
	$legacy_plugins = $plugins; # Make a copy of plugins activated via config.php
	# Check that manually (via config.php) activated plugins are included in the plugins table.
	foreach($plugins as $plugin_name)
		{
		if ($plugin_name!='')
			{
			if (sql_value("SELECT inst_version AS value FROM plugins WHERE name='$plugin_name'",'')=='')
				{
				# Installed plugin isn't marked as installed in the DB.  Update it now.
				# Check if there's a plugin.yaml file to get version and author info.
				$plugin_yaml_path = get_plugin_path($plugin_name) . "/{$plugin_name}.yaml";
				$p_y = get_plugin_yaml($plugin_yaml_path, false);
				# Write what information we have to the plugin DB.
				sql_query("REPLACE plugins(inst_version, author, descrip, name, info_url, update_url, config_url, priority) ".
						  "VALUES ('{$p_y['version']}','{$p_y['author']}','{$p_y['desc']}','{$plugin_name}'," .
						  "'{$p_y['info_url']}','{$p_y['update_url']}','{$p_y['config_url']}','{$p_y['default_priority']}')");
				}
			}
		}
    # Need verbatum queries for this query
    $mysql_vq = $mysql_verbatim_queries;
    $mysql_verbatim_queries = true;
	$active_plugins = sql_query("SELECT name,enabled_groups,config,config_json FROM plugins WHERE inst_version>=0 order by priority");
    $mysql_verbatim_queries = $mysql_vq;

    $active_yaml = array();
    $plugins = array();
    foreach($active_plugins as $plugin)
	    {
	    # Check group access && YAML, only enable for global access at this point
	    $plugin_yaml_path = get_plugin_path($plugin["name"])."/".$plugin["name"].".yaml";
	    $py = get_plugin_yaml($plugin_yaml_path, false);
	    array_push($active_yaml,$py);
	    if ($plugin['enabled_groups']=='' && !isset($py["userpreferencegroup"]))
		    {
		    # Add to the plugins array if not already present which is what we are working with
		    $plugins[]=$plugin['name'];
		    }
	    }

	for ($n=count($active_plugins)-1;$n>=0;$n--)
		{
		$plugin=$active_plugins[$n];
		if ($plugin['enabled_groups']=='' && !isset($active_yaml[$n]["userpreferencegroup"]))
			{
			include_plugin_config($plugin['name'], $plugin['config'], $plugin['config_json']);
			}
		}
	}
else
	{
	for ($n=count($plugins)-1;$n>=0;$n--)
		{
		include_plugin_config($plugins[$n]);
		}
	}

// Load system wide config options from database
process_config_options();

# Include the appropriate language file
$pagename=safe_file_name(str_replace(".php","",pagename()));

// Allow plugins to set $language from config as we cannot run hooks at this point
if(!isset($language))
	{
	$language = setLanguage();
	}

# Fix due to rename of US English language file
if (isset($language) && $language=="us") {$language="en-US";}

# Always include the english pack (in case items have not yet been translated)
include dirname(__FILE__)."/../languages/en.php";
if ($language!="en")
	{
	if (substr($language, 2, 1)=='-' && substr($language, 0, 2)!='en')
	@include dirname(__FILE__)."/../languages/" . safe_file_name(substr($language, 0, 2)) . ".php";
	@include dirname(__FILE__)."/../languages/" . safe_file_name($language) . ".php";
	}

# Register all plugins
for ($n=0;$n<count($plugins);$n++)
	{
	register_plugin($plugins[$n]);
	hook("afterregisterplugin");
	}

# Register their languages in reverse order
for ($n=count($plugins)-1;$n>=0;$n--)
	{
	register_plugin_language($plugins[$n]);
	}
global $suppress_headers;
# Set character set.
if (($pagename!="download") && ($pagename!="graph") && !$suppress_headers) {header("Content-Type: text/html; charset=UTF-8");} // Make sure we're using UTF-8.
#------------------------------------------------------


# Pre-load all text for this page.
$pagefilter="AND (page = '" . $pagename . "' OR page = 'all' OR page = '' " .  (($pagename=="dash_tile")?" OR page = 'home'":"") . ")";
if ($pagename=="admin_content") {$pagefilter="";} # Special case for the team content manager. Pull in all content from all pages so it's all overridden.

$site_text=array();
$results=sql_query("select language,name,text from site_text where (page='$pagename' or page='all' or page='') and (specific_to_group is null or specific_to_group=0)");
for ($n=0;$n<count($results);$n++) {$site_text[$results[$n]["language"] . "-" . $results[$n]["name"]]=$results[$n]["text"];}

$query = sprintf('
		SELECT `name`,
		       `text`,
		       `page`,
		       `language`, specific_to_group 
		  FROM site_text
		 WHERE (`language` = "%s" OR `language` = "%s")
		   %s  #pagefilter
		   AND (specific_to_group IS NULL OR specific_to_group = 0);
	',
	escape_check($language),
	escape_check($defaultlanguage),
	$pagefilter
);
$results=sql_query($query);

// Go through the results twice, setting the default language first, then repeat for the user language so we can override the default with any language specific entries
for ($n=0;$n<count($results);$n++) 
	{
	if($results[$n]["language"]!=$defaultlanguage){continue;}
	if ($results[$n]["page"]=="") 
		{
		$lang[$results[$n]["name"]]=$results[$n]["text"];
		} 
	else 
		{
		$lang[$results[$n]["page"] . "__" . $results[$n]["name"]]=$results[$n]["text"];
		}
	}
for ($n=0;$n<count($results);$n++) 
	{
	if($results[$n]["language"]!=$language){continue;}
	if ($results[$n]["page"]=="") 
		{
		$lang[$results[$n]["name"]]=$results[$n]["text"];
		} 
	else 
		{
		$lang[$results[$n]["page"] . "__" . $results[$n]["name"]]=$results[$n]["text"];
		}
	}
	
# Blank the header insert
$headerinsert="";

# Initialise hook for plugins
hook("initialise");

# Load the language specific stemming algorithm, if one exists
$stemming_file=dirname(__FILE__) . "/../lib/stemming/" . safe_file_name($defaultlanguage) . ".php"; # Important - use the system default language NOT the user selected language, because the stemmer must use the system defaults when indexing for all users.
if (file_exists($stemming_file)) {include ($stemming_file);}

# Global hook cache and related hits counter
$hook_cache = array();
$hook_cache_hits = 0;

# Load the sysvars into an array. Useful so we can check migration status etc.
$systemvars = sql_query("SELECT name, value FROM sysvars");
$sysvars = array();
foreach($systemvars as $systemvar)
	{
	$sysvars[$systemvar["name"]] = $systemvar["value"];
	}

function hook($name,$pagename="",$params=array(),$last_hook_value_wins=false)
	{
	# Plugin architecture.  Look for hooks with this name (and corresponding page, if applicable) and run them sequentially.
	# Utilises a cache for significantly better performance.  
	# Enable $draw_performance_footer in config.php to see stats.

	# Allow modifications to the hook itself:
	if(function_exists("hook_modifier") && !hook_modifier($name, $pagename, $params)) return;

	global $hook_cache;
	if($pagename == '')
		{
		global $pagename;
		}
	
	# the index name for the $hook_cache
	$hook_cache_index = $name . "|" . $pagename;
	
	# we have already processed this hook name and page combination before so return from cache
	if (isset($hook_cache[$hook_cache_index]))
		{
		# increment stats
		global $hook_cache_hits;
		$hook_cache_hits++;

		unset($GLOBALS['hook_return_value']);

		// we use $GLOBALS['hook_return_value'] so that hooks can directly modify the overall return value

		foreach ($hook_cache[$hook_cache_index] as $function)
			{
			$function_return_value = call_user_func_array($function, $params);

			if ($function_return_value === null)
				{
				continue;	// the function did not return a value so skip to next hook call
				}

			if (!$last_hook_value_wins &&
				isset($GLOBALS['hook_return_value']) &&
				(gettype($GLOBALS['hook_return_value']) == gettype($function_return_value)) &&
				(is_array($function_return_value) || is_string($function_return_value) || is_bool($function_return_value)))
				{
				if (is_array($function_return_value))
					{
					// We merge the cached result with the new result from the plugin and remove any duplicates
					// Note: in custom plugins developers should work with the full array (ie. superset) rather than just a sub-set of the array.
					//       If your plugin needs to know if the array has been modified previously by other plugins use the global variable "hook_return_value"
					$GLOBALS['hook_return_value'] = array_values(array_unique(array_merge_recursive($GLOBALS['hook_return_value'], $function_return_value), SORT_REGULAR));
					}
				elseif (is_string($function_return_value))
					{
					$GLOBALS['hook_return_value'] .= $function_return_value;		// appends string
					}
				elseif (is_bool($function_return_value))
					{
					$GLOBALS['hook_return_value'] = $GLOBALS['hook_return_value'] || $function_return_value;		// boolean OR
					}
				}
			else
				{
				$GLOBALS['hook_return_value'] = $function_return_value;
				}
			}

		return (isset($GLOBALS['hook_return_value']) ? $GLOBALS['hook_return_value'] : false);
		}

	# we have not encountered this hook and page combination before so go add it
	global $plugins;
	
	# this will hold all of the functions to call when hitting this hook name and page combination
	$function_list = array();
	
	for ($n=0;$n<count($plugins);$n++)
		{	
		# "All" hooks
		$function="Hook" . ucfirst($plugins[$n]) . "All" . ucfirst($name);		
		if (function_exists($function)) 
			{			
			$function_list[]=$function;
			}
		else 
			{
			# Specific hook	
			$function="Hook" . ucfirst($plugins[$n]) . ucfirst($pagename) . ucfirst($name);
			if (function_exists($function)) 
				{
				$function_list[]=$function;
				}
			}
		}	
	
	# add the function list to cache
	$hook_cache[$hook_cache_index] = $function_list;

	# do a callback to run the function(s) - this will not cause an infinite loop as we have just added to cache for execution.
	return hook($name, $pagename, $params, $last_hook_value_wins);
	}

# Indicate that from now on we want to group together DML statements into one transaction (faster as only one commit at end).
function db_begin_transaction()
	{
	global $db,$use_mysqli;
	if ($use_mysqli && function_exists('mysqli_begin_transaction'))
		{
		mysqli_begin_transaction($db);
		}
	}

# Tell the database to commit the current transaction.
function db_end_transaction()
	{
	global $db,$use_mysqli;
	if ($use_mysqli && function_exists('mysqli_commit'))
		{
		mysqli_commit($db);
		}
	}

function sql_query($sql,$cache=false,$fetchrows=-1,$dbstruct=true, $logthis=2, $reconnect=true)
    {
    # sql_query(sql) - execute a query and return the results as an array.
	# Database functions are wrapped in this way so supporting a database server other than MySQL is 
	# easier.
	# $cache is not used at this time - it was intended for disk based results caching which may be added in the future.
    # If $fetchrows is set we don't have to loop through all the returned rows. We
    # just fetch $fetchrows row but pad the array to the full result set size with empty values.
    # This has been added retroactively to support large result sets, yet a pager can work as if a full
    # result set has been returned as an array (as it was working previously).
	# $logthis parameter is only relevant if $mysql_log_transactions is set.  0=don't log, 1=always log, 2=detect logging - i.e. SELECT statements will not be logged
    global $db,$config_show_performance_footer,$debug_log,$debug_log_override,$mysql_verbatim_queries,$use_mysqli, $mysql_log_transactions;
    
	if (!isset($debug_log_override))
		{
		check_debug_log_override();
		}
	
    if ($config_show_performance_footer)
    	{
    	# Stats
    	# Start measuring query time
    	$time_start = microtime(true);
   	    global $querycount;
		$querycount++;
    	}
    	
    if ($debug_log || $debug_log_override) 
		{
		debug("SQL: " . $sql);
		}
	
    if($mysql_log_transactions && !($logthis==0))
    	{	
		global $mysql_log_location, $lang;
		$requirelog=true;
		if($logthis==2)
			{
			// Ignore any SELECTs if the decision to log has not been indicated by function call, 	
			if(strtoupper(substr(trim($sql),0,6))=="SELECT")
				{$requirelog=false;}
			}
			
		if($logthis==1 || $requirelog)
			{
			# Log this to a transaction log file so it can be replayed after restoring database backup
			$mysql_log_dir = dirname($mysql_log_location);
			if (!is_dir($mysql_log_dir))
				{
				@mkdir($mysql_log_dir, 0333, true);
				if (!is_dir($mysql_log_dir))
					{exit("ERROR: Unable to create  folder for \$mysql_log_location specified in config file: " . $mysql_log_location);}
				}	
			
			if(!file_exists($mysql_log_location))
				{
				global $mysql_db;
				$mlf=@fopen($mysql_log_location,"wb");
				@fwrite($mlf,"USE " . $mysql_db . ";\r\n");
				if(!file_exists($mysql_log_location))
					{exit("ERROR: Invalid \$mysql_log_location specified in config file: " . $mysql_log_location);}
				// Set the permissions if we can to prevent browser access (will not work on Windows)
				chmod($mysql_log_location,0333);
				}
			
			$mlf=@fopen($mysql_log_location,"ab");
			fwrite($mlf,"/* " . date("Y-m-d H:i:s") . " */ " .  $sql . ";\n"); // Append the ';' so the file can be used to replay the changes
			fclose ($mlf);
			}
		
		}
    
    # Execute query    
	$result=$use_mysqli ? mysqli_query($db,$sql) : mysql_query($sql);
	
    if ($config_show_performance_footer){
    	# Stats
   		# Log performance data		
		global $querytime,$querylog;
		
		$time_total=(microtime(true) - $time_start);
		if (isset($querylog[$sql]))
			{
			$querylog[$sql]['dupe']=$querylog[$sql]['dupe']+1;
			$querylog[$sql]['time']=$querylog[$sql]['time']+$time_total;
			}
		else
			{
			$querylog[$sql]['dupe']=1;
			$querylog[$sql]['time']=$time_total;
			}	
		$querytime += $time_total;
	}
	
	$error=$use_mysqli ? mysqli_error($db) : mysql_error();	
	
	$return_rows=array();
    if ($error!="")
        {
        if ($error=="Server shutdown in progress")
        	{
			echo "<span class=error>Sorry, but this query would return too many results. Please try refining your query by adding addition keywords or search parameters.<!--$sql--></span>";        	
        	}
        elseif (substr($error,0,15)=="Too many tables")
        	{
			echo "<span class=error>Sorry, but this query contained too many keywords. Please try refining your query by removing any surplus keywords or search parameters.<!--$sql--></span>";        	
        	}
        elseif (strpos($error,"has gone away")!==false && $reconnect)
			{
			# SQL server connection has timed out or been killed. Try to reconnect and run query again.
			sql_connect();
			return sql_query($sql,$cache,$fetchrows,$dbstruct,$logthis,false);
			exit();
			}
        else
        	{
        	# Check that all database tables and columns exist using the files in the 'dbstruct' folder.
        	if ($dbstruct) # should we do this?
        		{
				check_db_structs();
        		
        		# Try again (no dbstruct this time to prevent an endless loop)
        		return sql_query($sql,$cache,$fetchrows,false,$reconnect);
        		exit();
        		}
        	
	        errorhandler("N/A", $error . "<br/><br/>" . $sql, "(database)", "N/A");
	        }
        exit;
        }
    elseif ($result===true)
        {        
		return $return_rows;		// no result set, (query was insert, update etc.) - simply return empty array.
        }
	
	$return_row_count=0;	
	while (($fetchrows==-1 || $return_row_count<$fetchrows) && (($use_mysqli && ($result_row=mysqli_fetch_assoc($result))) || (!$use_mysqli && ($result_row=mysql_fetch_assoc($result)))))
		{
		if ($mysql_verbatim_queries)		// no need to do clean up on every cell
			{
			$return_rows[$return_row_count]=$result_row;		// simply dump the entire row into the return results set
			}
		else
			{
			while (list($name,$value)=each($result_row))		// we need to clean up each cell
				{
				$return_rows[$return_row_count][$name]=str_replace("\\","",stripslashes($value));		// iterate through each cell cleaning up
				}
			}
		$return_row_count++;
		}
	
	if ($fetchrows==-1)		// we do not care about the number of rows returned so get out of here
		{
		return $return_rows;
		}
	
	# If we haven't returned all the rows ($fetchrows isn't -1) then we need to fill the array so the count
	# is still correct (even though these rows won't be shown).
	
	$query_returned_row_count=$use_mysqli ? mysqli_num_rows($result) : mysql_num_rows($result);		// get the number of rows returned from the query
	
	if ($return_row_count<$query_returned_row_count)
		{
		$return_rows=array_pad($return_rows,$query_returned_row_count,0);		// if short then pad out
		}
	
	return $return_rows;        
    }
	
	
function sql_value($query,$default)
    {
    # return a single value from a database query, or the default if no rows
    # The value returned must have the column name aliased to 'value'
    $result=sql_query($query,false,-1,true,0); // This is a select so we don't need to log this in the mysql log
    if (count($result)==0) {return $default;} else {return $result[0]["value"];}
    }

function sql_array($query)
	{
	# Like sql_value() but returns an array of all values found.
    # The value returned must have the column name aliased to 'value'
	$return=array();
    $result=sql_query($query,false,-1,true,0); // This is a select so we don't need to log this in the mysql log
    for ($n=0;$n<count($result);$n++)
    	{
    	$return[]=$result[$n]["value"];
    	}
    return $return;
	}

function sql_insert_id()
	{
	# Return last inserted ID (abstraction)
	global $use_mysqli;
	if ($use_mysqli){
		global $db;
		return mysqli_insert_id($db);
	}
	else { 
		return mysql_insert_id();
	}
	}

function check_db_structs($verbose=false)
	{
	CheckDBStruct("dbstruct",$verbose);
	global $plugins;
	for ($n=0;$n<count($plugins);$n++)
		{
		CheckDBStruct("plugins/" . $plugins[$n] . "/dbstruct");
		}
	hook("checkdbstruct");
	}

function CheckDBStruct($path,$verbose=false)
	{
	# Check the database structure against the text files stored in $path.
	# Add tables / columns / data / indices as necessary.
	global $mysql_db, $resource_field_column_limit;
	
	if (!file_exists($path)){
		# Check for path
		$path=dirname(__FILE__) . "/../" . $path; # Make sure this works when called from non-root files..
		if (!file_exists($path)) {return false;}
	}
	
	# Tables first.
	# Load existing tables list
	$ts=sql_query("show tables",false,-1,false);
	$tables=array();
	for ($n=0;$n<count($ts);$n++)
		{
		$tables[]=$ts[$n]["Tables_in_" . $mysql_db];
		}
	$dh=opendir($path);
	while (($file = readdir($dh)) !== false)
		{
		if (substr($file,0,6)=="table_")
			{
			$table=str_replace(".txt","",substr($file,6));
			
			# Check table exists
			if (!in_array($table,$tables))
				{
				# Create Table
				$sql="";
				$f=fopen($path . "/" . $file,"r");
				$hasPrimaryKey = false;
				$pk_sql = "PRIMARY KEY (";
				while (($col = fgetcsv($f,5000)) !== false)
				{
					if ($sql.="") {$sql.=", ";}
					$sql.=$col[0] . " " . str_replace("§",",",$col[1]);
					if ($col[4]!="") {$sql.=" default " . $col[4];}
					if ($col[3]=="PRI")
					{
						if($hasPrimaryKey)
						{
							$pk_sql .= ",";
						}
						$pk_sql.=$col[0];
						$hasPrimaryKey = true;
					}
					if ($col[5]=="auto_increment") {$sql.=" auto_increment ";}
				}
				$pk_sql .= ")";
				if($hasPrimaryKey)
				{
					$sql.="," . $pk_sql;
				}
				debug($sql);

				# Verbose mode, used for better output from the test script.
				if ($verbose) {echo "$table ";ob_flush();}
				
				sql_query("create table $table ($sql)",false,-1,false);
				
				# Add initial data
				$data=str_replace("table_","data_",$file);
				if (file_exists($path . "/" . $data))
					{
					$f=fopen($path . "/" . $data,"r");
					while (($row = fgetcsv($f,5000)) !== false)
						{
						# Escape values
						for ($n=0;$n<count($row);$n++)
							{
							$row[$n]=escape_check($row[$n]);
							$row[$n]="'" . $row[$n] . "'";
							if ($row[$n]=="''") {$row[$n]="null";}
							}
						sql_query("insert into $table values (" . join (",",$row) . ")",false,-1,false);
						}
					}
				}
			else
				{
				# Table already exists, so check all columns exist
				
				# Load existing table definition
				$existing=sql_query("describe $table",false,-1,false);

				##########
				# Copy needed resource_data into resource for search displays
				if ($table=="resource"){
					$joins=get_resource_table_joins();
					for ($m=0;$m<count($joins);$m++){
						
						# Look for this column in the existing columns.	
						$found=false;

						for ($n=0;$n<count($existing);$n++)
							{
							if ("field".$joins[$m]==$existing[$n]["Field"]) {$found=true;}
							}
						if (!$found)
							{
							# Add this column.
							$sql="alter table $table add column ";
							$sql.="field".$joins[$m] . " VARCHAR(" . $resource_field_column_limit . ")";
							sql_query($sql,false,-1,false);
							$values=sql_query("select resource,value from resource_data where resource_type_field=$joins[$m]",false,-1,false);
	
							for($x=0;$x<count($values);$x++){
								$value=$values[$x]['value'];
								$resource=$values[$x]['resource'];
								sql_query("update resource set field$joins[$m]='".escape_check($value)."' where ref=$resource",false,-1,false);	
						    }	
						}
					}	
				}		
				##########
				
				##########
				## RS-specific mod:
				# add theme columns to collection table as needed.
				global $theme_category_levels;
				if ($table=="collection"){
					for ($m=1;$m<=$theme_category_levels;$m++){
						if ($m==1){$themeindex="";}else{$themeindex=$m;}
						# Look for this column in the existing columns.	
						$found=false;

						for ($n=0;$n<count($existing);$n++)
							{
							if ("theme".$themeindex==$existing[$n]["Field"]) {$found=true;}
							}
						if (!$found)
							{
							# Add this column.
							$sql="alter table $table add column ";
							$sql.="theme".$themeindex . " VARCHAR(100)";
							sql_query($sql,false,-1,false);

						}
					}	
				}		
				
				##########				
								
				if (file_exists($path . "/" . $file))
					{
					$f=fopen($path . "/" . $file,"r");
					while (($col = fgetcsv($f,5000)) !== false)
						{
						if (count($col)> 1)
							{
							# Look for this column in the existing columns.
							$found=false;
							for ($n=0;$n<count($existing);$n++)
								{
								if ($existing[$n]["Field"]==$col[0])
									{
									$found=true;
									$existingcoltype=strtoupper($existing[$n]["Type"]);
									$basecoltype=strtoupper(str_replace("§",",",$col[1]));									
									# Check the column is of the correct type
									preg_match('/\s*(\w+)\s*\((\d+)\)/i',$basecoltype,$matchbase);
									preg_match('/\s*(\w+)\s*\((\d+)\)/i',$existingcoltype,$matchexisting);
									// Checks added so that we don't trim off data if a varchar size has been increased manually or by a plugin. 
									// - If column is of same type but smaller number, update
									// - If target column is of type text, update
									
									if	(
										(count($matchbase)==3 && count($matchexisting)==3 && $matchbase[1] == $matchexisting[1] && $matchbase[2] > $matchexisting[2])
										 ||
										(stripos($basecoltype,"text")!==false && stripos($existingcoltype,"text")===false)
										||
										(stripos($basecoltype,"BIGINT")!==false && stripos($existingcoltype,"INT")!==false)
									       )
										{        
										debug("DBSTRUCT - updating column " . $col[0] . " in table " . $table . " from " . $existing[$n]["Type"] . " to " . str_replace("§",",",$col[1]) );
										// Update the column type
										sql_query("alter table $table modify `" .$col[0] . "` " .  $col[1]);       
										}																				
									}							
								}
							if (!$found)
									{
									# Add this column.										
									$sql="alter table $table add column ";
									$sql.=$col[0] . " " . str_replace("§",",",$col[1]); # Allow commas to be entered using '§', necessary for a type such as decimal(2,10)
									if ($col[4]!="") {$sql.=" default " . $col[4];}
									if ($col[3]=="PRI") {$sql.=" primary key";}
									if ($col[5]=="auto_increment") {$sql.=" auto_increment ";}
									sql_query($sql,false,-1,false);
									}	
							}
						}
					}
				}
				
			# Check all indices exist
			# Load existing indexes
			$existing=sql_query("show index from $table",false,-1,false);
					
			$file=str_replace("table_","index_",$file);
			if (file_exists($path . "/" . $file))
				{
				$done=array(); # List of indices already processed.
				$f=fopen($path . "/" . $file,"r");
				while (($col = fgetcsv($f,5000)) !== false)
					{
					# Look for this index in the existing indices.
					$found=false;
					for ($n=0;$n<count($existing);$n++)
						{
						if ($existing[$n]["Key_name"]==$col[2]) {$found=true;}
						}
					if (!$found && !in_array($col[2],$done))
						{
						# Add this index.
						
						# Fetch list of columns for this index
						$cols=array();
						$f2=fopen($path . "/" . $file,"r");
						while (($col2 = fgetcsv($f2,5000)) !== false)
							{
							if ($col2[2]==$col[2]) {$cols[]=$col2[4];}
							}
						
						$sql="create index " . $col[2] . " on $table (" . join(",",$cols) . ")";
						sql_query($sql,false,-1,false);
						$done[]=$col[2];
						}
					}
				}
			}
		}
	}
	
function getval($val,$default,$force_numeric=false)
    {
    # return a value from POST, GET or COOKIE (in that order), or $default if none set
    if (array_key_exists($val,$_POST)) {return ($force_numeric && !is_numeric($_POST[$val])?$default:$_POST[$val]);}
    if (array_key_exists($val,$_GET)) {return ($force_numeric && !is_numeric($_GET[$val])?$default:$_GET[$val]);}
    if (array_key_exists($val,$_COOKIE)) {return ($force_numeric && !is_numeric($_COOKIE[$val])?$default:$_COOKIE[$val]);}
    return $default;
    }

function getvalescaped($val,$default,$force_numeric=false)
    {    
    # return a value from get/post, escaped, SQL-safe and XSS-free
    $value=getval($val,$default,$force_numeric);
    if (is_array($value))
        {
        foreach ($value as &$item)
            {
            $item=escape_check($item); 
            if (strpos(strtolower($item),"<script")!==false) return $default;
            }
        }
    else
        {
        $value=escape_check($value);
        if (strpos(strtolower($value),"<script")!==false) {return $default;}
        }
    return $value;
    }
    
function getuid()
    {
    # generate a unique ID
    return strtr(escape_check(microtime() . " " . $_SERVER["REMOTE_ADDR"]),". ","--");
    }

function escape_check($text) #only escape a string if we need to, to prevent escaping an already escaped string
    {
    global $db,$use_mysqli;
    if ($use_mysqli)
        {
        $text=mysqli_real_escape_string($db,$text);
        }
    else 
        {
        $text=mysql_real_escape_string($text);
        }
    # turn all \\' into \'
    while (!(strpos($text,"\\\\'")===false))
        {
        $text=str_replace("\\\\'","\\'",$text);
        }

    # Remove any backslashes that are not being used to escape single quotes.
    $text=str_replace("\\'","{bs}'",$text);
    $text=str_replace("\\n","{bs}n",$text);
    $text=str_replace("\\r","{bs}r",$text);

	if (!$GLOBALS['mysql_verbatim_queries'])
		$text=str_replace("\\","",$text);
    $text=str_replace("{bs}'","\\'",$text);            
    $text=str_replace("{bs}n","\\n",$text);            
    $text=str_replace("{bs}r","\\r",$text);  
                      
    return $text;
    }

function unescape($text) 
    {
    // for comparing escape_checked strings against mysql content because	
    // just doing $text=str_replace("\\","",$text);	does not undo escape_check

    # Remove any backslashes that are not being used to escape single quotes.
    $text=str_replace("\\'","\'",$text);
    $text=str_replace("\\n","\n",$text);
    $text=str_replace("\\r","\r",$text);
    $text=str_replace("\\","",$text);    
    

    return $text;
    }


if (!function_exists("nicedate")) {
function nicedate($date,$time=false,$wordy=true)
	{
	# format a MySQL ISO date
	# Always use the 'wordy' style from now on as this works better internationally.
	global $lang,$date_d_m_y,$date_yyyy;
	$y = substr($date,0,4);
	if(!$date_yyyy)
	{
		$y = substr($y, 2, 2);
	}
	if ( $y=="" ) return "-";
	$m = @$lang["months"][substr($date,5,2)-1];
	if ($m=="") return $y;
	$d = substr($date,8, 2);
	if ($d=="" || $d=="00") return $m . " " . $y;
	$t = $time ? (" @ "  . substr($date,11,5)) : "";
	if($date_d_m_y)
		{
		return $d . " " . $m . " " . $y . $t;
		}
	else{
		return $m . " " . $d . " " . $y . $t;
		}
	}	
}

function redirect($url)
	{
	# Redirect to the provided URL using a HTTP header Location directive.
	global $baseurl,$baseurl_short;
	if (getval("ajax","")!="")
		{
		# When redirecting from an AJAX loaded page, forward the AJAX parameter automatically so headers and footers are removed.	
		if (strpos($url,"?")!==false)
			{
			$url.="&ajax=true";
			}
		else
			{
			$url.="?ajax=true";
			}
		}
	
	if (substr($url,0,1)=="/")
		{
		# redirect to an absolute URL
		header ("Location: " . str_replace('/[\\\/]/D',"",$baseurl) . str_replace($baseurl_short,"/",$url));
		}
	else
		{	
		if(strpos($url,$baseurl)!==false)
			{
			// exit($url);	
			// Base url has already been added
			header ("Location: " . $url);	
			exit();
			}

		# redirect to a relative URL
		header ("Location: " . $baseurl . "/" . $url);
		}
	exit();
	}

function http_get_preferred_language($strict_mode=false)
	{
	global $languages;

	if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		return null;

	$accepted_languages=preg_split('/,\s*/',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$current_lang=false;
	$current_quality=0;
	$language_map = array();
	foreach ($languages as $key => $value)
		$language_map[strtolower($key)] = $key;

	foreach ($accepted_languages as $accepted_language)
		{
		$res=preg_match('/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i',$accepted_language,$matches);
		if (!$res)
			continue;

		$lang_code=explode('-',$matches[1]);

		// Use specified quality, if any
		if (isset($matches[2]))
			$lang_quality=(float)$matches[2];
		else
			$lang_quality=1.0;

		while (count($lang_code))
			{
			$short=strtolower(join('-', $lang_code));
			if (array_key_exists($short, $language_map) && $lang_quality > $current_quality)
				{
				$current_lang=$language_map[$short];
				$current_quality=$lang_quality;
				}

			if ($strict_mode)
				break;

			array_pop($lang_code);
			}
		}

        return $current_lang;
	}

function setLanguage()
	{
	global $browser_language,$disable_languages,$defaultlanguage,$languages,$global_cookies,$baseurl_short;
	$language="";
	if (isset($_GET["language_set"]))
	    {
	    $language=$_GET["language_set"];
	    if(array_key_exists($language,$languages)) 
			{
		    # Cannot use the general.php: rs_setcookie() here since general may not have been included.
		    if ($global_cookies)
		        {
		        # Remove previously set cookies to avoid clashes
		        setcookie("language", "", time() - 3600, $baseurl_short . "pages/", '', false, true);
		        setcookie("language", "", time() - 3600, $baseurl_short, '', false, true);
		        # Set new cookie
		        setcookie("language", $language, time() + (3600*24*1000), "/", '', false, true);
		        }
		    else
		        {
		        # Set new cookie
		        setcookie("language", $language, time() + (3600*24*1000));
		        setcookie("language", $language, time() + (3600*24*1000), $baseurl_short . "pages/", '', false, true);
		        }
		    return $language;
		    }
		    else{$language="";}
	    }
	if (isset($_GET["language"]) && array_key_exists($_GET["language"],$languages)) {return $_GET["language"];}	
	if (isset($_POST["language"]) && array_key_exists($_POST["language"],$languages)) {return $_POST["language"];}
	if (isset($_COOKIE["language"]) && array_key_exists($_COOKIE["language"],$languages)) {return $_COOKIE["language"];}

	if(!$disable_languages && $browser_language && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
		$language = http_get_preferred_language();
		if(!empty($language) && array_key_exists($language,$languages)){return $language;}
		} 
	if(($disable_languages || $language ==="") && isset($defaultlanguage)) {return $defaultlanguage;}
	# Final case.
	return 'en';
	}

function checkperm($perm)
    {
    # check that the user has the $perm permission
    global $userpermissions;
    if (!(isset($userpermissions))) {return false;}
    if (in_array($perm,$userpermissions)) {return true;} else {return false;}
    }

// Check if user is allowed to edit user with passed reference
function checkperm_user_edit($user)
	{
	if (!checkperm('u'))    // does not have edit user permission
		{
		return false;
		}
	if (!isset($user['usergroup']))		// allow for passing of user array or user ref to this function.
		{
		$user=get_user($user);
		}
	$editusergroup=$user['usergroup'];
	if (!checkperm('U') || $editusergroup == '')    // no user editing restriction, or is not defined so return true
		{
		return true;
		}
	global $U_perm_strict, $usergroup;
	// Get all the groups that the logged in user can manage 
	$validgroups = sql_array("SELECT `ref` AS  'value' FROM `usergroup` WHERE " .
		($U_perm_strict ? "FIND_IN_SET('{$usergroup}',parent)" : "(`ref`='{$usergroup}' OR FIND_IN_SET('{$usergroup}',parent))")
	);
	
	// Return true if the target user we are checking is in one of the valid groups
	return (in_array($editusergroup, $validgroups));
	}

function pagename()
	{
	$name=safe_file_name(getvalescaped('pagename', ''));
	if (!empty($name))
		return $name;
	$url=str_replace("\\","/", $_SERVER["PHP_SELF"]); // To work with Windows command line scripts
	$urlparts=explode("/",$url);
   $url=$urlparts[count($urlparts)-1];
    return escape_check($url);
    }
    
function text($name)
	{
	global $site_text,$pagename,$language,$languages,$usergroup,$lang;
	
	# Look for the site content in the language strings. These will already be overridden with site content if present.
	$key=$pagename . "__" . $name;	
	if (array_key_exists($key,$lang)) {return $lang[$key];}
	else if(array_key_exists("all__" . $name,$lang)) {return $lang["all__" . $name];}

	/*
		Old method, commented for reference; look directly in the site content table.
	
	# Returns site text with name $name, or failing that returns dummy text.
	global $site_text,$pagename,$language,$languages,$usergroup;
	if (array_key_exists($language . "-" . $name,$site_text)) {return $site_text[$language . "-" .$name];} 
	
	# Can't find the language key? Look for it in other languages.
	reset($languages);foreach ($languages as $key=>$value)
		{
		if (array_key_exists($key . "-" . $name,$site_text)) {return $site_text[$key . "-" . $name];} 		
		}
	if (!array_key_exists('en', $languages))
		{
		if (array_key_exists("en-" . $name,$site_text)) {return $site_text["en-" . $name];}
		}
	*/
	
	return "";
	}
    
function get_section_list($page)
	{
	return sql_array("select distinct name value from site_text where page='$page' and name<>'introtext' order by name");
	}

function resolve_user_agent($agent)
    {
    if ($agent=="") {return "-";}
    $agent=strtolower($agent);
    $bmatches=array( # Note - order is important - first come first matched
                    "firefox"=>"Firefox",
                    "chrome"=>"Chrome",
                    "opera"=>"Opera",
                    "safari"=>"Safari",
                    "applewebkit"=>"Safari",
                    "msie 3."=>"IE3",
                    "msie 4."=>"IE4",
                    "msie 5.5"=>"IE5.5",
                    "msie 5."=>"IE5",
                    "msie 6."=>"IE6",
                    "msie 7."=>"IE7",
                    "msie 8."=>"IE8",
                    "msie 9."=>"IE9",
                    "msie 10."=>"IE10",
                    "trident/7.0"=>"IE11",
		    "msie"=>"IE",
		    "trident"=>"IE",
                    "netscape"=>"Netscape",
                    "mozilla"=>"Mozilla"
                    #catch all for mozilla references not specified above
                    );
    $osmatches=array(
                    "iphone"=>"iPhone",                    
                    "nt 6.1"=>"Windows 7",
                    "nt 6.0"=>"Vista",
                    "nt 5.2"=>"WS2003",
                    "nt 5.1"=>"XP",
                    "nt 5.0"=>"2000",
                    "nt 4.0"=>"NT4",
                    "windows 98"=>"98",
                    "linux"=>"Linux",
                    "freebsd"=>"FreeBSD",
                    "os x"=>"OS X",
                    "mac_powerpc"=>"Mac",
                    "sunos"=>"Sun",
                    "psp"=>"Sony PSP",
                    "api"=>"Api Client"
                    );
    $b="???";$os="???";
    foreach($bmatches as $key => $value)
        {if (!strpos($agent,$key)===false) {$b=$value;break;}}
    foreach($osmatches as $key => $value)
        {if (!strpos($agent,$key)===false) {$os=$value;break;}}
    return $os . " / " . $b;
    }
    



function get_ip()
	{
	global $ip_forwarded_for;
	
	if ($ip_forwarded_for)
		{
		# Attempt to read Apache forwarding header instead.
		$headers = @apache_request_headers();
		if (@array_key_exists('X-Forwarded-For', $headers)) {return $headers["X-Forwarded-For"];}
		}
		
	# Returns the IP address for the current user.
	if (array_key_exists("REMOTE_ADDR",$_SERVER)) {return $_SERVER["REMOTE_ADDR"];}


	# Can't find an IP address.
	return "???";
	}


if (!function_exists("daily_stat")){
function daily_stat($activity_type,$object_ref)
	{
	global $disable_daily_stat;if($disable_daily_stat===true){return;}  //can be used to speed up heavy scripts	when stats are less important
	# Update the daily statistics after a loggable event.
	# the daily_stat table contains a counter for each 'activity type' (i.e. download) for each object (i.e. resource)
	# per day.
	$date=getdate();$year=$date["year"];$month=$date["mon"];$day=$date["mday"];
	

	# Set object ref to zero if not set.
    
	if ($object_ref=="") {$object_ref=0;}

    
	# Find usergroup
	global $usergroup;
	if (!isset($usergroup)) {$usergroup=0;}
	
	# External or not?
	global $k;$external=0;
	if (getval("k","")!="") {$external=1;}
	
	# First check to see if there's a row
	$count=sql_value("select count(*) value from daily_stat where year='$year' and month='$month' and day='$day' and usergroup='$usergroup' and activity_type='$activity_type' and object_ref='$object_ref' and external='$external'",0);
	if ($count==0)
		{
		# insert
		sql_query("insert into daily_stat(year,month,day,usergroup,activity_type,object_ref,external,count) values ('$year','$month','$day','$usergroup','$activity_type','$object_ref','$external','1')",false,-1,true,0);
		}
	else
		{
		# update
		sql_query("update daily_stat set count=count+1 where year='$year' and month='$month' and day='$day' and usergroup='$usergroup' and activity_type='$activity_type' and object_ref='$object_ref' and external='$external'",false,-1,true,0);
		}
	}    
}

function include_plugin_config($plugin_name,$config="",$config_json="")
    {
    global $mysql_charset;
    
    $pluginpath=get_plugin_path($plugin_name);
    
    $configpath = $pluginpath . "/config/config.default.php";
    if (file_exists($configpath)) {include $configpath;}
    $configpath = $pluginpath . "/config/config.php";
    if (file_exists($configpath)) {include $configpath;}

    if ($config_json != "" && function_exists('json_decode'))
        {
        if (!isset($mysql_charset))
            {
            $config_json = iconv('ISO-8859-1', 'UTF-8', $config_json);
            }
        $config_json = json_decode($config_json, true);
        if ($config_json)
            {
            foreach($config_json as $key=>$value)
                {
                $$key = $value;
                }
            }
        }
	elseif ($config != "")
		{
		$config=unserialize(base64_decode($config));
		foreach($config as $key=>$value)
			$$key = $value;
		}

	# Copy config variables to global scope.
    unset($plugin_name, $config, $config_json, $configpath);
	$vars = get_defined_vars();
	foreach ($vars as $name=>$value)
		{
		global $$name;
		$$name = $value;
		}
	}
function register_plugin_language($plugin)
    {
    global $plugins,$language,$pagename,$lang,$applicationname;
    
    	# Include language file
    	$langpath=get_plugin_path($plugin) . "/languages/";
	
    	if (file_exists($langpath . "en.php")) {include $langpath . "en.php";}
    	if ($language!="en")
    		{
    		if (substr($language, 2, 1)=='-' && substr($language, 0, 2)!='en')
    			@include $langpath . safe_file_name(substr($language, 0, 2)) . ".php";
    		@include $langpath . safe_file_name($language) . ".php";
    		}
    }
    
function get_plugin_path($plugin,$url=false)
    {
    # For the given plugin shortname, return the path on disk
    # Supports plugins being in the filestore folder (for user uploaded plugins)
    global $baseurl_short,$storagedir,$storageurl;
    
    # Standard location    
    $pluginpath=dirname(__FILE__) . "/../plugins/" . $plugin;
    if (file_exists($pluginpath)) {return ($url?$baseurl_short . "plugins/" . $plugin:$pluginpath);}

    # Filestore location
    $pluginpath=$storagedir . "/plugins/" . $plugin;
    if (file_exists($pluginpath)) {return ($url?$storageurl . "/plugins/" . $plugin:$pluginpath);}
    }
    
function register_plugin($plugin)
	{
	global $plugins,$language,$pagename,$lang,$applicationname;

	# Also include plugin hook file for this page.
	if ($pagename=="collections_frameless_loader"){$pagename="collections";}
	
	$pluginpath=get_plugin_path($plugin);
	    
	$hookpath=$pluginpath . "/hooks/" . $pagename . ".php";
	if (file_exists($hookpath)) {include_once $hookpath;}
	
	# Support an 'all' hook
	$hookpath=$pluginpath . "/hooks/all.php";
	if (file_exists($hookpath)) {include_once $hookpath;}
	
	return true;	
	}
	
/**
 * Recursively removes a directory.
 * 
 * Recursively removes a directory.  Currently this is only used by the plugin
 * management interface to permanently delete a plugin.  This function does
 * not check to see that php is <em>allowed</em> to delete the specified
 * path currently.
 * 
 * @todo ADD - Check that PHP has permissions to delete $path
 * @param string $path Directory path to remove.
 */
function rcRmdir ($path){ # Recursive rmdir function.
	if (is_dir($path)){
	    $dirh = opendir($path);
	    while (false !== ($file = readdir($dirh))){
	        if (is_dir($path.'/'.$file)){
	        	if (!((strlen($file)==1 && $file[0]=='.') || (substr($file,0,2)=='..'))){
	        		rcRmdir($path.'/'.$file);
	        	}
	        }
	        else {
	            unlink($path.'/'.$file);
	        }
		}
		closedir($dirh);
		rmdir($path);
	}
}

function get_resource_table_joins(){
	
	global 
	$rating_field,
	$sort_fields,
	$small_thumbs_display_fields,
	$xl_thumbs_display_fields,
	$thumbs_display_fields,
	$list_display_fields,
	$data_joins,
	$metadata_template_title_field,
	$view_title_field,
	$date_field,
	$config_sheetlist_fields,
	$config_sheetthumb_fields,
	$config_sheetsingle_fields;

	$joins=array_merge(
	$sort_fields,
	$small_thumbs_display_fields,
	$xl_thumbs_display_fields,
	$thumbs_display_fields,
	$list_display_fields,
	$data_joins,
	$config_sheetlist_fields,
	$config_sheetthumb_fields,
	$config_sheetsingle_fields,
		array(
		$rating_field,
		$metadata_template_title_field,
		$view_title_field,
		$date_field)
	);
	$additional_joins=hook("additionaljoins");
	if ($additional_joins) $joins=array_merge($joins,$additional_joins);
	$joins=array_unique($joins);
	$n=0;
	foreach ($joins as $join){
		if ($join!=""){
			$return[$n]=$join;
			$n++;
			}
		}
	return $return;
	}
    
function debug($text,$resource_log_resource_ref=null,$resource_log_code=LOG_CODE_TRANSFORMED)
	{

    # Update the resource log if resource reference passed.
	if(!is_null($resource_log_resource_ref))
        {
        resource_log($resource_log_resource_ref,$resource_log_code,'','','',$text);
        }

	# Output some text to a debug file.
	# For developers only
	global $debug_log, $debug_log_override, $debug_log_location;
	if (!$debug_log && !$debug_log_override) {return true;} # Do not execute if switched off.
	
	# Cannot use the general.php: get_temp_dir() method here since general may not have been included.
	if (isset($debug_log_location))
		{
		$debugdir = dirname($debug_log_location);
		if (!is_dir($debugdir)){mkdir($debugdir, 0755, true);}
		}
	else 
		{
		$debug_log_location=get_debug_log_dir() . "/debug.txt";
		}
	if(!file_exists($debug_log_location))
		{
		// Set the permissions if we can to prevent browser access (will not work on Windows)
		$f=fopen($debug_log_location,"a");
		chmod($debug_log_location,0333);
		}
    else
        {
		$f=fopen($debug_log_location,"a");
		}
    fwrite($f,date("Y-m-d H:i:s") . " " . $text . "\n");
    fclose ($f);
	return true;
	}
	
/**
 * Determines where the debug log will live.  Typically, same as tmp dir (See general.php: get_temp_dir().
 * Since general.php may not be included, we cannot use that method so I have created this one too.
 * @return string - The path to the debug_log directory.
 */
function get_debug_log_dir()
{
    // Set up the default.
    $result = dirname(dirname(__FILE__)) . "/filestore/tmp";

    // if $tempdir is explicity set, use it.
    if(isset($tempdir))
    {
        // Make sure the dir exists.
        if(!is_dir($tempdir))
        {
            // If it does not exist, create it.
            mkdir($tempdir, 0777);
        }
        $result = $tempdir;
    }
    // Otherwise, if $storagedir is set, use it.
    else if (isset($storagedir))
    {
        // Make sure the dir exists.
        if(!is_dir($storagedir . "/tmp"))
        {
            // If it does not exist, create it.
            mkdir($storagedir . "/tmp", 0777);
        }
        $result = $storagedir . "/tmp";
    }
    else
    {
        // Make sure the dir exists.
        if(!is_dir($result))
        {
            // If it does not exist, create it.
            mkdir($result, 0777);
        }
    }
    // return the result.
    return $result;
}

function show_pagetime(){
	global $pagetime_start;
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$total_time = round(($time - $pagetime_start), 4);
	echo $total_time." sec";
}

/*
 * Permissions Functions
 * Each function encapsulates a more complex combination of permissions
 *
 */
function checkPermission_anonymoususer()
	{
	global $anonymous_login,$username;
	return (isset($anonymous_login) && $anonymous_login==$username);
	}


# Dash Permissions
function checkPermission_dashadmin()	
	{
	return ((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")));
	}
function checkPermission_dashuser()
	{
	return !checkperm("dtu");
	}

function checkPermission_dashmanage()
	{
	#Home_dash is on, And not the Anonymous user with default dash, And (Dash tile user (Not with a managed dash) || Dash Tile Admin)
	global $managed_home_dash,$unmanaged_home_dash_admins, $anonymous_default_dash;
	return (!checkPermission_anonymoususer() || !$anonymous_default_dash) && ((!$managed_home_dash && (checkPermission_dashuser() || checkPermission_dashadmin()))
				|| ($unmanaged_home_dash_admins && checkPermission_dashadmin()));
	}
function checkPermission_dashcreate()
	{
	#Home_dash is on, And not Anonymous use, And (Dash tile user (Not with a managed dash) || Dash Tile Admin)
	global $managed_home_dash,$unmanaged_home_dash_admins;
	return !checkPermission_anonymoususer() 
			&& 
				(
					(!$managed_home_dash && (checkPermission_dashuser() || checkPermission_dashadmin())) 
				||
					($managed_home_dash && checkPermission_dashadmin())
				|| 
					($unmanaged_home_dash_admins && checkPermission_dashadmin())
				);
	}
	
function setup_user($userdata)
	{
        # Given an array of user data loaded from the user table, set up all necessary global variables for this user
        # including permissions, current collection, config overrides and so on.
        
    global $userpermissions, $usergroup, $usergroupname, $usergroupparent, $useremail, $userpassword, $userfullname, $userfixedtheme, 
           $ip_restrict_group, $ip_restrict_user, $rs_session, $global_permissions, $userref, $username, $useracceptedterms, $anonymous_user_session_collection, 
           $global_permissions_mask, $user_preferences, $userrequestmode, $usersearchfilter, $usereditfilter, $userderestrictfilter, $hidden_collections, 
           $userresourcedefaults, $userrequestmode, $request_adds_to_collection, $usercollection, $lang, $validcollection, $userpreferences;
		
	# Hook to modify user permissions
	if (hook("userpermissions")){$userdata["permissions"]=hook("userpermissions");} 

    $userref           = $userdata['ref'];
    $username          = $userdata['username'];
    $useracceptedterms = $userdata['accepted_terms'];
	
	# Create userpermissions array for checkperm() function
	$userpermissions=array_diff(array_merge(explode(",",trim($global_permissions)),explode(",",trim($userdata["permissions"]))),explode(",",trim($global_permissions_mask))); 
	$userpermissions=array_values($userpermissions);# Resquence array as the above array_diff() causes out of step keys.
	
	$usergroup=$userdata["usergroup"];
	$usergroupname=$userdata["groupname"];
        $usergroupparent=$userdata["parent"];
        $useremail=$userdata["email"];
        $userpassword=$userdata["password"];
        $userfullname=$userdata["fullname"];
	if (!isset($userfixedtheme)) {$userfixedtheme=$userdata["fixed_theme"];} # only set if not set in config.php

        $ip_restrict_group=trim($userdata["ip_restrict_group"]);
        $ip_restrict_user=trim($userdata["ip_restrict_user"]);
        
        if(isset($rs_session) && !checkperm('b')) // This is only required if anonymous user has collection functionality
		{
		if (!function_exists("get_user_collections"))
			{
			include_once "collections_functions.php";
			}
		// Get all the collections that relate to this session
		$sessioncollections=get_session_collections($rs_session,$userref,true); 
		if($anonymous_user_session_collection)
			{
			// Just get the first one if more
			$usercollection=$sessioncollections[0];		
			$collection_allow_creation=false; // Hide all links that allow creation of new collections
			}
		else
			{
			// Unlikely scenario, but maybe we do allow anonymous users to change the selected collection for all other anonymous users
			$usercollection=$userdata["current_collection"];
			}		
		}
	else
		{	
		$usercollection=$userdata["current_collection"];
		// Check collection actually exists
		$validcollection=sql_value("select ref value from collection where ref='$usercollection'",0);
		if($validcollection==0)
			{
			// Not a valid collection - switch to user's primary collection if there is one
			$usercollection=sql_value("select ref value from collection where user='$userref' and name like 'My Collection%' order by created asc limit 1",0);
			if ($usercollection!=0)
				{
				# set this to be the user's current collection
				sql_query("update user set current_collection='$usercollection' where ref='$userref'");
				}
			}
		
		if ($usercollection==0 || !is_numeric($usercollection))
			{
			# Create a collection for this user
			global $lang;
			include_once "collections_functions.php"; # Make sure collections functions are included before create_collection
			# The collection name is translated when displayed!
			$usercollection=create_collection($userref,"My Collection",0,1); # Do not translate this string!
			# set this to be the user's current collection
			sql_query("update user set current_collection='$usercollection' where ref='$userref'");
			}
		}

        $usersearchfilter=isset($userdata["search_filter_override"]) && $userdata["search_filter_override"]!='' ? $userdata["search_filter_override"] : $userdata["search_filter"];
        $usereditfilter=$userdata["edit_filter"];
        $userderestrictfilter=$userdata["derestrict_filter"];
        $hidden_collections=explode(",",$userdata["hidden_collections"]);
        $userresourcedefaults=$userdata["resource_defaults"];
        $userrequestmode=trim($userdata["request_mode"]);

    	$userpreferences = ($user_preferences) ? sql_query("SELECT user, `value` AS colour_theme FROM user_preferences WHERE user = '" . escape_check($userref) . "' AND parameter = 'colour_theme';") : FALSE;
    	$userpreferences = ($userpreferences && isset($userpreferences[0])) ? $userpreferences[0]: FALSE;

        # Some alternative language choices for basket mode / e-commerce
        if ($userrequestmode==2 || $userrequestmode==3)
			{
			$lang["addtocollection"]=$lang["addtobasket"];
			$lang["action-addtocollection"]=$lang["addtobasket"];
			$lang["addtocurrentcollection"]=$lang["addtobasket"];
			$lang["requestaddedtocollection"]=$lang["buyitemaddedtocollection"];
			$lang["action-request"]=$lang["addtobasket"];
			$lang["managemycollections"]=$lang["viewpurchases"];
			$lang["mycollection"]=$lang["yourbasket"];
			$lang["action-removefromcollection"]=$lang["removefrombasket"];
			$lang["total-collections-0"] = $lang["total-orders-0"];
			$lang["total-collections-1"] = $lang["total-orders-1"];
			$lang["total-collections-2"] = $lang["total-orders-2"];
			
			# The request button (renamed "Buy" by the line above) should always add the item to the current collection.
			$request_adds_to_collection=true;
			}        
    
	
        # Apply config override options
        $config_options=trim($userdata["config_options"]);
        if ($config_options!="")
            {
            // We need to get all globals as we don't know what may be referenced here
            extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
            eval($config_options);
            }
        
	}
