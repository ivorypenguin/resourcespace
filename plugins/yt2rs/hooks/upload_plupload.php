<?php
function Hookyt2rsUpload_pluploadupload_page_bottom()
	{
	global $userref, $yt2rs_field_id, $lang;
	$ref_user = 0 - $userref;
	$youtube_copy_path = get_data_by_field($ref_user, $yt2rs_field_id);
	if ($youtube_copy_path == "")
		{
		return false;
		}
	  else if (preg_match("/youtu.be\/[a-z1-9.-_]+/", $youtube_copy_path))
		{
		preg_match("/youtu.be\/([a-z1-9.-_]+)/", $youtube_copy_path, $matches);
		}
	  else if (preg_match("/youtube.com(.+)v=([^&]+)/", $youtube_copy_path))
		{
		preg_match("/v=([^&]+)/", $youtube_copy_path, $matches);
		}
	$ytthumb_id = $matches[1];
	$thumb_path = 'http://img.youtube.com/vi/' . $ytthumb_id . '/mqdefault.jpg';
?>
	<h1><?php
	echo $lang['yt2rs_thumb']; ?></h1>
	

<?php
	echo $thumb_path;
	}
?>
