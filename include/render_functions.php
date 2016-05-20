<?php
/**
* Functions used to render HTML & Javascript
*
* @package ResourceSpace
*/

/*
TO DO: add here other functions used for rendering such as:
- render_search_field from search_functions.php (completed)
*/

/**
* Renders the HTML for the provided $field for inclusion in a search form, for example the
* advanced search page. Standard field titles are translated using $lang.  Custom field titles are i18n translated.
*
* $field    an associative array of field data, i.e. a row from the resource_type_field table.
* $name     the input name to use in the form (post name)
* $value    the default value to set for this field, if any
*/
function render_search_field($field,$value="",$autoupdate,$class="stdwidth",$forsearchbar=false,$limit_keywords=array())
    {
    node_field_options_override($field);
    
    global $auto_order_checkbox, $auto_order_checkbox_case_insensitive, $lang, $category_tree_open, $minyear, $daterange_search, $searchbyday, $is_search, $values, $n, $simple_search_show_dynamic_as_dropdown, $clear_function, $simple_search_display_condition, $autocomplete_search, $baseurl, $fields, $baseurl_short, $extrafooterhtml;
    
    $name="field_" . ($forsearchbar ? htmlspecialchars($field["name"]) : $field["ref"]);
    $id="field_" . $field["ref"];
    
    if($forsearchbar)
    	{
    	// need to check simple search specifics
    	
    	}
    
    #Check if field has a display condition set
    $displaycondition=true;
    if ($field["display_condition"]!="" && (!$forsearchbar || ($forsearchbar && !empty($simple_search_display_condition) && in_array($field['ref'],$simple_search_display_condition))))
        {
        $s=explode(";",$field["display_condition"]);
        $condref=0;
        foreach ($s as $condition) # Check each condition
            {
            $displayconditioncheck=false;
            $s=explode("=",$condition);
            global $fields;
            for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
                {
                if ($s[0]==$fields[$cf]["name"] && ($fields[$cf]["resource_type"]==0 || $fields[$cf]["resource_type"]==$field["resource_type"])) # this field needs to be checked
                    {
                    $display_condition_js_prepend=($forsearchbar ? "#simplesearch_".$fields[$cf]["ref"]." " : "");
                    
                    $scriptconditions[$condref]["field"] = $fields[$cf]["ref"];  # add new jQuery code to check value
                    $scriptconditions[$condref]['type'] = $fields[$cf]['type'];

                    //$scriptconditions[$condref]['options'] = $fields[$cf]['options'];

                    $scriptconditions[$condref]['node_options'] = array();
                    node_field_options_override($scriptconditions[$condref]['node_options'],$fields[$cf]['ref']);

                    $checkvalues=$s[1];
                    $validvalues=explode("|",strtoupper($checkvalues));
                    $scriptconditions[$condref]["valid"]= "\"";
                    $scriptconditions[$condref]["valid"].= implode("\",\"",$validvalues);
                    $scriptconditions[$condref]["valid"].= "\"";
                    if(isset($values[$fields[$cf]["name"]])) // Check if there is a matching value passed from search
                        {
                        $v=trim_array(explode(" ",strtoupper($values[$fields[$cf]["name"]])));
                        foreach ($validvalues as $validvalue)
                            {
                            if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value
                            }
                        }
                    if (!$displayconditioncheck) {$displaycondition=false;}
                    #add jQuery code to update on changes
                        if (($fields[$cf]['type'] == 2 || $fields[$cf]['type'] == 3) && $fields[$cf]['display_as_dropdown'] == 0 && !$forsearchbar) # add onchange event to each checkbox field
                            {
                            # construct the value from the ticked boxes
                            $val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                            //$options=trim_array(explode(",",$fields[$cf]["options"]));

                            $options=array();
                            node_field_options_override($options,$fields[$cf]['ref']);

                            ?><script type="text/javascript">
                            jQuery(document).ready(function() {<?php
                                for ($m=0;$m<count($options);$m++)
                                    {
                                    $checkname=($forsearchbar ? $fields[$cf]["name"] : $fields[$cf]["ref"]) . "_" . md5($options[$m]);
                                    echo "
                                    jQuery('<?php echo $display_condition_js_prepend ?>input[name=\"" . $checkname . "\"]').change(function (){
                                        checkDisplayCondition" . $field["ref"] . "();
                                        });";
                                    }
                                    ?>
                                });
                            </script><?php
                            }
                        # Handle Radio Buttons type:
                        else if($fields[$cf]['type'] == 12 && $fields[$cf]['display_as_dropdown'] == 0) {
                        ?>
                            <script type="text/javascript">
                            jQuery(document).ready(function() {
                                // Check for radio buttons (default behaviour)
                                jQuery('<?php echo $display_condition_js_prepend ?>input[name=field_<?php echo ($forsearchbar ? $fields[$cf]["name"] : $fields[$cf]["ref"])  ?>]:radio').change(function() {
                                    checkSearchDisplayCondition<?php echo $field["ref"];?>();
                                });

                                <?php

                                $options=array();
                                node_field_options_override($options,$fields[$cf]['ref']);

                                //$options = trim_array(explode(',', ['options']));

                                foreach ($options as $option) {
                                    $name = 'field_' . ($forsearchbar ? $fields[$cf]["name"] : $fields[$cf]["ref"]) . '_' . sha1($option); ?>
                                    
                                    // Check for checkboxes (advanced search behaviour)
                                    jQuery('<?php echo $display_condition_js_prepend ?>input[name=<?php echo $name; ?>]:checkbox').change(function() {
                                        checkSearchDisplayCondition<?php echo $field['ref']; ?>();
                                    });

                                <?php
                                }
                                ?>
                            });
                            </script>

                        <?php 
                        } 
                        else
                            {
                            ?>
                            <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery('<?php echo $display_condition_js_prepend ?>#field_<?php echo $fields[$cf]["ref"];?>').change(function (){
                                checkSearchDisplayCondition<?php echo $field["ref"];?>();
                                });
                            });
                            </script>
                        <?php
                            }
                    }
                } # see if next field needs to be checked

            $condref++;
            } # check next condition

        ?>
        <script type="text/javascript">
        
        function checkSearchDisplayCondition<?php echo $field["ref"];?>() {
            var questionField          = jQuery('#<?php echo ($forsearchbar ? "simplesearch_" . $field["ref"] : "question_" . $n );?>');
            var fieldInput			   = jQuery('#<?php echo ($forsearchbar ? "simplesearch_" . $field["ref"] : "question_" . $n );?> #field_<?php echo $field["ref"]?>');
            var fieldStatus            = questionField.css('display');
            var newFieldStatus         = 'none';
            var newFieldProvisional    = true;
            var newFieldProvisionalTest;
            <?php
            foreach ($scriptconditions as $scriptcondition)
                { ?>

                newFieldProvisionalTest = false;

                if (jQuery('#field_<?php echo $scriptcondition["field"]; ?>').length != 0) {
                    fieldValues<?php echo $scriptcondition["field"]; ?> = jQuery('#field_<?php echo $scriptcondition["field"]; ?>').val().toUpperCase().split(',');
                } else {
                <?php
                    # Handle Radio Buttons type:
                    if($scriptcondition['type'] == 12) 
                        {
                        //$scriptcondition["options"] = explode(',', $scriptcondition["options"]);

                        $scriptcondition["options"]=array();
                        node_field_options_override($scriptcondition["options"],$scriptcondition["field"]);

                        foreach ($scriptcondition["options"] as $key => $radio_button_value)
                            {
                            $scriptcondition["options"][$key] = sha1($radio_button_value);
                            }
                        $scriptcondition["options"] = implode(',', $scriptcondition["options"]);

                        ?>

                        var options_string = '<?php echo $scriptcondition["options"]; ?>';
                        var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
                        var checked = null;
                        var fieldOkValues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

                        for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++) {
                            if(jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) {
                                checked = jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val().toUpperCase();
                                if(jQuery.inArray(checked, fieldOkValues<?php echo $scriptcondition["field"]; ?>) > -1) {
                                    newFieldProvisionalTest = true;
                                }
                            }
                        }

                        <?php
                        } # end of handling radio buttons type
                        ?>

                    fieldValues<?php echo $scriptcondition["field"]; ?> = new Array();
                    checkedVals<?php echo $scriptcondition["field"]; ?> = jQuery('input[name^=<?php echo $scriptcondition["field"]; ?>_]');
                
                    jQuery.each(checkedVals<?php echo $scriptcondition["field"]; ?>, function() {
                        if (jQuery(this).is(':checked')) {
                            checkText<?php echo $scriptcondition["field"]; ?> = jQuery(this).parent().next().text().toUpperCase();
                            fieldValues<?php echo $scriptcondition["field"]; ?>.push(jQuery.trim(checkText<?php echo $scriptcondition["field"]; ?>));
                        }
                    });
                }
                    
                fieldOkValues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];
                jQuery.each(fieldValues<?php echo $scriptcondition["field"]; ?>,function(f,v) {
                    if ((jQuery.inArray(v,fieldOkValues<?php echo $scriptcondition["field"]; ?>))>-1 || (fieldValues<?php echo $scriptcondition["field"]; ?> == fieldOkValues<?php echo $scriptcondition["field"]; ?> )) {
                        newFieldProvisionalTest = true;
                    }
                });

                if (newFieldProvisionalTest == false) {
                    newFieldProvisional = false;
                }
                    
                <?php
                } ?>
            
            if (newFieldProvisional == true) {
                newFieldStatus = 'block'
            }
            if (newFieldStatus != fieldStatus) {
                questionField.slideToggle();
                if(newFieldStatus == 'block') {
                	fieldInput.prop("disabled",false);
                } else {
                	fieldInput.prop("disabled","disabled");
                }
                if (questionField.css('display') == 'block') {
                    questionField.css('border-top','');
                } else {
                    questionField.css('border-top','none');
                }
            }
        }
        </script>
    	<?php
    	if($forsearchbar)
    		{
    		// add the display condition check to the clear function
    		$clear_function.="checkSearchDisplayCondition".$field['ref']."();";
    		}
        }

    $is_search = true;

    if (!$forsearchbar)
        {
        ?>
        <div class="Question" id="question_<?php echo $n ?>" <?php if (!$displaycondition) {?>style="display:none;border-top:none;"<?php } ?><?php
        if (strlen($field["tooltip_text"])>=1)
            {
            echo "title=\"" . htmlspecialchars(lang_or_i18n_get_translated($field["tooltip_text"], "fieldtooltip-")) . "\"";
            }
        ?>>
        <label><?php echo htmlspecialchars(lang_or_i18n_get_translated($field["title"], "fieldtitle-")) ?></label>
        <?php
        }
    else
        {
        hook("modifysearchfieldtitle");
        ?>
        <div class="SearchItem" id="simplesearch_<?php echo $field["ref"] ?>" <?php if (!$displaycondition) {?>style="display:none;"<?php } if (strlen($field["tooltip_text"]) >= 1){ echo "title=\"" . htmlspecialchars(lang_or_i18n_get_translated($field["tooltip_text"], "fieldtooltip-")) . "\"";} ?> ><?php echo htmlspecialchars(lang_or_i18n_get_translated($field["title"], "fieldtitle-")) ?></br>
        
        <?php
        #hook to modify field type in special case. Returning zero (to get a standard text box) doesn't work, so return 1 for type 0, 2 for type 1, etc.
		if(hook("modifyfieldtype")){$fields[$n]["type"]=hook("modifyfieldtype")-1;}
        }

    //hook("rendersearchhtml", "", array($field, $class, $value, $autoupdate));

    switch ($field["type"]) {
        case 0: # -------- Text boxes
        case 1:
        case 5:
        case 8:
        case ($forsearchbar && $field["type"]==9 && !$simple_search_show_dynamic_as_dropdown):
        ?><input class="<?php echo $class ?>" type=text name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo htmlspecialchars($value)?>" <?php if($forsearchbar && !$displaycondition) { ?> disabled <?php } ?> <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } if(!$forsearchbar){ ?> onKeyPress="if (!(updating)) {setTimeout('UpdateResultCount()',2000);updating=true;}" <?php } ?> ><?php 
        
        if ($forsearchbar && $autocomplete_search) { 
				# Auto-complete search functionality
				?></div>
				<script type="text/javascript">
				
				jQuery(document).ready(function () { 
				
					jQuery("#field_<?php echo htmlspecialchars($field["name"])?>").autocomplete( { source: "<?php echo $baseurl?>/pages/ajax/autocomplete_search.php?field=<?php echo htmlspecialchars($field["name"]) ?>&fieldref=<?php echo $field["ref"]?>"} );
					})
				
				</script>
				<div class="SearchItem">
			<?php } 
			# Add to the clear function so clicking 'clear' clears this box.
			$clear_function.="document.getElementById('field_" . ($forsearchbar? $field["ref"] : $field["name"]) . "').value='';";
        break;
    
        case 2: 
        case 3:
        case ($forsearchbar && $field["type"]==9 && $simple_search_show_dynamic_as_dropdown):
        if(!hook("customchkboxes", "", array($field, $value, $autoupdate, $class, $forsearchbar, $limit_keywords)))
            {
            # -------- Show a check list or dropdown for dropdowns and check lists?
            # By default show a checkbox list for both (for multiple selections this enabled OR functionality)
            
            # Translate all options
            $adjusted_dropdownoptions=hook("adjustdropdownoptions");
            if ($adjusted_dropdownoptions){$options=$adjusted_dropdownoptions;}
            
            if($forsearchbar)
            	{
            	$optionfields[]=$field["name"]; # Append to the option fields array, used by the AJAX dropdown filtering
            	}
            
            $option_trans=array();
            $option_trans_simple=array();
            for ($m=0;$m<count($field["node_options"]);$m++)
                {
                $trans=i18n_get_translated($field["node_options"][$m]);
                $option_trans[$field["node_options"][$m]]=$trans;
                $option_trans_simple[]=$trans;
                }

            if ($auto_order_checkbox && !hook("ajust_auto_order_checkbox","",array($field))) {
                if($auto_order_checkbox_case_insensitive){natcasesort($option_trans);}
                else{asort($option_trans);}
            }
            $options=array_keys($option_trans); # Set the options array to the keys, so it is now effectively sorted by translated string       
            
            if ($field["display_as_dropdown"] || $forsearchbar)
                {
                # Show as a dropdown box
                $set=trim_array(explode(";",cleanse_string($value,true)));
                if($forsearchbar)
                	{
                	$name="field_drop_" . htmlspecialchars($field["name"]);
                	}
                ?><select class="<?php echo $class ?>" name="<?php echo $name ?>" id="<?php echo $id ?>" <?php if($forsearchbar && !$displaycondition) { ?> disabled <?php } ?> <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } if($forsearchbar){?> onChange="FilterBasicSearchOptions('<?php echo htmlspecialchars($field["name"]) ?>',<?php echo htmlspecialchars($field["resource_type"]) ?>);" <?php } ?>><option value=""></option><?php
                foreach ($option_trans as $option=>$trans)
                    {
                    if (trim($trans)!="")
                        {
                        ?>
                        <option value="<?php echo htmlspecialchars(trim($trans))?>" <?php if (in_array(cleanse_string($trans,true),$set)) {?>selected<?php } ?>><?php echo htmlspecialchars(trim($trans))?></option>
                        <?php
                        }
                    }
                ?></select><?php
                if($forsearchbar)
                	{
                	# Add to the clear function so clicking 'clear' clears this box.
					$clear_function.="document.getElementById('field_" . ($forsearchbar ? $field["ref"] : $field["name"]) . "').selectedIndex=0;";
                	}
                }
            else
                {
                # Show as a checkbox list (default)
                
                $set=trim_array(explode(";",cleanse_string($value,true)));
                $wrap=0;

                $l=average_length($option_trans_simple);
                $cols=10;
                if ($l>5)  {$cols=6;}
                if ($l>10) {$cols=4;}
                if ($l>15) {$cols=3;}
                if ($l>25) {$cols=2;}
                # Filter the options array for blank values and ignored keywords.
                $newoptions=array();
                foreach ($options as $option)
                    {
                    if ($option!=="" && (count($limit_keywords)==0 || in_array(strval($option), $limit_keywords)))
                        {
                        $newoptions[]=$option;
                        }
                    }
					
                $options=$newoptions;
				
                $height=ceil(count($options)/$cols);

                global $checkbox_ordered_vertically, $checkbox_vertical_columns;
                if ($checkbox_ordered_vertically)
                    {                   
                    if(!hook('rendersearchchkboxes'))
                        {
                        # ---------------- Vertical Ordering (only if configured) -----------
                        ?><table cellpadding=2 cellspacing=0><tr><?php
                        for ($y=0;$y<$height;$y++)
                            {
                            for ($x=0;$x<$cols;$x++)
                                {
                                # Work out which option to fetch.
                                $o=($x*$height)+$y;
                                if ($o<count($options))
                                    {
                                    $option=$options[$o];
                                    $trans=$option_trans[$option];

                                    $name=$field["ref"] . "_" . md5($option);
                                    if ($option!=="")
                                        {
                                        ?>
                                        <td valign=middle><input type=checkbox id="<?php echo htmlspecialchars($name) ?>" name="<?php echo ($name) ?>" value="yes" <?php if (in_array(cleanse_string($trans,true),$set)) {?>checked<?php } ?> <?php if ($autoupdate) { ?>onClick="UpdateResultCount();"<?php } ?>></td><td valign=middle><?php echo htmlspecialchars($trans)?>&nbsp;&nbsp;</td>

                                        <?php
                                        }
                                    else
                                        {
                                        ?><td></td><td></td><?php
                                        }
                                    }
                                }?></tr><tr><?php
                            }
                        ?></tr></table><?php
                        }
                    }
                else
                    {
                    # ---------------- Horizontal Ordering (Standard) ---------------------             
                    ?><table cellpadding=2 cellspacing=0><tr><?php
                    foreach ($option_trans as $option=>$trans)
                        {
                        $wrap++;if ($wrap>$cols) {$wrap=1;?></tr><tr><?php }
                        $name=$field["ref"] . "_" . md5($option);
                        if ($option!=="")
                            {
                            ?>
                            <td valign=middle><input type=checkbox id="<?php echo htmlspecialchars($name) ?>" name="<?php echo htmlspecialchars($name) ?>" value="yes" <?php if (in_array(cleanse_string(i18n_get_translated($option),true),$set)) {?>checked<?php } ?> <?php if ($autoupdate) { ?>onClick="UpdateResultCount();"<?php } ?>></td><td valign=middle><?php echo htmlspecialchars($trans)?>&nbsp;&nbsp;</td>
                            <?php
                            }
                        }
                    ?></tr></table><?php
                    }
                    
                }
            }
        break;
        
        case 4:
        case 6: 
        case 10: # ----- Date types
        $found_year='';$found_month='';$found_day='';$found_start_year='';$found_start_month='';$found_start_day='';$found_end_year='';$found_end_month='';$found_end_day='';
        if (!$forsearchbar && $daterange_search)
            {
            $startvalue=substr($value,strpos($value,"start")+5,10);
            $ss=explode(" ",$startvalue);
            if (count($ss)>=3)
                {
                $found_start_year=$ss[0];
                $found_start_month=$ss[1];
                $found_start_day=$ss[2];
                }
            $endvalue=substr($value,strpos($value,"end")+3,10);
            $se=explode(" ",$endvalue);
            if (count($se)>=3)
                {
                $found_end_year=$se[0];
                $found_end_month=$se[1];
                $found_end_day=$se[2];
                }
            ?>
            <!--  date range search start -->           
            <div><label class="InnerLabel"><?php echo $lang["fromdate"]?></label>
            <select name="<?php echo htmlspecialchars($name) ?>_startyear" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$y;$d>=$minyear;$d--)
                {
                ?><option <?php if ($d==$found_start_year) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_startmonth" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_start_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_startday" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyday"]?></option>
              <?php
              for ($d=1;$d<=31;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_start_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
                }
              ?>
            </select>   
            </div><br><div><label></label><label class="InnerLabel"><?php echo $lang["todate"]?></label><select name="<?php echo $name?>_endyear" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$y;$d>=$minyear;$d--)
                {
                ?><option <?php if ($d==$found_end_year ) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_endmonth" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              $md=date("n");
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_end_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_endday" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyday"]?></option>
              <?php
              $td=date("d");
              for ($d=1;$d<=31;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_end_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
                }
              ?>
            </select>
            <!--  date range search end date-->         
            </div>
            <?php }
        else
            {
            $s=explode("|",$value);
            if (count($s)>=3)
            {
            $found_year=$s[0];
            $found_month=$s[1];
            $found_day=$s[2];
            }
            ?>      
            <select name="<?php echo $name?>_year" id="<?php echo $id?>_year" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$minyear;$d<=$y;$d++)
                {
                ?><option <?php if ($d==$found_year) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            
            <?php if ($forsearchbar && $searchbyday) { ?><br /><?php } ?>
            
            <select name="<?php echo $name?>_month" id="<?php echo $id?>_month" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            
            <?php if (!$forsearchbar || ($forsearchbar && $searchbyday)) 
            	{ 
            	?>
				<select name="<?php echo $name?>_day" id="<?php echo $id?>_day" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
				  <option value=""><?php echo $lang["anyday"]?></option>
				  <?php
				  for ($d=1;$d<=31;$d++)
					{
					$m=str_pad($d,2,"0",STR_PAD_LEFT);
					?><option <?php if ($d==$found_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
					}
				  ?>
				</select>
            	<?php 
            	}
            if($forsearchbar)
            	{
            	# Add to the clear function so clicking 'clear' clears this box.
				$clear_function.="
					document.getElementById('field_" . $field["ref"] . "_year').selectedIndex=0;
					document.getElementById('field_" . $field["ref"] . "_month').selectedIndex=0;
					";
				if($searchbyday)
					{
					$clear_function.="document.getElementById('field_" . $field["ref"] . "_day').selectedIndex=0;";
					}
				}
            }
                    
        break;
        
        
        case 7: # ----- Category Tree
        $options=$field["options"];

        //$set=trim_array(explode(";",cleanse_string($value,true)));
        $set=preg_split('/[;\|]/',cleanse_string($value,true));

        if ($forsearchbar)
            {
            
            ?>
			<div id="field_<?php echo htmlspecialchars($field["name"]) ?>" >
			<div id="<?php echo htmlspecialchars($field["name"]) ?>_statusbox" class="MiniCategoryBox">
                <script>UpdateStatusBox("<?php echo htmlspecialchars($field["name"]) ?>", false);</script>
            </div>
			<input type="hidden" name="field_cat_<?php echo htmlspecialchars($field["name"]) ?>" id="<?php echo htmlspecialchars($field["name"]) ?>_category" value="<?php echo htmlspecialchars(implode('|',$set)); ?>">
			
			
			<?php
            if (!isset($extrafooterhtml))
                {
                $extrafooterhtml='';
                }
			# Add floating frame HTML. This must go in the footer otherwise it appears in the wrong place in IE due to it existing within a floated parent (the search bar).
			$extrafooterhtml.="
			<div class=\"RecordPanel\" style=\"display:none;position:fixed;top:100px;left:200px;text-align:left;\" id=\"cattree_" . $fields[$n]["name"] . "\">" . $lang["pleasewait"] . "</div>
			<script type=\"text/javascript\">
			// Load Category Tree
			jQuery(document).ready(function () {
				jQuery('#cattree_" . $field["name"] . "').load('" . $baseurl_short . "pages/ajax/category_tree_popup.php?field=" . $field["ref"] . "&value=" . urlencode($value) . "&nc=" . time() . "');
				})
			</script>
			";
			
			echo "<a href=\"#\" onClick=\"jQuery('#cattree_" . $field["name"] . "').css('top', (jQuery(this).position().top)-200);jQuery('#cattree_" . $field["name"] . "').css('left', (jQuery(this).position().left)-400);jQuery('#cattree_" . $field["name"] . "').css('position', 'fixed');jQuery('#cattree_" . $field["name"] . "').show();jQuery('#cattree_" . $field["name"] . "').draggable();return false;\">" . $lang["select"] . "</a></div>";

			# Add to clear function
			$clear_function.="DeselectAll('" . $field["name"] ."', true);";
            
            /*# On the search bar?
            # Produce a smaller version of the category tree in a single dropdown - max two levels
            ?>
            <select class="<?php echo $class ?>" name="field_<?php echo $field["ref"]?>"><option value=""></option><?php
            $class=explode("\n",$options);

            for ($t=0;$t<count($class);$t++)
                {
                $s=explode(",",$class[$t]);
                if (count($s)==3 && $s[1]==0)
                    {
                    # Found a first level
                    ?>
                    <option <?php if (in_array(cleanse_string($s[2],true),$set)) {?>selected<?php } ?>><?php echo htmlspecialchars($s[2]) ?></option>
                    <?php
                    
                    # Parse tree again looking for level twos at this point
                    for ($u=0;$u<count($class);$u++)
                        {
                        $v=explode(",",$class[$u]);
                        if (count($v)==3 && $v[1]==$s[0])
                            {
                            # Found a first level
                            ?>
                            <option value="<?php echo htmlspecialchars($s[2]) . "," . htmlspecialchars($v[2]) ?>" <?php if (in_array(cleanse_string($s[2],true),$set) && in_array(cleanse_string($v[2],true),$set)) {?>selected<?php } ?>>&nbsp;-&nbsp;<?php echo htmlspecialchars($v[2]) ?></option>
                            <?php
                            }                       
                        }
                    }
                }           
            ?>
            </select>
            <?php*/
            }
        else
            {
            # For advanced search and elsewhere, include the category tree.
            include "../pages/edit_fields/7.php";
            }
        break;
        
        case 9: #-- Dynamic keywords list
        $value=str_replace(";",",",$value); # Different syntax used for keyword separation when searching.
        include __DIR__ . "/../pages/edit_fields/9.php";
        break;      

        // Radio buttons:
        case 12:
            // auto save is not needed when searching
            $edit_autosave = FALSE;
             
            $display_as_radiobuttons = FALSE;
            $display_as_checkbox = TRUE;

            if($field['display_as_dropdown']) {
                $display_as_dropdown = TRUE;
                $display_as_checkbox = FALSE;
            }
            
            include '../pages/edit_fields/12.php';
        break;
        }
    ?>
    <div class="clearerleft"> </div>
    </div>
    <?php
    }

