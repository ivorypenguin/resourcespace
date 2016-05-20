<?php

# Global everything we need, in case called inside a function (e.g. for push_metadata support)
global $k,$lang,$show_resourceid,$show_access_field,$show_resource_type,$show_hitcount,$show_contributed_by,$baseurl_short,$search,$enable_related_resources,$force_display_template_order_by;

// -----------------------  Tab calculation -----------------
$fields_tab_names = array();
	
foreach ($fields as $field) {
	$fields_tab_names[] = $field['tab_name'];
	$resources_per_tab_name[$field['tab_name']][] = $field['ref'];
}

$fields_tab_names = array_values(array_unique($fields_tab_names));

// Clean the tabs by removing the ones that would just be empty:
$tabs_with_data = array();
foreach ($fields_tab_names as $tabname) {
	for ($i = 0; $i < count($fields); $i++) { 
		
		$displaycondition = check_view_display_condition($fields, $i);
	
		if($displaycondition && $tabname == $fields[$i]['tab_name'] && $fields[$i]['value'] != '' && $fields[$i]['value'] != ',' && $fields[$i]['display_field'] == 1 && ($access == 0 || ($access == 1 && !$field['hide_when_restricted']))) {
			$tabs_with_data[] = $tabname;
		}

	}
}
$fields_tab_names = array_intersect($fields_tab_names, $tabs_with_data);

if(isset($related_type_show_with_data)) {
	// Get resource type tab names (if any set):
	$resource_type_tab_names = sql_array('SELECT tab_name as value FROM resource_type', '');
	$resource_type_tab_names = array_values(array_unique($resource_type_tab_names));

	// These are the tab names which will be rendered for the resource specified:
	$fields_tab_names = array_values(array_unique((array_merge($fields_tab_names, $resource_type_tab_names))));
}

// Make sure the fields_tab_names is empty if there are no values:
foreach ($fields_tab_names as $key => $value) {
	if(empty($value)) {
		unset($fields_tab_names[$key]);
	}
}

$modified_view_tabs=hook("modified_view_tabs","view",array($fields_tab_names));if($modified_view_tabs!=='' && is_array($modified_view_tabs)){$fields_tab_names=$modified_view_tabs;}

//Check if we want to use a specified field as a caption below the preview
if(isset($display_field_below_preview) && is_int($display_field_below_preview))
	{
	$df=0;
	foreach ($fields as $field)
		{
		if($field["fref"]==$display_field_below_preview)
			{
			$displaycondition=check_view_display_condition($fields,$df);
			if($displaycondition)
				{
				$previewcaption=$fields[$df];
				// Remove from the array so we don't display it twice
				unset($fields[$df]);
				//Reorder array 
				$fields=array_values($fields);				
				}
			}
		$df++;			
		}
	}
        
?>
        
        
<div id="Metadata">
<?php
$extra="";

#  -----------------------------  Draw tabs ---------------------------
$tabname="";
$tabcount=0;
$tmp = hook("tweakfielddisp", "", array($ref, $fields)); if($tmp) $fields = $tmp;
if((isset($fields_tab_names) && !empty($fields_tab_names)) && count($fields) > 0) { ?>
	
	<div class="TabBar">
	
	<?php
		foreach ($fields_tab_names as $tabname) { ?>

			<div id="<?php echo ($modal ? "Modal" : "")?>tabswitch<?php echo $tabcount; ?>" class="Tab<?php if($tabcount == 0) { ?> TabSelected<?php } ?>">
				<a href="#" onclick="Select<?php echo ($modal ? "Modal" : "")?>Tab(<?php echo $tabcount; ?>);return false;"><?php echo i18n_get_translated($tabname)?></a>
			</div>
		
		<?php 
			$tabcount++;
		} ?>

	</div> <!-- end of TabBar -->
	<script type="text/javascript">
	function Select<?php echo ($modal ? "Modal" : "")?>Tab(tab) {
		// Deselect all tabs
		<?php for($n = 0; $n < $tabcount; $n++) { ?>
		document.getElementById("<?php echo ($modal ? "Modal" : "")?>tab<?php echo $n; ?>").style.display="none";
		document.getElementById("<?php echo ($modal ? "Modal" : "")?>tabswitch<?php echo $n; ?>").className="Tab";
		<?php } ?>
		document.getElementById("<?php echo ($modal ? "Modal" : "")?>tab" + tab).style.display="block";
		document.getElementById("<?php echo ($modal ? "Modal" : "")?>tabswitch" + tab).className="Tab TabSelected";
	}
	</script>

<?php
} ?>

