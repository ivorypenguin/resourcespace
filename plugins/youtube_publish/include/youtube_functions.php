<?php

function get_youtube_authorization_code()
	{
	global $ref, $baseurl, $youtube_publish_client_id, $language;
	$url = "https://accounts.google.com/o/oauth2/auth";

		$params = array(
			"response_type" => "code",
			"client_id" => $youtube_publish_client_id,
			"redirect_uri" => "$baseurl/plugins/youtube_publish/pages/youtube_upload.php",
			"scope" => "https://gdata.youtube.com",
			"access_type" => 'offline',
			"approval_prompt" => 'force',
			"state" => $ref,
			"hl"=>$language
			);

		$request_to = $url . '?' . http_build_query($params);

		header("Location: " . $request_to);
	}

function delete_youtube_tokens()
	{
	global $userref;
	sql_query("update user set youtube_access_token='', youtube_refresh_token='' where ref='$userref'");
	}

function get_youtube_access_token($refresh=false)
	{
	global $baseurl, $userref,$youtube_publish_client_id, $youtube_publish_client_secret, $youtube_publish_callback_url, $code;
	$url = 'https://accounts.google.com/o/oauth2/token';

	if ($refresh)
		{
		$refresh_token = sql_value("select youtube_refresh_token as value from user where ref='$userref'","");
		if ($refresh_token=="")
			{
			get_youtube_authorization_code();
			exit();
			}
		$params = array(
			"client_id" => $youtube_publish_client_id,
			"client_secret" => $youtube_publish_client_secret,
			"refresh_token" => $refresh_token,
			"grant_type" => "refresh_token"
			);
		}
	else
		{
		$params = array(
			"code" => $code,
			"client_id" => $youtube_publish_client_id,
			"client_secret" => $youtube_publish_client_secret,
			"redirect_uri" => $baseurl . $youtube_publish_callback_url,
			"grant_type" => "authorization_code"
			);
		}


	$curl = curl_init( "https://accounts.google.com/o/oauth2/token");
	curl_setopt( $curl, CURLOPT_HEADER, "Content-Type:application/x-www-form-urlencoded" );
	curl_setopt( $curl, CURLOPT_POST, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $params );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 1 );

	$response = json_decode( curl_exec( $curl ), true );

	curl_close( $curl );
	//exit (print_r($response));

	if (isset( $response["error"]))
		{
		sql_query("update user set youtube_access_token='' where ref='$userref'");
		//exit("ERROR: bad response" . print_r($response));
		get_youtube_authorization_code();
		exit();
		}
	if (isset($response["access_token"]))
		{
		$access_token = escape_check($response["access_token"]);
		sql_query("update user set youtube_access_token='$access_token' where ref='$userref'");
		if (isset($response["refresh_token"]))
			{
			$refresh_token = escape_check($response["refresh_token"]);
			sql_query("update user set youtube_refresh_token='$refresh_token' where ref='$userref'");
			}
		debug("YouTube plugin: Access token: " . $access_token);
		debug("YouTube plugin: Refresh token: " . $refresh_token);
		}

	# Get user account details and store these so we can tell which account they will be uploading to

	$headers = array( "Authorization: Bearer " . $access_token,
                 "GData-Version: 2"
				 );
	$curl = curl_init( "https://gdata.youtube.com/feeds/api/users/default");
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $curl, CURLOPT_HTTPGET, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 1 );

	#$response = json_decode( curl_exec( $curl ), true );
	$response = curl_exec( $curl );
	$userdataxml = new SimpleXmlElement($response, LIBXML_NOCDATA);
	//exit(print_r($userdataxml));
	$youtube_username = escape_check($userdataxml->title);
	sql_query("update user set youtube_username='$youtube_username' where ref='$userref'");

	return $access_token;

	}


