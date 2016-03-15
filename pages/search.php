<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/resource_functions.php"; //for checking scr access
include "../include/search_functions.php";
include_once "../include/collections_functions.php";
include_once '../include/render_functions.php';

# External access support (authenticate only if no key provided, or if invalid access key provided)
$s=explode(" ",getvalescaped("search",""));
$k=getvalescaped("k","");

if (($k=="") || (!check_access_key_collection(str_replace("!collection","",$s[0]),$k))) {include "../include/authenticate.php";}

// Set a flag for logged in users if $external_share_view_as_internal is set and logged on user is accessing an external share
$internal_share_access = ($k!="" && $external_share_view_as_internal && isset($is_authenticated) && $is_authenticated);

if ($k=="" || $internal_share_access)
    {
    #note current user collection for add/remove links if we haven't got it set already
    if(!isset($usercollection))
		{
		if((isset($anonymous_login) && ($username==$anonymous_login)) && isset($rs_session) && $anonymous_user_session_collection)
			{	
			$sessioncollections=get_session_collections($rs_session,$userref,true); 
			$usercollection=$sessioncollections[0];
			$collection_allow_creation=false; // Hide all links that allow creation of new collections						
			}
		else
			{
			if(isset($user))
				{
				$user=get_user($userref);
				}
			$usercollection=$user['current_collection'];
			}
		}
	}
# Disable checkboxes for external users.
if ($k!="" && !$internal_share_access) {$use_checkboxes_for_selection=false;}

$search = getvalescaped('search', '');
$modal  = ('true' == getval('modal', ''));

hook("moresearchcriteria");

# create a display_fields array with information needed for detailed field highlighting
$df=array();


$all_field_info=get_fields_for_search_display(array_unique(array_merge($sort_fields,$thumbs_display_fields,$list_display_fields,$xl_thumbs_display_fields,$small_thumbs_display_fields)));

# get display and normalize display specific variables
$display=getvalescaped("display",$default_display);rs_setcookie('display', $display);

if ($display=="thumbs" || $display=="stripes"){ 
	$display_fields	= $thumbs_display_fields;  
	if (isset($search_result_title_height)) { $result_title_height = $search_result_title_height; }
	$results_title_trim = $search_results_title_trim;
	$results_title_wordwrap	= $search_results_title_wordwrap;
	}
	
if ($display=="list"){ 
	$display_fields	= $list_display_fields; 
	$results_title_trim = $list_search_results_title_trim;
	}
	
if ($display=="smallthumbs"){ 
	$display_fields	= $small_thumbs_display_fields; 
	if (isset($small_search_result_title_height)) { $result_title_height = $small_search_result_title_height; }
	$results_title_trim = $small_search_results_title_trim;
	$results_title_wordwrap = $small_search_results_title_wordwrap;
	}
if ($display=="xlthumbs"){ 
	$display_fields = $xl_thumbs_display_fields;
	if (isset($xl_search_result_title_height)) { $result_title_height = $xl_search_result_title_height; }
	$results_title_trim = $xl_search_results_title_trim;
	$results_title_wordwrap = $xl_search_results_title_wordwrap;
	}

$n=0;
foreach ($display_fields as $display_field)
	{
	# Find field in selected list
	for ($m=0;$m<count($all_field_info);$m++)
		{
		if ($all_field_info[$m]["ref"]==$display_field)
			{
			$field_info=$all_field_info[$m];
			$df[$n]['ref']=$display_field;
			$df[$n]['type']=$field_info['type'];
			$df[$n]['indexed']=$field_info['keywords_index'];
			$df[$n]['partial_index']=$field_info['partial_index'];
			$df[$n]['name']=$field_info['name'];
			$df[$n]['title']=$field_info['title'];
			$df[$n]['value_filter']=$field_info['value_filter'];
			$n++;
			}
		}
	}
$n=0;	
$df_add=hook("displayfieldsadd");
# create a sort_fields array with information for sort fields
$n=0;
$sf=array();
foreach ($sort_fields as $sort_field)
	{
	# Find field in selected list
	for ($m=0;$m<count($all_field_info);$m++)
		{
		if ($all_field_info[$m]["ref"]==$sort_field)
			{ 
			$field_info=$all_field_info[$m];
			$sf[$n]['ref']=$sort_field;
			$sf[$n]['title']=$field_info['title'];
			$n++;
			}
		}
	}
$n=0;	

# Append extra search parameters from the quick search.
if (!$config_search_for_number || !is_numeric($search)) # Don't do this when the search query is numeric, as users typically expect numeric searches to return the resource with that ID and ignore country/date filters.
	{
	// For the simple search fields, collect from the GET and POST requests and assemble into the search string.
	reset ($_POST);reset($_GET);

	foreach (array_merge($_GET, $_POST) as $key=>$value)
		{
		if (is_string($value))
		  {
		  $value=trim($value);
		  }
		if ($value!="" && substr($key,0,6)=="field_")
			{
			if ((strpos($key,"_year")!==false)||(strpos($key,"_month")!==false)||(strpos($key,"_day")!==false))
				{
				# Date field
				
				# Construct the date from the supplied dropdown values
				$key_part=substr($key,0, strrpos($key, "_"));
				$field=substr($key_part,6);
                $value="";
				if (strpos($search, $field.":")===false) 
				    {
                $key_year=$key_part."_year";
				$value_year=getvalescaped($key_year,"");
				if ($value_year!="") $value=$value_year;
				else $value="nnnn";
				
				$key_month=$key_part."_month";
				$value_month=getvalescaped($key_month,"");
				if ($value_month=="") $value_month.="nn";
				
				$key_day=$key_part."_day";
				$value_day=getvalescaped($key_day,"");
				if ($value_day!="") $value.="|" . $value_month . "|" . $value_day;
				elseif ($value_month!="nn") $value.="|" . $value_month;
    				
    
    				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . $field . ":" . $value;

				    }
	            				
				}
			elseif (strpos($key,"_drop_")!==false)
				{
				# Dropdown field
				# Add keyword exactly as it is as the full value is indexed as a single keyword for dropdown boxes.
				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,11) . ":" . $value;
				}		
			elseif (strpos($key,"_cat_")!==false)
				{
				# Category tree field
				# Add keyword exactly as it is as the full value is indexed as a single keyword for dropdown boxes.
				$value=str_replace(",",";",$value);
				if (substr($value,0,1)==";") {$value=substr($value,1);}
				
				$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,10) . ":" . $value;
				}		

			else
				{
				# Standard field
				$values =  explode(' ', mb_strtolower(trim_spaces(str_replace($config_separators, ' ', $value)), 'UTF-8'));
				foreach ($values as $value)
					{
					# Standard field
					$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . substr($key,6) . ":" . $value;
					}
				}
			}
		}

	$year=getvalescaped("year","");
	if ($year!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "year:" . $year;}
	$month=getvalescaped("month","");
	if ($month!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "month:" . $month;}
	$day=getvalescaped("day","");
	if ($day!="") {$search=(($search=="")?"":join(", ",split_keywords($search)) . ", ") . "day:" . $day;}
	}

