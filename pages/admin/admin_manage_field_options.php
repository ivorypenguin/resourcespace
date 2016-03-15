<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';

if(!checkperm('k'))
    {
    header('HTTP/1.1 401 Unauthorized');
    die('Permission denied!');
    }

include_once '../../include/resource_functions.php';
include_once '../../include/node_functions.php';


// Initialize
$ajax       = getvalescaped('ajax', '');

$field      = getvalescaped('field', '');
$field_data = get_field($field);

$node_ref   = getvalescaped('node_ref', '');
$nodes      = array();

$chosencsslink ='<link type="text/css" rel="stylesheet" href="' . $baseurl_short . 'lib/chosen/chosen.min.css"></link>';
$chosenjslink = '<script type="text/javascript" src="' . $baseurl_short . 'lib/chosen/chosen.jquery.min.js"></script>';

if(!$ajax)
	{
	$headerinsert .= $chosencsslink;
	$headerinsert .= $chosenjslink;
	}

$new_node_record_form_action = '/pages/admin/admin_manage_field_options.php?field=' . $field;


// Process form requests
if('true' === $ajax && !(trim($node_ref)=="") && 0 < $node_ref)
    {
    $option_name     = trim(getvalescaped('option_name', ''));
    $option_parent   = getvalescaped('option_parent', '');
    $option_order_by = getvalescaped('option_order_by', '');
    $node_action     = getvalescaped('node_action', '');

    // [Save Option]
    if('save' === $node_action)
        {
        $response['refresh_page'] = false;
        $node_ref_data            = array();

        if(trim($option_parent) != '' || (get_node($node_ref, $node_ref_data) && $node_ref_data['parent'] != $option_parent))
            {
            $response['refresh_page'] = true;
            }

        // Option order_by is not being sent because that can be asynchronously changed and we might not know about it,
        // thus this will be checked upon saving the data. If order_by is null / empty string, then we will use the current value
        set_node($node_ref, $field, $option_name, $option_parent, $option_order_by);

        echo json_encode($response);
        exit();
        }

    // [Move Option]
    if('movedown' === $node_action || 'moveup' === $node_action)
        {
        $response['error']   = null;
        $response['sibling'] = null;

        $current_node     = array();
        if(!get_node($node_ref, $current_node))
            {
            $response['error'] = 'No node found!';
            exit(json_encode($response));
            }

        // Locate current node position within its siblings
        $siblings                    = get_nodes($field, $current_node['parent']);
        $current_node_siblings_index = array_search($node_ref, array_column($siblings, 'ref'));

        $pre_sibling      = 0;
        $post_sibling     = 0;
        $allow_reordering = false;
        $new_nodes_order  = array();

        // Get pre & post siblings of current node
        // Note: these can be 0 if current node is either first/ last in the list
        if(1 < count($siblings) && isset($current_node_siblings_index))
            {
            if(isset($siblings[$current_node_siblings_index - 1]))
                {
                $pre_sibling = $siblings[$current_node_siblings_index - 1]['ref'];
                }

            if(isset($siblings[$current_node_siblings_index + 1]))
                {
                $post_sibling = $siblings[$current_node_siblings_index + 1]['ref'];
                }
            }

        // Create the new order for nodes based on direction
        switch($node_action)
            {
            case 'moveup':
                $response['sibling'] = $pre_sibling;
                move_array_element($siblings, $current_node_siblings_index, $current_node_siblings_index - 1);

                // This is the first node in the list so we can't reorder upwards
                if(0 < $pre_sibling)
                    {
                    $allow_reordering = true;
                    }
                break;
            
            case 'movedown':
                $response['sibling'] = $post_sibling;
                move_array_element($siblings, $current_node_siblings_index, $current_node_siblings_index + 1);

                // This is the last node in the list so we can't reorder downwards
                if(0 < $post_sibling)
                    {
                    $allow_reordering = true;
                    }
                break;
            }

        // Create the new array of nodes order
        foreach($siblings as $sibling)
            {
            $new_nodes_order[] = $sibling['ref'];
            }

        if($allow_reordering)
            {
            reorder_node($new_nodes_order);
            }

        echo json_encode($response);
        exit();
        }

    // [Delete Option]
    if('delete' === $node_action)
        {
        delete_node($node_ref);
        }
    }

