<?php
function update_resource_type_field_order($neworder)
	{
	global $lang;
	if (!is_array($neworder)) {
		exit ("Error: invalid input to update_resource_type_field_order function.");
	}

	$updatesql= "update resource_type_field set order_by=(case ref ";
	$counter = 10;
	foreach ($neworder as $restype){
		$updatesql.= "when '$restype' then '$counter' ";
		$counter = $counter + 10;
	}
	$updatesql.= "else order_by END)";
	sql_query($updatesql);
	log_activity($lang['resourcetypefieldreordered'],LOG_CODE_REORDERED,implode(', ',$neworder),'resource_type_field','order_by');
	}
	
function update_resource_type_order($neworder)
	{
	global $lang;
	if (!is_array($neworder)) {
		exit ("Error: invalid input to update_resource_type_field_order function.");
	}

	$updatesql= "update resource_type set order_by=(case ref ";
	$counter = 10;
	foreach ($neworder as $restype){
		$updatesql.= "when '$restype' then '$counter' ";
		$counter = $counter + 10;
	}
	$updatesql.= "else order_by END)";
	sql_query($updatesql);
	log_activity($lang['resourcetypereordered'],LOG_CODE_REORDERED,implode(', ',$neworder),'resource_type','order_by');
	}


/**
* Function used to re-order slideshow images
* 
* @param  integer  $from  "ID" of the image we move from
* @param  integer  $to    "ID" of the image we move to
*
* @return  void
*/
function reorder_slideshow_images($from, $to)
    {
    // global $baseurl, $homeanim_folder;

    $slideshow_files = get_slideshow_files_data();

    if(!file_exists($slideshow_files[$from]['file_path']))
        {
        trigger_error('File "' . $slideshow_files[$from]['file_path'] . '" does not exist or could not be found/accessed!');
        }

    if(!file_exists($slideshow_files[$to]['file_path']))
        {
        trigger_error('File "' . $slideshow_files[$to]['file_path'] . '" does not exist or could not be found/accessed!');
        }

    // Calculate files to be moved around
    $from_file = $slideshow_files[$from]['file_path'];
    $temp_file = $slideshow_files[$from]['file_path'] . '.tmp';
    $to_file   = $slideshow_files[$to]['file_path'];

    // Swap the slideshow images
    if(!copy($from_file, $temp_file))
        {
        trigger_error("Failed to copy '$from_file' to temp file '$temp_file'");
        }
    rename($to_file, $from_file);
    rename($temp_file, $to_file);

    // Check if there are any link files that need to be changed as well
    $from_link_file      = '';
    $from_link_file_temp = '';
    $to_link_file        = '';

    if(isset($slideshow_files[$from]['link_file_path']) && file_exists($slideshow_files[$from]['link_file_path']))
        {
        $from_link_file      = $slideshow_files[$from]['link_file_path'];
        $from_link_file_temp = $from_link_file . '.tmp';
        }

    if(isset($slideshow_files[$to]['link_file_path']) && file_exists($slideshow_files[$to]['link_file_path']))
        {
        $to_link_file = $slideshow_files[$to]['link_file_path'];
        }

    // Swap/ rename the slideshow link file(s)
    // Case 1: both slideshows have a link in which case we need a temp file
    if('' !== trim($from_link_file) && '' !== trim($to_link_file) && '' !== trim($from_link_file_temp))
        {
        if(!copy($from_link_file, $from_link_file_temp))
            {
            trigger_error("Failed to copy '$from_link_file' to temp file '$from_link_file_temp'");
            }
        rename($to_link_file, $from_link_file);
        rename($from_link_file_temp, $to_link_file);
        }

    // Case 2: only $from has a link file. Simply rename it
    if('' !== trim($from_link_file) && '' === trim($to_link_file))
        {
        $to_computed_link_file = str_replace(basename($from_link_file), $to . '.txt', $from_link_file);
        rename($from_link_file, $to_computed_link_file);
        }

    // Case 3: only $to has a link file. Simply rename it
    if('' === trim($from_link_file) && '' !== trim($to_link_file))
        {
        $from_computed_link_file = str_replace(basename($to_link_file), $from . '.txt', $to_link_file);
        rename($to_link_file, $from_computed_link_file);
        }

    return;
    }