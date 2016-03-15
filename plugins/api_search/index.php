<?php

include(dirname(__FILE__)."/../../include/db.php");
include_once(dirname(__FILE__)."/../../include/general.php");
include(dirname(__FILE__)."/../../include/search_functions.php");
include(dirname(__FILE__)."/../../include/resource_functions.php");
include(dirname(__FILE__)."/../../include/collections_functions.php");
$api=true;

include(dirname(__FILE__)."/../../include/authenticate.php");

// required: check that this plugin is available to the user
if (!in_array("api_search",$plugins)){die("no access");}

$search           = getval('search', '');
$search           = refine_searchstring($search);
$restypes         = getvalescaped('restypes', '');
$order_by         = getvalescaped('order_by','relevance');
$sort             = getvalescaped('sort','desc');
$archive          = getvalescaped('archive',0);
$starsearch       = getvalescaped('starsearch', '');
$collection       = getvalescaped('collection', '',true);
$original         = filter_var(getvalescaped('original', FALSE), FILTER_VALIDATE_BOOLEAN);
$metadata         = filter_var(getvalescaped('metadata', FALSE), FILTER_VALIDATE_BOOLEAN);
$prettyfieldnames = filter_var(getvalescaped('prettyfieldnames', FALSE), FILTER_VALIDATE_BOOLEAN);
$access_filter    = getvalescaped('access', -999, TRUE);
$shortnames       = filter_var(getvalescaped('shortnames',FALSE), FILTER_VALIDATE_BOOLEAN);
$results_per_page = getvalescaped('results_per_page', 0, true);
$page             = getvalescaped('page', 0, true);

$help=getval('help', '');
if ($help!=""){
header('Content-type: text/plain');
echo file_get_contents("readme.txt");
die();
}


if ($api_search['signed']){

// test signature? get query string minus leading ? and skey parameter
$test_query="";
parse_str($_SERVER["QUERY_STRING"],$parsed);
foreach ($parsed as $parsed_parameter=>$value){
    if ($parsed_parameter!="skey"){
        $test_query.=$parsed_parameter.'='.$value."&";
    }
    }
$test_query=rtrim($test_query,"&");

    // get hashkey that should have been used to create a signature.
    $hashkey=md5($api_scramble_key.getval("key",""));

    // generate the signature required to match against given skey to continue
    $keytotest = md5($hashkey.$test_query);

    if ($keytotest <> getval('skey','')){
		header("HTTP/1.0 403 Forbidden.");
		echo "HTTP/1.0 403 Forbidden. Invalid Signature";
		exit;
	}
}

if ($collection!=""){$searchadd="!collection".$collection.", ";} else {$searchadd="";}

$results=do_search($searchadd.$search,$restypes,$order_by,$archive,-1,$sort,false,$starsearch);
if(!is_array($results)) {
    $results=array();
}

// Handle results in one go as much as possible
// Note: do it outside of this loop for exceptional cases only
// TODO: add other cases here as well
foreach ($results as $key => $result) {

    // Filter results by access:
    if($access_filter != -999 && $access_filter >= 0) {

        if($result['access'] != $access_filter) {
            unset($results[$key]);
        }

    }

}
$results = array_values($results);


// Limit results shown back through multiple pages
$paginate = false;
if(0 < $results_per_page || 0 < $page)
    {
    $paginate = true;

    $results_per_page = (0 < $results_per_page ? $results_per_page : 15);
    $page             = (0 < $page ? $page : 1);

    $min_result = ($page - 1) * $results_per_page;
    $max_result = ($page * $results_per_page) - 1;

    // build a new array with pagination info
    $pagination = array();
    $pagination['total_pages'] = ceil(count($results) / $results_per_page);
    
    // If client code is looking for a page outside of range, send 400 error
    if($page > $pagination['total_pages'])
        {
        header('HTTP/1.0 400 Bad Request', true, 400);
        exit(str_replace('[%max_page_number%]', $pagination['total_pages'], $lang['api_search_error_page_out_of_range']));
        }
    
    $pagination['total_resources'] = count($results);
    $pagination['per_page']        = $results_per_page;
    $pagination['page']            = $page;

    $newresult = array();
    for($n = 0; $n < count($results); $n++)
        {
        if(($n >= $min_result) && $n <= $max_result)
            {
            $newresult[] = $results[$n];
            }
        }    
    $results = $newresult;
    }

