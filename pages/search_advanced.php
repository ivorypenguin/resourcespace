<?php
include "../include/db.php";
include "../include/authenticate.php"; if (!checkperm("s")) {exit ("Permission denied.");}
include "../include/general.php";
include "../include/search_functions.php";
include "../include/collections_functions.php";

$archive=getvalescaped("archive",0,true);
$starsearch=getvalescaped("starsearch","");	
setcookie("starsearch",$starsearch);
if (!isset($_COOKIE["advancedsearchsection"])) 
    {
     if (isset($default_advanced_search_mode)) $opensection=$default_advanced_search_mode;
     else  $opensection="Global";
    }
else $opensection=$_COOKIE["advancedsearchsection"];

# Disable auto-save function, only applicable to edit form. Some fields pick up on this value when rendering then fail to work.
$edit_autosave=false;

if (getval("submitted","")=="yes" && getval("resetform","")=="")
	{
	$restypes="";
	reset($_POST);foreach ($_POST as $key=>$value)
		{
		if (substr($key,0,12)=="resourcetype") {if ($restypes!="") {$restypes.=",";} $restypes.=substr($key,12);}
		if ($key=="hiddenfields") 
		    {
		    $hiddenfields=$value;
		 
		
		    }
		}
	setcookie("restypes",$restypes);
		
	# advanced search - build a search query and redirect
	$fields=array_merge(get_advanced_search_fields(false, $hiddenfields ),get_advanced_search_collection_fields(false, $hiddenfields ));
  
	# Build a search query from the search form
	$search=search_form_to_search_query($fields);
	$search=refine_searchstring($search);
	
    
	hook("moresearchcriteria");

	if (getval("countonly","")!="")
		{
        
		# Only show the results (this will appear in an iframe)
		if (count($search)==0)
			{
			$count=0;
            
			}
		else
			{
			#debug("restypes:".$restypes."=".$search.";".substr($restypes,0,11));
			if (substr($restypes,0,11)!="Collections")
			    $result=do_search($search,$restypes,"relevance",$archive,1,"",false,$starsearch);
			else 
			    $result=do_collections_search($search,$restypes,$archive);
			if (is_array($result))
				{
				$count=count($result);
				}
			else
				{
				$count=0;
				
				
				}
			}
		?>
		<html>
		<script type="text/javascript">
	
		
		<?php if ($count==0) { ?>
		//parent.document.getElementById("dosearch").disabled=true;
		parent.document.getElementById("dosearch").value="<?php echo $lang["nomatchingresults"]?>";
		<?php } else { ?>
		parent.document.getElementById("dosearch").value="<?php echo $lang["view"] . " " . number_format($count) . " " . $lang["matchingresults"] ?>";
		parent.document.getElementById("dosearch").disabled=false;
		<?php } ?>
		</script>
		</html>
		<?php
		exit();
		}
	else
		{
		# Log this			
		daily_stat("Advanced search",$userref);

		redirect($baseurl_short."pages/search.php?search=" . urlencode($search) . "&archive=" . $archive);
		}
	}



# Reconstruct a values array based on the search keyword, so we can pre-populate the form from the current search
$search=@$_COOKIE["search"];
$keywords=explode(",",$search);
$allwords="";$found_year="";$found_month="";$found_day="";$found_start_date="";$found_end_date="";
$values=array();
for ($n=0;$n<count($keywords);$n++)
	{
	$keyword=$keywords[$n];
	if (strpos($keyword,":")!==false)
		{
		$nk=explode(":",$keyword);
		$name=trim($nk[0]);
		$keyword=trim($nk[1]);
		if ($name=="day") {$found_day=$keyword;}
		if ($name=="month") {$found_month=$keyword;}
		if ($name=="year") {$found_year=$keyword;}
		if ($name=="startdate") {$found_start_date=$keyword;}
		if ($name=="enddate") {$found_end_date=$keyword;}
		if (isset($values[$name])){$values[$name].=" ".$keyword;}
		else {
			$values[$name]=$keyword;
		}
		}
	else
		{
		if ($allwords=="") {$allwords=$keyword;} else {$allwords.=", " . $keyword;}
		}
	}
$allwords=str_replace(", ","",$allwords);

if (getval("resetform","")!="") {$found_year="";$found_month="";$found_day="";$found_start_date="";$found_end_date="";$allwords="";$starsearch="";}