// [Toggle tree node]
if('true' === $ajax && 'true' === getval('draw_tree_node_table', '') && 7 == $field_data['type'])
    {
    $nodes         =  get_nodes($field, $node_ref);
    $nodes_counter = count($nodes);
    $i             = 0;
    foreach($nodes as $node)
        {
        $last_node = false;
        if(++$i === $nodes_counter)
            {
            $last_node = true;
            }
        draw_tree_node_table($node['ref'], $node['resource_type_field'], $node['name'], $node['parent'], $node['order_by'], $last_node);
        }
    exit();
    }

// [New Option]
$submit_new_option = getvalescaped('submit_new_option', '');
if('true' === $ajax && !(trim($submit_new_option)=="") && 'add_new' === $submit_new_option)
    {
    $new_option_name     = trim(getvalescaped('new_option_name', ''));
    $new_option_parent   = getvalescaped('new_option_parent', '');
    $new_option_order_by = get_node_order_by($field, 7 == $field_data['type'], $new_option_parent);

    $new_record_ref = set_node(NULL, $field, $new_option_name, $new_option_parent, $new_option_order_by);

    if(isset($new_record_ref) && !(trim($new_record_ref)==""))
        {
        if(7 != $field_data['type'] && (trim($new_option_parent)==""))
            {
            ?>
            <tr id="node_<?php echo $new_record_ref; ?>">
                <td>
                    <input type="text" name="option_name" form="option_<?php echo $new_record_ref; ?>" value="<?php echo $new_option_name; ?>" onblur="this.value=this.value.trim()" >
                </td>
                <td>
                    <div class="ListTools">
                        <form id="option_<?php echo $new_record_ref; ?>" method="post" action="/pages/admin/admin_manage_field_options.php?field=<?php echo $field; ?>">
                            <input type="hidden" name="node_ref" value="<?php echo $new_record_ref; ?>">
                            <input type="hidden" name="option_<?php echo $new_record_ref; ?>_order_by" value="<?php echo $new_option_order_by; ?>">

                            <button type="submit" onclick="SaveNode(<?php echo $new_record_ref; ?>); return false;"><?php echo $lang['save']; ?></button>
                            <button type="submit" onclick="ReorderNode(<?php echo $new_record_ref; ?>, 'moveup'); return false;"><?php echo $lang['action-move-up']; ?></button>
                            <button type="submit" onclick="ReorderNode(<?php echo $new_record_ref; ?>, 'movedown'); return false;"><?php echo $lang['action-move-down']; ?></button>
                            <button type="submit" onclick="DeleteNode(<?php echo $new_record_ref; ?>); return false;"><?php echo $lang['action-delete']; ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php

            exit();
            }
        draw_tree_node_table($new_record_ref, $field, $new_option_name, $new_option_parent, $new_option_order_by);
        }

    exit();
    }

include '../../include/header.php';

if($ajax)
	{
	echo $chosencsslink;
	echo $chosenjslink;
	}
	
