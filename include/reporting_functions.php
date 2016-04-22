<?php
# Reporting functions

function get_report_name($report)
	{
    # Translates or customizes the report name.
	$customName = hook('customreportname', '', array($report));
	if ($customName)
		return $customName;

	return lang_or_i18n_get_translated($report["name"], "report-");
	}

function get_reports()
	{
    # Returns an array of reports. The standard reports are translated using $lang. Custom reports are i18n translated.
    # The reports are always listed in the same order - regardless of the used language. 

    # Executes query.
    $r = sql_query("select * from report order by name");

    # Translates report names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++)
		{
		if (!hook('ignorereport', '', array($r[$n])))
			{
	        $r[$n]["name"] = get_report_name($r[$n]);
			$return[] = $r[$n]; # Adds to return array.
			}
		}
    return $return;
	}

function do_report($ref,$from_y,$from_m,$from_d,$to_y,$to_m,$to_d,$download=true,$add_border=false)
	{
	# Run report with id $ref for the date range specified. Returns a result array.
	global $lang, $baseurl;

	$report=sql_query("select * from report where ref='$ref'");$report=$report[0];
	$report['name'] = get_report_name($report);

	if ($download)
		{
		$filename=str_replace(array(" ","(",")","-","/"),"_",$report["name"]) . "_" . $from_y . "_" . $from_m . "_" . $from_d . "_" . $lang["to"] . "_" . $to_y . "_" . $to_m . "_" . $to_d . ".csv";
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=" . $filename . "");
		}

	if($results = hook("customreport", "", array($ref,$from_y,$from_m,$from_d,$to_y,$to_m,$to_d,$download,$add_border, $report))); else {

	$sql=$report["query"];
	$sql=str_replace("[from-y]",$from_y,$sql);
	$sql=str_replace("[from-m]",$from_m,$sql);
	$sql=str_replace("[from-d]",$from_d,$sql);
	$sql=str_replace("[to-y]",$to_y,$sql);
	$sql=str_replace("[to-m]",$to_m,$sql);
	$sql=str_replace("[to-d]",$to_d,$sql);

	global $view_title_field;
	#back compatibility for three default reports, to replace "title" with the view_title_field.
	#all reports should either use r.title or view_title_field when referencing the title column on the resource table.
	if ($ref==7||$ref==8||$ref==9){
		$sql=str_replace(",title",",field".$view_title_field,$sql);
	}

    $sql=str_replace("view_title_field","field".$view_title_field,$sql);
	$sql=str_replace("r.title","field".$view_title_field,$sql);

	$results=sql_query($sql);
	#echo "\"Number of results: " . count($results) . "\"\n";
	}

	if ($download)
		{
		for ($n=0;$n<count($results);$n++)
			{
			$result=$results[$n];
			if ($n==0)
				{
				$f=0;
				foreach ($result as $key => $value)
					{
					$f++;
					if ($f>1) {echo ",";}
					if ($key!="thumbnail")
						{echo "\"" . lang_or_i18n_get_translated($key,"columnheader-") . "\"";}
					}
				echo "\n";
				}
			$f=0;
			foreach ($result as $key => $value)
				{
				$f++;
				if ($f>1) {echo ",";}
				$custom = hook('customreportfield', '', array($result, $key, $value, $download));
				if ($custom !== false)
					{
					echo $custom;
					}
				else if ($key!="thumbnail")
					{
					$value=lang_or_i18n_get_translated($value, "usergroup-");
					$value=str_replace('"','""',$value); # escape double quotes
					if (substr($value,0,1)==",") {$value=substr($value,1);} # Remove comma prefix on dropdown / checkbox values 
					echo "\"" . $value  . "\"";
						
					}
				}
			echo "\n";
			}
		}
	else
		{
		# Not downloading - output a table
		$border="";
		if ($add_border) {$border="border=\"1\"";}
		$output="<br /><h2>" . $report['name'] . "</h2><style>.InfoTable td {padding:5px;}</style><table $border class=\"InfoTable\">";
		for ($n=0;$n<count($results);$n++)
			{
			$result=$results[$n];
			if ($n==0)
				{
				$f=0;
				$output.="<tr>\r\n";
				foreach ($result as $key => $value)
					{
					$f++;
					if ($key=="thumbnail")
						{$output.="<td><strong>Link</strong></td>\r\n";}
					else
						{
						$output.="<td><strong>" . lang_or_i18n_get_translated($key,"columnheader-") . "</strong></td>\r\n";
						}
					}
				$output.="</tr>\r\n";
				}
			$f=0;
			$output.="<tr>\r\n";
			foreach ($result as $key => $value)
				{
				$f++;
				if ($key=="thumbnail")
					{
					$thm_path=get_resource_path($value,true,"thm",false,"",$scramble=-1,$page=1,false);
					if (!file_exists($thm_path)){
						$resourcedata=get_resource_data($value);
						$thm_url= $baseurl . "/gfx/" . get_nopreview_icon($resourcedata["resource_type"],$resourcedata["file_extension"],true);
						}
					else
						{
						$thm_url=get_resource_path($value,false,"col",false,"",-1,1,false);
						}
					$output.="<td><a href=\"" . $baseurl . "/?r=" . $value .  "\" target=\"_blank\"><img src=\"" . $thm_url . "\"></a></td>\r\n";
					}
				else
					{
					$custom = hook('customreportfield', '', array($result, $key, $value, $download));
					if ($custom !== false)
						{
						$output .= $custom;
						}
					else
						{
						$output.="<td>" . lang_or_i18n_get_translated($value, "usergroup-") . "</td>\r\n";
						}
					}
				}
			$output.="</tr>\r\n";
			}
		$output.="</table>\r\n";
		if (count($results)==0) {$output.=$lang["reportempty"];}
		return $output;
		}

	exit();
	}