function render_advanced_search_buttons() {
 global $lang;
 ?><div class="QuestionSubmit">
 <label for="buttons"> </label>
 <input name="resetform" id="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />
 &nbsp;
 <input name="dosearch" id="dosearch" type="submit" value="<?php echo $lang["action-viewmatchingresults"]?>" />
 </div>

 <?php 
 }


include "../include/header.php";
?>
<script type="text/javascript">
 

jQuery(document).ready(function()
    {

    jQuery('.SearchTypeCheckbox').change(function() 
        {
        id=(this.name).substr(12);
       
        if (jQuery(this).is(":checked")) 
            {
            jQuery('.SearchTypeCheckbox').removeAttr('checked');
            jQuery(this).attr('checked','checked');
            jQuery('.AdvancedSectionHead').hide();
            jQuery('.AdvancedSection').hide();
            SetCookie("advancedsearchsection", id);
            
            if (getCookie('AdvancedSearchTypeSpecificSection'+id)!="collapsed") jQuery('#AdvancedSearchTypeSpecificSection'+id).show();
            jQuery('#AdvancedSearchTypeSpecificSection'+id+'Head').show();
            
            if (id=="Collections") 
                {
                jQuery("#AdvancedSearchTypeSpecificSectionGlobalHead").hide();
                jQuery("#AdvancedSearchTypeSpecificSectionGlobal").hide()
                }
            else 
               {
                jQuery("#AdvancedSearchTypeSpecificSectionGlobalHead").show();
                if (getCookie('AdvancedSearchTypeSpecificSectionGlobal')!="collapsed") jQuery("#AdvancedSearchTypeSpecificSectionGlobal").show()
                }
            }
//        else 
//            {
//            jQuery('.SearchTypeCheckbox').removeAttr('checked');
//            jQuery('.AdvancedSectionHead').hide();
//            jQuery('.AdvancedSection').hide();
//           
//            if (id=="Collections") 
//                {
//                jQuery("#AdvancedSearchTypeSpecificSectionGlobalHead").show();
//                jQuery("#AdvancedSearchTypeSpecificSectionGlobal").show()
//
//                }
//            jQuery('#AdvancedSearchTypeSpecificSectionGlobal').show();
//            jQuery('#AdvancedSearchTypeSpecificSectionGlobalHead').show();
//            SetCookie("advancedsearchsection", "Global");
//             jQuery('#SearchGlobal').attr('checked','checked');
//            }
        
        
        UpdateResultCount();
        });
    jQuery('.CollapsibleSectionHead').click(function() 
            {
            cur=jQuery(this).next();
            cur_id=cur.attr("id");
            if (cur.is(':visible'))
                {
                SetCookie(cur_id, "collapsed");
                jQuery(this).removeClass('expanded');
                jQuery(this).addClass('collapsed');
                }
            else
                {
                SetCookie(cur_id, "expanded")
                jQuery(this).addClass('expanded');
                jQuery(this).removeClass('collapsed');
                }
    
            cur.slideToggle();
           
            
            return false;
            }).each(function() 
                {
                    cur_id=jQuery(this).next().attr("id"); 
                    if (getCookie(cur_id)=="collapsed")
                        {
                        jQuery(this).next().hide();
                        jQuery(this).addClass('collapsed');
                        }
                    else jQuery(this).addClass('expanded');
    
                });
    
    });
</script>
<div class="BasicsBox">
<h1><?php echo ($archive==0)?$lang["advancedsearch"]:$lang["archiveonlysearch"]?> </h1>
<p class="tight"><?php echo text("introtext")?></p>
<form method="post" id="advancedform" action="<?php echo $baseurl ?>/pages/search_advanced.php" >
<input type="hidden" name="submitted" id="submitted" value="yes">
<input type="hidden" name="countonly" id="countonly" value="">

<script type="text/javascript">
var updating=false;
function UpdateResultCount()
	{
	updating=false;
	// set the target of the form to be the result count iframe and submit
	document.getElementById("advancedform").target="resultcount";
	document.getElementById("countonly").value="yes";
	
	
	jQuery("#advancedform").submit();
	document.getElementById("advancedform").target="";
	document.getElementById("countonly").value="";
	}
	
