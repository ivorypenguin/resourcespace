<?php
include "../include/db.php";
include "../include/authenticate.php"; #if (!checkperm("s")) {exit ("Permission denied.");}
include "../include/general.php";
include "../include/collections_functions.php";
include "../include/resource_functions.php";
include "../include/search_functions.php"; 

$ref=getvalescaped("ref","",true);
$copycollectionremoveall=getvalescaped("copycollectionremoveall","");
$offset=getval("offset",0);
$find=getvalescaped("find","");
$col_order_by=getvalescaped("col_order_by","name");
$sort=getval("sort","ASC");

# Does this user have edit access to collections? Variable will be found in functions below.  
$multi_edit=allow_multi_edit($ref);


# Fetch collection data
$collection=get_collection($ref);if ($collection===false) {
	$error=$lang['error-collectionnotfound'];
	error_alert($error);
	exit();
	}
$resources=do_search("!collection".$ref);
$colcount=count($resources);

# Collection copy functionality
$copy=getval("copy","");
if ($copy!="")
	{
	copy_collection($copy,$ref,$copycollectionremoveall!="");
	refresh_collection_frame();
	}

if (getval("submitted","")!="")
	{
	# Save collection data
	save_collection($ref);
	if (getval("redirect","")!="")
		{
		if (getval("addlevel","")=="yes"){
			redirect ($baseurl_short."pages/collection_edit.php?ref=".$ref."&addlevel=yes");
			}		
		else if ((getval("theme","")!="") || (getval("newtheme","")!=""))
			{
			redirect ($baseurl_short."pages/themes.php?manage=true");
			}
		else
			{
			redirect ($baseurl_short."pages/collection_manage.php?offset=".$offset."&col_order_by=".$col_order_by."&sort=".$sort."&find=".urlencode($find)."&reload=true");
			}
		}
	else
		{
		# No redirect, we stay on this page. Reload the collection info.
		$collection=get_collection($ref);
		}
	}

	
include "../include/header.php";
?>
<div class="BasicsBox">
<h1><?php echo $lang["editcollection"]?></h1>
<p><?php echo text("introtext")?></p>
<form method=post id="collectionform" action="<?php echo $baseurl_short?>pages/collection_edit.php" onSubmit="if (jQuery('#usercollection').html()!='<?php echo htmlspecialchars($ref) ?>'){return CentralSpacePost(this,true);}">
<input type=hidden name=redirect id=redirect value=yes>
<input type=hidden name=ref value="<?php echo htmlspecialchars($ref) ?>">
<input type=hidden name="submitted" value="true">

<div class="Question">
<label for="name"><?php echo $lang["name"]?></label><input type=text class="stdwidth" name="name" id="name" value="<?php echo htmlspecialchars($collection["name"]) ?>" maxlength="100" <?php if ($collection["cant_delete"]==1) { ?>readonly=true<?php } ?>>
<div class="clearerleft"> </div>
</div>

<?php hook('additionalfields');?>

<div class="Question">
<label for="keywords"><?php echo $lang["relatedkeywords"]?></label><textarea class="stdwidth" rows="3" name="keywords" id="keywords" <?php if ($collection["cant_delete"]==1) { ?>readonly=true<?php } ?>><?php echo htmlspecialchars($collection["keywords"])?></textarea>
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label><?php echo $lang["id"]?></label><div class="Fixed"><?php echo htmlspecialchars($collection["ref"]) ?></div>
<div class="clearerleft"> </div>
</div>

<?php if ($collection["savedsearch"]!="") { 
$result_limit=sql_value("select result_limit value from collection_savedsearch where collection='$ref'","");	
?>
<div class="Question">
<label for="name"><?php echo $lang["smart_collection_result_limit"] ?></label><input type=text class="stdwidth" name="result_limit" id="result_limit" value="<?php echo htmlspecialchars($result_limit) ?>" />
<div class="clearerleft"> </div>
</div>
<?php } ?>



<div class="Question">
<label for="public"><?php echo $lang["access"]?></label>
<?php if ($collection["cant_delete"]==1) { 
# This is a user's My Collection, which cannot be made public or turned into a theme. Display a warning.
?>
<input type="hidden" id="public" name="public" value="0">
<div class="Fixed"><?php echo $lang["mycollection_notpublic"] ?></div>
<?php } else { ?>
<select id="public" name="public" class="shrtwidth" onchange="document.getElementById('redirect').value='';document.getElementById('collectionform').submit();">
<option value="0" <?php if ($collection["public"]!=1) {?>selected<?php } ?>><?php echo $lang["private"]?></option>
<?php if ($collection["cant_delete"]!=1 && ($enable_public_collections || checkperm("h"))) { ?><option value="1" <?php if ($collection["public"]==1) {?>selected<?php } ?>><?php echo $lang["public"]?></option><?php } ?>
</select>
<?php } ?>
<div class="clearerleft"> </div>
</div>

