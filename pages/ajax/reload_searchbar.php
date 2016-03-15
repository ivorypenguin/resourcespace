<?php

include_once('../../include/db.php');
include_once('../../include/general.php');
include_once('../../include/authenticate.php');
include_once('../../include/search_functions.php');
include_once('../../include/resource_functions.php');
include_once('../../include/collections_functions.php');


if ($simple_search_reset_after_search)
	{
	$restypes="";
	$search="";
	$quicksearch="";
	$starsearch="";
	
	}
else 
	{
	# pull values from cookies if necessary, for non-search pages where this info hasn't been submitted
	if (!isset($restypes)) {$restypes=@$_COOKIE["restypes"];}
	if (!isset($search) || ((strpos($search,"!")!==false))) {$quicksearch=(isset($_COOKIE["search"])?$_COOKIE["search"]:"");} else {$quicksearch=$search;}
	if (!isset($starsearch) || ((strpos($starsearch,"!")!==false))) {$starsearch=(isset($_COOKIE["starsearch"])?$_COOKIE["starsearch"]:"");} 
	}


include_once('../../include/searchbar.php');
?>
<script type="text/javascript">
      jQuery(document).ready(function() { 
      
      if (typeof AdditionalJs == 'function') {   
        AdditionalJs();  
      }
  
      });
  </script>