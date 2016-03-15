<?php

define('MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX','!deprecated');
define('MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX_CATEGORY_TREE',"-1,,!deprecated\n");

// function to automatically migrate options lists to nodes
function migrate_resource_type_field_check(&$resource_type_field)
	{

	if (
        !isset($resource_type_field['options']) ||
        is_null($resource_type_field['options']) ||
		$resource_type_field['options']=='' ||
        ($resource_type_field['type'] == 7 && preg_match('/^' . MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX_CATEGORY_TREE . '/',$resource_type_field['options'])) ||
        preg_match('/^' . MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX . '/',$resource_type_field['options'])
	)
		{
		return;  // get out of here as there is nothing to do
		}

    // Delete all nodes for this resource type field
    // This is to prevent systems that migrated to have old values that have been removed from a default field
    // example: Country field
    delete_nodes_for_resource_type_field($resource_type_field['ref']);

	if ($resource_type_field['type'] == 7)		// category tree
		{
        migrate_category_tree_to_nodes($resource_type_field['ref'],$resource_type_field['options']);

        // important!  this signifies that this field has been migrated by prefixing with -1,,MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX
        sql_query("UPDATE `resource_type_field` SET `options`=CONCAT('" . escape_check (MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX_CATEGORY_TREE) . "',options) WHERE `ref`={$resource_type_field['ref']}");

		}
	else		// general comma separated fields
		{
		$options = preg_split('/\s*,\s*/',$resource_type_field['options']);
		$order=10;
		foreach ($options as $option)
			{
			set_node(null,$resource_type_field['ref'],$option,null,$order);
			$order+=10;
			}

        // important!  this signifies that this field has been migrated by prefixing with MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX
        sql_query("UPDATE `resource_type_field` SET `options`=CONCAT('" . MIGRATION_FIELD_OPTIONS_DEPRECATED_PREFIX . "',',',options) WHERE `ref`={$resource_type_field['ref']}");
		}
	}

function migrate_category_tree_to_nodes($resource_type_field_ref,$category_tree_options)
    {
    $options = array();
    $option_lines = preg_split('/\r\n|\r|\n/',$category_tree_options);
    $order = 10;

    // first pass insert current nodes into nodes table
    foreach ($option_lines as $line)
        {
        $line_fields = preg_split('/\s*,\s*/', $line);
        if (count($line_fields) != 3)
        {
            continue;
        }
        $id = trim($line_fields[0]);
        $parent_id = trim($line_fields[1]);
        $name = trim($line_fields[2]);
        $ref = set_node(null,$resource_type_field_ref,$name,null,$order);

        $options['node_id_' . $id] = array(
            'id' => $id,
            'name' => $name,
            'parent_id' => $parent_id,
            'order' => $order,
            'ref' => $ref
        );
        $order+=10;
        }

    // second pass is to set parent refs
    foreach ($options as $option)
        {
        $ref = $option['ref'];
        $name = $option['name'];
        $order= $option['order'];
        $parent_id = $option['parent_id'];
        if ($parent_id == '')
        {
            continue;
        }
        $parent_ref = isset($options['node_id_' . $parent_id]) ? $options['node_id_' . $parent_id]['ref'] : null;
        set_node($ref,$resource_type_field_ref,$name,$parent_ref,$order);
        }
    }


function populate_resource_nodes($startingref=0)
	{
	global $use_mysqli,$mysql_server,$mysql_username,$mysql_password,$mysql_db;
	
	// Populate resource_node with all resources that have resource_data matching 
	// Also get hit count from resource_keyword if the normalised keyword matches
	
	if (is_process_lock("resource_node_migration"))
		{
		return false;
		}
		
	debug("resource_node_migration starting from node ID: " . $startingref);
	$nodes=sql_query("select n.ref, n.name, n.resource_type_field, f.partial_index from node n join resource_type_field f on n.resource_type_field=f.ref order by resource_type_field;");
	$count=count($nodes);
	
	if($count==0)
		{			
		// Node table is not yet populated. Need to populate this first
		$metadatafields=sql_query("select * from resource_type_field");
		foreach($metadatafields as $metadatafield)
			{
			migrate_resource_type_field_check($metadatafield);
			}			
		$nodes=sql_query("select n.ref, n.name, n.resource_type_field, f.partial_index from node n join resource_type_field f on n.resource_type_field=f.ref order by resource_type_field;");
		$count=count($nodes);
		}
		
	set_process_lock("resource_node_migration");
	
	for($n=$startingref;$n<$count;$n++)
		{
		// Populate node_keyword table
		check_node_indexed($nodes[$n], $nodes[$n]["partial_index"]);
		
		// Get all resources with this node string, adding a union with the resource_keyword table to get hit count.
		// Resource keyword may give false positives for substrings so also make sure we have a hit
		$nodekeyword = normalize_keyword(cleanse_string($nodes[$n]['name'],false));
		sql_query("insert into resource_node (resource, node, hit_count, new_hit_count)
				  select resource,'" . $nodes[$n]['ref'] . "', max(hit_count), max(new_hit_count)
				  from
						(select rk.resource, '" . $nodes[$n]['ref'] . "', rk.hit_count, rk.new_hit_count, 0 found from keyword k
						join resource_keyword rk on rk.keyword=k.ref and rk.resource_type_field='" . $nodes[$n]['resource_type_field'] . "' and rk.resource>0
						where
						k.keyword='" . $nodekeyword  . "'
					union
						select resource, '" . $nodes[$n]['ref'] . "','1' hit_count, '1' new_hit_count, 1 found from resource_data
						where 
						resource_type_field='" . $nodes[$n]['resource_type_field'] . "' and resource>0 and find_in_set('" . escape_check($nodes[$n]['name']) . "',value))
					fn where fn.found=1 group by fn.resource;");
		
		sql_query("delete from sysvars where name='resource_node_migration_state'");
		sql_query("insert into sysvars (name, value) values ('resource_node_migration_state', '$n')");
		}
	
	clear_process_lock("resource_node_migration");
	sql_query("delete from sysvars where name='resource_node_migration_state'");
	sql_query("insert into sysvars (name, value) values ('resource_node_migration_state', 'COMPLETE')");
	return true;
	}
