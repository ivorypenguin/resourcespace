<?php
# Reporting functions

function get_reports()
{
    # Returns an array of reports. The standard reports are translated using $lang. Custom reports are i18n translated.
    # The reports are always listed in the same order - regardless of the used language. 

    # Executes query.
    $r = sql_query("select * from report order by name");

    # Translates report names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["name"] = lang_or_i18n_get_translated($r[$n]["name"], "report-");
        $return[] = $r[$n]; # Adds to return array.
    }
    return $return;
}

function do_report($ref,$from_y,$from_m,$from_d,$to_y,$to_m,$to_d,$download=true,$add_border=false)
	{
	# Run report with id $ref for the date range specified. Returns a result array.
	global $lang, $baseurl;

	$report=sql_query("select * from report where ref='$ref'");$report=$report[0];

    # Translates the report name.
    $report["name"]=lang_or_i18n_get_translated($report["name"], "report-");

	if ($download)
		{
		$filename=str_replace(array(" ","(",")","-","/"),"_",$report["name"]) . "_" . $from_y . "_" . $from_m . "_" . $from_d . "_" . $lang["to"] . "_" . $to_y . "_" . $to_m . "_" . $to_d . ".csv";
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=" . $filename . "");
		}

	if($results = hook("customreport", "", array($ref,$from_y,$from_m,$from_d,$to_y,$to_m,$to_d,$download,$add_border))); else {

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
				if ($key!="thumbnail")
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
		$output="<br /><style>.InfoTable td {padding:5px;}</style><table $border class=\"InfoTable\">";
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
					$output.="<td>" . lang_or_i18n_get_translated($value, "usergroup-") . "</td>\r\n";
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

function create_periodic_email($user,$report,$period,$email_days,$send_all_users)
	{
	# Creates a new automatic periodic e-mail report.
#	echo ("user=$user, report=$report, period=$period, email_days=$email_days");

	# Delete any matching rows for this report/period.
	sql_query("delete from report_periodic_emails where user='$user' and report='$report' and period='$period'");

	# Insert a new row.
	sql_query("insert into report_periodic_emails(user,report,period,email_days) values ('$user','$report','$period','$email_days')");
	$ref=sql_insert_id();
	
	# Send to all users?
	if (checkperm('m') && $send_all_users)
		{
		sql_query("update report_periodic_emails set send_all_users=1 where ref='$ref'");
		}

	# Return
	return true;
	}


function send_periodic_report_emails()
	{
	# For all configured periodic reports, send a mail if necessary.
	global $lang,$baseurl;

	# Query to return all 'pending' report e-mails, i.e. where we haven't sent one before OR one is now overdue.
	$reports=sql_query("select pe.*,u.email,r.name,pe.send_all_users from report_periodic_emails pe join user u on pe.user=u.ref join report r on pe.report=r.ref where pe.last_sent is null or date_add(pe.last_sent,interval pe.email_days day)<=now()");
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
		$unsubscribe="<br>" . $lang["unsubscribereport"] . "<br>" . $baseurl . "/?ur=" . $report["ref"];
		$email=$report["email"];
		echo $lang["sendingreportto"] . " " . $email . "<br>" . $output . $unsubscribe . "<br>";
		send_mail($email,$title,$output . $unsubscribe,"","","",null,"","",true);

		# Send to all other active users, if configured.		
		if ($report["send_all_users"])
			{
			# Send the report to all active users.
			$users=get_users();
			foreach ($users as $user)
				{
				$email=$user["email"];
				if ($user["approved"] && $email!=$report["email"]) # Do not send to original report user, as they receive the mail with the unsubscribe link above.
					{
					echo $lang["sendingreportto"] . " " . $email . "<br>" . $output . "<br>";
					send_mail($email,$title,$output,"","","",null,"","",true);
					}
				}
			}

		# Mark as done.
		sql_query("update report_periodic_emails set last_sent=now() where ref='" . $report["ref"] . "'");
		}
	}

function unsubscribe_periodic_report($unsubscribe)
	{
	global $userref;
	sql_query("delete from report_periodic_emails where user='$userref' and ref='$unsubscribe'");
	}


?>
