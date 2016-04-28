<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include_once '../../include/config_functions.php';

// Do not allow access to anonymous users
if(isset($anonymous_login) && ($anonymous_login == $username))
    {
    header('HTTP/1.1 401 Unauthorized');
    die('Permission denied!');
    }

$userpreferences_plugins= array();
$plugin_names=array();
$plugins_dir = dirname(__FILE__)."/../../plugins/";
foreach($active_plugins as $plugin)
    {
    $plugin = $plugin["name"];
    array_push($plugin_names,trim(mb_strtolower($plugin)));
    $plugin_yaml = get_plugin_yaml($plugins_dir.$plugin.'/'.$plugin.'.yaml', false);
    if(isset($plugin_yaml["userpreferencegroup"]))
        {
        $upg = trim(mb_strtolower($plugin_yaml["userpreferencegroup"]));
        $userpreferences_plugins[$upg][$plugin]=$plugin_yaml;
        }
    }

if(getvalescaped("quicksave",FALSE))
    {
    $ctheme = getvalescaped("colour_theme","");
    if($ctheme==""){exit("missing");}
    $ctheme = preg_replace("/^col-/","",trim(mb_strtolower($ctheme)));
    if($ctheme =="default")
        {
        if(empty($userpreferences))
            {
            // create a record
            sql_query("INSERT INTO user_preferences (user, parameter, `value`) VALUES (" . $userref . ", 'colour_theme', NULL);");
            rs_setcookie("colour_theme", "",100, "/", "", substr($baseurl,0,5)=="https", true);
            exit("1");
            }
        else
            {
            sql_query("UPDATE user_preferences SET `value` = NULL WHERE user = " . $userref . " AND parameter = 'colour_theme';");
            rs_setcookie("colour_theme", "",100, "/", "", substr($baseurl,0,5)=="https", true);
            exit("1");
            }
        }
    if(in_array("col-".$ctheme,$plugin_names))
        {
        // check that record exists for user
        if(empty($userpreferences))
            {
            // create a record
            sql_query("INSERT into user_preferences (user, parameter, `value`) VALUES (" . $userref . ", 'colour_theme', '" . escape_check(preg_replace('/^col-/', '', $ctheme)) . "');");
            rs_setcookie("colour_theme", escape_check(preg_replace("/^col-/","",$ctheme)),100, "/", "", substr($baseurl,0,5)=="https", true);
            exit("1");
            }
        else
            {
            sql_query("UPDATE user_preferences SET `value` = '" . escape_check(preg_replace('/^col-/', '', $ctheme)) . "' WHERE user = " . $userref . " AND parameter = 'colour_theme';");
            rs_setcookie("colour_theme", escape_check(preg_replace("/^col-/","",$ctheme)),100, "/", "", substr($baseurl,0,5)=="https", true);
            exit("1");
            }
        }

    exit("0");
    }

$enable_disable_options = array($lang['userpreference_disable_option'], $lang['userpreference_enable_option']);

include "../../include/header.php";
?>
<div class="BasicsBox"> 
    <h1><?php echo $lang["userpreferences"]?></h1>
    <p><?php echo $lang["modifyuserpreferencesintro"]?></p>
    
    <?php
    /* Display */
    $options_available = 0; # Increment this to prevent a "No options available" message

    /* User Colour Theme Selection */
    if((isset($userfixedtheme) && $userfixedtheme=="") && isset($userpreferences_plugins["colourtheme"]) && count($userpreferences_plugins["colourtheme"])>0)
        {
        ?>
        <h2><?php echo $lang['userpreference_colourtheme']; ?></h2>
        <div class="Question">
            <label for="">
                <?php echo $lang["userpreferencecolourtheme"]; ?>
            </label>
            <script>
                function updateColourTheme(theme) {
                    jQuery.post(
                        window.location,
                        {"colour_theme":theme,"quicksave":"true"},
                        function(data){
                            location.reload();
                        });
                }
            </script>
            <?php
            # If there are multiple options provide a radio button selector
            if(count($userpreferences_plugins["colourtheme"])>1)
                { ?>
                <table id="" class="radioOptionTable">
                    <tbody>
                        <tr>
                        <!-- Default option -->
                        <td valign="middle">
                            <input 
                                type="radio" 
                                name="defaulttheme" 
                                value="default" 
                                onChange="updateColourTheme('default');"
                                <?php
                                    if
                                    (
                                        (isset($userpreferences["colour_theme"]) && $userpreferences["colour_theme"]=="") 
                                        || 
                                        (!isset($userpreferences["colour_theme"]) && $defaulttheme=="")
                                    ) { echo "checked";}
                                ?>
                            />
                        </td>
                        <td align="left" valign="middle">
                            <label class="customFieldLabel" for="defaulttheme">
                                <?php echo $lang["default"];?>
                            </label>
                        </td>
                        <?php
                        foreach($userpreferences_plugins["colourtheme"] as $colourtheme)
                            { ?>
                            <td valign="middle">
                                <input 
                                    type="radio" 
                                    name="defaulttheme" 
                                    value="<?php echo preg_replace("/^col-/","",$colourtheme["name"]);?>" 
                                    onChange="updateColourTheme('<?php echo preg_replace("/^col-/","",$colourtheme["name"]);?>');"
                                    <?php
                                        if
                                        (
                                            (isset($userpreferences["colour_theme"]) && "col-".$userpreferences["colour_theme"]==$colourtheme["name"]) 
                                            || 
                                            (!isset($userpreferences["colour_theme"]) && $defaulttheme==$colourtheme["name"])
                                        ) { echo "checked";}
                                    ?>
                                />
                            </td>
                            <td align="left" valign="middle">
                                <label class="customFieldLabel" for="defaulttheme">
                                    <?php echo $colourtheme["name"];?>
                                </label>
                            </td>
                            <?php
                            }
                        ?>
                        </tr>
                    </tbody>
                </table>
                <?php
                }
            ?>
            <div class="clearerleft"> </div>
        </div>
        <?php
        $options_available++;
        }
    /* End User Colour Theme Selection */


    ?>

