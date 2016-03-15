<?php




function sync_flickr($search,$new_only=false,$photoset=0,$photoset_name="",$private=0)
	{
	# For the resources matching $search, synchronise with Flickr.
	
	global $flickr_api_key, $flickr_token, $flickr_caption_field, $flickr_keywords_field, $flickr_prefix_id_title, $lang, $flickr_scale_up, $flickr_nice_progress;
			
	$results=do_search($search);
	
	if($flickr_nice_progress){
		$results_processed=0;
		$results_new_publish=0;
		$results_no_publish=0;
		$results_update_publish=0;
	}
	
	foreach ($results as $result)
		{
		global $view_title_field;

		# Fetch some resource details.
		$title=i18n_get_translated($result["field" . $view_title_field]);
		$description=sql_value("select value from resource_data where resource_type_field=$flickr_caption_field and resource='" . $result["ref"] . "'","");
		$keywords=sql_value("select value from resource_data where resource_type_field=$flickr_keywords_field and resource='" . $result["ref"] . "'","");
		$photoid=sql_value("select flickr_photo_id value from resource where ref='" . $result["ref"] . "'","");
		
		if($flickr_nice_progress){
			$nice_title=$result["ref"]." - ".$title;
		}
		
		# Prefix ID to title?
		if ($flickr_prefix_id_title){
			$title=$result["ref"] . ") " . $title;
		}
			
		if (!$new_only || $photoid==""){
			// Output: Processing resource...
			if(!$flickr_nice_progress){echo "<li>" . $lang["processing"] . ": " . $title . "\n";}
			else{flickr_update_progress_file("photo ".$nice_title);}
	
			$im=get_resource_path($result["ref"],true,"scr",false,"jpg");
			if(!file_exists($im) && $flickr_scale_up){
				$im=get_resource_path($result["ref"],true,"lrp",false,"jpg");
				if(!file_exists($im)){
					$im=get_resource_path($result["ref"],true,"hrp",false,"jpg");
					if(!file_exists($im)){
						$im=get_resource_path($result["ref"],true,"",false,$result["file_extension"]);
					}
				}
			}
			if(!file_exists($im)){
				// Output: No suitable upload...
				if(!$flickr_nice_progress){echo "<li>" . $lang["flickr-problem-finding-upload"];}
				else{
					$results_no_publish++;
					$results_processed++;
					//flickr_update_progress_file("no publish ".$nice_title." | processed=".$results_processed." (".$results_no_publish.")");
					flickr_update_progress_file("no publish ".$nice_title." | processed=".$results_processed." new_publish=".$results_new_publish." no_publish=".$results_no_publish." update_meta=".$results_update_publish);
				}
				continue;
			}
	
			# If replacing, add the photo ID of the photo to replace.
			if ($photoid!=""){
				// Output: Already published - updating metadata...
				if(!$flickr_nice_progress){echo "<li>" . str_replace("%photoid", $photoid, $lang["updating_metadata_for_existing_photoid"]);}
				else{flickr_update_progress_file("updating ".$nice_title);}
				
				# Also resubmit title, description and keywords.
				flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photos.setTags","auth_token"=>$flickr_token, "photo_id"=>$photoid, "tags"=>$keywords));

				flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photos.setMeta","auth_token"=>$flickr_token, "photo_id"=>$photoid, "title"=>$title, "description"=>$description));
				
				if($flickr_nice_progress){
					$results_update_publish++;
					$results_processed++;
					//flickr_update_progress_file("updated ".$nice_title." | processed=".$results_processed." (".$results_update_publish.")");
					flickr_update_progress_file("updated ".$nice_title." | processed=".$results_processed." new_publish=".$results_new_publish." no_publish=".$results_no_publish." update_meta=".$results_update_publish);
				}
			}

			# New uploads only. Send the photo file.
			if ($photoid==""){
				// Output: Publishing new resource...
				if(!$flickr_nice_progress){echo "<li>" . str_replace("%photoid", $title, $lang["flickr_new_upload"]);}
				else{flickr_update_progress_file("adding ".$nice_title);}
				
				$url="https://api.flickr.com/services/upload/";
				
				# support > PHP 5.4
				
				if(function_exists("curl_file_create")){
					$photo_curl=curl_file_create($im,'image/jpg');
				}
				else{
					$photo_curl="@" . $im;
				}

				# Build paramater list for upload
				$data=array(
				"photo"=>$photo_curl,
				"api_key"=>$flickr_api_key,
				"auth_token" => $flickr_token,
				"title" => $title,
				"description" => $description,
				"tags" => $keywords
				);

				# Add the signature by signing the data...
				$data["api_sig"]=flickr_sign($data,array("photo"),true);
				
				# Use CURL to upload the photo.
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				$photoid=flickr_get_response_tag(curl_exec($ch),"photoid");
				
				// Output: New upload complete...
				if(!$flickr_nice_progress){echo "<li>" . str_replace("%photoid", $photoid, $lang["photo-uploaded"]);}
				else{
					$results_new_publish++;
					$results_processed++;
					//flickr_update_progress_file("added ".$nice_title." | processed=".$results_processed." (".$results_new_publish.")");
					flickr_update_progress_file("added ".$nice_title." | processed=".$results_processed." new_publish=".$results_new_publish." no_publish=".$results_no_publish." update_meta=".$results_update_publish);
				}

				# Update Flickr tag ID
				sql_query("update resource set flickr_photo_id='" . escape_check($photoid) . "' where ref='" . $result["ref"] . "'");
			}

			$created_new_photoset=false;
			if ($photoset==0){
				# Photoset must be created.
				flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photosets.create","auth_token"=>$flickr_token, "title"=>$photoset_name, "primary_photo_id"=>$photoid),"","POST");
				global $last_xml;
				#echo htmlspecialchars($last_xml);
				$pos_1=strpos($last_xml,"id=\"");
				$pos_2=strpos($last_xml,"\"",$pos_1+5);
				$photoset=substr($last_xml,$pos_1+4,$pos_2-$pos_1-4);
				
				
				// Output: New photoset created...
				if(!$flickr_nice_progress){echo "<li>" . str_replace(array("%photoset_name", "%photoset"), array($photoset_name, $photoset), $lang["created-new-photoset"]);}
				else{flickr_update_progress_file("new photoset ".$photoset_name);}
				$created_new_photoset=true;
			}

			# Add to photoset
			if (!$created_new_photoset){ # If we've just created a photoset then this will already be present within it as the primary photo (added during the create photoset request).
				flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photosets.addPhoto","auth_token"=>$flickr_token, "photoset_id"=>$photoset, "photo_id"=>$photoid));
				// Output: Added new upload to photoset...
				if(!$flickr_nice_progress){echo "<li>" . str_replace(array("%photoid", "%photoset"), array($photoid, $photoset), $lang["added-photo-to-photoset"]);}
				else{flickr_update_progress_file("adding photo to photoset ".$nice_title);}
				#global $last_xml;echo nl2br(htmlspecialchars($last_xml));
			}
						
			# Set permissions
			// Output: Updating permissions...
			if(!$flickr_nice_progress){echo "<li>" . str_replace("%permission", $private==0 ? $lang["flickr_public"] : $lang["flickr_private"], $lang["setting-permissions"]);}
			else{
				$perm=$private==0 ? $lang["flickr_public"] : $lang["flickr_private"];
				flickr_update_progress_file("permissions ".$perm);
			}
			flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photos.setPerms","auth_token"=>$flickr_token, "photo_id"=>$photoid, "is_public"=>($private==0?1:0),"is_friend"=>0,"is_family"=>0,"perm_comment"=>0,"perm_addmetadata"=>0),"","POST");

			
		}
	}
	// Output: Done with all requests...
	if(!$flickr_nice_progress){echo "<li>" . $lang["done"];}
	else{//flickr_update_progress_file($lang["done"]." | processed=".$results_processed);}
		flickr_update_progress_file($lang["done"]." | processed=".$results_processed." new_publish=".$results_new_publish." no_publish=".$results_no_publish." update_meta=".$results_update_publish);}
}
	


