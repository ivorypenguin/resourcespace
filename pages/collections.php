<?php
include_once dirname(__FILE__)."/../include/db.php";
include_once dirname(__FILE__)."/../include/general.php";
include_once dirname(__FILE__)."/../include/collections_functions.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("collection","",true),$k))) {include_once dirname(__FILE__)."/../include/authenticate.php";}
if (checkperm("b")){exit($lang["error-permissiondenied"]);}
include_once dirname(__FILE__)."/../include/research_functions.php";
include_once dirname(__FILE__)."/../include/resource_functions.php";
include_once dirname(__FILE__)."/../include/search_functions.php";
include_once dirname(__FILE__) . '/../include/render_functions.php';

$order_by=getvalescaped("order_by",$default_collection_sort);
$sort=getvalescaped("sort","DESC");
$search=getvalescaped("search","");
$last_collection=getval('last_collection','');
$restypes=getvalescaped('restypes','');
$archive=getvalescaped('archive','');
$daylimit=getvalescaped('daylimit','');
$offset=getvalescaped('offset','');
$resources_count=getvalescaped('resources_count','');

$change_col_url="search=" . urlencode($search). "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&restypes=" . urlencode($restypes) . "&archive=" .urlencode($archive) . "&daylimit=" . urlencode($daylimit) . "&offset=" . urlencode($offset) . "&resources_count=" . urlencode($resources_count);

// Set a flag for logged in users if $external_share_view_as_internal is set and logged on user is accessing an external share
$internal_share_access = ($k!="" && $external_share_view_as_internal && isset($is_authenticated) && $is_authenticated);

// copied from collection_manage to support compact style collection adds (without redirecting to collection_manage)
$addcollection=getvalescaped("addcollection","");
if ($addcollection!="")
	{
	# Add someone else's collection to your My Collections
	add_collection($userref,$addcollection);
	set_user_collection($userref,$addcollection);
	refresh_collection_frame();
	
   	# Log this
	daily_stat("Add public collection",$userref);
	}
/////

#Remove all from collection
$emptycollection = getvalescaped("emptycollection","",true);
if($emptycollection!='' && getvalescaped("submitted","")=='removeall' && getval("removeall","")!="" && collection_writeable($emptycollection))
    {
    remove_all_resources_from_collection($emptycollection);
    }
    
# Disable checkboxes for external users.
if ($k!="" && !$internal_share_access) {$use_checkboxes_for_selection=false;}

if(!isset($thumbs))
    {
    $thumbs=getval("thumbs","unset");
    if($thumbs == "unset")
        {
        $thumbs = $thumbs_default;
        rs_setcookie("thumbs", $thumbs, 1000,"","",false,false);
        }
    }

# Basket mode? - this is for the e-commerce user request modes.
if ($userrequestmode==2 || $userrequestmode==3)
	{
	# Enable basket
	$basket=true;	
	}
else
	{
	$basket=false;
	}

$collection=getvalescaped("collection","",true);
$entername=getvalescaped("entername","");
		
# ------------ Change the collection, if a collection ID has been provided ----------------
if ($collection!="")
	{
	hook("prechangecollection");
	#change current collection
	
	if (($k=="" || $internal_share_access) && $collection==-1)
		{
		# Create new collection
		if ($entername!=""){ $name=$entername;} 
		else { $name=get_mycollection_name($userref);}
		$new=create_collection ($userref,$name);
		set_user_collection($userref,$new);
		
		# Log this
		daily_stat("New collection",$userref);
		}
	elseif(!isset($usercollection) || $collection!=$usercollection)
		{
                $validcollection=sql_value("select ref value from collection where ref='$collection'",0);
                # Switch the existing collection
		if ($k=="" || $internal_share_access) {set_user_collection($userref,$collection);}
		$usercollection=$collection;
		}

	hook("postchangecollection");
	}

// Load collection info. 
// get_user_collections moved before output as function may set cookies
$cinfo=get_collection($usercollection);
$list=get_user_collections($userref);

# if the old collection or new collection is being displayed as search results, we'll need to update the search actions so "save results to this collection" is properly displayed
if(substr($search, 0, 11) == '!collection' && ($k == '' || $internal_share_access))
	{ 
	# Extract the collection number - this bit of code might be useful as a function
    $search_collection = explode(' ', $search);
    $search_collection = str_replace('!collection', '', $search_collection[0]);
    $search_collection = explode(',', $search_collection); // just get the number
    $search_collection = escape_check($search_collection[0]);
    if($search_collection==$last_collection || ($last_collection!=='' && $search_collection==$usercollection))
    	{
        ?>
        <script>        	
        	jQuery('.ActionsContainer.InpageNavLeftBlock').load(baseurl + "/pages/ajax/update_search_actions.php?<?php echo $change_col_url?>&collection=<?php echo $search_collection?>", function() {
    			jQuery(this).children(':first').unwrap();
			});
        </script>
        <?php
        }
    }
	


# Check to see if the user can edit this collection.
$allow_reorder=false;
if (($k=="" || $internal_share_access) && (($userref==$cinfo["user"]) || ($cinfo["allow_changes"]==1) || (checkperm("h"))))
	{
	$allow_reorder=true;
	}	
	
