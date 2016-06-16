<?php

function HookRss2AllSearchbarbeforebottomlinks()
{
 	global $baseurl,$lang,$userpassword,$username,$api_scramble_key;
 	$skey = md5($api_scramble_key.make_api_key($username,$userpassword)."!last50"); 
?>
<p><i class="fa fa-fw fa-rss"></i>&nbsp;<a href="<?php echo $baseurl?>/plugins/rss2/pages/rssfilter.php?key=<?php echo make_api_key($username,$userpassword);?>&search=!last50&skey=<?php echo urlencode($skey); ?>"><?php echo $lang["new_content_rss_feed"]; ?></a></p>
<?php
}

