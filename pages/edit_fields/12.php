<?php
/*******************************************/
/**************RADIO BUTTONS****************/
/*******************************************/

$set = trim($value);
// Translate the options:
for($i = 0; $i < count($field['node_options']); $i++) {
    $field['node_options'][$i] = i18n_get_translated($field['node_options'][$i]);
}

$l=average_length($field['node_options']);

$cols=10;
if($l>5)  {$cols=6;}
if($l>10) {$cols=4;}
if($l>15) {$cols=3;}
if($l>25) {$cols=2;}

$rows=ceil(count($field['node_options'])/$cols);

// Default behaviour
if(!isset($display_as_radiobuttons)) {
    $display_as_radiobuttons = TRUE;
}

// Display as checkboxes is a feature for advanced search only
if(!isset($display_as_checkbox)) {
    $display_as_checkbox = FALSE;
}

// Display as dropdown is a feature for advanced search only, if set in field options
if(!isset($display_as_dropdown)) {
    $display_as_dropdown = FALSE;
}

// Autoupdate is set only on search forms, otherwise it should be false
if(!isset($autoupdate)) {
        $autoupdate = FALSE;
}

if(!isset($help_js)) {
    $help_js = '';
}

if ($edit_autosave) { ?>
        <script type="text/javascript">
                // Function to allow radio buttons to save automatically when $edit_autosave from config is set: 
                function radio_allow_save() {
                        preventautosave=false;
                        
                        setTimeout(function () {
                        preventautosave=true;
                    }, 1000);
                }
        </script>
<?php } 

if($display_as_radiobuttons) 
    {
    ?>
    <table id="" class="radioOptionTable" cellpadding="3" cellspacing="3">                    
            <tbody>
                    <tr>
                            <?php 
                            $row = 1;
                            $col = 1;
                            foreach ($field['node_options'] as $val)
                                {
                                if($col > $cols) 
                                    {
                                    $col = 1;
                                    $row++; ?>
                                    </tr>
                                    <tr>
                                    <?php 
                                    }
                                $col++;
                                ?>
                                <td width="10" valign="middle">
                                    <input type="radio" id="field_<?php echo $field["ref"] . '_' . sha1($val); ?>" name="field_<?php echo $field["ref"]; ?>" value="<?php echo $val; ?>" <?php if(i18n_get_translated($val)==i18n_get_translated($set) || ','.i18n_get_translated($val) == i18n_get_translated($set)) {?>checked<?php } ?> <?php if($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } echo $help_js; ?>/>
                                </td>
                                <td align="left" valign="middle">
                                    <label class="customFieldLabel" for="field_<?php echo $field["ref"] . '_' . sha1($val); ?>" <?php if($edit_autosave) { ?>onmousedown="radio_allow_save();" <?php } ?>><?php echo $val; ?></label>
                                </td>
                                <?php 
                                } 
                            ?>
                    </tr>
            </tbody>
    </table>
    <?php
    }
else if($display_as_checkbox) // On advanced search, by default, show as checkboxes:
    { ?>
    <table cellpadding=2 cellspacing=0>
        <tbody>
            <tr>
                <?php
                $row = 1;
                $col = 1;
                foreach ($field['node_options'] as $option) {
                    if($col > $cols) {
                        $col = 1;
                        $row++; ?>
                        </tr>
                        <tr>
                        <?php
                    }
                    $col++; ?>
                    <td valign=middle>
                        <input type=checkbox id="field_<?php echo $field["ref"] . '_' . sha1($option); ?>" name="field_<?php echo $field["ref"] . '_' . sha1($option); ?>" value="<?php echo $option; ?>" <?php if($option == $set) { ?>checked<?php } ?> <?php if($autoupdate) { ?>onClick="UpdateResultCount();"<?php } ?>>
                    </td>
                    <td valign=middle>
                        <?php echo htmlspecialchars($option)?>&nbsp;&nbsp;
                    </td>
                    <?php
                }
                ?>
            </tr>
        </tbody>
    </table>
    <?php
    }
else if($display_as_dropdown) // On advanced search, display it as a dropdown, if set like this:
    { ?>
    <select class="<?php echo $class ?>" name="field_<?php echo $field["ref"]?>" id="field_<?php echo $field["ref"]?>" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>><option value=""></option><?php
    foreach ($field['node_options'] as $value) {
        
        if (trim($value) != '') { ?>
            <option value="<?php echo htmlspecialchars(trim($value)); ?>" <?php if($value == $set) { echo 'selected'; } ?>><?php echo htmlspecialchars(trim($value)); ?></option>
        <?php
        }
    } ?>
    </select><?php
    }
?>
