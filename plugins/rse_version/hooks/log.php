<?php

function HookRse_versionLogLog_extra_columns_header()
    {
    global $lang;
    ?><td width="5%"><div class="ListTools"><?php echo $lang["tools"] ?></div></td><?php    
    }

function HookRse_versionLogLog_extra_columns_row()
    {
    global $lang;
    global $log;
    global $n;
    ?>
    <td>
    <div class="ListTools">
        
    <?php if ($log[$n]["revert_enabled"]) { ?>
    <a href="../plugins/rse_version/pages/revert.php?ref=<?php echo $log[$n]["ref"] ?>" onClick="CentralSpaceLoad(this,true);return false;">&gt;&nbsp;<?php echo $lang["revert"] ?></a></td>
    <?php } ?>
    </div>
    </td>
    <?php    
    }

function HookRse_versionLogGet_resource_log_extra_fields()
    {
    # Extend get_resource_log so that the state of the previous value is fetched also.
    return ",((r.previous_value is not null and (r.type='e' or r.type='m')) or (r.previous_file_alt_ref is not null and r.type='u')) revert_enabled";
    }

function HookRse_versionLogLog_diff_td_extra($ref)
    {
    # For images, display the uploaded image in the "Difference" section of the log.
    global $lang;
    global $log;
    global $n;
    
    $image="";
    if ($log[$n]["type"]=="u")
        {
        # Attempt to find the image. For the latest upload, this is the current file.
        $latest=sql_query("select previous_file_alt_ref from resource_log where resource='$ref' and type='u' and ref>'" . $log[$n]["ref"] . "' order by ref limit 1");
        if (count($latest)==0)
            {
            # There are no subsequent uploads. The current file is the latest one.
            $image_path=get_resource_path($ref,true,"thm");
            if (file_exists($image_path)) {$image=get_resource_path($ref,false,"thm");}
            }
        else
            {
            # We've found a more recent upload; the upload therefore is represented in the alternative file for this.
            $alt_file=$latest[0]["previous_file_alt_ref"];
            #$alt_file_info=get_alternative_file($ref,$alt_file);
            $image_path=get_resource_path($ref, true, 'thm', true, "", -1, 1, false, "", $alt_file);
            if (file_exists($image_path)) {$image=get_resource_path($ref, false, 'thm', true, "", -1, 1, false, "", $alt_file);}
            }
    
        if ($image!="")
            {
            ?>
            <img src="<?php echo $image ?>" />
            <?php
            }
        }
    }