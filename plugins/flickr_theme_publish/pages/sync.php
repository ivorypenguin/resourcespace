<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php";
include "../../../include/search_functions.php";
include_once "../../../include/collections_functions.php";
include "../inc/flickr_functions.php";

include "../../../include/header.php";

$theme=getvalescaped("theme","");

$id="flickr_".$theme;
$progress_file=get_temp_dir(false,$id) . "/progress_file.txt";

?>
<h1><?php echo $lang["flickr_title"] ?></h1>
<?php
if($flickr_nice_progress){
	?>
	<script>
		function flickr_open_nice_progress(id,publishType){
			permission=jQuery('select[name="private"]').val();
			url='<?php echo $baseurl?>/plugins/flickr_theme_publish/pages/sync_progress.php?theme='+id+'&publish_type='+publishType+'&permission='+permission;
			window.open(url).focus();
		}
	</script>
	<?php
}

# Handle clear photo IDs
if (getval("clear_photoid","")!="")
	{
	sql_query("update resource set flickr_photo_id=null where ref in (select resource from collection_resource where collection='$theme')");
	
	}

# Handle log out
if (getval("logout","")!="")
	{
	sql_query("update user set flickr_token='',flickr_frob='' where ref='$userref'");
	}

# Does this user have a Flickr token set? If so let's try and use it.
$last_xml="";
$validtoken=flickr_check_token($userref);

if (!$validtoken){
	$validtoken=flickr_check_frob($userref);
}


if ($validtoken){
	# Valid token... we have a valid token for this user so we're ready to publish.

	if (getval("publish_all","")!="" || getval("publish_new","")!=""){
		$photoset_array=flickr_get_photoset();	
		$photoset_name=$photoset_array[0];
		$photoset=$photoset_array[1];	
	}
		
		
	if (getval("publish_all","")!=""){
		# Perform sync publishing all (updating any existing)
		sync_flickr("!collection" . $theme,false,$photoset,$photoset_name,getvalescaped("private",""));
	}
	elseif (getval("publish_new","")!=""){
		# Perform sync publishing new only.
		sync_flickr("!collection" . $theme,true,$photoset,$photoset_name,getvalescaped("private",""));
	}
	else{
		# Display option for sync
		$unpublished=sql_value("select count(*) value from resource join collection_resource on resource.ref=collection_resource.resource where collection_resource.collection='" . $theme . "' and flickr_photo_id is null",		0);
		
		# Count for all resources in selection
		$all=sql_value("select count(*) value from resource join collection_resource on resource.ref=collection_resource.resource where collection_resource.collection='" . $theme . "'",0);

		
		?>
		<form method="post" id='flickr_publish'>
	
		<!-- Public/private? -->
		<p><?php echo $lang["flickr_publish_as"] ?>
		<select name="private">
		<option value="0"><?php echo $lang["flickr-publish-public"] . "&nbsp;&nbsp;" ?></option>
		<option value="1" <?php if (getval("private","")==1) { ?>selected<?php } ?>><?php echo $lang["flickr-publish-private"] . "&nbsp;&nbsp;" ?></option>
		</select>
		</p>
		

		<p><?php echo $lang["publish_new_help"] ?></p>		
		<?php if($flickr_nice_progress){
			?><input <?php if ($unpublished==0) { ?>disabled<?php } ?> type="button" name="publish_new" id="publish_new" onclick="flickr_open_nice_progress('<?php echo $theme?>','new')" value="<?php echo ($unpublished==1 ? $lang["publish_new-1"] : str_replace("?",$unpublished,$lang["publish_new-2"])); ?>"><?php
		}
		else{
			?><input <?php if ($unpublished==0) { ?>disabled<?php } ?> type="submit" name="publish_new" id="publish_new" value="<?php echo ($unpublished==1 ? $lang["publish_new-1"] : str_replace("?",$unpublished,$lang["publish_new-2"])); ?>"><?php
		}?>



		<p>&nbsp;</p>
		<?php
		if ($all-$unpublished>0)
			{
			?>
		<p><?php echo $lang["publish_all_help"] ?></p>
		<?php if($flickr_nice_progress){
			?><input <?php if ($unpublished==0 && $all==0) { ?>disabled<?php } ?> type="button" name="publish_all" id="publish_all" onclick="flickr_open_nice_progress('<?php echo $theme?>','all')" value="<?php echo str_replace(array("$","?"),array($unpublished,$all-$unpublished),$lang["publish_all"]); ?>"><?php
		}
		else{
			?><input <?php if ($unpublished==0 && $all==0) { ?>disabled<?php } ?> type="submit" name="publish_all" id="publish_all" value="<?php echo str_replace(array("$","?"),array($unpublished,$all-$unpublished),$lang["publish_all"]); ?>"><?php
		}
			}
		?>

		<br /><br /><br /><br /><br /><hr /><h2><?php echo $lang["clear-flickr-photoids"] ?></h2>
		<p><?php echo $lang["flickr_clear_photoid_help"] ?></p>
		<input type="submit" name="clear_photoid" value="<?php echo $lang["action-clear-flickr-photoids"]; ?>">
		
		</form>
		<?php
		}
	}

include "../../../include/footer.php";

?>
