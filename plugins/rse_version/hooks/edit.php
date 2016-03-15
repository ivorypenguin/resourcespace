<?php

function HookRse_versionEditEdit_all_extra_modes()
    {
    global $lang;
    ?>
    <option value="Revert"><?php echo $lang["revertmetadatatodatetime"] ?></option>
    <?php
    }
    
    #edit_all_mode_js
    #edit_all_after_findreplace
    

function HookRse_versionEditEdit_all_mode_js()
    {
    # Add to the JS executed when a mode selector is changed on 'edit all'
    global $n;
    ?>var r=document.getElementById('revert_<?php echo $n?>');if (this.value=='Revert') {r.style.display='block';q.style.display='none';} else {r.style.display='none';if (this.value!='FR') {q.style.display='block';}}
    <?php
    }
    
function HookRse_versionEditEdit_all_after_findreplace($field,$n)
    {
    # Add a revert date/time box after 'edit all' mode selector when reversion mode selected.
    global $lang;
    ?>
    <div class="Question" id="revert_<?php echo $n?>" style="display:none;border-top:none;">
    <label>&nbsp;</label>
    <input type="text" name="revert_<?php echo $field["ref"]?>" class="stdwidth" value="<?php echo date("Y-m-d H:i"); ?>" />
    </div>
    <?php
    }
    
    
function HookRse_versionEditSave_resource_data_multi_extra_modes($ref,$field)
    {
    # Process the batch revert action - hooks in to the save operation (save_resource_data_multi())
    				
    # Remove text/option(s) mode?
    if (getval("modeselect_" . $field["ref"],"")=="Revert")
            {
            $revert_date=getvalescaped("revert_" . $field["ref"],"");
            
            # Find the value of this field as of this date and time in the resource log.
            $value=sql_value("select previous_value value from resource_log where resource='$ref' and resource_type_field='" . $field["ref"] . "' and (type='e' or type='m') and date>'$revert_date' and previous_value is not null order by date limit 1",-1);
            
           /*if ($ref==126149)
                {
                echo "PROCESS! $revert_date";
                echo $value;
                exit();
                }
             */
           
            if ($value!=-1) {return $value;}
            }
    return false;
    }
