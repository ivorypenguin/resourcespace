<?php 
include "../../include/db.php";
include "../../include/authenticate.php"; 
include "../../include/general.php"; 
include "../../include/resource_functions.php"; 

if (!$metadata_report) {exit("This function is not enabled.");}

$exiftool_fullpath = get_utility_path("exiftool");
if ($exiftool_fullpath==false)
	{
	echo $lang["exiftoolnotfound"];
	}
else
	{
	$ref=getval("ref","");
	$resource=get_resource_data($ref);
	$ext=$resource['file_extension'];
	if ($ext==""){die($lang['nometadatareport']);}
	$resource_type=$resource['resource_type'];
	$type_name=get_resource_type_name($resource_type);

	$image=get_resource_path($ref,true,"",false,$ext);
	if (!file_exists($image)) {die($lang['error']);}

	#test if filetype is supported by exiftool
	$command=$exiftool_fullpath . " -listf";
	$formats=run_command($command);
	$ext=strtoupper($ext);
	if (strlen(strstr($formats,$ext))<2){die(str_replace_formatted_placeholder("%extension", $ext, $lang['filetypenotsupported']));}
	if (in_array(strtolower($ext),$exiftool_no_process)) {die(str_replace_formatted_placeholder("%extension", $ext, $lang['exiftoolprocessingdisabledforfiletype']));}
	
	#build array of writable tags
	$command=$exiftool_fullpath . " -listw";
	$writable_tags=run_command($command);
	$writable_tags=strtolower(str_replace("\n","",$writable_tags));
	$writable_tags_array=explode(" ",$writable_tags);
	
	$command=$exiftool_fullpath . " -ver";
	$exiftool_version=run_command($command);
	
	if($exiftool_version>=7.4){
	#build array of writable formats
	$command=$exiftool_fullpath . " -listwf";
	$writable_formats=run_command($command);
	$writable_formats=str_replace("\n","",$writable_formats);
	$writable_formats_array=explode(" ",$writable_formats);
	$file_writability=in_array($ext,$writable_formats_array); 
	}

    # Create a report for the original file.
    $command = $exiftool_fullpath . " -s -t -G --filename --exiftoolversion --filepermissions --NativeDigest --History --Directory " . escapeshellarg($image)." 2>&1";
    $report_original = run_command($command);

    # Create a temporary file (simulate download) and create a report for it.
    $tmpfile = write_metadata($image, $ref);
    $command = $exiftool_fullpath . " -s -t -G --filename --exiftoolversion --filepermissions --NativeDigest --History --Directory " . escapeshellarg($tmpfile)." 2>&1";
    $report_simulated = run_command($command);

    # Remove the temporary file.
    unlink($tmpfile);

    # Process the report of the simulated download.
    $results_simulated = array();
    $i = 0;
    $fields_simulated = explode("\n", $report_simulated);
    foreach ($fields_simulated as $field_simulated)
        {
        $tag_value = explode("\t", $field_simulated); 
        if (count($tag_value)==3)
            {
            $results_simulated[$i]["group"] = trim(strtolower($tag_value[0]));
            $results_simulated[$i]["tag"] = trim(strtolower($tag_value[1]));
            $results_simulated[$i]["value"] = trim($tag_value[2]);
            $tagprops = "";
            if((in_array($results_simulated[$i]["tag"], $writable_tags_array) && $file_writability)) {$tagprops.= "w";}
            if ($tagprops!="") {$results_simulated[$i]["tagprops"] = "($tagprops)";} else {$results_simulated[$i]["tagprops"] = "";}
            $i++;
            }
        }

    # Create a list of resource fields which are mapped to exiftool tags.
    $write_to = get_exiftool_fields($resource_type); # Returns an array of exiftool tags for the particular resource type, which are basically RS resource fields with an 'exiftool field' set.

    for($i = 0; $i<count($write_to); $i++) # Loop through all the found fields.
        {
        # Populate the resourcefields array.
        $tags = explode(",", $write_to[$i]['exiftool_field']); # Each 'exiftool field' may contain more than one tag.
        foreach ($tags as $tag)
            {
            $tag = strtolower($tag);
            $resourcefields[$tag]['ref'] = $write_to[$i]['ref'];
            $resourcefields[$tag]['listed'] = false;
            }
        }

    # Build report:

    # Work out the write status.
	if(!isset($file_writability)){$file_writability=true;$writability_comment=$lang['notallfileformatsarewritable'];}else{$writability_comment="";}
	($exiftool_write&&$file_writability)?$write_status=$lang['metadatatobewritten']." ".$writability_comment:$write_status=$lang['nowritewillbeattempted'];

	echo "<table class=\"InfoTable\">";
	echo "<tr><td colspan=\"5\">".$lang['resourcetype'].": ".$type_name."</td></tr>";
	echo "<tr><td colspan=\"5\">".$lang['existing_tags']."</td></tr>";
	echo "<tr><td width=\"150\">".$applicationname."</td><td width=\"50\">".$lang['group']."</td><td width=\"150\">".$lang['exiftooltag']."</td><td>".$lang['embeddedvalue']."</td><td>$write_status</td></tr>";

    # Process the report of the original file.
    $fields = explode("\n", $report_original);
	foreach ($fields as $field)
		{
		echo "<tr>";
		$tag_value=explode("\t",$field); 
		if (count($tag_value)==3)
			{
            $group = trim(strtolower($tag_value[0]));
            $tag = trim(strtolower($tag_value[1]));
            $value = trim($tag_value[2]);
            $tagprops = "";
            if((in_array($tag, $writable_tags_array) && $file_writability)) {$tagprops.= "w";}
            if ($tagprops!="") {$tagprops = "($tagprops)";}

            # Check if the tag is mapped to an RS resource field.
			if(isset($resourcefields[$tag]['ref']) || isset($resourcefields[$group.":".$tag]['ref']))
				{
                # Work out the RS resource field ref and title for the tag, set the listed status of the tag.
				if (isset($resourcefields[$tag]['ref']))
                    {
					$RS_field_ref=$resourcefields[$tag]['ref'];
                    $resourcefields[$tag]['listed'] = true;
                    }
				elseif (isset($resourcefields[$group.":".$tag]['ref']))
                    {
					$RS_field_ref=$resourcefields[$group.":".$tag]['ref'];
                    $resourcefields[$group.":".$tag]['listed'] = true;
                    }
				$RS_field_name=sql_query("select title from resource_type_field where ref = $RS_field_ref");
                $RS_field_name = lang_or_i18n_get_translated($RS_field_name[0]['title'], "fieldtitle-");
                # Display the RS resource field ref, title, exiftool group, tag and properties.
				echo "<td>". str_replace(array('%ref%', '%name%'), array($RS_field_ref, $RS_field_name), $lang['field_ref_and_name']) . "</td><td>$group</td><td>$tag $tagprops</td>";
				} 
			else 
				{
                # Not an RS resource field; display exiftool group, tag and properties.
				echo "<td></td><td>$group</td><td>$tag $tagprops</td>";
				}

            # Look for the tag in the simulated download.
            $exists_in_simulated = false;
            foreach ($results_simulated as $simulated_result)
                {
                if ($simulated_result["group"]==$group && $simulated_result["tag"]==$tag)
                    {
                    $exists_in_simulated = true;
                    break;
                    }
                }
            if ($exists_in_simulated)
				{
                # The tag exists also in the simulated download.
                $newvalue = $simulated_result['value'];

                # Compare the values from the original file and the simulated download.
                if ($value!=$newvalue && $tag!="filesize" && $tag!="filemodifydate")
                    {
                    echo "<td>- " . $value . "</td><td>+ " . $newvalue . "</td>";
                    }
                else
                    {
                    if ($tag=="filemodifydate")
                        {
                        echo "<td>" . $value . "</td><td>+ " . $lang["date_of_download"] . "</td>";
                        }
                    else
                        {
                        echo "<td>" . $value . "</td><td></td>";
                        }
                    }
                }
			else 
				{
                # The tag is removed in the simulated download.
                echo "<td>- " . $value . "</td><td>+</td>";
				}
				
			echo "</tr>";
			}
		}

    # Add tags which don't exist in the original file?
    if ($exiftool_write&&$file_writability)
        {
        echo "<tr><td colspan=\"5\">" . $lang['new_tags'] . "</td></tr>";
        echo "<tr><td width=\"150\">".$applicationname."</td><td width=\"50\">".$lang['group']."</td><td width=\"150\">".$lang['exiftooltag']."</td><td>".$lang['embeddedvalue']."</td><td>$write_status</td></tr>";

        # Process the report of the original file.
        foreach ($results_simulated as $result_simulated)
            {
            $group = $result_simulated["group"];
            $tag = $result_simulated["tag"];
            $value = $result_simulated["value"];
            $tagprops = $result_simulated["tagprops"];

            # Check if the tag hasn't been displayed already.
            if((isset($resourcefields[$tag]['listed']) && !($resourcefields[$tag]['listed'])) || (isset($resourcefields[$group.":".$tag]['listed']) && !($resourcefields[$group.":".$tag]['listed'])))
                {
                # Work out the RS resource field ref and title for the tag.
                echo "<tr>";
                if (isset($resourcefields[$tag]['ref']))
                    {
                    $RS_field_ref=$resourcefields[$tag]['ref'];
                    }
                elseif (isset($resourcefields[$group.":".$tag]['ref']))
                    {
                    $RS_field_ref=$resourcefields[$group.":".$tag]['ref'];
                    }
                $RS_field_name = sql_query("select title from resource_type_field where ref = $RS_field_ref");
                $RS_field_name = lang_or_i18n_get_translated($RS_field_name[0]['title'], "fieldtitle-");
                # Display the RS resource field ref, title, exiftool group, tag and properties.
                echo "<td>". str_replace(array('%ref%', '%name%'), array($RS_field_ref, $RS_field_name), $lang['field_ref_and_name']) . "</td><td>$group</td><td>$tag $tagprops</td>"; 

                # Display the value.
                if ($tag!="filesize" && $tag!="filemodifydate")
                    {
                    echo "<td></td><td>+ " . $value . "</td>";
                    }

                echo "</tr>";
                }
            }
        }
    echo "</table>";
    }