<?php if ($collection["public"]==0 || ( ($collection['public']==1 && !$themes_in_my_collections && $collection['theme']=='') || ($collection['public']==1 && $themes_in_my_collections) )) { ?>
<?php if (!hook("replaceuserselect")){?>
<div class="Question">
<label for="users"><?php echo $lang["attachedusers"]?></label><?php $userstring=htmlspecialchars($collection["users"]); include "../include/user_select.php"; ?>
<div class="clearerleft"> </div>
</div>
<?php } /* end hook replaceuserselect */?>

<?php } 

if ($collection['public']==1){ 
	
//////////////////////////
// find current number of themes used
$themecount=1;
foreach($collection as $key=>$value){
	if (substr($key,0,5)=="theme"){
		if ($value==""){break 1;} 
		else{
			if (substr($key,5)==""){
				$themecount=1;				
				$orig_themecount=$themecount;
				}
			else{
				$themecount=substr($key,5);
				$orig_themecount=$themecount;
				}
		}
	}
}
#echo "<br/>Current theme level:".$themecount;

// find number of theme columns
foreach($collection as $key=>$value){
	if (substr($key,0,5)=="theme"){
		$themecolumns=substr($key,5);
	}
}		
//echo "<br/>Theme levels available:".$themecolumns;	

if(!hook("overridethemesel")):	
if (checkperm("h") && $enable_themes) { # Only users with the 'h' permission can publish public collections as themes.


?>
<input type=hidden name="addlevel" id="addlevel" value=""/>
<?php

if (getval("addlevel","")=="yes"){$themecount++;}
$lastselected=false;
# Theme category levels
for ($i=1;$i<=$themecount;$i++){
if ($theme_category_levels>=$i)
	{
	if ($i==1){$themeindex="";}else{$themeindex=$i;}	

	$themearray=array();
	for($y=0;$y<$i-1;$y++){
		if ($y==0){
				$themearray[]=$collection["theme"];
			}
			else {
				$themearray[]=$collection["theme".($y+1)];
			}
	}	
	$themes=get_theme_headers($themearray);
	?>
	<div class="Question">
	<label for="theme<?php echo $themeindex?>"><?php echo $lang["themecategory"] . " ".$themeindex ?></label>
	<?php if (count($themes)>0){?><select class="stdwidth" name="theme<?php echo $themeindex?>" id="theme<?php echo $themeindex?>" <?php if ($theme_category_levels>=$themeindex) { ?>onchange="if (document.getElementById('theme<?php echo $themeindex?>').value!=='') {document.getElementById('addlevel').value='yes'; return CentralSpacePost(jQuery('#collectionform')[0])} else {document.getElementById('redirect').value='';return CentralSpacePost(jQuery('#collectionform')[0])}"<?php } ?>><option value=""><?php echo $lang["select"]?></option>
	<?php 
	$lastselected=false;
	for ($n=0;$n<count($themes);$n++) { ?>
	<option <?php if ($collection["theme".$themeindex]==$themes[$n]) { ?>selected<?php } ?>><?php echo htmlspecialchars($themes[$n]) ?></option>
	<?php if ($collection["theme".$themeindex]==$themes[$n] && $i==$orig_themecount){$lastselected=true;} ?>
	<?php } ?>
	</select>
	<?php if (getval("addlevel","")!="yes" && $lastselected){$themecount++;}?>
	<div class="clearerleft"> </div>
	<label><?php echo $lang["newcategoryname"]?></label>
		<?php } //end conditional selector?>
	<input type=text class="medwidth" name="newtheme<?php echo $themeindex?>" id="newtheme<?php echo $themeindex?>" value="" maxlength="100">
	<?php if ($themecount!=1){?>
	<input type=button class="medcomplementwidth" value="<?php echo $lang['save'];?>" style="display:inline;" onclick="document.getElementById('addlevel').value='yes';return CentralSpacePost(jQuery('#collectionform')[0])"/>	
	<?php } ?>
	<?php if ($themecount==1){?>
	<input type=button class="medcomplementwidth" value="<?php echo $lang['add'];?>" style="display:inline;" onclick="if (document.getElementById('newtheme<?php echo $themeindex?>').value==''){alert('<?php echo $lang["collectionsnothemeselected"] ?>');return false;}document.getElementById('addlevel').value='yes';return CentralSpacePost(jQuery('#collectionform')[0])"/><?php }?>
	<div class="clearerleft"> </div>
	</div>
	<?php
	}
}

} else {
	// in case a user can edit collections but doesn't have themes enabled, preserve them
	for ($i=1;$i<=$themecount;$i++){
		if ($theme_category_levels>=$i)	{
			if ($i==1){$themeindex="";}else{$themeindex=$i;}	
			?>
			<input type=hidden name="theme<?php echo $themeindex?>" value="<?php echo htmlspecialchars($collection["theme".$themeindex]) ?>">
			<?php
		}
	}	
}
endif; // hook: overridethemesel 
}


