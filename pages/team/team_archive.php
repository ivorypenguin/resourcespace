<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";if (!checkperm("i")) {exit ("Permission denied.");}

include "../../include/header.php";
?>


<div class="BasicsBox"> 
  <h1><?php echo $lang["managearchiveresources"]?></h1>
  <p><?php echo text("introtext")?></p>
  
  <p><a href="<?php echo $baseurl_short?>pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoteamhome"]?></a></p>
  
	<div class="VerticalNav">
	<ul>
	<li><a href="<?php echo $baseurl_short?>pages/edit.php?ref=-<?php echo $userref?>&single=true&status=2" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["newarchiveresource"]?></a></li>

	<li><a href="<?php echo $baseurl_short?>pages/search_advanced.php?archive=2" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["searcharchivedresources"]?></a></li>

	<li><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!archivepending")?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["viewresourcespendingarchive"]?></a></li>

	<?php hook("addlinktoteamarchive");?>

	</ul>
	</div>
	
  </div>

<?php
include "../../include/footer.php";
?>