$searchresourceid = "";
if (is_numeric(trim(getvalescaped("searchresourceid","")))){
	$searchresourceid = trim(getvalescaped("searchresourceid",""));
	$search = "!resource$searchresourceid";
}
	
hook("searchstringprocessing");


# Fetch and set the values
//setcookie("search",$search); # store the search in a cookie if not a special search
$offset=getvalescaped("offset",0);if (strpos($search,"!")===false) {rs_setcookie('saved_offset', $offset);}
if ((!is_numeric($offset)) || ($offset<0)) {$offset=0;}

// Is this a collection search?
$collectionsearch = substr($search,0,11)=="!collection"; // We want the default collection order to be applied

$order_by=getvalescaped("order_by","");if (strpos($search,"!")===false || substr($search,0,11)=="!properties") {rs_setcookie('saved_order_by', $order_by);}
if ($order_by=="")
	{
	if ($collectionsearch) // We want the default collection order to be applied
		{
		$order_by="relevance";
		}
	else
		{
		$order_by=$default_sort;
		}
	}
$per_page=getvalescaped("per_page",$default_perpage);rs_setcookie('per_page', $per_page);
$archive=getvalescaped("archive",0);if (strpos($search,"!")===false) {rs_setcookie('saved_archive', $archive);}
$jumpcount=0;

if (getvalescaped("recentdaylimit","")!="") //set for recent search, don't set cookie
	{
	$daylimit=getvalescaped("recentdaylimit","");
	}
else if($recent_search_period_select==true && strpos($search,"!")===false) //set cookie for paging
	{
	$daylimit=getvalescaped("daylimit",""); 
	rs_setcookie('daylimit', $daylimit);
	}
else {$daylimit="";} // clear cookie for new search

# Most sorts such as popularity, date, and ID should be descending by default,
# but it seems custom display fields like title or country should be the opposite.
$default_sort_order="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_order="ASC";}
$sort=getvalescaped("sort",$default_sort_order);rs_setcookie('saved_sort', $sort);
$revsort = ($sort=="ASC") ? "DESC" : "ASC";

## If displaying a collection
# Enable/disable the reordering feature. Just for collections for now.
$allow_reorder=false;

# get current collection resources to pre-fill checkboxes
if ($use_checkboxes_for_selection){
$collectionresources=get_collection_resources($usercollection);
}
    $hiddenfields=getvalescaped("hiddenfields","");

# fetch resource types from query string and generate a resource types cookie
if (getvalescaped("resetrestypes","")=="")
	{
	$restypes=getvalescaped("restypes","");
	}
else
	{ 
	$restypes="";
	reset($_POST);reset($_GET);foreach (array_merge($_GET, $_POST) as $key=>$value)

		{
		
	    $hiddenfields=Array();
		//$hiddenfields=explode(",",$hiddenfields);
		if ($key=="rttickall" && $value=="on"){$restypes="";break;}	
		if ((substr($key,0,8)=="resource")&&!in_array($key, $hiddenfields)) {if ($restypes!="") {$restypes.=",";} $restypes.=substr($key,8);}
		}

	rs_setcookie('restypes', $restypes);

	# This is a new search, log this activity
	if ($archive==2) {daily_stat("Archive search",0);} else {daily_stat("Search",0);}
	}
$modified_restypes=hook("modifyrestypes_aftercookieset");
if($modified_restypes){$restypes=$modified_restypes;}

# if search is not a special search (ie. !recent), use starsearchvalue.
if (getvalescaped("search","")!="" && strpos(getvalescaped("search",""),"!")!==false)
	{
	$starsearch="";
	}
else
	{
	$starsearch=getvalescaped("starsearch","");	
	rs_setcookie('starsearch', $starsearch);
}

# If returning to an old search, restore the page/order by and other non search string parameters
if (!array_key_exists("search",$_GET) && !array_key_exists("search",$_POST))
	{
	$offset=getvalescaped("saved_offset",0,true);rs_setcookie('saved_offset', $offset);
	$order_by=getvalescaped("saved_order_by","relevance");rs_setcookie('saved_order_by', $order_by);
	$sort=getvalescaped("saved_sort","");rs_setcookie('saved_sort', $sort);
	$archive=getvalescaped("saved_archive",0);rs_setcookie('saved_archive', $archive);
	}
	
hook("searchparameterhandler");	
	
# If requested, refresh the collection frame (for redirects from saves)
if (getvalescaped("refreshcollectionframe","")!="")
	{
	refresh_collection_frame();
	}

# Initialise the results references array (used later for search suggestions)
$refs=array();

# Special query? Ignore restypes
if (strpos($search,"!")!==false &&  substr($search,0,11)!="!properties") {$restypes="";}

