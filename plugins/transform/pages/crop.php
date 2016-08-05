<?php

include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/authenticate.php";
include_once "../../../include/search_functions.php";
include_once "../../../include/resource_functions.php";
include_once "../../../include/search_functions.php";
include_once "../../../include/image_processing.php";

include_once "../include/transform_functions.php";

// verify that the requested ResourceID is numeric.
$ref = $_REQUEST['ref'];
if (!is_numeric($ref)){ echo "Error: non numeric ref."; exit; }
if(-2147483648 >= $ref || 2147483647 <= $ref)
    {
    echo 'The value you chose is not correct, it is out of range for an ID';
    exit;
    }

# Load edit access level
$edit_access=get_edit_access($ref);

# Load download access level
$access=get_resource_access($ref);

$cropperestricted=in_array($usergroup,$cropper_restricteduse_groups);

// are they requesting to change the original?
if (isset($_REQUEST['mode']) && strtolower($_REQUEST['mode']) == 'original'){
    $original = true;
} else {
    $original = false;
}



// if they can't download this resource, they shouldn't be doing this
// also, if they are trying to modify the original but don't have edit access
// they should never get these errors, because the links shouldn't show up if no perms
if ($access!=0 || ($original && !$edit_access)){
	include "../../../include/header.php";
	echo "Permission denied.";
	include "../../../include/footer.php";
	exit;
}


$imversion = get_imagemagick_version();

// generate a preview image for the operation if it doesn't already exist
if (!file_exists(get_temp_dir() . "/transform_plugin/pre_$ref.jpg")){
	//echo  "generating preview";
	//exit();
	generate_transform_preview($ref) or die("Error generating transform preview.");
}


# Locate imagemagick.
if (!isset($imagemagick_path)){
	echo "Error: ImageMagick must be configured for crop functionality. Please contact your system administrator.";
	exit;
}

$command = get_utility_path("im-convert");
if ($command==false) {exit("Could not find ImageMagick 'convert' utility.");}

// retrieve file extensions
$orig_ext = sql_value("select file_extension value from resource where ref = '$ref'",'');
$preview_ext = sql_value("select preview_extension value from resource where ref = '$ref'",'');

// retrieve image paths for preview image and original file
//$previewpath = get_resource_path($ref,true,$cropper_cropsize,false,$preview_ext);
$previewpath = get_temp_dir() . "/transform_plugin/".$cropper_cropsize."_$ref.jpg";
//echo $previewpath;
//exit();
$originalpath= get_resource_path($ref,true,'',false,$orig_ext);

hook('transformcropbeforegetsize');

// retrieve image sizes for original image and preview used for cropping
$cropsizes = @getimagesize($previewpath);
$origsizes = @getimagesize($originalpath);
$cropwidth = $cropsizes[0];
$cropheight = $cropsizes[1];
$origwidth = $origsizes[0];
$origheight = $origsizes[1];

// Get parameters from Manage slideshow page
$manage_slideshow_action = getvalescaped('manage_slideshow_action', '');
$manage_slideshow_id = getvalescaped('manage_slideshow_id', '');

$return_to_url = getvalescaped('return_to_url', '');


