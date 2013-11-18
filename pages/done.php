<?php
include "../include/db.php";
include "../include/general.php";
include "../include/collections_functions.php";
if (getval("user","")!="" || isset($anonymous_login)) {include "../include/authenticate.php";} #Authenticate if already logged in, so the correct theme is displayed when using user group specific themes.

if (getval("refreshcollection","")!="")
	{
	refresh_collection_frame();
	}

# fetch the current search 
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);

$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

include "../include/header.php";
?>

<div class="BasicsBox">
    <h1><?php echo $lang["complete"]?></h1>
    <p><?php echo text(htmlspecialchars(getvalescaped("text",""))) ?></p>
   
    <?php if (getval("user","")!="" || getval("k","")!="" || isset($anonymous_login)) { # User logged in? ?>
 
 	<?php
	if(!hook("donebacktoresource")):
 	# Ability to link back to a resource page
	$resource=getval("resource","");
	if ($resource!="")
		{
		?>
	    <p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($resource) ?>&k=<?php echo urlencode(getval("k","")) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo ($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>">&gt;&nbsp;<?php echo $lang["continuetoresourceview"]?></a></p>
		<?php
		}
	endif; # hook donebacktoresource
	?>
 
	<?php if (getval("k","")=="") { ?>
    <p><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>">&gt;&nbsp;<?php echo $lang["continuetoresults"]?></a></p>

    <p><a href="<?php echo ($use_theme_as_home?$baseurl_short.'pages/themes.php':$default_home_page)?>">&gt;&nbsp;<?php echo $lang["continuetohome"]?></a></p>

    <?php } ?>
    
    <?php hook("extra");?>
    <?php } else {?>
    <p><a href="<?php echo $baseurl_short?>login.php">&gt;&nbsp;<?php echo $lang["continuetouser"]?></a></p>
    <?php } ?>
</div>

<?php
include "../include/footer.php";
?>
