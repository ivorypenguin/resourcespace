<?php
# Request functions
# Functions to accomodate resource requests and orders (requests with payment)

function get_request($request)
    {
    $result=sql_query("select u.username,u.fullname,u.email,r.user,r.collection,r.created,r.request_mode,r.status,r.comments,r.expires,r.assigned_to,r.reason,r.reasonapproved,u2.username assigned_to_username from request r left outer join user u  on r.user=u.ref left outer join user u2 on r.assigned_to=u2.ref where r.ref='$request'");
    if (count($result)==0)
        {
        return false;
        }
    else
        {
        return $result[0];
        }
    }

function get_user_requests()
    {
    global $userref;
    if (!is_numeric($userref)){ return false; }
    return sql_query("select u.username,u.fullname,r.*,if(collection.ref is null,'0',collection.ref) collection_id, (select count(*) from collection_resource cr where cr.collection=r.collection) c from request r left outer join user u on r.user=u.ref left join collection on r.collection = collection.ref where r.user = '$userref' order by ref desc");
    }
    
function save_request($request)
    {
    # Use the posted form to update the request
    global $applicationname,$baseurl,$lang,$request_senduserupdates,$admin_resource_access_notifications;
        
    $status=getvalescaped("status","",true);
    $expires=getvalescaped("expires","");
    $currentrequest=get_request($request);
    $oldstatus=$currentrequest["status"];
    $assigned_to=getvalescaped("assigned_to","");
    $reason=getvalescaped("reason","");
    $reasonapproved=getvalescaped("reasonapproved","");
    
    
    # --------------------- User Assignment ------------------------
    # Has the assigned_to value changed?
    if ($currentrequest["assigned_to"]!=$assigned_to && checkperm("Ra"))
        {
        if ($assigned_to==0)
            {
            # Cancel assignment
            sql_query("update request set assigned_to=null where ref='$request'");
            }
        else
            {
            # Update and notify user
            sql_query("update request set assigned_to='$assigned_to' where ref='$request'");
            $message=$lang["requestassignedtoyoumail"] . "\n\n$baseurl/?q=" . $request . "\n";
            
            get_config_option($assigned_to,'user_pref_resource_access_notifications', $send_message, true);		  
            if($send_message)
                {
                get_config_option($assigned_to,'email_user_notifications', $send_email);
                $assigned_to_user=get_user($assigned_to);
                if($send_email && $assigned_to_user["email"]!="")
                    {               
                    send_mail($assigned_to_user["email"],$applicationname . ": " . $lang["requestassignedtoyou"],$message);
                    //send_mail($download_user['email'],$applicationname . ": " . $lang["notify_resource_change_email_subject"],str_replace(array("[days]","[url]"),array($notify_on_resource_change_days,$baseurl . "/?r=" . $resource),$lang["notify_resource_change_email"]),"","",'notify_resource_change_email',array("days"=>$notify_on_resource_change_days,"url"=>$baseurl . "/?r=" . $resource));
                    }
                else
                    {
                    message_add($assigned_to,$message,$baseurl . "/?q=" . $request);
                    }
                }
            
            get_config_option($currentrequest["user"],'user_pref_resource_access_notifications', $send_message, $admin_resource_access_notifications);		  
            if($send_message)
                {
                $userconfirmmessage=str_replace("%",$assigned_to_user["fullname"] . " (" . $assigned_to_user["email"] . ")" ,$lang["requestassignedtouser"]);
                if ($request_senduserupdates)
                    {
                    get_config_option($currentrequest["user"],'email_user_notifications', $send_email);
                    if($send_email && $currentrequest["email"]!="")
                        {    
                        send_mail($currentrequest["email"],$applicationname . ": " . $lang["requestupdated"] . " - $request",$userconfirmmessage);
                        }
                    else
                        {
                        message_add($currentrequest["user"],$lang["requestupdated"] . " - " . $request . "<br />" . $userconfirmmessage,$baseurl . "/?c=" . $currentrequest["collection"]);
                        }
                        
                    }
                }
            }
        }
    
    
    # Has either the status or the expiry date changed?
    if (($oldstatus!=$status || $expires!=$currentrequest["expires"]) && $status==1)
        {
        # --------------- APPROVED -------------
        # Send approval e-mail
        // $reasonapproved=str_replace(array("\\r","\\n"),"\n",$reasonapproved);$reasonapproved=str_replace("\n\n","\n",$reasonapproved); # Fix line breaks.
        $reasonapproved = unescape($reasonapproved);
        $message=$lang["requestapprovedmail"] . "\n\n" . $lang["approvalreason"]. ": " . $reasonapproved . "\n\n" ;
        $message.="$baseurl/?c=" . $currentrequest["collection"] . "\n";
        if ($expires!="")
            {
            # Add expiry time to message.
            $message.=$lang["requestapprovedexpires"] . " " . nicedate($expires) . "\n\n";
            }
                   
        get_config_option($currentrequest["user"],'email_user_notifications', $send_email);
        if($send_email && $currentrequest["email"]!="")
            {
            send_mail($currentrequest["email"],$applicationname . ": " . $lang["requestcollection"] . " - " . $lang["resourcerequeststatus1"],$message);
            }
        else
            {
            message_add($currentrequest["user"],$message,$baseurl . "/?c=" . $currentrequest["collection"]);
            }
               
        
        # Mark resources as full access for this user
        foreach (get_collection_resources($currentrequest["collection"]) as $resource)
            {
            open_access_to_user($currentrequest["user"],$resource,$expires);
            }
			
		# Clear any outstanding notifications about this request that may have been sent to other admins
	    message_remove_related(MANAGED_REQUEST,$request);
		}

    if ($oldstatus!=$status && $status==2)  
        {
        # --------------- DECLINED -------------
        # Send declined e-mail

        // $reason=str_replace(array("\\r","\\n"),"\n",$reason);$reason=str_replace("\n\n","\n",$reason); # Fix line breaks.
        $reason = unescape($reason);
        $message=$lang["requestdeclinedmail"] . "\n\n" . $lang["declinereason"] . ": ". $reason . "\n\n$baseurl/?c=" . $currentrequest["collection"] . "\n";
               
        get_config_option($currentrequest["user"],'email_user_notifications', $send_email);
        if($send_email && $currentrequest["email"]!="")
            {
            send_mail($currentrequest["email"],$applicationname . ": " . $lang["requestcollection"] . " - " . $lang["resourcerequeststatus2"],$message);
            }
        else
            {
            message_add($currentrequest["user"],$message,$baseurl . "/?c=" . $currentrequest["collection"]);
            }

        # Remove access that my have been granted by an inadvertant 'approved' command.
        foreach (get_collection_resources($currentrequest["collection"]) as $resource)
            {
            remove_access_to_user($currentrequest["user"],$resource);
            }
			
		# Clear any outstanding notifications about this request that may have been sent to other admins
	    message_remove_related(MANAGED_REQUEST,$request);
        }

    if ($oldstatus!=$status && $status==0)
        {
        # --------------- PENDING -------------
        # Moved back to pending. Delete any permissions set by a previous 'approve'.
        foreach (get_collection_resources($currentrequest["collection"]) as $resource)
            {
            remove_access_to_user($currentrequest["user"],$resource);
            }
        }

        // Escape again because we had to unescape it before adding it to the e-mail body
        $reasonapproved = escape_check($reasonapproved);
        $reason = escape_check($reason);

    # Save status
    sql_query("update request set status='$status',expires=" . ($expires==""?"null":"'$expires'") . ",reason='$reason',reasonapproved='$reasonapproved' where ref='$request'");

    if (getval("delete","")!="")
        {
        # Delete the request - this is done AFTER any e-mails have been sent out so this can be used on approval.
        sql_query("delete from request where ref='$request'");
		
		# Clear any outstanding notifications about this request that may have been sent to other admins
	    message_remove_related(MANAGED_REQUEST,$request);
        
		return true;        
        }

    }
    
    
