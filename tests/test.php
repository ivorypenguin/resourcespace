<?php
include "../include/db.php";
include "../include/general.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

/*
  
  Test.php
  --------
  Create a test database and storagedir, then run a series of tests in sequence
  A default database will be created but tests can create new fields, resource types, etc as part of testing, and those items
  utilised by later tests.

  */

$argv=preg_replace('/^(-|--|\/)/','',$argv);    // remove leading /, -- or -

if(array_search('?',$argv)!==false || array_search('help',$argv)!==false)
    {
?>

Command line paramaters:

-nosetup        Do not setup the database, connect user in current state
-noteardown     Do not drop the database once tests have completed
-help or -?     This help information
[n]...          Specific test number(s) to run
<?php
    exit;
    }

# Create an array of tests that were passed from the command line
$specific_tests=array();
foreach($argv as $arg)
    {
    if(is_numeric($arg))
        {
        array_push($specific_tests, str_pad($arg,6,'0',STR_PAD_LEFT));
        }
    }

$mysql_db = "rs_test_db";
$test_user_name = "admin";
$inst_plugins = sql_query('SELECT name FROM plugins WHERE inst_version>=0 order by name');

if(array_search('nosetup',$argv)===false)
    {

    # Create a database for testing purposes
    echo "Creating database $mysql_db\n";
    ob_flush();
    sql_query("drop database if exists `$mysql_db`");
    sql_query("create database `$mysql_db`");

    # Connect and create standard tables.
    echo "Creating default database tables...";
    ob_flush();
    sql_connect();
    check_db_structs(true);
    echo "...done\n";

    # Insert a new user and run as them.
    $u = new_user($test_user_name);
    }
else
    {
    sql_connect();

    # Try to retrieve the ref of the existing user
    $u = sql_value("SELECT `ref` AS value FROM `user` WHERE `username`='{$test_user_name}'",-1);
    if ($u==-1)
        {
        die("Could not find existing '{$test_user_name}' user");
        }
    }

# Setup user
user_set_usergroup($u, 3);
$userdata = get_user($u);
setup_user($userdata);
echo "Now running as user $userref\n";
ob_flush();

# Use an alternative filestore path
$storagedir .= "/rs_test/";
if (!file_exists($storagedir))
    {
    mkdir($storagedir);
    }
echo "Filestore is now at $storagedir\n";

# Get a list of core tests
$core_tests = scandir("test_list");
$core_tests = array_filter($core_tests, function ($string)
    {
    global $specific_tests;
    if (strpos($string, ".php")===false)
        {
        return false;
        }
    if (count($specific_tests)==0)
        {
        return true;
        }
    foreach($specific_tests as $specific_test)
        {
        if (strpos($string, $specific_test)!==false)
            {
            return true;
            }
        }
    return false;
    }); # PHP files only
asort($core_tests);

$core_tests = array('test_list' => $core_tests);

# Get a list of plugin tests
$plugin_tests = array();
foreach ($inst_plugins as $plugin)
	{
	if (file_exists('../plugins/' . $plugin['name'] . '/tests'))
		{	
		$plugin_tests['../plugins/' . $plugin['name'] . '/tests'] = scandir('../plugins/' . $plugin['name'] . '/tests');
		}
	}
foreach ($plugin_tests as $key => $tests)
	{
	$plugin_tests[$key] = array_filter($tests, function ($string)
		{
		global $specific_tests;
		if (strpos($string, ".php")===false)
			{
			return false;
			}
		if (count($specific_tests)==0)
			{
			return true;
			}
		return false;
		});
	asort($tests);
	}
	
$plugin_tests = array_filter($plugin_tests); # Remove empty sub arrays

if (!empty($plugin_tests))
    {
    $tests = array_merge($core_tests, $plugin_tests);	
    }
else
	{
	$tests = $core_tests;	
	}		

# Run tests
echo "-----\n";ob_flush();
foreach ($tests as $key => $test_stack)
    {
	foreach ($test_stack as $test)
	    {
		echo "Running test " . str_pad($test,45," ") . " ";ob_flush();
		$result = include $key . '/'. $test;
		if ($result===false)
		    {
			echo "FAIL\n";ob_flush();
			if (isset($email_test_fails_to))
			    {
				$svnrevision=trim(shell_exec("svnversion .")); 
				send_mail($email_test_fails_to,"Test $test has failed as of r" . $svnrevision,"Hi,\n\nAs of revision " . $svnrevision. " the test '" . $test . "' is failing.\n\nThis e-mail was sent from the installation at $baseurl.");
				}
			if ($key=="test_list")
				{
				exit();	# If a core test fails cancel all other tests
				}
			else
				{
				break;	# If a plugin test fails abort tests for this plugin but continue
				}					
			}
		echo "OK\n";ob_flush();
		}
	echo "-----\n";ob_flush();
	}
echo "All tests complete.\n";

if(array_search('noteardown',$argv)===false)
    {
    # Remove database
    sql_query("drop database `$mysql_db`");
    }
