<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";

$ref=getvalescaped("ref","",true);

$search=getvalescaped("search","");
$offset=getvalescaped("offset","",true);
$order_by=getvalescaped("order_by","");
$archive=getvalescaped("archive","",true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}

$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);


# Fetch resource data.
$resource=get_resource_data($ref);

# Not allowed to edit this resource?
if ((!get_edit_access($ref,$resource["archive"], false,$resource) || checkperm('A')) && $ref>0) {exit ("Permission denied.");}

hook("pageevaluation");

# Handle deleting a file
if (getval("filedelete","")!="")
	{
	delete_alternative_file($ref,getvalescaped("filedelete",""));
	}

include "../include/header.php";
?>
<div class="BasicsBox">
<p>
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoeditresource"]?></a><br / >
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoresourceview"]?></a>
</p>
	<?php if ($alternative_file_resource_preview){ 
		$imgpath=get_resource_path($resource['ref'],true,"col",false);
		if (file_exists($imgpath)){ ?><img src="<?php echo get_resource_path($resource['ref'],false,"col",false);?>"/><?php } 
	} ?>
	<?php if ($alternative_file_resource_title){ 
		echo "<h2>" . htmlspecialchars(i18n_get_translated($resource['field'.$view_title_field])) . "</h2><br/>";
	}?>
<h1><?php echo $lang["managealternativefilestitle"]?></h1>
</div>

<form method=post id="fileform" action="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>">
<input type=hidden name="filedelete" id="filedelete" value="">

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td><?php echo $lang["name"]?></td>
<td><?php echo $lang["description"]?></td>
<td><?php echo $lang["filetype"]?></td>
<td><?php echo $lang["filesize"]?></td>
<td><?php echo $lang["date"]?></td>
<?php if(count($alt_types) > 1){ ?><td><?php echo $lang["alternatetype"]?></td><?php } ?>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
$alt_order_by="";$alt_sort="";
if ($alt_types_organize){$alt_order_by="alt_type";$alt_sort="asc";} 
$files=get_alternative_files($ref,$alt_order_by,$alt_sort);
    hook("alt_files_before_list");
for ($n=0;$n<count($files);$n++)
	{
	?>
	<!--List Item-->
	<tr>
	<td><?php echo htmlspecialchars($files[$n]["name"])?></td>	
	<td><?php echo htmlspecialchars($files[$n]["description"])?>&nbsp;</td>	
	<td><?php echo ($files[$n]["file_extension"]==""?$lang["notuploaded"]:htmlspecialchars(str_replace_formatted_placeholder("%extension", $files[$n]["file_extension"], $lang["cell-fileoftype"]))); ?></td>	
	<td><?php echo formatfilesize($files[$n]["file_size"])?></td>	
	<td><?php echo nicedate($files[$n]["creation_date"],true)?></td>
	<?php if(count($alt_types) > 1){ ?><td><?php echo $files[$n]["alt_type"] ?></td><?php } ?>
	<td><div class="ListTools">
	
	<a href="#" onclick="if (confirm('<?php echo $lang["filedeleteconfirm"]?>')) {document.getElementById('filedelete').value='<?php echo $files[$n]["ref"]?>';document.getElementById('fileform').submit();} return false;"><?php echo LINK_CARET ?><?php echo $lang["action-delete"]?></a>

	&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/alternative_file.php?resource=<?php echo urlencode($ref)?>&ref=<?php echo $files[$n]["ref"]?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET ?><?php echo $lang["action-edit"]?></a>

        <?php hook("refreshinfo"); ?>
	
	</td>
	
	</tr>
	<?php
	}
?>
</table>
</div>

<p>
	<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/upload_plupload.php?alternative=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET ?><?php echo $lang["alternativebatchupload"] ?></a>
	<?php
	if($upload_methods['fetch_from_local_folder'])
		{
		?>
		<br/>
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_batch_select.php?use_local=yes&collection_add=&entercolname=&autorotate=&alternative=<?php echo urlencode($ref) ?>&uploader=local&single=&local=true&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo $sort?>&archive=<?php echo urlencode($archive)?>"><?php echo LINK_CARET ?><?php echo $lang["alternativelocalupload"] ?></a>
		<?php
		}
	?>
</p>



</form>

<?php
include "../include/footer.php";
?>