/**
* Renders sort order functionality as a dropdown box
* Renders sort order functionality as a dropdown box
*
*/
if (!function_exists("render_sort_order")){
function render_sort_order(array $order_fields)
    {
    global $order_by, $baseurl_short, $lang, $search, $archive, $restypes, $k, $sort, $date_field;

    // use query strings here as this is used to render elements and sometimes it
    // can depend on other params
    $modal  = ('true' == getval('modal', ''));
    ?>

    <select id="sort_order_selection">
    
    <?php
    $options = '';
    foreach($order_fields as $name => $label)
        {
        // date shows as 'field'.$date_field rather than 'date' for collection searches so let's fix it
        if($name=='field'.$date_field)
			{
			$name='date';
			}
		
        $fixed_order = $name == 'relevance';
        $selected    = ($order_by == $name || ($name=='date' && $order_by=='field'.$date_field));
		
        // Build the option:
        $option = '<option value="' . $name . '"';

        if(($selected && $fixed_order) || $selected)
            {
            $option .= ' selected';
            }

        $option .= sprintf('
                data-url="%spages/search.php?search=%s&amp;order_by=%s&amp;archive=%s&amp;k=%s&amp;restypes=%s"
            ',
            $baseurl_short,
            urlencode($search),
            $name,
            urlencode($archive),
            urlencode($k),
            urlencode($restypes)
        );

        $option .= '>';
        $option .= $label;
        $option .= '</option>';

        // Add option to the options list
        $options .= $option;
        }

        hook('render_sort_order_add_option', '', array($options));
        echo $options;
    ?>
    
    </select>
    <select id="sort_selection">
        <option value="ASC" <?php if($sort == 'ASC') {echo 'selected';} ?>><?php echo $lang['sortorder-asc']; ?></option>
        <option value="DESC" <?php if($sort == 'DESC') {echo 'selected';} ?>><?php echo $lang['sortorder-desc']; ?></option>
    </select>
    
    <script>
    function updateCollectionActions(order_by,sort_direction){
    	jQuery("#CollectionDiv .ActionsContainer select option").each(function(){
    		dataURL = jQuery(this).data("url");
    		if(typeof dataURL!=='undefined'){
    			dataURLVars = dataURL.split('&');
    			
    			replace_needed=false;
    			
    			for (i = 0; i < dataURLVars.length; i++) {
        			dataURLParameterName = dataURLVars[i].split('=');
	
   	     			if (dataURLParameterName[0] === 'order_by') {
   	        			dataURLVars[i] = dataURLParameterName[0]+'='+order_by;
   	        			replace_needed=true;
   		     		}
       		 		else if (dataURLParameterName[0] === 'sort') {
      	     			dataURLVars[i] = dataURLParameterName[0]+'='+sort_direction;
      	     			replace_needed=true;
      		  		}
     		   	}
   		     	if(replace_needed){
   		     		newDataURL=dataURLVars.join("&");
    				jQuery(this).attr("data-url", newDataURL);
    			}
    		}
    	});
    }
    
    jQuery('#sort_order_selection').change(function() {
        var selected_option      = jQuery('#sort_order_selection option[value="' + this.value + '"]');
        var selected_sort_option = jQuery('#sort_selection option:selected').val();
        var option_url           = selected_option.data('url');

        option_url += '&sort=' + selected_sort_option;

         <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(option_url);
        updateCollectionActions(selected_option.val(), selected_sort_option);
    });

    jQuery('#sort_selection').change(function() {
        var selected_option                = this.value;
        var selected_sort_order_option     = jQuery('#sort_order_selection option:selected');
        var selected_sort_order_option_url = selected_sort_order_option.data('url');

        selected_sort_order_option_url += '&sort=' + selected_option;

        <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(selected_sort_order_option_url);
        updateCollectionActions(selected_sort_order_option.val(), selected_option);
    });
    </script>
    <?php
    return;
    }
}
/**
* Renders a dropdown option
* 
*/
if (!function_exists("render_dropdown_option")){
function render_dropdown_option($value, $label, array $data_attr = array(), $extra_tag_attributes  = '')
    {
    $result = '<option value="' . $value . '"';

    // Add any extra tag attributes
    if(trim($extra_tag_attributes) !== '')
        {
        $result .= ' ' . $extra_tag_attributes;
        }

    // Add any data attributes you may need
    foreach($data_attr as $data_attr_key => $data_attr_value)
        {
        $data_attr_key = str_replace(' ', '_', $data_attr_key);

        $result .= ' data-' . $data_attr_key . '="' . $data_attr_value . '"';
        }

    $result .= '>' . $label . '</option>';

    return $result;
    }
}

