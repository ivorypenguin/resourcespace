<?php
/**
* Functions used to render HTML & Javascript
*
* @package ResourceSpace
*/

/*
TO DO: add here other functions used for rendering such as:
- render_search_field from search_functions.php
*/

/**
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
        $selected    = $order_by == $name; echo "selected=$selected - name=$name<br/>";
		
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
    jQuery('#sort_order_selection').change(function() {
        var selected_option      = jQuery('#sort_order_selection option[value="' + this.value + '"]');
        var selected_sort_option = jQuery('#sort_selection option:selected').val();
        var option_url           = selected_option.data('url');

        option_url += '&sort=' + selected_sort_option;

         <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(option_url);
    });

    jQuery('#sort_selection').change(function() {
        var selected_option                = this.value;
        var selected_sort_order_option     = jQuery('#sort_order_selection option:selected');
        var selected_sort_order_option_url = selected_sort_order_option.data('url');

        selected_sort_order_option_url += '&sort=' + selected_option;

        <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(selected_sort_order_option_url);
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

    global $baseurl, $lang, $k, $pagename;

    
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