<?php

if (!defined("RUNNING_ASYNC")) {define("RUNNING_ASYNC", !isset($allow_smart_collections));}

if (RUNNING_ASYNC)
	{
	include dirname(__FILE__)."/../../include/db.php";
	include_once dirname(__FILE__)."/../../include/general.php";
	include dirname(__FILE__)."/../../include/authenticate.php";
	include_once dirname(__FILE__)."/../../include/collections_functions.php";
	include dirname(__FILE__)."/../../include/resource_functions.php";
	include dirname(__FILE__)."/../../include/search_functions.php";
	if (empty($_SERVER['argv'][1])) {exit();}

	$collection=$_SERVER['argv'][1];
	$smartsearch_ref=sql_value("select savedsearch value from collection where ref='$collection'","");	
	}


$smartsearch=sql_query("select search,restypes,starsearch,archive,created,result_limit from collection_savedsearch where ref='$smartsearch_ref'");

if (isset($smartsearch[0]['search']))
	{
	$smartsearch=$smartsearch[0];
					
	# Option to limit results;
	$result_limit=$smartsearch["result_limit"]; if ($result_limit=="" || $result_limit==0) {$result_limit=-1;}
	 	
	$startTime = microtime(true);
	global $smartsearch_accessoverride;
	$results=do_search($smartsearch['search'], $smartsearch['restypes'], "relevance", $smartsearch['archive'],$result_limit,"desc",$smartsearch_accessoverride,$smartsearch['starsearch']);
	//$startTime = microtime(true); 
	# results is a list of the current search without any restrictions
	# we need to compare against the current collection contents to minimize inserts and deletions
	$current_contents=sql_array("select resource value from collection_resource where collection='$collection'");
		
	$results_contents=array();
	$counter=0;
	if (!empty($results)&&is_array($results))
		{
		foreach($results as $results_item)
			{ 
			if (isset($results_item['ref']))
				{
				$results_contents[]=$results_item['ref'];
				$counter++;
				if ($counter>=$result_limit && $result_limit!=-1) 
					{	
					break;
					}
				}
			}
		}
		
	//echo "Comparing results...";		
	$results_contents_add = array_values(array_diff($results_contents, $current_contents));
	$current_contents_remove = array_values(array_diff($current_contents, $results_contents));
							
	$count_results=count($results_contents_add);
	if ($count_results>0)	
		{
		# Add any new resources
		debug( "smart_collections_async : Adding $count_results resources to collection...");
		for ($n=0;$n<$count_results;$n++)
			{
			add_resource_to_collection($results_contents_add[$n],$collection,true);
			}
		}
					
		$count_contents=count($current_contents_remove);
		if ($count_contents>0)	
			{
			# Remove any resources no longer present.
			debug( "smart_collections_async : Removing $count_contents resources...");	
			for ($n=0;$n<$count_contents;$n++)
				{				
				remove_resource_from_collection($current_contents_remove[$n],$collection,true);
				}
			}	
		$endTime = microtime(true);  
		$elapsed = $endTime - $startTime;
		if (RUNNING_ASYNC)
			{
			debug("smart_collections_async : $elapsed seconds for ".$smartsearch['search']);
			}
	}		
