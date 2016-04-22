<?php

include '../../../include/db.php';
include '../../../include/general.php';
include '../../../include/authenticate.php';
if (!checkperm('u')){
	exit ($lang['error-permissiondenied']);
}
include '../include/auto_group_functions.php';

$offset=getvalescaped("offset",0);
$find=getvalescaped("find","");
$order_by=getvalescaped("order_by","u.username");
$group=getvalescaped("group",0);
$per_page=getvalescaped("per_page_list",$default_perpage_list);setcookie("per_page_list",$per_page, 0, '', '', false, true);

if (array_key_exists("find",$_POST)) {$offset=0;} # reset page counter when posting

include "../../../include/header.php";
?>


<div class="BasicsBox"> 
	<h2>&nbsp;</h2>
	<h1><?php echo $lang["managegroups"]?></h1>
	<p><?php echo text("introtext")?></p>
	<?php
	# Fetch rows
	$groups=auto_group_get_groups();
	$groups=auto_group_get_group_usergroups();
	if(!empty($groups)){
		$results=count($groups);
	}
	else{
		$results=0;
	}
	$totalpages=ceil($results/$per_page);
	$curpage=floor($offset/$per_page)+1;
	
	$url=$baseurl_short."plugins/auto_group/pages/groups.php?group=" . $group . "&order_by=" . $order_by . "&find=" . urlencode($find);
	$jumpcount=1;
	
	# Create an a-z index
	$atoz="<div class=\"InpageNavLeftBlock\">";
	if ($find=="") {$atoz.="<span class='Selected'>";}
	$atoz.="<a href=\"" . $baseurl . "/plugins/auto_group/pages/groups.php?order_by=u.username&group=" . $group . "&find=\" onClick=\"return CentralSpaceLoad(this);\">" . $lang["viewall"] . "</a>";
	if ($find=="") {$atoz.="</span>";}
	$atoz.="&nbsp;&nbsp;";
	for ($n=ord("A");$n<=ord("Z");$n++){
		if ($find==chr($n)) {$atoz.="<span class='Selected'>";}
		$atoz.="<a href=\"" . $baseurl . "/plugins/auto_group/pages/groups.php?order_by=u.username&group=" . $group . "&find=" . chr($n) . "\" onClick=\"return CentralSpaceLoad(this);\">&nbsp;" . chr($n) . "&nbsp;</a> ";
		if ($find==chr($n)) {$atoz.="</span>";}
		$atoz.=" ";
	}
	$atoz.="</div>";
	
	?>
	<div class="TopInpageNav"><?php echo $atoz?>
		<div class="InpageNavLeftBlock"><?php echo $lang["resultsdisplay"]?>:
		<?php 
		for($n=0;$n<count($list_display_array);$n++){
			if($per_page==$list_display_array[$n]){
				?><span class="Selected"><?php echo $list_display_array[$n]?></span><?php
			}
			else{
				?><a href="<?php echo $url; ?>&per_page_list=<?php echo $list_display_array[$n]?>" onClick="return CentralSpaceLoad(this);"><?php echo $list_display_array[$n]?></a><?php
			} 
			?>&nbsp;|<?php
		}
		if($per_page==99999){
			?><span class="Selected"><?php echo $lang["all"]?></span><?php
		}
		else{
			?><a href="<?php echo $url; ?>&per_page_list=99999" onClick="return CentralSpaceLoad(this);"><?php echo $lang["all"]?></a><?php
		}
		?></div><?php
		pager(false);
	?></div>
	<strong><?php echo $lang["total"] . ": " . $results; ?> </strong>
	<?php echo $lang["groups"];
	?><br />
</div>
<?php
include "../../../include/footer.php";