?>
<div class="BasicsBox">
    <p>
        <a href="<?php echo $baseurl_short; ?>pages/admin/admin_resource_type_field_edit.php?ref=<?php echo $field; ?>" onClick="return CentralSpaceLoad(this, true);">&lt;&nbsp;<?php echo $lang['back']?></a>
    </p>
    <h1><?php echo $lang['manage_metadata_field_options'] . (isset($field_data['title']) ? ' - ' . $field_data['title'] : ''); ?></h1>

	<p><?php echo $lang["metadata_option_change_warning"] ?></p>

    <div class="ListView">
        <table class="ListviewStyle" border="0" cellspacing="0" cellpadding="5">
        <?php
        // When editing a category tree we won't show the table headers since the data
        // will move to the right every time we go one level deep
        if(7 != $field_data['type'])
            {
            ?>
            <thead>
                <tr class="ListviewTitleStyle">
                    <td>Name</td>
                    <td><div class="ListTools"><?php echo $lang['tools']; ?></div></td>
                </tr>
            </thead>
            <tbody>
        <?php
        // Render existing nodes
		$nodes = get_nodes($field);

        if(0 == count($nodes))
            {
            $fieldinfo = get_resource_type_field($field);

            migrate_resource_type_field_check($fieldinfo);

            $nodes = get_nodes($field);
            }

        foreach($nodes as $node)
            {
            check_node_indexed($node, $field_data['partial_index']);
            ?>
            <tr id="node_<?php echo $node['ref']; ?>">
                <td>
                    <input type="text" name="option_name" form="option_<?php echo $node['ref']; ?>" value="<?php echo $node['name']; ?>" onblur="this.value=this.value.trim()" >
                </td>
                <td>
                    <div class="ListTools">
                        <form id="option_<?php echo $node['ref']; ?>" method="post" action="/pages/admin/admin_manage_field_options.php?field=<?php echo $field; ?>">
                            <input type="hidden" name="node_ref" value="<?php echo $node['ref']; ?>">
                            <input type="hidden" name="option_<?php echo $node['ref']; ?>_order_by" value="<?php echo $node['order_by']; ?>">

                            <button type="submit" onclick="SaveNode(<?php echo $node['ref']; ?>); return false;"><?php echo $lang['save']; ?></button>
                            <button type="submit" onclick="ReorderNode(<?php echo $node['ref']; ?>, 'moveup'); return false;"><?php echo $lang['action-move-up']; ?></button>
                            <button type="submit" onclick="ReorderNode(<?php echo $node['ref']; ?>, 'movedown'); return false;"><?php echo $lang['action-move-down']; ?></button>
                            <button type="submit" onclick="DeleteNode(<?php echo $node['ref']; ?>); return false;"><?php echo $lang['action-delete']; ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php
            }
        render_new_node_record($new_node_record_form_action, FALSE);
        ?>
            </tbody>
            <?php
            }
            ?>
        </table>
    </div><!-- end of ListView -->

<?php
// Category trees
$tree_nodes = get_nodes($field);
if($field_data['type'] == 7 && !($tree_nodes==""))
    {
    $nodes_counter = count($tree_nodes);
    $i             = 0;

    foreach($tree_nodes as $node)
        {
        check_node_indexed($node, $field_data['partial_index']);

        $last_node = false;
        if(++$i === $nodes_counter)
            {
            $last_node = true;
            }

        draw_tree_node_table($node['ref'], $node['resource_type_field'], $node['name'], $node['parent'], $node['order_by'], $last_node);
        }
    }

// Render a new node record form when we don't have any node set in the database
if($field_data['type'] == 7 && !$tree_nodes)
    {
    render_new_node_record($new_node_record_form_action, TRUE);
    ?>
    <script>
    jQuery('.node_parent_chosen_selector').chosen({});
    </script>
    <?php
    }
?>
</div><!-- end of BasicBox -->
<script>
function AddNode(parent)
    {
    var new_node_children     = jQuery('#new_node_' + parent + '_children');
    var new_option_name       = new_node_children.find('input[name=new_option_name]');
    var new_option_parent     = new_node_children.find('select[name=new_option_parent]');
    var new_option_parent_val = new_option_parent.val();

    if(typeof new_option_parent_val === 'undefined' || new_option_parent_val == '')
        {
        new_option_parent_val = 0;
        }

    var new_node_parent_children = jQuery('#new_node_' + new_option_parent_val + '_children');
    var node_parent_children     = jQuery('#node_' + new_option_parent_val + '_children');

    var post_url  = '<?php echo $baseurl; ?>/pages/admin/admin_manage_field_options.php';
    var post_data = 
        {
        ajax: true,
        field: <?php echo $field; ?>,
        submit_new_option: 'add_new',
        new_option_name: new_option_name.val(),
        new_option_parent: new_option_parent.val()
        };

    jQuery.post(post_url, post_data, function(response)
        {
        if(typeof response !== 'undefined')
            {
            // Add new node and reset to default the values for a new record
            // If there are no children in the node append for now
            if(new_node_parent_children.length == 0)
                {
                node_parent_children.append(response);

                // Mark node as parent on the UI
                jQuery('#node_' + new_option_parent_val).data('toggleNodeMode', 'ex');
                jQuery('#node_' + new_option_parent_val + '_toggle_button').attr('src', '<?php echo $baseurl_short; ?>gfx/interface/node_ex.gif');
                jQuery('#node_' + new_option_parent_val + '_toggle_button').attr('onclick', 'ToggleTreeNode(' + new_option_parent_val + ', <?php echo $field; ?>);');
                }
            else
                {
                new_node_parent_children.before(response);
                }

            new_option_name.val('');
            new_option_parent.val(parent);

            jQuery('.node_parent_chosen_selector').chosen({});
            }
        });
    }

