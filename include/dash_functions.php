<?php
/*
 * Dash Functions - Jethro, Montala Ltd
 * Functions for the homepage dash tiles
 * 
 */

/*
 * Create a dash tile template
 * @$all_users, 
 *	If passed true will push the tile out to all users in your installation
 *  If passed false you must give this tile to a user with sql_insert_id() to have it used
 * 
 */
function create_dash_tile($url,$link,$title,$reload_interval,$all_users,$default_order_by,$resource_count,$text="",$delete=1, array $specific_user_groups = array())
	{
	
	$rebuild_order=TRUE;

	# Validate Parameters
	if(empty($reload_interval) || !is_numeric($reload_interval))
		{$reload_interval=0;}

	$delete    = $delete ? 1 : 0;
	$all_users = $all_users ? 1 : 0;

	if(!is_numeric($default_order_by))
		{
		$default_order_by=append_default_position();
		$rebuild_order=FALSE;
		}
	$resource_count = $resource_count?1:0;

	# De-Duplication of tiles on creation
	$existing = sql_query("SELECT ref FROM dash_tile WHERE url='".$url."' AND link='".$link."' AND title='".escape_check($title)."' AND txt='".escape_check($text)."' AND reload_interval_secs=".$reload_interval." AND all_users=".$all_users." AND resource_count=".$resource_count);
	if(isset($existing[0]["ref"]))
		{
		$tile=$existing[0]["ref"];
		$rebuild_order=FALSE;
		}
	else
		{
		$result = sql_query("INSERT INTO dash_tile (url,link,title,reload_interval_secs,all_users,default_order_by,resource_count,allow_delete,txt) VALUES ('".$url."','".$link."','".escape_check($title)."',".$reload_interval.",".$all_users.",".$default_order_by.",".$resource_count.",".$delete.",'".escape_check($text)."')");
		$tile=sql_insert_id();

        foreach($specific_user_groups as $user_group_id)
            {
            add_usergroup_dash_tile($user_group_id, $tile, $default_order_by);
            build_usergroup_dash($user_group_id);
            }
		}

	# If tile already existed then this no reorder
	if($rebuild_order){reorder_default_dash();}
	
	if($all_users==1 && empty($specific_user_groups))
		{
		sql_query("DELETE FROM user_dash_tile WHERE dash_tile=".$tile);
		$result = sql_query("INSERT user_dash_tile (user,dash_tile,order_by) SELECT user.ref,'".$tile."',5 FROM user");
		}
	return $tile;
	}

/* 
 * Update Dash tile based upon ref
 * This updates the record in the dash_tile table
 * If the all_user flag is being changed it will only get pushed out to users not removed. That action is specifically upon delete not edit as this is a flag
 */
