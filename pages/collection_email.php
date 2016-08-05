<?php include "../include/db.php";
include "../include/authenticate.php"; #if (!checkperm("s")) {exit ("Permission denied.");}
include "../include/general.php";
include "../include/collections_functions.php";
include "../include/resource_functions.php";
include "../include/search_functions.php";


$themeshare=getvalescaped("catshare","false");
$themecount=0;
if(getvalescaped("subthemes","false")!="false"){$subthemes=true;}else{$subthemes=false;}
$linksuffix="?";
$ref=getvalescaped("ref","",true);
if ($themeshare!="false")
	{
	$themeshare=true;
	# came here from theme category share page
	$themes=array("");
	reset($_POST);reset($_GET);
	foreach (array_merge($_GET, $_POST) as $key=>$value) 
		{
		// only set necessary vars
		if (substr($key,0,5)=="theme" && $value!=""){
			$themes[$themecount]=urldecode($value);
			$themecount++;
			}
		}
	for ($x=0;$x<count($themes);$x++){
		if ($x!=0){ $linksuffix.="&"; }
		$linksuffix.="theme" . ($x+1);
		$linksuffix.="=". urlencode($themes[$x]);
		$themename=$themes[$x];
	}
	$collectionstoshare=get_themes($themes,$subthemes);
	foreach($collectionstoshare as $collection)
		{
		if ($ref!=""){$ref.=", ";}
		$ref.=$collection["ref"];
		}		
	$ref=explode(", ",$ref);$ref=array_unique($ref);$ref=implode(", ",$ref);
	}
else
	{
	$themeshare=false;
	$themename="";
	# Fetch collection data
	if (!is_numeric($ref)) ##  multiple collections may be referenced
		{
		$refArray = explode(',',$ref);
		$collection=get_collection($refArray[0]);if ($collection===false) {exit("Collection not found.");}
		}
	else {
	$collection=get_collection($ref);if ($collection===false) {exit("Collection not found.");}
		}
	}
	
#Check if sharing allowed
if (!$allow_share) {
        $show_error=true;
        $error=$lang["error-permissiondenied"];
        }
	
#Check if any resources are not approved
if (!$collection_allow_not_approved_share && !is_collection_approved($ref))
	{	
	$show_error=true;
    $error=$lang["notapprovedsharecollection"];
	}
	
# Get min access to this collection
$minaccess=collection_min_access($ref);

if ($minaccess>=1 && !$restricted_share) # Minimum access is restricted or lower and sharing of restricted resources is not allowed. The user cannot share this collection.
	{
	$show_error=true;
    $error=$lang["restrictedsharecollection"];
	}
	
if (isset($show_error)){?>
    <script type="text/javascript">
    alert('<?php echo $error;?>');
        history.go(-1);
    </script><?php
    exit();}
	
$errors="";
if (getval("save","")!="")
	{
	# Email / share collection
	# Build a new list and insert
	$users=getvalescaped("users","");
	$message=getvalescaped("message","");
	$access=getvalescaped("access",-1);
	$expires=getvalescaped("expires","");	
	$feedback=getvalescaped("request_feedback","");	if ($feedback=="") {$feedback=false;} else {$feedback=true;}
	
	$use_user_email=getvalescaped("use_user_email",false);
	if ($use_user_email){$user_email=$useremail;} else {$user_email="";} // if use_user_email, set reply-to address
	if (!$use_user_email){$from_name=$applicationname;} else {$from_name=$userfullname;} // make sure from_name matches email
	
	if (getval("ccme",false)){ $cc=$useremail;} else {$cc="";}
	$errors=email_collection($ref,i18n_get_collection_name($collection),$userfullname,$users,$message,$feedback,$access,$expires,$user_email,$from_name,$cc,$themeshare,$themename,$linksuffix);

	if ($errors=="")
		{
		# Log this	
		// fix for bomb on multiple collections, daily stat object ref must be a single number.
		$crefs=explode(",",$ref);
		foreach ($crefs as $cref){		
			daily_stat("E-mailed collection",$cref);
		}
		if (!hook("replacecollectionemailredirect")){
			redirect($baseurl_short."pages/done.php?text=collection_email");
			}
		}
	}


if ($collection_dropdown_user_access_mode){
$users=get_users();
}

include "../include/header.php";
?>
<div class="BasicsBox">
<h1><?php if ($themeshare){echo $lang["email_theme_category"];} else {echo $lang["emailcollection"];}?></h1>

<p><?php 
if ($themeshare && text("introtextthemeshare")!="")
	{echo text("introtextthemeshare");}
else 
	{echo text("introtext");}?>
</p>

<form name="collectionform" method=post id="collectionform" action="<?php echo $baseurl_short?>pages/collection_email.php<?php echo $linksuffix ?>&catshare=<?php if($themeshare==true){echo "true";}else{echo "false";}?>">
<input type=hidden name=redirect id=redirect value=yes>
<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">
<?php if ($email_multi_collections && !$themeshare) { ?>
<script type="text/javascript">
   function getSelected(opt) {
      var sel = '';
	  var newref = '';
      var index = 0;
      for (var intLoop=0; intLoop < opt.length; intLoop++) { 
         if (opt[intLoop].selected) 
		 {  sel = sel + ', ' +  '<?php echo $collection_prefix?>' + opt[intLoop].value;
		 	newref = newref + ',' +  opt[intLoop].value;
		 }
      }
	  document.collectionform.ref.value = newref.substring(1, newref.length );
      return sel.substring(2, sel.length );
   }
</script>
<?php } 