function get_requests()
    {
    # If permission Rb (accept resource request assignments) is set then limit the list to only those assigned to this user - EXCEPT for those that can assign requests, who can always see everything.
    $condition="";global $userref;
    if (checkperm("Rb") && !checkperm("Ra")) {$condition="where r.assigned_to='" . $userref . "'";}
    
    return sql_query("select u.username,u.fullname,r.*,(select count(*) from collection_resource cr where cr.collection=r.collection) c,r.assigned_to,u2.username assigned_to_username from request r left outer join user u on r.user=u.ref left outer join user u2 on r.assigned_to=u2.ref $condition order by status,ref desc");
    }

function email_collection_request($ref,$details)
    {
    # Request mode 0
    # E-mails a collection request (posted) to the team
    global $applicationname,$email_from,$baseurl,$email_notify,$username,$useremail,$lang,$request_senduserupdates,$userref,$resource_type_request_emails,$resource_request_reason_required,$admin_resource_access_notifications, $always_email_from_user;
    
    $message="";
    #if (isset($username) && trim($username)!="") {$message.=$lang["username"] . ": " . $username . "\n";}
    
    $templatevars['url']=$baseurl."/?c=".$ref;
    $collectiondata=get_collection($ref);
    if (isset($collectiondata["name"])){
    $templatevars["title"]=$collectiondata["name"];}
    
    # Create a copy of the collection which is the one sent to the team. This is so that the admin
    # user can e-mail back an external URL to the collection if necessary, to 'unlock' full (open) access.
    # The user cannot then gain access to further resources by adding them to their original collection as the
    # shared collection is a copy.
    # A complicated scenario that is best avoided using 'managed requests'.
    $copied=create_collection(-1,$lang["requestcollection"]);
    copy_collection($ref,$copied);
    $ref=$copied;
    
    $templatevars["requesturl"]=$baseurl."/?c=".$ref;
    
    $templatevars['username']=$username . " (" . $useremail . ")";
    $userdata=get_user($userref);
    $templatevars["fullname"]=$userdata["fullname"];
    
    reset ($_POST);
    foreach ($_POST as $key=>$value)
        {
        if (strpos($key,"_label")!==false)
            {
            # Add custom field
            $setting=trim($_POST[str_replace("_label","",$key)]);
            if ($setting!="")
                {
                $message.=$value . ": " . $_POST[str_replace("_label","",$key)] . "\n\n";
                }
            }
        }
    if (trim($details)!="") {$message.=$lang["requestreason"] . ": " . newlines($details) . "\n\n";} elseif ($resource_request_reason_required) {return false;}
    
    # Add custom fields
    $c="";
    global $custom_request_fields,$custom_request_required;
    if (isset($custom_request_fields))
        {
        $custom=explode(",",$custom_request_fields);
    
        # Required fields?
        if (isset($custom_request_required)) {$required=explode(",",$custom_request_required);}
    
        for ($n=0;$n<count($custom);$n++)
            {
            if (isset($required) && in_array($custom[$n],$required) && getval("custom" . $n,"")=="")
                {
                return false; # Required field was not set.
                }
            
            $message.=i18n_get_translated($custom[$n]) . ": " . getval("custom" . $n,"") . "\n\n";
            }
        }
    
    $templatevars["requestreason"]=$message;
    
    $userconfirmmessage = $lang["requestsenttext"] . "\n\n$message";
    $message=$lang["user_made_request"] . "\n\n" . $lang["username"] . ": " . $username . "\n$message";
    $notification_message=$message;
    $message.=$lang["viewcollection"] . ":\n" .  $templatevars['url'];
    
    $admin_notify_emails=array();    
    $admin_notify_users=array();
    # Check if alternative request email notification address is set, only valid if collection contains resources of the same type 
    if(isset($resource_type_request_emails))
        {
        $requestrestypes=array_unique(sql_array("select r.resource_type as value from collection_resource cr left join resource r on cr.resource=r.ref where cr.collection='$ref'"));
        if(count($requestrestypes)==1 && isset($resource_type_request_emails[$requestrestypes[0]]))
            {
            $admin_notify_emails[]=$resource_type_request_emails[$requestrestypes[0]];
            }
        }
    else
        {
        $notify_users=get_notification_users("RESOURCE_ACCESS");
        foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_access_notifications', $send_message, $admin_resource_access_notifications);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
                $admin_notify_emails[] = $notify_user['email'];				
				}        
			else
				{
				$admin_notify_users[]=$notify_user["ref"];
				}
			}
        }
    foreach($admin_notify_emails as $admin_notify_email)
		{
        send_mail($admin_notify_email,$applicationname . ": " . $lang["requestcollection"] . " - $ref",$message,($always_email_from_user)?$useremail:$email_from,($always_email_from_user)?$useremail:$email_from,"emailcollectionrequest",$templatevars);
    	}
	
	if (count($admin_notify_users)>0)
		{
		global $userref;
        message_add($admin_notify_users,$notification_message,$templatevars["requesturl"],$userref, MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,COLLECTION_REQUEST, $ref);
		}
              
    if ($request_senduserupdates)
        {
        get_config_option($userref,'email_user_notifications', $send_email);    
        if($send_email && $useremail!="")
            {
            send_mail($useremail,$applicationname . ": " . $lang["requestsent"] . " - $ref",$userconfirmmessage,$email_from,$email_notify,"emailusercollectionrequest",$templatevars);
            }        
        else
            {
                
            global $userref;
            message_add($userref,$userconfirmmessage, $templatevars['url']);
            }
        }
    
    # Increment the request counter
    sql_query("update resource set request_count=request_count+1 where ref='$ref'");
    
    return true;
    }