# Do the search!
$search=refine_searchstring($search);
if (strpos($search,"!")===false || substr($search,0,11)=="!properties") {rs_setcookie('search', $search);}
hook('searchaftersearchcookie');
if (!hook("replacesearch")) {
	$result=do_search($search,$restypes,$order_by,$archive,$per_page+$offset,$sort,false,$starsearch,false,false,$daylimit, getvalescaped("go",""));
}
if(($k=="" || $internal_share_access) && strpos($search,"!")===false && $archive==0){$collections=do_collections_search($search,$restypes);} // don't do this for external shares

# Allow results to be processed by a plugin
$hook_result=hook("process_search_results","search",array("result"=>$result,"search"=>$search));
if ($hook_result!==false) {$result=$hook_result;}

if ($collectionsearch)
	{
	$collection=substr($search,11);
	$collection=explode(",",$collection);
	$collection=$collection[0];
	$collectiondata=get_collection($collection);
	
	if ($k!="" && !$internal_share_access) {$usercollection=$collection;} # External access - set current collection.
	if (!$collectiondata){?>
		<script>alert('<?php echo $lang["error-collectionnotfound"];?>');document.location='<?php echo $baseurl."/pages/" . $default_home_page;?>'</script>
	<?php } 
	# Check to see if this user can edit (and therefore reorder) this resource
	if (($userref==$collectiondata["user"]) || ($collectiondata["allow_changes"]==1) || (checkperm("h")))
		{
		$allow_reorder=true;
		}
	}

# Include function for reordering
if ($allow_reorder && $display!="list")
	{
	# Also check for the parameter and reorder as necessary.
	$reorder=getvalescaped("reorder",false);
	if ($reorder)
		{
		$neworder=json_decode(getvalescaped("order",false));
		update_collection_order($neworder,$collection,$offset);
		exit("SUCCESS");
		}
	}

