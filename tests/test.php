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

  
# Create a database for testing purposes
$mysql_db="rs_test_db";
echo "Creating database $mysql_db\n";ob_flush();
sql_query("drop database if exists `$mysql_db`");
sql_query("create database `$mysql_db`");

# Connect and create standard tables.
echo "Creating default database tables...";ob_flush();
sql_connect();
check_db_structs(true);
echo "...done\n";

# Use an alternative filestore path
$storagedir.="/rs_test/";
if (!file_exists($storagedir)) {mkdir($storagedir);}
echo "Filestore is now at $storagedir\n";

# Insert a new user and run as them.
$u=new_user("admin");
user_set_usergroup($u,3);
$userdata=get_user($u);
setup_user($userdata);
echo "Now running as user $userref\n";ob_flush();

# Get a list of tests
$tests=scandir("test_list");
$tests=array_filter($tests,function ($string) {return (strpos($string,".php")!==false);}); # PHP files only
asort($tests);

# Run tests
echo "-----\n";
foreach ($tests as $test)
    {
    echo "Running test " . str_pad($test,45," ") . " ";ob_flush();
    $result = include "test_list/" . $test;
    if ($result===false)
        {
        echo "FAIL\n";
        exit();
        }
    echo "OK\n";ob_flush();
    }
echo "-----\nAll tests complete.\n";

# Remove database
sql_query("drop database `$mysql_db`");