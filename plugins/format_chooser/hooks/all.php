<?php

include_once dirname(__FILE__) . "/../include/utility.php";

function HookFormat_chooserAllGetdownloadurl($ref, $size, $ext, $page = 1, $alternative = -1)
	{
	global $baseurl_short,$imagemagick_preserve_profiles, $format_chooser_input_formats, $format_chooser_output_formats;
    
    // Check whether download file extension matches
    if(!in_array(strtoupper($ext),$format_chooser_output_formats))
        {return false;}
    
    // Check whether original resource file extension matches    
    $original_ext = sql_value("select file_extension value from resource where ref = '".escape_check($ref)."'",'');    
    if(!in_array(strtoupper($original_ext),$format_chooser_input_formats))
        {return false;}
    
    $profile = getvalescaped('profile' , null);
	if (!empty($profile))
		$profile = '&profile=' . $profile;
	else
		{
		$path = get_resource_path($ref, true, $size, false, $ext, -1, $page,$size=="scr" && checkperm("w") && $alternative==-1, '', $alternative);
		if (file_exists($path) && (!$imagemagick_preserve_profiles || in_array($size,array("hpr","lpr")))) // We can use the existing previews unless we need to preserve the colour profiles (these are likely to have been removed from scr size and below) 
		return false;
		}

	return $baseurl_short . 'plugins/format_chooser/pages/convert.php?ref=' . $ref . '&size='
			. $size . '&ext=' . $ext . $profile . '&page=' . $page . '&alt=' . $alternative;
	}