# Reordering capability
if ($allow_reorder)
	{
	# Also check for the parameter and reorder as necessary.
	$reorder=getvalescaped("reorder",false);
	if ($reorder)
		{
		$neworder=json_decode(getvalescaped("order",false));
		update_collection_order($neworder,$usercollection);
		exit("SUCCESS");
		}
	}


# Include function for reordering
if ($allow_reorder)
	{
	?>
	<script type="text/javascript">
		function ReorderResourcesInCollection(idsInOrder) {
			var newOrder = [];
			jQuery.each(idsInOrder, function() {
				newOrder.push(this.substring(13));
				}); 
			
			jQuery.ajax({
			  type: 'POST',
			  url: '<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo urlencode($usercollection) ?>&search=<?php echo urlencode($search)?>&reorder=true',
			  data: {order:JSON.stringify(newOrder)},
			  success: function() {
			    var results = new RegExp('[\\?&amp;]' + 'search' + '=([^&amp;#]*)').exec(window.location.href);
			    var ref = new RegExp('[\\?&amp;]' + 'ref' + '=([^&amp;#]*)').exec(window.location.href);
			    if ((ref==null)&&(results!== null)&&('<?php echo urlencode("!collection" . $usercollection); ?>' === results[1])) CentralSpaceLoad('<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>',true);
			  }
			});		
		}
		jQuery(document).ready(function() {
			if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {
					return false;
				}

			jQuery('#CollectionSpace').sortable({
				distance: 50,
				connectWith: '#CentralSpaceResources',
				appendTo: 'body',
				zIndex: 99000,
				helper: function(event, ui)
					{
					//Hack to append the element to the body (visible above others divs), 
					//but still bellonging to the scrollable container
					jQuery('#CollectionSpace').append('<div id="CollectionSpaceClone" class="ui-state-default">' + ui[0].outerHTML + '</div>');   
					jQuery('#CollectionSpaceClone').hide();
					setTimeout(function() {
						jQuery('#CollectionSpaceClone').appendTo('body'); 
						jQuery('#CollectionSpaceClone').show();
					}, 1);
					
					return jQuery('#CollectionSpaceClone');
					},
				items: '.CollectionPanelShell',

				start: function (event, ui)
					{
					InfoBoxEnabled=false;
					if (jQuery('#InfoBoxCollection')) {jQuery('#InfoBoxCollection').hide();}
					jQuery('#trash_bin').show();
					},

				stop: function(event, ui)
					{
					InfoBoxEnabled=true;
					var idsInOrder = jQuery('#CollectionSpace').sortable("toArray");
					ReorderResourcesInCollection(idsInOrder);
					jQuery('#trash_bin').hide();
					}
			});
			jQuery('.CollectionPanelShell').disableSelection();
		});
		
		
	</script>
<?php } 
else { ?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
			jQuery('.ui-sortable').sortable('disable');
			jQuery('.CollectionPanelShell').enableSelection();			
		});	
	</script>
	<?php } 
	hook("responsivethumbsloaded");

?>
	<!-- Drag and Drop -->
	<script>
		jQuery('#CentralSpace').on('prepareTrash', function() {
			jQuery('#CollectionDiv').droppable({
				accept: '.ResourcePanelShell, .ResourcePanelShellSmall, .ResourcePanelShellLarge',

				drop: function(event, ui)
					{
					var query_strings = getQueryStrings();
					if(is_special_search('!collection', 11) && !is_empty(query_strings) && query_strings.search.substring(11) == usercollection)
						{
						// No need to re-add this resource since we are looking at the same collection in both CentralSpace and CollectionDiv
						return false;
						}

					var resource_id = jQuery(ui.draggable).attr("id");
					resource_id = resource_id.replace('ResourceShell', '');

					jQuery('#trash_bin').hide();
					AddResourceToCollection(event, resource_id, '');
					}
			});

			jQuery('#trash_bin').droppable({
				accept: '.CollectionPanelShell, .ResourcePanelShell, .ResourcePanelShellSmall, .ResourcePanelShellLarge',
				activeClass: "ui-state-hover",
				hoverClass: "ui-state-active",

				drop: function(event, ui) {
					var resource_id = jQuery(ui.draggable).attr("id");
					resource_id = resource_id.replace('ResourceShell', '');

					jQuery('#trash_bin').hide();

					// Cancel re-order in case it was triggered
					if(jQuery('#CentralSpace').hasClass('ui-sortable'))
						{
						jQuery('#CentralSpace').sortable('cancel');
						}
					if(jQuery('#CollectionSpace').hasClass('ui-sortable'))
						{
						jQuery('#CollectionSpace').sortable('cancel');
						}

					jQuery('#trash_bin_delete_dialog').dialog({
						title:'<?php echo $lang["trash_bin_delete_dialog_title"]; ?>',
						autoOpen: false,
						modal: true,
						resizable: false,
						dialogClass: 'delete-dialog no-close',
						buttons: {
							// Confirm removal of this resource from current collection
							"<?php echo $lang['yes']; ?>": function() {
								var query_strings = getQueryStrings();
								if(is_empty(query_strings))
									{
									console.error('RS_debug: query_strings returned an empty object. Search param was expected to get the collection ID in order to remove the resource from the collection using Drag & Drop.');
									jQuery(this).dialog('close');
									}
								var collection_id = query_strings.search.substring(11);

								RemoveResourceFromCollection(event, resource_id, '<?php echo $pagename; ?>', collection_id);
								jQuery('#ResourceShell' + resource_id).fadeOut();
								jQuery(this).dialog('close');
							},
							// Cancel action
							"<?php echo $lang['no']; ?>": function() {
								jQuery(this).dialog('close');
							}
						}
					});

					// Only show confirmation dialog when resource is being dragged from top (ie. CentralSpace)
					if(ui.draggable.attr('class') === 'CollectionPanelShell')
						{
						// Handle different cases such as Saved searches
						if(ui.draggable.data('savedSearch') === 'yes')
							{
							CollectionDivLoad('<?php echo $baseurl; ?>/pages/collections.php?removesearch=' + resource_id + '&nc=<?php echo time(); ?>');
							}
						else
							{
							RemoveResourceFromCollection(event, resource_id, '<?php echo $pagename; ?>');
							}
						}
					else
						{
						jQuery('#trash_bin_delete_dialog').dialog('open');
						}
				}
			});
		});

		jQuery(document).ready(function() {
			jQuery('#CentralSpace').trigger('prepareTrash');
		});
	</script>
	<!-- End of Drag and Drop -->
	<style>
	#CollectionMenuExp
		{
		height:<?php echo $collection_frame_height-15?>px;
		<?php if ($remove_collections_vertical_line){?>border-right: 0px;<?php }?>
		}
	</style>

	<?php hook("headblock");?>

	</head>

	<body class="CollectBack" id="collectbody">
