<?php
/**
 * Manage content string page (part of System area)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("o")) {exit ("Permission denied.");}
include "../../include/research_functions.php";
include_once "../../include/collections_functions.php";

$offset=getvalescaped("offset",0);
if (array_key_exists("findpage",$_POST) ||array_key_exists("findname",$_POST) || array_key_exists("findtext",$_POST)) {$offset=0;} # reset page counter when posting
$findpage=getvalescaped("findpage","");
$findname=getvalescaped("findname","");
$findtext=getvalescaped("findtext","");
$page=getvalescaped("page","");
$name=getvalescaped("name","");

$extended=false;
if ($findpage!="" || $findname!="" || $findtext!="")
  {
  # Extended view - show the language and user group columns when searching as multiple languages/groups may be returned rather than
  # the single entry returned when not searching.
  $extended=true;
  $groups=get_usergroups();  
  }


if ($page && $name){redirect($baseurl_short."pages/admin/admin_content_edit.php?page=$page&name=$name&offset=$offset&save=true&custom=1");}

include "../../include/header.php";
?>


<div class="BasicsBox" style="position:relative;"> 
  <h1><?php echo $lang["managecontent"]?></h1>
  <?php 
  $int_text=text("introtext");
  echo empty($int_text)?"":"<p>".$int_text."</p>";
$text=get_all_site_text($findpage, $findname,$findtext);

# pager
$per_page=15;
$results=count($text);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$url=$baseurl_short."pages/admin/admin_content.php?findpage=" . urlencode($findpage)."&findname=".urlencode($findname)."&findtext=".urlencode($findtext);
$jumpcount=1;

?><div style="float:right;margin-top:-5px;"><?php pager();?></div>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td width="10%"><?php echo $lang["page"]?></td>
<td width="25%"><?php echo $lang["name"]?></td>
<?php if ($extended) { ?>
<td width="10%"><?php echo $lang["language"]?></td>
<td width="10%"><?php echo $lang["group"]?></td>
<?php } ?>
<td width="<?php echo ($extended?"40":"55") ?>%"><?php echo $lang["text"]?></td>
<td width="10%"><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
for ($n=$offset;(($n<count($text)) && ($n<($offset+$per_page)));$n++)
	{
	$url=$baseurl_short . "pages/admin/admin_content_edit.php?page=" . urlencode($text[$n]["page"]) . "&name=" . urlencode($text[$n]["name"]) . "&editlanguage=" . $text[$n]["language"] . "&editgroup=" . $text[$n]["group"] . "&findpage=" . $findpage . "&findname=" . $findname . "&findtext=" . $findtext . "&offset=" . $offset;
	?>
	<tr>
	<td><div class="ListTitle"><a href="<?php echo $url ?>"><?php echo highlightkeywords(($text[$n]["page"]==""||$text[$n]["page"]=="all"?$lang["all"]:$text[$n]["page"]),$findpage,true);?></a></div></td>
	
	<td><div class="ListTitle"><a href="<?php echo $url ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo highlightkeywords($text[$n]["name"],$findname,true)?></a></div></td>
	
	<?php if ($extended) {
	# Extended view. Show the language and group when searching, as these variants are expanded out when searching.
	
	# Resolve the user group name.
	$group_resolved=$lang["deleted"];
	if ($text[$n]["group"]=="")
	  {
	  $group_resolved=$lang["all"];
	  }
	else
	  {
	  # resolve
	  foreach ($groups as $group)
	    {
	    if ($group["ref"]==$text[$n]["group"]) {$group_resolved=$group["name"];}
	    }
	  }
	?>
	<td><?php echo $text[$n]["language"] ?></td>
	<td><?php echo $group_resolved ?></td>
	<?php } ?>
	
	<td><a href="<?php echo $url ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo highlightkeywords(tidy_trim(htmlspecialchars($text[$n]["text"]),100),$findtext,true)?></a></td>
	
	<td><div class="ListTools"><a href="<?php echo $url ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["action-edit"]?> </a></div></td>
	</tr>
	<?php
	}
?>

</table>
</div>
<div class="BottomInpageNav">
	<?php
	$url = $baseurl_short . 'pages/admin/admin_content.php?findpage=' . urlencode($findpage) . '&findname=' . urlencode($findname) . '&findtext=' . urlencode($findtext);
	pager(false);
	?>
</div>
</div>


<div class="BasicsBox">
    <form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_content.php" onsubmit="return CentralSpacePost(this);">
		<div class="Question">
			<label for="find"><?php echo $lang["searchcontent"]?><br/><?php echo $lang["searchcontenteg"]?></label>
			<div class="tickset">
			 <div class="Inline"><input type=text placeholder="<?php echo $lang['searchbypage']?>" name="findpage" id="findpage" value="<?php echo $findpage?>" maxlength="100" class="shrtwidth" />
			
			<input type=text placeholder="<?php echo $lang['searchbyname']?>" name="findname" id="findname" value="<?php echo $findname?>" maxlength="100" class="shrtwidth" />
		
			<input type=text placeholder="<?php echo $lang['searchbytext']?>" name="findtext" id="findtext" value="<?php echo $findtext?>" maxlength="100" class="shrtwidth" />
			
			<input type="button" value="<?php echo $lang['clearall']?>" onClick="jQuery('#findtext').val('');jQuery('#findpage').val('');jQuery('#findname').val('');form.submit();" />
			<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" />
			 
			</div>
			</div>
			<div class="clearerleft"> 
			</div>
		</div>
	</form>
</div>

<?php if ($site_text_custom_create){?>
<div class="BasicsBox">
    <form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_content.php">
		<div class="Question">
			<label for="find"><?php echo $lang["addnewcontent"]?></label>
			<div class="tickset">
			 <div class="Inline"><input type=text name="page" id="page" maxlength="50" class="shrtwidth" /></div>
			 <div class="Inline"><input type=text name="name" id="name" maxlength="50" class="shrtwidth" /></div>
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
			</div>
			<div class="clearerleft"> </div>
		</div>
	</form>
</div>
<?php } ?>

<?php
include "../../include/footer.php";
?>
