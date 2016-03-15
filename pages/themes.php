<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include_once "../include/collections_functions.php";
include_once "../include/resource_functions.php";
include_once "../include/render_functions.php";
include_once "../include/search_functions.php";

global $default_perpage_list;
$themes_order_by=getvalescaped("themes_order_by",getvalescaped("saved_themes_order_by","name"));rs_setcookie('saved_themes_order_by', $themes_order_by);
$sort=getvalescaped("sort",getvalescaped("saved_themes_sort","ASC"));rs_setcookie('saved_themes_sort', $sort);
$per_page=getvalescaped("per_page_list",$default_perpage_list,true);rs_setcookie('per_page_list', $per_page);
$simpleview=$themes_simple_view || getval("simpleview","")=="true";

hook("themeheader");

if (!function_exists("DisplayTheme")){
function DisplayTheme($themes=array(), $simpleview=false)
	{
	if($simpleview)
		{
		global $baseurl_short;
		$getthemes=get_themes($themes);
		# Themes
		for ($m=0;$m<count($getthemes);$m++)
			{		
			?>				
			<a href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $getthemes[$m]["ref"]?>" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleLink" id="advertising_tile_<?php echo $getthemes[$m]["ref"]; ?>">
			<div class="FeaturedSimpleTile">
				<div id="FeaturedSimpleTileContents_<?php echo $getthemes[$m]["ref"] ; ?>"  class="HomePanelIN FeaturedSimpleTileContents" >	
					<h2><?php echo i18n_get_collection_name($getthemes[$m])?></h2>
				</div>			
			</div>			
			</a>			
			<?php
			}
		
		
		}
	else
		{
		global $theme_direct_jump,$themes_column_sorting,$themes_ref_column,$themes_date_column,$baseurl_short,$baseurl,
			   $default_perpage_list,$collection_prefix,$revsort,$sort,$find,$getthemes,$m,$lang,$flag_new_themes,
			   $contact_sheet,$theme_images,$allow_share,$zipcommand,$collection_download,$theme_images_align_right,
			   $themes_category_split_pages,$themes_category_split_pages_parents,$collections_compact_style,$pagename,
			   $show_edit_all_link,$preview_all,$userref,$collection_purge,$themes_category_split_pages,
			   $themes_category_split_pages_parents_root_node,$enable_theme_category_sharing,$enable_theme_category_edit,
			   $show_theme_collection_stats,$lastlevelchange,$themes_single_collection_shortcut, $download_usage;
	
		$themes_order_by=getvalescaped("themes_order_by",getvalescaped("saved_themes_order_by","name"));
		$sort=getvalescaped("sort",getvalescaped("saved_themes_sort","ASC"));	
		$revsort = ($sort=="ASC") ? "DESC" : "ASC";
		# pager
		$per_page=getvalescaped("per_page_list",$default_perpage_list,true);
	
		$collection_valid_order_bys=array("name","c");
	
		// sorting doesn't work for nonsplit
		if (!$themes_column_sorting || !$themes_category_split_pages || $theme_direct_jump){$sort="ASC";$themes_order_by="name";$themes_column_sorting=false;}
	
		if ($themes_ref_column){$collection_valid_order_bys[]="ref";}
		if ($themes_date_column){$collection_valid_order_bys[]="created";}
		
		$modified_collection_valid_order_bys=hook("modifycollectionvalidorderbys");
		if ($modified_collection_valid_order_bys){$collection_valid_order_bys=$modified_collection_valid_order_bys;}
		if (!in_array($themes_order_by,$collection_valid_order_bys)) {$sort="ASC";$themes_order_by="name";} # Check the value is one of the valid values (SQL injection filter)
	
		# Work out theme name
		$themecount=count($themes);
		for ($x=0;$x<$themecount;$x++)
			{
			if (isset($themes[$x])&&!isset($themes[$x+1]))
				$themename=i18n_get_translated($themes[$x]);
			}
	
		$getthemes=get_themes($themes);
	
		$tmp = hook("getthemesdisp", "", array($themes)); if($tmp!==false) $getthemes = $tmp;
		
		if ((!$themes_single_collection_shortcut && count($getthemes)>0) || ($themes_single_collection_shortcut && count($getthemes)>1))
			{
			?>
			<div class="RecordBox">
			<div class="RecordPanel">
	
			<div class="RecordHeader">
	
			<?php
			if ($themes_category_split_pages && $themes_category_split_pages_parents){?><h1><?php
			echo $lang["collections"];?></h1><?php }
	
			// count total items in themes
			$totalcount=0;
			for ($m=0;$m<count($getthemes);$m++)
				{$totalcount=$totalcount+$getthemes[$m]['c'];
			}
	
			if ($theme_images_align_right)
				{
				?>
				<div style="float:right;">
				<?php
				}
	
			$images=get_theme_image($themes);
			
			$modified_images=hook("modify_theme_images",'',array($themes));
			if(!empty($modified_images)){
				$images=$modified_images;
			}
			if (($images!==false) && ($theme_images))
				{
				for ($n=0;$n<count($images);$n++)
					{
					?><div style="float:left;margin-right:12px;"><img class="CollectImageBorder" src="<?php echo get_resource_path($images[$n],false,"col",false) ?>" /></div>
					<?php
					}
				}
			if ($theme_images_align_right)
				{
				?>
				</div>
				<?php
				}
			$themeslinks="";
			for ($x=0;$x<count($themes);$x++){
				$themeslinks.="theme".($x+1)."=".urlencode($themes[$x])."&";
			}
			?>
			<table><tr><td style="margin:0px;padding:0px;">
		<h1 ><?php if (($themes_category_split_pages && $themes_category_split_pages_parents) && !$theme_direct_jump)
				{
				if ($themes_category_split_pages_parents_root_node){?><a href="<?php echo $baseurl_short?>pages/themes.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["themes"];?></a> / <?php } 
				$themescrumbs="";
				for ($x=0;$x<count($themes);$x++){
					$themescrumbs.="theme".($x+1)."=".urlencode($themes[$x])."&";
					?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themescrumbs?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo htmlspecialchars(i18n_get_translated($themes[$x]))?></a> / <?php
					}
				}
			else
				{
				echo stripslashes(str_replace("*","",$themename));
				}?></h1></td></tr><tr><td style="margin:0px;padding:0px;">
				
				<?php
				if (($show_theme_collection_stats) || (!($themes_category_split_pages) && ($enable_theme_category_sharing || $enable_theme_category_edit)))
					{
					$linkparams="";
					for ($x=0;$x<count($themes);$x++){
					$linkparams.="theme".($x+1)."=".urlencode($themes[$x])."&";
					}
					if($show_theme_collection_stats)
						{
						?>
						<p style="clear:none;"><?php $collcount = count($getthemes); echo $collcount==1 ? $lang["collections-1"] : sprintf(str_replace("%number","%d",$lang["collections-2"]),$collcount,$totalcount); hook("themeactioninline");
						?>
						</p>
						</td><td style="margin:0px;padding:0px;">
						<?php
						}
						?>
					<?php
						if(!($themes_category_split_pages))
						{
						if (checkperm("h") && $enable_theme_category_sharing)
							{
							$sharelink="";
							for ($x=0;$x<count($themes);$x++)
								{
								$sharelink.="theme".($x+1)."=" . urlencode($themes[$x]) ."&";					
								}
							?>
							
							</td><tr><td style="margin:0px;padding:0px;">
							<a href="<?php echo $baseurl_short?>pages/theme_category_share.php?<?php echo $linkparams?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo "> " . $lang["share"] . "</a>";
							}
						hook("themeaction");
						
						if ($enable_theme_category_edit && checkperm("t"))
							{
							?>
							<a href="<?php echo $baseurl_short?>pages/theme_edit.php?<?php echo $linkparams . "lastlevelchange=" . $lastlevelchange?>" onClick="return CentralSpaceLoad(this,true);"><?php echo "> " . $lang["action-edit"] . "</a>";
							}
						}
					}
					?>
				
				</td></tr></table>
				<!-- The number of collections should never be equal to zero. -->
	
			<div class="clearerright"> </div>
			</div>
			<?php hook("beforethemelist");?>
			<br />
			<div class="Listview" style="margin-top:10px;margin-bottom:5px;clear:left;">
			<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
			<tr class="ListviewBoxedTitleStyle">
			<td class="name"><?php if ($themes_order_by=="name") {?><span class="Selected"><?php } if($themes_column_sorting) { ?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themeslinks?>themes_order_by=name&sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php } ?><?php echo $lang["collectionname"]?><?php  if($themes_category_split_pages) { ?></a><?php } ?><?php if ($themes_order_by=="name") {?><div class="<?php echo htmlspecialchars($sort)?>">&nbsp;</div><?php } ?></td>
			<?php if ($themes_ref_column){?>
			<td class="ref"><?php if ($themes_order_by=="ref") {?><span class="Selected"><?php } if($themes_column_sorting) { ?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themeslinks?>themes_order_by=ref&sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php } ?><?php echo $lang["id"]?><?php  if($themes_category_split_pages) { ?></a><?php } ?><?php if ($themes_order_by=="ref") {?><div class="<?php echo htmlspecialchars($sort)?>">&nbsp;</div><?php } ?></td>
			<?php } ?>
			<?php if ($themes_date_column){?>
			<td class="created"><?php if ($themes_order_by=="created") {?><span class="Selected"><?php } if($themes_column_sorting) { ?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themeslinks?>themes_order_by=created&sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php } ?><?php echo $lang["created"]?><?php  if($themes_category_split_pages) { ?></a><?php } ?><?php if ($themes_order_by=="created") {?><div class="<?php echo htmlspecialchars($sort)?>">&nbsp;</div><?php } ?></td>
			<?php } ?>
			<td class="count"><?php if ($themes_order_by=="c") {?><span class="Selected"><?php } if($themes_column_sorting) { ?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themeslinks?>themes_order_by=c&sort=<?php echo urlencode($revsort)?>" onClick="return CentralSpaceLoad(this);"><?php } ?><?php echo $lang["itemstitle"]?><?php  if($themes_category_split_pages) { ?></a><?php } ?><?php if ($themes_order_by=="c") {?><div class="<?php echo htmlspecialchars($sort)?>">&nbsp;</div><?php } ?></td>
			<?php hook("beforecollectiontoolscolumnheader","themes",array($themeslinks));?>
			<td class="tools"><div class="ListTools"><?php echo $lang["tools"]?></div></td>
			</tr>
	
			<?php
			for ($m=0;$m<count($getthemes);$m++)
				{
				?>
				<tr <?php hook("collectionlistrowstyle");?>>
				<td class="name" width="50%"><div class="ListTitle"><a href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $getthemes[$m]["ref"]?>&bc_from=themes"  title="<?php echo $lang["collectionviewhover"]?>" onClick="return CentralSpaceLoad(this,true);"><?php echo i18n_get_collection_name($getthemes[$m])?></a>
				<?php if ($flag_new_themes && (time()-strtotime($getthemes[$m]["created"]))<(60*60*24*14)) { ?><div class="NewFlag"><?php echo $lang["newflag"]?></div><?php } ?>
				</div></td>
				<?php if ($themes_ref_column){?>
				<td class="ref"><?php echo $getthemes[$m]["ref"];?></td>
				<?php } ?>
				<?php if ($themes_date_column){?>
				<td class="created"><?php echo nicedate($getthemes[$m]["created"],true)?></td>
				<?php } ?>
				<td class="count" width="5%"><?php echo $getthemes[$m]["c"]?></td>
				<?php hook('beforecollectiontoolscolumn'); ?>
				<td class="tools" nowrap>
					<div class="ListTools">
					<?php
					hook('render_themes_list_tools', '', array($getthemes[$m]));
					render_actions($getthemes[$m], false, false);
					?>
					</div>
				</td>
				</tr>
				<?php
				}
			?>
			</table>
			</div>
	
			</div>
			<div class="PanelShadow"> </div>
			</div>
			<?php
			}
		}
	}
}


$themes=array();
$themecount=0;
foreach ($_GET as $key => $value) {
	// only set necessary vars
	
	if (substr($key,0,5)=="theme" && substr($key,0,6)!="themes"){		
		if (empty($value)) break;	# if the value is empty then there is no point in continuing iterations of the loop
		$themes[$themecount]=rawurldecode($value);
		$themecount++;
		}
	}

$header=getvalescaped("header","");
$smart_theme=getvalescaped("smart_theme","");

# When changing higher levels, deselect the lower levels.
$lastlevelchange=getvalescaped("lastlevelchange",1);
if(!is_numeric($lastlevelchange)) {$lastlevelchange = 1;}

for ($n=$lastlevelchange;$n<=$themecount;$n++){
	if ($n>$lastlevelchange && !$themes_category_split_pages){
	$themes[$n-1]="";
	}
}

//if ($lastlevelchange=="1") {$theme2="";$theme3="";}
//if ($lastlevelchange=="2") {$theme3="";}
include "../include/header.php";
?>


<?php
if(!$simpleview)
	{?>
	<div class="BasicsBox">
	<form method=get id="themeform" action="<?php echo $baseurl_short?>pages/themes.php">
	<input type="hidden" name="lastlevelchange" id="lastlevelchange" value="">
	<?php
	}
else
	{
	?>
	<div class="BasicsBox FeaturedSimpleLinks">
	<?php
	}


if (!$themes_category_split_pages && !$theme_direct_jump) { ?>
  <h1><?php echo htmlspecialchars(getval("title",$lang["themes"]),ENT_QUOTES)?></h1>
  <p><?php echo text("introtext")?></p>
<?php } ?>

<?php if ($theme_direct_jump)
	{
	# Display title and description when 'direct jump' mode is enabled.
	$text=text("introtext");
	$title=htmlspecialchars(getval("title",$lang["themes"]),ENT_QUOTES);
	if (count($themes)>0)
		{
		$title=i18n_get_translated($themes[count($themes)-1]);
		$text=text("introtext" . $themes[count($themes)-1]);
		if ($text=="") {$text=text("introtext");}
		}
	?>
  <h1><?php echo $title ?></h1>
  <p><?php echo $text ?></p>
<?php } ?>


  <style>.ListviewTitleBoxed {background-color:#fff;}</style>

<?php
if(!hook('replacethemesbacklink')){
if ($themes_category_split_pages && isset($themes[0]) && !$theme_direct_jump)
	{
	echo "<div class='SearchBreadcrumbs'>";
	# Display breadcrumb links
	$link=$baseurl_short."pages/themes.php?";
	?>
	<a href="<?php echo $link ?>" onClick="return CentralSpaceLoad(this,true);">
		<span><?php echo $lang["themes"] ?></span>
	</a>
	<?php			
	for ($x=0;$x<count($themes);$x++)
		{
		//print_r($themes);	
		if ($x!=0){ $link.="&"; }
		$link.="theme";
		$link.=($x==0)?"":$x;
		$link.="=". urlencode($themes[$x]);
		if($simpleview)
		{$link.="&simpleview=true";}
		echo "&gt;&nbsp;";
		?><a href="<?php echo $link ?>" onClick="return CentralSpaceLoad(this,true);"><span><?php echo htmlspecialchars($themes[$x]) ?>&nbsp;</span></a><?php
		}
	echo "</div>";

}
} # end hook('replacethemesbacklink')

#if ($themes_category_split_pages && $theme1=="" && $smart_theme=="")
if ($smart_theme!="")
	{
	}
elseif ($themes_category_split_pages && !$theme_direct_jump)
	{
	# --------------- Split theme categories on to separate pages -------------------
	#
	# This option shows the theme categories / subcategories as a simple list, instead of using dropdown boxes.
	#

	?>
	<?php
	if (count($themes)<$theme_category_levels){
	$headers=get_theme_headers($themes);
	if (count($headers)>0)
		{
		if($simpleview)
			{
			# Theme headers
			for ($n=0;$n<count($headers);$n++)
				{
				$link=$baseurl_short."pages/themes.php?theme1=" . urlencode((!isset($themes[0]))? $headers[$n]:$themes[0]) . "&simpleview=true";
				for ($x=2;$x<count($themes)+2;$x++)
					{
					if (isset($headers[$n]))
						{
						$link.="&theme".$x."=" . urlencode((!isset($themes[$x-1]))? ((!isset($themes[$x-2]))?"":$headers[$n]):$themes[$x-1]);
						}
					}
					?>		
				<a href="<?php echo $link; ?>" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleLink" id="advertising_tile_<?php echo $headers[$n];?>">
					<div class="FeaturedSimpleTile">
						<div id="FeaturedSimpleTileContents_<?php echo $headers[$n] ; ?>"  class="HomePanelIN FeaturedSimpleTileContents" >	
							<h2><?php echo htmlspecialchars(i18n_get_translated(str_replace("*","",$headers[$n])))?></h2>
						</div>			
					</div>			
				</a>				
				<?php	
				}
			}
		else			
			{?>
			<div class="RecordBox">
			<div class="RecordPanel">
	
			<div class="RecordHeader">
			<h1 style="margin-top:5px;"><?php
			if (!isset($themes[0])){
				echo $lang["themes"];
				}
			else{
				if ($themes_category_split_pages_parents){
					$themeslinks="";
					echo (count($headers)>1)?$lang["subcategories"]:$lang["subcategory"];?></h1><h1 style="margin-top:5px;"><?php if ($themes_category_split_pages_parents_root_node){?><a href="<?php echo $baseurl_short?>pages/themes.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["themes"];?></a> / <?php } ?><?php
					for ($x=0;$x<count($themes);$x++){
						$themeslinks.="theme".($x+1)."=".urlencode($themes[$x])."&";
						?><a href="<?php echo $baseurl_short?>pages/themes.php?<?php echo $themeslinks?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo htmlspecialchars(i18n_get_translated($themes[$x]))?></a> / <?php
						}
				}
				else {
					echo $lang["subcategories"];
				}
			}?></h1>
			<?php hook("beforethemeheaderlist");?>
			</div>
	
			<div class="Listview" style="margin-top:10px;margin-bottom:10px;clear:left;">
			<table  id="themeheaders" border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
			<tr class="ListviewBoxedTitleStyle">
			<td><?php echo $lang["name"]?></td>
			<td><div class="ListTools"><?php if (!hook("replacethemetoolsheader")){?><?php echo $lang["tools"]?><?php } ?></div></td>
			</tr>
			<?php
	
			# Theme headers
			for ($n=0;$n<count($headers);$n++)
				{
				$link=$baseurl_short."pages/themes.php?theme1=" . urlencode((!isset($themes[0]))? $headers[$n]:$themes[0]);
				$linklang=$lang['action-select'];
				
				$headerlink=$link;
				
				if ($themes_single_collection_shortcut){
					// go ahead and get the collections for this theme header
					$getthemes=get_themes(array_merge($themes,array($headers[$n])));
	
					// if there is only one collection, make the header link directly to the collection
					if (count($getthemes)==1){$headerlink=$baseurl_short."pages/search.php?search=!collection".$getthemes[0]['ref']."&bc_from=themes";}
	
					// check if there are any subthemes under this header, and if not, make the >Select tool a direct jump as well. 
					// Otherwise, use the Expand lang.
					$headercheck=get_theme_headers(array_merge($themes,array($headers[$n])));
					if (count($headercheck)==0)
						{
						// no headers on the next level,
						} else {
						// there are further headers, use Expand instead of Select
						$linklang=$lang['action-expand'];
					}
				}
	
	
				$editlink=$baseurl_short."pages/theme_edit.php?theme1=" . urlencode((!isset($themes[0]))? $headers[$n]:$themes[0]);
				$sharelink=$baseurl_short."pages/theme_category_share.php?theme1=" . urlencode((!isset($themes[0]))? $headers[$n]:$themes[0]);
				for ($x=2;$x<count($themes)+2;$x++){
					if (isset($headers[$n])){
						$link.="&theme".$x."=" . urlencode((!isset($themes[$x-1]))? ((!isset($themes[$x-2]))?"":$headers[$n]):$themes[$x-1]);
						$headerlink.="&theme".$x."=" . urlencode((!isset($themes[$x-1]))? ((!isset($themes[$x-2]))?"":$headers[$n]):$themes[$x-1]);
						$editlink.="&theme".$x."=" . urlencode((!isset($themes[$x-1]))? ((!isset($themes[$x-2]))?"":$headers[$n]):$themes[$x-1]);
						$sharelink.="&theme".$x."=" . urlencode((!isset($themes[$x-1]))? ((!isset($themes[$x-2]))?"":$headers[$n]):$themes[$x-1]);
					}
				}?>
				<tr>
				<td><div class="ListTitle"><a href="<?php echo $headerlink ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo htmlspecialchars(i18n_get_translated(str_replace("*","",$headers[$n])))?></a><?php hook('addthemeheadertoolaftername')?></div></td>
				<td><div class="ListTools"><?php hook('addthemeheadertool')?><?php if (!hook("replacethemeselectlink")){?><a href="<?php echo $link ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $linklang;?></a><?php } 
				if (checkperm("h") && $enable_theme_category_sharing) {?>&nbsp;&nbsp;<a href="<?php echo $sharelink ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["share"]?></a><?php }
				if ($enable_theme_category_edit && checkperm("t")) {?>&nbsp;&nbsp;<a href="<?php echo $editlink ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-edit"]?></a><?php }
				hook("addcustomtoolsplitpage");
				?>
				</div></td>
				</tr>
				<?php
				}
	
			# Smart theme headers
			/*
			$headers=get_smart_theme_headers($themes);
			for ($n=0;$n<count($headers);$n++)
				{
				?>
				<tr>
				<td><div class="ListTitle"><a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo urlencode($headers[$n]["ref"])?>"><?php echo $headers[$n]["smart_theme_name"]?></a></div></td>
				<td><div class="ListTools"><a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo urlencode($headers[$n]["ref"])?>">&gt;&nbsp;<?php echo $lang["action-select"]?></a></div></td>
				</tr>
				<?php
				}*/
	
			?>
			</table>
			</div>
	
			</div>
			<div class="PanelShadow"> </div>
			</div>
			<?php
			}
		} }/*end if subcategory headers */ ?>
	<?php
	}
else
	{
	# --------------- All theme categories on one page, OR multi level browsing via dropdowns. -------------------


	if ($theme_category_levels>1 && !$theme_direct_jump)
		{
		# Display dropdown box for multiple theme selection levels.
		?>
		<div class="RecordBox">
		<div class="RecordPanel">

		<div class="Question" style="border-top:none;">
		<label for="theme1"><?php echo $lang["themecategory"] . " 1" ?></label>
		<select class="stdwidth" name="theme1" id="theme1" onchange="document.getElementById('lastlevelchange').value='1';document.getElementById('themeform').submit();">
		<?php
		//if (!isset($themes[0]))
			//{
			?><option value=""><?php echo $lang["select"]?></option><?php
			//}

		# ----------------- Level 1 headers -------------------------
		$headers=get_theme_headers(array());
		for ($n=0;$n<count($headers);$n++)
			{
			?><option value="<?php echo htmlspecialchars($headers[$n])?>" <?php if (isset($themes[0])&&
			stripslashes($themes[0])==
			stripslashes($headers[$n]))  { ?>selected<?php } ?>><?php echo str_replace("*","",i18n_get_translated($headers[$n]))?></option><?php
			}
		?>
		</select>
		<div class="clearerleft"> </div>
		</div>

		<?php
		if (count($themes)>0){
		for ($x=0;$x<count($themes);$x++){
		# ----------------- Level headers -------------------------
		if (isset($themes[$x])&&$themes[$x]!="" && $theme_category_levels>($x+1))
			{
			$themearray=array();
			for($n=0;$n<$x+1;$n++){
				$themearray[]=$themes[$n];
				}
			$headers=get_theme_headers($themearray);
			if (count($headers)>0)
				{
				?>
				<div class="Question" style="border-top:none;">
				<label for="theme<?php echo $x+2?>"><?php echo $lang["themecategory"] . " ".($x+2) ?></label>

				<select class="stdwidth" name="theme<?php echo $x+2?>" id="theme<?php echo $x+2?>" onchange="document.getElementById('lastlevelchange').value='<?php echo $x+2?>';document.getElementById('themeform').submit();">
					<option value=""><?php echo $lang['select']; ?></option>
				<?php
				for($n = 0; $n < count($headers); $n++)
					{
					?>
					<option value="<?php echo htmlspecialchars($headers[$n]); ?>" <?php if(isset($themes[$x + 1]) && stripslashes($themes[$x + 1]) == stripslashes($headers[$n])) { ?>selected<?php } ?>><?php echo str_replace("*", "", $headers[$n]); ?></option>
					<?php
					}
					?>
				</select>
				<div class="clearerleft"> </div>
				</div>
				<?php
				}
			}
		}
	}
		?>
		</div>
		</div>
		<?php
		}
	}


# Display Themes

if (isset($themes[0]) && $theme_direct_jump==false)
	{
	# Display just the selected theme
	DisplayTheme($themes, $simpleview);
	}
elseif (($theme_category_levels==1 && $smart_theme=="") || $theme_direct_jump)
	{
	# Display all themes
	$headers=get_theme_headers($themes);
	$tmp = hook("themeheadersdisp", "", array($themes)); if($tmp!==false) $headers = $tmp;
	for ($n=0;$n<count($headers);$n++)
		{
			DisplayTheme(array_merge($themes,array($headers[$n])), $simpleview);
		}
	}
?>

<?php
# ------- Smart Themes -------------
if ($header=="" && !isset($themes[0]))
	{
	$headers=get_smart_theme_headers();
	for ($n=0;$n<count($headers);$n++)
		{
		$node=getval("node",0);

		if ((checkperm("f*") || checkperm("f" . $headers[$n]["ref"]))
		&& !checkperm("f-" . $headers[$n]["ref"]) && ($smart_theme=="" || $smart_theme==$headers[$n]["ref"]))
			{				
			if($simpleview)
				{
				if (getval("smart_theme","")=="")
					{
					// Main featured collections page. Show smart theme name wth link to first level.
					?>
					
					<a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo urlencode(getval("parentnode",0)) ?>&nodename=<?php echo urlencode(getval("parentnodename","")) ?>&simpleview=true" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleLink" id="advertising_tile_smart<?php echo $n ;?>&simpleview=true">
					<div class="FeaturedSimpleTile">										
					<div id="FeaturedSimpleTileContents_smart<?php echo $n ; ?>"  class="HomePanelIN FeaturedSimpleTileContents" >	
								<h2><?php echo str_replace("*","",i18n_get_translated($headers[$n]["smart_theme_name"])); ?></h2>
							</div>			
						</div>			
					</a>
					<?php
					}
				elseif($node!=0)
					{				
					# Sub node, display node name and make it a link to the previous level.
					?>
					<p><a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo urlencode(getval("parentnode",0)) ?>&nodename=<?php echo urlencode(getval("parentnodename","")) ?>&simpleview=true" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["back"]?></a></p>
					<?php						
					}
				else
					{
					# First smart theme node, display link to main themes page
					?>
					<p><a href="<?php echo $baseurl_short?>pages/themes.php?simpleview=true" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["back"]?></a></p>
					<?php
					}
				}
			else
				{
				
				?>
				<div class="RecordBox">
				<div class="RecordPanel">
	
				<div class="RecordHeader">
				<h1 style="margin-top:5px;">
				<?php if ($node==0)
					{
					# Top level node. Just display smart theme name.
					echo str_replace("*","",i18n_get_translated($headers[$n]["smart_theme_name"]));
					}
				else
					{
					# Sub node, display node name and make it a link to the previous level.
					?>
					<a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo urlencode(getval("parentnode",0)) ?>&nodename=<?php echo urlencode(getval("parentnodename","")) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo getval("nodename","???") ?></a>
					<?php
					}
				
				}
				
			if($simpleview)
				{
				if (getval("smart_theme","")!="") // We are in the smart theme already
					{
                    $themes = get_smart_themes_nodes($headers[$n]['ref'], (7 == $headers[$n]['type']), $node);
					for ($m=0;$m<count($themes);$m++)
						{
						$s=$headers[$n]["name"] . ":" . $themes[$m]["name"];
						if ($themes[$m]['is_parent'])
							{
							?>
							<a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo $themes[$m]["node"] ?>&parentnode=<?php echo urlencode($node) ?>&parentnodename=<?php echo urlencode(getval("nodename","")) ?>&nodename=<?php echo urlencode($themes[$m]["name"]) ?>&simpleview=true" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleLink" id="advertising_tile_smart<?php echo $n ;?>">
							<div class="FeaturedSimpleTile">
								<div id="FeaturedSimpleTileContents_smart<?php echo $n ; ?>"  class="HomePanelIN FeaturedSimpleTileContents" >	
									<h2><?php echo i18n_get_collection_name($themes[$m])?>	</h2>									
								</div>
							</div>			
							</a>
							<?php
							}
						else
							{
							# Has no children. Default action is to show matching resources.
							?>
							<a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($s)?>&resetrestypes=true" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleLink" id="advertising_tile_smart<?php echo $n ;?>">
							<div class="FeaturedSimpleTile">
								<div id="FeaturedSimpleTileContents_smart<?php echo $n ; ?>"  class="HomePanelIN FeaturedSimpleTileContents" >	
									<h2><?php echo i18n_get_collection_name($themes[$m])?></h2>
								</div>			
							</div>			
							</a>
							<?php
							}					
						}
					}
				}
			else
				{				
				?>
				</h1>
				</div>
				
				<?php hook("aftersmartthemetitle");?>
				
				<div class="Listview" style="margin-top:10px;margin-bottom:10px;clear:left;">
				<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
				<tr class="ListviewBoxedTitleStyle">
				<td><?php echo $lang["name"]?></td>
				<?php hook("beforecollectiontoolscolumnheader");?>
				<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
				</tr>
	
				<?php
				$themes = get_smart_themes_nodes($headers[$n]['ref'], (7 == $headers[$n]['type']), $node);
				for ($m=0;$m<count($themes);$m++)
					{
					$s=$headers[$n]["name"] . ":" . $themes[$m]["name"];

					# Indent this item?
					$indent = str_pad('', $themes[$m]['indent'] * 5, ' ');
                    if(0 < $themes[$m]['indent'])
                        {
                        $indent .= '&#746;';
                        }
                    $indent .= '&nbsp;';
					$indent = str_replace(" ","&nbsp;",$indent);
	
					?>
					<tr>
					<td><div class="ListTitle"><?php echo $indent?>
					<?php if($themes[$m]['is_parent'] && $themes_category_navigate_levels)
						{
						# Has children. Default action is to navigate to a deeper level.
						?>
						<a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo $themes[$m]["node"] ?>&parentnode=<?php echo urlencode($node) ?>&parentnodename=<?php echo urlencode(getval("nodename","")) ?>&nodename=<?php echo urlencode($themes[$m]["name"]) ?>" onClick="return CentralSpaceLoad(this,true);">
						<?php
						}
					else
						{
						# Has no children. Default action is to show matching resources.
						?>
						<a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($s)?>&resetrestypes=true" onClick="return CentralSpaceLoad(this,true);">
						<?php
						}
					?>
	
					<?php echo i18n_get_collection_name($themes[$m])?></a>
					</div></td>
					<?php hook("beforecollectiontoolscolumn");?>
					<td><div class="ListTools">
					<a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($s)?>&resetrestypes=true" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $themes_category_split_pages?$lang["action-viewmatchingresources"]:$lang["viewall"]?></a>
					<?php
                    if($themes_category_split_pages && 7 == $headers[$n]['type'] && $themes[$m]['is_parent'])
                        {
                        ?>
                        <a href="<?php echo $baseurl_short?>pages/themes.php?smart_theme=<?php echo $headers[$n]["ref"] ?>&node=<?php echo $themes[$m]["node"] ?>&parentnode=<?php echo urlencode($node) ?>&parentnodename=<?php echo urlencode(getval("nodename","")) ?>&nodename=<?php echo urlencode($themes[$m]["name"]) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-expand"]?></a>
                        <?php
                        }

                    hook('additionalsmartthemetool');
                    ?>
					</div></td>
					</tr>
					<?php
					}
				?>
				</table>
				</div>
	
				</div>
				<div class="PanelShadow"> </div>
				</div>
				<?php
				}
			} //end of if ((checkperm("f*") || checkperm("f" . $headers[$n]["ref"])) && !checkperm("f-" . $headers[$n]["ref"]) && ($smart_theme=="" || $smart_theme==$headers[$n]["ref"]))
		} // end of for ($n=0;$n<count($headers);$n++)
	} // end of if ($header=="" && !isset($themes[0]))

if($simpleview)
	{
	?>
	</div><!-- End of FeaturedSimpleLinks -->
	<?php
	}
else
	{?>
	</form>
	</div>
	<?php
	if (!$public_collections_header_only)
		{?>
		<?php if (!checkperm("b") && $enable_public_collections)
			{ ?>
			<div class="clearerleft"> </div>
			<div class="BasicsBox">
				<h1><?php echo $lang["findpubliccollection"]?></h1>
				<p class="tight"><?php echo text("findpublic")?></p>
				<p><a href="<?php echo $baseurl_short?>pages/collection_public.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["findpubliccollection"]?>&nbsp;&gt;</a></p>
			</div>
			<?php
			} 
		} 
	}

include "../include/footer.php";
?>
