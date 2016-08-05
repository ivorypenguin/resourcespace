<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}

include "../../include/header.php";

$find=getval("find","");
$order_by=getval("orderby","name");

$reports=sql_query("select ref, name from report" . ($find=="" ? "" : " where ref like '%{$find}%' or name like '%{$find}%'") . " order by {$order_by}");

?><div class="BasicsBox"> 
	
	<p><a href="<?php echo $baseurl . "/pages/admin/admin_home.php" ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK ?><?php echo $lang["systemsetup"]?></a></p>
	
	<h1><?php echo $lang['page-title_report_management']; ?></h1>
	<p><?php echo $lang['page-subtitle_report_management_edit'] ?></p>

<?php
function addColumnHeader($orderName, $labelKey)
	{
	global $baseurl, $order_by, $find, $lang;

	if ($order_by == $orderName)
		$image = '<span class="ASC"></span>';
	else if ($order_by == $orderName . ' desc')
		$image = '<span class="DESC"></span>';
	else
		$image = '';

	?><td>
	<a href="<?php echo $baseurl ?>/pages/admin/admin_report_management.php?<?php
	if ($find!="") { ?>&find=<?php echo $find; }
	?>&orderby=<?php echo $orderName . ($order_by==$orderName ? '+desc' : ''); ?>"
	   onClick="return CentralSpaceLoad(this);"><?php echo $lang[$labelKey] . $image ?></a>
	</td>
	<?php
}

	?>
	<div class="Listview">
		<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
			<tr class="ListviewTitleStyle">
				<?php addColumnHeader("ref", "property-reference"); ?>
				<?php addColumnHeader("name", "property-name"); ?>
				<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
			</tr>
<?php
		foreach ($reports as $report)
			{
			$edit_url="{$baseurl_short}pages/admin/admin_report_management_edit.php?ref={$report["ref"]}" . ($find=="" ? "" : "&find={$find}") . ($order_by=="name" ? "" : "&orderby={$order_by}");
			$view_url="{$baseurl_short}pages/team/team_report.php?ref={$report['ref']}";
?>			<tr>
				<td>
					<a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo str_highlight ($report["ref"],$find,STR_HIGHLIGHT_SIMPLE); ?></a>
				</td>					
				<td>
					<a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo str_highlight ($report["name"],$find,STR_HIGHLIGHT_SIMPLE); ?></a>
				</td>
				<td>
					<div class="ListTools">
						<?php echo LINK_CARET ?><a href="<?php echo $view_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-view"]?></a>
						<?php echo LINK_CARET ?><a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["action-edit"]?></a>
						<?php echo LINK_CARET ?><a href="<?php echo $edit_url; ?>&copyreport=true" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["copy"]?></a>
					</div>
				</td>
			</tr>
<?php
			}
?>		</table>
	</div>
</div>		<!-- end of BasicsBox -->

<div class="BasicsBox">
	<form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_report_management.php" onSubmit="return CentralSpacePost(this,false);">

		<div class="Question">
			<label for="find"><?php echo $lang["property-search_filter"] ?></label>
			<input name="find" type="text" class="medwidth" value="<?php echo $find; ?>">
			<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]; ?>&nbsp;&nbsp;">
			<div class="clearerleft"></div>
		</div>
<?php
	if ($find!="")
		{
?>		<div class="QuestionSubmit">
			<label for="buttonsave"></label>
			<input name="buttonsave" type="button" onclick="CentralSpaceLoad('admin_report_management.php',false);"
				   value="&nbsp;&nbsp;<?php echo $lang["clearbutton"]; ?>&nbsp;&nbsp;">
		</div>
<?php
		}
?>	</form>
</div>

<div class="BasicsBox">
	<form method="post" action="<?php echo $baseurl_short; ?>pages/admin/admin_report_management_edit.php" onSubmit="return CentralSpacePost(this,false);">
		<div class="Question">
			<label for="name"><?php echo $lang['action-title_create_report_called']; ?></label>
			<div class="tickset">
				<div class="Inline">
					<input name="newreportname" type="text" value="" class="shrtwidth">
				</div>
				<div class="Inline">
					<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]; ?>&nbsp;&nbsp;" onclick="return (this.form.elements[0].value!='');">
				</div>
			</div>
			<div class="clearerleft"></div>
		</div>
		<?php
		if ($order_by)
			{
			?><input type="hidden" name="orderby" value="<?php echo $order_by; ?>">
			<?php
			}
		if ($find)
			{
			?><input type="hidden" name="find" value="<?php echo $find; ?>">
			<?php
			}
		?>
	</form>
</div>


<?php
include "../../include/footer.php";