jQuery(document).ready(function(){
	    jQuery('#advancedform').submit(function() {
            if (jQuery('#AdvancedSearchTypeSpecificSectionCollections').is(":hidden") && (document.getElementById("countonly").value!="yes")) 
                {
                    jQuery('.tickboxcoll').removeAttr('checked');
                }
	       var inputs = jQuery('#advancedform :input');
	       var hiddenfields = Array();
	       inputs.each(function() {

	           if (jQuery(this).parent().is(":hidden")) hiddenfields.push((this.name).substr(6));
	           
	       });
	      jQuery("#hiddenfields").val(hiddenfields.toString());
	    
    	    
    	    	
	    });
		jQuery('.Question').easyTooltip({
			xOffset: -50,
			yOffset: 40,
			charwidth: 50,
			tooltipId: "advancedTooltip",
			cssclass: "ListviewStyle"
			});
		});

</script>

<?php
if($advanced_search_buttons_top)
 {
 render_advanced_search_buttons();
 }

if(!hook("advsearchrestypes")): ?>
<div class="Question">
<label><?php echo $lang["search-mode"]?></label><?php
$rt=explode(",",getvalescaped("restypes",""));
$types=get_resource_types();
$wrap=0;
?><table><tr>
<td valign=middle><input type=radio class="SearchTypeCheckbox" id="SearchGlobal" name="resourcetypeGlobal" value="yes" <?php if ($opensection=="Global") { ?>checked<?php }?>></td><td valign=middle><?php echo $lang["resources-all-types"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<?php
$hiddentypes=Array();
for ($n=0;$n<count($types);$n++)
	{
	$wrap++;if ($wrap>4) {$wrap=1;?></tr><tr><?php }
	?><td valign=middle><input type=radio class="SearchTypeCheckbox" name="resourcetype<?php echo $types[$n]["ref"]?>" value="yes" <?php if ($opensection==$types[$n]["ref"]) { ?>checked<?php } else $hiddentypes[]=$types[$n]["ref"]; ?>></td><td valign=middle><?php echo htmlspecialchars($types[$n]["name"])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><?php	
	}
?>
<?php if ($search_includes_user_collections || $search_includes_public_collections ||$search_includes_themes)
    {
?>
<td valign=middle><input type=radio class="SearchTypeCheckbox" name="resourcetypeCollections" value="yes" <?php if ($opensection=="Collections") { ?>checked<?php }?>></td><td valign=middle><?php print $lang["collections"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
<?php
    }
?>
</tr></table>
<div class="clearerleft"> </div>
</div>
<?php endif; ?>
<!-- Search across all fields -->
<input type="hidden" id="hiddenfields" name="hiddenfields" value="">
<div class="Question">
<label for="allfields"><?php echo $lang["allfields"]?></label><input class="stdwidth" type=text name="allfields" id="allfields" value="<?php echo htmlspecialchars($allwords)?>" onChange="UpdateResultCount();">
<div class="clearerleft"> </div>
</div>

<h1 class="AdvancedSectionHead CollapsibleSectionHead" id="AdvancedSearchTypeSpecificSectionGlobalHead" <?php if ($opensection=="Collections") {?> style="display: none;" <?php } ?>><?php echo $lang["resourcetype-global_fields"]; ?></h1>
<div class="AdvancedSection" id="AdvancedSearchTypeSpecificSectionGlobal" <?php if ($opensection=="Collections") {?> style="display: none;" <?php } ?>>
 
<!-- Search for resource ID(s) -->
<div class="Question">
<label for="resourceids"><?php echo $lang["resourceids"]?></label><input class="stdwidth" type=text name="resourceids" id="resourceids" value="<?php echo htmlspecialchars(getval("resourceids","")) ?>" onChange="UpdateResultCount();">
<div class="clearerleft"> </div>
</div>

<div class="Question"><label><?php echo $lang["bydate"]?></label>
<?php 
if ($daterange_search)
	{ ?>
	<!--  date range search -->
	<div><label class="InnerLabel"><?php echo $lang["fromdate"]?></label>
	<select name="startyear" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyyear"]?></option>
	  <?php
	  $y=date("Y");
	  for ($n=$minyear;$n<=$y;$n++)
		{
		?><option <?php if ($n==substr($found_start_date,0,4)) { ?>selected<?php } ?>><?php echo $n?></option><?php
		}
	  ?>
	</select>
	<select name="startmonth" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anymonth"]?></option>
	  <?php
	  for ($n=1;$n<=12;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($found_start_date,5,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$n-1]?></option><?php
		}
	  ?>
	</select>
	<select name="startday" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyday"]?></option>
	  <?php
	  for ($n=1;$n<=31;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($found_start_date,8,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
		}
	  ?>
	</select>
	<!-- end date -->	
	</div><br><div><label></label><label class="InnerLabel"><?php echo $lang["todate"]?></label>
	<select name="endyear" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyyear"]?></option>
	  <?php
	  $y=date("Y");
	  for ($n=$minyear;$n<=$y;$n++)
		{
		?><option <?php if ($n==substr($found_end_date,0,4)) { ?>selected<?php } ?>><?php echo $n?></option><?php
		}
	  ?>
	</select>
	<select name="endmonth" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anymonth"]?></option>
	  <?php
	  for ($n=1;$n<=12;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($found_end_date,5,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$n-1]?></option><?php
		}
	  ?>
	</select>
	<select name="endday" class="SearchWidth" style="width:98px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyday"]?></option>
	  <?php
	  for ($n=1;$n<=31;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==substr($found_end_date,8,2)) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
		}
	  ?>
	</select>
	</div>

	<!--  end of date range search -->

	<?php
	}
else
	{
	?>
	
	<select name="year" class="SearchWidth" style="width:100px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyyear"]?></option>
	  <?php
	  $y=date("Y");
	  for ($n=$minyear;$n<=$y;$n++)
		{
		?><option <?php if ($n==$found_year) { ?>selected<?php } ?>><?php echo $n?></option><?php
		}
	  ?>
	</select>
	<select name="month" class="SearchWidth" style="width:100px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anymonth"]?></option>
	  <?php
	  for ($n=1;$n<=12;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==$found_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$n-1]?></option><?php
		}
	  ?>
	</select>
	<select name="day" class="SearchWidth" style="width:100px;" onChange="UpdateResultCount();">
	  <option value=""><?php echo $lang["anyday"]?></option>
	  <?php
	  for ($n=1;$n<=31;$n++)
		{
		$m=str_pad($n,2,"0",STR_PAD_LEFT);
		?><option <?php if ($n==$found_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
		}
	  ?>
	</select>

	<?php } ?>


