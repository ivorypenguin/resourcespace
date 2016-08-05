<?php
include_once(dirname(__FILE__) . '/../include/autoassign_general.php');

function HookAutoassign_mrequestsAllAutoassign_individual_requests($user_ref, $collection_ref, $message, $manage_collection_request)
{
    global $manage_request_admin, $assigned_to_user, $notify_manage_request_admin, $request_query;

    // Do not process this any further as this only handles indivual resource requests
    if($manage_collection_request) {
        return true;
    }

    $resources              = get_collection_resources($collection_ref);
    $resource_data          = get_resource_field_data($resources[0]); // in this case it should only have one resource
    $mapped_fields          = get_mapped_fields();
    $assigned_administrator = 0;

    foreach ($resource_data as $r_data) {
        if(in_array($r_data['ref'], $mapped_fields)) {
            $assigned_administrator = get_mapped_user_by_field($r_data['ref'], $r_data['value']);
            break;
        }
    }

    // Default back to auto assign based on resource type (if set to do so)
    if($assigned_administrator === 0 && isset($manage_request_admin) && !$manage_collection_request) {
        return false;
    }

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
                             '%s',  # user
                             '%s',  # collection
                             NOW(),   #created
                             1,       # request_mode
                             0,       # status
                             '%s',  # comments
                             '%s'   # assigned_to
                        );
        ",
        $user_ref,
        $collection_ref,
        escape_check($message),
        $assigned_administrator
    );

    $assigned_to_user = get_user($assigned_administrator);
    $notify_manage_request_admin = true;


    // If we've got this far, make sure auto assigning managed requests based on resource types won't overwrite this
    unset($manage_request_admin);

    return true;
}

function HookAutoassign_mrequestsAllAutoassign_collection_requests($user_ref, $collection_data, $message, $manage_collection_request)
{
    global $manage_request_admin, $assigned_to_user, $email_notify, $lang, $baseurl, $applicationname, $request_query, $notify_manage_request_admin;

    // Do not process this any further as this should only handle collection requests
    if(!$manage_collection_request) {
        return false;
    }

    $resources                             = get_collection_resources($collection_data['ref']);
    $mapped_fields                         = get_mapped_fields();
    $collection_resources_by_assigned_user = array();
    $collections                           = array();


    // Build the collections map between asigned user and resources the collection should contain
    foreach ($resources as $resource) {
        $resource_data          = get_resource_field_data($resource);
        $assigned_administrator = 0;
        $resource_not_assigned  = true;

        foreach ($resource_data as $r_data) {
            if(in_array($r_data['ref'], $mapped_fields)) {
                $assigned_administrator = get_mapped_user_by_field($r_data['ref'], $r_data['value']);

                if($assigned_administrator === 0) {
                    $collection_resources_by_assigned_user['not_managed'][] = $resource;
                } else {
                    $collection_resources_by_assigned_user[$assigned_administrator][] = $resource;
                }

                $resource_not_assigned = false;
                break;
            }
        }

        if($resource_not_assigned && !isset($manage_request_admin)) {
            $collection_resources_by_assigned_user['not_managed'][] = $resource;
        }
    }


    // Create collections based on who is supposed to handle the request
    foreach ($collection_resources_by_assigned_user as $assigned_user_id => $collection_resources) {
        if($assigned_user_id === 'not_managed') {
            $collections['not_managed'] = create_collection($user_ref, $collection_data['name'] . ' request for unmanaged resources');
            foreach ($collection_resources as $collection_resource_id) {
                add_resource_to_collection($collection_resource_id, $collections['not_managed']);
            }
            continue;
        }
        
        $user = get_user($assigned_user_id);
        $collections[$assigned_user_id] = create_collection($user_ref, $collection_data['name'] . ' request - managed by ' . $user['email']);
        foreach ($collection_resources as $collection_resource_id) {
            add_resource_to_collection($collection_resource_id, $collections[$assigned_user_id]);
        }

        // Attach assigned admin to this collection
        add_collection($user['ref'], $collections[$assigned_user_id]);
    }

    if(!empty($collections)) {
        foreach ($collections as $assigned_to => $collection_id) {
            $assigned_to_user = get_user($assigned_to);
            $request_query    = sprintf("
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
                                     '%s',  # user
                                     '%s',  # collection
                                     NOW(), # created
                                     1,     # request_mode
                                     0,     # status
                                     '%s',  # comments
                                     '%s'   # assigned_to
                                );
                ",
                $user_ref,
                $collection_id,
                escape_check($message),
                $assigned_to
            );

            if($assigned_to === 'not_managed' || !$assigned_to_user) {
                $assigned_to_user['email'] = $email_notify;
                $request_query             = sprintf("
                        INSERT INTO request(
                                                user,
                                                collection,
                                                created,
                                                request_mode,
                                                `status`,
                                                comments
                                           )
                             VALUES (
                                         '%s',  # user
                                         '%s',  # collection
                                         NOW(), # created
                                         1,     # request_mode
                                         0,     # status
                                         '%s'   # comments
                                    );
                    ",
                    $user_ref,
                    $collection_id,
                    escape_check($message),
                    $assigned_to
                );
            }

            sql_query($request_query);
            $request = sql_insert_id();

            // Send the mail:
            $email_message = $lang['requestassignedtoyoumail'] . "\n\n" . $baseurl . "/?q=" . $request . "\n";
            send_mail($assigned_to_user['email'], $applicationname . ': ' . $lang['requestassignedtoyou'], $email_message);

            unset($email_message);
        }

        $notify_manage_request_admin = false;

    }


    // If we've got this far, make sure auto assigning managed requests based on resource types won't overwrite this
    unset($manage_request_admin);

    return true;
}