if ($themeshare)
	{?>
	<div class="Question">
		<label for="subthemes"><?php echo $lang["share_theme_category_subcategories"]?></label>
		<input type="checkbox" id="subthemes" name="subthemes" value="true" <?php if ($subthemes){echo "checked";} ?>>
		<div class="clearerleft"> </div>
	</div>
	<?php
	}
else
	{?>	
	<div class="Question">
	<label><?php if ($themeshare) {echo $lang["themes"];} else {echo $lang["collectionname"];}?></label><div class="Fixed"><?php 
		if (!$email_multi_collections &&  !$themeshare) { 
			echo i18n_get_collection_name($collection);
		} else { ##  this select copied from collections.php 
			
			?>		
			<select name="collection" multiple="multiple" size="10" class="SearchWidthExt" style="width:365px;" 
				onchange="document.getElementById('refDiv').innerHTML = getSelected(this); " >
			<?php
			
			$list=get_user_collections($userref);
			$found=false;
			for ($n=0;$n<count($list);$n++)
				{

				if ($collection_dropdown_user_access_mode){    
					foreach ($users as $user){
						if ($user['ref']==$list[$n]['user']){$colusername=$user['fullname'];}
					}
					# Work out the correct access mode to display
					if (!hook('collectionaccessmode')) {
						if ($list[$n]["public"]==0){
							$accessmode= $lang["private"];
						}
						else{
							if (strlen($list[$n]["theme"])>0){
								$accessmode= $lang["theme"];
							}
						else{
								$accessmode= $lang["public"];
							}
						}
					}
				}


					?>	
				<option value="<?php echo $list[$n]["ref"]?>" <?php if ($ref==$list[$n]["ref"]) {?> 	selected<?php $found=true;} ?>><?php echo i18n_get_collection_name($list[$n]) ?><?php if ($collection_dropdown_user_access_mode){echo "&nbsp;&nbsp;".htmlspecialchars("(". $colusername."/".$accessmode.")"); } ?></option>
				<?php 
				}
			if ($found==false)
				{
				# Add this one at the end, it can't be found
				$notfound=get_collection($ref);
				if ($notfound!==false)
					{
					?>
					<option value="<?php echo urlencode($ref) ?>" selected><?php echo $notfound["name"]?></option>
					<?php
					}
				}
			
			?>
			</select> <?php } ?>
			</div>
			<div class="clearerleft"> </div>
			</div>
	<?php }?>
<div class="Question">
<label for="message"><?php echo $lang["message"]?></label><textarea class="stdwidth" rows=6 cols=50 name="message" id="message"></textarea>
<div class="clearerleft"> </div>
</div>

<?php if(!hook("replaceemailtousers")){?>
<div class="Question">
<label for="users"><?php echo $lang["emailtousers"]?></label><?php $userstring=getval("users","");include "../include/user_select.php"; ?>
<div class="clearerleft"> </div>
<?php if ($errors!="") { ?><div class="FormError">!! <?php echo $errors?> !!</div><?php } ?>
</div>
<?php } #end hook replaceemailtousers ?>

<?php if(!hook("replaceemailaccessselector")){?>
<div class="Question" id="question_access">
<label for="archive"><?php echo $lang["externalselectresourceaccess"]?></label>
<select class="stdwidth" name="access" id="access">
<?php
# List available access levels. The highest level must be the minimum user access level.
for ($n=$minaccess;$n<=1;$n++) { ?>
<option value="<?php echo $n?>"><?php echo $lang["access" . $n]?></option>
<?php } ?>
</select>
<div class="clearerleft"> </div>
</div>
<?php } # end hook replaceemailaccessselector ?>

<?php if(!hook("replaceemailexpiryselector")){?>
<div class="Question">
<label><?php echo $lang["externalselectresourceexpires"]?></label>
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
<?php } # end hook replaceemailexpiryselector ?>

<?php if ($collection["user"]==$userref) { # Collection owner can request feedback.
?>
<?php if(!hook("replaceemailrequestfeedback")){?>
<div class="Question">
<label for="request_feedback"><?php echo $lang["requestfeedback"]?></label><input type=checkbox id="request_feedback" name="request_feedback" value="yes">
<div class="clearerleft"> </div>
</div>
<?php } # end hook replaceemailrequestfeedback ?>
<?php } ?>

<?php if ($email_from_user && !$always_email_from_user){?>
<?php if ($useremail!="") { # Only allow this option if there is an email address available for the user.
?>
<div class="Question">
<label for="use_user_email"><?php echo $lang["emailfromuser"].$useremail.". ".$lang["emailfromsystem"].$email_from ?></label><input type=checkbox checked id="use_user_email" name="use_user_email">
<div class="clearerleft"> </div>
</div>
<?php } ?>
<?php } ?>

<?php if ($cc_me && $useremail!=""){?>
<div class="Question">
<label for="ccme"><?php echo str_replace("%emailaddress", $useremail, $lang["cc-emailaddress"]); ?></label><input type=checkbox checked id="ccme" name="ccme">
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php hook("additionalemailfield");?>

<?php if(!hook("replaceemailsubmitbutton")){?>
<div class="QuestionSubmit">
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php if ($themeshare){echo $lang["email_theme_category"];} else {echo $lang["emailcollection"];}?>&nbsp;&nbsp;" />
</div>
<?php } # end hook replaceemailsubmitbutton ?>

</form>
</div>

<?php include "../include/footer.php";
?>