function upload_video($access_token="")
	{
	global $lang, $video_title, $video_description, $video_keywords, $video_category, $filename, $ref, $status, $youtube_video_url, $youtube_publish_developer_key;


	# Set status as necessary
	if ($status=="private"){$private = '<yt:private/>';}
		else{$private = '';}
	if ($status=="unlisted"){$accesscontrol = '<yt:accessControl action="list" permission="denied"/>';}
		else{$accesscontrol = '';}

    $data= '<?xml version="1.0"?>
                <entry xmlns="http://www.w3.org/2005/Atom"
                  xmlns:media="http://search.yahoo.com/mrss/"
                  xmlns:yt="http://gdata.youtube.com/schemas/2007">
                  <media:group>
                    <media:title type="plain">' . htmlspecialchars( $video_title ) . '</media:title>
					' . $private . '
                    <media:description type="plain">' . htmlspecialchars( $video_description ) . '</media:description>
                    <media:category
                      scheme="http://gdata.youtube.com/schemas/2007/categories.cat">' . htmlspecialchars($video_category) .'</media:category>
                    <media:keywords>' . htmlspecialchars($video_keywords) . '</media:keywords>
                  </media:group>
				  ' . $accesscontrol . '
                </entry>';

	$data.= "\r\n\r\n";

	#####	For resumable

	$headers = array( "Authorization: Bearer " . $access_token,
                 "GData-Version: 2",
                 "X-GData-Key: key=" . $youtube_publish_developer_key,
                 "Content-length: " . strlen($data),
                 "Content-Type: application/atom+xml; charset=UTF-8",
				 "Slug: " . htmlspecialchars($filename),
				 "Connection: close" ,
				 "Expect:"
				 );

	$youtube_upload_url="http://uploads.gdata.youtube.com/resumable/feeds/api/users/default/uploads";

	$curl = curl_init($youtube_upload_url);


	//curl_setopt( $curl, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );;
	curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
	curl_setopt( $curl, CURLINFO_HEADER_OUT , 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_POST, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 0 );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
	curl_setopt($curl, CURLOPT_HEADER, TRUE);

	$response = curl_exec( $curl );


	if(!curl_errno($curl))
		{
		$info = curl_getinfo($curl);
		if ($info['http_code']==401)
			{
			curl_close( $curl );
			get_youtube_access_token(true);
			return array(false,$lang["youtube_publish_renewing_token"],true);
			}
		}
	else
		{
		curl_close( $curl );
		$upload_result=$lang["error"] . curl_error($curl);
		return array(false,curl_errno($curl),false);
		}

	$header = substr($response, 0, $info['header_size']);
	$retVal = array();
	$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach( $fields as $field )
			{
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) )
					{
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
					}
				else
					{
                    $retVal[$match[1]] = trim($match[2]);
					}
				}
			}
	if (isset($retVal['Location']))
		{
		$location =  $retVal['Location'];
		}
	else
		{
		$upload_result=$lang["youtube_publish_failedupload_nolocation"];
		curl_close( $curl );
		return array(false,$upload_result,false);
		}

	curl_close( $curl );

	# Finally upload the file

	# Get file info for upload
	$resource=get_resource_data($ref);
	$alternative=-1;
	$ext=$resource["file_extension"];
	$path=get_resource_path($ref,true,"",false,$ext,-1,1,false,"",$alternative);

	# We assign a default mime-type, in case we can find the one associated to the file extension.
	$mime="application/octet-stream";

	# Get mime type via exiftool if possible
	$exiftool_fullpath = get_utility_path("exiftool");
	if ($exiftool_fullpath!=false)
		{
		$command=$exiftool_fullpath . " -s -s -s -t -mimetype " . escapeshellarg($path);
		$mime=run_command($command);
		}

	# Override or correct for lack of exiftool with config mappings
	if (isset($mime_type_by_extension[$ext]))
		{
		$mime = $mime_type_by_extension[$ext];
		}


	$video_file = fopen($path, 'rb');

	$curl = curl_init($location);

	curl_setopt($curl, CURLOPT_PUT, 1);
	curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($curl, CURLOPT_INFILE, $video_file); // file pointer
	curl_setopt($curl, CURLOPT_INFILESIZE, filesize($path));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, $mime );
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3600);

	$response = curl_exec( $curl );
	$videoxml = new SimpleXmlElement($response, LIBXML_NOCDATA);
	$urlAtt = $videoxml->link->attributes();
	$youtube_new_url	= $urlAtt['href'];
	$youtube_urlmatch = '#http://(www\.)youtube\.com/watch\?v=([^ &\n]+)(&.*?(\n|\s))?#i';
	preg_match($youtube_urlmatch, $youtube_new_url, $matches);
	$youtube_new_url=$matches[0];
	# end of actual file upload


	fclose($video_file);
	$video_file = null;

	return array(true,$youtube_new_url,false);
	}
