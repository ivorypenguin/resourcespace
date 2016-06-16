<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm_user_edit($userref))
	{
	redirect($baseurl_short ."login.php?error=error-permissions-login&url={$baseurl_short}pages/admin/admin_system_log.php");
	exit;
	}

$sortby = getval("sortby","");
$filter = getval("filter","");
$backurl=getval("backurl","");
$actasuser=getval('actasuser',$userref);
$offset=getval('offset',0);

include "../../include/header.php";

?><script>

var sortByactivitylog = "<?php echo $sortby; ?>";
var filteractivitylog = "<?php echo $filter; ?>";
var offset = "<?php echo $offset; ?>";

function SystemConsoleactivitylogLoad(refresh_secs, extra)
{
	if (extra == undefined)
	{
		extra = "";
	}
	CentralSpaceLoad("admin_system_log.php?sortby=" + encodeURIComponent(sortByactivitylog) +
	'&actasuser=<?php echo $actasuser; ?>&filter=' + encodeURIComponent(filteractivitylog) +
	'&offset=' + encodeURIComponent(offset) + extra);
}

</script>

<div class="BasicsBox">

	<?php if ($backurl!=""){?><p><a href="<?php echo $backurl?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET_BACK ?><?php echo strpos($backurl,"team_user_edit")!==false?$lang["edituser"]:$lang["manageusers"] ?></a></p><?php } ?>

	<h1><?php echo $lang["systemlog"]; ?></h1>
	<p><?php echo text("introtext"); ?></p>
</div>

<input type="text" class="stdwidth" placeholder="Filter" value="<?php echo $filter; ?>" onkeyup="if(this.value=='')
	   {
	   jQuery('#filterbuttonactivitylog').attr('disabled','disabled');
	   jQuery('#clearbuttonactivitylog').attr('disabled','disabled')
	   } else {
	   jQuery('#filterbuttonactivitylog').removeAttr('disabled');
	   jQuery('#clearbuttonactivitylog').removeAttr('disabled')
	   }
	   filteractivitylog=this.value;
	   var e = event;
	   if (e.keyCode === 13)
	   {
	   SystemConsoleactivitylogLoad();
	   }">
</input>
<input id="filterbuttonactivitylog" type="button" onclick="offset=0; SystemConsoleactivitylogLoad();" value="<?php echo $lang['filterbutton']; ?>">
<input id="clearbuttonactivitylog" type="button" onclick="filteractivitylog=''; offset=0; SystemConsoleactivitylogLoad();" value="<?php echo $lang['clearbutton']; ?>">

<?php

$_GET['callback']="activitylog";
$_GET['actasuser']=$actasuser;
include_once __DIR__ . "/../team/team_system_console.php";

if (count($results) > $results_per_page)
	{
	?><input type="button" onclick="offset++; SystemConsoleactivitylogLoad();" value="<?php echo $lang["loadmorebutton"]; ?>">&nbsp;<?php
	}

if ($offset > 0)
	{
	?><input id = "clearbuttonactivitylog" type = "button" onclick = "offset--; SystemConsoleactivitylogLoad();" value = "<?php echo $lang['back']; ?>" ><?php
	}

include "../../include/footer.php";
