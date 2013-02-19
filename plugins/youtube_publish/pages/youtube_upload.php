<?php
include "../../../include/db.php";
include "../../../include/general.php";
include "../../../include/authenticate.php";
include "../../../include/search_functions.php";
include "../../../include/resource_functions.php";
include "../include/youtube_functions.php";

global $baseurl, $baseurl_short,$view_title_field, $youtube_publish_url_field, $youtube_publish_allow_multiple;


$deletetokens=getvalescaped("deletetokens",false);
if ($deletetokens)
	{
	sql_query("update user set youtube_access_token='', youtube_refresh_token='', youtube_username='' where ref='$userref'");
	}

$ref=getvalescaped("resource","");
if ($ref==""){$ref=getvalescaped("state","");}

# Load access level
$access=get_resource_access($ref);

# check permissions (error message is not pretty but they shouldn't ever arrive at this page unless entering a URL manually)
if ($access!=0) 
		{
		exit($lang["youtube_publish_accessdenied"]);
		}

# fetch the current search (for finding similar matches)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
$starsearch=getvalescaped("starsearch","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);
$private=getval("private",false);
if ($youtube_publish_url_field>0){$youtube_url=sql_value("select value from resource_data where resource='$ref' and resource_type_field=$youtube_publish_url_field", "");}
$youtube_error=false;

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

if ($youtube_publish_client_id=="" || $youtube_publish_client_secret=="")
		{
		exit ($lang["youtube_publish_notconfigured"] . " <a href=\"$baseurl/plugins/youtube_publish/pages/setup.php\">$baseurl/plugins/youtube_publish/pages/setup.php</a>");
		}

# next / previous resource browsing
	
$go=getval("go","");
if ($go!="")
	{
	# Re-run the search and locate the next and previous records.
	$result=do_search($search,$restypes,$order_by,$archive,240+$offset+1);
	if (is_array($result))
		{
		# Locate this resource
		$pos=-1;
		for ($n=0;$n<count($result);$n++)
			{
			if ($result[$n]["ref"]==$ref) {$pos=$n;}
			}
		if ($pos!=-1)
			{
			if (($go=="previous") && ($pos>0)) {$ref=$result[$pos-1]["ref"];}
			if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($offset+72)) {$offset=$pos+1;}} # move to next page if we've advanced far enough
			}
		else
			{
			?>
			<script type="text/javascript">
			alert("<?php echo $lang["resourcenotinresults"] ?>");
			</script>
			<?php
			}
		}
	}


# Check we have an access token. If not, get one
$youtube_access_token = sql_value("select youtube_access_token as value from user where ref='$userref'","");
//$youtube_refresh_token = sql_value("select youtube_refresh_token as value from user where ref='$userref'","");
if ($youtube_access_token=="")
	{
	# We don't have a token, do we have a code?
	$code= getvalescaped('code',"");
	if ($code=="")
		{
		# no? Then get a temp code, we will be returned to this page afterwards with it in the querystring
		get_youtube_authorization_code();
		exit();
		}
	else
		{
		// Use the code to get an access token
		$access_token_response=get_youtube_access_token();
		if (!$access_token_response){$youtube_error=$lang["youtube_access_failed"];}
		}
	}

$youtube_username = sql_value("select youtube_username as value from user where ref='$userref'","");
	
						
if( isset( $_POST['video_title'] ) && isset( $_POST['video_description'] ) ) 
	{
	$video_title = getvalescaped("video_title","");
	$video_description = getval("video_description","");	
	$video_keywords = getvalescaped("video_keywords","");
    $video_category = getvalescaped("video_category",""); // This is the uploading video category. There are only certain categories that are accepted. See below 
	$filename=get_data_by_field($ref,$filename_field);
	//Set values so that upload can be retried if for example the access token ahs expired and needed to be refreshed
	$uploadsuccess=false;
	$retry=-true;
	while (!$uploadsuccess & $retry)
		{
		list ($uploadsuccess, $message, $retry) = upload_video($youtube_access_token);
		if (!$uploadsuccess)
			{
			$youtube_access_token = sql_value("select youtube_access_token as value from user where ref='$userref'","");
			}
		else
			{
			$youtube_new_url = $message;
			if ($youtube_publish_url_field>0)
				{
				if ($youtube_publish_allow_multiple)
					{
					update_field($ref,$youtube_publish_url_field,$youtube_url . "<br><a href=\"" . $youtube_new_url . "\" target=\"_blank\">" . $youtube_new_url . "</a>");
					$youtube_old_url = $youtube_url;
					$youtube_url = $youtube_url . "<br><a href=\"" . $youtube_new_url . "\" target=\"_blank\">" . $youtube_new_url . "</a>";
					}
				else
					{
					update_field($ref,$youtube_publish_url_field,"<a href=\"" . $youtube_new_url . "\" target=\"_blank\">" . $youtube_new_url . "</a>");
					$youtube_old_url = $youtube_url;
					$youtube_url = $youtube_new_url;
					}
				}
			resource_log($ref,'e',$youtube_publish_url_field?$youtube_publish_url_field:0,$lang["youtube_publish_log_share"],$fromvalue=$youtube_old_url,$tovalue=$youtube_new_url);
			}			
		}
		
	
	
	
	
	}

			