/**
* Renders search actions functionality as a dropdown box
* 
*/
if (!function_exists("render_actions")){
function render_actions(array $collection_data, $top_actions = true, $two_line = true, $id = '')
    {
    if(hook('prevent_running_render_actions'))
        {
        return;
        }

    global $baseurl, $lang, $k, $pagename, $order_by, $sort;

    
    // globals that could also be passed as a reference
    global $result /*search result*/;

    $action_selection_id = $pagename . '_action_selection' . $id;
    if(!$top_actions)
        {
        $action_selection_id .= '_bottom';
        }
    if(isset($collection_data['ref']))
        {
        $action_selection_id .= '_' . $collection_data['ref'];
        }
        ?>

    <div class="ActionsContainer  <?php if($top_actions) { echo 'InpageNavLeftBlock'; } ?>">
		<?php
		if (!hook("modifyactionslabel","",array($collection_data,$top_actions)))
			{
			?>
			<div class="DropdownActionsLabel"><?php echo $lang['actions']; ?>:</div>
			<?php
			}

    if($two_line)
        {
        ?>
        <br />
        <?php
        }
        ?>
        <select onchange="action_onchange_<?php echo $action_selection_id; ?>(this.value);" id="<?php echo $action_selection_id; ?>" <?php if(!$top_actions) { echo 'class="SearchWidth"'; } ?>>
            <option class="SelectAction" value=""><?php echo $lang["actions-select"]?></option>
            <?php

            // Collection Actions
            $collection_actions_array = compile_collection_actions($collection_data, $top_actions);
			
            // Usual search actions
            $search_actions_array = compile_search_actions($top_actions);
            
            $actions_array = array_merge($collection_actions_array, $search_actions_array);
            
            $modify_actions_array = hook('modify_unified_dropdown_actions_options', '', array($actions_array,$top_actions));
            
	if(!empty($modify_actions_array))
                {
                $actions_array = $modify_actions_array;
                }

            // loop and display
			$options='';
			for($a = 0; $a < count($actions_array); $a++)
				{
				if(!isset($actions_array[$a]['data_attr']))
					{
					$actions_array[$a]['data_attr'] = array();
					}

				if(!isset($actions_array[$a]['extra_tag_attributes']))
					{
					$actions_array[$a]['extra_tag_attributes'] = '';
					}

				$options .= render_dropdown_option($actions_array[$a]['value'], $actions_array[$a]['label'], $actions_array[$a]['data_attr'], $actions_array[$a]['extra_tag_attributes']);

				$add_to_options = hook('after_render_dropdown_option', '', array($actions_array, $a));
				if($add_to_options != '')
					{
					$options .= $add_to_options;
					}
				}

			echo $options;
            ?>
        </select>
        <script>
        function action_onchange_<?php echo $action_selection_id; ?>(v)
            {
            if(v == '')
                {
                return false;
                }

            switch(v)
                {
            <?php
            if(!empty($collection_data))
                {
                ?>
                case 'select_collection':
                    ChangeCollection(<?php echo $collection_data['ref']; ?>, '');
                    break;

                case 'remove_collection':
                    if(confirm("<?php echo $lang['removecollectionareyousure']; ?>")) {
                        // most likely will need to be done the same way as delete_collection
                        document.getElementById('collectionremove').value = '<?php echo urlencode($collection_data["ref"]); ?>';
                        document.getElementById('collectionform').submit();
                    }
                    break;

                case 'purge_collection':
                    if(confirm('<?php echo $lang["purgecollectionareyousure"]; ?>'))
                        {
                        document.getElementById('collectionpurge').value='".urlencode($collections[$n]["ref"])."';
                        document.getElementById('collectionform').submit();
                        }
                    break;
                <?php
                }

            if(!$top_actions || !empty($collection_data))
                {
                ?>
                case 'delete_collection':
                    if(confirm('<?php echo $lang["collectiondeleteconfirm"]; ?>')) {
                        var post_data = {
                            ajax: true,
                            dropdown_actions: true,
                            delete: <?php echo urlencode($collection_data['ref']); ?> 
                        };

                        jQuery.post('<?php echo $baseurl; ?>/pages/collection_manage.php', post_data, function(response) {
                            if(response.success === 'Yes')
                                {
                                CollectionDivLoad('<?php echo $baseurl; ?>/pages/collections.php?collection=' + response.redirect_to_collection + '&k=' + response.k + '&nc=' + response.nc);

                                if(basename(document.URL).substr(0, 17) === 'collection_manage')
                                    {
                                    CentralSpaceLoad(document.URL);
                                    }
                                else
                                    {
                                    CentralSpaceLoad('<?php echo $baseurl; ?>/pages/search.php?search=!collection' + response.redirect_to_collection, true);
                                    }
                                }
                        }, 'json');    
                    }
                    break;
                <?php
                }

            // Add extra collection actions javascript case through plugins
            // Note: if you are just going to a different page, it should be easily picked by the default case
            $extra_options_js_case = hook('render_actions_add_option_js_case');
            if(trim($extra_options_js_case) !== '')
                {
                echo $extra_options_js_case;
                }
            ?>

                case 'save_search_to_collection':
                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    CollectionDivLoad(option_url);
                    break;

                case 'save_search_to_dash':
                    var option_url  = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    var option_link = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('link');
                    
                    // Dash requires to have some search parameters (even if they are the default ones)
                    if((basename(option_link).substr(0, 10)) != 'search.php')
                        {
                        option_link = (window.location.href).replace(window.baseurl, '');
                        }

                    option_url    += '&link=' + option_link;

                    CentralSpaceLoad(option_url);
                    break;

                case 'save_search_smart_collection':
                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    CollectionDivLoad(option_url);
                    break;

                case 'save_search_items_to_collection':
                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    CollectionDivLoad(option_url);
                    break;

                case 'empty_collection':
                    if(!confirm('<?php echo $lang["emptycollectionareyousure"]; ?>'))
                        {
                        break;
                        }

                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    CollectionDivLoad(option_url);
                    break;

            <?php
            if(!$top_actions)
                {
                ?>
                case 'delete_all_in_collection':
                    if(confirm('<?php echo $lang["deleteallsure"]; ?>'))
                        {
                        var post_data = {
                            submitted: true,
                            ref: '<?php echo $collection_data["ref"]; ?>',
                            name: '<?php echo urlencode($collection_data["name"]); ?>',
                            public: '<?php echo $collection_data["public"]; ?>',
                            deleteall: 'on'
                        };

                        jQuery.post('<?php echo $baseurl; ?>/pages/collection_edit.php?ajax=true', post_data, function()
                            {
                            CollectionDivLoad('<?php echo $baseurl; ?>/pages/collections.php?collection=<?php echo $collection_data["ref"] ?>');
                            });
                        }
                    break;

					case 'hide_collection':
						var action = 'hidecollection';
						var collection = <?php echo urlencode($collection_data['ref']);?>;
						var mycol = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('mycol');
						
						jQuery.ajax({
							type: 'POST',
							url: baseurl_short + 'pages/ajax/showhide_collection.php?action=' + action + '&collection=' + collection,
							success: function(data) {
								if (data.trim() == "HIDDEN") {
									CollectionDivLoad('<?php echo $baseurl; ?>/pages/collections.php?collection='+mycol);
								}
							},
							error: function (err) {
								console.log("AJAX error : " + JSON.stringify(err, null, 2));
							}
						}); 
						break;
                <?php
                }
                ?>

                case 'csv_export_results_metadata':
                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    window.location.href = option_url;
                    break;

                default:
                    var option_url = jQuery('#<?php echo $action_selection_id; ?> option:selected').data('url');
                    CentralSpaceLoad(option_url, true);
                    break;
                }

                // Go back to no action option
                jQuery('#<?php echo $action_selection_id; ?> option[value=""]').attr('selected', 'selected');

        }
        </script>
    </div>
    
    <?php
    return;
    }
}


