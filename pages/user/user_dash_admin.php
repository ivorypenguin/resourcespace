<?php
$pagename = "home";
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
include "../../include/dash_functions.php";

#If can't manage own dash return to user home.
if(!hook("replace_dash_admin_permission_relocate")){
	if(!($home_dash && checkPermission_dashmanage()))
		{header("location: ".$baseurl_short."pages/user/user_home.php");exit;}
	}
if(getvalescaped("quicksave",FALSE))
	{
	$tile = getvalescaped("tile","");
	#If a valid tile value supplied
	if(!empty($tile) && is_numeric($tile))
		{
		#Tile available to this user?
		$available = get_user_available_tiles($userref,$tile);
		if(!empty($available))
			{
			$tile = $available[0]["tile"];
			$usertile = $available[0]["usertile"];
			if(get_user_tile($usertile,$userref))
				{
				#Delete if the user already has the tile
				delete_user_dash_tile($usertile,$userref);
				$dtiles_available = get_user_available_tiles($userref);
				exit("negativeglow");
				}
			else
				{
				#Add to the front of the pile if the user already has the tile
				add_user_dash_tile($userref,$tile,5);
				$dtiles_available = get_user_available_tiles($userref);
				exit("positiveglow");
				}
			}
		}
	exit("Save Failed");
	}

if(getvalescaped("submit",FALSE))
	{
	$tiles = getvalescaped("tiles","");
	if(empty($tiles))
		{
		empty_user_dash($userref);
		}
	else
		{
		#Start Fresh
		empty_user_dash($userref,false);
		$order_by = 10;
		foreach($tiles as $tile)
			{
			add_user_dash_tile($userref,$tile,$order_by);
			$order_by+=10;
			}
		}
	}


include "../../include/header.php";
?>
<div class="BasicsBox"> 
	<h1><?php echo $lang["manage_own_dash"];?></h1>
	<p>
		<?php echo $lang["manageowndashinto"];?>
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
	  	$dtiles_available = get_user_available_tiles($userref);
		build_dash_tile_list($dtiles_available);
	  	?>
	  </tbody>
  	</table>
  	<div id="confirm_dialog" style="display:none;text-align:left;"><?php echo $lang["dashtiledeleteusertile"];?></div>
  	<noscript>
	  	<div class="QuestionSubmit">
	  		<input type="submit" value="<?php echo $lang["save"]?>"/>
	  	</div>
  	</noscript>
	</form>
	<style>
	.ListviewStyle tr.positiveglow td,.ListviewStyle tr.positiveglow:hover td{background: rgba(45, 154, 0, 0.38);}
	.ListviewStyle tr.negativeglow td,.ListviewStyle tr.negativeglow:hover td{  background: rgba(227, 73, 75, 0.38);}
	</style>
	<script type="text/javascript">
		function processTileChange(tile) {
			jQuery.post(
				window.location,
				{"tile":tile,"quicksave":"true"},
				function(data){
					jQuery("#tile"+tile).removeClass("positiveglow");
					jQuery("#tile"+tile).removeClass("negativeglow");
					jQuery("#tile"+tile).addClass(data);
					window.setTimeout(function(){jQuery("#tile"+tile).removeClass(data);},2000);
				}
			);
		}
		function changeTile(tile,all_users) {
			if(all_users==0) {
				jQuery("#confirm_dialog").dialog({
		        	title:'<?php echo $lang["dashtiledelete"]; ?>',
		        	modal: true,
    				resizable: false,
					dialogClass: 'confirm-dialog no-close',
                    buttons: {
                        "<?php echo $lang['confirmdashtiledelete'] ?>": function() {processTileChange(tile); jQuery(this).dialog( "close" );CentralSpaceLoad(window.location.href);},
                        "<?php echo $lang['cancel'] ?>":  function() { jQuery(".tilecheck[value="+tile+"]").attr('checked', true); jQuery(this).dialog('close'); }
                    }
                });
			} else {
				processTileChange(tile);
			}
		}
	</script>
	<div>
		<?php
		# Create New Tile (Has dtu or dta (hdta) permissions)
		if($home_dash && checkPermission_dashcreate())
			{ ?>
			<p>
				<a href="<?php echo $baseurl."/pages/dash_tile.php?create=true&tltype=ftxt&modifylink=true&freetext=Helpful%20tips%20here&nostyleoptions=true&all_users=0&link=http://resourcespace.org/knowledge-base/&title=Knowledge%20Base";?>">&gt;&nbsp; <?php echo $lang["createdashtilefreetext"]?></a>
			</p>
			<?php
			} 
		hook('after_dash_admin_create_new_tile');
		?>
	</div>
</div>

<?php
include "../../include/footer.php";
