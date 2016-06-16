<?php
function HookApi_coreUser_homeUser_home_additional_links(){
	global $lang,$baseurl;
	?>
	<li><i class="fa fa-fw fa-plug"></i>&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/plugins/api_core/index.php"><?php echo $lang["apiaccess"];?></a></li>
	<?php
	}