function flickr_api($url,$params,$response_tag="",$method="GET")
	{
	# Automatically sign the request, process it, and return it

	# Build query and sign it
	$url.="?" . flickr_sign($params);
	
	# Run query
	
	$opts = array(
	  'https'=>array(
	    'method'=>$method
	  )
	);

	$context = stream_context_create($opts);

	
	$xml=file_get_contents($url,false,$context);
	global $last_xml;$last_xml=$xml;
	
	if ($response_tag=="")
		{
		return true;
		}
	else
		{
		return flickr_get_response_tag($xml,$response_tag);
		}
	}



function flickr_sign($params,$ignore=array(),$output_sig=false)
	{
	global $flickr_api_secret;
	
	ksort($params);
	$string=$flickr_api_secret; 
	foreach ($params as $param=>$value) {if (!in_array($param,$ignore)) {$string.=$param . $value;}}
	if ($output_sig)
		{
		return md5($string);
		}
	else
		{
		return http_build_query($params) . "&api_sig=" . md5($string);
		}
	}



function do_post_request($url, $data, $optional_headers = null)
{
  global $lang;
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception(str_replace(array("%url", "%php_errormsg"), array($url, $php_errormsg), $lang["problem-with-url"]));
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception(str_replace(array("%url", "%php_errormsg"), array($url, $php_errormsg), $lang["problem-reading-data"]));
  }
  return $response;
}