if (getval("previewsize","")!=""){
    for($n=0;$n<count($results);$n++){
        $access=get_resource_access($results[$n]);
        $use_watermark=check_use_watermark();
        $filepath=get_resource_path($results[$n]['ref'],true,getval('previewsize',''),false,'jpg',-1,1,$use_watermark,'',-1);
        $previewpath=get_resource_path($results[$n]['ref'],false,getval("previewsize",""),false,"jpg",-1,1,$use_watermark,"",-1);
        if (file_exists($filepath)){
            $results[$n]['preview']=$previewpath;
        }
        else {
            $previewpath=explode('filestore/',$previewpath);
            $previewpath=$previewpath[0]."gfx/";
            $file=$previewpath.get_nopreview_icon($results[$n]["resource_type"],$results[$n]["file_extension"],false,true);
            $results[$n]['preview']=$file;
        }
    }
}

if($original) {
    for($i = 0; $i < count($results); $i++) {
        $access = get_resource_access($results[$i]);
        $filepath = get_resource_path($results[$i]['ref'], TRUE, '', FALSE, $results[$i]['file_extension'], -1, 1, FALSE, '', -1);
        $original_link = get_resource_path($results[$i]['ref'], FALSE, '', FALSE, $results[$i]['file_extension'], -1, 1, FALSE, '', -1);

        if(file_exists($filepath)) {
            $results[$i]['original_link'] = $original_link;
        } else {
            $results[$i]['original_link'] = 'No original link available.';
        }

        // Get the size of the original file:
        $original_size = get_original_imagesize($results[$i]['ref'], $filepath, $results[$i]['file_extension']);
        $original_size = formatfilesize($original_size[0]);
        $original_size = str_replace('&nbsp;', ' ', $original_size);
        $results[$i]['original_size'] = $original_size;
    }
}

// flv file and thumb if available
if (getval("flvfile","")!=""){
    for($n=0;$n<count($results);$n++){
        // flv previews
        $flvfile=get_resource_path($results[$n]['ref'],true,"pre",false,$ffmpeg_preview_extension);
        if (!file_exists($flvfile)) {$flvfile=get_resource_path($results[$n]['ref'],true,"",false,$ffmpeg_preview_extension);}
        if (!(isset($results[$n]['is_transcoding']) && $results[$n]['is_transcoding']==1) && file_exists($flvfile) && (strpos(strtolower($flvfile),".".$ffmpeg_preview_extension)!==false))
            {
            if (file_exists(get_resource_path($results[$n]['ref'],true,"pre",false,$ffmpeg_preview_extension)))
                {
                $flashpath=get_resource_path($results[$n]['ref'],false,"pre",false,$ffmpeg_preview_extension,-1,1,false,"",-1,false);
                }
            else 
                {
                $flashpath=get_resource_path($results[$n]['ref'],false,"",false,$ffmpeg_preview_extension,-1,1,false,"",-1,false);
                }
            $results[$n]['flvpath']=$flashpath;
            $thumb=get_resource_path($results[$n]['ref'],false,"pre",false,"jpg"); 
            $results[$n]['flvthumb']=$thumb;
        }
    }
}

if (getval("videosonly","")!=""){
	$newresult=array();
	for ($n=0;$n<count($results);$n++){
		if (isset($results[$n]["flvpath"]) && isset($results[$n]["flvthumb"])){
			$newresult[]=$results[$n];
		}
	}
	$results=$newresult;
}


$modified_result=hook("modifyapisearchresult");
if ($modified_result){
	$results=$modified_result;
}

 // this function in api_core   
$results=refine_api_resource_results($results);
$limit_to=getval("limit_to","");

