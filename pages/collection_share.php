<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/search_functions.php";
include "../include/resource_functions.php";
include "../include/collections_functions.php";

# Fetch vars
$ref=getvalescaped("ref","",true);
# if bypass sharing page option is on, redirect to e-mail
if ($bypass_share_screen)
	{
	redirect('pages/collection_email.php?ref='.$ref ) ;
	}

$collection=get_collection($ref);

#Check if sharing allowed
if (!$allow_share) {
        $show_error=true;
        $error=$lang["error-permissiondenied"];
        }

# Check if editing existing external share
$editaccess=getvalescaped("editaccess","");
($editaccess=="")?$editing=false:$editing=true;
	

#Check if any resources are not approved
$collectionstates=is_collection_approved($ref);
if (!$collection_allow_not_approved_share && $collectionstates==false) {
        $show_error=true;
        $error=$lang["notapprovedsharecollection"];
        }
	
if(is_array($collectionstates) && (count($collectionstates)>1 || !in_array(0,$collectionstates)))
	{
	$warningtext=$lang["collection_share_status_warning"];
	foreach($collectionstates as $collectionstate)
		{
		$warningtext.="<br>" . $lang["status" . $collectionstate];
		}
	}


# Get min access to this collection
$minaccess=collection_min_access($ref);

if ($minaccess>=1 && !$restricted_share) # Minimum access is restricted or lower and sharing of restricted resources is not allowed. The user cannot share this collection.
        {
        $show_error=true;
    $error=$lang["restrictedsharecollection"];
        }

if (!$collection_allow_empty_share && count(get_collection_resources($ref))==0) # Sharing an empty collection?
        {
        $show_error=true;
    $error=$lang["cannotshareemptycollection"];
        }



# Process deletion of access keys
if (getval("deleteaccess","")!="" && !isset($show_error))
        {
        delete_collection_access_key($ref,getvalescaped("deleteaccess",""));
        }


include "../include/header.php";
?>


<?php if (isset($show_error)){?>
    <script type="text/javascript">
    alert('<?php echo $error;?>');
        history.go(-1);
    </script><?php
    exit();}
?>
  
	<div class="BasicsBox"> 	
	<form method=post id="collectionform" action="<?php echo $baseurl_short?>pages/collection_share.php">
	<input type="hidden" name="ref" id="ref" value="<?php echo htmlspecialchars($ref) ?>">
	<input type="hidden" name="deleteaccess" id="deleteaccess" value="">
	<input type="hidden" name="editaccess" id="editaccess" value="<?php echo htmlspecialchars($editaccess)?>">
	<input type="hidden" name="editexpiration" id="editexpiration" value="">
	<input type="hidden" name="editaccesslevel" id="editaccesslevel" value="">
	<input type="hidden" name="generateurl" id="generateurl" value="">

	<h1><?php echo str_replace("%collectionname", i18n_get_collection_name($collection), $lang["sharecollection-name"]);?></h1>
	<?php
	if(isset($warningtext))
		{
		echo "<div class='PageInformal'>" . $warningtext . "</div>";
		}?>
	
	<div class="VerticalNav">
	<ul>
	<?php
	
	if(!$editing)
		{?>
		
		

		<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_email.php?ref=<?php echo urlencode($ref) ?>"><?php echo $lang["emailcollection"]?></a></li>

		<li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_share.php?ref=<?php echo urlencode($ref) ?>&generateurl=true"><?php echo $lang["generateurl"]?></a></li>

		<?php hook("extra_share_options");
		}
	if (getval("generateurl","")!="" || $editing)
		{
		if (!($hide_internal_sharing_url) && !$editing)
			{
			?>
			<p><?php echo $lang["generateurlinternal"]?></p>
			
			<p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/?c=<?php echo urlencode($ref) ?>">
			<?php
			}
			
		$access=getvalescaped("access","");
		$expires=getvalescaped("expires","");
		if ($access=="" || $editing)
			{
			?>
			<p><?php if (!$editing){echo $lang["selectgenerateurlexternal"];} ?></p>
			
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
			<?php 
			if (!$editing)
				{?>
				<input name="generateurl" type="submit" value="&nbsp;&nbsp;<?php echo $lang["generateexternalurl"]?>&nbsp;&nbsp;" />
				<?php 
				}
			else
				{?>
				<input name="editexternalurl" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
				<?php
				}?>
			</div>
			<?php
			}
		else if (getvalescaped("editaccess","")=="")
			{
			# Access has been selected. Generate a new URL.
			?>
			<p><?php echo $lang["generateurlexternal"]?></p>
		
			<p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/?c=<?php echo urlencode($ref) ?>&k=<?php echo generate_collection_access_key($ref,0,"URL",$access,$expires)?>">
			<?php
			}
		# Process editing of external share
		if (getval("editexternalurl","")!="")
			{
			$editsuccess=edit_collection_external_access($editaccess,$access,$expires);
			if($editsuccess){echo $lang['saved'];}
			}
		}

