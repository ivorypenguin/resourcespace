<?php

// ---------- start of n-level theme permissions ----------

# returns array of key=>value
# key = theme path, pipe delimited ("|"), e.g. Cars|German|VW
# value = boolean - TRUE(1) if permission to view or FALSE if denied
# --- set via permission manager j*, (include all), j<top level include> and j-<exclude below top level> directives
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
			if (
					(substr_count ($pathString,"|") == 0 && !array_search ("j${pathString}", $permissions))		// for top level we just need to make sure that "jMyTheme" does not exist										
					||
					(array_search ("j-${pathString}", $permissions)) 	// look for minus path to indicate that we do not have permission from here and below
				)
				{				
				$perm = false;		// *** IMPORTANT *** for this and all other sub-levels permission will not be granted - cool!
				}
			$stack[$pathString] = $perm;	// add to return stack
			}	// end field iteration
		}	// end row iteration
	return $stack;
	}

// ---------- end of n-level theme permissions ----------

?>