/**
* Creates a new automatic periodic e-mail report
*
*/
function create_periodic_email($user, $report, $period, $email_days, $send_all_users, array $user_groups)
	{
	# Delete any matching rows for this report/period.
	$query = sprintf('
			DELETE
			  FROM report_periodic_emails
			 WHERE user = \'%s\'
			   AND report = \'%s\'
			   AND period = \'%s\';
		',
		$user,
		$report,
		$period
	);
	sql_query($query);

	# Insert a new row.
	$query = sprintf('
			INSERT INTO report_periodic_emails (
			                                       user,
			                                       report,
			                                       period,
			                                       email_days
			                                   )
			     VALUES (
			                \'%s\',  # user
			                \'%s\',  # report
			                \'%s\',  # period
			                \'%s\'   # email_days
			            );
		',
		$user,
		$report,
		$period,
		$email_days
	);
	sql_query($query);
	$ref = sql_insert_id();
	
	# Send to all users?
	if (checkperm('m'))
		{
		if($send_all_users)
			{
			sql_query('UPDATE report_periodic_emails SET send_all_users = 1 WHERE ref = "' . $ref . '";');
			}

		if(!empty($user_groups))
			{
			sql_query('UPDATE report_periodic_emails SET user_groups = "' . implode(',', $user_groups) . '" WHERE ref = "' . $ref . '";');
			}
		}

	# Return
	return true;
	}