<div style="display:none;" id="currentusercollection"><?php echo $usercollection?></div>

<script>usercollection='<?php echo htmlspecialchars($usercollection) ?>';</script>
<?php 

$add=getvalescaped("add","");
if ($add!="")
	{
	$allowadd=true;
	// If we provide a collection ID use that one instead
	$to_collection = getvalescaped('toCollection', '');

	if(checkperm("noex"))
		{
		// If collection has been shared externally users with this permission can't add resources
		$externalkeys=get_collection_external_access(($to_collection === '') ? $usercollection : $to_collection);
		if(count($externalkeys)>0)
				{
				$allowadd=false;				
				?>
				<script language="Javascript">alert("<?php echo $lang["sharedcollectionaddblocked"]?>");</script>
				<?php
				}
		}
	if($allowadd)
		{
		if(strpos($add,",")>0)
			{
			$addarray=explode(",",$add);
			}
		else
			{
			$addarray[0]=$add;
			unset($add);
			}	
		foreach ($addarray as $add)
			{
			hook("preaddtocollection");
			#add to current collection		
			if (add_resource_to_collection($add,($to_collection === '') ? $usercollection : $to_collection,false,getvalescaped("size",""))==false)
				{ ?>
				<script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
				}
			else
				{
				# Log this	
				daily_stat("Add resource to collection",$add);
			
				# Update resource/keyword kit count
				if ((strpos($search,"!")===false) && ($search!="")) {update_resource_keyword_hitcount($add,$search);}
				hook("postaddtocollection");
				}
			}
		# Show warning?
		if (isset($collection_share_warning) && $collection_share_warning)
			{
			?><script language="Javascript">alert("<?php echo $lang["sharedcollectionaddwarning"]?>");</script><?php
			}
		}
	}

$remove=getvalescaped("remove","");
if ($remove!="")
	{
	// If we provide a collection ID use that one instead
	$from_collection = getvalescaped('fromCollection', '');

	if(strpos($remove,",")>0)
		{
		$removearray=explode(",",$remove);
		}
	else
		{
		$removearray[0]=$remove;
		unset($remove);
		}	
	foreach ($removearray as $remove)
		{
		hook("preremovefromcollection");
		#remove from current collection
		if (remove_resource_from_collection($remove, ($from_collection === '') ? $usercollection : $from_collection) == false)
			{
			?><script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
			}
		else
			{
			# Log this	
			daily_stat("Removed resource from collection",$remove);		
			hook("postremovefromcollection");
			}
		}
	}
	
$addsearch=getvalescaped("addsearch",-1);
if ($addsearch!=-1)
	{
    if (!collection_writeable($usercollection))
        { ?>
        <script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
        }
    else
        {
        hook("preaddsearch");
		if(checkperm("noex"))
				{
				// If collection has been shared externally user can't add resources
				?>
				<script language="Javascript">alert("<?php echo $lang["sharedcollectionaddblocked"]?>");</script><?php
				}
        if (getval("mode","")=="")
            {
            #add saved search
            add_saved_search($usercollection);

            # Log this
            daily_stat("Add saved search to collection",0);
            }
        else
            {
            #add saved search (the items themselves rather than just the query)
            $resourcesnotadded=add_saved_search_items($usercollection);
            if (!empty($resourcesnotadded))
                {
                ?><script language="Javascript">alert("<?php echo $lang["notapprovedresources"] . implode(", ",$resourcesnotadded);?>");</script><?php
                }
            # Log this
            daily_stat("Add saved search items to collection",0);
            }
        hook("postaddsearch");
        }
	}