if($metadata) {

    global $api_search_full_field_data;
    $api_search_full_field_data = implode(',', $api_search_full_field_data);

    if(trim($api_search_full_field_data) == '') {
        exit($lang['api_search_error_no_fields_set']);
    }

    // Build api_search field string in order to find the fields:
    $fields = sql_query('SELECT ref, name, title FROM resource_type_field WHERE ref IN (' . $api_search_full_field_data . ');');
    foreach ($fields as $field) {
        $full_fields_options['field' . $field['ref']]['name'] = $field['name'];
        $full_fields_options['field' . $field['ref']]['title'] = $field['title'];
    }

    for($i = 0; $i < count($results); $i++) {
    
        $full_field_data_ids_list = '';

        // Build list of IDs of field types to return full data for:
        // NOTE: fields are displayed either like [field18] or [Caption] or [caption]
        foreach ($full_fields_options as $field_key => $full_field_info)
            {
            if((!$prettyfieldnames && array_key_exists($field_key, $results[$i])) ||
                ($prettyfieldnames && array_key_exists($full_field_info['title'], $results[$i])) ||
                (!$prettyfieldnames && $shortnames && array_key_exists($full_field_info['name'], $results[$i]))
            )
                {
                $full_field_data_ids_list .= substr($field_key, 5) . ',';
                }

            }
        $full_field_data_ids_list = substr($full_field_data_ids_list, 0, -1);

        if(trim($full_field_data_ids_list) == '') {
            continue;
        }

        // Get the full field value:
        $query = sprintf('
                  SELECT resource_type_field,value
                    FROM resource_data
                   WHERE resource = %d
                     AND resource_type_field IN (%s)
                ORDER BY FIELD(resource_type_field,%s);
            ',
            $results[$i]['ref'],
            $full_field_data_ids_list,
            $full_field_data_ids_list
        );
        $metadata_values = sql_query($query, '');
            
        // Replace the values:
        foreach ($metadata_values as $metadata_field) {
            
            if(!$prettyfieldnames && array_key_exists('field' . $metadata_field['resource_type_field'], $full_fields_options) && array_key_exists('field' . $metadata_field['resource_type_field'], $results[$i]))
                {
                $results[$i]['field' . $metadata_field['resource_type_field']] = $metadata_field['value'];
                }
            else if($prettyfieldnames && array_key_exists('field' . $metadata_field['resource_type_field'], $full_fields_options) && array_key_exists($full_fields_options['field' . $metadata_field['resource_type_field']]['title'], $results[$i]))
                {
                $results[$i][$full_fields_options['field' . $metadata_field['resource_type_field']]['title']] = $metadata_field['value'];
                }
            else if(!$prettyfieldnames && $shortnames && array_key_exists('field' . $metadata_field['resource_type_field'], $full_fields_options) && array_key_exists($full_fields_options['field' . $metadata_field['resource_type_field']]['name'], $results[$i]))
                {
                $results[$i][$full_fields_options['field' . $metadata_field['resource_type_field']]['name']] = $metadata_field['value'];
                }

        }

    }

}

if (getval("content","")=="xml" && !$paginate){
    header('Content-type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?><results>';
    foreach ($results as $result){
         if ($limit_to!=""){echo "<$limit_to>";} else {echo '<resource>';}
        if (is_array($result)){
			foreach ($result as $resultitem=>$value){
				echo '<'.$resultitem.'>';
				echo xml_entities($value);
				echo '</'.$resultitem.'>';
			}
		} else {
				echo $result;
			}
        
        if ($limit_to!=""){echo "</$limit_to>";} else {echo '</resource>';}
    }
    echo '</results>';
}

else if (getval("content","")=="xml" && $paginate){

	$resources=$results;
   
    header('Content-type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<results>';
    echo '<pagination>';

    foreach ($pagination as $resultitem=>$value){
        echo '<'.$resultitem.'>';
        echo xml_entities($value);
        echo '</'.$resultitem.'>';
    }

    
    echo '</pagination>';
    echo '<resources>';
    foreach ($resources as $result){
        if ($limit_to!=""){echo "<$limit_to>";} else {echo '<resource>';}
        if (is_array($result)){
			foreach ($result as $resultitem=>$value){
				echo '<'.$resultitem.'>';
				echo xml_entities($value);
				echo '</'.$resultitem.'>';
			}
		} else {
			echo $result;
		}
        if ($limit_to!=""){echo "</$limit_to>";} else {echo '</resource>';}
    }
    echo '</resources>';
	echo '</results>';
}

else {
    header('Content-type: application/json');
    if ($paginate) {
        $results = array('resources' => $results, 'pagination' => $pagination);
    }
    echo json_encode($results); // echo json without headers by default
} 




