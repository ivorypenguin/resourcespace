<?php

include_once __DIR__ . '/definitions.php';		// includes log code definitions for resource_log() callers.

function log_activity($note=null, $log_code=LOG_CODE_UNSPECIFIED, $value_new=null, $remote_table=null, $remote_column=null, $remote_ref=null, $ref_column_override=null, $value_old=null, $user=null, $generate_diff=false)
	{

	if (is_null($log_code))
		{
		$log_code == LOG_CODE_UNSPECIFIED;
		}

	if(!function_exists('log_diff'))
		{
		include_once(__DIR__ . '/resource_functions.php');
		}

	if (is_null($user))
		{
		global $userref;
		$user = isset($userref) && !is_null($userref) ? $userref : 0;
		}

	if (is_null($value_old) && !is_null($remote_table) && !is_null($remote_column) && !is_null($remote_ref))	// only try and get the old value if not explicitly set and we have table details
		{
		$row = sql_query("SELECT * FROM `{$remote_table}` WHERE `" . (is_null($ref_column_override) ? 'ref' : escape_check($ref_column_override)) . "`='{$remote_ref}'");
		if (isset($row[0][$remote_column]))
			{
			$value_old = $row[0][$remote_column];
			$log_code = $log_code==LOG_CODE_UNSPECIFIED ? LOG_CODE_EDITED : $log_code;
			}
		else
			{
			$log_code = $log_code==LOG_CODE_UNSPECIFIED ? LOG_CODE_CREATED : $log_code;
			}
		}

	if ($value_old == $value_new && ($log_code == LOG_CODE_EDITED || $log_code == LOG_CODE_COPIED))	// return if the value has not changed
		{
		return;
		}

	sql_query("INSERT INTO `activity_log` (`logged`,`user`,`log_code`,`note`,`value_old`,`value_new`,`value_diff`,`remote_table`,`remote_column`,`remote_ref`) VALUES (" .
		"NOW()," .
		"'{$user}'," .
		"'" . (!LOG_CODE_validate($log_code) ? LOG_CODE_UNSPECIFIED : $log_code) . "'," .
		"'" . (is_null($note) ? '' : escape_check($note)) . "'," .
		"'" . (is_null($value_old) ? '' : escape_check($value_old)) . "'," .
		"'" . (is_null($value_new) ? '' : escape_check($value_new)) . "'," .
		"'" . (!is_null($value_old) && !is_null($value_new) && $generate_diff ? escape_check(log_diff($value_old,$value_new)) : '') . "'," .
		"'" . (is_null($remote_table) ? '' : escape_check($remote_table)) . "'," .
		"'" . (is_null($remote_column) ? '' : escape_check($remote_column)) . "'," .
		"'" . (is_null($remote_ref) ? '' : escape_check($remote_ref)) . "'" .
		")");
	}