/**
* @param string $name
* @param array  $current  Current selected values (eg. array(1, 3) for Admins and Super admins user groups selected)
* @param int    $size     How many options to show before user has to scroll
*/
function render_user_group_multi_select($name, array $current = array(), $size = 10, $style = '')
    {
    ?>
    <select id="<?php echo $name; ?>" name="<?php echo $name; ?>[]" multiple="multiple" size="<?php echo $size; ?>" style="<?php echo $style; ?>">
    <?php
    foreach(get_usergroups() as $usergroup)
        {
        ?>
        <option value="<?php echo $usergroup['ref']; ?>"<?php echo (in_array($usergroup['ref'], $current) ? ' selected' : ''); ?>><?php echo $usergroup['name']; ?></option>
        <?php
        }
        ?>
    </select>
    <?php
    }


/**
* @param string  $name
* @param integer $current  Current selected value. Use user group ID
*/
function render_user_group_select($name, $current = null, $style = '')
    {
    ?>
    <select id="<?php echo $name; ?>" name="<?php echo $name; ?>" style="<?php echo $style; ?>">
    <?php
    foreach(get_usergroups() as $usergroup)
        {
        ?>
        <option value="<?php echo $usergroup['ref']; ?>"<?php echo ((!is_null($current) && $usergroup['ref'] == $current) ? ' selected' : ''); ?>><?php echo $usergroup['name']; ?></option>
        <?php
        }
        ?>
    </select>
    <?php
    }


