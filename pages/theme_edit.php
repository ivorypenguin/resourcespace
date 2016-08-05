<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/resource_functions.php";
include "../include/search_functions.php";
if (!$enable_theme_category_edit){ die ('$enable_theme_category_edit=false');}

function save_themename()
	{
		global $baseurl, $link, $themename, $collection_column;
		$sql="update collection set	" . $collection_column . "='" . getvalescaped("rename","") . "' where " . $collection_column . "='" . escape_check($themename)."'";
		sql_query($sql);
		redirect("pages/" . $link);
	}

$themes=array();
$themecount=0;
reset($_POST);reset($_GET);foreach (array_merge($_GET, $_POST) as $key=>$value) {
	// only set necessary vars
	if (substr($key,0,5)=="theme" && $value!=""){
		$themes[$themecount]=urldecode($value);
		$themecount++;
		}
	}


# Work out theme name and level, also construct back link
$link="themes.php?";
$lastlevelchange=getvalescaped("lastlevelchange",1,true);
$link.="lastlevelchange=" . $lastlevelchange . "&";
for ($x=0;$x<$themecount;$x++)
	{
	if (!$x==0){$link.="&";}
	if ($x==0)
		{
		$collection_column="theme";
		if ($x<$themecount-1)
			{$link.= "theme=" . urlencode($themes[$x]);}
		elseif ($x==$themecount-1 && getval("rename","")!="" && !($themes_category_split_pages)) #add  new name of theme to back link
			{
			$link.= "theme=" . getvalescaped("rename",""); 
			}
		$themename=i18n_get_translated($themes[$x]);
		}
	else
		{
		$collection_column="theme" . ($x+1);
		if ($x<$themecount-1 || getval("rename","")=="") #add current theme to back link only if not renaming it
			{$link.= "theme" . ($x+1) . "=" . urlencode($themes[$x]);}
		elseif ($x==$themecount-1 && getval("rename","")!="" && !($themes_category_split_pages)) #add  new name of theme to back link
			{
			$link.= "theme" . ($x+1) . "=" . getvalescaped("rename",""); 
			}
		$themename=i18n_get_translated($themes[$x]);
		}
	}

if (getval("rename","")!="")
	{
		# Save theme category
		save_themename();
	}


include "../include/header.php";

if (!checkperm("t")) {
	echo "You do not have permission to edit theme categories. " ;
	exit;
	} 

?>
<p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl . "/pages/" . $link?>">&lt;&nbsp;<?php echo $lang["backtothemes"]?></a></p>
<?php

?>
<div class="BasicsBox">
<h1><?php echo $lang["edit_theme_category"] ?></h1>
<p><?php echo text("introtext")?></p>
	<form method=post id="themeform" action="<?php echo $baseurl_short?>pages/theme_edit.php">
		<input type="hidden" name="collection_column" id="collection_column" value="<?php echo $collection_column?>">
		<input type="hidden" name="link" id="link" value="<?php echo $link?>">
		<input type="hidden" name="lastlevelchange" id="lastlevelchange" value="<?php echo htmlspecialchars($lastlevelchange)?>">

		<?php
		for ($x=0;$x<$themecount;$x++)
			{
			if ($x==0)
				{?>
				<input type="hidden" name="theme" id="theme" value="<?php echo i18n_get_translated($themes[$x])?>">
				<?php
				}
			else
				{ ?>
				<input type="hidden" name="theme<?php echo $x+1 ?>" id="theme<?php echo $x+1 ?>" value="<?php echo i18n_get_translated($themes[$x]) ?>">
				<?php }
			}
		?>

		<div class="Question">
			<label for="rename"><?php echo $lang["name"]?></label><input type=text class="stdwidth" name="rename" id="rename" value="<?php echo $themename?>" maxlength="100" />
			<div class="clearerleft"> </div>
		</div>

		<div class="QuestionSubmit">
			<label for="buttons"> </label>
			<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
		</div>
	</form>
</div>

<?php
include "../include/footer.php";
?>