$removesearch=getvalescaped("removesearch","");
if ($removesearch!="")
	{
    if (!collection_writeable($usercollection))
        { ?>
        <script language="Javascript">alert("<?php echo $lang["cantmodifycollection"]?>");</script><?php
        }
    else
        {
        hook("preremovesearch");
        #remove saved search
        remove_saved_search($usercollection,$removesearch);
        hook("postremovesearch");
        }
	}
	
$addsmartcollection=getvalescaped("addsmartcollection",-1);
if ($addsmartcollection!=-1)
	{
	
	# add collection which autopopulates with a saved search 
	add_smart_collection();
		
	# Log this
	daily_stat("Added smart collection",0);	
	}
	
$research=getvalescaped("research","");
if ($research!="")
	{
	hook("preresearch");
	$col=get_research_request_collection($research);
	if ($col==false)
		{
		$rr=get_research_request($research);
		$name="Research: " . $rr["name"];  # Do not translate this string, the collection name is translated when displayed!
		$new=create_collection ($rr["user"],$name,1);
		set_user_collection($userref,$new);
		set_research_collection($research,$new);
		}
	else
		{
		set_user_collection($userref,$col);
		}
	hook("postresearch");
	}
	
hook("processusercommand");
?>


<?php 
$searches=get_saved_searches($usercollection);

// Do an initial count of how many resources there are in the collection (only returning ref and archive)
$results_all=do_search("!collection" . $usercollection,"","relevance",0,-1,"desc",false,0,false,false,"",false,true,true);
$count_result=count($results_all);

// Then do another pass getting all data for the maximum allowed collection thumbs
$result=do_search("!collection" . $usercollection,"","relevance",0,$max_collection_thumbs,"desc");

$hook_count=hook("countresult","",array($usercollection,$count_result));if (is_numeric($hook_count)) {$count_result=$hook_count;} # Allow count display to be overridden by a plugin (e.g. that adds it's own resources from elsewhere e.g. ResourceConnect).
$feedback=$cinfo["request_feedback"];



# E-commerce functionality. Work out total price, if $basket_stores_size is enabled so that they've already selected a suitable size.
$totalprice=0;
if (($userrequestmode==2 || $userrequestmode==3) && $basket_stores_size)
	{
	foreach ($result as $resource)
		{
		# For each resource in the collection, fetch the price (set in config.php, or config override for group specific pricing)
		$id=$resource["purchase_size"];
		if ($id=="") {$id="hpr";} # Treat original size as "hpr".
		if (array_key_exists($id,$pricing))
			{
			$price=$pricing[$id];
			
			# Pricing adjustment hook (for discounts or other price adjustments plugin).
			$priceadjust=hook("adjust_item_price","",array($price,$resource["ref"],$resource["purchase_size"]));
			if ($priceadjust!==false)
				{
				$price=$priceadjust;
				}
			
			$totalprice+=$price;
			}
		else
			{
			$totalprice+=999; # Error.
			}
		}
	}

if(!hook("clearmaincheckboxesfromcollectionframe")){
	if ($use_checkboxes_for_selection ){?>
	
	<script type="text/javascript">
	var checkboxes=jQuery('input.checkselect');
	//clear all
	checkboxes.each(function(box){
		jQuery(checkboxes[box]).attr('checked',false);
		jQuery(checkboxes[box]).change();
	});
	</script>
<?php }
} // end hook clearmaincheckboxesfromcollectionframe

if(!hook("updatemaincheckboxesfromcollectionframe")){
		
	if ($use_checkboxes_for_selection){?>
	<script type="text/javascript"><?php
	# update checkboxes in main window
	for ($n=0;$n<count($result);$n++)			
		{
		$ref=$result[$n]["ref"];
		?>
		if (jQuery('#check<?php echo htmlspecialchars($ref) ?>')){
		jQuery('#check<?php echo htmlspecialchars($ref) ?>').attr('checked',true);
		}
			
	<?php }
	} ?></script><?php
}# end hook updatemaincheckboxesfromcollectionframe

?><div><?php

