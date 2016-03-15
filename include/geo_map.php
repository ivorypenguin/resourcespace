

<div id="map_canvas" style="width: 100%; height: <?php echo isset($mapheight)?$mapheight:"500" ?>px; display:block; float:none;overflow: hidden;" class="Picture" ></div>


<script>
OpenLayers.Lang.setCode("<?php echo $language?>");
OpenLayers.ImgPath="<?php echo $baseurl ?>/lib/OpenLayers/img/";

map = new OpenLayers.Map("map_canvas");

var osm = new OpenLayers.Layer.OSM("<?php echo $lang["openstreetmap"]?>"
	
	<?php if ($geo_tile_caching && extension_loaded("curl")){
	    $tilecache=get_temp_dir()."/tiles";
        if (!file_exists($tilecache)){
                mkdir($tilecache);
                chmod($tilecache,0777);
        }
	?>
		,"<?php echo $baseurl?>/pages/ajax/tiles.php?z=${z}&x=${x}&y=${y}&r=mapnik",{transitionEffect: 'resize'}
	
	<?php } else { ?>
	
		,"http://tile.openstreetmap.org/${z}/${x}/${y}.png",{transitionEffect: 'resize'}
		
	<?php } ?>
	
);

var gphy = new OpenLayers.Layer.Google(
"<?php echo $lang["google_terrain"]?>",
{type: google.maps.MapTypeId.TERRAIN}
// used to be {type: G_PHYSICAL_MAP}
);
var gmap = new OpenLayers.Layer.Google(
"<?php echo $lang["google_default_map"]?>", // the default
{numZoomLevels: 20}
// default type, no change needed here
);
var gsat = new OpenLayers.Layer.Google(
"<?php echo $lang["google_satellite"]?>",
{type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}
// used to be {type: G_SATELLITE_MAP, numZoomLevels: 22}
);


map.addLayers([<?php echo $geo_layers ?>]);
map.addControl(new OpenLayers.Control.LayerSwitcher());
    
</script>