$title=get_data_by_field($ref,$youtube_publish_title_field);

$description=get_data_by_field($ref,$youtube_publish_descriptionfield);

$video_keywords="";
foreach ($youtube_publish_keywords_fields as $youtube_publish_keywords_field)
	{
	$resource_keywords=get_data_by_field($ref,$youtube_publish_keywords_field);
	$video_keywords.=$resource_keywords;
	}

		
include "../../../include/header.php";



?>

<script language="JavaScript">
function confirmSubmit()
{
var agree=confirm("<?php echo $lang["youtube_publish_legal_warning"]; ?>");
if (agree)
return true ;
else
return false ;
}

</script>


<a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo $ref?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>" onClick="return CentralSpaceLoad(this,true);">&lt; <?php echo $lang["backtoresourceview"]?></a></p>


<div class="backtoresults">
<a class="prevLink" href="<?php echo $baseurl_short?>plugins/youtube_publish/pages/youtube_upload.php?ref=<?php echo $ref?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&go=previous&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this);">&lt;&nbsp;<?php echo $lang["previousresult"]?></a>
<?php 
hook("viewallresults");
?>
|
<a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["viewallresults"]?></a>
|
<a class="nextLink" href="<?php echo $baseurl_short?>plugins/youtube_publish/pages/youtube_upload.php?ref=<?php echo $ref?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&go=next&<?php echo hook("nextpreviousextraurl") ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["nextresult"]?>&nbsp;&gt;</a>
</div>

<?php
 
 
if (isset($youtube_new_url) && $youtube_new_url)
			{
			echo "<div><p><b>" . $lang["youtube_publish_success"]   . "</b></p></div>";	
			}
else
	{
	if ($youtube_error) 
		{
		echo "<div class=\"FormIncorrect\"><p>" . $youtube_error  . "</p></div>";		
		}
	}
?>

<div class="Question">

<p> 
<?php echo $lang["youtube_publish_existingurl"] . " :- <p>";
	if ($youtube_url!="")
		{
		echo $youtube_url;
		if (!$youtube_publish_allow_multiple)
			{
			echo "</p><div class=\"FormIncorrect\"><p><br>" . $lang["youtube_publish_alreadypublished"] . "</p></div>";
			exit();
			}
		}
	else
		{
		echo $lang["youtube_publish_notuploaded"];
		}
echo "</p>";
?>

</div>

<?php
if ($youtube_username != '')
	{	
	?>	
	<div class="Question" >
	<?php echo "<p>" . $lang["youtube_publishloggedinas"] . " : " . "<strong>" . $youtube_username . "</strong><br></p>";
	echo "<p><a href=\"" . $baseurl . "/plugins/youtube_publish/pages/youtube_upload.php?resource=" . $ref . "&deletetokens=true" . "\">&gt; " . $lang["youtube_publish_change_login"] . "</a></p>";?>
	</div>
	<?php ;}?>