function managed_collection_request($ref,$details,$ref_is_resource=false)
    {	
    # Request mode 1
    # Managed via the administrative interface
    
    # An e-mail is still sent.
    global $applicationname,$email_from,$baseurl,$email_notify,$username,$useremail,$userref,$lang,$request_senduserupdates,$watermark,$filename_field,$view_title_field,$access,$resource_type_request_emails, $manage_request_admin, $resource_request_reason_required, $admin_resource_access_notifications, $always_email_from_user;

    # Has a resource reference (instead of a collection reference) been passed?
    # Manage requests only work with collections. Create a collection containing only this resource.
    if ($ref_is_resource)
        {
        $admin_mail_template="emailresourcerequest";
        $user_mail_template="emailuserresourcerequest";
        
        $resourcedata=get_resource_data($ref);
        $templatevars['thumbnail']=get_resource_path($ref,true,"thm",false,"jpg",$scramble=-1,$page=1,($watermark)?(($access==1)?true:false):false);

        # Allow alternative configuration settings for this resource type
        resource_type_config_override($resourcedata['resource_type']);
        
        if (!file_exists($templatevars['thumbnail'])){
        $templatevars['thumbnail']="../gfx/".get_nopreview_icon($resourcedata["resource_type"],$resourcedata["file_extension"],false);
        }
        $templatevars['url']=$baseurl."/?r=".$ref;
        if (isset($filename_field)){
        $templatevars["filename"]=$lang["fieldtitle-original_filename"] . ": " . get_data_by_field($ref,$filename_field);}
        if (isset($resourcedata["field" . $view_title_field])){
        $templatevars["title"]=$resourcedata["field" . $view_title_field];}
        
        $c=create_collection($userref,$lang["request"] . " " . date("ymdHis"));
        add_resource_to_collection($ref,$c);
        $ref=$c; # Proceed as normal
        }
    else {
    
        $admin_mail_template="emailcollectionrequest";
        $user_mail_template="emailusercollectionrequest";
    
        $collectiondata=get_collection($ref);
        $templatevars['url']=$baseurl."/?c=".$ref;
        if (isset($collectiondata["name"])){
        $templatevars["title"]=$collectiondata["name"];}
        }

    # Formulate e-mail text
    $templatevars['username']=$username;
    $templatevars["useremail"]=$useremail;
    $userdata=get_user($userref);
    $templatevars["fullname"]=$userdata["fullname"];
    
    $message="";
    reset ($_POST);
    foreach ($_POST as $key=>$value)
        {
        if (strpos($key,"_label")!==false)
            {
            # Add custom field
            $setting=trim($_POST[str_replace("_label","",$key)]);
            if ($setting!="")
                {
                $message.=$value . ": " . $setting . "\n\n";
                }
            }
        }
    if (trim($details)!="") {$message.=$lang["requestreason"] . ": " . newlines($details) . "\n\n";}  elseif ($resource_request_reason_required) {return false;}
    
    # Add custom fields
    $c="";
    global $custom_request_fields,$custom_request_required;
    if (isset($custom_request_fields))
        {
        $custom=explode(",",$custom_request_fields);
    
        # Required fields?
        if (isset($custom_request_required)) {$required=explode(",",$custom_request_required);}
    
        for ($n=0;$n<count($custom);$n++)
            {
            if (isset($required) && in_array($custom[$n],$required) && getval("custom" . $n,"")=="")
                {
                return false; # Required field was not set.
                }
            
            $message.=i18n_get_translated($custom[$n]) . ": " . getval("custom" . $n,"") . "\n\n";
            }
        }
    
    # Create the request
    global $request_query;
    $request_query = "insert into request(user,collection,created,request_mode,status,comments) values ('$userref','$ref',now(),1,0,'" . escape_check($message) . "')";

    global $notify_manage_request_admin, $assigned_to_user, $admin_resource_access_notifications;
    $notify_manage_request_admin = false;
	$notification_sent = false;
    // Manage individual requests of resources:
    hook('autoassign_individual_requests', '', array($userref, $ref, $message, isset($collectiondata)));
    if(isset($manage_request_admin) && $ref_is_resource)
		{
		$request_resource_type = $resourcedata["resource_type"];
		if(array_key_exists($request_resource_type, $manage_request_admin)) 
			{
			$admin_notify_user=$manage_request_admin[$request_resource_type];
			$request_query = sprintf("
                    INSERT INTO request(
                                            user,
                                            collection,
                                            created,
                                            request_mode,
                                            `status`,
                                            comments,
                                            assigned_to
                                       )
                         VALUES (
                                     '%s',
                                     '%s',
                                     NOW(),
                                     1,
                                     0,
                                     '%s',
                                     '%s'
                                );
                ",
                $userref,
                $ref,
                escape_check($message),
                $admin_notify_user
            );
			$notify_manage_request_admin = true;
			}
		}
    $amendedmessage=hook('amend_request_message','', array($userref, $ref, isset($collectiondata) ? $collectiondata : array(), $message, isset($collectiondata)));
    if($amendedmessage)
        {
        $message=$amendedmessage;
        }
    
    // Manage collection requests:
    hook('autoassign_collection_requests', '', array($userref, isset($collectiondata) ? $collectiondata : array(), $message, isset($collectiondata)));
    
    if(isset($manage_request_admin) && isset($collectiondata)) 
		{
        // $all_r_types = get_resource_types();
        // foreach ($all_r_types as $r_type) 
			// {
            // $all_resource_types[] = $r_type['ref']; 
			// }   

        $resources = get_collection_resources($collectiondata['ref']);

        // Get distinct resource types found in this collection:
        $collection_resources_by_type = array();
        foreach ($resources as $resource_id) 
			{
            $resource_data = get_resource_data($resource_id);
            // Create a list of resource IDs based on type to separate them into different collections:
            $collection_resources_by_type[$resource_data['resource_type']][] = $resource_id;
			}

        // Split into collections based on resource type:
        foreach ($collection_resources_by_type as $collection_type => $collection_resources)
			{
            // Store all resources of unmanaged type in one collection which will be sent to the system administrator:
            if(!isset($manage_request_admin[$collection_type]))
				{
                $collections['not_managed'] = create_collection($userref, $collectiondata['name'] . ' for unmanaged types');
                foreach ($collection_resources as $collection_resource_id) 
					{
                    add_resource_to_collection($collection_resource_id, $collections['not_managed']);
					}
                continue;
				}            
            $collections[$collection_type] = create_collection($userref, $collectiondata['name'] . ' for type ' . $collection_type);
            foreach ($collection_resources as $collection_resource_id)
				{
                add_resource_to_collection($collection_resource_id, $collections[$collection_type]);
				}
			}
        
        if(isset($collections) && count($collections) > 1)
            {
            foreach ($collections as $request_resource_type => $collection_id)
				{
                $assigned_to = '';
                $assigned_to_users=array();
                $assigned_to_user_emails=array();
                if(array_key_exists($request_resource_type, $manage_request_admin))
                    {
                    $assigned_to = $manage_request_admin[$request_resource_type];
                    get_config_option($assigned_to,'email_user_notifications', $send_email);
                    if($send_email)
                        {
                        // We need the user's email address
                        $assigned_to_user = get_user($manage_request_admin[$request_resource_type]);
                        $assigned_to_user_emails[] = $assigned_to_user['email'];
                        }
                    else
                        {
                        // We will be sending a notification message 
                        $assigned_to_users[] = get_user($assigned_to);
                        }
                    }
                else
                    {
                    // No specific user allocated, get all users, adding $email_notify address if this does not belong to a system user
                    $assigned_to_users=get_notification_users("RESOURCE_ACCESS"); 
                    foreach ($assigned_to_users as $assigned_to_user)
                        {
                        if($assigned_to_user['email']==$email_notify)
                            {
                            $email_notify_is_user=true;
                            }
                        }
                    if(!$email_notify_is_user){$assigned_to_user_emails[] = $email_notify;}                        
                    }

                if(trim($assigned_to) != '')
                    {
                    $request_query = sprintf("
                        INSERT INTO request(
                                                user,
                                                collection,
                                                created,
                                                request_mode,
                                                `status`,
                                                comments,
                                                assigned_to
                                           )
                             VALUES (
                                         '%s',
                                         '%s',
                                         NOW(),
                                         1,
                                         0,
                                         '%s',
                                         '%s'
                                    );
                    ",
                    $userref,
                    $collection_id,
                    escape_check($message),
                    $assigned_to
                    );
                    }
                else
                    {
                    $request_query = sprintf("
                        INSERT INTO request(
                                                user,
                                                collection,
                                                created,
                                                request_mode,
                                                `status`,
                                                comments
                                           )
                             VALUES (
                                         '%s',
                                         '%s',
                                         NOW(),
                                         1,
                                         0,
                                         '%s'
                                    );
                    ",
                    $userref,
                    $collection_id,
                    escape_check($message)
                    );
                    }

                sql_query($request_query);
                $request = sql_insert_id();

                
                // Send the mail
                $email_message = $lang['requestassignedtoyoumail'] . "\n\n" . $baseurl . "/?q=" . $request . "\n";
                
                foreach ($assigned_to_users as $assigned_to_user)
                    {
                    get_config_option($assigned_to_user,'email_user_notifications', $send_email);
                    if($send_email)
                        {  
                        $assigned_to_user_emails[] = $assigned_to_user['email'];
                        }
                    else
                        {
                        $assigned_to_notify_users[] = $assigned_to_user['ref'];
                        }
                    }
                    
                foreach ($assigned_to_user_emails as $assigned_to_user_email)
                    {
                    send_mail($assigned_to_user_email, $applicationname . ': ' . $lang['requestassignedtoyou'], $email_message);
                    }
                if (count($assigned_to_notify_users) > 0)
                    {
					message_add($assigned_to_notify_users,$lang['requestassignedtoyou'],$baseurl . "/?q=" . $request,$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,MANAGED_REQUEST, $request);
                    }    
                unset($email_message);
				}

            $notify_manage_request_admin = false;
			$notification_sent = true;
            }
        else
            {
            $ref = implode('', $collections);
            }

		}

    if(hook('bypass_end_managed_collection_request', '', array(!isset($collectiondata), $ref, $request_query, $message, $templatevars, $assigned_to_user, $admin_mail_template, $user_mail_template))) {
        return true;
    }

    sql_query($request_query);
    $request=sql_insert_id();
    $templatevars["request_id"]=$request;
    $templatevars["requesturl"]=$baseurl."/?q=".$request;
    $templatevars["requestreason"]=$message;
    hook("afterrequestcreate", "", array($request));

    # Automatically notify the admin who was assigned the request if we set this earlier:
    if($notify_manage_request_admin)
        {
        $notification_message = $lang['requestassignedtoyoumail'];
		$request_url = $baseurl . "/?q=" . $request;
		$admin_message = $notification_message . "\n\n" . $request_url . "\n";
		get_config_option($assigned_to_user,'email_user_notifications', $send_email, false);  // Don't get default as we may get the requesting user's preference
        if($send_email)
            {
			$assigned_to_user = get_user($admin_notify_user);
            send_mail($assigned_to_user['email'], $applicationname . ': ' . $lang['requestassignedtoyou'], $admin_message);
            }        
        else
            {   
            message_add($admin_notify_user,$notification_message, $request_url,$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,MANAGED_REQUEST, $request);
            }
		$notification_sent = true;
        }
		
    $admin_notify_emails=array();    
    $admin_notify_users=array();
    # Check if alternative request email notification address is set, only valid if collection contains resources of the same type 
    if(isset($resource_type_request_emails))
        {
        $requestrestypes=array_unique(sql_array("select r.resource_type as value from collection_resource cr left join resource r on cr.resource=r.ref where cr.collection='$ref'"));
        if(count($requestrestypes)==1 && isset($resource_type_request_emails[$requestrestypes[0]]))
            {
            $admin_notify_emails[]=$resource_type_request_emails[$requestrestypes[0]];
            }
        }
	elseif(!$notification_sent)
		{
        $notify_users=get_notification_users("RESOURCE_ACCESS");
		foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_access_notifications', $send_message, $admin_resource_access_notifications);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
                $admin_notify_emails[] = $notify_user['email'];	
				}        
			else
				{
				$admin_notify_users[]=$notify_user["ref"];
				}
			}
        }
	# Send the e-mails and/or notification messages   
	$admin_notify_message=$lang["user_made_request"]. "<br /><br />" . $lang["username"] . ": " . $username . "<br />$message<br /><br />";
    $notification_message = $lang["user_made_request"]. "<br />" . $lang["username"] . ": " . $username . "<br />" . $message;
	$admin_notify_message.=$lang["clicktoviewresource"] . "<br />" . $templatevars["requesturl"];
	foreach($admin_notify_emails as $admin_notify_email)
		{
        send_mail($admin_notify_email,$applicationname . ": " . $lang["requestcollection"] . " - $ref",$admin_notify_message,($always_email_from_user)?$useremail:$email_from,($always_email_from_user)?$useremail:$email_from,$admin_mail_template,$templatevars);
    	}
	if (count($admin_notify_users)>0)
		{
		global $userref;		
        message_add($admin_notify_users,$notification_message,$templatevars["requesturl"],$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,MANAGED_REQUEST,$request);
		}
		
    
	if ($request_senduserupdates)
		{
		$userconfirm_notification = $lang["requestsenttext"] . "<br /><br />" . $message;
		$userconfirmmessage = $userconfirm_notification . "<br /><br />" . $lang["clicktoviewresource"] . "<br />$baseurl/?c=$ref";
		get_config_option($userref,'email_user_notifications', $send_email);    
        if($send_email && $useremail!="")
            {
			send_mail($useremail,$applicationname . ": " . $lang["requestsent"] . " - $ref",$userconfirmmessage,$email_from,$email_notify,$user_mail_template,$templatevars);
            }        
        else
            {  
            global $userref;
            message_add($userref,$userconfirm_notification, $templatevars['url']);
            }
		}    
    
    # Increment the request counter
    sql_query("update resource set request_count=request_count+1 where ref='$ref'");
    
    return true;
    }


