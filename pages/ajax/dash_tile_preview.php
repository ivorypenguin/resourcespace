<?php
/*
 * Ajax generation handling for dash tile previews - Montala Ltd, Jethro Dew
 * Separated out into a new file as there is no existing dash tile record to pull information from
 * Content for the tile is sent via ajax to this page. Standard build functions available from include/dash_tile_generation.php
 */

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/search_functions.php";
include_once "../../include/collections_functions.php";
include "../../include/dash_functions.php";


global $userref,$baseurl_short;

$tile_type=getvalescaped("tltype","");
$tile_style=getvalescaped("tlstyle","");

$tile                   = array();
$tile['ref']            = getvalescaped('edit', '');
$tile['link']           = getvalescaped('tllink', '');
$tile['txt']            = getvalescaped('tltxt', '');
$tile['title']          = getvalescaped('tltitle', '');
$tile['resource_count'] = getvalescaped('tlrcount', '');


$tile_id="previewdashtile";
$tile_width = getvalescaped("tlwidth","");
$tile_height = getvalescaped("tlheight","");
if(!is_numeric($tile_width) || !is_numeric($tile_height)){exit($lang["error-missingtileheightorwidth"]);}
include "../../include/dash_tile_generation.php";
tile_select($tile_type,$tile_style,$tile,$tile_id,$tile_width,$tile_height);
exit($lang["nodashtilefound"]);