?>
<?php hook("collectionshareoptions") ?>
</ul>
</div>

<?php if (collection_writeable($ref)||
	(isset($collection['savedsearch']) && $collection['savedsearch']!=null && ($userref==$collection["user"] || checkperm("h"))))
	{
	if (!($hide_internal_sharing_url) && !$editing)
		{
		?>
		<h2><?php echo $lang["internalusersharing"]?></h2>
		<div class="Question">
		<label for="users"><?php echo $lang["attachedusers"]?></label>
		<div class="Fixed"><?php echo (($collection["users"]=="")?$lang["noattachedusers"]:htmlspecialchars($collection["users"])); ?><br /><br />
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/collection_edit.php?ref=<?php echo urlencode($ref); ?>">&gt;&nbsp;<?php echo $lang["action-edit"];?></a>
		</div>
		<div class="clearerleft"> </div>
		</div>
		
		<p>&nbsp;</p>
		<?php
		}
		?>
	<h2><?php echo $lang["externalusersharing"]?></h2>
	<div class="Question">

	<?php
	$keys=get_collection_external_access($ref);
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
		<td><?php echo $lang["sharedwith"];?></td>
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
			<td><div class="ListTitle"><a target="_blank" href="<?php echo $baseurl . "?c=" . urlencode($ref) . "&k=" . urlencode($keys[$n]["access_key"]) ?>"><?php echo htmlspecialchars($keys[$n]["access_key"]) ?></a></div></td>
			<td><?php echo htmlspecialchars(resolve_users($keys[$n]["users"]))?></td>
			<td><?php echo htmlspecialchars($keys[$n]["emails"]) ?></td>
			<td><?php echo htmlspecialchars(nicedate($keys[$n]["maxdate"],true));	?></td>
			<td><?php echo htmlspecialchars(nicedate($keys[$n]["lastused"],true)); ?></td>
			<td><?php echo htmlspecialchars(($keys[$n]["expires"]=="")?$lang["never"]:nicedate($keys[$n]["expires"],false)) ?></td>
			<td><?php echo htmlspecialchars(($keys[$n]["access"]==-1)?"":$lang["access" . $keys[$n]["access"]]); ?></td>
			<td><div class="ListTools">
			<a href="#" onClick="if (confirm('<?php echo $lang["confirmdeleteaccess"]?>')) {document.getElementById('deleteaccess').value='<?php echo htmlspecialchars($keys[$n]["access_key"]) ?>';document.getElementById('collectionform').submit(); }">&gt;&nbsp;<?php echo $lang["action-delete"]?></a>
			<a href="#" onClick="document.getElementById('editaccess').value='<?php echo htmlspecialchars($keys[$n]["access_key"]) ?>';document.getElementById('editexpiration').value='<?php echo htmlspecialchars($keys[$n]["expires"]) ?>';document.getElementById('editaccesslevel').value='<?php echo htmlspecialchars($keys[$n]["access"]) ?>';CentralSpacePost(document.getElementById('collectionform'),true);">&gt;&nbsp;<?php echo $lang["action-edit"]?></a>
			</div></td>
			</tr>
			<?php
			}
		?>
		</table>
		</div>
		<?php
		}
	?>
	</div>	
	
	<?php
	}
?>

</form>
</div>

<?php
include "../include/footer.php";
?>
