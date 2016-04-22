<?php

#function HookTrack_field_historyViewHOOKNAME()

function HookTrack_field_historyViewValue_mod_after_highlight($field,$value){
	global $ref, $track_fields, $baseurl, $k;

	if($k=='' && in_array($field['ref'], $track_fields)) {
		
		$get_params = '?ref=' . $ref . '&field=' . $field['ref'] . '&field_title=' . $field['title'];

		$value.= '<a href="' . $baseurl . '/plugins/track_field_history/pages/field_history_log.php' . $get_params . '" style="margin-left: 20px;">&gt;&nbsp;History</a>';
		
		return $value;
	}

}
?>