if (true) { // draw both

?><div id="CollectionMaxDiv" style="display:<?php if ($thumbs=="show") { ?>block<?php } else { ?>none<?php } ?>"><?php 
# ---------------------------- Maximised view -------------------------------------------------------------------------
if (hook("replacecollectionsmax", "", array($k!="")))
	{
	# ------------------------ Hook defined view ----------------------------------
	}
else if ($basket)
	{
	# ------------------------ Basket Mode ----------------------------------------
	?>
	<div id="CollectionMenu">
	<h2><?php echo $lang["yourbasket"] ?></h2>
	<form action="<?php echo $baseurl_short?>pages/purchase.php">

	<?php if ($count_result==0) { ?>
	<p><br /><?php echo $lang["yourbasketisempty"] ?></p><br /><br /><br />
	<?php } else { ?>
	<p><br /><?php if ($count_result==1) {echo $lang["yourbasketcontains-1"];} else {echo str_replace("%qty",$count_result,$lang["yourbasketcontains-2"]);} ?>

	<?php if ($basket_stores_size) {
	# If they have already selected the size, we can show a total price here.
	?><br/><?php echo $lang["totalprice"] ?>: <?php echo $currency_symbol . " " . number_format($totalprice,2) ?><?php } ?>
	
	</p>

	<p style="padding-bottom:10px;"><input type="submit" name="buy" value="&nbsp;&nbsp;&nbsp;<?php echo $lang["buynow"] ?>&nbsp;&nbsp;&nbsp;" /></p>
	<?php } ?>
	<?php if (!$disable_collection_toggle) { ?>
    <a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo LINK_CARET ?><?php echo $lang["hidethumbnails"]?></a>
  <?php } ?>
	<a href="<?php echo $baseurl_short?>pages/purchases.php" onclick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["viewpurchases"]?></a>


	</form>
	</div>
	<?php	
	}
elseif ($k!="" && !$internal_share_access)
	{
	# ------------- Anonymous access, slightly different display ------------------
	$tempcol=$cinfo;
	?>
<div id="CollectionMenu">
  <h2><?php echo i18n_get_collection_name($tempcol)?></h2>
	<br />
	<div class="CollectionStatsAnon">
	<?php echo $lang["created"] . " " . nicedate($tempcol["created"])?><br />
  	<?php echo $count_result . " " . $lang["youfoundresources"]?><br />
  	</div>
    <?php
	if ($download_usage && ((isset($zipcommand) || $collection_download) && $count_result>0)) { ?>
		<a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/download_usage.php?collection=" .  $usercollection . "&k=" . $k)?>"><?php echo LINK_CARET ?><?php echo $lang["action-download"]?></a>
	<?php } else if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
	<a href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>" onclick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["action-download"]?></a>
	<?php }
     if ($feedback) {?><br /><br /><a onclick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo LINK_CARET ?><?php echo $lang["sendfeedback"]?></a><?php } ?>
    <?php if ($count_result>0 && checkperm("q"))
    	{ 
		# Ability to request a whole collection (only if user has restricted access to any of these resources)
		$min_access=collection_min_access($result);
		if ($min_access!=0)
			{
		    ?>
		    <br/><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo LINK_CARET ?><?php echo $lang["requestall"]?></a>
		    <?php
		    }
	    }
	?>
	<?php if (!$disable_collection_toggle) { ?>
    <br/><a  id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["hidethumbnails"]?></a>
  <?php } ?>
</div>
<?php 
} else { 
# -------------------------- Standard display --------------------------------------------
?>
<?php if ($collection_dropdown_user_access_mode){?>
<div id="CollectionMenuExp">
<?php } else { ?>
<div id="CollectionMenu">
<?php } ?>

<?php if (!hook("thumbsmenu")) { ?>
  <?php if (!hook("replacecollectiontitle") && !hook("replacecollectiontitlemax")) { ?><h2 id="CollectionsPanelHeader"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_manage.php"><?php echo $lang["mycollections"]?></a></h2><?php } ?>
  <form method="get" id="colselect" onsubmit="newcolname=encodeURIComponent(jQuery('#entername').val());CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?collection=-1&search=<?php echo urlencode($search)?>&k=<?php echo urlencode($k) ?>&entername='+newcolname);return false;">
		<div class="SearchItem" style="padding:0;margin:0;"><?php echo $lang["currentcollection"]?>&nbsp;(<strong><?php echo $count_result?></strong>&nbsp;<?php if ($count_result==1){echo $lang["item"];} else {echo $lang["items"];}?>): 
		<select name="collection" id="collection" onchange="if(document.getElementById('collection').value==-1){document.getElementById('entername').style.display='block';document.getElementById('entername').focus();return false;} <?php if (!checkperm("b")){ ?>ChangeCollection(jQuery(this).val(),'<?php echo urlencode($k)  ?>','<?php echo urlencode($usercollection) ?>','<?php echo $change_col_url?>');<?php } else { ?>document.getElementById('colselect').submit();<?php } ?>" <?php if ($collection_dropdown_user_access_mode){?>class="SearchWidthExp"<?php } else { ?> class="SearchWidth"<?php } ?>>
		<?php
		$found=false;
		for ($n=0;$n<count($list);$n++)
			{
			if(in_array($list[$n]['ref'],$hidden_collections)){continue;}

            if ($collection_dropdown_user_access_mode){    
                $colusername=$list[$n]['fullname'];
                
                # Work out the correct access mode to display
                if (!hook('collectionaccessmode')) {
                    if ($list[$n]["public"]==0){
                        $accessmode= $lang["private"];
                    }
                    else{
                        if (strlen($list[$n]["theme"])>0){
                            $accessmode= $lang["theme"];
                        }
                    else{
                            $accessmode= $lang["public"];
                        }
                    }
                }
            }
                

			#show only active collections if a start date is set for $active_collections 
			if (strtotime($list[$n]['created']) > ((isset($active_collections))?strtotime($active_collections):1))
					{ ?>
			<option value="<?php echo $list[$n]["ref"]?>" <?php if ($usercollection==$list[$n]["ref"]) {?> 	selected<?php $found=true;} ?>><?php echo i18n_get_collection_name($list[$n]) ?> <?php if ($collection_dropdown_user_access_mode){echo htmlspecialchars("(". $colusername."/".$accessmode.")"); } ?></option>
			<?php }
			}
		if ($found==false)
			{
			# Add this one at the end, it can't be found
			$notfound=$cinfo;
			if ($notfound!==false)
				{
				?>
				<option selected><?php echo i18n_get_collection_name($notfound) ?></option>
				<?php
				}
                        elseif($validcollection==0)
                            {
                            ?>
                            <option selected><?php echo $lang["error-collectionnotfound"] ?></option>
                            <?php  
                            }
			}
		
		if ($collection_allow_creation) { ?>
			<option value="-1">(<?php echo $lang["createnewcollection"]?>)</option>
		<?php } ?>

		</select>
		<input type=text id="entername" name="entername" style="display:none;" placeholder="<?php echo $lang['entercollectionname']?>" <?php if ($collection_dropdown_user_access_mode){?>class="SearchWidthExp"<?php } else { ?> class="SearchWidth"<?php } ?>>
		</div>			
  </form>

	<?php
	// Render dropdown actions
	hook("beforecollectiontoolscolumn");

    $resources_count = $count_result;
	render_actions($cinfo, false,true,'',$results_all);
    hook("aftercollectionsrenderactions");
	?>
 	<ul>
	<?php
	hook('collectiontool');
	if(!$disable_collection_toggle)
		{
		?>
		<li>
			<a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang['hidethumbnails']; ?></a>
		</li>
			<?php
			}
			?>
	</ul>
</div>
<?php
}
} ?>

<!--Resource panels-->
<?php if ($collection_dropdown_user_access_mode){?>
<div id="CollectionSpace" class="CollectionSpaceExp">
<?php } else { ?>
<div id="CollectionSpace" class="CollectionSpace">
<?php } ?>

<?php 
# Loop through saved searches
if (isset($cinfo['savedsearch'])&&$cinfo['savedsearch']==null  && ($k=='' || $internal_share_access))
	{ // don't include saved search item in result if this is a smart collection  

	# Setting the save search icon
	$folderurl=$baseurl."/gfx/images/";
	$iconurl=$folderurl."save-search"."_".$language.".gif";
	if (!file_exists($iconurl))
		{
		# A language specific icon is not found, use the default icon
		$iconurl = $folderurl . "save-search.gif";
		}

	for ($n=0;$n<count($searches);$n++)			
		{
		$ref=$searches[$n]["ref"];
		$url=$baseurl_short."pages/search.php?search=" . urlencode($searches[$n]["search"]) . "&restypes=" . urlencode($searches[$n]["restypes"]) . "&archive=" . urlencode($searches[$n]["archive"]);
		?>
		<!--Resource Panel-->
		<div id="ResourceShell<?php echo $searches[$n]['ref']; ?>" class="CollectionPanelShell" data-saved-search="yes">
		<table border="0" class="CollectionResourceAlign"><tr><td>
		<a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $url?>"><img border=0 width=56 height=75 src="<?php echo $iconurl?>"/></a></td>
		</tr></table>
		<?php if(!hook('replacesavedsearchtitle')){?>
		<div class="CollectionPanelInfo"><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $url?>"><?php echo tidy_trim($lang["savedsearch"],(13-strlen($n+1)))?> <?php echo $n+1?></a>&nbsp;</div><?php } ?>
		<?php if(!hook('replaceremovelink_savedsearch')){?>
		<div class="CollectionPanelInfo"><a onclick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?removesearch=<?php echo urlencode($ref) ?>&nc=<?php echo time()?>">x <?php echo $lang["action-remove"]?>
		</a></div>	<?php } ?>			
		</div>
		<?php		
		}
}		

# Loop through thumbnails
if ($count_result>0) 
	{
	if($count_result>$max_collection_thumbs){$results_count=$max_collection_thumbs;}
	else{$results_count=count($result);}
	# loop and display the results
	for ($n=0;$n<$results_count;$n++)					
		{
		$ref=$result[$n]["ref"];
		?>
<?php if (!hook("resourceview")) { ?>
		<!--Resource Panel-->
		<div class="CollectionPanelShell" id="ResourceShell<?php echo urlencode($ref) ?>">
		<?php if (!hook("rendercollectionthumb")){?>
		<?php $access=get_resource_access($result[$n]);
		$use_watermark=check_use_watermark();?>
		<table border="0" class="CollectionResourceAlign"><tr><td>
		<a style="position:relative;" onclick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode("!collection" . $usercollection)?>&k=<?php echo $k?>&curpos=<?php echo $n ?>"><?php if ($result[$n]["has_image"]==1) { 
		
		$colimgpath=get_resource_path($ref,false,"col",false,$result[$n]["preview_extension"],-1,1,$use_watermark,$result[$n]["file_modified"])
		?>
		<img border=0 src="<?php echo $colimgpath?>" class="CollectImageBorder" title="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>" alt="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>" />
			<?php
		
		} else { ?><img border=0 src="<?php echo $baseurl_short?>gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],true) ?>" /><?php } ?><?php hook("aftersearchimg","",array($result[$n]))?></a></td>
		</tr></table>
		<?php } /* end hook rendercollectionthumb */?>
		
		<?php 

		$title=$result[$n]["field".$view_title_field];	
		$title_field=$view_title_field;
		if (isset($metadata_template_title_field) && isset($metadata_template_resource_type))
			{
			if ($result[$n]['resource_type']==$metadata_template_resource_type)
				{
				$title=$result[$n]["field".$metadata_template_title_field];
				$title_field=$metadata_template_title_field;
				}	
			}	
		$field_type=sql_value("select type value from resource_type_field where ref=$title_field","");
		if($field_type==8){
			$title=strip_tags($title);
			$title=str_replace("&nbsp;"," ",$title);
		}
		?>	
		<?php if (!hook("replacecolresourcetitle")){?>
		<div class="CollectionPanelInfo"><a onclick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode("!collection" . $usercollection)?>&k=<?php echo urlencode($k) ?>" title="<?php echo htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))?>"><?php echo htmlspecialchars(tidy_trim(i18n_get_translated($title),14));?></a>&nbsp;</div>
		<?php } ?>
		
		<?php if ($k!="" && $feedback) { # Allow feedback for external access key users
		?>
		<div class="CollectionPanelInfo">
		<span class="IconComment <?php if ($result[$n]["commentset"]>0) { ?>IconCommentAnim<?php } ?>"><a onclick="return ModalLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_comment.php?ref=<?php echo urlencode($ref) ?>&collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="" width="14" height="12" /></a></span>		
		</div>
		<?php } ?>
	
		<?php if ($k=="" || $internal_share_access) { ?><div class="CollectionPanelInfo">
		<?php if (($feedback) || (($collection_reorder_caption || $collection_commenting))) { ?>
		<span class="IconComment <?php if ($result[$n]["commentset"]>0) { ?>IconCommentAnim<?php } ?>"><a onclick="return ModalLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_comment.php?ref=<?php echo urlencode($ref) ?>&collection=<?php echo urlencode($usercollection) ?>"><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="" width="14" height="12" /></a></span>		
		<?php } ?>

		<?php if (!isset($cinfo['savedsearch'])||(isset($cinfo['savedsearch'])&&$cinfo['savedsearch']==null)){ // add 'remove' link only if this is not a smart collection 
			?>
		<?php if (!hook("replaceremovelink")){?>
		<a class="CollectionResourceRemove" onclick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?remove=<?php echo urlencode($ref) ?>&nc=<?php echo time()?>"><i class="fa fa-minus-circle"></i> <?php echo $lang["action-remove"]?></a>
		<?php
				} //end hook replaceremovelink 
			} # End of remove link condition 
		?></div><?php 
		} # End of k="" condition 
		 ?>
		</div>
		<?php
		} # End of ResourceView hook
	  } # End of loop through resources
	?>
	<div class="clearerleft"></div>
	<?php
	} # End of results condition

	
