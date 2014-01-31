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

# Work out the access to the resource, which is the minimum permitted share level.
$minaccess=get_resource_access($ref);
if ($minaccess>=1 && !$restricted_share) # Minimum access is restricted or lower and sharing of restricted resources is not allowed. The user cannot share this collection.
        {
        $show_error=true;
        $error=$lang["restrictedsharecollection"];
        }
        
# Process deletion of access keys
if (getval("deleteaccess","")!="")
    {
    delete_resource_access_key($ref,getvalescaped("deleteaccess",""));
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
<p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p>

<h1><?php echo $lang["share-resource"] ?></h1>

<div class="BasicsBox"> 
<form method=post id="resourceshareform" action="<?php echo $baseurl_short?>pages/resource_share.php" onSubmit="return CentralSpacePost(this);">
<input type="hidden" name="ref" id="ref" value="<?php echo htmlspecialchars($ref) ?>">
<input type="hidden" name="generateurl" id="generateurl" value="">
<input type="hidden" name="deleteaccess" id="deleteaccess" value="">
            
	<div class="VerticalNav">
	<ul>

        <li><a href="<?php echo $baseurl_short?>pages/resource_email.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["emailresource"]?></a></li>

        <li><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo urlencode($ref) ?>&generateurl=true&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>"><?php echo $lang["generateurl"]?></a></li>

        <?php
        if (getval("generateurl","")!="" && getval("deleteaccess","")=="")
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
		
			<p><input class="URLDisplay" type="text" value="<?php echo $baseurl?>/?r=<?php echo urlencode($ref) ?>&k=<?php echo generate_resource_access_key($ref,$userref,$access,$expires,"URL")?>">
			<?php
			}
        ?>
        
        </ul>
        </div>

        <h2><?php echo $lang["externalusersharing"]?></h2>
	<div class="Question">
	<?php
	$keys=get_resource_external_access($ref);
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
                <td><?php echo $lang["type"] ?></td>
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
			<td><div class="ListTitle"><a target="_blank" href="<?php echo $baseurl .
                        (is_numeric($keys[$n]["collection"])?"?c=" . urlencode($keys[$n]["collection"]):"?r=" . urlencode($ref))
                        . "&k=" . urlencode($keys[$n]["access_key"]) ?>"><?php echo htmlspecialchars($keys[$n]["access_key"]) ?></a></div></td>
                        <td><?php echo (is_numeric($keys[$n]["collection"])?$lang["sharecollection"]:$lang["share-resource"]) ?></td>
			<td><?php echo htmlspecialchars(resolve_users($keys[$n]["users"]))?></td>
			<td><?php echo htmlspecialchars($keys[$n]["emails"]) ?></td>
			<td><?php echo htmlspecialchars(nicedate($keys[$n]["maxdate"],true));	?></td>
			<td><?php echo htmlspecialchars(nicedate($keys[$n]["lastused"],true)); ?></td>
			<td><?php echo htmlspecialchars(($keys[$n]["expires"]=="")?$lang["never"]:nicedate($keys[$n]["expires"],false)) ?></td>
			<td><?php echo htmlspecialchars(($keys[$n]["access"]==-1)?"":$lang["access" . $keys[$n]["access"]]); ?></td>
			<td><div class="ListTools">
                        <?php if (is_numeric($keys[$n]["collection"]))
                            {
                            ?>
                            <a onClick="return CentralSpaceLoad(this,true);" href="collection_share.php?ref=<?php echo $keys[$n]["collection"] ?>">&gt;&nbsp;<?php echo $lang["viewcollection"]?></a>
                            <?php
                            }
                        else
                            {
                            ?>
                            <a href="#" onClick="if (confirm('<?php echo $lang["confirmdeleteaccessresource"]?>')) {document.getElementById('deleteaccess').value='<?php echo htmlspecialchars($keys[$n]["access_key"]) ?>';document.getElementById('resourceshareform').submit(); }">&gt;&nbsp;<?php echo $lang["action-delete"]?></a>      
                            <?php
                            }
                        ?>
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
	        
        
        
        
        
</form>
</div>


<?php

include "../include/footer.php";
?>