<?php

// ---------- start of n-level theme permissions ----------

# used to store black-list of collections which user is not allowed to view via themes (because of "j*" permissions)
$current_user_collection_blacklisted_no_perms = array();

# returns TRUE only if user is allowed to view collection as restricted by "j*" permissions for theme
#
function checkThemePerm($collectionRef) 
	{	
	global $current_user_collection_blacklisted_no_perms;
	if (empty($current_user_collection_blacklisted_no_perms)) getThemePathPerms();
	return (!in_array($collectionRef, $current_user_collection_blacklisted_no_perms));
	}

# returns array of key=>value
# key = theme path - pipe delimited ("|"), e.g. Cars|German|VW
# value = boolean - TRUE if permission to view (by default) or FALSE if denied (i.e. set via permission manager j* directive)
#	--- will also update $current_user_collection_blacklisted_no_perms at the same time adding banned collections because of TRUE j<path> for current user
#
function getThemePathPerms() 
	{	
	global $theme_category_levels, $permissions, $current_user_collection_blacklisted_no_perms;
	$stack = array();	
	$sql_theme_columns_name = "";	
	for ($i=2; $i<=$theme_category_levels; $i++) $sql_theme_columns_name .= ",theme{$i}";	// build up list of columns depending on how many theme levels specified in setup
	$collections=sql_query("select distinct ref, theme theme1${sql_theme_columns_name} from collection where length(theme)>0 order by theme1${sql_theme_columns_name}");  // *** order by is IMPORTANT *** 
	foreach ($collections as $collection) 
		{
		$pathString = "";
		$ref = 0;
		$perm = true;		// by default grant permission
		foreach ($collection as $item) 
			{
			if ($ref == 0)	// the first field return in query is the ref, so grab it and continue
				{
				$ref = $item;
				continue;
				}			
			if (empty($item)) break;	// the current field is blank so quit field iteration 
			if ($pathString != "") $pathString .= "|";	// only add separator if not first field
			$pathString .= $item;									
			if (array_search ("j${pathString}", $permissions)) 
				{				
				array_push ($current_user_collection_blacklisted_no_perms, $ref);	// we have found a j* permission so add to black list.
				$perm = false;		// *** IMPORTANT *** for this and all other sub-levels permission will not be granted - cool!
				}
			$stack[$pathString] = $perm;	// add to return stack
			}	// end field iteration
		}	// end row iteration
	return $stack;
	}

// ---------- end of n-level theme permissions ----------

?>