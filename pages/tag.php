<?php
include "../include/db.php";
include "../include/authenticate.php"; if (!checkperm("n")) {exit("Permission denied");}
include "../include/general.php";
include "../include/resource_functions.php";

if (!$speedtagging) {exit("This function is not enabled.");}

if (getval("save","")!="")
	{
	$ref=getvalescaped("ref","",true);
	$keywords=getvalescaped("keywords","");
	
	# support resource_type based tag fields
	$resource_type=get_resource_data($ref);
	$resource_type=$resource_type['resource_type'];
	if (isset($speedtagging_by_type[$resource_type])){$speedtaggingfield=$speedtagging_by_type[$resource_type];}
	
	$oldval=get_data_by_field($ref,$speedtaggingfield);
	
	update_field($ref,$speedtaggingfield,$keywords);
	
	# Write this edit to the log.
	resource_log($ref,'e',$speedtaggingfield,"",$oldval,$keywords);
	}

	
# append resource type restrictions based on 'T' permission	
	# look for all 'T' permissions and append to the SQL filter.
	global $userpermissions;
	$rtfilter=array();
	$sql_join="";
	$sql_filter="";
	for ($n=0;$n<count($userpermissions);$n++)
		{
		if (substr($userpermissions[$n],0,1)=="T")
			{
			$rt=substr($userpermissions[$n],1);
			if (is_numeric($rt)) {$rtfilter[]=$rt;}
			}
		}
	if (count($rtfilter)>0)
		{
		$sql_filter.=" and r.resource_type not in (" . join(",",$rtfilter) . ")";
		}
	
	# append "use" access rights, do not show restricted resources unless admin
	if (!checkperm("v"))
		{
		$sql_filter.=" and r.access<>'2'";
		}
	# ------ Search filtering: If search_filter is specified on the user group, then we must always apply this filter.
	global $usersearchfilter;
	$sf=explode(";",$usersearchfilter);
	if (strlen($usersearchfilter)>0)
		{
		for ($n=0;$n<count($sf);$n++)
			{
			$s=explode("=",$sf[$n]);
			if (count($s)!=2) {exit ("Search filter is not correctly configured for this user group.");}

			# Support for "NOT" matching. Return results only where the specified value or values are NOT set.
			$filterfield=$s[0];$filter_not=false;
			if (substr($filterfield,-1)=="!")
				{
				$filter_not=true;
				$filterfield=substr($filterfield,0,-1);# Strip off the exclamation mark.
				}

			# Find field(s) - multiple fields can be returned to support several fields with the same name.
			$f=sql_array("select ref value from resource_type_field where name='" . escape_check($filterfield) . "'");
			if (count($f)==0) {exit ("Field(s) with short name '" . $filterfield . "' not found in user group search filter.");}
			
			# Find keyword(s)
			$ks=explode("|",strtolower(escape_check($s[1])));
			$modifiedsearchfilter=hook("modifysearchfilter");
			if ($modifiedsearchfilter){$ks=$modifiedsearchfilter;} 
			$kw=sql_array("select ref value from keyword where keyword in ('" . join("','",$ks) . "')");
			#if (count($k)==0) {exit ("At least one of keyword(s) '" . join("', '",$ks) . "' not found in user group search filter.");}
					
		    if (!$filter_not)
		    	{
		    	# Standard operation ('=' syntax)
			    $sql_join.=" join resource_keyword filter" . $n . " on r.ref=filter" . $n . ".resource and filter" . $n . ".resource_type_field in ('" . join("','",$f) . "') and filter" . $n . ".keyword in ('" . 	join("','",$kw) . "') ";	
			    }
			else
				{
				# Inverted NOT operation ('!=' syntax)
				if ($sql_filter!="") {$sql_filter.=" and ";}
				$sql_filter .= "r.ref not in (select resource from resource_keyword where resource_type_field in ('" . join("','",$f) . "') and keyword in ('" . 	join("','",$kw) . "'))"; # Filter out resources that do contain the keyword(s)
				}
			}
		}
		
		

# Fetch a resource

$ref=sql_value("select r.ref value,count(*) c from resource r left outer join resource_keyword rk on r.ref=rk.resource and rk.resource_type_field='$speedtaggingfield' $sql_join where r.has_image=1 and archive=0 $sql_filter group by r.ref  order by c,rand() limit 1",0);
if ($ref==0) {exit ("No resources to tag.");}




# Load resource data
$resource=get_resource_data($ref);

# Load existing keywords
#$existing=sql_array("select distinct k.keyword value from resource_keyword rk join keyword k on rk.keyword=k.ref where rk.resource='$ref' and length(k.keyword)>1 and k.keyword not like '%0%' and k.keyword not like '%1%' and k.keyword not like '%2%' and k.keyword not like '%3%' and k.keyword not like '%4%' and k.keyword not like '%5%' and k.keyword not like '%6%' and k.keyword not like '%7%' and k.keyword not like '%8%' and k.keyword not like '%9%' and k.keyword not like '% %' order by k.keyword");
$existing=array();

$words=sql_value("select value from resource_data where resource='$ref' and resource_type_field='$speedtaggingfield'","");

/*
# Fetch very rough 'completion status' to give some measure of progress
$complete=sql_value("select count(*) value from resource_data where resource_type_field='$speedtaggingfield' and length(value)>0",0);
$total=sql_value("select count(*) value from resource where has_image=1 and archive=0",0);
$percent=min(100,ceil($complete/max(1,$total)*100));
*/
$percent=0;


include "../include/header.php";
?>
<div class="BasicsBox"> 

<form method="post" id="mainform" action="<?php echo $baseurl_short?>pages/tag.php">
<input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref)?>">

<h1><?php echo $lang["speedtagging"]?></h1>
<p><?php echo text("introtext")?></p>

<?php 
$imagepath=get_resource_path($ref,false,"pre",false,$resource["preview_extension"]);
?>
<div class="RecordBox"><div class="RecordPanel"><img src="<?php echo $imagepath?>" alt="" class="Picture" />


<!--<div class="Question">
<label for="keywords"><?php echo $lang["existingkeywords"]?></label>
<div class="Fixed"><?php echo join(", ",$existing)?></div>
</div>-->

<div class="clearerleft"> </div>

<div class="Question">
<label for="keywords"><?php echo $lang["extrakeywords"]?></label>
<input type="text" class="stdwidth" rows=6 cols=50 name="keywords" id="keywords" value="<?php echo htmlspecialchars($words)?>">
</div>

<script type="text/javascript">
document.getElementById('keywords').focus();
</script>

<div class="QuestionSubmit">
<label for="buttons"> </label>
<input name="save" type="submit" default value="&nbsp;&nbsp;<?php echo $lang["next"]?>&nbsp;&nbsp;" />
</div>

<div class="clearerleft"> </div>
</div></div>

<!--<p>Thanks for helping. The speed tagging project is <?php echo $percent?>% complete.</p>-->

<p><?php echo $lang["leaderboard"]?><table>
<?php
$lb=sql_query("select u.fullname,count(*) c from user u join resource_log rl on rl.user=u.ref where rl.resource_type_field='$speedtaggingfield' group by u.ref order by c desc limit 5;");
for ($n=0;$n<count($lb);$n++)
	{
	?>
	<tr><td><?php echo $lb[$n]["fullname"]?></td><td><?php echo $lb[$n]["c"]?></td></tr>
	<?php
	}
?>
</table></p>

</form>
</div>

<?php
include "../include/footer.php";
?>