function update_dash_tile($tile,$url,$link,$title,$reload_interval,$all_users,$default_order_by,$resource_count,$text="",$delete=1)
	{
	if(!is_array($tile)){$tile = get_tile($tile);}

	#Sensible Defaults for insertion to Database
	if(empty($reload_interval) || !is_numeric($reload_interval))
		{$reload_interval=0;}
	$delete = $delete?1:0;
	$all_users=$all_users?1:0;

	if(!is_numeric($default_order_by))
		{
		$default_order_by=$tile["default_order_by"];
		}
	$resource_count = $resource_count?1:0;

	sql_query("UPDATE dash_tile 
				SET 
					url='".escape_check($url)."',
					link='".escape_check($link)."',
					title='".escape_check($title)."',
					reload_interval_secs=".$reload_interval.",
					all_users='".$all_users."',
					default_order_by='".$default_order_by."',
					resource_count='".$resource_count."',
					allow_delete='".$delete."',
					txt='".escape_check($text)."'
				WHERE 
					ref='".$tile["ref"]."'");
	# Check if the tile is being changed to an all_user tile from user specific
	if($all_users==1 && $tile["all_users"]==0)
		{
		#Delete the users existing record to ensure they don't get a duplicate.
		sql_query("DELETE FROM user_dash_tile WHERE dash_tile=".$tile["ref"]);
		sql_query("INSERT user_dash_tile (user,dash_tile,order_by) SELECT user.ref,'".$tile["ref"]."',5 FROM user");
		}
	}

/*
 * Delete a dash tile
 * @$tile, the dash_tile.ref number of the tile to be deleted
 * @$cascade, whether this delete should remove the tile from all users.
 */
function delete_dash_tile($tile,$cascade=TRUE,$force=FALSE)
	{
	#Force Delete ignores the allow_delete flag (This allows removal of config tiles)
	$allow_delete = $force? "":"AND allow_delete=1";
	sql_query("DELETE FROM dash_tile WHERE ref='".$tile."' ".$allow_delete);
	if($cascade)
		{
		sql_query("DELETE FROM user_dash_tile WHERE dash_tile='".$tile."'");
		sql_query("DELETE FROM usergroup_dash_tile WHERE dash_tile = '{$tile}'");
		}
	}

/*
 * Turn off push to all users "all_users" flag and cascade delete any existing entries users might have
 * @$tile, the dash_tile.ref number of the tile to be hidden from all users
 */
function revoke_all_users_flag_cascade_delete($tile)
	{
	sql_query("UPDATE dash_tile SET `all_users`=0 WHERE `ref`='{$tile}'");
	sql_query("DELETE FROM `user_dash_tile` WHERE `dash_tile`='{$tile}'");
	}

/*
 * Returns the position to append a tile to the default dash order
 */
function append_default_position()
	{
	$last_tile=sql_query("SELECT default_order_by from dash_tile order by default_order_by DESC LIMIT 1");
	return isset($last_tile[0]["default_order_by"])?$last_tile[0]["default_order_by"]+10:10;
	}

/*
 * Reorders the default dash,
 * this is useful when you have just inserted a new tile or moved a tile and need to reorder them with the proper 10 gaps 
 * Tiles should be ordered with values 10,20,30,40,50,60,70 for easy insertion
 */
function reorder_default_dash()
	{
	$tiles = sql_query("SELECT ref FROM dash_tile WHERE all_users=1 ORDER BY default_order_by");
	$order_by=10 * count($tiles);
	for($i=count($tiles)-1;$i>=0;$i--)
		{
		$result = update_default_dash_tile_order($tiles[$i]["ref"],$order_by);
		$order_by-=10;
		}
	}
/*
 * Simple updates a particular dash_tile with the new order_by.
 * this does NOT apply to a users dash, that must done with the user_dash functions.
 */
function update_default_dash_tile_order($tile,$order_by)
	{
	return sql_query("UPDATE dash_tile SET default_order_by='".$order_by."' WHERE ref='".$tile."'");
	}
/*
 * Gets the full content from a tile record row
 *
 */
function get_tile($tile)
 	{
 	$result=sql_query("SELECT * FROM dash_tile WHERE ref='".$tile."'");
 	return isset($result[0])?$result[0]:false;
 	}

/*
 * Checks if an all_user tile is currently in use and therefore active for all_users
 * Pass the dash_tile.ref of tile to check
 */
function all_user_dash_tile_active($tile)
	{
	return	sql_query("
			SELECT 
				dash_tile.ref AS 'tile',
				dash_tile.title,
				dash_tile.url,
				dash_tile.reload_interval_secs,
				dash_tile.link,
				dash_tile.default_order_by as 'order_by',
				dash_tile.allow_delete 
			FROM dash_tile 
			WHERE 
				dash_tile.all_users=1 
				AND
				dash_tile.ref=".$tile."
				AND 
				(
					dash_tile.allow_delete=1 
					OR 
					(
						dash_tile.allow_delete=0 
						AND 
						dash_tile.ref IN (SELECT DISTINCT user_dash_tile.dash_tile FROM user_dash_tile)
					)
				) ORDER BY default_order_by
			");
	}

/*
 * Checks if a tile already exists.
 * This is based upon  a complete set of values so unless all values match exactly it will return false.
 *
 */
function existing_tile($title,$all_users,$url,$link,$reload_interval,$resource_count,$text="")
	{
	$sql = "SELECT ref FROM dash_tile WHERE url='".$url."' AND link='".$link."' AND title='".escape_check($title)."' AND reload_interval_secs=".$reload_interval." AND all_users=".$all_users." AND resource_count=".$resource_count." AND txt='".escape_check($text)."'";
	$existing = sql_query($sql);
	if(isset($existing[0]["ref"]))
		{
		return true;
		}
	else
		{
		return false;
		}
	}

/*
 * Cleanup Duplicate and Loose Tiles
 * This removes all unused tiles that are flagged as "allowed to delete"
 */
function cleanup_dash_tiles()
	{
	sql_query("DELETE FROM dash_tile WHERE allow_delete = 1 AND ref NOT IN (SELECT DISTINCT dash_tile FROM user_dash_tile)");
	}


/*
 * Checks if this tiles config is still active
 * @param: $tile = tile record
 * @param: $tilestyle = extracted tilestyle of this config tile
 */
function checkTileConfig($tile,$tile_style)
	{
	#Returns whether the config is still on for these tiles
	switch($tile_style)
		{
		case "thmsl": 	global $home_themeheaders; return $home_themeheaders;
		case "theme":	global $home_themes; return $home_themes;
		case "mycol":	global $home_mycollections; return $home_mycollections;
		case "advsr":	global $home_advancedsearch; return $home_advancedsearch;
		case "mycnt":	global $home_mycontributions; return $home_mycontributions;
		case "hlpad":	global $home_helpadvice; return $home_helpadvice;
		case "custm":	global $custom_home_panels; return isset($custom_home_panels)? checkConfigCustomHomePanels($tile,$tile_style) : FALSE;
		}
	}

/*
 * Checks the configuration for each custom tile.
 * If the config for the tile is still there then return true
 */
function checkConfigCustomHomePanels($tile,$tile_style)
	{
	global $custom_home_panels;
	$tile_config_set = FALSE;
	for ($n=0;$n<count($custom_home_panels);$n++)
			{
			if(existing_tile($tile["title"],$tile["all_users"],$tile["url"],$tile["link"],$tile["reload_interval_secs"],$tile["resource_count"],$tile["txt"]))
				{
				$tile_config_set = TRUE;
				}
			}
	return $tile_config_set;
	}

/*
 * All dash tiles available to all_users
 * If you provide a dash_tile ref it will check if this tile exists within the list of available tiles
 *
 */
function get_alluser_available_tiles($tile="null")
	{
	$tilecheck = (is_numeric($tile)) ? "AND ref='".$tile."'":"";
	return sql_query
		(
			"
			SELECT 
				dash_tile.ref,
				dash_tile.ref as 'tile',
				dash_tile.title,
				dash_tile.txt,
				dash_tile.link,
				dash_tile.url,
				dash_tile.reload_interval_secs,
				dash_tile.resource_count,
				dash_tile.all_users,
				dash_tile.allow_delete,
				dash_tile.default_order_by,
				(IF(ref IN (select distinct dash_tile FROM user_dash_tile),1,0)) as 'dash_tile'
			FROM
				dash_tile
			WHERE
				dash_tile.all_users=1 
			".$tilecheck."
                AND ref NOT IN (SELECT dash_tile FROM usergroup_dash_tile)
			ORDER BY 
			dash_tile,
			default_order_by

			"
		);
	}

/*
 * Retrieves the default dash which only display all_user tiles.
 * This should only be accessible to thos with Dash Tile Admin permissions
 */
function get_default_dash($user_group_id = null)
	{
	global $baseurl,$baseurl_short,$lang,$anonymous_login,$username,$dash_tile_shadows, $dash_tile_colour, $dash_tile_colour_options;
	#Build Tile Templates
	$tiles = sql_query("SELECT dash_tile.ref AS 'tile',dash_tile.title,dash_tile.url,dash_tile.reload_interval_secs,dash_tile.link,dash_tile.default_order_by as 'order_by',dash_tile.allow_delete FROM dash_tile WHERE dash_tile.all_users = 1 AND dash_tile.ref NOT IN (SELECT dash_tile FROM usergroup_dash_tile) AND (dash_tile.allow_delete=1 OR (dash_tile.allow_delete=0 AND dash_tile.ref IN (SELECT DISTINCT user_dash_tile.dash_tile FROM user_dash_tile))) ORDER BY default_order_by");
    
    if(!is_null($user_group_id))
        {
        $tiles = get_usergroup_available_tiles($user_group_id);
        }

	$order=10;
	if(count($tiles)==0){echo $lang["nodashtilefound"];exit;}
	foreach($tiles as $tile)
		{
        if(($order != $tile["order_by"] || ($tile["order_by"] % 10) > 0) && is_null($user_group_id))
            {
            update_default_dash_tile_order($tile["tile"],$order);
            }
        else if((!isset($tile['default_order_by']) || $order != $tile['default_order_by'] || ($tile['default_order_by'] % 10) > 0) && !is_null($user_group_id))
            {
            update_usergroup_dash_tile_order($user_group_id, $tile['tile'], $order);
            }

		$order+=10;

        $tile_custom_style = '';

        if($dash_tile_colour)
            {
            $buildstring = explode('?', $tile['url']);
            parse_str(str_replace('&amp;', '&', $buildstring[1]), $buildstring);

            if(isset($buildstring['tltype']) && allow_tile_colour_change($buildstring['tltype']) && isset($buildstring['tlstylecolour']))
                {
                $tile_custom_style .= get_tile_custom_style($buildstring);
                }
            }
            ?>
		<a 
			<?php 
			# Check link for external or internal
			if(mb_strtolower(substr($tile["link"],0,4))=="http")
				{
				$link = $tile["link"];
				$newtab = true;
				}
			else
				{
				$link = $baseurl."/".htmlspecialchars($tile["link"]);
				$newtab=false;
				}
			?>
			href="<?php echo $link?>" <?php echo $newtab ? "target='_blank'" : "";?>
			onClick="if(dragging){dragging=false;e.defaultPrevented;}" 
			class="HomePanel DashTile DashTileDraggable <?php echo $tile["allow_delete"]? "":"conftile";?>" 
			id="tile<?php echo htmlspecialchars($tile["tile"]);?>"
		>
			<div id="contents_tile<?php echo htmlspecialchars($tile["tile"]);?>" class="HomePanelIN HomePanelDynamicDash <?php echo ($dash_tile_shadows)? "TileContentShadow":"";?>" style="<?php echo $tile_custom_style; ?>">
				<?php if (strpos($tile["url"],"dash_tile.php")!==false) {
                                # Only pre-render the title if using a "standard" tile and therefore we know the H2 will be in the target data.
                                ?>
                                <h2 class="title"><?php echo htmlspecialchars($tile["title"]);?></h2>
                                <?php } ?>
				<p>Loading...</p>
				<script>
					height = jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").height();
					width = jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").width();
					jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").load("<?php echo $baseurl."/".$tile["url"]."&tile=".htmlspecialchars($tile["tile"]);?>&tlwidth="+width+"&tlheight="+height);
				</script>
			</div>
			
		</a>
		<?php
		}
		?>
		<div id="dash_tile_bin"><span class="dash_tile_bin_text"><?php echo $lang["tilebin"];?></span></div>
		<div id="delete_dialog" style="display:none;"></div>
		<div id="delete_permanent_dialog" style="display:none;text-align:left;"><?php echo $lang['confirmdeleteconfigtile'];?></div>
	
		<script>
			function deleteDefaultDashTile(id) {
				jQuery.post( "<?php echo $baseurl?>/pages/ajax/dash_tile.php",{"tile":id,"delete":"true"},function(data){
					jQuery("#tile"+id).remove();
				});
			}
			function updateDashTileOrder(index,tile) {
				jQuery.post( "<?php echo $baseurl?>/pages/ajax/dash_tile.php",{"tile":tile,"new_index":((index*10))<?php if(!is_null($user_group_id)) { echo ", \"selected_user_group\": {$user_group_id}";} ?>});
			}
			var dragging=false;
				jQuery(function() {
					if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {
						jQuery("#HomePanelContainer").prepend("<p><?php echo $lang["dashtilesmalldevice"];?></p>");
						return false;
					}
				 	jQuery("#HomePanelContainer").sortable({
				  	  items: ".DashTileDraggable",
				  	  start: function(event,ui) {
				  	  	jQuery("#dash_tile_bin").show();
				  	  	dragging=true;
				  	  },
				  	  stop: function(event,ui) {
			          	jQuery("#dash_tile_bin").hide();
				  	  },
			          update: function(event, ui) {
			          	nonDraggableTiles = jQuery(".HomePanel").length - jQuery(".DashTileDraggable").length;
			          	newIndex = (ui.item.index() - nonDraggableTiles)+1;
			          	var id=jQuery(ui.item).attr("id").replace("tile","");
			          	updateDashTileOrder(newIndex,id);
			          }
				  	});
				    jQuery("#dash_tile_bin").droppable({
						accept: ".DashTileDraggable",
						activeClass: "ui-state-hover",
						hoverClass: "ui-state-active",
						drop: function(event,ui) {
							var id=jQuery(ui.draggable).attr("id");
							id = id.replace("tile","");
							title = jQuery(ui.draggable).find(".title").html();
							jQuery("#dash_tile_bin").hide();
							if(jQuery("#tile"+id).hasClass("conftile")) {
								jQuery("#delete_permanent_dialog").dialog({
							    	title:'<?php echo $lang["dashtiledelete"]; ?>',
							    	modal: true,
									resizable: false,
									dialogClass: 'delete-dialog no-close',
							        buttons: {
							            "<?php echo $lang['confirmdefaultdashtiledelete'] ?>": function() {
							            		jQuery(this).dialog("close");
							            		deleteDefaultDashTile(id);
							            	},    
							            "<?php echo $lang['cancel'] ?>": function() { 
							            		jQuery(this).dialog('close');
							            	}
							        }
							    });
							    return;
							}
							jQuery("#delete_dialog").dialog({
						    	title:'<?php echo $lang["dashtiledelete"]; ?>',
						    	modal: true,
								resizable: false,
								dialogClass: 'delete-dialog no-close',
						        buttons: {
						            "<?php echo $lang['confirmdefaultdashtiledelete'] ?>": function() {jQuery(this).dialog("close");deleteDefaultDashTile(id); },    
						            "<?php echo $lang['cancel'] ?>": function() { jQuery(this).dialog('close'); }
						        }
						    });
						}
			    	});
			  	});
		</script>
	<div class="clearerleft"></div>
	<?php
	}
/*
 * Shows only tiles that are marked for all_users (and displayed on a user dash if they are a legacy tile).
 * No controls to modify or reorder (See $managed_home_dash config option)
 */
function get_managed_dash()
	{
	global $baseurl,$baseurl_short,$lang,$anonymous_login,$username,$dash_tile_shadows, $anonymous_default_dash, $userref, $usergroup, $dash_tile_colour, $dash_tile_colour_options;
	#Build Tile Templates
	if(checkPermission_anonymoususer() && !$anonymous_default_dash)
        {
        // Anonymous user but may have had dash customised dash configured first
        $tiles = sql_query("SELECT dash_tile.ref AS 'tile',dash_tile.title,dash_tile.url,dash_tile.reload_interval_secs,dash_tile.link,dash_tile.default_order_by as 'order_by'
                       FROM user_dash_tile
                            JOIN dash_tile
                            ON user_dash_tile.dash_tile = dash_tile.ref
                            WHERE user_dash_tile.user='".$userref."'
                            ORDER BY user_dash_tile.order_by");    
        }
    else
        {
        $tiles = sql_query("SELECT dash_tile.ref AS 'tile', dash_tile.title, dash_tile.url, dash_tile.reload_interval_secs, dash_tile.link, dash_tile.default_order_by as 'order_by'
                              FROM dash_tile
                             WHERE dash_tile.all_users = 1
                               AND (dash_tile.ref IN (SELECT dash_tile FROM usergroup_dash_tile WHERE usergroup_dash_tile.usergroup = '{$usergroup}')
								OR dash_tile.ref NOT IN (SELECT distinct dash_tile FROM usergroup_dash_tile))
                               AND (
                                    dash_tile.allow_delete = 1
                                    OR (
                                        dash_tile.allow_delete = 0
                                        AND dash_tile.ref IN (SELECT DISTINCT user_dash_tile.dash_tile FROM user_dash_tile)
                                       )
                                   )
                            ORDER BY default_order_by"
                            );
        }
    
    foreach($tiles as $tile)
		{
        $tile_custom_style = '';

        if($dash_tile_colour)
            {
            $buildstring = explode('?', $tile['url']);
            parse_str(str_replace('&amp;', '&', $buildstring[1]), $buildstring);

            if(isset($buildstring['tltype']) && allow_tile_colour_change($buildstring['tltype']) && isset($buildstring['tlstylecolour']))
                {
                $tile_custom_style .= get_tile_custom_style($buildstring);
                }
            }
		?>
		<a 
			<?php 
			# Check link for external or internal
			if(mb_strtolower(substr($tile["link"],0,4))=="http")
				{
				$link = $tile["link"];
				$newtab = true;
				}
			else
				{
				$link = $baseurl."/".htmlspecialchars($tile["link"]);
				$newtab=false;
				}
			?>
			href="<?php echo $link?>" <?php echo $newtab ? "target='_blank'" : "";?>
			onClick="if(dragging){dragging=false;e.defaultPrevented;}" 
			class="HomePanel DashTile DashTileDraggable" 
			id="tile<?php echo htmlspecialchars($tile["tile"]);?>"
		>
			<div id="contents_tile<?php echo htmlspecialchars($tile["tile"]);?>" class="HomePanelIN HomePanelDynamicDash <?php echo ($dash_tile_shadows)? "TileContentShadow":"";?>" style="<?php echo $tile_custom_style; ?>">
				<?php if (strpos($tile["url"],"dash_tile.php")!==false) 
					{
                    # Only pre-render the title if using a "standard" tile and therefore we know the H2 will be in the target data.
                    ?>
                    <h2 class="title"><?php echo htmlspecialchars($tile["title"]);?></h2>
                    <?php 
                	} ?>
				<p>Loading...</p>
				<script>
					height = jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").height();
					width = jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").width();
					jQuery("#contents_tile<?php echo htmlspecialchars($tile["tile"]);?>").load("<?php echo $baseurl."/".$tile["url"]."&tile=".htmlspecialchars($tile["tile"]);?>&tlwidth="+width+"&tlheight="+height);
				</script>
			</div>
		</a>
		<?php
		} 
	?>
	<div class="clearerleft"></div>
	<?php
	}


/*
 * User Group dash functions
 */

/*
 * Add a tile for a user group
 *
 */
function add_usergroup_dash_tile($usergroup, $tile, $default_order_by)
    {
    if(!is_numeric($usergroup) || !is_numeric($tile))
        {
        return false;
        }

    $reorder = true;
    if(!is_numeric($default_order_by))
        {
        $default_order_by = append_usergroup_position($usergroup);
        $reorder          = false;
        }

    $existing = sql_query("SELECT * FROM usergroup_dash_tile WHERE usergroup = '{$usergroup}' AND dash_tile = {$tile}");
    if(!$existing)
        {
        $result = sql_query("INSERT INTO usergroup_dash_tile (usergroup, dash_tile, default_order_by) VALUES ('{$usergroup}', '{$tile}', '{$default_order_by}')");
        }
    else
        {
        return $existing;
        }

    if($reorder)
        {
        reorder_usergroup_dash($usergroup);
        }

    return true;
    }

/*
 * Get the position for a new tile at the end of the current usergroup tiles.
 * Returns the last position or the first position if no tiles found for this usergroup
 */
function append_usergroup_position($usergroup)
    {
    $last_tile = sql_query("SELECT order_by FROM usergroup_dash_tile WHERE usergroup = '{$usergroup}' ORDER BY default_order_by DESC LIMIT 1");

    return isset($last_tile[0]['default_order_by']) ? $last_tile[0]['order_by'] + 10 : 10;
    }

function reorder_usergroup_dash($usergroup)
    {
    $usergroup_tiles = sql_query("SELECT usergroup_dash_tile.ref FROM usergroup_dash_tile LEFT JOIN dash_tile ON usergroup_dash_tile.dash_tile = dash_tile.ref WHERE usergroup_dash_tile.usergroup = '{$usergroup}' ORDER BY usergroup_dash_tile.default_order_by");
    $order_by        = 10 * count($usergroup_tiles);

    for($i = count($usergroup_tiles) - 1; $i >= 0; $i--)
        {
        update_usergroup_dash_tile_order($usergroup, $usergroup_tiles[$i]['ref'], $order_by);
        $order_by -= 10;
        }
    }

function update_usergroup_dash_tile_order($usergroup, $tile, $default_order_by)
    {
    sql_query("UPDATE usergroup_dash_tile SET default_order_by = '{$default_order_by}' WHERE usergroup = '{$usergroup}' AND ref = '{$tile}'");
    }

function build_usergroup_dash($user_group, $user_id = 0)
    {
    $user_group_tiles = sql_array("SELECT dash_tile.ref AS `value` FROM usergroup_dash_tile JOIN dash_tile ON usergroup_dash_tile.dash_tile = dash_tile.ref WHERE usergroup_dash_tile.usergroup = '{$user_group}' ORDER BY usergroup_dash_tile.default_order_by");

    // If client code has specified a user ID, then just add the tiles for it
    if(is_numeric($user_id) && 0 < $user_id)
        {
        $starting_order = append_user_position($user_id);

        foreach($user_group_tiles as $tile)
            {
            sql_query("DELETE FROM user_dash_tile WHERE user = '{$user_id}' AND dash_tile = {$tile}");

            add_user_dash_tile($user_id, $tile, $starting_order);
            $starting_order += 10;
            }

        return;
        }

    $user_list = sql_array("SELECT ref AS `value` FROM user WHERE usergroup = '{$user_group}'");
    foreach($user_list as $user)
        {
        $starting_order  = append_user_position($user);

        foreach($user_group_tiles as $tile)
            {
            sql_query("DELETE FROM user_dash_tile WHERE user = '{$user}' AND dash_tile = {$tile}");

            add_user_dash_tile($user, $tile, $starting_order);
            $starting_order += 10;
            }
        }

    return;
    }

function get_tile_user_groups($tile_id)
    {
    return sql_array("SELECT usergroup AS `value` FROM usergroup_dash_tile WHERE dash_tile = '{$tile_id}';");
    }


function get_usergroup_available_tiles($user_group_id, $tile = '')
    {
    if(!is_numeric($user_group_id))
        {
        trigger_error('$user_group_id has to be a number');
        }

    $tile_sql = '';
    if('' != $tile)
        {
        $tile_sql = "AND dt.ref = '" . escape_check($tile) . "'";
        }

    return sql_query("SELECT dt.ref, dt.ref AS `tile`, dt.title, dt.txt, dt.link, dt.url, dt.reload_interval_secs, dt.resource_count, dt.all_users, dt.allow_delete, dt.default_order_by, udt.order_by , 1 AS 'dash_tile' FROM dash_tile AS dt LEFT JOIN usergroup_dash_tile AS udt ON dt.ref = udt.dash_tile WHERE dt.all_users = 1 AND udt.usergroup = '{$user_group_id}' {$tile_sql} ORDER BY udt.default_order_by ASC");
    }

/**
 * Get usergroup_dash_tile record
 * 
 * @param integer $tile_id
 * @param integer $user_group_id
 * 
 * @return array
 */
 function get_usergroup_tile($tile_id, $user_group_id)
    {
    $return = sql_query("SELECT * FROM usergroup_dash_tile WHERE dash_tile = '" . escape_check($tile_id) . "' AND usergroup = '" . escape_check($user_group_id) . "'");

    if(0 < count($return))
        {
        return $return[0];
        }

    return array();
    }

/*
 * User Dash Functions 
 */

/*
 * Add a tile to a users dash
 * Affects the user_dash_tile table, tile must be the ref of a record from dash_tile
 *
 */
function add_user_dash_tile($user,$tile,$order_by)
	{
	$reorder=TRUE;
	if(!is_numeric($user)||!is_numeric($tile)){return false;}
	if(!is_numeric($order_by))
		{
		$order_by=append_user_position($user);
		$reorder=FALSE;
		}
	$existing = sql_query("SELECT * FROM user_dash_tile WHERE user=".$user." AND dash_tile=".$tile);
	if(!$existing)
		{
		$result = sql_query("INSERT INTO user_dash_tile (user,dash_tile,order_by) VALUES (".$user.",".$tile.",".$order_by.")");
		}
	else
		{
		return $existing;
		}
	if($reorder){reorder_user_dash($user);}
	return true;
	}

/*
 * Get user_dash_tile record, 
 * Provide the user_dash_tile ref as the $tile
 * this a place holder which links a dash_tile template with the user and the order that that tile should appear on THIS users dash
 *
 */
 function get_user_tile($usertile,$user)
 	{
 	$result=sql_query("SELECT * FROM user_dash_tile WHERE ref='".escape_check($usertile)."' AND user=".escape_check($user));
 	return isset($result[0])?$result[0]:false;
 	}

 /*
  * Builds a users dash, this is a quick way of adding all_user tiles back to a users dash. 
  * The Add_user_dash_tile function used checks for an existing match so that it won't duplicate tiles on a users dash
  * 
  */
 function create_new_user_dash($user)
 	{
 	$tiles = sql_query("SELECT dash_tile.ref as 'tile',dash_tile.title,dash_tile.url,dash_tile.reload_interval_secs,dash_tile.link,dash_tile.default_order_by as 'order' FROM dash_tile WHERE dash_tile.all_users = 1 AND ref NOT IN (SELECT dash_tile FROM usergroup_dash_tile) AND (dash_tile.allow_delete=1 OR (dash_tile.allow_delete=0 AND dash_tile.ref IN (SELECT DISTINCT user_dash_tile.dash_tile FROM user_dash_tile))) ORDER BY default_order_by");
 	foreach($tiles as $tile)
 		{
 		add_user_dash_tile($user,$tile["tile"],$tile["order"]);
 		}
 	}
/*
 * Updates a user_dash_tile record for a specific tile on a users dash with an order.
 *
 */
function update_user_dash_tile_order($user,$tile,$order_by)
	{
	return sql_query("UPDATE user_dash_tile SET order_by='".escape_check($order_by)."' WHERE user='".escape_check($user)."' and ref='".$tile."'");
	}
/*
 * Delete a tile from a user dash
 * this will only remove the tile from this users dash. 
 * It must be the ref of the row in the user_dash_tile
 * this also performs cleanup to ensure that there are no unused templates in the dash_tile table
 *
 */
function delete_user_dash_tile($usertile,$user)
	{
	if(!is_numeric($usertile) || !is_numeric($user)){return false;}
	
	$row = get_user_tile($usertile,$user);
	sql_query("DELETE FROM user_dash_tile WHERE ref='".$usertile."' and user='".$user."'");

	$existing = sql_query("SELECT count(*) as 'count' FROM user_dash_tile WHERE dash_tile='".$row["dash_tile"]."'");
	if($existing[0]["count"]<1)
		{
		delete_dash_tile($row["dash_tile"]);
		}
	}

/*
 * Remove all tiles from a users dash
 * Purge option does the cleanup in dash_tile removing any unused tiles
 * Turn purge off if you are just doing a quick rebuild of the tiles.
 */
function empty_user_dash($user,$purge=true)
	{
	$usertiles = sql_query("SELECT dash_tile FROM user_dash_tile WHERE user_dash_tile.user='".escape_check($user)."'");
	sql_query("DELETE FROM user_dash_tile WHERE user='".$user."'");
	if($purge)
		{
		foreach($usertiles as $tile)
			{
			$existing = sql_query("SELECT count(*) as 'count' FROM user_dash_tile WHERE dash_tile='".$tile["dash_tile"]."'");
			if($existing[0]["count"]<1)
				{
				delete_dash_tile($tile["dash_tile"]);
				}
			}
		}	
	}


/*
 * Reorders the users dash,
 * this is useful when you have just inserted a new tile or moved a tile and need to reorder them with the proper 10 gaps 
 * Tiles should be ordered with values 10,20,30,40,50,60,70 for easy insertion
 */
function reorder_user_dash($user)
	{
	$user_tiles = sql_query("SELECT user_dash_tile.ref FROM user_dash_tile LEFT JOIN dash_tile ON user_dash_tile.dash_tile = dash_tile.ref WHERE user_dash_tile.user='".$user."' ORDER BY user_dash_tile.order_by");
	$order_by=10 * count($user_tiles);
	for($i=count($user_tiles)-1;$i>=0;$i--)
		{
		$result = update_user_dash_tile_order($user,$user_tiles[$i]["ref"],$order_by);
		$order_by-=10;
		}
	}

/*
 * Returns the position for a tile at the end of existing tiles
 *
 */
function append_user_position($user)
	{
	$last_tile=sql_query("SELECT order_by FROM user_dash_tile WHERE user='".$user."' ORDER BY order_by DESC LIMIT 1");
	return isset($last_tile[0]["order_by"])?$last_tile[0]["order_by"]+10:10;
	}

/*
 * All dash tiles available to the supplied userref
 * If you provide a dash_tile ref it will check if this tile exists within the list of available tiles to the user
 *
 */
function get_user_available_tiles($user,$tile="null")
	{
	$tilecheck = (is_numeric($tile)) ? "WHERE ref='".$tile."'":"";
	return sql_query
		(
			"
			SELECT 
				result.*
			FROM
			(	(
				SELECT 
					dash_tile.ref,
					'' as 'dash_tile',
					'' as 'usertile', 
					'' as 'user', 
					'' as 'order_by',
					dash_tile.ref as 'tile',
					dash_tile.title,
					dash_tile.txt,
					dash_tile.link,
					dash_tile.url,
					dash_tile.resource_count,
					dash_tile.all_users,
					dash_tile.allow_delete,
					dash_tile.default_order_by
				FROM
					dash_tile
				WHERE
					dash_tile.all_users = 1
					AND
					ref 
					NOT IN
					(
						SELECT 
							dash_tile.ref
						FROM
							user_dash_tile
						RIGHT OUTER JOIN
							dash_tile
						ON 
							user_dash_tile.dash_tile = dash_tile.ref

						WHERE
							user_dash_tile.user = '".$user."'
					)
                AND ref NOT IN (SELECT dash_tile FROM usergroup_dash_tile)
				)
			UNION
				(
				SELECT 
					dash_tile.ref,
					user_dash_tile.dash_tile,
					user_dash_tile.ref as 'usertile', 
					user_dash_tile.user, 
					user_dash_tile.order_by,
					dash_tile.ref as 'tile',
					dash_tile.title,
					dash_tile.txt,
					dash_tile.link,
					dash_tile.url,
					dash_tile.resource_count,
					dash_tile.all_users,
					dash_tile.allow_delete,
					dash_tile.default_order_by
				FROM
					user_dash_tile
				RIGHT OUTER JOIN
					dash_tile
				ON 
					user_dash_tile.dash_tile = dash_tile.ref
				WHERE
					user_dash_tile.user = '".$user."'
				)
			) result
			".$tilecheck."
			ORDER BY result.order_by,result.default_order_by

			"
		);
	}

/*
 * Returns a users dash along with all necessary scripts and tools for manipulation
 * checks for the permissions which allow for deletions and manipulation of all_user tiles from the dash
 *
 */
function get_user_dash($user)
	{
	global $baseurl,$baseurl_short,$lang,$dash_tile_shadows,$help_modal, $dash_tile_colour, $dash_tile_colour_options;
	#Build User Dash and recalculate order numbers on display
	$user_tiles = sql_query("SELECT dash_tile.ref AS 'tile',dash_tile.title,dash_tile.all_users,dash_tile.url,dash_tile.reload_interval_secs,dash_tile.link,user_dash_tile.ref AS 'user_tile',user_dash_tile.order_by FROM user_dash_tile JOIN dash_tile ON user_dash_tile.dash_tile = dash_tile.ref WHERE user_dash_tile.user='".$user."' ORDER BY user_dash_tile.order_by");

	$order=10;
	foreach($user_tiles as $tile)
		{
		if($order != $tile["order_by"] || ($tile["order_by"] % 10) > 0){update_user_dash_tile_order($user,$tile["user_tile"],$order);}
		$order+=10;

        $tile_custom_style = '';

        if($dash_tile_colour)
            {
            $buildstring = explode('?', $tile['url']);
            parse_str(str_replace('&amp;', '&', $buildstring[1]), $buildstring);

            if(isset($buildstring['tltype']) && allow_tile_colour_change($buildstring['tltype']) && isset($buildstring['tlstylecolour']))
                {
                $tile_custom_style .= get_tile_custom_style($buildstring);
                }
            }
		?>
		<a 
			<?php 
			# Check link for external or internal
			if(mb_strtolower(substr($tile["link"],0,4))=="http")
				{
				$link = $tile["link"];
				$newtab = true;
				}
			else
				{
				$link = $baseurl."/".htmlspecialchars($tile["link"]);
				$newtab=false;
				}
			?>
			href="<?php echo parse_dashtile_link($link)?>" <?php echo $newtab ? "target='_blank'" : "";?> 
			onClick="if(dragging){dragging=false;e.defaultPrevented}<?php echo $newtab? "": "return " . ($help_modal && strpos($link,"pages/help.php")!==false?"ModalLoad(this,true);":"CentralSpaceLoad(this,true);");?>" 
			class="HomePanel DashTile DashTileDraggable <?php echo ($tile['all_users']==1)? 'allUsers':'';?>"
			tile="<?php echo $tile['tile']; ?>"
			id="user_tile<?php echo htmlspecialchars($tile["user_tile"]);?>"
		>
			<div id="contents_user_tile<?php echo htmlspecialchars($tile["user_tile"]);?>" class="HomePanelIN HomePanelDynamicDash <?php echo ($dash_tile_shadows)? "TileContentShadow":"";?>" style="<?php echo $tile_custom_style; ?>">
				<script>
				jQuery(function(){
					var height = jQuery("#contents_user_tile<?php echo htmlspecialchars($tile["user_tile"]);?>").height();
					var width = jQuery("#contents_user_tile<?php echo htmlspecialchars($tile["user_tile"]);?>").width();
					jQuery('#contents_user_tile<?php echo htmlspecialchars($tile["user_tile"]) ?>').load("<?php echo $baseurl."/".$tile["url"]."&tile=".htmlspecialchars($tile["tile"]);?>&user_tile=<?php echo htmlspecialchars($tile["user_tile"]);?>&tlwidth="+width+"&tlheight="+height);
				});
				</script>
			</div>
			
		</a>
		<?php
		}
	# Check Permissions to Display Deleting Dash Tiles
	if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")) || !checkperm("dtu"))
		{ ?>
		<div id="dash_tile_bin"><span class="dash_tile_bin_text"><?php echo $lang["tilebin"];?></span></div>
		<div id="delete_dialog" style="display:none;"></div>
		<div id="delete_permanent_dialog" style="display:none;text-align:left;"><?php echo $lang['confirmdeleteconfigtile'];?></div>
		<script>
			function deleteDashTile(id) {
				jQuery.post( "<?php echo $baseurl?>/pages/ajax/dash_tile.php",{"user_tile":id,"delete":"true"},function(data){
					jQuery("#user_tile"+id).remove();
				});
			}
			function deleteDefaultDashTile(tileid,usertileid) {
				jQuery.post( "<?php echo $baseurl?>/pages/ajax/dash_tile.php",{"tile":tileid,"delete":"true"},function(data){
					jQuery("#user_tile"+usertileid).remove();
				});
			}
		<?php
		}
	else
		{
		echo "<script>";
		} ?>
		function updateDashTileOrder(index,tile) {
			jQuery.post( "<?php echo $baseurl?>/pages/ajax/dash_tile.php",{"user_tile":tile,"new_index":((index*10))});
		}
		var dragging=false;
			jQuery(function() {
				if(jQuery(window).width()<600 && jQuery(window).height()<600 && is_touch_device()) {
						return false;
					}				
			 	jQuery("#HomePanelContainer").sortable({
			  	  items: ".DashTileDraggable",
			  	  start: function(event,ui) {
			  	  	jQuery("#dash_tile_bin").show();
			  	  	dragging=true;
			  	  },
			  	  stop: function(event,ui) {
		          	jQuery("#dash_tile_bin").hide();
			  	  },
		          update: function(event, ui) {
		          	nonDraggableTiles = jQuery(".HomePanel").length - jQuery(".DashTileDraggable").length;
		          	newIndex = (ui.item.index() - nonDraggableTiles) + 1;
		          	var id=jQuery(ui.item).attr("id").replace("user_tile","");
		          	updateDashTileOrder(newIndex,id);
		          }
			  	});
			<?php
			# Check Permissions to Display Deleting Dash Tiles
			if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")) || !checkperm("dtu"))
				{
				?> 	
			    jQuery("#dash_tile_bin").droppable({
			      accept: ".DashTileDraggable",
			      activeClass: "ui-state-hover",
			      hoverClass: "ui-state-active",
			      drop: function(event,ui) {
			      	var id=jQuery(ui.draggable).attr("id");
			      	id = id.replace("user_tile","");
			    <?php
			    # If permission to delete all_user tiles
			    if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))
			    	{ ?>
			    	var tileid=jQuery(ui.draggable).attr("tile");
			    <?php
			      	} ?>

			      	title = jQuery(ui.draggable).find(".title").html();
			      	jQuery("#dash_tile_bin").hide();
		      	<?php
		      	# If permission to delete all_user tiles
				if((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))
					{
					?>
			      	if(jQuery(ui.draggable).hasClass("allUsers")) {
			      		// This tile is set for all users so provide extra options
				        jQuery("#delete_dialog").dialog({
				        	title:'<?php echo $lang["dashtiledelete"]; ?>',
				        	modal: true,
		    				resizable: false,
	    					dialogClass: 'delete-dialog no-close',
		                    buttons: {
		                        "<?php echo $lang['confirmdashtiledelete'] ?>": function() {deleteDashTile(id); jQuery(this).dialog( "close" );},
		                        "<?php echo $lang['confirmdefaultdashtiledelete'] ?>": function() {deleteDefaultDashTile(tileid,id); jQuery(this).dialog( "close" );},
		                        "<?php echo $lang['managedefaultdash'] ?>": function() {window.location = "<?php echo $baseurl; ?>/pages/team/team_dash_tile.php"; return false;},
		                        "<?php echo $lang['cancel'] ?>":  function() { jQuery(this).dialog('close'); }
		                    }
		                });
		            }
		            else {
		            	//This tile belongs to this user only
				        jQuery("#delete_dialog").dialog({
				        	title:'<?php echo $lang["dashtiledelete"]; ?>',
				        	modal: true,
		    				resizable: false,	    				
	    					dialogClass: 'delete-dialog no-close',
		                    buttons: {
		                        "<?php echo $lang['confirmdashtiledelete'] ?>": function() {deleteDashTile(id); jQuery(this).dialog( "close" );},
		                        "<?php echo $lang['cancel'] ?>": function() { jQuery(this).dialog('close'); }
		                    }
		                });
		            }
	            <?php
	            	}
	       		else #Only show dialog to delete for this user
	       			{ ?>
	       			var dialog = jQuery("#delete_dialog").dialog({
			        	title:'<?php echo $lang["dashtiledelete"]; ?>',
			        	modal: true,
	    				resizable: false,
	    				dialogClass: 'delete-dialog no-close',
	                    buttons: {
	                        "<?php echo $lang['confirmdashtiledelete'] ?>": function() {deleteDashTile(id); jQuery(this).dialog( "close" );},
	                        "<?php echo $lang['cancel'] ?>": function() {jQuery(this).dialog('close'); }
	                    }
	                });
			    <?php
	       			} ?>
			      }
		    	});
		    	<?php
	    		} 
	    	?>
		  	});

	</script>
	<?php
	}

/*
 * Helper Functions
 */
function parse_dashtile_link($link)
	{
	global $userref;
	$link = str_replace("[userref]",$userref,$link);

	return $link;
	}

/*
 * Dash Admin Display Functions
 */
#Build dash listfunction
function build_dash_tile_list($dtiles_available)
	{
	global $lang,$baseurl_short,$baseurl;
	foreach($dtiles_available as $tile)
  		{
  		$checked = false;
  		if(!empty($tile["dash_tile"]))
  			{$checked=true;}

  		$buildstring = explode('?',$tile["url"]);
		parse_str(str_replace("&amp;","&",$buildstring[1]),$buildstring);
  		?>
  		<tr id="tile<?php echo $tile["ref"];?>">
  			<td>
  				<input 
  					type="checkbox" 
  					class="tilecheck" 
  					name="tiles[]" 
  					value="<?php echo $tile["ref"];?>" 
  					onChange="changeTile(<?php echo $tile["ref"];?>,<?php echo $tile["all_users"];?>);"
  					<?php echo $checked?"checked":"";?> 
  				/>
  			</td>
  			<td>
  				<?php 
  				if(isset($buildstring["tltype"]) && $buildstring["tltype"]=="conf" && $buildstring["tlstyle"]!="custm" && $buildstring["tlstyle"]!="pend" && isset($lang[$tile["title"]]))
  					{echo i18n_get_translated($lang[$tile["title"]]);}
  				else 
  					{echo i18n_get_translated($tile["title"]);}
  				?>
  			</td>
  			<td>
  				<?php 
  				if(isset($buildstring["tltype"]) && $buildstring["tltype"]=="conf" && $buildstring["tlstyle"]!="custm" && $buildstring["tlstyle"]!="pend")
  					{$tile["txt"] = text($tile["title"]);}
  				else if(isset($buildstring["tltype"]) && $buildstring["tltype"]=="conf" && $buildstring["tlstyle"]=="pend")
  					{
					if(isset($lang[strtolower($tile['txt'])]))
						{
						$tile['txt'] = $lang[strtolower($tile["txt"])];
						}
					else
						{
						$tile['txt'] = htmlspecialchars($tile['txt']);
						}
					}
  				
  				if(strlen($tile["txt"])>75)
  					{
  					echo substr(i18n_get_translated($tile["txt"]),0,72)."...";
  					}
  				else
  					{
  					echo i18n_get_translated($tile["txt"]);
  					}
  				?>
  			</td>
  			<td>
  				<a 
  					href="<?php echo (mb_strtolower(substr($tile["link"],0,4))=="http")? $tile["link"]: $baseurl."/".htmlspecialchars($tile["link"]);?>"
  					target="_blank"
  				>
  					<?php echo $lang["dashtilevisitlink"];?>
  				</a>
  			</td>
  			<td><?php echo $tile["resource_count"]? $lang["yes"]: $lang["no"];?></td>
  			<td>
  				<?php
  				if  (	
  						$tile["allow_delete"]
  						&&
  						(
  							($tile["all_users"] && checkPermission_dashadmin()) 
  							|| 
  							(!$tile["all_users"] && (checkPermission_dashuser() || checkPermission_dashadmin()))
	  					)
  					)
  					{ ?>
  					<a href="<?php echo $baseurl_short; ?>pages/dash_tile.php?edit=<?php echo $tile['ref'];?>" ><?php echo $lang["action-edit"];?></a>
  					<?php
  					}
  				?>
  			</td>
  		</tr>
  		<?php
  		}
  	}

/**
* Check whether we allow a colour change of a tile from the interface.
* At the moment it is only available for blank search tiles and text
* text only tiles.
* 
* @param string $tile_type
* @param string $tile_style Examples: thmbs, multi, blank, ftxt
* 
* @return boolean
*/
function allow_tile_colour_change($tile_type, $tile_style = '')
    {
    global $lang, $dash_tile_colour, $dash_tile_colour_options, $tile_styles;

    $allowed_styles = array('blank', 'ftxt');

    // Check a specific style for a type
    if($dash_tile_colour && '' !== $tile_style && !in_array($tile_style, $allowed_styles))
        {
        return false;
        }

    // Is one of the allowed styles in the styles available for this tile type?
    if($dash_tile_colour && 0 < count(array_intersect($tile_styles[$tile_type], $allowed_styles)))
        {
        return true;
        }

    return false;
    }

/**
* Renders a new section to pick/ select a colour. User can either use the color
* picker or select a colour from the ones already available (config option)
* 
* @param string $tile_style
* @param string $tile_colour Hexadecimal code (without the # sign). Example: 0A8A0E
* 
* @return void
*/
function render_dash_tile_colour_chooser($tile_style, $tile_colour)
    {
    global $lang, $dash_tile_colour, $dash_tile_colour_options, $baseurl;
    if('ftxt' == $tile_style)
        {
        ?>
        <div class="Question">
        <?php
        }
    else
        {
        ?>
        <span id="tile_style_colour_chooser" style="display: none;">
        <?php
        }
        ?>
            <label class="stdwidth"><?php echo $lang['colour']; ?></label>
    <?php
    // Show either color picker OR a drop down selector
    if(0 === count($dash_tile_colour_options))
        {
        ?>
        <script src="<?php echo $baseurl; ?>/lib/spectrum/spectrum.js"></script>
        <link rel="stylesheet" href="<?php echo $baseurl; ?>/lib/spectrum/spectrum.css" />
        <input id="tile_style_colour" name="tlstylecolour" type="text" onchange="update_tile_preview_colour(this.value);" value="<?php echo $tile_colour; ?>">
        <script>
            jQuery('#tile_style_colour').spectrum({
                showAlpha: true,
                showInput: true,
                clickoutFiresChange: true,
                preferredFormat: 'rgb'
            });
        </script>
        <?php
        }
    else
        {
        ?>
        <select id="tile_style_colour" name="tile_style_colour" onchange="update_tile_preview_colour(this.value);">
        <?php
        foreach($dash_tile_colour_options as $dash_tile_colour_option_value => $dash_tile_colour_option_text)
            {
            ?>
            <option value="<?php echo $dash_tile_colour_option_value; ?>"><?php echo $dash_tile_colour_option_text; ?></option>
            <?php
            }
        ?>
        </select>
        <?php
        }
        ?>

    <!-- Show/ hide colour picker/ selector -->
    <script>
        function update_tile_preview_colour(colour)
            {
            jQuery('#previewdashtile').css('background-color', '#' + colour);
            }

    <?php
    if('ftxt' == $tile_style)
        {
        ?>
        jQuery(document).ready(function() {
            if(jQuery('#tile_style_colour').val() != '')
                {
                update_tile_preview_colour('<?php echo $tile_colour; ?>');
                }
        });
        <?php
        }
    else
        {
        ?>
        jQuery(document).ready(function() {
            if(jQuery('#tile_style_<?php echo $tile_style; ?>').attr('checked'))
                {
                jQuery('#tile_style_colour_chooser').show();
                update_tile_preview_colour('<?php echo $tile_colour; ?>');
                }
        });

        jQuery('input:radio[name="tlstyle"]').change(function() {
            if(jQuery(this).attr('checked') && jQuery(this).val() == '<?php echo $tile_style; ?>')
                {
                jQuery('#tile_style_colour_chooser').show();
                }
            else
                {
                jQuery('#tile_style_colour_chooser').hide();
                jQuery('#tile_style_colour').val('');
                jQuery('#tile_style_colour').removeAttr('style');
                jQuery('#previewdashtile').removeAttr('style');
                }
        });
        <?php
        }
        ?>
    </script>
    <?php
    if('ftxt' == $tile_style)
        {
        ?>
        </div>
        <?php
        }
    else
        {
        ?>
        </span>
        <?php
        }
        ?>
    <div class="clearerleft"></div>
    <?php

    return;
    }

function get_tile_custom_style($buildstring)
    {
    if (isset($buildstring['tlstylecolour']))
        {
        $return_value="background-color: ";
        if (preg_match('/^[a-fA-F0-9]+$/',$buildstring['tlstylecolour']))
            {
            // this is a fix for supporting legacy hex values that do not have '#' at start
            $return_value.='#';
            }
        $return_value.=$buildstring['tlstylecolour'] . ';';
        return $return_value;
        }
    else
        {
        return '';
        }
    }