if($count_result > $max_collection_thumbs)
	{
	?>
	<div class="CollectionPanelShell">
		<table border="0" class="CollectionResourceAlign">
			<tr>
				<td><img/></td>
			</tr>
		</table>
		<div class="CollectionPanelInfo">
			<a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $usercollection?>&k=<?php echo urlencode($k) ?>"><?php echo $lang['viewall']?>...</a>
		</div>
	</div>
	<?php
	}

?></div><?php		
# Plugin for additional collection listings	(deprecated)
if (file_exists("plugins/collection_listing.php")) {include "plugins/collection_listing.php";}

hook("thumblistextra");
?>
</div>  
<?php 

}


	?><div id="CollectionMinDiv" style="display:<?php if ($thumbs=="hide") { ?>block<?php } else { ?>none<?php } ?>"><?php 
	if (true)
	{
	# ------------------------- Minimised view
	?>
	<!--Title-->	
	<?php if (!hook("nothumbs")) {

	if (hook("replacecollectionsmin", "", array($k!="")))
		{
		# ------------------------ Hook defined view ----------------------------------
		}
	else if ($basket)
		{
		# ------------------------ Basket Mode ----------------------------------------
		?>
		<div id="CollectionMinTitle"><h2><?php echo $lang["yourbasket"] ?></h2></div>
		<div id="CollectionMinRightNav">
		<form action="<?php echo $baseurl_short?>pages/purchase.php">
		<ul>
		
		<?php if ($count_result==0) { ?>
		<li><?php echo $lang["yourbasketisempty"] ?></li>
		<?php } else { ?>

		<?php if ($basket_stores_size) {
		# If they have already selected the size, we can show a total price here.
		?><li><?php echo $lang["totalprice"] ?>: <?php echo $currency_symbol . " " . number_format($totalprice,2) ?><?php } ?></li>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection)?>"><?php echo $lang["viewall"]?></a></li>
		<li><input type="submit" name="buy" value="&nbsp;&nbsp;&nbsp;<?php echo $lang["buynow"] ?>&nbsp;&nbsp;&nbsp;" /></li>
		<?php } ?>
	  <?php if (!$disable_collection_toggle) { ?>
		<?php /*if ($count_result<=$max_collection_thumbs) { */?><li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["showthumbnails"]?></a></li><?php /*}*/ ?>
	  <?php } ?>
		<li><a href="<?php echo $baseurl_short?>pages/purchases.php" onclick="return CentralSpaceLoad(this,true);"><?php echo $lang["viewpurchases"]?></a></li>
		</ul>
		</form>

		</div>
		<?php	
		} // end of Basket Mode
	elseif ($k!="")
		{
		# Anonymous access, slightly different display
		$tempcol=$cinfo;
		?>
	<div id="CollectionMinTitle" class="ExternalShare"><h2><?php echo i18n_get_collection_name($tempcol)?></h2></div>
	<div id="CollectionMinRightNav" class="ExternalShare">
		<?php if(!hook("replaceanoncollectiontools")){ ?>
		<?php if ((isset($zipcommand) || $collection_download) && $count_result>0) { ?>
		<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k) ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k)?>"><?php echo $lang["action-download"]?></a></li>
		<?php } ?>
		<?php if ($feedback) {?><li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo $lang["sendfeedback"]?></a></li><?php } ?>
		<?php if ($count_result>0)
			{ 
			# Ability to request a whole collection (only if user has restricted access to any of these resources)
			$min_access=collection_min_access($result);
			if ($min_access!=0)
				{
				?>
				<li><a onclick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_request.php?ref=<?php echo urlencode($usercollection) ?>&k=<?php echo urlencode($k) ?>"><?php echo 	$lang["requestall"]?></a></li>
				<?php
				}
			}
		?>
	  <?php if (!$disable_collection_toggle) { ?>
		<li><a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang["showthumbnails"]?></a></li>
	  <?php } ?>
	  <?php } # end hook("replaceanoncollectiontools") ?>
	</div>
	<?php 
		}
	else
		{
		?>
		<div id="CollectionMinTitle" class="ExternalShare">
		<?php
		if(!hook('replacecollectiontitle') && !hook('replacecollectiontitlemin'))
			{
			?>
			<h2><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_manage.php"><?php echo $lang['mycollections']; ?></a></h2>
			<?php
			}
			?>
		</div>

		<!--Menu-->	
		<div id="CollectionMinRightNav">
        	<a id="toggleThumbsLink" href="#" onClick="ToggleThumbs();return false;"><?php echo $lang['showthumbnails']; ?></a>
    	<?php
    	hook('aftertogglethumbs');

	    // Render dropdown actions
		render_actions($cinfo, false, false, "min",$results_all);
		?>
		</div>

		<!--Collection Dropdown-->
		<?php
		if(!hook('replace_collectionmindroptitle'))
			{
			?>
		<div id="CollectionMinDropTitle"><?php echo $lang['currentcollection']; ?>:&nbsp;</div>
    		<?php
    		} # end hook replace_collectionmindroptitle
    		?>
		<div id="CollectionMinDrop">
	 		<form method="get"
	 			  id="colselect2" 
	 			  onsubmit="newcolname=encodeURIComponent(jQuery('#entername2').val());CollectionDivLoad('<?php echo $baseurl_short; ?>pages/collections.php?thumbs=hide&collection=-1&search=<?php echo urlencode($search)?>&k=<?php echo urlencode($k); ?>&search=<?php echo urlencode($search)?>&entername='+newcolname);return false;">
				<div class="MinSearchItem" id="MinColDrop">
					<input type=text id="entername2" name="entername" placeholder="<?php echo $lang['entercollectionname']; ?>" style="display:inline;display:none;" class="SearchWidthExp">
				</div>
				<script>jQuery('#collection').clone().attr('id','collection2').attr('onChange',"if(document.getElementById('collection2').value==-1){document.getElementById('entername2').style.display='inline';document.getElementById('entername2').focus();return false;}<?php if (!checkperm('b')){ ?>ChangeCollection(jQuery(this).val(),'<?php echo urlencode($k) ?>','<?php echo urlencode($usercollection) ?>','<?php echo $change_col_url ?>');<?php } else { ?>document.getElementById('colselect2').submit();<?php } ?>").prependTo('#MinColDrop');</script>
	  		</form>
		</div>
		<?php
		}
	}
	?>
	<!--Collection Count-->	
	<?php if(!hook("replace_collectionminitems")){?>
	<div id="CollectionMinitems"><strong><?php echo $count_result?></strong>&nbsp;<?php if ($count_result==1){echo $lang["item"];} else {echo $lang["items"];}?></div>
	<?php } # end hook replace_collectionminitems ?>
	</div>
	<?php } ?>

<?php draw_performance_footer();
