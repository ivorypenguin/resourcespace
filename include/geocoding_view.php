<?php

	global $geo_search_restrict;	
	if (count($geo_search_restrict)>0)
		{
		foreach ($geo_search_restrict	as $zone)
			{
			# Inside zone? Do not show location data
	 		if ($resource["geo_lat"] >= $zone[0] && $resource["geo_lat"] <= $zone[2] && 
	 		$resource["geo_long"] >= $zone[1] && $resource["geo_long"] <= $zone[3]) { return false; }
			}
		}
		
	if($hide_geolocation_panel && !isset($geolocation_panel_only))
		{?>
		<script>
		function ShowGeolocation()
			{
			if(!jQuery("#GeolocationData").length){				
				jQuery.ajax({
					type:"GET",
					url: '<?php echo $baseurl_short?>pages/ajax/geolocation_loader.php?ref=<?php echo urlencode($ref)?>&k=<?php echo urlencode($k)?>',
					success: function(data){
						jQuery("#GeolocationHideLink").after(data);						
						}
				    });
				}			
			
			jQuery("#GeolocationData").slideDown(); 
			jQuery("#GeolocationHideLink").show();
			jQuery("#GeolocationShowLink").hide();
			}
		function HideGeolocation()
				{
				jQuery("#GeolocationData").slideUp(); 
				jQuery("#GeolocationShowLink").show();
				jQuery("#GeolocationHideLink").hide();
				}
			</script><?php
			}
			
 if (!isset($geolocation_panel_only))
	{
	?>
	<!-- Begin Geolocation Section -->
	<div class="RecordBox">
	<div class="RecordPanel"><?php
	    
	if ($hide_geolocation_panel)
	    {?>
	    <div id="GeolocationShowLink" class="CollapsibleSection" ><?php echo "<a href=\"javascript: void(0)\" onClick=\"ShowGeolocation();\">&#x25B8;&nbsp;" . $lang["showgeolocationpanel"] . "</a>";?></div>
	    <div id="GeolocationHideLink" class="CollapsibleSection" style="display:none"><?php echo "<a href=\"javascript: void(0)\" onClick=\"HideGeolocation();return false;\">&#x25BE;&nbsp;" . $lang["hidegeolocationpanel"] . "</a>";?></div>
	    <?php		
	    }
	}
	
     if(!$hide_geolocation_panel || isset($geolocation_panel_only))
	{?>
	<div id="GeolocationData">
	<div class="Title"><?php echo $lang['location-title']; ?></div>
	<?php
       
	if ($resource["geo_lat"]!="" && $resource["geo_long"]!="")
	    {
		    ?>
	    <?php if ($edit_access) { ?>
	    <p>&gt;&nbsp;<a href="<?php echo $baseurl_short?>pages/geo_edit.php?ref=<?php echo urlencode($ref); ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang['location-edit']; ?></a></p><?php } ?>
	    
		    <?php $mapheight=$view_mapheight; include dirname(__FILE__) . "/geo_map.php";
		    $zoom = $resource["mapzoom"];
		    if (!($zoom>=2 && $zoom<=21)) {
			    // set $zoom based on precision of specified position
			    $zoom = 18;
			    $siglon = round(100000*abs($resource["geo_long"]))%100000;
			    $siglat = round(100000*abs($resource["geo_lat"]))%100000;
			    if ($siglon%100000==0 && $siglat%100000==0) {
				    $zoom = 3;
			    } elseif ($siglon%10000==0 && $siglat%10000==0) {
				    $zoom = 6;
			    } elseif ($siglon%1000==0 && $siglat%1000==0) {
				    $zoom = 10;
			    } elseif ($siglon%100==0 && $siglat%100==0) {
				    $zoom = 15;
			    }
		    }
		    ?>
		    <script>
		    var lonLat = new OpenLayers.LonLat( <?php echo $resource["geo_long"] ?>, <?php echo $resource["geo_lat"] ?> )
		      .transform(
			new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
			map.getProjectionObject() // to Spherical Mercator Projection
		      );
		var markers = new OpenLayers.Layer.Markers("<?php echo $lang["markers"]?>");
		map.addLayer(markers);
    
		<?php if (!hook("addmapicon")) { ?>
			    markers.addMarker(new OpenLayers.Marker(lonLat));
		    <?php } ?>
    
		map.setCenter (lonLat, Math.min(<?php echo $zoom ?>, map.getNumZoomLevels() - 1));
	
	      </script>
		<?php     
		    } else {?>
		<a href="<?php echo $baseurl_short?>pages/geo_edit.php?ref=<?php echo urlencode($ref); ?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang['location-add'];?></a>
	
		<?php }?>
		<?php if ($view_panels) { ?>
			<script>
			    jQuery(document).ready(function () {
    
		    		jQuery("#GeolocationData").children(".Title").attr("panel", "GeolocationData").appendTo("#Titles1");
		    		removePanel=jQuery("#GeolocationData").parent().parent(".RecordBox");
		    		jQuery("#GeolocationData").appendTo("#Panel1").addClass("TabPanel").hide();
		    		removePanel.remove();
		    		
		         });
		    </script>
				<?php } ?>
	</div>
	<?php
	}
	
 if (!isset($geolocation_panel_only))
	{?>
	</div> <!-- End of RecordPanel  -->
	 <div class="PanelShadow"></div>
	 </div> <!-- End of RecordBox -->
	<!-- End Geolocation Section -->
	<?php }