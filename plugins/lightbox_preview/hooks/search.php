<?php

include dirname(__FILE__) . "/../include/utility.php";

function HookLightbox_previewSearchReplacefullscreenpreviewicon()
	{
	global $baseurl_short, $ref, $result, $n, $k, $search, $offset, $sort, $order_by, $archive,
			$lang, $showkeypreview, $value;

	$url = getPreviewURL($result[$n]);
	if ($url === false)
		return false;

	$showkeypreview = true;

	# Replace the link to add the 'previewlink' ID
	?>
		<span class="IconPreview"><a id="previewlink<?php echo $ref ?>" href="<?php
			echo $baseurl_short?>pages/preview.php?from=search&ref=<?php
			echo urlencode($ref)?>&ext=<?php echo $result[$n]["preview_extension"]?>&search=<?php
			echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php
			echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php
			echo urlencode($archive)?>&k=<?php echo urlencode($k)?>" title="<?php
			echo $lang["fullscreenpreview"]?>"><img src="<?php echo $baseurl_short?>gfx/interface/sp.gif" alt="<?php echo $lang["fullscreenpreview"]?>" width="22" height="12" /></a></span>
	<?php
	setLink('#previewlink' . $ref, $url, $value);
	return true;
	}

function HookLightbox_previewSearchEndofsearchpage()
	{
	addLightBox('.IconPreview a');
	}

?>
