<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";
if(!((checkperm("h") && !checkperm("hdta")) || (checkperm("dta") && !checkperm("h")))){exit($lang["error-permissiondenied"]);}
include "../../include/dash_functions.php";

include "../../include/header.php";
?>
<div class="BasicsBox"> 
	<h1><?php echo $lang["manage_all_dash"];?></h1>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);">
		&lt;&nbsp;<?php echo $lang["backtoteamhome"]?>
	</a>
</p>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_dash_admin.php" onClick="return CentralSpaceLoad(this,true);">
		&gt;&nbsp;<?php echo $lang["dasheditmodifytiles"];?>
	</a>
</p>
<p>
	<a href="<?php echo $baseurl_short?>pages/team/team_dash_tile_special.php" onClick="return CentralSpaceLoad(this,true);">
		&gt;&nbsp;<?php echo $lang["specialdashtiles"];?>
	</a>
</p>
	<div id="HomePanelContainer" class="manage-all-user-tiles">
	<?php
	get_default_dash();
	?>
	</div>
</div>
<?php
include "../../include/footer.php";
?>