// if we've been told to do something
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'docrop'){

$width       = getvalescaped('width','',true);
$height      = getvalescaped('height','',true);
$xcoord      = getvalescaped('xcoord','',true);
$ycoord      = getvalescaped('ycoord','',true);
$description = getvalescaped('description','');
$cropsize    = getvalescaped('cropsize','',true);
$new_width   = getvalescaped('new_width','',true);
$new_height  = getvalescaped('new_height','',true);
$alt_type    = getvalescaped('alt_type','');


if (isset($_REQUEST['flip']) && $_REQUEST['flip'] == 1 && !$cropperestricted){
    $flip = true;
} else {
    $flip = false;
}

if (isset($_REQUEST['rotation']) && is_numeric($_REQUEST['rotation']) && $_REQUEST['rotation'] > 0 && $_REQUEST['rotation'] < 360 && !$cropperestricted){
    $rotation = $_REQUEST['rotation'];
}else{
    $rotation = 0;
}

if (isset($_REQUEST['filename']) && $cropper_custom_filename){
	$filename = $_REQUEST['filename'];
} else {
	$filename = '';
}

// verify that all crop parameters are numeric - just a precaution
if (!is_numeric($width) || !is_numeric($height) || !is_numeric($xcoord) || !is_numeric($ycoord)){
	// should never happen, but if it does, could be bad news
	echo $lang['nonnumericcrop'];
	exit;
}

if ($cropper_debug){
	error_log("origwidth: $origwidth, width: $width / origheight = $origheight, height = $height, $xcoord / $ycoord");
}

if (($width == 0 && $height == 0 && ($new_width > 0||$new_height > 0)) ||  $cropperestricted) {
	// the user did not indicate a crop. presumably they are scaling
	$verb = $lang['scaled'];
	$crop_necessary = false;
} else if (!$cropperestricted){
	$crop_necessary = true;
	$verb = $lang['cropped'];
	// now we need to mathematically convert to the original size
	$finalxcoord = round ((($origwidth  * $xcoord)/$cropwidth),0);
	$finalycoord = round ((($origheight * $ycoord)/$cropheight),0);
	$finalwidth  = round ((($origwidth  * $width)/$cropwidth),0);
	$finalheight = round ((($origheight * $height)/$cropheight),0);
}

// determine output format
// prefer what the user requested. If nothing, look for configured default. If nothing, use same as original
if (getval("slideshow","")!=""  && !$cropperestricted){$new_ext="jpg";}
else if (isset($_REQUEST['new_ext']) && strlen($_REQUEST['new_ext']) == 3){
	// is this an allowed extension?
	$new_ext = strtolower($_REQUEST['new_ext']);
	if (!in_array(strtoupper($new_ext),$cropper_formatarray)){
		$new_ext = strtolower($orig_ext);
	}
} elseif (isset($cropper_default_target_format)) {
	$new_ext = strtolower($cropper_default_target_format);
} else {
	$new_ext = strtolower($orig_ext);
}

if ( $cropper_custom_filename && strlen($filename) > 0){
	$mytitle = $filename;
} else{
	$mytitle = escape_check("$verb " . str_replace("?",strtoupper($new_ext),$lang["fileoftype"]));
}

if (strlen($alt_type)>0){ $mytitle .= " - $alt_type"; }

$mydesc = escape_check($description);

# Is this a download only?
$download=(getval("download","")!="");

if (!$download && !$edit_access){
	include "../../../include/header.php";
	echo "Permission denied.";
	include "../../../include/footer.php";
	exit;
}

if ($cropper_enable_alternative_files && !$download && !$original && getval("slideshow","")=="")
	{
	$newfile=add_alternative_file($ref,$mytitle,$mydesc,'','',0,escape_check($alt_type));
	$newpath = get_resource_path($ref, true, "", true, $new_ext, -1, 1, false, "", $newfile);
	}
else
	{
	$tmpdir = get_temp_dir();
	$newpath = "$tmpdir/transform_plugin/download_$ref." . $new_ext;
	}

// workaround for weird change in colorspace command in ImageMagick 6.7.5
if (strtoupper($new_ext) == 'JPG' && $cropper_jpeg_rgb){
       if ($imversion[0]<6 || ($imversion[0] == 6 &&  $imversion[1]<7) || ($imversion[0] == 6 && $imversion[1] == 7 && $imversion[2]<5)){

                $colorspace1 = " -colorspace sRGB ";
                $colorspace2 =  " -colorspace RGB ";
        } else {
                $colorspace1 = " -colorspace RGB ";
                $colorspace2 =  " -colorspace sRGB ";
        }
} else {
	$colorspace1 = '';
	$colorspace2 = '';
}

$commandprefix="";
$keep_transparency=false;
if (strtoupper($new_ext)=="PNG" || strtoupper($new_ext)=="GIF")
    {
    $commandprefix = " -background transparent ";
    $keep_transparency=true;
    $command .= $commandprefix . " \"$originalpath\" ";
    
    }
else
    {
    $command .= $commandprefix . " \"$originalpath\"[0] ";
    }

    



$resolution=getval("resolution","",TRUE);
if ($resolution!="")
	{
	$command .= " -units PixelsPerInch -density " .  $resolution . " ";
	}
	
	
// below is a hack to make this work with multilayer images
// the result will always be a flattened single-layer image
if ($orig_ext=="psd" && !$keep_transparency){$command .= " -alpha Off ";}
$command .= "-flatten ";

$command .= $colorspace1;

if ($crop_necessary  && !$cropperestricted){
	$command .= " -crop " . $finalwidth . "x" . $finalheight . "+" . $finalxcoord . "+$finalycoord ";
}

if ($cropper_use_repage){
	$command .= " +repage "; // force imagemagick to repage image to fix canvas and offset info
}

// did the user request a width? If so, tack that on
if (is_numeric($new_width)||is_numeric($new_height)){
	
	
	$scalewidth = is_numeric($new_width)?true:false;
	$scaleheight = is_numeric($new_height)?true:false;
	
	if (!$cropper_allow_scale_up){
		// sanity checks
		// don't allow a specified size larger than the natural crop size
		// or the original size of the image
		if ($crop_necessary) {
			$checkwidth = $finalwidth;
			$checkheight = $finalheight;
		} else {
			$checkwidth = $origwidth;
			$checkheight = $origheight;
		}
		
		if (is_numeric($new_width) && $new_width > $checkwidth){
			// if the requested width is greater than the original or natural size, ignore
			$new_width = '';
			$scalewidth = false;
		}
	
		if (is_numeric($new_height) && $new_height > $checkheight){
			// if the requested height is greater than original or natural size, ignore
			$new_height = '';
			$scaleheight = false;
		}
	
	}

	if ($scalewidth || $scaleheight){
		// add scaling command
		// note that there is a minor issue here: may be rounding
		// errors when the crop box is scaled up from preview size to original	 size
		// if so and the resulting match doesn't quite match the required width and 
		// height, there may be a tiny amount of distortion introduced as the
		// program scales up or down by a few pixels. This should be
		// imperceptible, but perhaps worth revisiting at some point.
		
		$command .= " -scale $new_width";
		
		if ($new_height > 0){
			$command .= "x$new_height";
		}
		
		$command .= " ";
	}
	
}

if ($flip  && !$cropperestricted){
    $command .= " -flop ";
}

if ($rotation > 0 && !$cropperestricted){
    $command .= " -rotate $rotation ";
}


if ($flip || $rotation > 0 && !$cropperestricted){
    // assume we should reset exif orientation flag since they have rotated to another orientation
    $command .= " -orient undefined ";
}


$command .= $colorspace2;

$command .= " \"$newpath\"";

if ($cropper_debug && !$download && getval("slideshow","")==""){
	error_log($command);
	if (isset($_REQUEST['showcommand'])){
		echo "$command";
		delete_alternative_file($ref,$newfile);
		exit;
	}
}

// fixme -- do we need to trap for errors from imagemagick?
$shell_result = run_command($command);
if ($cropper_debug){
	error_log("SHELL RESULT: $shell_result");
}

if ($resolution!="")
        {
        // See if we have got exiftool, in which case we can target the Photoshop specific PPI data
        $exiftool_fullpath = get_utility_path("exiftool");
        global $exiftool_no_process;
        if (($exiftool_fullpath!=false) && !in_array($new_ext,$exiftool_no_process))
                {
                $command = $exiftool_fullpath . " -m -overwrite_original -E ";
                $command.= "-Photoshop:XResolution=$resolution -Photoshop:YResolution=$resolution";
                $command.= " " . escapeshellarg($newpath);
                $output = run_command($command);
                }
        }


// get final pixel dimensions of resulting file
$newfilesize = filesize_unlimited($newpath);
$newfiledimensions = getimagesize($newpath);
$newfilewidth = $newfiledimensions[0];
$newfileheight = $newfiledimensions[1];

// generate previews if needed
global $alternative_file_previews;
if ($cropper_enable_alternative_files && $alternative_file_previews && !$download && !$original && getval("slideshow","")=="" && !$cropperestricted)
	{
	create_previews($ref,false,$new_ext,false,false,$newfile);
	}

// strip of any extensions from the filename, since we'll provide that
if(preg_match("/(.*)\.\w\w\w\\$/",$filename,$matches)){
	$filename = $matches[1];
}

// avoid bad characters in filenames
$filename = preg_replace("/[^A-Za-z0-9_\- ]/",'',$filename);
//$filename = str_replace(' ','_',trim($filename));

// if there is not a filename, create one
if ( $cropper_custom_filename && strlen($filename) > 0){
	$filename = "$filename";
} else {
	if (!$alternative_file_previews || $download || getval("slideshow","")!="")
		{
		$filename=$ref . "_" . strtolower($lang['transformed']);
		}
	elseif ($original && !$cropperestricted)
		{
                // fixme
                }
        else
                {
		$filename = "alt_$newfile";
		}
}

$filename = escape_check($filename);

$lcext = strtolower($new_ext);

$mpcalc = round(($newfilewidth*$newfileheight)/1000000,1);

// don't show  a megapixel count if it rounded down to 0
if ($mpcalc > 0){
	$mptext = " ($mpcalc " . $lang["megapixel-short"] . ")";
} else {
	$mptext = '';
}

if (strlen($mydesc) > 0){ $deschyphen = ' - '; } else { $deschyphen = ''; }
	
// Do something with the final file:
if ($cropper_enable_alternative_files && !$download && !$original && getval("slideshow","")=="" && !$cropperestricted){
    // we are supposed to make an alternative
    
	// note that we will now record transformation applied to alt files for future use
    $sql  = "update resource_alt_files set file_name='{$filename}.".$lcext."',file_extension='$lcext', file_size = '$newfilesize', description = concat(description,'" . $deschyphen . $newfilewidth . " x " . $newfileheight . " " . $lang['pixels'] . " $mptext') ";
	$sql .= ", transform_scale_w=" . ($new_width>0?"'$new_width'":"null") . ", transform_scale_h=" . ($new_height>0?"'$new_height'":"null") . "";
	$sql .= ", transform_crop_w=" . ($finalwidth>0?"'$finalwidth'":"null") . ", transform_crop_h=" . ($finalheight>0?"'$finalheight'":"null") . ", transform_crop_x=" . ($finalxcoord>0?"'$finalxcoord'":"null") . ", transform_crop_y=" . ($finalycoord>0?"'$finalycoord'":"null") . "";
	$sql .= ", transform_flop=" . ($flip?"'1'":"null") . ", transform_rotation=" . ($rotation>0?"'$rotation'":"null") . "";
    $sql .= " where ref='$newfile'";

	$result = sql_query($sql);
	resource_log($ref,'b','',"$new_ext " . strtolower($verb) . " to $newfilewidth x $newfileheight");

} elseif ($original && getval("slideshow","")=="" && !$cropperestricted) {
    // we are supposed to replace the original file

    $origalttitle = $lang['priorversion'];
    $origaltdesc = $lang['replaced'] . " " . strftime("%Y-%m-%d, %H:%M");
    $origfilename = sql_value("select value from resource_data left join resource_type_field on resource_data.resource_type_field = resource_type_field.ref where resource = '$ref' and name = 'original_filename'",$ref . "_original.$orig_ext");
    $origalt  = add_alternative_file($ref,$origalttitle,$origaltdesc);
    $origaltpath = get_resource_path($ref, true, "", true, $orig_ext, -1, 1, false, "", $origalt);
    $mporig =  round(($origwidth*$origheight)/1000000,2);
    $filesizeorig = filesize_unlimited($originalpath);
    rename($originalpath,$origaltpath);
    $result = sql_query("update resource_alt_files set file_name='{$origfilename}',file_extension='$orig_ext',file_size = '$filesizeorig' where ref='$origalt'");
    $neworigpath = get_resource_path($ref,true,'',false,$new_ext);
    rename($newpath,$neworigpath);
    $result = sql_query("update resource set file_extension = '$new_ext' where ref = '$ref' limit 1"); // update extension
    resource_log($ref,'t','','original transformed');
    create_previews($ref, false, $orig_ext, false, false, $origalt);
    create_previews($ref,false,$new_ext);

    # delete existing resource_dimensions
    sql_query("delete from resource_dimensions where resource='$ref'");
    sql_query("insert into resource_dimensions (resource, width, height, file_size) values ('$ref', '$newfilewidth', '$newfileheight', '$newfilesize')");

    # call remove annotations, since they will not apply to transformed
    hook("removeannotations","",array($ref));

    // remove the cached transform preview, since it will no longer be accurate
    if (file_exists(get_temp_dir() . "/transform_plugin/pre_$ref.jpg")){
	unlink(get_temp_dir() . "/transform_plugin/pre_$ref.jpg");
    }

	hook("transformcropafterreplaceoriginal");

    if('' !== $return_to_url)
        {
        redirect($return_to_url);
        }

    redirect("pages/view.php?ref=$ref");
    exit;

} elseif (getval("slideshow","")!="" && !$cropperestricted)
	{
	# Produce slideshow.
	$sequence=getval("sequence","");
	if (!is_numeric($sequence)) {exit("Invalid sequence number. Please enter a numeric value.");}

    if(!checkperm('t'))
        {
        exit('Permission denied.');
        }

	if(file_exists(dirname(__FILE__) . '/../../../' . $homeanim_folder . '/' . $sequence . '.jpg') &&
        !is_writable(dirname(__FILE__) . '/../../../' . $homeanim_folder . '/' . $sequence . '.jpg'))
		{
        exit ("Unable to replace existing slideshow image. Please check file permissions or use different slideshow sequence number");
        }

	copy($newpath,dirname(__FILE__) . "/../../../".$homeanim_folder."/" . $sequence . ".jpg");
	$sslinkfile = dirname(__FILE__) . "/../../../".$homeanim_folder."/" . $sequence . ".txt";
	if (getval("linkslideshow","")==1)
		{
		#Create/overwrite text file with link to resource view page
		file_put_contents($sslinkfile,$ref);
		}
	else
		#delete the existing link text file if it exists
		{
		if (file_exists($sslinkfile)){unlink($sslinkfile);}
		}
	unlink($newpath);
	unlink(get_temp_dir() . "/transform_plugin/pre_$ref.jpg");
	}
else
	{
    // we are supposed to download
	# Output file, delete file and exit
	$filename.="." . $new_ext;
	header(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
	header("Content-Type: application/octet-stream");

	set_time_limit(0);
	readfile($newpath);
	unlink($newpath);	
	unlink(get_temp_dir() . "/transform_plugin/pre_$ref.jpg");
	exit();
}
hook("aftercropfinish");

// If other pages request us to go back to them rather then on the view page, do so
if('' !== $return_to_url)
    {
    redirect($return_to_url);
    }

// send user back to view page
header("Location:../../../pages/view.php?ref=$ref\n\n");
exit;

} else {

// get resource info
$resource = get_resource_data($ref);

// retrieve path to image and figure out size we're using
if ($resource["has_image"]==1)
        {
		$imageurl = get_temp_dir(true) . "/transform_plugin/pre_$ref.jpg";
		$imagepath = get_temp_dir(false) . "/transform_plugin/pre_$ref.jpg";

        	//$imagepath=get_resource_path($ref,true,$cropper_cropsize,false,$resource["preview_extension"],-1,1);
        	if (!file_exists($imagepath)){
				echo $lang['noimagefound'];
				exit;
			}
        	//$imageurl=get_resource_path($ref,false,$cropper_cropsize,false,$resource["preview_extension"],-1,1);
        	
			$origpresizes = getimagesize($imagepath);
			$origprewidth = $origpresizes[0];
			$origpreheight = $origpresizes[1];
        
		} else {
		
			echo $lang['noimagefound'];
			exit;
		}


//header("X-UA-Compatible:IE=EmulateIE7"); // hack to make cropperUI work in IE8	
// update: apparently this now breaks it in both IE 8 and 9. So removing it unless anyone has a better idea. -DD, 8/2011

include "../../../include/header.php";

# slider, sound, controls
?>

<h1><?php echo ($original ? $lang['transform_original'] : $lang['transformimage']); ?></h1>
<p><?php
  if($cropperestricted)
      {
      echo $lang['transformblurbrestricted'];
      }
  else
      {
      echo ($original ? $lang['transformblurb-original'] : $lang['transformblurb']);
      }?>
</p>
<?php

if (file_exists($imagepath))
    {
    ?>
    <div id='cropimgdiv' style='float:left;padding:0;margin:0;' onmouseover='unfocus_widths();' ><img src="<?php echo $imageurl?>" id='cropimage' /></div>
    <?php
    }
		
		
if(!$cropperestricted)
    {
    ?>
    <script type="text/javascript" language="javascript">
        function onEndCrop( coords )
            {
            document.dimensionsform.xcoord.value=coords.x;
            document.dimensionsform.ycoord.value=coords.y;
            document.dimensionsform.width.value=coords.w;
            document.dimensionsform.height.value=coords.h;
            }

        var jcrop_api;
    
		    /**
		     * A little manager that allows us to reset the options dynamically
		     */
		    var CropManager = {
			    /**
			     * Holds the current Cropper.Img object
			     * @var obj
			     */
			    curCrop: null,
									    
			    /** 
			     * Attaches/resets the image cropper
			     *
			     * @access private
			     * @param obj Event object
			     * @return void
			     */
			    attachCropper: function( e ) {
				    document.dimensionsform.lastWidthSetting.value = document.getElementById('new_width').value;
				    document.dimensionsform.lastHeightSetting.value = document.getElementById('new_height').value;
    
    
				    this.removeCropper();
				    this.curCrop = jQuery('#cropimage').Jcrop(
                        {
                            onRelease: onEndCrop ,
                            onChange: onEndCrop ,
                            onSelect: onEndCrop ,
                            aspectRatio: jQuery('#new_width').val()/jQuery('#new_height').val()
                        },
                        function()
                            {
                            jcrop_api = this;
                            }
                    );
    
				    if( e != null ) Event.stop( e );
			    },
			    
			    /**
			     * Removes the cropper
			     *
			     * @access public
			     * @return void
			     */
			    removeCropper: function() {
				    if( this.curCrop != null ) {
					    this.curCrop = null;
				    }
			    },
			    
			    /**
			     * Resets the cropper, either re-setting or re-applying
			     *
			     * @access public
			     * @return void
			     */
			    resetCropper: function() {
				    this.attachCropper();
			    }
		    };	
	    
		    
		    
		    
		    function unfocus_widths(){
			    document.getElementById('new_width').blur();
			    document.getElementById('new_height').blur();
		    }
		    
		    function evaluate_values(){
			    // do we need to redraw the cropper?
			    if (
				    (document.getElementById('new_width').value == document.getElementById('lastWidthSetting').value && document.getElementById('new_height').value == document.getElementById('lastHeightSetting').value) 
				    || (document.getElementById('lastWidthSetting').value == '' && document.getElementById('new_width').value == '') 
				    || (document.getElementById('lastHeightSetting').value == '' && document.getElementById('new_height').value == '') 
				    )
			    {
				    return true;
			    } else {
				    CropManager.attachCropper();
				    return true;
			    }
			    
		    
		    }
		    
		    function validate_transform(theform){
		    
			    // make sure that this is a reasonable transformation before we submit the form.
			    // fixme - could add more sophisticated validation here
			    <?php
				if ($cropper_force_original_format)
					{
					// Ignore valid dimension check as user may just want to download a different file format
					?> 
					if (theform.xcoord.value == 0 && theform.ycoord.value == 0 && theform.new_width.value == '' && theform.new_height.value == '' <?php if ($cropper_rotation) { ?>&& theform.rotation.value == 0 & !theform.flip.checked <?php } ?>){
				    alert('<?php echo addslashes($lang['errormustchoosecropscale']); ?>');
				    return false;
					}
					<?php 
					}
	    
			    if (!$cropper_allow_scale_up) { ?>
				    if (Number(theform.new_width.value) > Number(theform.origwidth.value) || Number(theform.new_height.value) > Number(theform.origheight.value)){
					    alert('<?php echo addslashes($lang['errorspecifiedbiggerthanoriginal']); ?>');
					    return false;
				    }
			    <?php } ?>
			    return true;
		    
		    }

    // Function used to set the information needed by the cropper and to display/ hide transform options & actions
    function replace_slideshow_set_information(replace_slideshow_checkbox)
        {
        if(replace_slideshow_checkbox.checked)
            {
            document.getElementById('new_width').value  = '<?php if(isset($home_slideshow_width)) { echo $home_slideshow_width; } else { echo "517"; } ?>';
            document.getElementById('new_height').value = '<?php if(isset($home_slideshow_height)) { echo $home_slideshow_height; } else { echo "350"; } ?>';
            
            document.getElementById('transform_options').style.display = 'none';
            document.getElementById('transform_actions').style.display = 'none';
            document.getElementById('transform_slideshow_options').style.display = 'block';
            evaluate_values();
            }
        else
            {
            document.getElementById('transform_options').style.display = 'block';
            document.getElementById('transform_actions').style.display = 'block';
            document.getElementById('transform_slideshow_options').style.display = 'none';
            }

        return;
        }

    function check_cropper_selection()
        {
        var curCoords = jcrop_api.tellSelect();
        if(curCoords.w === 0 && curCoords.h === 0)
            {
            styledalert('Warning!', 'Please select an appropriate size!');

            return false;
            }

        return true;
        }

    </script>
    <?php
    }
    ?>	
    <div id="cropbox"  style='float:left; margin-left:20px'>
      <form name='dimensionsform' id="dimensionsForm" action="<?php echo $baseurl_short?>plugins/transform/pages/crop.php" onsubmit='return validate_transform(this);'>
	<input type='hidden' name='action' value='docrop' />
	<input type='hidden' name='xcoord' id='xcoord' value='0' />
	<input type='hidden' name='ycoord' id='ycoord' value='0' />
	<input type='hidden' name='width' id='width' value='<?php echo $origprewidth ?>' />
	<input type='hidden' name='height' id='height'  value='<?php echo $origpreheight ?>' />
	<input type='hidden' name='ref' id='ref' value='<?php echo $ref; ?>' />
	<input type='hidden' name='cropsize' id='cropsize' value='<?php echo $cropper_cropsize; ?>' />
	<input type='hidden' name='lastWidthSetting' id='lastWidthSetting' value='' />
	<input type='hidden' name='lastHeightSetting' id='lastHeightSetting' value='' />
	<input type='hidden' name='origwidth' id='origwidth'  value='<?php echo $origwidth ?>' />
	<input type='hidden' name='origheight' id='origheight'  value='<?php echo $origheight ?>' />
    <?php
    if('' !== $return_to_url)
        {
        ?>
        <input name="return_to_url" type="hidden" value="<?php echo $return_to_url; ?>">
        <?php
        }
    ?>
	<?php
	hook("cropafterhiddeninputs");
	if(is_writable(dirname(__FILE__)."/../../../" . $homeanim_folder)){echo "<!-- File Permissions Error-->";} //Notify of file permissions error
	if ($original && !$cropperestricted){ ?> <input type='hidden' name='mode' id='mode'  value='original' /> <?php }
	if ($cropper_enable_replace_slideshow && !$cropperestricted && checkperm('t') && is_writable(dirname(__FILE__)."/../../../" . $homeanim_folder)) 
        {
        echo $lang['replaceslideshowimage']; ?>
        <input type="checkbox" name='slideshow' id='slideshow' value="1" onClick="replace_slideshow_set_information(this);"
        <?php if('' !== trim($manage_slideshow_action)) { ?> checked<?php } ?> />
        <?php
        }
        ?>
	<table id="transform_slideshow_options"<?php if('' === trim($manage_slideshow_action)) { ?> style="display: none;"<?php } ?>>
		<tr><td colspan="4"><p><?php echo $lang['transformcrophelp'] ?></p></td></tr>
	    <tr>
		    <td style='text-align:right'><?php echo $lang["slideshowmakelink"]; ?>: </td>
		    <td><input type="checkbox" name='linkslideshow' id='linkslideshow' value="1" checked></td>
	    </tr>
	    <tr>
	    	<td style='text-align:right'><?php echo $lang["slideshowsequencenumber"]; ?>: </td>
	    	<td><input type='text' name='sequence' id='sequence' value="<?php if('' !== trim($manage_slideshow_id)) { echo $manage_slideshow_id; } ?>" size='4' /></td>
		</tr>
	    <tr>
            <td colspan="4">
                <input type="submit" name="submit" value="<?php echo $lang['replaceslideshowimage']; ?>"
                    onclick="if(check_cropper_selection()) { jQuery('#dimensionsForm').submit(); }; return false;">
            </td>
        </tr>
	</table>
    <table id="transform_options"<?php if('' !== trim($manage_slideshow_action)) { ?> style="display: none;"<?php } ?>>
      <tr>
        <td style='text-align:right'><?php echo $lang["width"]; ?>: </td>
        <td><input type='text' name='new_width' id='new_width' value='' size='4' <?php ($cropperestricted)?"onblur='evaluate_values()'":"" ?> />
          <?php echo $lang['px']; ?></td>
        <td style='text-align:right'><?php echo $lang["height"]; ?>: </td>
        <td><input type='text' name='new_height'  id='new_height' value='' size='4'  onblur='evaluate_values()' />
          <?php echo $lang['px']; ?></td>
      </tr>
      <?php if ($cropper_rotation && !$cropperestricted){ ?>
      <tr>
        <td style='text-align:right'><?php echo $lang['rotation']; ?>: </td>
        <td colspan='3'>
          <select name='rotation'>
              <option value="0"><?php echo $lang['rotation0']; ?>&nbsp;</option>
              <option value="90"><?php echo $lang['rotation90']; ?>&nbsp;</option>
              <option value="180"><?php echo $lang['rotation180']; ?>&nbsp;</option>
              <option value="270"><?php echo $lang['rotation270']; ?>&nbsp;</option>
          </select>

          &nbsp;&nbsp;&nbsp; <?php echo $lang['fliphorizontal']; ?> <input type="checkbox" name='flip' value="1" />
        </td>
      </tr>
      <?php }
      if (!$original) { ?>
      <?php if ($cropper_custom_filename){ ?>
      <tr>
        <td style='text-align:right'><?php echo $lang["name"]; ?>: </td>
        <td colspan='3'><input type='text' name='filename' value='' size='30'/></td>
      </tr>
      <?php }
		if ($cropper_enable_alternative_files && $edit_access && !$cropperestricted) { ?>
      <tr>
        <td style='text-align:right'><?php echo $lang["description_for_alternative_file"]; ?>: </td>
        <td colspan='3'><input type='text' name='description' value='' size='30'/></td>
      </tr>
      <?php
		}
	  } else { ?>
      <input type='hidden' name='filename' value=''/>
      <input type='hidden' name='description' value=''/>
      <?php }
      

        // if the system is configured to support a type selector for alt files, show it
        if (isset($alt_types) && count($alt_types) > 1){
                echo "<tr><td style='text-align:right'>\n<label for='alt_type'>".$lang["alternatetype"].":</label></td><td colspan='3'><select name='alt_type' id='alt_type'>";
                foreach($alt_types as $thealttype){
                        $thealttype = htmlspecialchars($thealttype,ENT_QUOTES);
                        echo "\n   <option value='$thealttype' >$thealttype</option>";
                }
                echo "</select>\n</td></tr>";
        } else {
		echo "<input type='hidden' name='alt_type' value='' />\n";
	}
?>
       <tr>
        <td style='text-align:right'><?php echo $lang['type']; ?>: </td>
        <td colspan='3'><?php
			if ($cropper_force_original_format==true){
				echo "<input type='hidden' name='new_ext' value='";
				echo strtoupper($orig_ext) . "' />" . strtoupper($orig_ext);
			} else {
			?>
          <select name='new_ext'>
            <?php 
				foreach ($cropper_formatarray as $theformat){
					echo "<option value='$theformat'";
					
					if (strtolower($theformat) == strtolower($orig_ext)) {
						echo " selected";
					}
					echo ">" . str_replace_formatted_placeholder("%extension", $theformat, $lang["field-fileextension"]) . "&nbsp;</option>\n";
				}
				?>
          </select>
          <?php } // end of if force_original_format ?></td>
      </tr>
	  
	  
	  <?php
	  if (count($cropper_resolutions)>0)
			{?>
		    <tr>
			<td style='text-align:right'><?php echo $lang['cropper_resolution_select']; ?>: </td>
			<td colspan='3'>
			  <select name='resolution'>
				<option value='' selected></option>
					<?php 
					foreach ($cropper_resolutions as $cropper_resolution)
						{
						echo "<option value='$cropper_resolution'>" . $cropper_resolution . "&nbsp;</option>\n";
						}
						?>
			  </select>
			</td>
		    </tr>
			<?php
			}
			?>
	  
	  
	  
    </table>
    <?php
if ($cropper_debug){
	echo "<input type='checkbox'  name='showcommand' value='1'>Debug IM Command</checkbox>";
}
?>
    <p style='text-align:right;margin-top:15px;' id='transform_actions'>
      <input type='button' value="<?php echo $lang['cancel']; ?>" onclick="javascript:return CentralSpaceLoad('<?php echo $baseurl_short . "pages/view.php?ref=" . $ref ?>',true);" />
      <?php if ($original){ ?>
             <input type='submit' name='replace' value="<?php echo $lang['transform_original']; ?>" />
      <?php } else { ?>
        <input type='submit' name='download' value="<?php echo $lang["action-download"]; ?>" />
        <?php if ($edit_access && $cropper_enable_alternative_files && !$cropperestricted) { ?><input type='submit' name='submit' value="<?php echo $lang['savealternative']; ?>" /><?php } ?>
      <?php } // end of if $original ?>
    </p>
  </form>
  <p>
    <?php

		# MP calculation
		$mp=round(($origwidth*$origheight)/1000000,1);
		if ($mp > 0){
			$orig_mptext = "($mp  " . $lang["megapixel-short"] . ")";
		} else {
			$orig_mptext = '';
		}
		
		echo $lang['originalsize'];
		echo ": $origwidth x $origheight ";
		echo $lang['pixels'];
		echo " $orig_mptext";

		?>
  </p>
</div>
<?php
if(!$cropperestricted && '' === trim($manage_slideshow_action))
    {
    ?>
    <script>CropManager.attachCropper();</script>
    <?php	
    }
else if(!$cropperestricted && '' !== trim($manage_slideshow_action))
    {
    ?>
    <script>
    jQuery(document).ready(function() {
        if(jQuery('#slideshow').is(':checked'))
            {
            replace_slideshow_set_information(jQuery('#slideshow').get(0))
            }
    });
    </script>
    <?php
    }
    
include "../../../include/footer.php";

} // end of if action docrop

?>