function send_periodic_report_emails()
	{
	# For all configured periodic reports, send a mail if necessary.
	global $lang,$baseurl;

	# Query to return all 'pending' report e-mails, i.e. where we haven't sent one before OR one is now overdue.
	$query = "
		SELECT pe.*,
		       u.email,
		       r.name
		  FROM report_periodic_emails pe
		  JOIN user u ON pe.user = u.ref
		  JOIN report r ON pe.report = r.ref
		 WHERE pe.last_sent IS NULL
		    OR date_add(date(pe.last_sent), INTERVAL pe.email_days DAY) <= date(now());
	";
	$reports=sql_query($query);
	foreach ($reports as $report)
		{
		$start=time()-(60*60*24*$report["period"]);

		$from_y = date("Y",$start);
		$from_m = date("m",$start);
		$from_d = date("d",$start);

		$to_y = date("Y");
		$to_m = date("m");
		$to_d = date("d");

		# Translates the report name.
		$report["name"] = lang_or_i18n_get_translated($report["name"], "report-");

		# Generate remote HTML table.
		$output=do_report($report["report"], $from_y, $from_m, $from_d, $to_y, $to_m, $to_d,false,true);

		# Formulate a title
		$title = $report["name"] . ": " . str_replace("?",$report["period"],$lang["lastndays"]);

		# Send mail to original user - this contains the unsubscribe link
		# Note: this is basically the only way at the moment to delete a periodic report
		$delete_link = sprintf('<br />%s<br />%s/?dr=%s',
			$lang['report_delete_periodic_email_link'],
			$baseurl,
			$report['ref']
		);

		$unsubscribe="<br>" . $lang["unsubscribereport"] . "<br>" . $baseurl . "/?ur=" . $report["ref"];
		$email=$report["email"];

		// Check user unsubscribed from this report
		$query = sprintf('
				SELECT true as `value`
				  FROM report_periodic_emails_unsubscribe
				 WHERE user_id = "%s"
				   AND periodic_email_id = "%s";
			',
			$report['user'],
			$report['ref']
		);
		$unsubscribed_user = sql_value($query, false);

		if(!$unsubscribed_user)
			{
			echo $lang["sendingreportto"] . " " . $email . "<br>" . $output . $delete_link . $unsubscribe . "<br>";
			send_mail($email,$title,$output . $delete_link  . $unsubscribe);
			}

		// Jump to next report if this should only be sent to one user
		if(!$report['send_all_users'] && empty($report['user_groups']))
			{
			# Mark as done.
			sql_query('UPDATE report_periodic_emails set last_sent = now() where ref = "' . $report['ref'] . '";');
			
			continue;
			}

		# Send to all other active users, if configured.
		# Send the report to all active users.
		$users = get_users();

		// Send e-mail reports to users belonging to the specific user groups
		if(!empty($report['user_groups']))
			{
			$users = get_users($report['user_groups']);
			}

		foreach($users as $user)
			{
			$email = $user['email'];

			# Do not send to original report user, as they receive the mail with the unsubscribe link above.
			if(($user['approved'] && $email == $report['email']) || !$user['approved'])
				{
				continue;
				}

			// Check user unsubscribed from this report
			$query = sprintf('
					SELECT true as `value`
					  FROM report_periodic_emails_unsubscribe
					 WHERE user_id = "%s"
					   AND periodic_email_id = "%s";
				',
				$user['ref'],
				$report['ref']
			);
			$unsubscribed_user = sql_value($query, false);

			if(!$unsubscribed_user)
				{
				$unsubscribe_link = sprintf('<br />%s<br />%s/?ur=%s',
					$lang['unsubscribereport'],
					$baseurl,
					$report['ref']
				);

				echo $lang["sendingreportto"] . " " . $email . "<br>" . $output . $unsubscribe_link . "<br>";
				send_mail($email, $title, $output . $unsubscribe_link);
				}
			}

		# Mark as done.
		sql_query('UPDATE report_periodic_emails set last_sent = now() where ref = "' . $report['ref'] . '";');
		}
	}

function delete_periodic_report($ref)
	{
	global $userref;
	sql_query('DELETE FROM report_periodic_emails WHERE user = "' . $userref . '" AND ref = "' . $ref . '";');
	sql_query('DELETE FROM report_periodic_emails_unsubscribe WHERE periodic_email_id = "' . $ref . '"');

	return true;
	}

function unsubscribe_user_from_periodic_report($user_id, $periodic_email_id)
	{
	$query = sprintf('
			INSERT INTO report_periodic_emails_unsubscribe (
			                                                   user_id,
			                                                   periodic_email_id
			                                               )
			     VALUES (
			                "%s", # user_id
			                "%s"  # periodic_email_id
			            );
		',
		$user_id,
		$periodic_email_id
	);
	sql_query($query);

	return true;
	}

function get_translated_activity_type($activity_type)
	{
	# Activity types are stored in plain text english in daily_stat. This function will use language strings to resolve a translated value where one is set.
	global $lang;
	$key="stat-" . strtolower(str_replace(" ","",$activity_type));
	if (!isset($lang[$key]))
		{
		return $activity_type;
		}
	else
		{
		return $lang[$key];
		}
	}

?>
