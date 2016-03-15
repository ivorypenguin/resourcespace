<?php
include '../../../include/db.php';
include '../../../include/authenticate.php'; 
include_once '../../../include/general.php';
include '../../../include/resource_functions.php';
include '../../../include/image_processing.php';

$ref=getvalescaped("ref","");

# Load log entry
$log=sql_query("select * from resource_log where ref='$ref'");
if (count($log)==0) {exit("Log entry not found");}
$log=$log[0];
$resource=$log["resource"];
$field=$log["resource_type_field"];
$type=$log["type"];

if ($type=="e" || $type=="m")
    {
    # ----------------------------- PROCESSING FOR "e" (edit) and "m" (multi edit) METADATA ROWS ---------------------------------------------
    
    # Fetch current value for comparison
    $current=get_data_by_field($resource,$field);
    
    $diff=log_diff($current,$log["previous_value"]);
    
    # Process submit
    if (getval("action","")=="revert")
        {
        update_field($resource, $field, $log["previous_value"]);
        resource_log($resource,"e",$field,$lang["revert_log_note"],$current,$log["previous_value"]);
        redirect("pages/view.php?ref=" . $resource);
        }
    }
elseif($type=="u")
    {
    # ----------------------------- PROCESSING FOR "u" IMAGE UPLOAD ROWS ---------------------------------------------
    
    # Process submit
    if (getval("action","")=="revert")
        {
        # Perform the reversion. First this reversion itself needs to be logged and therefore 'revertable'.
        
        # Find file extension of current resource.
        $old_extension=sql_value("select file_extension value from resource where ref='$resource'","");
        
        # Ceate a new alternative file based on the current resource
        $alt_file=add_alternative_file($resource,'','','',$old_extension,0,'');
        $new_path = get_resource_path($resource, true, '', true, $old_extension, -1, 1, false, "", $alt_file);
        
        # Copy current file to alternative file.
        $old_path=get_resource_path($resource,true, '', true, $old_extension);
        if (file_exists($old_path))
            {
            copy($old_path,$new_path);
            }
        else
            {
            echo "Missing file: $old_path ($old_extension)";
            exit();
            }
            
        # Also copy thumbnail
        $old_thumb=get_resource_path($resource,true,'thm',true,"");
        if (file_exists($old_thumb))
            {
            $new_thumb=get_resource_path($resource, true, 'thm', true, "", -1, 1, false, "", $alt_file);
            copy($old_thumb,$new_thumb);
            }
            
        # Update log so this has a pointer.
        $log_ref=resource_log($resource,"u",0,$lang["revert_log_note"]);
        sql_query("update resource_log set previous_file_alt_ref='$alt_file' where ref='$log_ref'");
    
        # Now perform the revert, copy and recreate previews.
        $revert_alt_ref=$log["previous_file_alt_ref"];
        $revert_ext=sql_value("select file_extension value from resource_alt_files where ref='$revert_alt_ref'","");
        
        $revert_path=get_resource_path($resource, true, '', true, $revert_ext, -1, 1, false, "", $revert_alt_ref);
        $current_path=get_resource_path($resource,true, '', true, $revert_ext);
        if (file_exists($revert_path))
            {
            copy($revert_path,$current_path);
            sql_query("update resource set file_extension='" . escape_check($revert_ext) . "' where ref='$resource'");
            create_previews($resource,false,$revert_ext);
            }
        else
            {
            echo "Revert fail... $revert_path not found.";exit();
            }
        redirect("pages/view.php?ref=" . $resource);
        }
    }

include "../../../include/header.php";
?>

<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short ?>pages/log.php?ref=<?php echo $resource ?>" onClick="CentralSpaceLoad(this,true);return false;">&lt;&nbsp;<?php echo $lang["back"] ?></a></p>

<h1><?php echo $lang["revert"]?></h1>

<form method=post name="form" id="form" action="<?php echo $baseurl_short ?>plugins/rse_version/pages/revert.php" onSubmit="CentralSpacePost(this,true);return false;">
<input type="hidden" name="ref" value="<?php echo $ref ?>">
<input type="hidden" name="action" value="revert">
    
<?php if ($type=="e" || $type=="m") { ?>
<div class="Question">
<label><?php echo $lang["revertingwillapply"]?></label>
<div class="Fixed"><?php echo nl2br(htmlspecialchars($diff)) ?></div>
<div class="clearerleft"> </div>
</div>
<?php } ?>


<?php if ($type=="u") {
    # Fetch the thumbnail of the image
    $alt_file=$log["previous_file_alt_ref"];
    $alt_thumb=get_resource_path($resource, true, 'thm', true, "", -1, 1, false, "", $alt_file);
    if (file_exists($alt_thumb))
        {
        $alt_thumb_url=get_resource_path($resource, false, 'thm', true, "", -1, 1, false, "", $alt_file); 
        ?>
        <div class="Question">
        <label><?php echo $lang["revertingwillreplace"]?></label>

        <div class="Fixed">
        <img src="<?php echo $alt_thumb_url ?>" alt="<?php echo $lang["preview"] ?>" />
        </div>
        <div class="clearerleft"> </div>
        </div>
        <?php
        }
    ?>


<?php } ?>


<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="revert" type="submit" value="&nbsp;&nbsp;<?php echo $lang["revert"]?>&nbsp;&nbsp;" />
</div>

</form>
</div>


<?php
include "../../../include/footer.php";
?>
