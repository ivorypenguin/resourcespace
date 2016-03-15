<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
if(!checkPermission_dashadmin()){exit($lang["error-permissiondenied"]);}
include "../../include/dash_functions.php";



if(getvalescaped("quicksave",FALSE))
	{
	$tile = getvalescaped("tile","");
	$revokeallusers = getvalescaped("revokeallusers",false);

	#If a valid tile value supplied
	if(!empty($tile) && is_numeric($tile))
		{
		#Tile available to this user?
		$available = get_alluser_available_tiles($tile);
		if(!empty($available))
			{
			$tile = $available[0];
			$active = all_user_dash_tile_active($tile["ref"]);
			if($active)
				{
				if ($revokeallusers)
					{
					revoke_all_users_flag_cascade_delete($tile['ref']);
					}
				else
					{
					#Delete if the tile is active
					#Check config tiles for permanent deletion
					$force = false;
					$search_string = explode('?', $tile["url"]);
					parse_str(str_replace("&amp;", "&", $search_string[1]), $search_string);
					if ($search_string["tltype"] == "conf")
						{
						$force = !checkTileConfig($tile, $search_string["tlstyle"]);
						}
						delete_dash_tile($tile["ref"], true, $force);
					}
				reorder_default_dash();
				$dtiles_available = get_alluser_available_tiles();
				exit("negativeglow");
				}
			else
				{
				#Add to the front of the pile if the user already has the tile
				sql_query("DELETE FROM user_dash_tile WHERE dash_tile=".$tile["ref"]);
				sql_query("INSERT user_dash_tile (user,dash_tile,order_by) SELECT user.ref,'".$tile["ref"]."',5 FROM user");

				$dtiles_available = get_alluser_available_tiles();
				exit("positiveglow");
				}
			}
		}
	exit("Save Failed");
	}

include "../../include/header.php";
?>
<div class="BasicsBox"> 
	<h1><?php echo $lang["managedefaultdash"];?></h1>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);">
		&lt;&nbsp;<?php echo $lang["backtoteamhome"]?>
	</a>
</p>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_dash_tile.php" onClick="return CentralSpaceLoad(this,true);">
		&lt;&nbsp;<?php echo $lang["managedefaultdash"]?>
	</a>
</p>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_dash_tile_special.php" onClick="return CentralSpaceLoad(this,true);">
		&gt;&nbsp;<?php echo $lang["specialdashtiles"];?>
	</a>
</p>
	<form class="Listview">
	<input type="hidden" name="submit" value="true" />
	<table class="ListviewStyle">
		<thead>
			<tr class="ListviewTitleStyle">
				<td><?php echo $lang["dashtileshow"];?></td>
				<td><?php echo $lang["dashtiletitle"];?></td>
				<td><?php echo $lang["dashtiletext"];?></td>
				<td><?php echo $lang["dashtilelink"];?></td>
				<td><?php echo $lang["showresourcecount"];?></td>
				<td><?php echo $lang["tools"];?></td>
			</tr>
		</thead>
		<tbody id="dashtilelist">
	  	<?php
	  	$dtiles_available = get_alluser_available_tiles();
		build_dash_tile_list($dtiles_available);
	  	?>
	  </tbody>
  	</table>
  	<div id="confirm_dialog" style="display:none;text-align:left;"></div>
	</form>
	<style>
	.ListviewStyle tr.positiveglow td,.ListviewStyle tr.positiveglow:hover td{background: rgba(45, 154, 0, 0.38);}
	.ListviewStyle tr.negativeglow td,.ListviewStyle tr.negativeglow:hover td{  background: rgba(227, 73, 75, 0.38);}
	</style>
	<script type="text/javascript">
		function processTileChange(tile,revoke_all_users) {
			if(revoke_all_users === undefined) {
				revoke_all_users = false;
			}			
			jQuery.post(
				window.location,
				{"tile":tile,"quicksave":"true","revokeallusers":revoke_all_users},
				function(data){
					jQuery("#tile"+tile).removeClass("positiveglow");
					jQuery("#tile"+tile).removeClass("negativeglow");
					jQuery("#tile"+tile).addClass(data);
					window.setTimeout(function(){jQuery("#tile"+tile).removeClass(data);},2000);
				}
			);
		}
		function changeTile(tile,all_users) {
			if(!jQuery("#tile"+tile+" .tilecheck").attr("checked")) {
				jQuery("#confirm_dialog").dialog({
		        	title:'<?php echo $lang["dashtiledelete"]; ?>',
		        	modal: true,
    				resizable: false,
					dialogClass: 'confirm-dialog no-close',
                    buttons: {
						"<?php echo $lang['confirmdefaultdashtiledelete']; ?>": function() {processTileChange(tile,true); jQuery(this).dialog( "close" );CentralSpaceLoad(window.location.href);},
                        "<?php echo $lang['cancel'] ?>":  function() { jQuery(".tilecheck[value="+tile+"]").attr('checked', true); jQuery(this).dialog('close'); }
                    }
                });
			} else {
				processTileChange(tile);
			}
		}
	</script>
</div>
<?php
include "../../include/footer.php";
?>
