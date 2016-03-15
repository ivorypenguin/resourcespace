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

function HookRss2SearchRender_search_actions_add_option($options)
	{
 	global $baseurl_short, $search, $restypes, $archive, $starsearch, $lang,
 	$userpassword,$username,$api_scramble_key ,$k;
    
    $c=count($options);
    
    if($k=='')
		{
		$apikey=make_api_key($username,$userpassword);
		$skey = md5($api_scramble_key.$apikey.$search.$archive); 
		
		$data_attribute['url'] = sprintf('%splugins/rss2/pages/rssfilter.php?key=%s&search=<?php echo urlencode($search)?>&restypes=%s&archive=%s&starsearch=%s&skey=%s',
			$baseurl_short,
			urlencode($apikey),
			urlencode($restypes),
			urlencode($archive),
			urlencode($starsearch),
			urlencode($skey)
		);
		$options[$c]['value']='rss';
		$options[$c]['label']=$lang["rss_feed_for_search_filter"];
		$options[$c]['data_attr']=$data_attribute;
		
		return $options;
		}
}