function SaveNode(ref)
    {
    var node          = jQuery('#node_' + ref);
    var node_children = jQuery('#node_' + ref + '_children');
    var option_name   = node.find('input[name=option_name]').val();
    var option_parent = node.find('select[name=option_parent]').val();

    var post_url  = '<?php echo $baseurl; ?>/pages/admin/admin_manage_field_options.php';
    var post_data = 
        {
        ajax: true,
        field: <?php echo $field; ?>,
        node_ref: ref,
        node_action: 'save',
        option_name: option_name,
        option_parent: option_parent
        };

    jQuery.post(post_url, post_data, function(response)
        {
        if(typeof response.refresh_page !== 'undefined' && response.refresh_page === true)
            {
            location.reload();
            }
        }, 'json');

    }

function DeleteNode(ref)
    {
    var confirmation = confirm('Are you sure you wish to DELETE this field option?');
    if(!confirmation)
        {
        return false;
        }

    var post_url  = '<?php echo $baseurl; ?>/pages/admin/admin_manage_field_options.php';
    var post_data = 
        {
        ajax: true,
        field: <?php echo $field; ?>,
        node_ref: ref,
        node_action: 'delete'
        };

    jQuery.post(post_url, post_data);
    jQuery('#node_' + ref).remove();
    jQuery('#node_' + ref + '_children').remove();

    return true;
    }

function ReorderNode(ref, direction)
    {
    var node          = jQuery('#node_' + ref);
    var node_children = jQuery('#node_' + ref + '_children');

    var post_url  = '<?php echo $baseurl; ?>/pages/admin/admin_manage_field_options.php';
    var post_data =
        {
        ajax: true,
        field: <?php echo $field; ?>,
        node_ref: ref,
        node_action: direction
        };

    jQuery.post(post_url, post_data, function(response)
        {
        if(direction == 'moveup' && response.sibling && response.sibling.length > 0)
            {
            node.insertBefore('#node_' + response.sibling);
            node_children.insertBefore('#node_' + response.sibling);
            }

        if(direction == 'movedown' && response.sibling && response.sibling.length > 0)
            {
            node.insertAfter('#node_' + response.sibling);
            node_children.insertAfter('#node_' + response.sibling);
            }
        }, 'json');
    }

function ToggleTreeNode(ref, field_ref)
    {
    var node_children    = jQuery('#node_' + ref + '_children');
    var table_node       = jQuery('#node_' +ref);
    var toggle_node_mode = jQuery(table_node).data('toggleNodeMode');
    var toggle_button    = jQuery('#node_' + ref + '_toggle_button');

    var post_url  = '<?php echo $baseurl; ?>/pages/admin/admin_manage_field_options.php';
    var post_data = 
        {
        ajax: true,
        field: field_ref,
        node_ref: ref,
        draw_tree_node_table: true
        };

    // Hide expanded children
    if('ex' === toggle_node_mode && '' !== node_children.html())
        {
        node_children.hide();
        jQuery(table_node).data('toggleNodeMode', 'unex');
        jQuery(toggle_button).attr('src', '<?php echo $baseurl_short; ?>gfx/interface/node_unex.gif');

        return true;
        }

    // Show parent children
    if('unex' === toggle_node_mode && '' !== node_children.html())
        {
        node_children.show();
        jQuery(table_node).data('toggleNodeMode', 'ex');
        jQuery(toggle_button).attr('src', '<?php echo $baseurl_short; ?>gfx/interface/node_ex.gif');

        return true;
        }

    jQuery.post(post_url, post_data, function(response)
        {
        if(typeof response !== 'undefined')
            {
            node_children.html(response);
            jQuery('.node_parent_chosen_selector').chosen({});

            jQuery(table_node).data('toggleNodeMode', 'ex');
            jQuery(toggle_button).attr('src', '<?php echo $baseurl_short; ?>gfx/interface/node_ex.gif');
            }
        });

    return true;
    }

jQuery('.node_parent_chosen_selector').chosen({});
</script>
<?php
include '../../include/footer.php';
