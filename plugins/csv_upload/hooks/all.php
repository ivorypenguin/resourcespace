<?php
	
function HookCsv_uploadAllTopnavlinksafterhome()
	{
    global $baseurl,$lang;
	if (checkperm("c"))
		{
		?><li><a href="<?php echo $baseurl ?>/plugins/csv_upload/pages/csv_upload.php" onClick="CentralSpaceLoad(this,true);return false;"><?php echo $lang["csv_upload_nav_link"]; ?></a></li>
		<?php
		}
	}
