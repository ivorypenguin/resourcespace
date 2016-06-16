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
$filter_by_parent=getval("filterbyparent", "");
$filter_by_permissions=getval("filterbypermissions","");

if ($filter_by_permissions != "")
{
	foreach (explode(",",$filter_by_permissions) as $permission) {
		$permission = trim($permission);
		if ($permission == "")
			{
			continue;
			}
		if (isset ($sql_permision_filter))
			{
			$sql_permision_filter.=" and ";
			}
		else
			{
			$sql_permision_filter="(";
			}
		$permission = preg_replace('(\W+)','\\\\\\\$0',$permission);		// we need to pass two "\" before the escaped char for regex to take it literally (doubled here as sql_query() will convert most of them)
		$sql_permision_filter .= "usergroup.permissions regexp binary '^{$permission}|,{$permission},|,{$permission}\$|^{$permission}\$'";
	}
	$sql_permision_filter .= ")";
}

$offset=getvalescaped("offset",0);
$order_by=getval("orderby","name");

$groups=sql_query("
	select 
		usergroup.ref as ref,
		usergroup.name as name,
		count(user.ref) as users,
		parentusergroup.ref as pref,
		if (usergroup.parent is not null and usergroup.parent<>'' and usergroup.parent<>'0' and (parentusergroup.name is null or parentusergroup.name=''),usergroup.ref,parentusergroup.ref) as pref,
		if (usergroup.parent is not null and usergroup.parent<>'' and usergroup.parent<>'0' and (parentusergroup.name is null or parentusergroup.name=''),'orphaned',parentusergroup.name) as pname,
		(usergroup.parent is not null and usergroup.parent<>'' and usergroup.parent<>'0' and (parentusergroup.name is null or parentusergroup.name='')) as orphaned
	from
		usergroup 
	left outer join usergroup parentusergroup
	on 	
		usergroup.parent=parentusergroup.ref
	left outer join user
	on
		usergroup.ref=user.usergroup where true" .
	($find=="" ? "" : " and (usergroup.ref like '%{$find}%' or usergroup.name like '%{$find}%' or parentusergroup.name like '%{$find}%')") .
	($filter_by_parent=="" ? "" : " and parentusergroup.ref={$filter_by_parent}") .
	($filter_by_permissions=="" ? "" : " and {$sql_permision_filter}") .
	" group by
		usergroup.ref
	order by {$order_by}"
);

# pager
$per_page=15;
$results=count($groups);
$totalpages=ceil($results/$per_page);
$curpage=floor($offset/$per_page)+1;
$url="admin_group_management.php?" . ($order_by ? "orderby={$order_by}" : "");

function addColumnHeader($orderName, $labelKey)
{
	global $baseurl, $order_by, $filter_by_parent, $filter_by_permissions, $find, $lang;

	if ($order_by == $orderName)
		$image = '<span class="ASC"></span>';
	else if ($order_by == $orderName . ' desc')
		$image = '<span class="DESC"></span>';
	else
		$image = '';

?>		<td>
			<a href="<?php echo $baseurl ?>/pages/admin/admin_group_management.php?<?php
				if ($find!="") { ?>&find=<?php echo $find; }
				if ($filter_by_parent!="") { ?>&filterbyparent=<?php echo $filter_by_parent; }
				if ($filter_by_permissions!="") { ?>&filterbypermissions=<?php echo $filter_by_permissions; }
			?>&orderby=<?php echo $orderName . ($order_by==$orderName ? '+desc' : ''); ?>"
			onClick="return CentralSpaceLoad(this);"><?php echo $lang[$labelKey] . $image ?></a>
		</td>
<?php
}

?><div class="BasicsBox"> 

	
	<h1><?php echo $lang['page-title_user_group_management']; ?></h1>	
	<p><?php echo $lang['page-subtitle_user_group_management']; ?></p>
	
	<div class="TopInpageNav">
		<div class="TopInpageNavLeft">
			<div class="InpageNavLeftBlock">&nbsp;</div>		
		</div>
<?php 		
		pager(false);		
?>		<div class="clearerleft"></div>				
	</div>
	
	<div class="Listview">
		<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
			<tr class="ListviewTitleStyle">
				<?php addColumnHeader("ref", "property-reference"); ?>
				<?php addColumnHeader("name", "property-user_group"); ?>
				<?php addColumnHeader("users", "users"); ?>
				<?php addColumnHeader("pname", "property-user_group_parent"); ?>
				<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
			</tr>