include ("../include/search_title_processing.php");

    
# Special case: numeric searches (resource ID) and one result: redirect immediately to the resource view.
if ((($config_search_for_number && is_numeric($search)) || $searchresourceid > 0) && is_array($result) && count($result)==1)
	{
	redirect($baseurl_short."pages/view.php?ref=" . $result[0]["ref"] . "&search=" . urlencode($search) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&offset=" . urlencode($offset) . "&archive=" . urlencode($archive) . "&k=" . urlencode($k));
	}
	

# Include the page header to and render the search results
include "../include/header.php";
if($k=="" || $internal_share_access)
	{
	 ?>
	<script type="text/javascript">
	var dontReloadSearchBar=<?php echo getval('noreload', null)!=null ? 'true' : 'false' ?>;
	if (dontReloadSearchBar !== true)
		ReloadSearchBar();
	ReloadLinks();
	</script>
 	<?php
	}
if ($display_user_rating_stars && ($k=="" || $internal_share_access))
	{
	if (!hook("replace_user_rating_searchviewjs")){?>
	<script src="<?php echo $baseurl ?>/lib/js/user_rating_searchview.js?1" type="text/javascript"></script>
	<?php
	}
	}

// Allow Drag & Drop from collection bar to CentralSpace only when special search is "!collection"
if($collectionsearch && collection_writeable(substr($search, 11)))
	{
	?>
	<script>
		jQuery(document).ready(function() {
		if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {
			return false;
		}
			jQuery('#CentralSpaceResources').droppable({
				accept: '.CollectionPanelShell',

				drop: function(event, ui) {
					if(!is_special_search('!collection', 11)) {
						return false;
					}

					// get the current collection from the search page (ie. CentralSpace)
					var query_strings = getQueryStrings();
					if(is_empty(query_strings)) {
						return false;
					}

					var resource_id = jQuery(ui.draggable).attr("id");
					resource_id = resource_id.replace('ResourceShell', '');
					var collection_id = query_strings.search.substring(11);

					jQuery('#trash_bin').hide();
					AddResourceToCollection(event, resource_id, '', collection_id);
					CentralSpaceLoad(window.location.href, true);
				}
			});

			jQuery('#CentralSpace').trigger('CentralSpaceSortable');
		});
	</script>
	<?php
	}

if(!$collectionsearch)
	{
	?>
	<!-- Search items should only be draggable if results are not a collection -->
	<script>	
	jQuery(document).ready(function() {
		if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {return false;}
		jQuery('#CentralSpaceResources .ResourcePanelShell, .ResourcePanelShellLarge, .ResourcePanelShellSmall').draggable({
			distance: 50,
			connectWith: '#CollectionSpace',
			appendTo: 'body',
			zIndex: 99000,
			helper: 'clone',
			revert: false,
			start: function(event, ui)
				{
				InfoBoxEnabled = false;
				jQuery(this).css('visibility', 'hidden');
				},
			stop: function(event, ui)
				{
				InfoBoxEnabled = true;
				jQuery(this).css('visibility', '');
				}
		});
	});
	</script>
	<?php
	}
	
	if ($allow_reorder && $display!="list") {
?>
	<script type="text/javascript">
	var allow_reorder = true;
	
	function ReorderResources(idsInOrder) {
		var newOrder = [];
		jQuery.each(idsInOrder, function() {
			newOrder.push(this.substring(13));
			});
		jQuery.ajax({
		  type: 'POST',
		  url: 'search.php?search=!collection<?php echo urlencode($collection) ?>&reorder=true',
		  data: {order:JSON.stringify(newOrder)},
		  success: function(){
		  <?php if (isset($usercollection) && ($usercollection==$collection)) { ?>
			 UpdateCollectionDisplay('<?php echo isset($k)?htmlspecialchars($k):"" ?>');
		  <?php } ?>
			} 
		});
	}
	jQuery('#CentralSpace').on('CentralSpaceSortable', function() {
		if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {return false;}
        jQuery('.ui-sortable').sortable('enable');
		jQuery('#CentralSpaceResources').sortable({
			connectWith: '#CollectionSpace',
			appendTo: 'body',
			zIndex: 99000,
			helper: function(event, ui)
				{
				//Hack to append the element to the body (visible above others divs), 
				//but still bellonging to the scrollable container
				jQuery('#CentralSpaceResources').append('<div id="CentralSpaceResourceClone" class="ui-state-default">' + ui[0].outerHTML + '</div>');   
				jQuery('#CentralSpaceResourceClone').hide();
				setTimeout(function() {
					jQuery('#CentralSpaceResourceClone').appendTo('body'); 
					jQuery('#CentralSpaceResourceClone').show();
				}, 1);
				
				return jQuery('#CentralSpaceResourceClone');
				},
			items: '.ResourcePanelShell, .ResourcePanelShellLarge, .ResourcePanelShellSmall',
			cancel: '.DisableSort',
			
			start: function (event, ui)
				{
				InfoBoxEnabled=false;
				if (jQuery('#InfoBox')) {jQuery('#InfoBox').hide();}
				if (jQuery('#InfoBoxCollection')) {jQuery('#InfoBoxCollection').hide();}
				if(is_special_search('!collection', 11))
					{
					// get the current collection from the search page (ie. CentralSpace)
					var query_strings = getQueryStrings();
					if(is_empty(query_strings))
						{
						return false;
						}
					var collection_id = query_strings.search.substring(11);

					jQuery('#trash_bin').show();
					}
				},

			update: function(event, ui)
				{
				// Don't reorder when top and bottom collections are the same and you drag & reorder from top to bottom
				if(ui.item[0].parentElement.id == 'CollectionSpace')
					{
					return false;
					}

				InfoBoxEnabled=true;
				var idsInOrder = jQuery('#CentralSpaceResources').sortable("toArray");
				ReorderResources(idsInOrder);
				if(is_special_search('!collection', 11))
					{
					jQuery('#trash_bin').hide();
					}
				},

			stop: function(event, ui)
				{
				InfoBoxEnabled=true;
				if(is_special_search('!collection', 11))
					{
					jQuery('#trash_bin').hide();
					}
				}
		});
		jQuery('.ResourcePanelShell').disableSelection();
		jQuery('.ResourcePanelShellLarge').disableSelection();
		jQuery('.ResourcePanelShellSmall').disableSelection();

		// CentralSpace should only be sortable (ie. reorder functionality) for collections only
		if(!allow_reorder)
			{
			jQuery('#CentralSpaceResources').sortable('disable');
			}
	});
	</script>
<?php }
	elseif (!hook("noreorderjs")) { ?>
	<script type="text/javascript">
        jQuery(document).ready(function () {
			jQuery('#CentralSpaceResources .ui-sortable').sortable('disable');
			jQuery('.ResourcePanelShell').enableSelection();
			jQuery('.ResourcePanelShellLarge').enableSelection();
			jQuery('.ResourcePanelShellSmall').enableSelection();
		});
	
	</script>
	<?php }

if(getval("promptsubmit","")!= "" && getval("archive","")=="-2" && checkperm("e-1"))
	{
	// User has come here from upload. Show a prompt to submit the resources in current collection for review
	?>
	<script>	
	jQuery(document).ready(function() {
		jQuery("#modal_dialog").dialog({
							    	title:'<?php echo $lang["submit_dialog_text"]; ?>',
							    	modal: true,
									resizable: false,
									dialogClass: 'no-close',
							        buttons: {
							            "<?php echo $lang['action_submit_review'] ?>": function() {
							            		jQuery(this).dialog("close");
												window.location.href='<?php echo $baseurl_short?>pages/edit.php?submitted=true&editthis_status=true&collection=<?php echo $usercollection ?>&status=-1';
							            	},    
							            "<?php echo $lang['action_continue_editing'] ?>": function() { 
							            		jQuery(this).dialog('close');
												<?php 
												$collection_add=getvalescaped("collection_add","");
												if ($collection_add!="")
													{?>
													window.location.href='<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $collection_add ?>';
													<?php
													}?>
												}
							            	}							        
							    });
	});
	</script>
	<?php
	}

# Hook to replace all search results (used by ResourceConnect plugin, allows search mechanism to be entirely replaced)
if (!hook("replacesearchresults")) { 

# Extra CSS to support more height for titles on thumbnails.
if (isset($result_title_height))
	{
	?>
	<style>
	.ResourcePanelInfo .extended
		{
		white-space:normal;
		height: <?php echo $result_title_height ?>px;
		}
	</style>
	<?php
	}

#if (is_array($result)||(isset($collections)&&(count($collections)>0)))

if(!$search_titles && isset($theme_link))
	{
	// Show the themes breadcrumbs if they exist, but not if we are using the search_titles
	echo "<div class='SearchBreadcrumbs'>" . $theme_link . '&nbsp;&gt;&nbsp;<span id="coltitle'.$collection.'"><a  href="'.$baseurl_short.'pages/search.php?search=!collection' . $collection . '" onClick="return CentralSpaceLoad(this,true);">'.i18n_get_collection_name($collectiondata). '</a></span>' . "</div>" ;
	}

if (!hook("replacesearchheader")) # Always show search header now.
	{
	$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=".urlencode($sort)."&amp;offset=" . urlencode($offset) . "&amp;archive=" . urlencode($archive)."&amp;sort=".urlencode($sort) . "&amp;restypes=" . urlencode($restypes);
	$resources_count=is_array($result)?count($result):0;
    if (isset($collections)) 
        {
        $results_count=count($collections)+$resources_count;
    	}
	?>
	<div class="TopInpageNav">
	<div class="TopInpageNavLeft">
	<?php hook("responsiveresultoptions"); ?>
	<div id="SearchResultFound" class="InpageNavLeftBlock"><?php echo $lang["youfound"]?>:<br /><span class="Selected">
	<?php
	if (isset($collections)) 
	    {
        echo number_format($results_count)?> </span><?php echo ($results_count==1) ? $lang["youfoundresult"] : $lang["youfoundresults"];
	    } 
	else
	    {
	    echo number_format($resources_count)?> </span><?php echo ($resources_count==1)? $lang["youfoundresource"] : $lang["youfoundresources"];
	    }
	 ?></div>
	<div class="InpageNavLeftBlock <?php if($iconthumbs) {echo 'icondisplay';} ?>"><?php echo $lang["display"]?>:<br />


	<?php
	if($display_selector_dropdowns)
		{
		?>
		<select class="medcomplementwidth ListDropdown" style="width:auto" id="displaysize" name="displaysize" onchange="CentralSpaceLoad(this.value,true);">
		<?php if ($xlthumbs==true) { ?><option <?php if ($display=="xlthumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["xlthumbs"]?></option><?php } ?>
		<option <?php if ($display=="thumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["largethumbs"]?></option>
		<?php if ($smallthumbs==true) { ?><option <?php if ($display=="smallthumbs"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["smallthumbs"]?></option><?php } ?>
		<?php if ($searchlist==true) { ?><option <?php if ($display=="list"){?>selected="selected"<?php } ?> value="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>"><?php echo $lang["list"]?></option><?php } ?>
		</select>&nbsp;
		<?php
		}
	elseif($iconthumbs)
		{
		if($xlthumbs == true)
			{
			if($display == 'xlthumbs')
				{
				?>
				<span class="xlthumbsiconactive">&nbsp;</span>
				<?php
				}
			else
				{
				?>
				<a href="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["xlthumbstitle"] ?>' onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this);">
					<span class="xlthumbsicon">&nbsp;</span>
				</a>
				<?php
				}
				?>&nbsp;
				<?php
			}

		if($display == 'thumbs')
			{
			?>
			<span class="largethumbsiconactive">&nbsp;</span>
			<?php
			}
		else
			{
			?>
			<a href="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["largethumbstitle"] ?>' onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this);">
				<span class="largethumbsicon">&nbsp;</span>
			</a>
			<?php
			}

		if($smallthumbs == true)
			{
			if($display == 'smallthumbs')
				{
				?>
				<span class="smallthumbsiconactive">&nbsp;</span>
				<?php
				}
			else
				{
				?>
				<a href="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k)?>" title='<?php echo $lang["smallthumbstitle"] ?>' onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this);">
					<span class="smallthumbsicon">&nbsp;</span>
				</a>
				<?php
				}
			}
		if ($searchlist == true) 
			{
			if($display == 'list')
				{
				?>
				<span class="smalllisticonactive">&nbsp;</span>
				<?php
				}
			else
				{
				?>
				<a href="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>" title='<?php echo $lang["listtitle"] ?>' onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this);">
					<span class="smalllisticon">&nbsp;</span>
				</a>
				<?php
				}
			}

			hook('adddisplaymode');
		}
	else
		{
		if ($xlthumbs==true) { ?> <?php if ($display=="xlthumbs") { ?><span class="Selected"><?php echo $lang["xlthumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=xlthumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["xlthumbs"]?></a><?php } ?>&nbsp; |&nbsp;<?php } ?>
	<?php if ($display=="thumbs") { ?> <span class="Selected"><?php echo $lang["largethumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=thumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["largethumbs"]?></a><?php } ?>&nbsp; |&nbsp; 
	<?php if ($smallthumbs==true) { ?> <?php if ($display=="smallthumbs") { ?><span class="Selected"><?php echo $lang["smallthumbs"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=smallthumbs&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["smallthumbs"]?></a><?php } ?>&nbsp; |&nbsp;<?php } ?>
	<?php if ($display=="list") { ?> <span class="Selected"><?php echo $lang["list"]?></span><?php } else { ?><a href="<?php echo $url?>&amp;display=list&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["list"]?></a><?php } ?> <?php hook("adddisplaymode"); ?> 
	<?php
	}
	?>
	</div> 
	<?php
	if ($display_selector_dropdowns && $recent_search_period_select && strpos($search,"!")===false && getvalescaped("recentdaylimit","")==""){?>
	<div class="InpageNavLeftBlock"><?php echo $lang["period"]?>:<br />
		<select class="medcomplementwidth ListDropdown" style="width:auto" id="resultsdisplay" name="resultsdisplay" onchange="CentralSpaceLoad(this.value,true);">
		<?php for($n=0;$n<count($recent_search_period_array);$n++){
			if ($display_selector_dropdowns){?>
				<option <?php if ($daylimit==$recent_search_period_array[$n]){?>selected="selected"<?php } ?> value="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>"><?php echo urlencode($results_display_array[$n])?>&amp;daylimit=<?php echo urlencode(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?></option>
			<?php } ?>
		<?php } ?>
		<option <?php if ($daylimit==""){?>selected="selected"<?php } ?> value="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>"><?php echo urlencode($results_display_array[$n])?>&amp;daylimit=<?php echo $lang["anyday"] ?></option>
		</select>
	</div>
	<?php } 
	
	# order by
	#if (strpos($search,"!")===false)
	if ($search!="!duplicates" && $search!="!unused" && !hook("replacesearchsortorder")) # Ordering enabled for collections/themes too now at the request of N Ward / Oxfam
		{
		$rel=$lang["relevance"];
		if(!hook("replaceasadded"))
			{
			if (isset($collection)){$rel=$lang["collection_order_description"];}
			elseif (strpos($search,"!")!==false && substr($search,0,11)!="!properties") {$rel=$lang["asadded"];}
			}

		$orderFields = array('relevance' => $rel);
		if ($random_sort)
			$orderFields['random'] = $lang['random'];
		if ($popularity_sort)
			$orderFields['popularity'] = $lang['popularity'];
		if ($orderbyrating)
			$orderFields['rating'] = $lang['rating'];
		if ($date_column)
			$orderFields['date'] = $lang['date'];
		if ($colour_sort)
			$orderFields['colour'] = $lang['colour'];
		if ($order_by_resource_id)
			$orderFields['resourceid'] = $lang['resourceid'];
		if ($order_by_resource_type)
			$orderFields['resourcetype'] = $lang['type'];

		# Add thumbs_display_fields to sort order links for thumbs views
		for ($x=0;$x<count($sf);$x++)
			{
			if (!isset($metadata_template_title_field)){$metadata_template_title_field=false;}
			if ($sf[$x]['ref']!=$metadata_template_title_field)
				{
				$orderFields['field' . $sf[$x]['ref']] = htmlspecialchars($sf[$x]['title']);
				}
			}

		$modifiedFields = hook('modifyorderfields', '', array($orderFields));
		if ($modifiedFields)
			$orderFields = $modifiedFields;
		?>
		<div id="searchSortOrderContainer" class="InpageNavLeftBlock ">
		<?php
		echo $lang["sortorder"] . ':<br />';

		if(!hook('render_sort_order_differently', '', array($orderFields)))
			{
			render_sort_order($orderFields);
			}

		hook('sortorder');
		?>
		</div>
		<?php
		}

		if($display_selector_dropdowns || $perpage_dropdown)
			{
			?>
			<div class="InpageNavLeftBlock"><?php echo ucfirst($lang['perpage']); ?>:
				<br />
				<select id="resultsdisplay" class="medcomplementwidth ListDropdown" style="width:auto" name="resultsdisplay" onchange="<?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this.value,true);">
			<?php
			for($n = 0; $n < count($results_display_array); $n++)
				{
				if($display_selector_dropdowns || $perpage_dropdown)
					{
					?>
					<option <?php if($per_page == $results_display_array[$n]) { ?>selected="selected"<?php } ?> value="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode($search); ?>&amp;order_by=<?php echo urlencode($order_by); ?>&amp;archive=<?php echo urlencode($archive); ?>&amp;k=<?php echo urlencode($k); ?>&amp;per_page=<?php echo urlencode($results_display_array[$n]); ?>&amp;sort=<?php echo urlencode($sort); ?>"><?php echo urlencode($results_display_array[$n]); ?></option>
					<?php
					}
				}
				?>
				</select>
			</div>
			<?php
			}

		if(!isset($collectiondata))
			{
			$collectiondata = array();
			}
		render_actions($collectiondata,true);

		hook("search_header_after_actions");
		
		if (!$display_selector_dropdowns && !$perpage_dropdown){?>
		<div class="InpageNavLeftBlock"><?php echo ucfirst($lang["perpage"]);?>:<br />
		<?php 
		for($n=0;$n<count($results_display_array);$n++){?>
		<?php if ($per_page==$results_display_array[$n]){?><span class="Selected"><?php echo urlencode($results_display_array[$n])?></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($results_display_array[$n])?>&amp;sort=<?php echo urlencode($sort)?>" onClick="return CentralSpaceLoad(this);"><?php echo urlencode($results_display_array[$n])?></a><?php } ?><?php if ($n>-1&&$n<count($results_display_array)-1){?>&nbsp;|<?php } ?>
		<?php } ?>
		</div>
		<?php } 
	
		if (!$display_selector_dropdowns && $recent_search_period_select && strpos($search,"!")===false && getvalescaped("recentdaylimit","")==""){?>
		<div class="InpageNavLeftBlock"><?php echo $lang["period"]?>:<br />
		<?php 
		for($n=0;$n<count($recent_search_period_array);$n++){
			if ($daylimit==$recent_search_period_array[$n]){?><span class="Selected"><?php echo htmlspecialchars(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?> </span>&nbsp;|&nbsp;<?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>&amp;daylimit=<?php echo urlencode($recent_search_period_array[$n])?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars(str_replace("?",$recent_search_period_array[$n],$lang["lastndays"]))?></a>&nbsp;|&nbsp;<?php } 
			}
		if ($daylimit==""){?><span class="Selected"><?php echo $lang["all"] ?></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;per_page=<?php echo urlencode($per_page)?>&amp;sort=<?php echo urlencode($sort)?>&amp;daylimit=" onClick="return CentralSpaceLoad(this);"><?php echo $lang["all"]?></a><?php } 
		?>				
		</div>
		<?php } ?>		
		
	<?php

		
	$results=count($result);
	$totalpages=ceil($results/$per_page);
	if ($offset>$results) {$offset=0;}
	$curpage=floor($offset/$per_page)+1;
	$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive) . "&amp;k=" . urlencode($k) . "&amp;restypes=" . urlencode($restypes);	
	?>
	</div>
	<?php hook("stickysearchresults"); ?> <!--the div TopInpageNavRight was added in after this hook so it may need to be adjusted -->
	<div class="TopInpageNavRight">
	<?php
	    pager();
		$draw_pager=true;
	?>
	</div>
	<div class="clearerleft"></div>
	</div>
	<?php
} 
		hook("stickysearchresults");

	if ($search_titles)
		{
		hook("beforesearchtitle");
		echo $search_title;
		hook("aftersearchtitle");
		hook("beforecollectiontoolscolumn");
		}
	
	hook("beforesearchresults");
	
	# Archive link
	if (($archive==0) && (strpos($search,"!")===false) && $archive_search) 
		{ 
		$arcresults=do_search($search,$restypes,$order_by,2,0);
		if (is_array($arcresults)) {$arcresults=count($arcresults);} else {$arcresults=0;}
		if ($arcresults>0) 
			{
			?>
			<div class="SearchOptionNav"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;archive=2" onClick="return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang["view"]?> <span class="Selected"><?php echo number_format($arcresults)?></span> <?php echo ($arcresults==1)?$lang["match"]:$lang["matches"]?> <?php echo $lang["inthearchive"]?></a></div>
			<?php 
			}
		else
			{
			?>
			<div class="InpageNavLeftBlock">&gt;&nbsp;<?php echo $lang["nomatchesinthearchive"]?></div>
			<?php 
			}
		}
	echo $search_title_links;
	hook("beforesearchresults2");
	hook("beforesearchresultsexpandspace");
	?>
	<div class="clearerleft"></div>
	<div id="CentralSpaceResources">
	<?php
	
	if ((!is_array($result) || count($result)<1) && empty($collections))
		{
			// No matches found? Log this in
			$key_id = resolve_keyword($search);

			if($key_id === FALSE) {
				$key_id = resolve_keyword($search, TRUE);
				daily_stat('Keyword usage', $key_id);
			}

			daily_stat("Keyword usage - no results found", $key_id);
		?>
		<div class="BasicsBox"> 
		  <div class="NoFind">
			<p><?php echo $lang["searchnomatches"]?></p>
			<?php
			if(!$collectionsearch) // Don't show hints if a collection search is empty 
				{
				if ($result!="" && !is_array($result))
					{
					?>
					<p><?php echo $lang["try"]?>: <a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode(strip_tags($result))?>"><?php echo stripslashes($result)?></a></p>
					<?php $result=array();
					}
				else
					{
					?>
					<p><?php if (strpos($search,"country:")!==false) { ?><p><?php echo $lang["tryselectingallcountries"]?> <?php } 
					elseif (strpos($search,"year:")!==false) { ?><p><?php echo $lang["tryselectinganyyear"]?> <?php } 
					elseif (strpos($search,"month:")!==false) { ?><p><?php echo $lang["tryselectinganymonth"]?> <?php } 
					else 		{?><?php echo $lang["trybeinglessspecific"]?><?php } ?> <?php echo $lang["enteringfewerkeywords"]?></p>
					<?php
					}
				hook("afterresulthints");
				}
		  ?>
		  </div>
		</div>
		<?php
		}

    $list_displayed = false;
    # Listview - Display title row if listview and if any result.
    if ($display=="list" && ((is_array($result) && count($result)>0) || (isset($collections) && is_array($collections) && count($collections)>0)))
        {
        $list_displayed = true;
		?>
		<div class="Listview">
		<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">

		<?php if(!hook("replacelistviewtitlerow")){?>	
		<tr class="ListviewTitleStyle">
		<?php if (!hook("listcheckboxesheader")){?>
		<?php if ($use_checkboxes_for_selection){?><td><?php echo $lang['addremove'];?></td><?php } ?>
		<?php } # end hook listcheckboxesheader 

		for ($x=0;$x<count($df);$x++)
			{?>
			<?php if ($order_by=="field".$df[$x]['ref']) {?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=field<?php echo $df[$x]['ref']?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars($df[$x]['title'])?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=field<?php echo $df[$x]['ref']?>&amp;sort=<?php echo urlencode($revsort)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo htmlspecialchars($df[$x]['title'])?></a></td><?php } ?>
			<?php }
		
		if ($display_user_rating_stars && ($k=="" || $internal_share_access) || hook("forceratingstarheading")){?><td><?php if ($order_by=="popularity") {?><span class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=popularity&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>&amp;sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["popularity"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></span><?php } else { ?><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;order_by=popularity&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["popularity"]?></a><?php } ?></td><?php } 
		if (isset($rating_field)){?><td>&nbsp;</td><!-- contains admin ratings --><?php }
		if ($id_column){?><?php if ($order_by=="resourceid"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourceid&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourceid&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["id"]?></a></td><?php } ?><?php } ?>
		<?php if ($resource_type_column){?><?php if ($order_by=="resourcetype"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourcetype&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["type"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=resourcetype&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["type"]?></a></td><?php } ?><?php } ?>
		<?php if ($list_view_status_column){?><?php if ($order_by=="status"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=status&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["status"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=status&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["status"]?></a></td><?php } ?><?php } ?>
		<?php if ($date_column){?><?php if ($order_by=="date"){?><td class="Selected"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=date&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["date"]?></a><div class="<?php echo urlencode($sort)?>">&nbsp;</div></td><?php } else { ?><td><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&amp;sort=<?php echo urlencode($revsort)?>&amp;order_by=date&amp;archive=<?php echo urlencode($archive) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["date"]?></a></td><?php } ?><?php } ?>
		<?php hook("addlistviewtitlecolumn");?>
		<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
		</tr>
		<?php } ?> <!--end hook replace listviewtitlerow-->
		<?php
		}
		# Include public collections and themes in the main search, if configured.		
		if ($offset==0 && isset($collections)&& strpos($search,"!")===false && $archive==0)
			{
			include "../include/search_public.php";
			}

	
	# work out common keywords among the results
	if ((count($result)>$suggest_threshold) && (strpos($search,"!")===false) && ($suggest_threshold!=-1))
		{
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]) {$refs[]=$result[$n]["ref"];} # add this to a list of results, for query refining later
			}
		$suggest=suggest_refinement($refs,$search);
		if (count($suggest)>0)
			{
			?><p><?php echo $lang["torefineyourresults"]?>: <?php
			for ($n=0;$n<count($suggest);$n++)
				{
				if ($n>0) {echo ", ";}
				?><a  href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo  urlencode(strip_tags($suggest[$n])) ?>" onClick="return CentralSpaceLoad(this);"><?php echo stripslashes($suggest[$n])?></a><?php
				}
			?></p><?php
			}
		}
		
	$rtypes=array();
	if (!isset($types)){$types=get_resource_types();}
	for ($n=0;$n<count($types);$n++) {$rtypes[$types[$n]["ref"]]=$types[$n]["name"];}
    if (is_array($result) && count($result)>0)
        {
        $showkeypreview = false;
        $showkeycollect = false;
        $showkeycollectout = false;
        $showkeyemail = false;
	$showkeyedit = false;
        $showkeystar = false;
        $showkeycomment = false;

        # loop and display the results
        for ($n=$offset;(($n<count($result)) && ($n<($offset+$per_page)));$n++)
            {
	    # Allow alternative configuration settings for this resource type.
	    resource_type_config_override($result[$n]["resource_type"]);
		
		if ($order_by=="resourcetype" && $display!="list")
			{
			if ($n==0 || ((isset($result[$n-1])) && $result[$n]["resource_type"]!=$result[$n-1]["resource_type"]))
				{
				echo "<h1 class=\"SearchResultsDivider\" style=\"clear:left;\">" . htmlspecialchars($rtypes[$result[$n]["resource_type"]]) .  "</h1>";
				}
			}
			
            $ref = $result[$n]["ref"];
	    
			$GLOBALS['get_resource_data_cache'][$ref] = $result[$n];
			$url = $baseurl_short."pages/view.php?ref=" . $ref . "&amp;search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=". urlencode($sort) . "&amp;offset=" . urlencode($offset) . "&amp;archive=" . urlencode($archive) . "&amp;k=" . urlencode($k) . "&amp;curpos=" . urlencode($n) . '&amp;restypes=' . urlencode($restypes);
			
			if ($result[$n]["access"]==0 && !checkperm("g") && !$internal_share_access)
				{
				# Resource access is open but user does not have the 'g' permission. Set access to restricted. If they have been granted specific access this will be added next
				$result[$n]["access"]=1; 
				}			
				
			// Check if user or group has been granted specific access level as set in array returned from do_search function. 
			if($result[$n]["user_access"]!="")
				{$result[$n]["access"]=$result[$n]["user_access"];}
			elseif ($result[$n]["group_access"]!="")
				{$result[$n]["access"]=$result[$n]["group_access"];}
			// Global $access needs to be set to check watermarks in search views (and may be used in hooks)		
			$access=$result[$n]["access"];
	    
            if (isset($result[$n]["url"])) {$url = $result[$n]["url"];} # Option to override URL in results, e.g. by plugin using process_Search_results hook above
 
            $rating = "";
            if (isset($rating_field)){$rating = "field".$rating_field;}
			hook("beforesearchviewcalls");
            if ($display=="thumbs")
                {
                #  ---------------------------- Thumbnails view ----------------------------
                include "search_views/thumbs.php";
                } 

            if ($display=="xlthumbs")
                {
                #  ---------------------------- X-Large Thumbnails view ----------------------------
                include "search_views/xlthumbs.php";
                }

            if ($display=="smallthumbs")
                {
                # ---------------- Small Thumbs view ---------------------
                include "search_views/smallthumbs.php";
                }

            if ($display=="list")
                {
                # ----------------  List view -------------------
                include "search_views/list.php";
                }

            if ($display=="stripes")
                {
                # ----------------  Stripes view -------------------
                include "search_views/stripes.php";
                }
		
            hook("customdisplaymode");

            }
        }
    # Listview - Add closing tag if a list is displayed.
    if ($list_displayed==true)
        {
        ?>
        </table>
        </div>
        <?php
        }
    else
        {
        # Display keys (only keys used in the current search view).
        if (!hook("replacesearchkey"))
            {
            if (is_array($result) && count($result)>0)
                { ?>
                <div class="BottomInpageKey"><?php
                    echo $lang["key"] . " ";
                    if ($showkeystar) { ?><div class="KeyStar"><?php echo $lang["verybestresources"]?></div><?php }
                    if ($showkeycomment) { ?><div class="KeyComment"><?php echo $lang["addorviewcomments"]?></div><?php }
                    if ($showkeyedit) { ?><div class="KeyEdit"><?php echo $lang["editresource"]?></div><?php }
		    if ($showkeyemail) { ?><div class="KeyEmail"><?php echo $lang["share-resource"]?></div><?php }
                    if ($showkeycollectout) { ?><div class="KeyCollectOut"><?php echo $lang["removefromcurrentcollection"]?></div><?php }
                    if ($showkeycollect) { ?><div class="KeyCollect"><?php echo $lang["addtocurrentcollection"]?></div><?php }
                    if ($showkeypreview) { ?><div class="KeyPreview"><?php echo $lang["fullscreenpreview"]?></div><?php }
                    hook("searchkey"); ?>
                </div><?php
                }
            } /* end hook replacesearchkey */
        }        
$url=$baseurl_short."pages/search.php?search=" . urlencode($search) . "&amp;order_by=" . urlencode($order_by) . "&amp;sort=" . urlencode($sort) . "&amp;archive=" . urlencode($archive) . "&amp;daylimit=" . urlencode($daylimit) . "&amp;k=" . urlencode($k) . "&amp;restypes=" . urlencode($restypes);	
?>
</div> <!-- end of CentralSpaceResources -->

<?php
if(!$modal)
    {
    ?>
    <!--Bottom Navigation - Archive, Saved Search plus Collection-->
    <div class="BottomInpageNav">
        <?php hook('add_bottom_in_page_nav_left'); ?>
        <div class="BottomInpageNavRight">	
        <?php 
        if (isset($draw_pager)) {pager(false);} 
        ?>
        </div>
        <div class="clearerleft"></div>
    </div>
	<?php
	}
} # End of replace all results hook conditional

hook("endofsearchpage");
if($search_anchors){ ?>
	<script>
		place='<?php echo getval("place","")?>';
		display='<?php echo $display?>';
		highlight='<?php echo $search_anchors_highlight?>';
		jQuery(document).ready(function(){
			if(place){
				ele_id='ResourceShell'+place;
				elementScroll = document.getElementById(ele_id);
				if(jQuery(elementScroll).length){
					elementScroll.scrollIntoView();
					if(highlight){
						jQuery(elementScroll).addClass("search-anchor");
					}
				}
			}
			
		});
	</script>
	<?php
}


include "../include/footer.php";

