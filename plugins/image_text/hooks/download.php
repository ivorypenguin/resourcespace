<?php

function HookImage_textDownloadModifydownloadfile()
	{
	global $ref, $path, $tmpfile, $userref, $usergroup,  $ext, $resource_data,
	$image_text_restypes, $image_text_override_groups, $image_text_filetypes,
	$size, $page, $use_watermark, $alternative, $image_text_height_proportion,
	$image_text_max_height, $image_text_min_height, $image_text_font, $image_text_position, $image_text_banner_position;
	
	# Return if not configured for this resource type or if user has requested no overlay and is permitted this
	if(!in_array($resource_data['resource_type'], $image_text_restypes) || !in_array(strtoupper($ext), $image_text_filetypes) || (getval("nooverlay","")!="" && in_array($usergroup, $image_text_override_groups)) || $use_watermark){return false;}
	
    # Get text from field
	global $image_text_field_select, $image_text_default_text;
	$overlaytext=get_data_by_field($ref, $image_text_field_select);
	if($overlaytext=="")
		{
		if($image_text_default_text!="")
			{$overlaytext=$image_text_default_text;}
		else
			{return false;}
		}
	
	# If this is not a temporary file having metadata written see if we already have a suitable size with the correct text
	$image_text_saved_file=get_resource_path($ref,true,$size . "_image_text_" . md5($overlaytext . $image_text_height_proportion . $image_text_max_height . $image_text_min_height . $image_text_font . $image_text_position . $image_text_banner_position) . "_" ,false,$ext,-1,$page);
    
	if ($path!=$tmpfile && file_exists ($image_text_saved_file))
		{
		$path=$image_text_saved_file;
		return true;
		}	
        
	# Locate imagemagick.
        $identify_fullpath = get_utility_path("im-identify");
        if ($identify_fullpath==false) {exit("Could not find ImageMagick 'identify' utility at location '$imagemagick_path'.");}
                    
        # Get image's dimensions.
        $identcommand = $identify_fullpath . ' -format %wx%h '. escapeshellarg($path);
        $identoutput=run_command($identcommand);
        preg_match('/^([0-9]+)x([0-9]+)$/ims',$identoutput,$smatches);
        if ((@list(,$width,$height) = $smatches)===false) { return false; }
        
        $olheight=floor($height * $image_text_height_proportion);
        if($olheight<$image_text_min_height && intval($image_text_min_height)!=0){$olheight=$image_text_min_height;}
		if($olheight>$image_text_max_height && intval($image_text_max_height)!=0){$olheight=$image_text_max_height;}
                
        # Locate imagemagick.
        $convert_fullpath = get_utility_path("im-convert");
        if ($convert_fullpath==false) {exit("Could not find ImageMagick 'convert' utility at location '$imagemagick_path'");}
            
            
        $tmpolfile= get_temp_dir() . "/" . $ref . "_image_text_" . $userref . "." . $ext;
        $createolcommand = $convert_fullpath . ' -background "#000" -fill white -gravity "' . $image_text_position . '" -font "' . $image_text_font . '" -size ' . $width  . 'x' . $olheight . ' caption:" ' . $overlaytext .  '  " ' . escapeshellarg($tmpolfile);
        $result=run_command($createolcommand);
        
        $newdlfile = get_temp_dir() . "/" . $ref . "_image_text_result_" . $userref . "." . $ext;
        if($image_text_banner_position=="bottom")
			{$convertcommand = $convert_fullpath . " " . escapeshellarg($path) .  ' ' . escapeshellarg($tmpolfile) . ' -append ' . escapeshellarg($newdlfile);}
		else
			{$convertcommand = $convert_fullpath . " " . escapeshellarg($tmpolfile) .  ' ' . escapeshellarg($path) . ' -append ' . escapeshellarg($newdlfile);}
        $result=run_command($convertcommand);
        
        $oldpath=$path;
		
		if ($path!=$tmpfile) # If this is not a temporary file having metadata written then copy it to the filestore for future use
			{
			copy($newdlfile, $image_text_saved_file);
			}
		
        $path=$newdlfile;
        if(strpos(get_temp_dir(),$oldpath)!==false){unlink($oldpath);}
        unlink($tmpolfile);      
		
		
        return true;
        }