<?php

function HookTms_linkTeam_homeCustomteamfunctionadmin()
    {
	global $lang, $tms_link_script_failure_notify_days;
    $scriptlastran=sql_value("select value from sysvars where name='last_tms_import'","");
	$tms_link_script_failure_notify_seconds=intval($tms_link_script_failure_notify_days)*60*60*24;

	if($scriptlastran=="" || time()>=(strtotime($scriptlastran)+$tms_link_script_failure_notify_seconds))
		{
		$tmsalerthtml="<div class=\"PageInformal \">" . str_replace("%days%",$tms_link_script_failure_notify_days,$lang["tms_link_script_problem"]) . " "  . (($scriptlastran!="")?date("l F jS Y @ H:m:s",strtotime($scriptlastran)):$lang["status-never"]) . "</div>";
		echo "<script>
			tmsalerthtml='" . $tmsalerthtml. "';
			jQuery('#CentralSpace .BasicsBox .VerticalNav').prepend(tmsalerthtml);
			</script>
			";
		}
	
	return false;
    }
