<?php


function refine_api_resource_results($results){
	
	global $api_search_exclude_fields,$lang;
	$fields=sql_array("select ref value from resource_type_field");
	$fields=sql_query("select ref, title,name from resource_type_field where  ref in ('" . join("','",$fields) . "') order by order_by");

	$field_title=array();

	foreach ($fields as $field){
		$field_title[$field['ref']]=i18n_get_translated($field['title']);
	}
	foreach ($fields as $field){
		$field_name[$field['ref']]=$field['name'];
	}
	
	$limit_to=getval("limit_to","");
    if ($limit_to!=""){
		$newresult=array();
		$x=0;
		if (substr($limit_to,0,5)!="field"){
			$name_field=array_flip($field_name);
			if (isset($name_field[$limit_to])){$limit_to="field".$name_field[$limit_to];}
		}
		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (strtolower($key)==strtolower($limit_to)){
					$newresult[]=$value;
				}
			}
		}
		$results=$newresult;
	}

	if (getval("shortnames","")!=""){

		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (substr($key,0,5)=="field"){
						$field=str_replace("field","",$key);
						if (isset($field_name[$field])){
							$results[$n][str_replace(' ', '_',$field_name[$field])]=$results[$n][$key];
						}
						unset ($results[$n][$key]);
					}
			}
		}
	}
	// Prettify field titles
	if (getval("prettyfieldnames","")!=""){

		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (substr($key,0,5)=="field"){
						$field=str_replace("field","",$key);
						if (isset($field_title[$field])){
							$results[$n][str_replace(' ', '_',$field_title[$field])]=$results[$n][$key];
						}
						unset ($results[$n][$key]);
					}
			}
		}
	}

	if (getval("contributedby","true")!="false"){
		$users=get_users();
		$n=0;
		$users_array=array();
		foreach($users as $user){
			$users_array[$user['ref']]=$user['fullname'];
		}
		
		for ($n=0;$n<count($results);$n++){
			if (isset($results[$n]['created_by'])&& $results[$n]['created_by']>0 && isset($users_array[$results[$n]['created_by']])){
			$results[$n][str_replace(' ', '_', $lang['contributedby'])]=$users_array[$results[$n]['created_by']];
			}
		}
		
	}
   
	
	
	// Exclude fields (clean up the output)
	if ($api_search_exclude_fields!=""){
		$newresult=array();
		$api_search_exclude_fields=explode(",",$api_search_exclude_fields);
		$api_search_exclude_fields=trim_array($api_search_exclude_fields);
		$x=0;
		for ($n=0;$n<count($results);$n++){
			foreach ($results[$n] as $key=>$value){
				if (!in_array($key,$api_search_exclude_fields)){
					$newresult[$x][$key]=$value;
				}
			}
			$x++;	
		}
		$results=$newresult;
	}
return $results;
}
function HookApi_coreAllAdditional_title_pages_array(){
        return array("index");
}
function HookApi_coreAllAdditional_title_pages(){
        global $pagename,$lang,$applicationname;
        switch($pagename){
			case "index":
				$url=explode("/",$_SERVER['REQUEST_URI']);
				if($url[1]=="plugins" && $url[2]=="api_core"){
					$pagetitle=$lang["apiaccess"];
				}
                break;
		}
        if(isset($pagetitle)){
                echo "<script language='javascript'>\n";
                echo "document.title = \"$applicationname - $pagetitle\";\n";
                echo "</script>";
        }
}