function HookAutoassign_mrequestsAllBypass_end_managed_collection_request($manage_individual_requests, $collection_id, $request_query, $message, $templatevars, $assigned_to_user, $admin_mail_template, $user_mail_template)
{
    global $applicationname, $baseurl, $email_from, $email_notify, $lang, $username, $useremail, 
           $manage_request_admin, $notify_manage_request_admin, $resource_type_request_emails, $request_senduserupdates;

    // Collection requests have already sent e-mails so skip this step
    if(!$manage_individual_requests) {
        // Because we are bypassing the end of managed_collection_request function we need to return true
        return true;
    }

    sql_query($request_query);
    $request = sql_insert_id();

    $templatevars['request_id']    = $request;
    $templatevars['requesturl']    = $baseurl . '/?q=' . $request;
    $templatevars['requestreason'] = $message;

    # Automatically notify the admin who was assigned the request:
    if($notify_manage_request_admin) {
        // Attach assigned admin to this collection
        add_collection($assigned_to_user['ref'], $collection_id);

        $assigned_user_mail_message = $lang['requestassignedtoyoumail'] . "\n\n" . $baseurl . '/?q=' . $request . "\n";
        send_mail($assigned_to_user['email'], $applicationname . ': ' . $lang['requestassignedtoyou'], $assigned_user_mail_message);
    }

    
    # Check if alternative request email notification address is set, only valid if collection contains resources of the same type 
    $admin_notify_email = $email_notify;
    if(isset($resource_type_request_emails)) {
        $requestrestypes = array_unique(
            sql_array('SELECT r.resource_type AS value FROM collection_resource cr LEFT JOIN resource r ON cr.resource = r.ref WHERE cr.collection = "' . $collection_id . '"')
        );
        
        if(count($requestrestypes) == 1 && isset($resource_type_request_emails[$requestrestypes[0]])) {
            $admin_notify_email = $resource_type_request_emails[$requestrestypes[0]];
        }
    }

    # Send the e-mail
    $message  = $lang['user_made_request'] . '<br/><br/>' . $lang['username'] . ': ' . $username . '<br/>' . $message . '<br/><br/>';
    $message .= $lang['clicktoviewresource'] . '<br/>' . $baseurl . '/?q=' . $request;
    send_mail($admin_notify_email, $applicationname . ': ' . $lang['requestcollection'] . ' - ' . $collection_id, $message, $useremail, $useremail, $admin_mail_template, $templatevars);

    if($request_senduserupdates) {
        $user_confirm_message = $lang['requestsenttext'] . '<br/><br/>' . $message . '<br/><br/>' . $lang['clicktoviewresource'] . '<br/>' . $baseurl . '/?c=' . $collection_id;
        send_mail($useremail, $applicationname . ': ' . $lang['requestsent'] . ' - ' . $collection_id, $user_confirm_message, $email_from, $email_notify, $user_mail_template, $templatevars);
    }

    return true;
}
?>