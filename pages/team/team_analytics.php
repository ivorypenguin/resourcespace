<?php
#
# ResourceSpace Analytics - list my reports
#
include '../../include/db.php';
include '../../include/general.php';
include '../../include/authenticate.php';

global $baseurl;

$offset=getvalescaped("offset",0);
if (array_key_exists("findtext",$_POST)) {$offset=0;} # reset page counter when posting
$findtext=getvalescaped("findtext","");

$delete=getvalescaped("delete","");
if ($delete!="")
	{
	# Delete report
	sql_query("delete from user_report where ref='$delete' and user='$userref'");
	}

include dirname(__FILE__)."/../../include/header.php";

?>

<div class="BasicsBox"> 
  <h1><?php echo $lang["rse_analytics"]?></h1>
 


<?php
$search_sql="";
if ($findtext!="") {$search_sql="and name like '%" . $findtext . "%'";}
$reports=sql_query("select * from user_report where user='$userref' $search_sql order by ref");

# pager
$per_page=15;
$results=count($reports);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$url="team_analytics.php?findtext=".urlencode($findtext)."&offset=". $offset;
$jumpcount=1;
?>


<div class="TopInpageNav">
<a href="<?php echo $baseurl_short ?>pages/team/team_analytics_edit.php" onClick="return CentralSpaceLoad(this);"><?php echo LINK_CARET . $lang["report_create_new"] ?></a>

<?php pager(); ?></div>

<form method=post id="reportsform" onSubmit="return CentralSpacePost(this,true);">
<input type=hidden name="delete" id="reportdelete" value="">
</form>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<td><?php echo $lang["report_name"]?></td>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<?php
for ($n=$offset;(($n<count($reports)) && ($n<($offset+$per_page)));$n++)
	{
	?>
	<tr>
	<td><div class="ListTitle"><a href="team_analytics_edit.php?ref=<?php echo $reports[$n]["ref"] ?>" onclick="return CentralSpaceLoad(this,true);"><?php echo highlightkeywords($reports[$n]["name"],$findtext,true);?></a></div></td>
	<td>
	<div class="ListTools">
		<a href="team_analytics_edit.php?ref=<?php echo $reports[$n]["ref"]?>&backurl=<?php echo urlencode($url . "&offset=" . $offset . "&findtext=" . $findtext)?>" onclick="return CentralSpaceLoad(this,true);"><?php   echo LINK_CARET .$lang["action-edit"]?> </a>
		<a href="#" onclick="if (confirm('<?php echo $lang["confirm-deletion"]?>')) {document.getElementById('reportdelete').value='<?php echo $reports[$n]["ref"]?>';document.getElementById('reportsform').submit();} return false;"><?php echo LINK_CARET . $lang["action-delete"]?></a>
		</div>
	</td>
	</tr>
	<?php
	}
?>

</table>
</div>
<div class="BottomInpageNav"><?php pager(true); ?></div>
</div>

<div class="BasicsBox">
	<form method="post" onSubmit="return CentralSpacePost(this,true);">
		<div class="Question">
			<label for="find"><?php echo $lang["find"]?><br/></label>
			<div class="tickset">
			 <div class="Inline">			
			<input type=text placeholder="<?php echo $lang['searchbytext']?>" name="findtext" id="findtext" value="<?php echo $findtext?>" maxlength="100" class="shrtwidth" />
			
			<input type="button" value="<?php echo $lang['clearbutton']?>" onClick="$('findtext').value='';form.submit();" />
			<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" />
			 
			</div>
			</div>
			<div class="clearerleft"> 
			</div>
		</div>
	</form>

</div>

<?php
include dirname(__FILE__)."/../../include/footer.php";

?>

