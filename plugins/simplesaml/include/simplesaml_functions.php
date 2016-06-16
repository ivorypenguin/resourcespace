<?php


function simplesaml_authenticate()
	{
	global $as,$simplesaml_sp;
	if(!isset($as))
		{
		require_once(dirname(__FILE__) . '/../lib/lib/_autoload.php');
		$as = new SimpleSAML_Auth_Simple($simplesaml_sp);
		}
	$as->requireAuth();
	return true;
	}
	
function simplesaml_getattributes()
	{
	global $as,$simplesaml_sp;
	if(!isset($as))
		{
		require_once(dirname(__FILE__) . '/../lib/lib/_autoload.php');
		$as = new SimpleSAML_Auth_Simple($simplesaml_sp);
		}
	$as->requireAuth();
	$attributes = $as->getAttributes();
	return $attributes;
	}
	

function simplesaml_signout()
	{
	global $baseurl, $as, $simplesaml_sp;
	
	if(!isset($as))
		{
		require_once(dirname(__FILE__) . '/../lib/lib/_autoload.php');
		$as = new SimpleSAML_Auth_Simple($simplesaml_sp);
	
		}
	if($as->isAuthenticated())
		{
		$as->logout($baseurl . "/login.php"); 
		}
	
	}
	
function simplesaml_is_authenticated()
	{
	global $as,$simplesaml_sp;
	if(!isset($as))
		{
		require_once(dirname(__FILE__) . '/../lib/lib/_autoload.php');
		$as = new SimpleSAML_Auth_Simple($simplesaml_sp);
		}
	if(isset($as) && $as->isAuthenticated())
		{
		return true;
		}
	return false;	
	}

function simplesaml_getauthdata($value)
	{
	global $as,$simplesaml_sp;
	if(!isset($as))
		{
		require_once(dirname(__FILE__) . '/../lib/lib/_autoload.php');
		$as = new SimpleSAML_Auth_Simple($simplesaml_sp);
		}
	$as->requireAuth();
	$authdata = $as->getAuthData($value);
	return $authdata;
	}