<div id="<?php echo ($modal ? "Modal" : "")?>tab0" class="TabbedPanel<?php if ($tabcount>0) { ?> StyledTabbedPanel<?php } ?>">
<div class="clearerleft"> </div>
<div>
<?php 
#  ----------------------------- Draw standard fields ------------------------
?>
<?php if ($show_resourceid) { ?><div class="itemNarrow"><h3><?php echo $lang["resourceid"]?></h3><p><?php echo htmlspecialchars($ref)?></p></div><?php } ?>
<?php if ($show_access_field) { ?><div class="itemNarrow"><h3><?php echo $lang["access"]?></h3><p><?php echo @$lang["access" . $resource["access"]]?></p></div><?php } ?>
<?php if ($show_resource_type) { ?><div class="itemNarrow"><h3><?php echo $lang["resourcetype"]?></h3><p><?php echo  get_resource_type_name($resource["resource_type"])?></p></div><?php } ?>
<?php if ($show_hitcount){ ?><div class="itemNarrow"><h3><?php echo $resource_hit_count_on_downloads?$lang["downloads"]:$lang["hitcount"]?></h3><p><?php echo $resource["hit_count"]+$resource["new_hit_count"]?></p></div><?php } ?>
<?php hook("extrafields");?>
<?php
# contributed by field
if (!hook("replacecontributedbyfield")){
$udata=get_user($resource["created_by"]);
if ($udata!==false)
	{
	?>
<?php if ($show_contributed_by){?>	<div class="itemNarrow"><h3><?php echo $lang["contributedby"]?></h3><p><?php if (checkperm("u")) { ?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_user_edit.php?ref=<?php echo $udata["ref"]?>"><?php } ?><?php echo highlightkeywords(htmlspecialchars($udata["fullname"]),$search)?><?php if (checkperm("u")) { ?></a><?php } ?></p></div><?php } ?>
	<?php
	}
} // end hook replacecontributedby

# Show field data
$tabname="";
$tabcount=0;
$extra="";
$show_default_related_resources = TRUE;
foreach ($fields_tab_names as $tabname) {

	for($i = 0; $i < count($fields); $i++) {

		$displaycondition = check_view_display_condition($fields, $i);

		if($displaycondition && $tabname == $fields[$i]['tab_name']) {
			if(!hook('renderfield',"", array($fields[$i]))) {
				display_field_data($fields[$i]);

				// Show the fields with a display template now
				echo $extra;
				$extra = '';
			}
		}

	}

	// Add related resources which have the same tab name:
	if(isset($related_type_show_with_data) && isset($fields_tab_names) && !empty($fields_tab_names)) {
		
		include '../include/related_resources.php';

		$show_default_related_resources = FALSE;

		//Once we've shown the related resources unset the variable so they won't be shown as thumbnails:
		unset($relatedresources);
	}

	$tabcount++;
	if($tabcount != count($fields_tab_names)) { ?>
		<div class="clearerleft"></div>
		</div>
		</div>
		<div class="TabbedPanel StyledTabbedPanel" style="display:none;" id="<?php echo ($modal ? "Modal" : "")?>tab<?php echo $tabcount?>"><div>
	<?php
	}

}

if(empty($fields_tab_names)) {
	for($i = 0; $i < count($fields); $i++) {

		$displaycondition = check_view_display_condition($fields, $i);

		if($displaycondition) {
			if(!hook('renderfield',"", array($fields[$i]))) {
				display_field_data($fields[$i]);
			}
		}

	}
}

// Option to display related resources of specified types along with metadata
if ($enable_related_resources && $show_default_related_resources)
	{
	$relatedresources=do_search("!related" . $ref);
	#build array of related resources' types
	$related_restypes=array();
	for ($n=0;$n<count($relatedresources);$n++)
		{
		$related_restypes[]=$relatedresources[$n]['resource_type'];
		}
	#reduce extensions array to unique values
	$related_restypes=array_unique($related_restypes);
	
	$relatedtypes_shown=array();
	$related_resources_shown=0;
	if(isset($related_type_show_with_data))
		{
		
		# Render fields with display template before the list of related resources:
		echo $extra;
		
		foreach($related_type_show_with_data as $rtype)
			{
			// Is this a resource type that needs to be displayed?
			if (!in_array($rtype,$related_type_show_with_data) || (!in_array($rtype,$related_restypes) && !$related_type_upload_link))
				{
				continue;
				}
			$restypename=sql_value("select name as value from resource_type where ref = '$rtype'","");
			$restypename = lang_or_i18n_get_translated($restypename, "resourcetype-", "-2");		
			
			?>
			<div class="clearerleft"></div>
			<div class="item" id="RelatedResourceData">			
			<?php
			if(in_array($rtype,$related_restypes) || ($related_type_upload_link && $edit_access))
				{
				///only show the table if there are related resources of this type
				?>
				<div class="Listview ListviewTight" >
					<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
					<tbody>
					<tr class="ListviewTitleStyle">
					<td><h3><?php echo $restypename ?></h3></td>		
					<td><div class="ListTools"></div></td>                                    
					</tr>
					<?php
					foreach($relatedresources as $relatedresource)
						{
						if($relatedresource["resource_type"]==$rtype)
							{
							$relatedtitle=$relatedresource["field".$view_title_field];
												
							echo "<tr id=\"relatedresource" . $relatedresource["ref"] . "\" class=\"RelatedResourceRow\">";
							echo "<td class=\"link\"><a href=\"" . $baseurl_short . "pages/view.php?ref=" . $relatedresource["ref"] . "\">" . htmlspecialchars($relatedtitle) . "</a></td>";                                    
							echo "<td>";
							if($edit_access)
								{echo "<div class=\"ListTools\" ><a href=\"#\" onClick=\"if(confirm('" . $lang["related_resource_confirm_delete"] . "')){relateresources(" . $ref . "," . $relatedresource["ref"] . ",'remove');}return false;\" >&gt;&nbsp;" . $lang["action-remove"] . "</a></div>";
								}
							echo "</td>";	
							echo "</tr>";	
							$related_resources_shown++;
							}
						}
					
					if($related_type_upload_link && $edit_access)
						{
						echo "<tr><td></td><td><div class=\"ListTools\"><a href=\"" . $baseurl_short . "pages/edit.php?ref=-" . $userref . "&uploader=plupload&resource_type=" . $rtype ."&submitted=true&relateto=" . $ref . "&collection_add=&redirecturl=" . urlencode($baseurl . "/?r=" . $ref) . "\">&gt;&nbsp;" . $lang["upload"] . "</a></div></td>";
						}			
			
					?>
					</tbody>
					</table>
											 
				</div>
						
				<?php
				// We have displayed these, don't show them again later
				$relatedtypes_shown[]=$rtype;
				}
			?>
			</div><!-- End of RelatedResourceData -->
			<?php
			}
		}    
    }
    
?><?php hook("extrafields2");?>
<?php if(!$force_display_template_order_by){ ?> <div class="clearerleft"></div> <?php } ?>
<?php if(!isset($related_type_show_with_data)) { echo $extra; } ?>
<?php if($force_display_template_order_by){ ?> <div class="clearerleft"></div> <?php } ?>
</div>
</div>
<?php hook("renderafterresourcedetails"); ?>
<!-- end of tabbed panel-->
</div>

