<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";

	
$themes=array();
$themecount=0;
reset($_POST);reset($_GET);foreach (array_merge($_GET, $_POST) as $key=>$value) {
	// only set necessary vars
	if (substr($key,0,5)=="theme" && $value!=""){
		$themes[$themecount]=urldecode($value);
		$themecount++;
		}
	}
	
$header=getvalescaped("header","");
$smart_theme=getvalescaped("smart_theme","");
$showexisting=getvalescaped("showexisting","");
$subthemes=getvalescaped("subthemes",false);

$linksuffix="?";
for ($x=0;$x<count($themes);$x++){
	if ($x!=0){ $linksuffix.="&"; }
	$linksuffix.="theme" . ($x+1);
	$linksuffix.="=". urlencode($themes[$x]);
	$themename=$themes[$x];
}

$linksuffixprev=explode("&",$linksuffix);
array_pop($linksuffixprev); // remove last level
$linksuffixprev=implode('&',$linksuffixprev);

# Process deletion of access keys
if (getval("deleteaccess","")!="")
	{
	$ref=getvalescaped("ref","",true);	
	delete_collection_access_key($ref,getvalescaped("deleteaccess",""));
	}

include "../include/header.php";

?>

<div class="BasicsBox"> 
<form method=post id="themeform" action="<?php echo $baseurl_short?>pages/theme_category_share.php<?php echo $linksuffix ?>" onsubmit="return CentralSpacePost(this,true)">
<input type="hidden" name="generateurl" id="generateurl" value="">
<p><a href='<?php echo $baseurl_short?>pages/themes.php<?php echo $linksuffixprev?>' onclick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang['backtothemes']?></a></p>
<h1><?php echo $lang["share_theme_category"] . " - " . $themename?></h1>


<?php

// Get collections under the theme
$collectionstoshare=get_themes($themes,$subthemes);

if (count($collectionstoshare)<1) # There are no collections in this theme
		{
		$show_error=true;
		$error=$lang["cannotshareemptythemecategory"];
		}