<form action="<?php echo $baseurl ?>/plugins/youtube_publish/pages/youtube_upload.php?resource=<?php echo $ref ?>" method="post">
	<div class="Question" >
		<label for="video_title">Video Title</label>
		<input type="text" class="stdwidth" name="video_title" value="<?php echo $title; ?>"/>
		<br>
		<label for="video_description">Video Description</label>
		<textarea class="stdwidth" rows="6" columns="50" id="video-description" name="video_description"><?php echo strip_tags($description) . "\r\n" . $baseurl . "/?r=" . $ref; ?></textarea>
		<br>
		<label for="video_description">Video Tags</label>
		<textarea class="stdwidth" rows="6" columns="50" id="video_keywords" name="video_keywords"><?php echo htmlspecialchars($video_keywords); ?></textarea>
		<br>
	</div>	
	<div class="Question" >
	
		<p><?php echo $lang["youtube_publish_as"] ?>
		<select name="private">
		<option value="false"><?php echo $lang["youtube_publish_public"] . "&nbsp;&nbsp;" ?></option>
		<option value="true" <?php if ($private) { ?>selected<?php } ?>><?php echo $lang["youtube_publish_private"] . "&nbsp;&nbsp;" ?></option>
		</select>
		</p>
	</div>	
	
	<div class="Question" >
	
		<p><?php echo $lang["youtube_publish_category"] ?>
		<select name="video_category">
			<option value="Film"><?php echo $lang["youtube_publish_film"] . "&nbsp;&nbsp;" ?></option>
			<option value="Autos"><?php echo $lang["youtube_publish_autos"] . "&nbsp;&nbsp;" ?></option>
			<option value="Music"><?php echo $lang["youtube_publish_music"] . "&nbsp;&nbsp;" ?></option>
			<option value="Animals"><?php echo $lang["youtube_publish_animals"] . "&nbsp;&nbsp;" ?></option>
			<option value="Sports"><?php echo $lang["youtube_publish_sports"] . "&nbsp;&nbsp;" ?></option>
			<option value="Travel"><?php echo $lang["youtube_publish_travel"] . "&nbsp;&nbsp;" ?></option>
			<option value="Shortmov"><?php echo $lang["youtube_publish_shortmov"] . "&nbsp;&nbsp;" ?></option>
			<option value="Games"><?php echo $lang["youtube_publish_games"] . "&nbsp;&nbsp;" ?></option>
			<option value="Videblog"><?php echo $lang["youtube_publish_videblog"] . "&nbsp;&nbsp;" ?></option>
			<option value="People"><?php echo $lang["youtube_publish_people"] . "&nbsp;&nbsp;" ?></option>
			<option value="Comedy"><?php echo $lang["youtube_publish_comedy"] . "&nbsp;&nbsp;" ?></option>
			<option value="Entertainment"><?php echo $lang["youtube_publish_entertainment"] . "&nbsp;&nbsp;" ?></option>
			<option value="News"><?php echo $lang["youtube_publish_news"] . "&nbsp;&nbsp;" ?></option>
			<option value="Howto"><?php echo $lang["youtube_publish_howto"] . "&nbsp;&nbsp;" ?></option>
			<option value="Education"><?php echo $lang["youtube_publish_education"] . "&nbsp;&nbsp;" ?></option>
			<option value="Tech"><?php echo $lang["youtube_publish_tech"] . "&nbsp;&nbsp;" ?></option>
			<option value="Nonprofit"><?php echo $lang["youtube_publish_nonprofit"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies"><?php echo $lang["youtube_publish_movies"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_anime_action"><?php echo $lang["youtube_publish_movies_anime_action"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_action_adventure"><?php echo $lang["youtube_publish_movies_action_adventure"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_classics"><?php echo $lang["youtube_publish_movies_classics"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_comedy"><?php echo $lang["youtube_publish_movies_comedy"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_documentary"><?php echo $lang["youtube_publish_movies_documentary"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_drama"><?php echo $lang["youtube_publish_movies_drama"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_family"><?php echo $lang["youtube_publish_movies_family"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_foreign"><?php echo $lang["youtube_publish_movies_foreign"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_horror"><?php echo $lang["youtube_publish_movies_horror"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_sci_fi_fantasy"><?php echo $lang["youtube_publish_movies_sci_fi_fantasy"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_thriller"><?php echo $lang["youtube_publish_movies_thriller"] . "&nbsp;&nbsp;" ?></option>
			<option value="Movies_shorts"><?php echo $lang["youtube_publish_movies_shorts"] . "&nbsp;&nbsp;" ?></option>
			<option value="Shows"><?php echo $lang["youtube_publish_shows"] . "&nbsp;&nbsp;" ?></option>
			<option value="Trailers"><?php echo $lang["youtube_publish_trailers"] . "&nbsp;&nbsp;" ?></option>

		</select>
		</p>
	</div>	
	
	<input type="submit" value="Upload" onClick="return confirmSubmit()"/>
	
	
</form> 
	
<?php





include "../../../include/footer.php";
	
?>