<?php

function HookRss2SearchResultsbottomtoolbar()
{
 	global $baseurl, $search, $restypes, $archive, $starsearch,$lang;
 	global $userpassword,$username,$api_scramble_key;
    $apikey=make_api_key($username,$userpassword);
	$skey = md5($api_scramble_key.$apikey.$search.$archive); 
    global $k; if ($k!=""){return false;}
?>
<div class="InpageNavLeftBlock"><a href="<?php echo $baseurl?>/plugins/rss2/pages/rssfilter.php?key=<?php echo urlencode($apikey);?>&search=<?php echo urlencode($search)?>&restypes=<?php echo urlencode($restypes)?>&archive=<?php echo urlencode($archive)?>&starsearch=<?php echo urlencode($starsearch)?>&skey=<?php echo urlencode($skey); ?>">&gt;&nbsp;<?php echo $lang["rss_feed_for_search_filter"]; ?></a></div>
<?php
}

