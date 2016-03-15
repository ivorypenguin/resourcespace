<?php

function HookGrant_editAllCustomediteaccess()
	{
	global $ref,$userref;
	$access=sql_value("select resource value from grant_edit where resource='$ref' and user='$userref' and (expiry is null or expiry>=NOW())","");
	if($access!=""){return true;}
	return false;
	}
