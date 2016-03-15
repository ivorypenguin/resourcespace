<?php

	function check_debug_log_override()
		{
		global $debug_log_override, $userref;

		if (isset($debug_log_override) || !isset($userref))
			{
			return;
			}

		$debug_log_override = false;

		$debug_user = sql_value("SELECT value FROM sysvars WHERE name='debug_override_user'", "");
		$debug_expires = sql_value("SELECT value FROM sysvars WHERE name='debug_override_expires'", "");

		if ($debug_user == "" || $debug_expires == "")
			{
			return;
			}

		if ($debug_expires < time())
			{
			sql_query("DELETE FROM sysvars WHERE name='debug_override_user' OR name='debug_override_expires'");
			return;
			}

		if ($debug_user == -1 || $debug_user == $userref)
			{
			$debug_log_override = true;
			}

		}

	function create_debug_log_override($debug_user = -1, $debug_expires = 60)
		{
		sql_query("DELETE FROM sysvars WHERE name='debug_override_user' OR name='debug_override_expires'");
		$debug_expires += time();
		sql_query("INSERT INTO sysvars VALUES ('debug_override_user','{$debug_user}'), ('debug_override_expires','{$debug_expires}')");
		}
