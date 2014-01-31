<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";

$ref=getvalescaped ("ref","",true);
# fetch the current search (for finding simlar matches)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive",0,true);
$starsearch=getvalescaped("starsearch","");
$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);

$minaccess=get_resource_access($ref);

include "../include/header.php";
?>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>

<h1><?php echo $lang["share-resource"] ?></h1>

<div class="BasicsBox"> 
<form method=post id="collectionform" action="<?php echo $baseurl_short?>pages/resource_share.php" onSubmit="return CentralSpacePost(this);">
<input type="hidden" name="ref" id="ref" value="<?php echo htmlspecialchars($ref) ?>">
<input type="hidden" name="generateurl" id="generateurl" value="">

	<div class="VerticalNav">
	<ul>

        <li><a href="<?php echo $baseurl_short?>pages/resource_email.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["emailresource"]?></a></li>

        <li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo urlencode($ref) ?>&generateurl=true&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"><?php echo $lang["generateurl"]?></a></li>

        <?php
        if (getval("generateurl","")!="")
		{
                ?>
                <p><?php echo $lang["generateurlinternal"]?></p>
                <p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/?r=<?php echo $ref?>"></p>
                <?php
                }
                       
                $access=getvalescaped("access","");
		$expires=getvalescaped("expires","");
		if ($access=="")
			{
			?>
			<p><?php echo $lang["selectgenerateurlexternal"]; ?></p>
			
			<?php if(!hook('replaceemailaccessselector')): ?>
			<div class="Question" id="question_access">
			<label for="archive"><?php echo $lang["access"]?></label>
			<select class="stdwidth" name="access" id="access">
			<?php
			# List available access levels. The highest level must be the minimum user access level.
			for ($n=$minaccess;$n<=1;$n++) { ?>
			<option value="<?php echo $n?>" <?php if(getvalescaped("editaccesslevel","")==$n){echo "selected";}?>><?php echo $lang["access" . $n]?></option>
			<?php } ?>
			</select>
			<div class="clearerleft"> </div>
			</div>
			<?php endif; #hook replaceemailaccessselector ?>
			
			<div class="Question">
			<label><?php echo $lang["expires"]?></label>
			<select name="expires" class="stdwidth">
			<option value=""><?php echo $lang["never"]?></option>
			<?php for ($n=1;$n<=150;$n++)
				{
				$date=time()+(60*60*24*$n);
				?><option <?php $d=date("D",$date);if (($d=="Sun") || ($d=="Sat")) { ?>style="background-color:#cccccc"<?php } ?> value="<?php echo date("Y-m-d",$date)?>" <?php if(substr(getvalescaped("editexpiration",""),0,10)==date("Y-m-d",$date)){echo "selected";}?>><?php echo nicedate(date("Y-m-d",$date),false,true)?></option>
				<?php
				}
			?>
			</select>
			<div class="clearerleft"> </div>
			</div>
			
			<div class="QuestionSubmit" style="padding-top:0;margin-top:0;">
                            <label for="buttons"> </label>
                            <input name="generateurl" type="submit" value="&nbsp;&nbsp;<?php echo $lang["generateexternalurl"]?>&nbsp;&nbsp;" />
			</div>
			<?php
			}
		else
			{
			# Access has been selected. Generate a new URL.
			?>
			<p><?php echo $lang["generateurlexternal"]?></p>
		
			<p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/?r=<?php echo urlencode($ref) ?>&k=<?php echo generate_resource_access_key($ref,$userref,$access,$expires)?>">
			<?php
			}
        ?>
        
        </ul>
        </div>

</form>
</div>


<?php

include "../include/footer.php";
?>