else
	{
	foreach($collectionstoshare as $collection)
		{			
		$ref=$collection["ref"];		
	
		# Get min access to this collection
		$minaccess=collection_min_access($ref);
		
		if ($minaccess>=1 && !$restricted_share) # Minimum access is restricted or lower and sharing of restricted resources is not allowed. The user cannot share this collection.
			{
			$show_error=true;
			$error = str_replace("%collectionname%", i18n_get_collection_name($collection), $lang["collection-name"]) . "\n" . $lang["restrictedsharecollection"];
			}
			
		if (count(get_collection_resources($ref))==0) # Sharing an empty collection?
			{
			$show_error=true;
			$error = str_replace("%collectionname%", i18n_get_collection_name($collection), $lang["collection-name"]) . "\n" . $lang["cannotshareemptycollection"];
			}
		
		}
	
	$access=getvalescaped("access","");
	$expires=getvalescaped("expires","");
		
	if (getvalescaped("generateurl","")=="")
		{ ?>
									
			<div class="VerticalNav">

			<li><a id="emaillink" onClick="var _href=jQuery('#emaillink').attr('href');var subthemes=document.getElementById('subthemes').checked;jQuery('#emaillink').attr('href',_href + '&subthemes=' + subthemes);return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short . "pages/collection_email.php" . $linksuffix . "&catshare=true\">" . $lang["email_theme_category"];?></a></li>
			<li><a id="urllink" onClick="var _href=jQuery('#urllink').attr('href');var subthemes=document.getElementById('subthemes').checked;jQuery('#urllink').attr('href',_href + '&subthemes=' + subthemes);return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short . "pages/theme_category_share.php" . $linksuffix . "&generateurl=true&subthemes=" . $subthemes . "\">" . $lang["generateurls"];?></a></li>
			</div>
		<?php }
	else	
		{?>
		
			
		<?php
		if ($access=="")
			{
			if (!($hide_internal_sharing_url))
				{
				?>
				<p><?php echo $lang["generateurlinternal"]?></p>
				
				<p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/pages/themes.php<?php echo $linksuffix?>">
				<?php
				}
			?>
			<p><?php echo $lang["selectgenerateurlexternalthemecat"] ?></p>
			<div class="Question" id="question_access">
			<label for="archive"><?php echo $lang["access"]?></label>
			<select class="stdwidth" name="access" id="access">
			<?php
			# List available access levels. The highest level must be the minimum user access level.
			for ($n=$minaccess;$n<=1;$n++) { ?>
			<option value="<?php echo $n?>"><?php echo $lang["access" . $n]?></option>
			<?php } ?>
			</select>
			<div class="clearerleft"> </div>
			</div>
			
			<?php
			for ($x=0;$x<$themecount;$x++)
				{ ?>
					<input type="hidden" name="theme<?php echo $x+1 ?>" id="theme<?php echo $x+1 ?>" value="<?php echo i18n_get_translated($themes[$x]) ?>">
				<?php 
				}
			?>
			
			<div class="Question">
			<label><?php echo $lang["expires"]?></label>
			<select name="expires" class="stdwidth">
			<option value=""><?php echo $lang["never"]?></option>
			<?php for ($n=1;$n<=150;$n++)
				{
				$date=time()+(60*60*24*$n);
				?><option <?php $d=date("D",$date);if (($d=="Sun") || ($d=="Sat")) { ?>style="background-color:#cccccc"<?php } ?> value="<?php echo date("Y-m-d",$date)?>"><?php echo nicedate(date("Y-m-d",$date),false,true)?></option>
				<?php
				}
			?>
			</select>
			<div class="clearerleft"> </div>
			</div>	
			<div class="Question">
				<label for="subthemes"><?php echo $lang["share_theme_category_subcategories"]?></label>
				<input type="checkbox" id="subthemes" name="subthemes" value="true" <?php if ($subthemes){echo "checked";} ?>>
				<div class="clearerleft"> </div>
			</div>
						
			<div class="QuestionSubmit" style="padding-top:0;margin-top:0;">
			<label for="buttons"> </label>
			<input onclick="jQuery('#generateurl').val(true);" type="submit"  value="&nbsp;&nbsp;<?php echo $lang["generateexternalurls"]?>&nbsp;&nbsp;" />
			</div>
			
			<?php			
			}
		else
			{
			# Access has been selected. Generate a URL.			
			?>
			<p><?php echo $lang["generatethemeurlsexternal"]?></p>
			<p>
			<textarea class="URLDisplay" cols="100" rows="<?php echo count($collectionstoshare)*4+1; ?>" ><?php
			$unapproved_collection=false; 
			foreach($collectionstoshare as $collection){	
				$ref=$collection["ref"];
				
				#Check if any resources are not approved
				if (!is_collection_approved($ref) && !$collection_allow_not_approved_share) {
					echo str_replace("%collectionname%", i18n_get_collection_name($collection), $lang["collection-name"]) . "\r\n" . $lang["notapprovedsharecollection"] . "\r\n\r\n";
					$unapproved_collection=true;
				} else {
					echo str_replace("%collectionname%", i18n_get_collection_name($collection), $lang["collection-name"]) . "\r\n" . $baseurl?>/?c=<?php echo urlencode($ref)?>&k=<?php echo generate_collection_access_key($ref,0,"URL",$access,$expires) . "\r\n" . ($expires!="" ? str_replace("%date%", $expires, $lang["expires-date"]) : str_replace("%date%", $lang["never"], $lang["expires-date"])) . "\r\n\r\n";
				}
			}
			?>
			</textarea>
			<?php if ($unapproved_collection){?><script>alert('<?php echo $lang['notapprovedsharetheme']?>');</script><?php } ?>
			</p>
			<?php
			}
		}
	//Display existing shares for collections in theme

	if ($access=="")
		{
		foreach($collectionstoshare as $collection)
			{			
			$ref=$collection["ref"];
			$keys=get_collection_external_access($ref);
			?>
			<p>&nbsp;</p>
			<h2><?php echo str_replace("%collectionname%", i18n_get_collection_name($collection), $lang["externalusersharing-name"]);?></h2>
			<div class="Question">
			<?php
			if (count($keys)==0)
				{
				?>
				<p><?php echo $lang["noexternalsharing"] ?></p>
				<?php
				}
			else
				{
				?>
				<div class="Listview">
				<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
				<tr class="ListviewTitleStyle">
				<td><?php echo $lang["accesskey"];?></td>
				<td><?php echo $lang["sharedby"];?></td>
				<!--<td><?php echo $lang["sharedwith"];?></td>-->
				<td><?php echo $lang["lastupdated"];?></td>
				<td><?php echo $lang["lastused"];?></td>
				<td><?php echo $lang["expires"];?></td>
				<td><?php echo $lang["access"];?></td>
				<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
				</tr>
				<?php
				for ($n=0;$n<count($keys);$n++)
					{
					?>
					<tr>
					<td><div class="ListTitle"><a target="_blank" href="<?php echo $baseurl . "?c=" . urlencode($ref) . "&k=" . $keys[$n]["access_key"]?>"><?php echo $keys[$n]["access_key"]?></a></div></td>
					<td><?php echo resolve_users($keys[$n]["users"])?></td>
					<!--<td><?php echo $keys[$n]["emails"]?></td>-->
					<td><?php echo nicedate($keys[$n]["maxdate"],true);	?></td>
					<td><?php echo nicedate($keys[$n]["lastused"],true); ?></td>
					<td><?php echo ($keys[$n]["expires"]=="")?$lang["never"]:nicedate($keys[$n]["expires"],false)?></td>
					<td><?php echo ($keys[$n]["access"]==-1)?"":$lang["access" . $keys[$n]["access"]]; ?></td>
					<td><div class="ListTools">
					<a href="#" onClick="if (confirm('<?php echo $lang["confirmdeleteaccess"]?>')) {document.getElementById('deleteaccess').value='<?php echo $keys[$n]["access_key"] ?>';document.getElementById('ref').value='<?php echo htmlspecialchars($ref) ?>';document.getElementById('themeform').submit(); }">&gt;&nbsp;<?php echo $lang["action-delete"]?></a>
					</div></td>
					</tr>
					<?php
					}
				?>
				</table>
				</div>
				<?php
				}
				?></div>
			<?php }
		}?>	
	
	<input type="hidden" id="deleteaccess" name="deleteaccess" value=""/>
	<input type="hidden" id="ref" name="ref" value=""/>
	</form>	
	
	</div>
	<?php
	}


if (isset($show_error)){?>
    <script type="text/javascript">
    alert(<?php echo json_encode($error);?>);
    CentralSpaceLoad('<?php echo $baseurl_short?>pages/themes.php<?php echo $linksuffixprev?>'); 
    </script><?php
    }
?>

<?php
include "../include/footer.php";
?>
