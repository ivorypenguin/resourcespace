<?php
# English
# Language File for the ResourceSpace YouTube Plugin
# -------
#
#
$lang["youtube_publish_title"]="YouTube Publishing";
$lang["youtube_publish_linktext"]="Publish to YouTube";
$lang["youtube_publish_configuration"]="Publish to YouTube - Setup";
$lang["youtube_publish_notconfigured"] = "YouTube upload plugin not configured. Please ask your administrator to configure the plugin at";
$lang["youtube_publish_legal_warning"] = "By clicking 'OK' you certify that you own all rights to the content or that you are authorized by the owner to make the content publicly available on YouTube, and that it otherwise complies with the YouTube Terms of Service located at http://www.youtube.com/t/terms.";
$lang['youtube_publish_resource_types_to_include']="Select valid YouTube Resource Types";
$lang["youtube_publish_mappings_title"]="ResourceSpace - YouTube field mappings";
$lang["youtube_publish_title_field"]="Title field";
$lang["youtube_publish_descriptionfields"]="Description fields";
$lang["youtube_publish_keywords_fields"]="Tag fields";
$lang["youtube_publish_url_field"]="Metadata field to store YouTube URL";
$lang["youtube_publish_allow_multiple"]="Allow multiple uploads of the same resource?";
$lang["youtube_publish_log_share"]="Shared on YouTube";
$lang["youtube_publish_unpublished"]="unpublished"; 
$lang["youtube_publishloggedinas"]="You will be publishing to the YouTube account : %youtube_username%"; # %youtube_username% will be replaced, e.g. You will be publishing to the YouTube account : My own RS channel
$lang["youtube_publish_change_login"]="Use a different YouTube account";
$lang["youtube_publish_accessdenied"]="You do not have permission to publish this resource";
$lang["youtube_publish_alreadypublished"]="This resource has already been published to YouTube.";
$lang["youtube_access_failed"]="Failed to access YouTube upload service interface. Please contact your administrator or check your configuration. ";
$lang["youtube_publish_video_title"]="Video title";
$lang["youtube_publish_video_description"]="Video description";
$lang["youtube_publish_video_tags"]="Video tags";
$lang["youtube_publish_access"]="Set access";
$lang["youtube_public"]="public";
$lang["youtube_private"]="private";
$lang["youtube_publish_public"]="Public";
$lang["youtube_publish_private"]="Private";
$lang["youtube_publish_unlisted"]="Unlisted";
$lang["youtube_publish_button_text"]="Publish";
$lang["youtube_publish_authentication"]="Authentication";
$lang["youtube_publish_use_oauth2"]="Use OAuth 2.0?";
$lang["youtube_publish_oauth2_advice"]="<p><strong>YouTube OAuth 2.0 Instructions</strong><br></p><p>To set up this plugin you need to setup OAuth 2.0 as all other authentication methods are officially deprecated. For this you need to register your ResourceSpace site as a project with Google and get an OAuth client id and secret. There is no cost involved.</p><list><li>Log on to Google with any valid Google account (this does not need to be related to your YouTube account), then go to <a href=\"https://console.developers.google.com\" target=\"_blank\">https://console.developers.google.com</a></li><li>Create a new project (the name and ID don't matter, they are for your reference)</li><li>Expand 'APIs & auth', click APIs and then click the YouTube Data API</li><li>Click 'Enable API'</li><li>On the left hand side Select 'Credentials' and then 'Create new client ID'</li><li>Select 'Web Application' and click 'Configure consent screen'</li><li>Select an email address and enter a product name, then click 'Save'</li><li>Fill in the authorized javascript origins with your system base URL and the redirect URL with the callback URL specified at the top of this page and click 'Create Client ID'</li><li>Note down the client ID and secret then enter these details below</li><li>(Optional) Add a developer key. This is not currently essential but may become so. A developer key uniquely identifies a product that is submitting an API request. Please visit <a href=\"http://code.google.com/apis/youtube/dashboard/\" target=\"_blank\" >http://code.google.com/apis/youtube/dashboard/</a> to obtain a developer key.</li></list>";
$lang["youtube_publish_developer_key"]="Developer key"; 
$lang["youtube_publish_oauth2_clientid"]="Client ID";
$lang["youtube_publish_oauth2_clientsecret"]="Client Secret";
$lang["youtube_publish_base"]="Base URL";
$lang["youtube_publish_callback_url"]="Callback URL";
$lang["youtube_publish_username"]="YouTube Username";
$lang["youtube_publish_password"]="YouTube Password";
$lang["youtube_publish_existingurl"] = "Existing YouTube URL :- ";
$lang["youtube_publish_notuploaded"] = "Not uploaded";
$lang["youtube_publish_failedupload_error"] = "Upload error";
$lang["youtube_publish_success"] = "Video successfully published!";
$lang["youtube_publish_renewing_token"] = "Renewing access token";
$lang["youtube_publish_category"]="Category";
$lang["youtube_publish_category_error"]="Error retrieving YouTube categories: - ";
$lang["youtube_chunk_size"]="Chunk size to use when uploading to YouTube (MB)";
$lang["youtube_publish_add_anchor"]="Add anchor tags to URl when saving to YouTube URL metadata field?";

/*
No longer used since we use the API to retrieve categories

$lang["youtube_publish_film"]="Film & Animation";
$lang["youtube_publish_autos"]="Autos & Vehicles";
$lang["youtube_publish_music"]="Music";
$lang["youtube_publish_animals"]="Pets &amp; Animals";
$lang["youtube_publish_sports"]="Sports";
$lang["youtube_publish_travel"]="Travel &amp; Events";
$lang["youtube_publish_games"]="Gaming";
$lang["youtube_publish_people"]="People & Blogs";
$lang["youtube_publish_comedy"]="Comedy";
$lang["youtube_publish_entertainment"]="Entertainment";
$lang["youtube_publish_news"]="News &amp; Politics";
$lang["youtube_publish_howto"]="Howto &amp; Style";
$lang["youtube_publish_education"]="Education";
$lang["youtube_publish_tech"]="Science &amp; Technology";
$lang["youtube_publish_nonprofit"]="Nonprofits & Activism";
*/