<div class="CollapsibleSections">
    <?php
    // Result display section
    $all_field_info = get_fields_for_search_display(array_unique(array_merge(
        $sort_fields,
        $thumbs_display_fields,
        $list_display_fields,
        $xl_thumbs_display_fields,
        $small_thumbs_display_fields))
    );

    // Create a sort_fields array with information for sort fields
    $n  = 0;
    $sf = array();
    foreach($sort_fields as $sort_field)
        {
        // Find field in selected list
        for($m = 0; $m < count($all_field_info); $m++)
            {
            if($all_field_info[$m]['ref'] == $sort_field)
                {
                $field_info      = $all_field_info[$m];
                $sf[$n]['ref']   = $sort_field;
                $sf[$n]['title'] = $field_info['title'];

                $n++;
                }
            }
        }

    $sort_order_fields = array('relevance' => $lang['relevance']);
    if($random_sort)
        {
        $sort_order_fields['random'] = $lang['random'];
        }

    if($popularity_sort)
        {
        $sort_order_fields['popularity'] = $lang['popularity'];
        }

    if($orderbyrating)
        {
        $sort_order_fields['rating'] = $lang['rating'];
        }

    if($date_column)
        {
        $sort_order_fields['date'] = $lang['date'];
        }

    if($colour_sort)
        {
        $sort_order_fields['colour'] = $lang['colour'];
        }

    if($order_by_resource_id)
        {
        $sort_order_fields['resourceid'] = $lang['resourceid'];
        }

    if($order_by_resource_type)
        {
        $sort_order_fields['resourcetype'] = $lang['type'];
        }

    // Add thumbs_display_fields to sort order links for thumbs views
    for($x = 0; $x < count($sf); $x++)
        {
        if(!isset($metadata_template_title_field))
            {
            $metadata_template_title_field = false;
            }

        if($sf[$x]['ref'] != $metadata_template_title_field)
            {
            $sort_order_fields['field' . $sf[$x]['ref']] = htmlspecialchars($sf[$x]['title']);
            }
        }

    $page_def[] = config_add_html('<h2 class="CollapsibleSectionHead">' . $lang['resultsdisplay'] . '</h2><div id="UserPreferenceResultsDisplaySection" class="CollapsibleSection">');
    $page_def[] = config_add_single_select(
        'default_sort',
        $lang['userpreference_default_sort_label'],
        $sort_order_fields,
        true,
        300,
        '',
        true
    );
    $page_def[] = config_add_single_select('default_perpage', $lang['userpreference_default_perpage_label'], array(24, 48, 72, 120, 240), false, 300, '', true);

    // Default Display
    $default_display_array = array();
    if($smallthumbs || $GLOBALS['default_display'] == 'smallthumbs')
		{
		$default_display_array['smallthumbs'] = $lang['smallthumbstitle'];
		}
	$default_display_array['thumbs'] = $lang['largethumbstitle'];
	if($xlthumbs || $GLOBALS['default_display'] == 'xlthumbs')
		{
		$default_display_array['xlthumbs'] = $lang['xlthumbstitle'];
		}
	if($searchlist || $GLOBALS['default_display'] == 'list')
		{
		$default_display_array['list'] = $lang['listtitle'];
		}
	
    $page_def[] = config_add_single_select(
        'default_display',
        $lang['userpreference_default_display_label'],
        $default_display_array,
        true,
        300,
        '',
        true
    );
    
    $page_def[] = config_add_boolean_select('use_checkboxes_for_selection', $lang['userpreference_use_checkboxes_for_selection_label'], $enable_disable_options, 300, '', true);
    $page_def[] = config_add_boolean_select('resource_view_modal', $lang['userpreference_resource_view_modal_label'], $enable_disable_options, 300, '', true);
    $page_def[] = config_add_html('</div>');


    // User interface section
    $page_def[] = config_add_html('<h2 class="CollapsibleSectionHead">' . $lang['userpreference_user_interface'] . '</h2><div id="UserPreferenceUserInterfaceSection" class="CollapsibleSection">');
    $page_def[] = config_add_single_select('thumbs_default', $lang['userpreference_thumbs_default_label'], array('show' => $lang['showthumbnails'], 'hide' => $lang['hidethumbnails']), true, 300, '', true);
    $page_def[] = config_add_boolean_select('basic_simple_search', $lang['userpreference_basic_simple_search_label'], $enable_disable_options, 300, '', true);
    $page_def[] = config_add_html('</div>');


    // Email section, only show if user has got an email address
	if ($useremail!="")
		{
		$page_def[] = config_add_html('<h2 class="CollapsibleSectionHead">' . $lang['email'] . '</h2><div id="UserPreferenceEmailSection" class="CollapsibleSection">');
		$page_def[] = config_add_boolean_select('cc_me', $lang['userpreference_cc_me_label'], $enable_disable_options, 300, '', true);
		$page_def[] = config_add_boolean_select('email_user_notifications', $lang['userpreference_email_me_label'], $enable_disable_options, 300, '', true);
		$page_def[] = config_add_boolean_select('email_and_user_notifications', $lang['user_pref_email_and_user_notifications'], $enable_disable_options, 300, '', true);
		$page_def[] = config_add_boolean_select('user_pref_daily_digest', $lang['user_pref_daily_digest'], $enable_disable_options, 300, '', true);
		$page_def[] = config_add_boolean_select('user_pref_daily_digest_mark_read', $lang['user_pref_daily_digest_mark_read'], $enable_disable_options, 300, '', true);

		//$page_def[] = config_add_boolean_select('email_user_daily_digest', $lang['userpreference_email_digest_label'], $enable_disable_options, 300, '', true);
		$page_def[] = config_add_html('</div>');
		}


	// System notifications section - used to disable system generated messages 
	$page_def[] = config_add_html('<h2 class="CollapsibleSectionHead">' . $lang['mymessages'] . '</h2><div id="UserPreferenceAdminSection" class="CollapsibleSection">');
	$page_def[] = config_add_boolean_select('user_pref_show_notifications', $lang['user_pref_show_notifications'], $enable_disable_options, 300, '', true);
    $page_def[] = config_add_boolean_select('user_pref_resource_notifications', $lang['userpreference_resource_notifications'], $enable_disable_options, 300, '', true);
	if(checkperm("a"))
		{
		$page_def[] = config_add_boolean_select('user_pref_system_management_notifications', $lang['userpreference_system_management_notifications'], $enable_disable_options, 300, '', true);
		}
	
	if(checkperm("u"))
		{		
		$page_def[] = config_add_boolean_select('user_pref_user_management_notifications', $lang['userpreference_user_management_notifications'], $enable_disable_options, 300, '', true);
		}
	if(checkperm("R"))
		{	
		$page_def[] = config_add_boolean_select('user_pref_resource_access_notifications', $lang['userpreference_resource_access_notifications'], $enable_disable_options, 300, '', true);
		}

	$page_def[] = config_add_html('</div>');


    // Metadata section
    if(!$force_exiftool_write_metadata)
        {
        $page_def[] = config_add_html('<h2 class="CollapsibleSectionHead">' . $lang['metadata'] . '</h2><div id="UserPreferenceMetadataSection" class="CollapsibleSection">');
        $page_def[] = config_add_boolean_select('exiftool_write_option', $lang['userpreference_exiftool_write_metadata_label'], $enable_disable_options, 300, '', true);
        $page_def[] = config_add_html('</div>');
        }



    // Let plugins hook onto page definition and add their own configs if needed
    // or manipulate the list
    $plugin_specific_definition = hook('add_user_preference_page_def', '', array($page_def));
    if(is_array($plugin_specific_definition) && !empty($plugin_specific_definition))
        {
        $page_def = $plugin_specific_definition;
        }


    // Process autosaving requests
    // Note: $page_def must be defined by now in order to make sure we only save options that we've defined
    if('true' === getval('ajax', '') && 'true' === getval('autosave', ''))
        {
        // Get rid of any output we have so far as we don't need to return it
        ob_end_clean();

        $response['success'] = true;
        $response['message'] = '';

        $autosave_option_name  = getvalescaped('autosave_option_name', '');
        $autosave_option_value = getvalescaped('autosave_option_value', '');

        // Search for the option name within our defined (allowed) options
        // if it is not there, error and don't allow saving it
        $page_def_option_index = array_search($autosave_option_name, array_column($page_def, 1));
        if(false === $page_def_option_index)
            {
            $response['success'] = false;
            $response['message'] = $lang['systemconfig_option_not_allowed_error'];

            echo json_encode($response);
            exit();
            }

        if(!set_config_option($userref, $autosave_option_name, $autosave_option_value))
            {
            $response['success'] = false;
            }

        echo json_encode($response);
        exit();
        }


    config_generate_html($page_def);
    ?>
</div>
    <script>registerCollapsibleSections();</script>
    <?php config_generate_AutoSaveConfigOption_function($baseurl . '/pages/user/user_preferences.php'); ?>
</div>

<?php
include '../../include/footer.php';
