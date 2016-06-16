<?php

function HookNewsTeam_homeCustomteamfunction()
	{
	global $baseurl, $lang;
	
    if (checkperm("o"))
		{
		
		?><li><i class="fa fa-fw fa-newspaper-o"></i>&nbsp;<a href="<?php echo $baseurl ?>/plugins/news/pages/news_edit.php"><?php echo $lang["news_manage"]?></a></li>
		<?php
		}
		?>
	<?php
	}