function email_resource_request($ref,$details)
    {
    # E-mails a basic resource request for a single resource (posted) to the team
    # (not a managed request)
    
    global $applicationname,$email_from,$baseurl,$email_notify,$username,$useremail,$userref,$lang,$request_senduserupdates,$watermark,$filename_field,$view_title_field,$access,$resource_type_request_emails,$resource_request_reason_required, $admin_resource_access_notifications;
    
    $resourcedata=get_resource_data($ref);
    $templatevars['thumbnail']=get_resource_path($ref,true,"thm",false,"jpg",$scramble=-1,$page=1,($watermark)?(($access==1)?true:false):false);
    if (!file_exists($templatevars['thumbnail'])){
        $templatevars['thumbnail']="../gfx/".get_nopreview_icon($resourcedata["resource_type"],$resourcedata["file_extension"],false);
    }

    if (isset($filename_field)){
    $templatevars["filename"]=$lang["fieldtitle-original_filename"] . ": " . get_data_by_field($ref,$filename_field);}
    if (isset($resourcedata["field" . $view_title_field])){
    $templatevars["title"]=$resourcedata["field" . $view_title_field];}
    $templatevars['username']=$username . " (" . $useremail . ")";
    $templatevars['formemail']=getval("email","");
    $templatevars['url']=$baseurl."/?r=".$ref;
    $templatevars["requesturl"]=$templatevars['url'];
    
    $userdata=get_user($userref);
    $templatevars["fullname"]=$userdata["fullname"];
    
    $htmlbreak="";
    global $use_phpmailer;
    if ($use_phpmailer){$htmlbreak="<br><br>";}
    
    $list="";
    reset ($_POST);
    foreach ($_POST as $key=>$value)
        {
        if (strpos($key,"_label")!==false)
            {
            # Add custom field  
            $data="";
            $data=$_POST[str_replace("_label","",$key)];
            $list.=$htmlbreak. $value . ": " . $data."\n";
            }
        }
    $list.=$htmlbreak;      
    $templatevars['list']=$list;

    $templatevars['details']=stripslashes($details);
    $adddetails="";
    if ($templatevars['details']!=""){$adddetails=$lang["requestreason"] . ": " . newlines($templatevars['details'])."\n\n";} elseif ($resource_request_reason_required) {return false;}
    
    # Add custom fields
    $c="";
    global $custom_request_fields,$custom_request_required;
    if (isset($custom_request_fields))
        {
        $custom=explode(",",$custom_request_fields);
    
        # Required fields?
        if (isset($custom_request_required)) {$required=explode(",",$custom_request_required);}
    
        for ($n=0;$n<count($custom);$n++)
            {
            if (isset($required) && in_array($custom[$n],$required) && getval("custom" . $n,"")=="")
                {
                return false; # Required field was not set.
                }
            
            $c.=i18n_get_translated($custom[$n]) . ": " . getval("custom" . $n,"") . "\n\n";
            }
        }
    $templatevars["requestreason"]=$lang["requestreason"] . ": " . $templatevars['details']. $c ."";
    
    $message=$lang["user_made_request"] . "<br /><br />";
    $message.= isset($username)? $lang["username"] . ": " . $username . " (" . $useremail . ")<br />":"";
    $message.= (!empty($templatevars["formemail"]))? $lang["email"].":".$templatevars["formemail"]."<br />":"";
	$notification_message = $message . $adddetails . $c ; 
    $message.= $adddetails. $c . "<br /><br />" . $lang["clicktoviewresource"] . "<br />". $templatevars['url'];
	

    $admin_notify_emails=array();    
    $admin_notify_users=array();
    # Check if alternative request email notification address is set
	if(isset($resource_type_request_emails))
        {
        if(isset($resource_type_request_emails[$resourcedata["resource_type"]]))
            {
            $admin_notify_emails[]=$resource_type_request_emails[$resourcedata["resource_type"]];
            }
        } 
    else
        {
		$admin_notify_emails[]=$email_notify;
        $notify_users=get_notification_users("RESOURCE_ACCESS");
        foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_access_notifications', $send_message, $admin_resource_access_notifications);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
                $admin_notify_emails[] = $notify_user['email'];				
				}        
			else
				{
				$admin_notify_users[]=$notify_user["ref"];
				}
			}
        }
		
    foreach($admin_notify_emails as $admin_notify_email)
		{
		send_mail($admin_notify_email,$applicationname . ": " . $lang["requestresource"] . " - $ref",$message,$useremail,$useremail,"emailresourcerequest",$templatevars);
		}
	
	if (count($admin_notify_users)>0)
		{
		global $userref;
        message_add($admin_notify_users,$notification_message,$templatevars["requesturl"]);
		}
              
    if ($request_senduserupdates)
        { 
		$k=(getval("k","")!="")? "&k=".getval("k",""):"";
		$userconfirmmessage = $lang["requestsenttext"] . "<br /><br />" . $lang["requestreason"] . ": " . $templatevars['details'] . $c . "<br /><br />" . $lang["clicktoviewresource"] . "\n$baseurl/?r=$ref".$k;
		if(isset($userref))
			{
	        get_config_option($userref,'email_user_notifications', $send_email);    
			if($send_email && $useremail!="")
				{
				send_mail($useremail,$applicationname . ": " . $lang["requestsent"] . " - $ref",$userconfirmmessage,$email_from,$email_notify,"emailusercollectionrequest",$templatevars);
				}        
			else
				{               
				message_add($userref,$userconfirmmessage, $templatevars['url']);
				}
			}
		else
			{
			$sender =  (!empty($useremail))? $useremail : (!empty($templatevars["formemail"]))? $templatevars["formemail"] :"";
			
			if($sender!=""){send_mail($sender,$applicationname . ": " . $lang["requestsent"] . " - $ref",$userconfirmmessage,$email_from,$email_notify,"emailuserresourcerequest",$templatevars);}  
			}
        }
	
    # Increment the request counter
    sql_query("update resource set request_count=request_count+1 where ref='$ref'");
    }