if (checkperm("h") && $collection['public']==1)
	{
	# Option to publish to the home page.
	?>
	<div class="Question">
	<label for="allow_changes"><?php echo $lang["theme_home_promote"]?></label><input type=checkbox id="home_page_publish" name="home_page_publish" value="1" <?php if ($collection["home_page_publish"]==1) { ?>checked<?php } ?> onClick="document.getElementById('redirect').value='';document.getElementById('collectionform').submit();">
	<div class="clearerleft"> </div>
	</div>
	<?php
	if ($collection["home_page_publish"]&&!hook("hidehomepagepublishoptions"))
		{
		# Option ticked - collect extra data
		?>
		<div class="Question">
		<label for="home_page_text"><?php echo $lang["theme_home_page_text"]?></label><textarea class="stdwidth" rows="3" name="home_page_text" id="home_page_text"><?php echo htmlspecialchars($collection["home_page_text"]==""?$collection["name"]:$collection["home_page_text"])?></textarea>
		<div class="clearerleft"> </div>
		</div>
		<div class="Question">
		<label for="home_page_image">
		<?php echo $lang["theme_home_page_image"]?></label>
		
		<select class="stdwidth" name="home_page_image" id="home_page_image">
		<?php foreach ($resources as $resource)
			{
			?>
			<option value="<?php echo htmlspecialchars($resource["ref"]) ?>" <?php if ($resource["ref"]==$collection["home_page_image"]) { ?>selected<?php } ?>><?php echo str_replace(array("%ref", "%title"), array($resource["ref"], i18n_get_translated($resource["field" . $view_title_field])), $lang["ref-title"]) ?></option>
			<?php
			}
		?>
		</select>
		
		
		<div class="clearerleft"> </div>
		</div>		
		<?php
		}
	}
?>


<?php if (isset($collection['savedsearch'])&&$collection['savedsearch']==null){
	# disallowing share breaks smart collections 
	?>
<div class="Question">
<label for="allow_changes"><?php echo $lang["allowothersaddremove"]?></label><input type=checkbox id="allow_changes" name="allow_changes" <?php if ($collection["allow_changes"]==1) { ?>checked<?php } ?>>
<div class="clearerleft"> </div>
</div>
<?php } else { 
	# allow changes by default
	?><input type=hidden id="allow_changes" name="allow_changes" value="checked">
<?php } ?>

<?php if ($multi_edit && $colcount>1) { ?>
<div class="Question">
<label for="allow_changes"><?php echo $lang["relateallresources"]?></label><input type=checkbox id="relateall" name="relateall">
<div class="clearerleft"> </div>
</div><?php } ?>

<?php if (!$collections_compact_style && $colcount!=0 && $collection['savedsearch']==''){?>
<div class="Question">
<label for="removeall"><?php echo $lang["removeallresourcesfromcollection"]?></label><input type=checkbox id="removeall" name="removeall">
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php if (!$collections_compact_style && $multi_edit && !checkperm("D") && $colcount!=0) { ?>
<div class="Question">
<label for="deleteall"><?php echo $lang["deleteallresourcesfromcollection"]?></label><input type=checkbox id="deleteall" name="deleteall" onClick="if (this.checked) {return confirm('<?php echo $lang["deleteallsure"]?>');}">
<div class="clearerleft"> </div>
</div><?php } ?>

<?php
if ($enable_collection_copy) 
	{
	?>
	<div class="Question">
	<label for="copy"><?php echo $lang["copyfromcollection"]?></label>
	<select name="copy" id="copy" class="stdwidth" onChange="
	var ccra =document.getElementById('copycollectionremoveall');
	if (jQuery('#copy').val()!=''){ccra.style.display='block';}
	else{ccra.style.display='none';}">
	<option value=""><?php echo $lang["donotcopycollection"]?></option>
	<?php
	$list=get_user_collections($userref);
	for ($n=0;$n<count($list);$n++)
		{
		if ($ref!=$list[$n]["ref"]){?><option value="<?php echo htmlspecialchars($list[$n]["ref"]) ?>"><?php echo htmlspecialchars($list[$n]["name"])?></option> <?php }
		}
	?>
	</select>
	<div class="clearerleft"> </div>
	</div>
<div class="Question" id="copycollectionremoveall" style="display:none;">
<label for="copycollectionremoveall"><?php echo $lang["copycollectionremoveall"]?></label><input type=checkbox id="copycollectionremoveall" name="copycollectionremoveall" value="yes">
<div class="clearerleft"> </div>
</div>

<?php } ?>



<div class="Question">
<label><?php echo $lang["collectionlog"]?></label>
<div class="Fixed">
<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_log.php?ref=<?php echo urlencode($ref) ?>"><?php echo $lang["log"]?> &gt;</a>
</div>
<div class="clearerleft"> </div>
</div>

<?php hook('colleditformbottom');?>

<?php if (file_exists("plugins/collection_edit.php")) { include "plugins/collection_edit.php"; } ?>

<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
</div>
</form>
</div>

<?php		
include "../include/footer.php";
?>
