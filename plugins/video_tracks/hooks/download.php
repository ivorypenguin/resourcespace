<?php

function HookVideo_tracksDownloadModifydownloadpath()
	{
    global $video_tracks_download_export;
	$video_track_string=getval("video_tracks_export","");
	if($video_tracks_download_export && $video_track_string!="")
		{
		global $path, $video_tracks_export_folder, $userref;
		$video_track_details=json_decode(base64_decode($video_track_string),true);
		if($video_track_details[0] !=0 && $video_track_details[0]!=$userref){return false;}
		$path=$video_tracks_export_folder . $video_track_details[1];
		//exit($path);
		return true;
		}		
	return false;
	}