<div class="clearerleft"> </div>
</div>
<?php if ($star_search && $display_user_rating_stars){?>
<div class="Question"><label><?php echo $lang["starsminsearch"];?></label>
<select id="starsearch" name="starsearch" class="stdwidth" onChange="UpdateResultCount();">
<option value=""><?php echo $lang['anynumberofstars']?></option>
<?php for ($n=1;$n<=5;$n++){?>
	 <option value="<?php echo $n;?>" <?php if ($n==$starsearch){?>selected<?php } ?>><?php for ($x=0;$x<$n;$x++){?>&#9733;<?php } ?></option>
<?php } ?>
</select>
<div class="clearerleft"> </div>
</div>
<?php } ?>

<?php hook('advsearchaddfields'); ?>

<iframe src="blank.html" name="resultcount" id="resultcount" style="visibility:hidden;" width=1 height=1></iframe>
<?php
# Fetch fields
$fields=get_advanced_search_fields($archive>0);
$showndivide=-1;

# Preload resource types
$rtypes=get_resource_types();

for ($n=0;$n<count($fields);$n++)
	{
	# Show a dividing header for resource type specific fields?
	if (($fields[$n]["resource_type"]!=0) && ($showndivide!=$fields[$n]["resource_type"]))
		{
		$showndivide=$fields[$n]["resource_type"];
		$label="??";
		# Find resource type name
		for ($m=0;$m<count($rtypes);$m++)
			{
			# Note: get_resource_types() has already translated the resource type name for the current user.
			if ($rtypes[$m]["ref"]==$fields[$n]["resource_type"]) {$label=$rtypes[$m]["name"];}
			}
		?>
		</div><h1 class="AdvancedSectionHead CollapsibleSectionHead" id="AdvancedSearchTypeSpecificSection<?php echo $fields[$n]["resource_type"]; ?>Head" <?php if ($opensection!=$fields[$n]["resource_type"]) {?> style="display: none;" <?php } ?>><?php echo $lang["typespecific"] . ": " . $label ?></h1>
		<div class="AdvancedSection" id="AdvancedSearchTypeSpecificSection<?php echo $fields[$n]["resource_type"]; ?>" <?php if ($opensection!=$fields[$n]["resource_type"]) {?> style="display: none;" <?php } ?>>
		<?php

		}

	# Work out a default value
	if (array_key_exists($fields[$n]["name"],$values)) {$value=$values[$fields[$n]["name"]];} else {$value="";}
	if (getval("resetform","")!="") {$value="";}
	
	# Render this field
	render_search_field($fields[$n],$value,true);

	}
?>
</div>
<?php if  ($search_includes_user_collections || $search_includes_public_collections || $search_includes_themes) { ?>
<h1 class="AdvancedSectionHead" id="AdvancedSearchTypeSpecificSectionCollectionsHead" <?php if ($opensection!="Collections") {?> style="display: none;" <?php } ?>><?php echo $lang["collections"]; ?></h1>
<div class="AdvancedSection" id="AdvancedSearchTypeSpecificSectionCollections" <?php if ($opensection!="Collections") {?> style="display: none;" <?php } ?>>

<script type="text/javascript">	
function resetTickAllColl(){
	var checkcount=0;
	// set tickall to false, then check if it should be set to true.
	jQuery('#rttickallcoll').attr('checked',false);
	var tickboxes=jQuery('#advancedform .tickboxcoll');
		jQuery(tickboxes).each(function (elem) {
            if( tickboxes[elem].checked){checkcount=checkcount+1;}
        });
	if (checkcount==tickboxes.length){jQuery('#rttickallcoll').attr('checked',true);}	
}
</script>
<div class="Question">
<label><?php echo $lang["scope"]?></label><?php

$types=get_resource_types();
$wrap=0;
?><table><tr>
<td align="middle"><input type='checkbox' id='rttickallcoll' name='rttickallcoll' checked onclick='jQuery("#advancedform .tickboxcoll").each (function(index,Element) {jQuery(Element).attr("checked",(jQuery("#rttickallcoll").attr("checked")=="checked"));}); UpdateResultCount(); ' />&nbsp;<?php echo $lang['allcollectionssearchbar']?></td>

<?php

$clear_function="";
if ($search_includes_user_collections) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxMyCol" type="checkbox" name="resourcetypemycol" value="yes" <?php if (((count($rt)==1) && ($rt[0]=="")) || (in_array("mycol",$rt))) {?>checked="true"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/>&nbsp;<?php echo $lang["mycollections"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxMyCol').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
if ($search_includes_public_collections) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxPubCol" type="checkbox" name="resourcetypepubcol" value="yes" <?php if (((count($rt)==1) && ($rt[0]=="")) || (in_array("pubcol",$rt))) {?>checked="true"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/>&nbsp;<?php echo $lang["findpubliccollection"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxPubCol').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
if ($search_includes_themes) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxThemes" type="checkbox" name="resourcetypethemes" value="yes" <?php if (((count($rt)==1) && ($rt[0]=="")) || (in_array("themes",$rt))) {?>checked="true"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/>&nbsp;<?php echo $lang["findcollectionthemes"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxThemes').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
?>
</tr></table></div>
<script type="text/javascript">resetTickAllColl();</script>
<?php

$fields=get_advanced_search_collection_fields();
for ($n=0;$n<count($fields);$n++)
	{
	# Work out a default value
	if (array_key_exists($fields[$n]["name"],$values)) {$value=$values[$fields[$n]["name"]];} else {$value="";}
	if (getval("resetform","")!="") {$value="";}
	# Render this field
	render_search_field($fields[$n],$value,true);
	}

?>
</div>

<?php
}

global $advanced_search_archive_select;
if($advanced_search_archive_select)
	{
	?>
	<div class="Question">
		<label><?php echo $lang["status"]?></label>
		<select class="stdwidth" name="archive" id="archive" onChange="UpdateResultCount();">
			<?php 
			for ($n=-2;$n<=3;$n++)
				{
				if (!checkperm("z" . $n)) { ?><option value="<?php echo $n?>" <?php if ($archive==$n) { ?>selected<?php } ?>><?php echo $lang["status" . $n]?></option><?php }
				}
			foreach ($additional_archive_states as $additional_archive_state)
				{
				if (!checkperm("z" . $additional_archive_state)) { ?><option value="<?php echo $additional_archive_state?>" <?php if ($archive==$additional_archive_state) { ?>selected<?php } ?>><?php echo isset($lang["status" . $additional_archive_state])?$lang["status" . $additional_archive_state]:$additional_archive_state ?></option><?php }
				}			
			?>

		</select>
	</div>
	<?php
	}
else
	{?>
	<input type="hidden" name="archive" value="<?php echo htmlspecialchars($archive)?>">
	<?php
	}
	
render_advanced_search_buttons();

// show result count as it stands ?>
<script type="text/javascript">UpdateResultCount();</script>	
<?php if ($swap_clear_and_search_buttons){?>
<script type="text/javascript">
	jQuery("#resetform").before(jQuery("#dosearch"));
</script>
<?php } ?>
<?php
include "../include/footer.php";
?>
