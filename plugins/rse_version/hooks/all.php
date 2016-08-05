<?php

    
function HookRse_VersionAllBeforeremoveexistingfile($ref)
    {
    # Hook into upload_file and move out the existing file when uploading a new one.

    $old_extension=sql_value("select file_extension value from resource where ref='$ref'","");
    if ($old_extension!="")	
    	{
    	$old_path=get_resource_path($ref,true,"",true,$old_extension);
    	if (file_exists($old_path))
            {
            #$resource,$name,$description="",$file_name="",$file_extension="",$file_size=0,$alt_type='')
            
            $alt_file=add_alternative_file($ref,'','','',$old_extension,0,'');
            $new_path = get_resource_path($ref, true, '', true, $old_extension, -1, 1, false, "", $alt_file);
            
            copy($old_path,$new_path);
            
            
            # Also copy thumbnail
            $old_thumb=get_resource_path($ref,true,'thm',true,"");
            if (file_exists($old_thumb))
                {
                $new_thumb=get_resource_path($ref, true, 'thm', true, "", -1, 1, false, "", $alt_file);
                copy($old_thumb,$new_thumb);
                }
            
            # Store this value so it is written to the log later.
            global $previous_file_alt_ref;
            $previous_file_alt_ref=$alt_file;
            }
    	}
    }


    
function HookRse_VersionAllUpload_image_after_log_write($ref,$log_ref)
    {
    # After uploading an image and writing to the resource log, update the resource log so it stores the ref of the alternative file.
    global $previous_file_alt_ref;
    if (isset($previous_file_alt_ref))
        {
        sql_query("update resource_log set previous_file_alt_ref='$previous_file_alt_ref' where ref='$log_ref'");
        }
    }

function HookRse_VersionAllGet_alternative_files_extra_sql($resource)
    {
    # Filter the alternative files view to exclude alternative files that have been created to store earlier revisions.
    # This removes them from both the resource view page and also the 'manage alternative files' area.
    return "and ref not in (select previous_file_alt_ref from resource_log where previous_file_alt_ref is not null and type='u' and resource='$resource')";
    }
