<?php

function generate_transform_preview($ref){
	global $storagedir;	
        global $imagemagick_path;
	global $imversion;

	if (!isset($imversion)){
		$imversion = get_imagemagick_version();
	}

	$tmpdir = get_temp_dir();

        // get imagemagick path
        $command = get_utility_path("im-convert");
        if ($command==false) {exit("Could not find ImageMagick 'convert' utility.");}

        $orig_ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
	$transformsourcepath=get_resource_path($ref,true,'scr',false,'jpg'); //use screen size if available to save time
	if(!file_exists($transformsourcepath)) // use original if screen not available
		{$transformsourcepath= get_resource_path($ref,true,'',false,$orig_ext);}
	
	$modified_transformsourcepath=hook("modifytransformsourcepath");
	if ($modified_transformsourcepath){
		$transformsourcepath=$modified_transformsourcepath;
	}
	
	if(!is_dir(get_temp_dir() . "/transform_plugin")){mkdir(get_temp_dir() . "/transform_plugin",0777);}

       if ($imversion[0]<6 || ($imversion[0] == 6 &&  $imversion[1]<7) || ($imversion[0] == 6 && $imversion[1] == 7 && $imversion[2]<5)){
                $colorspace1 = " -colorspace sRGB ";
                $colorspace2 =  " -colorspace RGB ";
        } else {
                $colorspace1 = " -colorspace RGB ";
                $colorspace2 =  " -colorspace sRGB ";
        }

        $command .= " \"$transformsourcepath\"[0] +matte -flatten $colorspace1 -geometry 450 $colorspace2 \"$tmpdir/transform_plugin/pre_$ref.jpg\"";
        run_command($command);

	// while we're here, clean up any old files still hanging around
	$dp = opendir(get_temp_dir() . "/transform_plugin");
	while ($file = readdir($dp)) {
		if ($file <> '.' && $file <> '..'){
			if ((filemtime(get_temp_dir() . "/transform_plugin/$file")) < (strtotime('-2 days'))) {
				unlink(get_temp_dir() . "/transform_plugin/$file");
			}
		}
	}
	closedir($dp);

        return true;
  
}



?>
