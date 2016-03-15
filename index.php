<?php
include "include/db.php";
include_once 'include/general.php';
include_once 'include/collections_functions.php';



if (getval("rp","")!="")
	{
	# quick redirect to reset password
	$rp=getvalescaped("rp","");
	$topurl="pages/user/user_change_password.php?rp=" . $rp;
        redirect($topurl);
	}
        
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("c",""),$k) && !check_access_key(getvalescaped("r",""),$k))) {include "include/authenticate.php";}

if (!hook("replacetopurl"))
	{ 
	$topurl="pages/" . $default_home_page;
	if ($use_theme_as_home) {$topurl="pages/themes.php";}
	if ($use_recent_as_home) {$topurl="pages/search.php?search=" . urlencode("!last".$recent_search_quantity);}
	} /* end hook replacetopurl */ 


if (getval("c","")!="")
	{
	# quick redirect to a collection (from e-mails, keep the URL nice and short)
	$c=getvalescaped("c","");
	$topurl="pages/search.php?search=" . urlencode("!collection" . $c) . "&k=" . $k;;
	
	if ($k!="")
		{
		# External access user... set top URL to first resource
		$r=get_collection_resources($c);
		if (count($r)>0)
			{
			# Fetch collection data
			$cinfo=get_collection($c);if ($cinfo===false) {exit("Collection not found.");}
		
			if ($feedback_resource_select && $cinfo["request_feedback"])
				{
				$topurl="pages/collection_feedback.php?collection=" . $c . "&k=" . $k;		
				}
			else
				{
				$topurl="pages/search.php?search=" . urlencode("!collection" . $c) . "&k=" . $k;		
				}
			}
		}
	}

if (getval("r","")!="")
	{
	# quick redirect to a resource (from e-mails)
	$r=getvalescaped("r","");
	$topurl="pages/view.php?ref=" . $r . "&k=" . $k;
	}

if (getval("u","")!="")
	{
	# quick redirect to a user (from e-mails)
	$u=getvalescaped("u","");
	$topurl="pages/team/team_user_edit.php?ref=" . $u;
	}
	
if (getval("q","")!="")
	{
	# quick redirect to a request (from e-mails)
	$q=getvalescaped("q","");
	$topurl="pages/team/team_request_edit.php?ref=" . $q;
	}

if (getval('ur', '') != '')
	{
	# quick redirect to periodic report unsubscriptions.
	$ur = getvalescaped('ur', '');

	$topurl = 'pages/team/team_report.php?unsubscribe=' . $ur;
	}

if(getval('dr', '') != '')
	{
	# quick redirect to periodic report deletion.
	$dr = getvalescaped('dr', '');

	$topurl = 'pages/team/team_report.php?delete=' . $dr;
	}


# Redirect.
redirect($topurl);