function flickr_get_response_tag($xml,$response_tag)
	{
	$start=strpos($xml,"<" . $response_tag . ">");
	$end=strpos($xml,"</" . $response_tag . ">");	
	
	if ($start===false) {echo "<pre>" . htmlspecialchars($xml) . "</pre>";return false;}
	
	return trim(substr($xml,$start+strlen($response_tag) + 2,$end-$start-strlen($response_tag)-2));
	}

function flickr_update_progress_file($note){
	global $progress_file;
	$fp = fopen($progress_file, 'w');		
	$filedata=$note;
	fwrite($fp, $filedata);
	fclose($fp);
}

function flickr_check_token($userref){
	global $flickr_api_key,$flickr_token,$lang,$theme,$baseurl,$last_xml;
	
	$flickr_token=sql_value("select flickr_token value from user where ref='$userref'","");
	$validtoken=false;
	if ($flickr_token!=""){
		# Check the token
		$flickr_token=flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.auth.checkToken","auth_token"=>$flickr_token),"token");	
		if ($flickr_token!==false){
			$validtoken=true;
			$start=strpos($last_xml,"fullname=");
			$end=strpos($last_xml,"\"",$start+10);
			$fullname=substr($last_xml,$start+10,$end-$start-10);
			?>
			<p><?php echo $lang["flickrloggedinas"] . " <strong>" . htmlspecialchars($fullname) . "</strong>" ?> (<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/plugins/flickr_theme_publish/pages/sync.php?theme=<?php echo $theme ?>&logout=true"><?php echo $lang["logout"] ?></a>)</p>
			<?php
		}
	}
	return $validtoken;
}

function flickr_check_frob($userref){
	global $flickr_api_key,$flickr_frob,$lang,$auth_url,$theme;
	
	$flickr_frob=sql_value("select flickr_frob value from user where ref='$userref'","");
	$valid_frob=false;
	$validtoken=false;
	if($flickr_frob!=""){
		#echo "check existing frob $flickr_frob<br>";
		$flickr_token=flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.auth.getToken","frob"=>$flickr_frob),"token");	
		if ($flickr_token!==false){
			$valid_frob=true;
			$validtoken=true;
			sql_query("update user set flickr_token='" . escape_check($flickr_token) . "' where ref='$userref'");
		}
	}
	
	if(!$valid_frob){
		$flickr_frob=flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.auth.getFrob"),"frob");			

		sql_query("update user set flickr_frob='" . escape_check($flickr_frob) . "' where ref='$userref'");

	
		# Authenticate frob
		$auth_url="http://flickr.com/services/auth/?" . flickr_sign(array("api_key"=>$flickr_api_key,"perms"=>"write", "frob"=>$flickr_frob));
		?>
		<p>&gt;&nbsp;<a target=_blank href="<?php echo $auth_url; ?>"><?php echo $lang["flickrnotloggedin"] ?></a></p>
		<p><?php echo $lang["flickronceloggedinreload"] ?></p>
		<form method="post" action="sync.php?theme=<?php echo $theme ?>"><input type="submit" name="reload" value="<?php echo $lang["reload"] ?>"></form>
		<?php
	}
	return $validtoken;
}

function flickr_get_photoset(){
	global $flickr_api_key,$flickr_token,$last_xml,$theme;
	#$photoset=0;

	# Make sure a photoset exists for this theme
	flickr_api("http://flickr.com/services/rest/",array("api_key"=>$flickr_api_key,"method"=>"flickr.photosets.getList","auth_token"=>$flickr_token));
	
	#echo nl2br(htmlspecialchars($last_xml));
	
	# List all photosets.
	$p = xml_parser_create();
	xml_parse_into_struct($p, $last_xml, $vals, $index);
	xml_parser_free($p);
	#echo "<pre>Index array\n";
	#print_r($index);
	#echo "\nVals array\n";
	#print_r($vals);

	$last_photoset_id="";
	$photosets=array();
	for ($n=0;$n<count($vals);$n++){
		if (isset($vals[$n]["tag"]) && $vals[$n]["tag"]=="PHOTOSET" && isset($vals[$n]["attributes"]["ID"])){
			# Read the photoset ID and set, ready for nested title tag later
			$last_photoset_id=$vals[$n]["attributes"]["ID"];
		}
		if (isset($vals[$n]["tag"]) && $vals[$n]["tag"]=="TITLE"){
			# Read the title and set
			$photosets[$vals[$n]["value"]]=$last_photoset_id;
		}
	}

	# $photosets now contains a list of all the user's photosets.
	# Look for the name of the current collection.
	$photoset_name=sql_value("select name value from collection where ref='$theme'","");
	if (array_key_exists($photoset_name,$photosets)){
		# Name already exists. Just use this photoset ID.
		$photoset=$photosets[$photoset_name];
	}
	else{
		# Name does not exist. Set to zero so it is created during sync.
		$photoset=0;
	}
	$photoset_array=array($photoset_name,$photoset);
	return $photoset_array;
}
?>