/**
* Renders a list of user groups
* 
* @param string $name
* @param array  $current  Current selected values (eg. array(1, 3) for Admins and Super admins user groups selected)
* @param string $style    CSS styling that will apply to the outer container (ie. table element)
*
* @return void
*/
function render_user_group_checkbox_select($name, array $current = array(), $style = '')
    {
    ?>
    <table id="<?php echo $name; ?>"<?php if('' !== $style) { ?>style="<?php echo $style; ?>"<?php } ?>>
        <tbody>
    <?php
    foreach(get_usergroups() as $group)
        {
        ?>
        <tr>
            <td><input id="<?php echo $name . '_' . $group['ref']; ?>" type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo $group['ref']; ?>"<?php if(in_array($group['ref'], $current)) { ?> checked<?php } ?> /></td>
            <td><label for="<?php echo $name . '_' . $group['ref']; ?>"><?php echo $group['name']; ?></label></td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <?php

    return;
    }

/**
* render_text_input_question - Used to display a question with simple text input
* 
* @param string $label						Label of question
* @param string $input  					Name of input field
* @param string $additionaltext (optional) 	Text to to display after input
* @param boolean $numeric 					Set to true to force numeric input
*/
function render_text_question($label, $input, $additionaltext="", $numeric=false, $extra="", $current="")
    {
	?>
	<div class="Question" id = "pixelwidth">
		<label><?php echo $label; ?></label>
		<div>
		<?php
		echo "<input name=\"" . $input . "\" type=\"text\" ". ($numeric?"numericinput":"") . "\" value=\"" . $current . "\"" . $extra . "/>\n";
			
		echo $additionaltext;
		?>
		</div>
	</div>
	<div class="clearerleft"> </div>
	<?php
	}
	
/**
* render_split_text_question - Used to display a question with two inputs e.g. for a from/to range
* 
* @param string $label	Label of question
* @param array  $inputs  Array of input names and labels(eg. array('pixelwidthmin'=>'From','pixelwidthmin'=>'To')
* @param string $additionaltext (optional) 	Text to to display after input
* @param boolean $numeric 					Set to true to force numeric input
*/
function render_split_text_question($label, $inputs = array(), $additionaltext="", $numeric=false, $extra="", $currentvals=array())
    {
	?>
	<div class="Question" id = "pixelwidth">
		<label><?php echo $label; ?></label>
		<div>
		<?php
		foreach ($inputs as $inputname=>$inputtext)
			{
			echo "<div class=\"SplitSearch\">" . $inputtext . "</div>\n";
			echo "<input name=\"" . $inputname . "\" class=\"SplitSearch\" type=\"text\"". ($numeric?"numericinput":"") . "\" value=\"" . $currentvals[$inputname] . "\"" . $extra . " />\n";
			}
		echo $additionaltext;
		?>
		</div>
	</div>
	<div class="clearerleft"> </div>
	<?php
	}

/**
* render_dropdown_question - Used to display a question with a dropdown selector
* 
* @param string $label	Label of question
* @param string $input  name of input field
* @param array  $options  Array of options (value and text pairs) (eg. array('pixelwidthmin'=>'From','pixelwidthmin'=>'To')
*/
function render_dropdown_question($label, $inputname, $options = array(), $current="", $extra="")
    {
	?>
	<div class="Question" id = "pixelwidth">
		<label><?php echo $label; ?></label>
		<select  name="<?php echo $inputname?>" id="<?php echo $inputname?>" <?php echo $extra; ?>>
		<?php
		foreach ($options as $optionvalue=>$optiontext)
			{
			?>
			<option value="<?php echo htmlspecialchars(trim($optionvalue))?>" <?php if (trim($optionvalue)==trim($current)) {?>selected<?php } ?>><?php echo htmlspecialchars(trim($optiontext))?></option>
			<?php
			}
		?>
		</select>

	</div>
	<div class="clearerleft"> </div>
	<?php
	}

/**
* Render a table row (tr) for a single access key
* 
* @param array $record Access key record details
* 
* @return void
*/
function render_access_key_tr(array $record)
    {
    global $baseurl, $baseurl_short, $lang;
    $link      = '';
    $type      = '';
    $edit_link = '';

    // Set variable dependent on type (ie. Resource / Collection)
    if('' == $record['collection'] && '' != $record['resource'])
        {
        // For resource
        $link      = $baseurl . '?r=' . urlencode($record['resource']) . '&k=' . urlencode($record['access_key']);
        $type      = $lang['share-resource'];
        $edit_link = sprintf('%spages/resource_share.php?ref=%s&editaccess=%s&editexpiration=%s&editaccesslevel=%s&editgroup=',
            $baseurl_short,
            urlencode($record['resource']),
            urlencode($record['access_key']),
            urlencode($record['expires']),
            urlencode($record['access']),
            urlencode($record['usergroup'])
        );
        }
    else
        {
        // For collection
        $link      = $baseurl . '?c=' . urlencode($record['collection']) . '&k=' . urlencode($record['access_key']);
        $type      = $lang['sharecollection'];
        $edit_link = sprintf('%spages/collection_share.php?ref=%s&editaccess=%s&editexpiration=%s&editaccesslevel=%s&editgroup=',
            $baseurl_short,
            urlencode($record['collection']),
            urlencode($record['access_key']),
            urlencode($record['expires']),
            urlencode($record['access']),
            urlencode($record['usergroup'])
        );
        }
        ?>


    <tr id="access_key_<?php echo $record['access_key']; ?>">
        <td>
            <div class="ListTitle">
                <a href="<?php echo $link; ?>" target="_blank"><?php echo htmlspecialchars($record['access_key']); ?></a>
            </div>
        </td>
        <td><?php echo htmlspecialchars($type); ?></td>
        <td><?php echo htmlspecialchars(resolve_users($record['users'])); ?></td>
        <td><?php echo htmlspecialchars($record['emails']); ?></td>
        <td><?php echo htmlspecialchars(nicedate($record['maxdate'], true)); ?></td>
        <td><?php echo htmlspecialchars(nicedate($record['lastused'], true)); ?></td>
        <td><?php echo htmlspecialchars(('' == $record['expires']) ? $lang['never'] : nicedate($record['expires'], false)); ?></td>
        <td><?php echo htmlspecialchars((-1 == $record['access']) ? '' : $lang['access' . $record['access']]); ?></td>
        <td>
            <div class="ListTools">
                <a href="#" onClick="delete_access_key('<?php echo $record['access_key']; ?>', '<?php echo $record['resource']; ?>', '<?php echo $record['collection']; ?>');">&gt;&nbsp;<?php echo $lang['action-delete']; ?></a>
                <a href="<?php echo $edit_link; ?>">&gt;&nbsp;<?php echo $lang['action-edit']; ?></a>
            </div>
        </td>
    </tr>
    <?php

    return;
    }