<?php

require_once dirname(__FILE__) . '/../lib/Google/autoload.php';

function youtube_publish_initialize()
    {
    global $ref, $userref, $baseurl, $youtube_publish_client_id, $youtube_publish_client_secret,$language, $client,$youtube;

    $client = new Google_Client();
    $client->setClientId($youtube_publish_client_id);
    $client->setClientSecret($youtube_publish_client_secret);
    /*
    * This OAuth 2.0 access scope allows for full read/write access to the
    * authenticated user's account and requires requests to use an SSL connection.
    */
    $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
    $redirect = $baseurl . "/plugins/youtube_publish/pages/youtube_upload.php";
    $client->setRedirectUri($redirect);
    
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    
    $access_tokens = sql_query("select youtube_access_token,youtube_refresh_token from user where ref='$userref'");
    $access_token=$access_tokens[0]["youtube_access_token"];
    $refresh_token=$access_tokens[0]["youtube_refresh_token"];
    
    if ($access_token=="" ||  $refresh_token=="")
        {
        if(getval("code","")=="")
            {
            get_youtube_authorization_code();
            exit();
            }
        else
            { 
            global $youtube_publish_client_id, $youtube_publish_client_secret;
          
            $authresponse=$client->authenticate(getvalescaped("code",""));
            
            $access_token_array = json_decode($authresponse);
            
            $access_token = $access_token_array->access_token; 
            if (isset($access_token_array->refresh_token))
                {
                $refresh_token = $access_token_array->refresh_token;
                debug("YouTube plugin: Refresh token: " . $refresh_token);
                sql_query("update user set youtube_refresh_token='$refresh_token' where ref='$userref'");
                }
            else
                {
                delete_youtube_tokens();
                get_youtube_authorization_code();
                exit();
                }
            
            debug("YouTube plugin: Retrieved access token: " . $access_token);               
            sql_query("update user set youtube_access_token='$access_token' where ref='$userref'");
            
            }
        }

    try
        {
        $client->setAccessToken(json_encode(array("access_token"=>$access_token)));
        }
    catch (Google_ServiceException $e)
        {
        $errortext = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
    catch (Google_Exception $e)
        {
        $errortext = sprintf('<p>A client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
       
    if($client->isAccessTokenExpired())
        {
        try
            {
            $client->refreshToken($refresh_token);
            }
        catch (Google_ServiceException $e)
            {
            delete_youtube_tokens();
            get_youtube_authorization_code();
            exit();
            }
        catch (Google_Exception $e)
            {
            delete_youtube_tokens();
            get_youtube_authorization_code();
            exit();
            }

        }
    
    // Define an object that will be used to make all API requests.
    try
        {
        $youtube = new Google_Service_YouTube($client);
        # Get user account details and store these so we can tell which account they will be uploading to

        // Call the API's channels.list method with mine parameter to fetch authorized user's channel.
        $listResponse = $youtube->channels->listChannels('snippet', array(
                 'mine' => 'true',
              ));

        $youtube_username = escape_check($listResponse[0]['snippet']['title']);
        sql_query("update user set youtube_username='$youtube_username' where ref='$userref'");
        }
    catch (Google_ServiceException $e)
        {
        $errortext = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
    catch (Google_Exception $e)
        {
        $errortext = sprintf('<p>A client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
        
    if(isset($errortext))
        {
        return array(false,$errortext);
        }   
    return array(true,"");
    }

function get_youtube_authorization_code()
    {
    global $ref, $baseurl, $youtube_publish_client_id, $youtube_publish_client_secret,$language, $client,$youtube;  
              
    // If the user hasn't authorized the app, initiate the OAuth flow
    $state = $ref;
    $client->setState($state);
    $_SESSION['state'] = $state;
    $authUrl = $client->createAuthUrl();
             
    header("Location: " . $authUrl);
            
    }

function delete_youtube_tokens()
    {
    global $userref;
    sql_query("update user set youtube_access_token='', youtube_refresh_token='' where ref='$userref'");
    }

function upload_video()
    {
    global $lang, $video_title, $video_description, $video_keywords, $video_category, $filename, $ref, $video_status, $youtube_video_url, $youtube_publish_developer_key,$youtube_chunk_size, $client,$youtube;
    debug("youtube_publish: uploading video resource ID:" . $ref);
    $errortext="";
    try {           
        # Get file info for upload
        $resource=get_resource_data($ref);
        $alternative=-1;
        $ext=$resource["file_extension"];

        $videoPath=get_resource_path($ref,true,"",false,$ext,-1,1,false,"",$alternative);
                    
        // Create a snippet with title, description, tags and category ID
        // Create an asset resource and set its snippet metadata and type.
        // This example sets the video's title, description, keyword tags, and
        // video category.
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($video_title);
        $snippet->setDescription($video_description);
        $snippet->setTags(array($video_keywords));

        // Numeric video category. See                
        // https://developers.google.com/youtube/v3/docs/videoCategories/list 
        $snippet->setCategoryId($video_category);

        // Set the video's status to "public". Valid statuses are "public",
        // "private" and "unlisted".
        $status = new Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = $video_status;

        // Associate the snippet and status objects with a new video resource.
        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        if(!is_numeric($youtube_chunk_size)){$youtube_chunk_size=10;}

        $chunkSizeBytes = intval($youtube_chunk_size) * 1024 * 1024;

        // Setting the defer flag to true tells the client to return a request which can be called
        // with ->execute(); instead of making the API call immediately.
        $client->setDefer(true);

        // Create a request for the API's videos.insert method to create and upload the video.
        $insertRequest = $youtube->videos->insert("status,snippet", $video);

        // Create a MediaFileUpload object for resumable uploads.
        $media = new Google_Http_MediaFileUpload(
            $client,
            $insertRequest,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($videoPath));

        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($videoPath, "rb");
        while (!$status && !feof($handle)) 
            {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
            }

        fclose($handle);

        // If you want to make other calls after the file upload, set setDefer back to false
        $client->setDefer(true);
                
        $youtube_new_url = "https://www.youtube.com/watch?v=" . $status['id'];

        return array(true,$youtube_new_url);
        }
    catch (Google_ServiceException $e)
        {
        $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        exit($htmlBody);
        }
    catch (Google_Exception $e)
        {
        $htmlBody = sprintf('<p>A client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        exit($htmlBody);
        }
    catch (Google_ServiceException $e)
        {
        $errortext = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
    catch (Google_Exception $e)
        {
        $errortext = sprintf('<p>A client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }        
        
    if(isset($errortext))
        {
        return array(false,$errortext);
        }    
    }
        
function youtube_upload_get_categories()
    {
    global $client,$youtube;
    
    try
        {
        $listResponse = $youtube->videoCategories->listVideoCategories('snippet', array(
                'regionCode' => 'GB',
            ));
        }
    catch (Google_ServiceException $e)
        {
        $errortext = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
    catch (Google_Exception $e)
        {
        $errortext = sprintf('<p>A client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }        
        
    if(isset($errortext))
        {
        return $errortext;
        }
            
    $categories=$listResponse['items'];
    $availablecategories=array();
    foreach($categories as $category)
        {
        $availablecategories[$category["id"]]=$category["snippet"]["title"];
        }
    return $availablecategories;
    }
    
