<?php 
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; if (checkperm("b")){exit("Permission denied");}
#if (!checkperm("s")) {exit ("Permission denied.");}
include_once "../include/collections_functions.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";

$offset=getvalescaped("offset",0);
$per_page=getvalescaped("per_page_list",$default_perpage_list,true);rs_setcookie('per_page_list', $per_page);

include "../include/header.php";

?>
<div class="BasicsBox">
<p><a href="<?php echo $baseurl_short?>pages/collection_manage.php" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK ?><?php echo $lang["managecollectionslink"]?></a></p>	
<h1><?php echo $lang["shared_collections"]?></h1>
<?php

$collections=get_user_collections($userref,"!shared");
$results=count($collections);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$jumpcount=1;

$url=$baseurl_short."pages/view_shares.php?coluser=" . $userref;

?><div class="TopInpageNav"><?php pager(false); ?></div><?php

for ($n=$offset;(($n<count($collections)) && ($n<($offset+$per_page)));$n++)
	{	
	?>
	<div class="RecordBox">
	<div class="RecordPanel">
		<div class="RecordHeader">
			<table>
			<tr>
				<td style="margin:0px;padding:0px;">
					<h1 class="shared_collection_title"><a href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $collections[$n]['ref']?>" onclick="return CentralSpaceLoad(this);" ><?php echo i18n_get_collection_name($collections[$n]);  ?></a></h1>
				</td>
			</tr>
			</table>
		
			<div class="clearerright"> </div>
		</div><!-- End of RecordHeader --> 
		<div class="Listview" style="margin-top:10px;margin-bottom:5px;clear:left;">
			<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
				<tr class="ListviewBoxedTitleStyle">
					<td width="15%">
					<?php echo $lang["sharedwith"]; ?>
					</td>
					<td width="15%">
					<?php echo  $lang["access"] ?>
					</td>
					<td width="40%">
					<?php echo $lang["fieldtitle-notes"] ?>
					</td>
					<td width="30%"><div class="ListTools"><?php echo $lang["tools"]?></div></td>
				</tr>
			<?php
			// Display row for each share/attached user
			$colref=$collections[$n]["ref"];
			
			// Check for external shares
			$extshares=sql_query("select access, expires from external_access_keys where collection='$colref' and (expires is null or expires>now()) group by collection");
			
			if(count($extshares)!=0)
				{			
				foreach($extshares as $extshare)
					{
					echo "<tr>";
					echo "<td>" . "External " . "</td>";
					echo "<td>" . (($extshare["access"]==0)?$lang["access0"]:$lang["access1"]) . "</td>";
					echo "<td>" .  str_replace("%date%",(($extshare["expires"]!="")?nicedate($extshare["expires"]):$lang["never"]),$lang["expires-date"]) . "</td>";
					echo "<td><div class=\"ListTools\"><a onclick=\"return CentralSpaceLoad(this,true);\" href=\"" . $baseurl . "/pages/collection_share.php?ref=" . $collections[$n]["ref"] . "\"><?php echo LINK_CARET ?>" . $lang["action-edit"] . "</a></div></td>";
					echo "</tr>";
					}					
				}
				
			// Check for attached users
			$colusers=sql_query("select u.fullname, u.username from user_collection uc left join user u on u.ref=uc.user and user<>'$userref' where uc.collection='$colref'");
			if(count($colusers)!=0)
				{
				echo "<tr>";
				echo "<td>" . $lang["users"] . "</td>";
				echo "<td>" . (($collections[$n]["allow_changes"]==0)?$lang["view"]:$lang["addremove"]) . "</td>";
				echo "<td>" . $lang["users"] . ":<br>";
				foreach($colusers as $coluser)
					{
					echo (($coluser["fullname"]!="")?$coluser["fullname"]:$coluser["username"]) . "<br>";											
					}
				echo "</td>";
				echo "<td><div class=\"ListTools\"><a onclick=\"return CentralSpaceLoad(this,true);\" href=\"" . $baseurl . "/pages/collection_edit.php?ref=" . $collections[$n]["ref"] . "\"><?php echo LINK_CARET ?>" . $lang["action-edit"] . "</a></div></td>";
				echo "</tr>";
				}
				
			if ($collections[$n]["public"]==1)
				{
				if (strlen($collections[$n]["theme"])>0)
					{
					echo "<tr>";
					echo "<td>" . $lang["theme"] . "</td>";
					echo "<td>" . (($collections[$n]["allow_changes"]==0)?$lang["view"]:$lang["addremove"])  . "</td>";
					echo "<td>" . $lang["notavailableshort"] . "</td>";
					echo "<td><div class=\"ListTools\"><a onclick=\"return CentralSpaceLoad(this,true);\" href=\"" . $baseurl . "/pages/collection_edit.php?ref=" . $collections[$n]["ref"] . "\"><?php echo LINK_CARET ?>" . $lang["action-edit"] . "</a></div></td>";
					echo "</tr>";
					}
				else
					{
					echo "<tr>";
					echo "<td>" . $lang["public"] . "</td>";
					echo "<td>" . (($collections[$n]["allow_changes"]==0)?$lang["view"]:$lang["addremove"])  . "</td>";
					echo "<td>" . $lang["notavailableshort"] . "</td>";
					echo "<td><div class=\"ListTools\"><a onclick=\"return CentralSpaceLoad(this,true);\" href=\"" . $baseurl . "/pages/collection_edit.php?ref=" . $collections[$n]["ref"] . "\"><?php echo LINK_CARET ?>" . $lang["action-edit"] . "</a></div></td>";
					echo "</tr>";			
					}
				}
				?>
		
			</table>
		</div><!-- End of Listview --> 
		<div class="PanelShadow"> </div>
	</div> <!-- End of RecordPanel -->
	</div> <!--  End of RecordBox -->
	<?php
	}
	?>	


<div class="BottomInpageNav"><?php pager(false); ?></div>

</div><!--  End of BasicsBox -->
<?php		
include "../include/footer.php";
?>