<?php
		$url_params=
			($offset ? "&offset={$offset}" : "") .
			($order_by ? "&orderby={$order_by}" : "") .
			($filter_by_parent ? "&filterbyparent={$filter_by_parent}" : "") .
			($order_by ? "&find={$find}" : "") .
			($filter_by_permissions ? "&filterbypermissions={$filter_by_permissions}" : "");

		for ($n=$offset;(($n<count($groups)) && ($n<($offset+$per_page)));$n++)
			{
			$edit_url="{$baseurl_short}pages/admin/admin_group_management_edit.php?ref={$groups[$n]["ref"]}{$url_params}";
			$users_url="{$baseurl_short}pages/team/team_user.php?group={$groups[$n]["ref"]}&backlink=" . urlencode("{$baseurl_short}pages/admin/admin_group_management.php?{$url_params}");
?>			<tr>
				<td>
					<a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo str_highlight ($groups[$n]["ref"],$find,STR_HIGHLIGHT_SIMPLE); ?></a>
				</td>					
				<td>
					<a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo str_highlight ($groups[$n]["name"],$find,STR_HIGHLIGHT_SIMPLE); ?></a>
				</td>
				
				<td>					
					<a href="<?php echo $users_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $groups[$n]["users"]; ?></a>
				</td>

				<td>
					<?php
					if ($groups[$n]["orphaned"])
						{
						?><a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);">&lt;<?php echo $groups[$n]["pname"];?>&gt;</a>
						<?php
						}
					else
						{
						?><a href="<?php echo $baseurl_short; ?>pages/admin/admin_group_management.php?filterbyparent=<?php echo $groups[$n]["pref"];
						?>" onClick="return CentralSpaceLoad(this,false);"><?php echo str_highlight ($groups[$n]["pname"],$find,STR_HIGHLIGHT_SIMPLE); ?></a>
						<?php
						}
					?>
				</td>

				<td>
					<div class="ListTools">
						<a href="<?php echo $edit_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["action-edit"]?></a>
						&nbsp;
						<a href="<?php echo $users_url; ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["users"]?></a>
					</div>
				</td>
			</tr>
<?php
			}
?>		</table>
	</div>
	<div class="BottomInpageNav"><?php pager(false); ?></div>
</div>		<!-- end of BasicsBox -->

<div class="BasicsBox">
	<form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_group_management.php" onSubmit="return CentralSpacePost(this,false);">

		<input type="hidden" name="orderby" value="<?php echo $order_by; ?>">

		<div class="Question">
			<label for="find"><?php echo $lang["property-search_filter"] ?></label>
			<input name="find" type="text" class="medwidth" value="<?php echo $find; ?>">
			<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]; ?>&nbsp;&nbsp;">
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="filterbyparent"><?php echo $lang['action-title_filter_by_parent_group']; ?></label>
			<div class="tickset">
				<select name="filterbyparent" class="medwidth" onchange="this.form.submit();">
					<option value="" ><?php if($filter_by_parent != "") echo $lang["removethisfilter"]; ?></option>
<?php	

$groups=sql_query("
	select 	distinct 	
		parentusergroup.ref as ref,
		parentusergroup.name as name
	from 
		usergroup 
	left outer join usergroup parentusergroup
	on 			
		usergroup.parent=parentusergroup.ref		
	where parentusergroup.ref is not null
	order by usergroup.name"
);

foreach	($groups as $group)
	{
?>					<option <?php if ($filter_by_parent!="" && $filter_by_parent==$group['ref']) { ?> selected="true" <?php } ?>value="<?php echo $group['ref']; ?>"><?php echo $group['name']; ?></option>
<?php
	}
?>				</select>
			</div>
			<div class="clearerleft"> 
			</div>
		</div>

		<div class="Question">
			<label for="filterbypermissions"><?php echo $lang["action-title_filter_by_permissions"]; ?></label>
			<input name="filterbypermissions" type="text" class="medwidth" value="<?php echo $filter_by_permissions; ?>">
			<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["action-title_apply"]; ?>&nbsp;&nbsp;">
			<div class="clearerleft"></div>
		</div>

		<div class="FormHelp">
			<div class="FormHelpInner"><?php echo $lang["fieldhelp-permissions_filter"]; ?></div>
		</div>

<?php
	if ($find!="" || $filter_by_permissions!="" || $filter_by_parent!="") {
?>		<div class="QuestionSubmit">
			<label for="buttonsave"></label>
			<input name="buttonsave" type="submit"
				   onclick="CentralSpaceLoad('admin_group_management.php?orderby=<?php echo $order_by;
				   ?>',false);" value="&nbsp;&nbsp;<?php echo $lang["clearall"]; ?>&nbsp;&nbsp;">
		</div>
<?php
	}
?>	</form>
</div>

<div class="BasicsBox">
	<form method="post" action="<?php echo $baseurl_short; ?>pages/admin/admin_group_management_edit.php" onSubmit="return CentralSpacePost(this,false);">
		<div class="Question">
			<label for="name"><?php echo $lang['action-title_create_user_group_called']; ?></label>			
			<div class="tickset">
				<div class="Inline">
					<input name="newusergroupname" type="text" value="" class="shrtwidth">	
				</div>
				<div class="Inline">
					<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]; ?>&nbsp;&nbsp;" onclick="return (this.form.elements[0].value!='');">
				</div>
			</div>			
			<div class="clearerleft"></div>		
		</div>
<?php
	if ($offset) 
		{
?>		<input type="hidden" name="offset" value="<?php echo $offset; ?>">
<?php		}
	if ($order_by) 
		{
?>		<input type="hidden" name="order_by" value="<?php echo $order_by; ?>">
<?php		}
?>	</form>
</div>

<?php
include "../../include/footer.php";
?>
