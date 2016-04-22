<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/search_functions.php";
include_once "../../include/collections_functions.php";
include "../../include/render_functions.php";

$order_by=getvalescaped("order_by",'');
$sort=getvalescaped("sort","DESC");
$search=getvalescaped("search","");
$restypes=getvalescaped('restypes','');
$archive=getvalescaped('archive','');
$daylimit=getvalescaped('daylimit','');
$offset=getvalescaped('offset','');
$collection=getvalescaped('collection','');
$resources_count=getvalescaped('resources_count','');

$collection_data=get_collection($collection);

render_actions($collection_data, $top_actions = true, $two_line = true, $collection);