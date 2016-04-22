<?php
# Resource functions
# Functions to create, edit and index resources

include_once __DIR__ . '/definitions.php';		// includes log code definitions for resource_log() callers.

function create_resource($resource_type,$archive=999,$user=-1)
	{
	# Create a new resource.
	global $always_record_resource_creator,$index_contributed_by;

	if ($archive==999)
		{
		# Work out an appropriate default state
		$archive=0;
		if (!checkperm("e0")) {$archive=2;} # Can't create a resource in normal state? create in archive.
		}
	if ($archive==-2 || $archive==-1 || (isset($always_record_resource_creator) and $always_record_resource_creator))
		{
		# Work out user ref - note: only for content in status -2 and -1 (user submitted / pending review).
		global $userref;
		$user=$userref;
		} else {$user=-1;}
	sql_query("insert into resource(resource_type,creation_date,archive,created_by) values ('$resource_type',now(),'$archive','$user')");
	
	$insert=sql_insert_id();
	
	# set defaults for resource here (in case there are edit filters that depend on them)
	set_resource_defaults($insert);	
	
	# Autocomplete any blank fields.
	autocomplete_blank_fields($insert);

	# Always index the resource ID as a keyword
	remove_keyword_mappings($insert, $insert, -1);
	add_keyword_mappings($insert, $insert, -1);

	# Log this			
	daily_stat("Create resource",$insert);
	resource_log($insert,'c',0);
	
	# Also index contributed by field, unless disabled
	if ($index_contributed_by)
		{
		$resource=get_resource_data($insert);
		$userinfo=get_user($resource["created_by"]);
		add_keyword_mappings($insert,$userinfo["username"] . " " . $userinfo["fullname"],-1);
		}

	# Copying a resource of the 'pending review' state? Notify, if configured.
	if ($archive==-1)
		{
		notify_user_contributed_submitted(array($insert));
		}

	return $insert;
	}
	
function save_resource_data($ref,$multi,$autosave_field="")
	{
	# Save all submitted data for resource $ref.
	# Also re-index all keywords from indexable fields.
		
	global $lang, $auto_order_checkbox,$userresourcedefaults,$multilingual_text_fields,$languages,$language,$user_resources_approved_email;

	hook("befsaveresourcedata", "", array($ref));

	# save resource defaults
	# (do this here so that user can override them if the fields are visible.)
	if ($autosave_field=="") {set_resource_defaults($ref);	}

	# Loop through the field data and save (if necessary)
	$errors=array();
	$fields=get_resource_field_data($ref,$multi, !hook("customgetresourceperms"));
	$expiry_field_edited=false;
	$resource_data=get_resource_data($ref);
		
	# Load the configuration for the selected resource type. Allows for alternative notification addresses, etc.
	resource_type_config_override($resource_data["resource_type"]);                
    
	# Set up arrays of node ids to add/remove. We can't remove all nodes as user may not have access
	$nodes_to_add=array();
	$nodes_to_remove=array();
		
	for ($n=0;$n<count($fields);$n++)
		{
		if (!(
		
		# Not if field has write access denied
		checkperm("F" . $fields[$n]["ref"])
		||
		(checkperm("F*") && !checkperm("F-" . $fields[$n]["ref"]))
			
		)
                && ($autosave_field=="" || $autosave_field==$fields[$n]["ref"] || (is_array($autosave_field) && in_array($fields[$n]["ref"],$autosave_field)))
                )
			{

            node_field_options_override($fields[$n]);
			if ($fields[$n]["type"]==2)
				{
				# construct the value from the ticked boxes
				$val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
				//$options=trim_array(explode(",",$fields[$n]["options"]));
				
                foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					$name=$fields[$n]["ref"] . "_" . md5($nodedata['name']);
					if (getval($name,"")=="yes")
						{
						if ($val!=",") {$val .= ",";}
						$val .= $nodedata['name'];
						$nodes_to_add[] = $noderef;
						}
					else
						{
						$nodes_to_remove[] = $noderef;
						}
					}
				
				/*for ($m=0;$m<count($fields[$n]['node_options']);$m++)
					{
					$name=$fields[$n]["ref"] . "_" . md5($fields[$n]['node_options'][$m]);
					if (getval($name,"")=="yes")
						{
						if ($val!=",") {$val.=",";}
						$val.=$fields[$n]['node_options'][$m];
						}
					}
				*/
				}
			elseif ($fields[$n]["type"]==4 || $fields[$n]["type"]==6 || $fields[$n]["type"]==10)
				{
				# date type, construct the value from the date/time dropdowns
				$val=sprintf("%04d", getvalescaped("field_" . $fields[$n]["ref"] . "-y",""));
				if ((int)$val<=0) 
					{
					$val="";
					}
				elseif (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-m",""))!="") 
					{
					$val.="-" . $field;
					if (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-d",""))!="") 
						{
						$val.="-" . $field;
						if (($field=getval("field_" . $fields[$n]["ref"] . "-h",""))!="")
							{
							$val.=" " . $field . ":";
							if (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-i",""))!="") 
								{
									$val.=$field;
								} 
							else 
								{
									$val.="00";
								}
							}
						}
					}
				}
			elseif ($multilingual_text_fields && ($fields[$n]["type"]==0 || $fields[$n]["type"]==1 || $fields[$n]["type"]==5))
				{
				# Construct a multilingual string from the submitted translations
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");
				$val="~" . $language . ":" . $val;
				reset ($languages);
				foreach ($languages as $langkey => $langname)
					{
					if ($language!=$langkey)
						{
						$val.="~" . $langkey . ":" . getvalescaped("multilingual_" . $n . "_" . $langkey,"");
						}
					}
				}
			elseif ($fields[$n]["type"] == 3)
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");	
				foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					if (strip_leading_comma($val) == $nodedata['name'])
						{
						$nodes_to_add[] = $noderef;
						}
					else
						{
						$nodes_to_remove[] = $noderef;
						}
					}							
				// if it doesn't already start with a comma, add one
				if (substr($val,0,1) != ',')
					{
					$val = ','.$val;
					}				
				}
			elseif ($fields[$n]["type"] == 12)
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");	
				foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					if (in_array(strip_leading_comma($val),i18n_get_translations($nodedata['name'])))
						{
						$nodes_to_add[] = $noderef;
						// Correct the string to include all multingual strings as for dropdowns
						$val=$nodedata['name'];
						}
					else
						{
						$nodes_to_remove[] = $noderef;
						}
					}							
				// if it doesn't already start with a comma, add one
				if (substr($val,0,1) != ',')
					{
					$val = ','.$val;
					}				
				}
            elseif ($fields[$n]["type"] == 7 || $fields[$n]["type"]==9) // Category tree or dynamic keywords
				{
                $submittedval=getval("field_" . $fields[$n]["ref"],"");
				$submittedvals=explode("|",$submittedval);
                $newvals=array();
                foreach($fields[$n]["nodes"] as $noderef => $nodedata)
                    {
               
                    $addnode=false;
                    foreach($submittedvals as $checkval)
                        {
                        if (trim($checkval) == trim($nodedata['name']))
                            {
                            $addnode=true;                            
                            }                        
                        }
                    if($addnode)
                        {
                        $nodes_to_add[] = $noderef;
                        // Correct the string to include all multingual strings as for dropdowns
                        $newvals[]=escape_check($nodedata['name']);    
                        }
                    else
                        {
                        $nodes_to_remove[] = $noderef;    
                        }
                    }
				$val = ',' . implode(",",$newvals);
				}
			else
				{
				# Set the value exactly as sent.
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");
				} 
			
			# Check for regular expression match
			if (trim(strlen($fields[$n]["regexp_filter"]))>=1 && strlen($val)>0)
				{
				if(preg_match("#^" . $fields[$n]["regexp_filter"] . "$#",$val,$matches)<=0)
					{
					global $lang;
					debug($lang["information-regexp_fail"] . ": -" . "reg exp: " . $fields[$n]["regexp_filter"] . ". Value passed: " . $val);
					if (getval("autosave","")!="")
						{
						exit();
						}
					$errors[$fields[$n]["ref"]]=$lang["information-regexp_fail"] . " : " . $val;
					continue;
					}
				}
			$modified_val=hook("modifiedsavedfieldvalue",'',array($fields,$n,$val));
			if(!empty($modified_val)){$val=$modified_val;}
			
			$error=hook("additionalvalcheck", "all", array($fields, $fields[$n]));
			if ($error) 
			    {
			    global $lang;
			    if (getval("autosave","")!="")
			    	{
			    	exit($error);
			    	}
			    $errors[$fields[$n]["ref"]]=$error;
			    continue;
			    }

		    // Required fields cannot have empty values
		    if(1 == $fields[$n]['required'] && '' == $fields[$n]['display_condition'] && '' == strip_leading_comma($val) && '' == $autosave_field)
                {
                $errors[$fields[$n]['ref']] = i18n_get_translated($fields[$n]['title']) . ': ' . $lang['requiredfield'];

                continue;
                }
            else if(1 == $fields[$n]['required'] && '' == $fields[$n]['display_condition'] && '' == strip_leading_comma($val) && '' != $autosave_field)
                {
                echo $lang['requiredfield'];

                exit();
                }

			if (str_replace("\r\n","\n",$fields[$n]["value"])!== str_replace("\r\n","\n",unescape($val)))
				{
				//$testvalue=$fields[$n]["value"];var_dump($testvalue);$val=unescape($val);var_dump($val);
				//echo "FIELD:".$fields[$n]["value"]."!==ORIG:".unescape($val); 
				
				$oldval=$fields[$n]["value"];

				# This value is different from the value we have on record.

				# Write this edit to the log (including the diff) (unescaped is safe because the diff is processed later)
				resource_log($ref,'e',$fields[$n]["ref"],"",$fields[$n]["value"],unescape($val));

				# Expiry field? Set that expiry date(s) have changed so the expiry notification flag will be reset later in this function.
				if ($fields[$n]["type"]==6) {$expiry_field_edited=true;}

				# If 'resource_column' is set, then we need to add this to a query to back-update
				# the related columns on the resource table
				$resource_column=$fields[$n]["resource_column"];	

				# Purge existing data and keyword mappings, decrease keyword hitcounts.
				sql_query("delete from resource_data where resource='$ref' and resource_type_field='" . $fields[$n]["ref"] . "'");
				
				# Insert new data and keyword mappings, increase keyword hitcounts.
				sql_query("insert into resource_data(resource,resource_type_field,value) values('$ref','" . $fields[$n]["ref"] . "','" . escape_check($val) ."')");
								
				if ($fields[$n]["type"]==3 && substr($oldval,0,1) != ',')
					{
					# Prepend a comma when indexing dropdowns
					$oldval="," . $oldval;
					}
				
				if ($fields[$n]["keywords_index"]==1)
					{
					# Date field? These need indexing differently.
					$is_date=($fields[$n]["type"]==4 || $fields[$n]["type"]==6);

					$is_html=($fields[$n]["type"]==8);					
					
					remove_keyword_mappings($ref, i18n_get_indexable($oldval), $fields[$n]["ref"], $fields[$n]["partial_index"],$is_date,'','',$is_html);
					add_keyword_mappings($ref, i18n_get_indexable($val), $fields[$n]["ref"], $fields[$n]["partial_index"],$is_date,'','',$is_html);
					}
                else
                    {
                    // Remove all entries from resource_keyword for this field, useful if setting is changed and changed back leaving stale data
                    remove_all_keyword_mappings_for_field($ref,$fields[$n]["ref"]);
                    }
				
					# If this is a 'joined' field we need to add it to the resource column
					$joins=get_resource_table_joins();
					if (in_array($fields[$n]["ref"],$joins)){
						if(substr($val,0,1)==","){$val=substr($val,1);}
						sql_query("update resource set field".$fields[$n]["ref"]."='".escape_check($val)."' where ref='$ref'");
					}
                                        
                                # Add any onchange code
                                      if($fields[$n]["onchange_macro"]!="")
                                          {
                                          eval($fields[$n]["onchange_macro"]);    
                                          }
				
				}
			
			# Check required fields have been entered.
			$exemptfields = getvalescaped("exemptfields","");
			$exemptfields = explode(",",$exemptfields);
			if ($fields[$n]["required"]==1 && ($val=="" || $val==",") && !in_array($fields[$n]["ref"],$exemptfields))
				{
				global $lang;
				$errors[$fields[$n]["ref"]]=i18n_get_translated($fields[$n]["title"]).": ".$lang["requiredfield"];
				}
			}
		}	   
    
        if ($autosave_field=="")
            {
            # Additional tasks when editing all fields (i.e. not autosaving)
            
            # Always index the resource ID as a keyword
            remove_keyword_mappings($ref, $ref, -1);
            add_keyword_mappings($ref, $ref, -1);
            
            # Also index the resource type name, unless disabled
            global $index_resource_type;
            if ($index_resource_type)
                    {
                    $restypename=sql_value("select name value from resource_type where ref in (select resource_type from resource where ref='" . escape_check($ref) . "')","");
                    remove_all_keyword_mappings_for_field($ref,-2);
                    add_keyword_mappings($ref,$restypename,-2);
                    }
            
            # Also save related resources field
            sql_query("delete from resource_related where resource='$ref' or related='$ref'"); # remove existing related items
            $related=explode(",",getvalescaped("related",""));
            # Make sure all submitted values are numeric
            $ok=array();for ($n=0;$n<count($related);$n++) {if (is_numeric(trim($related[$n]))) {$ok[]=trim($related[$n]);}}
            if (count($ok)>0) {sql_query("insert into resource_related(resource,related) values ($ref," . join("),(" . $ref . ",",$ok) . ")");}
            }

	# Autocomplete any blank fields.
	autocomplete_blank_fields($ref);
	
	# Update resource_node table
	delete_resource_nodes($ref,$nodes_to_remove);
	if(count($nodes_to_add)>0)
		{
        add_resource_nodes($ref,$nodes_to_add);
		}
            
	# Expiry field(s) edited? Reset the notification flag so that warnings are sent again when the date is reached.
	$expirysql="";
	if ($expiry_field_edited) {$expirysql=",expiry_notification_sent=0";}

	if (!hook('forbidsavearchive', '', array($errors)))
		{
		# Also update archive status and access level
		$oldaccess=$resource_data['access'];
		$access=getvalescaped("access",$oldaccess,true);

		#$oldarchive=sql_value("select archive value from resource where ref='$ref'","");
		$oldarchive=$resource_data['archive'];
		$setarchivestate=getvalescaped("status",$oldarchive,true);
		
		if($setarchivestate!=$oldarchive && !checkperm("e" . $setarchivestate)) // don't allow change if user has no permission to change archive state
			{
			$setarchivestate=$oldarchive;
			}
			
		if ($access!=$oldaccess || $setarchivestate!=$oldarchive) // Only if changed
			{
			sql_query("update resource set archive='" . $setarchivestate . "',access='" . $access . "' $expirysql where ref='$ref'");  
			if ($setarchivestate!=$oldarchive && $ref>0)
				{
				resource_log($ref,"s",0,"",$oldarchive,$setarchivestate);
				}
			if ($access!=$oldaccess && $ref>0)
				{
				resource_log($ref,"a",0,"",$oldaccess,$access);
				}
            
            if ($oldaccess==3 && $access!=3)
                {
                # Moving out of the custom state. Delete any usergroup specific access.
                # This can delete any 'manual' usergroup grants also as the user will have seen this as part of the custom access.
                delete_resource_custom_access_usergroups($ref);
                }
			
			
			# Clear any outstanding notifications relating to submission of this resource
			message_remove_related(SUBMITTED_RESOURCE,$ref);
			
			// Notify the resources team ($email_notify) if moving from pending submission -> review.
			if ($oldarchive==-2 && $setarchivestate==-1 && $ref>0)
					{	
					notify_user_contributed_submitted(array($ref));
					}
			if ($oldarchive==-1 && $setarchivestate==-2 && $ref>0)
					{
					notify_user_contributed_unsubmitted(array($ref));
					}
			if($user_resources_approved_email)
				{	
				if (($oldarchive==-2 || $oldarchive==-1) && $ref>0 && $setarchivestate==0)
						{
						notify_user_resources_approved(array($ref));
						}	
				}
			}
		}
	# For access level 3 (custom) - also save custom permissions
	if (getvalescaped("access",0)==3) {save_resource_custom_access($ref);}

	# Update XML metadata dump file
	update_xml_metadump($ref);		
	
	hook("aftersaveresourcedata");

	if (count($errors)==0) {return true;} else {return $errors;}
	}
	


function set_resource_defaults($ref) 
	{	
	# Save all the resource defaults
	global $userresourcedefaults;
	if ($userresourcedefaults!="")
		{
		$s=explode(";",$userresourcedefaults);
		for ($n=0;$n<count($s);$n++)
			{
			$e=explode("=",$s[$n]);
			# Find field(s) - multiple fields can be returned to support several fields with the same name.
			$f=sql_array("select ref value from resource_type_field where name='" . escape_check($e[0]) . "'");
			if (count($f)==0) {exit ("Field(s) with short name '" . $e[0] . "' not found in resource defaults for this user group.");}
			for ($m=0;$m<count($f);$m++)
				{
				// Note: we are doing these checks to make sure users can override
                                // the resource defaults when they can edit the field.
                                // We always set defaults for an upload template as any defaults can be overridden by save_resource_data later on
                                if($ref<0 || (checkperm('F' . $f[$m]) || (checkperm('F*') && !checkperm('F-' . $f[$m]))))
                                    {
                                    update_field($ref, $f[$m], $e[1]);
                                    }
				}
			}
		}
	}

if (!function_exists("save_resource_data_multi")){
function save_resource_data_multi($collection)
	{
	# Save all submitted data for collection $collection, this is for the 'edit multiple resources' feature
	# Loop through the field data and save (if necessary)
	$list=get_collection_resources($collection);
        $errors=array();
	$tmp = hook("altercollist", "", array("save_resource_data_multi", $list)); if(is_array($tmp)) { if(count($tmp)>0) $list = $tmp; else return true; } // alter the collection list to spare some when saving multiple, if you need

	$ref=$list[0];
	$fields=get_resource_field_data($ref,true);
	global $auto_order_checkbox,$auto_order_checkbox_case_insensitive, $FIXED_LIST_FIELD_TYPES;
	$expiry_field_edited=false;
   
	for ($n=0;$n<count($fields);$n++)
		{
		if (getval("editthis_field_" . $fields[$n]["ref"],"")!="" || hook("save_resource_data_multi_field_decision","",array($fields[$n]["ref"])))
			{
             # Set up arrays of node ids selcted and we will then resolve these to add/remove. We can't remove all nodes as user may not have access
            $nodes_to_add=array();
            $nodes_to_remove=array();
            $selected_nodes=array();
            $unselected_nodes=array();
        
            node_field_options_override($fields[$n]);
			if ($fields[$n]["type"]==2)
				{
				# construct the value from the ticked boxes
				$val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                
                
                foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					$name=$fields[$n]["ref"] . "_" . md5($nodedata['name']);
					if (getval($name,"")=="yes")
						{
						if ($val!=",") {$val .= ",";}
						$val .= $nodedata['name'];
						$selected_nodes[] = $noderef;
						}
					else
						{
						$unselected_nodes[] = $noderef;
						}
					}
				}
			elseif ($fields[$n]["type"]==4 || $fields[$n]["type"]==6 || $fields[$n]["type"]==10)
				{
				# date/expiry date type, construct the value from the date dropdowns
				$val=sprintf("%04d", getvalescaped("field_" . $fields[$n]["ref"] . "-y",""));
				if ((int)$val<=0) 
					{
					$val="";
					}
				elseif (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-m",""))!="") 
					{
					$val.="-" . $field;
					if (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-d",""))!="") 
						{
						$val.="-" . $field;
						if (($field=getval("field_" . $fields[$n]["ref"] . "-h",""))!="")
							{
							$val.=" " . $field . ":";
							if (($field=getvalescaped("field_" . $fields[$n]["ref"] . "-i",""))!="") 
								{
									$val.=$field;
								} 
							else 
								{
									$val.="00";
								}
							}
						}
					}
				}
			elseif ($fields[$n]["type"] == 3)
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");				
				foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					if (strip_leading_comma($val) == $nodedata['name'])
						{
						$selected_nodes[] = $noderef;
						}
					else
						{
						$unselected_nodes[] = $noderef;
						}
					}							
				// if it doesn't already start with a comma, add one
				if (substr($val,0,1) != ',')
					{
					$val = ','.$val;
					}				
				}
            elseif ($fields[$n]["type"] == 12)
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");	
				foreach($fields[$n]["nodes"] as $noderef => $nodedata)
					{
					if (in_array(strip_leading_comma($val),i18n_get_translations($nodedata['name'])))
						{
						$selected_nodes[] = $noderef;
						// Correct the string to include all multingual strings as for dropdowns
						$val=$nodedata['name'];
						}
					else
						{
						$unselected_nodes[] = $noderef;
						}
					}							
				// if it doesn't already start with a comma, add one
				if (substr($val,0,1) != ',')
					{
					$val = ','.$val;
					}				
				}
            elseif ($fields[$n]["type"] == 7 || $fields[$n]["type"]==9) // Category tree or dynamic keywords     
				{
				$submittedval=getval("field_" . $fields[$n]["ref"],"");
				$submittedvals=explode("|",$submittedval);
                $newvals=array();
                foreach($fields[$n]["nodes"] as $noderef => $nodedata)
                    {
                    $addnode=false;
                    foreach($submittedvals as $checkval)
                        {                  
						if (trim($checkval) == trim($nodedata['name']))                            {
                            $addnode=true;                            
                            }                        
                        }
                    if($addnode)
                        {
                        $selected_nodes[] = $noderef;
                        // Correct the string to include all multingual strings as for dropdowns
                        $newvals[]=escape_check($nodedata['name']);    
                        }
                    else
                        {
                        $unselected_nodes[] = $noderef;    
                        }
                    }
				$val = ',' . implode(",",$newvals);
				}
			else
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");
				}
			$origval=$val;
			# Loop through all the resources and save.
			for ($m=0;$m<count($list);$m++)
				{
				$ref=$list[$m];
				$resource_sql="";

				# Work out existing field value.
				$existing=escape_check(sql_value("select value from resource_data where resource='$ref' and resource_type_field='" . $fields[$n]["ref"] . "'",""));
				
				if (getval("modeselect_" . $fields[$n]["ref"],"")=="FR")
					{
                    # Find and replace mode? Perform the find and replace.
					$val=str_replace
						(
						getvalescaped("find_" . $fields[$n]["ref"],""),
						getvalescaped("replace_" . $fields[$n]["ref"],""),
						$existing
						);                    
					}
				
				# Append text/option(s) mode?
				if (getval("modeselect_" . $fields[$n]["ref"],"")=="AP")
					{
					$val=append_field_value($fields[$n],$origval,$existing);
                    $nodes_to_add=$selected_nodes;
					}
					
				# Prepend text/option(s) mode?
				elseif (getval("modeselect_" . $fields[$n]["ref"],"")=="PP")
                    {
					global $filename_field;
					if ($fields[$n]["ref"]==$filename_field)
                        {
						$val=rtrim($origval,"_")."_".trim($existing); // use an underscore if editing filename.
                        }
					else {
						# Automatically append a space when appending text types.
						$val=$origval . " " . $existing;
                        }
                    }
				elseif (getval("modeselect_" . $fields[$n]["ref"],"")=="RM")
					{
                    # Remove text/option(s) mode
                    $val=str_replace($origval,"",$existing);
                    if($fields[$n]["required"] && strip_leading_comma($val)=="")
                        {
                        // Required field and  no value now set, revert to existing and add to array of failed edits
                        global $lang;
                        $val=$existing;
                        if(!isset($errors[$fields[$n]["ref"]]))
                            {$errors[$fields[$n]["ref"]]=$lang["requiredfield"] . ". " . $lang["error_batch_edit_resources"] . ": " ;}
                        $errors[$fields[$n]["ref"]] .=  $ref;
                        if($m<count($list)-1){$errors[$fields[$n]["ref"]] .= ",";}
                        
                        }
                    else
                        {
                        $nodes_to_remove=$selected_nodes;
                        }
					}
				else
					{
                    # Replace text/option(s) mode
					$nodes_to_add=$selected_nodes;
                    $nodes_to_remove=$unselected_nodes;
					}
                
                # Possibility to hook in and alter the value - additional mode support
                $hookval=hook("save_resource_data_multi_extra_modes","",array($ref,$fields[$n]));
                if ($hookval!==false) {$val=$hookval;}

				//$val=strip_leading_comma($val);
				#echo "<li>existing=$existing, new=$val";
				if ($existing!==str_replace("\\","",$val))
					{
					# This value is different from the value we have on record.
					
					# Write this edit to the log.
					resource_log($ref,'m',$fields[$n]["ref"],"",$existing,$val);
		
					# Expiry field? Set that expiry date(s) have changed so the expiry notification flag will be reset later in this function.
					if ($fields[$n]["type"]==6) {$expiry_field_edited=true;}
				
					# If this is a 'joined' field we need to add it to the resource column
					$joins=get_resource_table_joins();
					if (in_array($fields[$n]["ref"],$joins)){
						sql_query("update resource set field".$fields[$n]["ref"]."='".escape_check($val)."' where ref='$ref'");
					}		
						
					# Purge existing data and keyword mappings, decrease keyword hitcounts.
					sql_query("delete from resource_data where resource='$ref' and resource_type_field='" . $fields[$n]["ref"] . "'");
					
					# Insert new data and keyword mappings, increase keyword hitcounts.
					sql_query("insert into resource_data(resource,resource_type_field,value) values('$ref','" . $fields[$n]["ref"] . "','" . escape_check($val) . "')");
		
					$oldval=$existing;
					$newval=$val;
					
					if (in_array($fields[$n]["type"],$FIXED_LIST_FIELD_TYPES))
						{
						# Prepend a comma when indexing dropdowns and checkboxes
						$newval=  strlen($val)>0 && $val[0]==',' ? $val : ',' . $val;
                        $oldval=  strlen($oldval)>0 && $oldval[0]==',' ? $oldval : ',' . $oldval;
						}
					
					if ($fields[$n]["keywords_index"]==1)
						{
						# Date field? These need indexing differently.
						$is_date=($fields[$n]["type"]==4 || $fields[$n]["type"]==6); 

						$is_html=($fields[$n]["type"]==8);

						remove_keyword_mappings($ref,i18n_get_indexable($oldval),$fields[$n]["ref"],$fields[$n]["partial_index"],$is_date,'','',$is_html);
						add_keyword_mappings($ref,i18n_get_indexable($newval),$fields[$n]["ref"],$fields[$n]["partial_index"],$is_date,'','',$is_html);
						}
                        
                    # Update resource_node table
                    delete_resource_nodes($ref,$nodes_to_remove);
                    if(count($nodes_to_add)>0)
                        {
                        add_resource_nodes($ref,$nodes_to_add);
                        }
            
                            # Add any onchange code
                            if($fields[$n]["onchange_macro"]!="")
                                {
                                eval($fields[$n]["onchange_macro"]);    
                                }
					}
				}
			}
		}
		
	# Also save related resources field
	if (getval("editthis_related","")!="")
		{
		$related=explode(",",getvalescaped("related",""));
		# Make sure all submitted values are numeric
		$ok=array();for ($n=0;$n<count($related);$n++) {if (is_numeric(trim($related[$n]))) {$ok[]=trim($related[$n]);}}

		for ($m=0;$m<count($list);$m++)
			{
			$ref=$list[$m];
			sql_query("delete from resource_related where resource='$ref' or related='$ref'"); # remove existing related items
			if (count($ok)>0) {sql_query("insert into resource_related(resource,related) values ($ref," . join("),(" . $ref . ",",$ok) . ")");}
			}
		}
	
	# Also update archive status
	global $user_resources_approved_email,$email_notify;	
	if (getval("editthis_status","")!="")
		{
		$notifyrefs=array();
		$usernotifyrefs=array();
		for ($m=0;$m<count($list);$m++)
			{
			$ref=$list[$m];                        
                        
                        if (!hook('forbidsavearchive', '', array($errors)))
                            {
                            # Also update archive status                            
                            
                            $oldarchive=sql_value("select archive value from resource where ref='$ref'","");
                            $setarchivestate=getvalescaped("status",$oldarchive,true); // We used to get the 'archive' value but this conflicts with the archiveused for searching                                
                            if($setarchivestate!=$oldarchive && !checkperm("e" . $setarchivestate)) // don't allow change if user has no permission to change archive state
                                {
                                $setarchivestate=$oldarchive;
                                }
                                
                            if ($setarchivestate!=$oldarchive) // Only if changed
                                {
                                sql_query("update resource set archive='" . $setarchivestate . "' where ref='$ref'");  
                                if ($setarchivestate!=$oldarchive && $ref>0)
                                    {
                                    resource_log($ref,"s",0,"",$oldarchive,$setarchivestate);
                                    }
                                                                
                                # Check states to see if notifications are necessary
                                if (
									($oldarchive==-2 && $setarchivestate==-1) ||
									($oldarchive==-1 && $setarchivestate==-2) || 
									($user_resources_approved_email && ($oldarchive==-2 || $oldarchive==-1) && $setarchivestate==0)
									)
										{	
										$notifyrefs[]=$ref;
										} 
                                }
                            }                                                			
			}
        
		if (($oldarchive==-2 || $oldarchive==-1) && $setarchivestate==0) # Clear any outstanding notifications relating to submission of this collection/resource
			{
			message_remove_related(SUBMITTED_COLLECTION,$collection);
			message_remove_related(SUBMITTED_RESOURCE,$notifyrefs);
			}		
		if (count($notifyrefs)>0)
			{
			if ($user_resources_approved_email && ($oldarchive==-2 || $oldarchive==-1) && $setarchivestate==0) # Notify the  users that their resources have been approved	
				{
				debug("Emailing approval notification for submitted resources to users");
				notify_user_resources_approved($notifyrefs);			
				}
			
			if ($oldarchive==-2 && $setarchivestate==-1) # Notify the resources team ($email_notify) if moving from pending submission->pending review
				{
				debug("Emailing notification of submitted resources to " . $email_notify);
				notify_user_contributed_submitted($notifyrefs, $collection);
				}
			
			if ($oldarchive==-1 && $setarchivestate==-2) # Notify the admin users of any submitted resources.
				{
				debug("Emailing notification of unsubmitted resources to " . $email_notify);
				notify_user_contributed_unsubmitted($notifyrefs, $collection);
				}	
			}	
		}
	
	# Expiry field(s) edited? Reset the notification flag so that warnings are sent again when the date is reached.
	if ($expiry_field_edited)
		{
		if (count($list)>0)
			{
			sql_query("update resource set expiry_notification_sent=0 where ref in (" . join(",",$list) . ")");
			}
		}
	
	# Also update access level
	if (getval("editthis_access","")!="")
		{
		for ($m=0;$m<count($list);$m++)
			{
			$ref=$list[$m];
			$access=getvalescaped("access",0);
			$oldaccess=sql_value("select access value from resource where ref='$ref'","");
			if ($access!=$oldaccess)
				{
				sql_query("update resource set access='$access' where ref='$ref'");
				
                                if ($oldaccess==3)
                                        {
                                        # Moving out of custom access - delete custom usergroup access.
                                        delete_resource_custom_access_usergroups($ref);
                                        }
				resource_log($ref,"a",0,"",$oldaccess,$access);
				}
			
			# For access level 3 (custom) - also save custom permissions
			if ($access==3) {save_resource_custom_access($ref);}
			}
		}
	
	# Update resource type?
	if (getval("editresourcetype","")!="")
		{
		for ($m=0;$m<count($list);$m++)
			{
			$ref=$list[$m];
			update_resource_type($ref,getvalescaped("resource_type",""));
			}
		}
		
	# Update location?
	if (getval("editlocation","")!="")
		{
		$location=explode(",",getvalescaped("location",""));
		if (count($list)>0) 
			{
			if (count($location)==2)
				{
				$geo_lat=(float)$location[0];
				$geo_long=(float)$location[1];
				sql_query("update resource set geo_lat=$geo_lat,geo_long=$geo_long where ref in (" . join(",",$list) . ")");
				}
			elseif (getvalescaped("location","")=="")
				{
				sql_query("update resource set geo_lat=null,geo_long=null where ref in (" . join(",",$list) . ")");
				}
			}
		}

	# Update mapzoom?
	if (getval("editmapzoom","")!="")
		{
		$mapzoom=getvalescaped("mapzoom","");
		if (count($list)>0)
			{
			if ($mapzoom!="")
				{
				sql_query("update resource set mapzoom=$mapzoom where ref in (" . join(",",$list) . ")");
				}
			else
				{
				sql_query("update resource set mapzoom=null where ref in (" . join(",",$list) . ")");
				}
			}
		}

	hook("saveextraresourcedata","",array($list));
		
	# Update XML metadata dump file for all edited resources.
	for ($m=0;$m<count($list);$m++)
		{
		update_xml_metadump($list[$m]);
		}
	
	hook("aftersaveresourcedata");
    if (count($errors)==0) {return true;} else {return $errors;}
    
	}
}

function append_field_value($field_data,$new_value,$existing_value)
	{
	if ($field_data["type"]!=2 && $field_data["type"]!=3 && $field_data["type"]!=9 && $field_data["type"]!=12 && substr($new_value,0,1)!=",")
		{
		# Automatically append a space when appending text types.
		$val=$existing_value . " " . $new_value;
		}
	else
		{
		# Verify a comma exists at the beginning of the value
		if(substr($new_value,0,1)!=",")
			{
			$new_value=",".$new_value;
            }
		
		$val=(trim($existing_value)!=","?$existing_value:"") . $new_value;
		
		}
	return $val;
	}

if (!function_exists("remove_keyword_mappings")){
function remove_keyword_mappings($ref,$string,$resource_type_field,$partial_index=false,$is_date=false,$optional_column='',$optional_value='',$is_html=false)
	{
	# Removes one instance of each keyword->resource mapping for each occurrence of that
	# keyword in $string.
	# This is used to remove keyword mappings when a field has changed.
	# We also decrease the hit count for each keyword.
	if (trim($string)=="") {return false;}
	$keywords=split_keywords($string,true,$partial_index,$is_date,$is_html);

	add_verbatim_keywords($keywords, $string, $resource_type_field);		// add in any verbatim keywords (found using regex).

	for ($n=0;$n<count($keywords);$n++)
		{
        unset ($kwpos);
		if (is_array($keywords[$n])){
			$kwpos=$keywords[$n]['position'];
			$keywords[$n]=$keywords[$n]['keyword'];
		}        
		$kw=$keywords[$n]; 
        if (!isset($kwpos)){$kwpos=$n;}
		remove_keyword_from_resource($ref,$keywords[$n],$resource_type_field,$optional_column='',$optional_value='',false, $kwpos);
		}	
	}
}

function remove_keyword_from_resource($ref,$keyword,$resource_type_field,$optional_column='',$optional_value='',$normalized=false, $position='')
    {
    if(!$normalized)
        {
		global $unnormalized_index;
        $kworig=$keyword;
        $keyword=normalize_keyword($keyword);
        if($keyword!=$kworig && $unnormalized_index)
			{
			// $keyword has been changed by normalizing, also remove the original value
			remove_keyword_from_resource($ref,$keyword,$resource_type_field,$optional_column='',$optional_value='',true);
			}
        }		
	
        $keyref=resolve_keyword($keyword,true);
	if ($optional_column<>'' && $optional_value<>'')	# Check if any optional column value passed and include this condition
		{
		sql_query("delete from resource_keyword where resource='$ref' and keyword='$keyref' and resource_type_field='$resource_type_field'" . (($position!="")?" and position='" . $position ."'":"") . " and $optional_column= $optional_value");
		}
	else{
		sql_query("delete from resource_keyword where resource='$ref' and keyword='$keyref' and resource_type_field='$resource_type_field'" . (($position!="")?" and position='" . $position ."'":""));
		}
	sql_query("update keyword set hit_count=hit_count-1 where ref='$keyref' limit 1");
			
    }



if(!function_exists('add_keyword_mappings')) {
function add_keyword_mappings($ref,$string,$resource_type_field,$partial_index=false,$is_date=false,$optional_column='',$optional_value='',$is_html=false)
    {
    /* For each instance of a keyword in $string, add a keyword->resource mapping.
    * Create keywords that do not yet exist.
    * Increase the hit count of each keyword that matches.
    * Store the position and field the string was entered against for advanced searching.
    */
    if(trim($string) == '')
        {
        return false;
        }

    $keywords = split_keywords($string, true, $partial_index, $is_date, $is_html);

    add_verbatim_keywords($keywords, $string, $resource_type_field); // add in any verbatim keywords (found using regex).

    db_begin_transaction();
    for($n = 0; $n < count($keywords); $n++)
        {
        unset($kwpos);
        if(is_array($keywords[$n]))
            {
            $kwpos        = $keywords[$n]['position'];
            $keywords[$n] = $keywords[$n]['keyword'];
            }

        $kw = $keywords[$n];
        if(!isset($kwpos))
            {
            $kwpos = $n;
            }

        add_keyword_to_resource($ref, $kw, $resource_type_field, $kwpos, $optional_column, $optional_value, false);
        }
    db_end_transaction();

    }
}

function add_keyword_to_resource($ref,$keyword,$resource_type_field,$position,$optional_column='',$optional_value='',$normalized=false)
    {
    if(!$normalized)
        {
		global $unnormalized_index;
        $kworig=$keyword;
        $keyword=normalize_keyword($keyword);
        if($keyword!=$kworig && $unnormalized_index)
                    {
                    // $keyword has been changed by normalizing, also index the original value
                    add_keyword_to_resource($ref,$kworig,$resource_type_field,$position,$optional_column,$optional_value,true);
                    }
        }
    global $noadd;
    if (!(in_array($keyword,$noadd)))
            {           
            debug("adding " . $keyword);
            $keyref=resolve_keyword($keyword,true);			
            
            # create mapping, increase hit count.
            if ($optional_column<>'' && $optional_value<>'')	# Check if any optional column value passed and add this
                    {
					sql_query("insert into resource_keyword(resource,keyword,position,resource_type_field,$optional_column) values ('$ref','$keyref','$position','$resource_type_field','$optional_value')");
					}
            else  
                    {
					sql_query("insert into resource_keyword(resource,keyword,position,resource_type_field) values ('$ref','$keyref','$position','$resource_type_field')");
					}

            sql_query("update keyword set hit_count=hit_count+1 where ref='$keyref'");
            
            # Log this
            daily_stat("Keyword added to resource",$keyref);
            }  	
    }
    
function remove_all_keyword_mappings_for_field($resource,$resource_type_field)
    {
    sql_query("delete from resource_keyword where resource='" . escape_check($resource) . "' and resource_type_field='" . escape_check($resource_type_field) . "'");
    }

                    
function update_field($resource,$field,$value)
	{

    global $FIXED_LIST_FIELD_TYPES;

	# Updates a field. Works out the previous value, so this is not efficient if we already know what this previous value is (hence it is not used for edit where multiple fields are saved)

	# accept shortnames in addition to field refs
	if (!is_numeric($field)){$field=sql_value("select ref value from resource_type_field where name='".escape_check($field)."'","");}

	# Fetch some information about the field
	$fieldinfo=sql_query("select ref,keywords_index,resource_column,partial_index,type, onchange_macro from resource_type_field where ref='$field'");

	if (count($fieldinfo)==0) {return false;} else {$fieldinfo=$fieldinfo[0];}
	    
    $fieldoptions = get_nodes($field);
    $newvalues    = trim_array(explode(',', $value));
    
    # Set up arrays of node ids to add/remove. 
	if (in_array($fieldinfo['type'], $FIXED_LIST_FIELD_TYPES))
        {
        $nodes_to_add=array();
        $nodes_to_remove=array();
        }
    
    # If this is a dynamic keyword we need to add it to the field options
    if($fieldinfo['type']==9 && !checkperm('bdk' . $field))
        {
        $currentoptions=array();
        foreach($fieldoptions as $fieldoption)
            {
            $fieldoptiontranslations=explode("~",$fieldoption['name']);
            if (count($fieldoptiontranslations)<2)
                {
                $currentoptions[]=trim($fieldoption['name']); # Not a translatable field
                debug("update_field: current field option: '" . trim($fieldoption['name']) . "'<br>");
                }
            else
                {
                $default="";
                for ($n=1;$n<count($fieldoptiontranslations);$n++)
                    {
                    # Not a translated string, return as-is
                    if (substr($fieldoptiontranslations[$n],2,1)!=":" && substr($fieldoptiontranslations[$n],5,1)!=":" && substr($fieldoptiontranslations[$n],0,1)!=":")
                        {
                        $currentoptions[]=trim($fieldoption);
                        debug("update_field: current field option: '" . $fieldoption . "'<br>");
                        }
                    else
                        {
                        # Support both 2 character and 5 character language codes (for example en, en-US).
                        $p=strpos($fieldoptiontranslations[$n],':');                         
                        $currentoptions[]=trim(substr($fieldoptiontranslations[$n],$p+1));
                        debug("update_field: current field option: '" . trim(substr($fieldoptiontranslations[$n],$p+1)) . "'<br>");
                        } 
                    }
                }
            }
        foreach($newvalues as $newvalue)
            {
            # Check if each new value exists in current options list
            if('' != $newvalue && !in_array($newvalue,$currentoptions))
                {
                # Append the option and update the field
                //sql_query("update resource_type_field set options=concat(ifnull(options,''), ', " . escape_check(trim($newvalue)) . "') where ref='$field'");
                $newnode = set_node(null,$field,escape_check(trim($newvalue)),null,null);
                $nodes_to_add[] = $newnode;

                $currentoptions[]=trim($newvalue);
                debug("update_field: field option added: '" . trim($newvalue) . "'<br>");
                }
            }
        }
    
    # Fetch previous value
    $existing=sql_value("select value from resource_data where resource='$resource' and resource_type_field='$field'","");
     
    if (in_array($fieldinfo['type'], $FIXED_LIST_FIELD_TYPES))
        {
        foreach($fieldoptions as $nodedata)
            {
            if (in_array($nodedata["name"],$newvalues))
                {
                $nodes_to_add[] = $nodedata["ref"];
                }
            else
                {
                $nodes_to_remove[] = $nodedata["ref"];
                }
            }
        # Update resource_node table
        delete_resource_nodes($resource,$nodes_to_remove);
        if(count($nodes_to_add)>0)
            {
            add_resource_nodes($resource,$nodes_to_add);
            }
        }
	if ($fieldinfo["keywords_index"])
		{
		$is_html=($fieldinfo["type"]==8);	
		
		# If there's a previous value, remove the index for those keywords
		$existing=sql_value("select value from resource_data where resource='$resource' and resource_type_field='$field'","");
		if (strlen($existing)>0)
			{
			remove_keyword_mappings($resource,i18n_get_indexable($existing),$field,$fieldinfo["partial_index"],false,'','',$is_html);
			}
		
		if (in_array($fieldinfo['type'], $FIXED_LIST_FIELD_TYPES) && substr($value,0,1) <> ','){
			$value = ','.$value;
		}
		
		//$value=strip_leading_comma($value);
		
		# Index the new value
		add_keyword_mappings($resource,i18n_get_indexable($value),$field,$fieldinfo["partial_index"],false,'','',$is_html);
		}
		
	# Delete the old value (if any) and add a new value.
	sql_query("delete from resource_data where resource='$resource' and resource_type_field='$field'");
	$value=escape_check($value);
	sql_query("insert into resource_data(resource,resource_type_field,value) values ('$resource','$field','$value')");
	
	if ($value=="") {$value="null";} else {$value="'" . $value . "'";}

	# If this is a 'joined' field we need to add it to the resource column
	$joins=get_resource_table_joins();
	if(in_array($fieldinfo['ref'],$joins))
		{
		if ($value!="null")
			{
			global $resource_field_column_limit;
			$truncated_value = substr($value, 0, $resource_field_column_limit);

            // Remove backslashes from the end of the truncated value
            if(substr($truncated_value, -1) === '\\')
                {
                $truncated_value = substr($truncated_value, 0, strlen($truncated_value) - 1);
                }

			if(substr($truncated_value, -1) !== '\'')
				{
				$truncated_value .= '\'';
				}
			}
		else
			{
			$truncated_value="null";
			}
		sql_query("update resource set field".$field."=" . $truncated_value . " where ref='$resource'");
		}			
	
        # Add any onchange code
        if($fieldinfo["onchange_macro"]!="")
            {
            eval($fieldinfo["onchange_macro"]);    
            }
        
        # Allow plugins to perform additional actions.
        hook("update_field","",array($resource,$field,$value,$existing));
	}

if (!function_exists("email_resource")){	
function email_resource($resource,$resourcename,$fromusername,$userlist,$message,$access=-1,$expires="",$useremail="",$from_name="",$cc="",$list_recipients=false, $open_internal_access=false, $useraccess=2,$group="")
	{
	# Attempt to resolve all users in the string $userlist to user references.

	global $baseurl,$email_from,$applicationname,$lang,$userref,$usergroup,$attach_user_smart_groups;
	
	if ($useremail==""){$useremail=$email_from;}
	if ($group=="") {$group=$usergroup;}
        
	# remove any line breaks that may have been entered
	$userlist=str_replace("\\r\\n",",",$userlist);

	if (trim($userlist)=="") {return ($lang["mustspecifyoneusername"]);}
	$userlist=resolve_userlist_groups($userlist);
	if($attach_user_smart_groups && strpos($userlist,$lang["groupsmart"] . ": ")!==false)
		{
		$userlist_with_groups=$userlist;
		$groups_users=resolve_userlist_groups_smart($userlist,true);
		if($groups_users!='')
			{
			if($userlist!="")
				{
				$userlist=remove_groups_smart_from_userlist($userlist);
				if($userlist!="")
					{
					$userlist.=",";
					}
				}
			$userlist.=$groups_users;
			}
		}
	
	$ulist=trim_array(explode(",",$userlist));
	$ulist=array_filter($ulist);
	$ulist=array_values($ulist);

	$emails=array();
	$key_required=array();
	
	$emails_keys=resolve_user_emails($ulist);
	$unames=$emails_keys['unames'];
	$emails=$emails_keys['emails'];
	$key_required=$emails_keys['key_required'];

	# Send an e-mail to each resolved user / e-mail address
	$subject="$applicationname: $resourcename";
	if ($fromusername==""){$fromusername=$applicationname;} // fromusername is used for describing the sender's name inside the email
	if ($from_name==""){$from_name=$applicationname;} // from_name is for the email headers, and needs to match the email address (app name or user name)
	
	$message=str_replace(array("\\n","\\r","\\"),array("\n","\r",""),$message);

#	Commented 'no message' line out as formatted oddly, and unnecessary.
#	if ($message==""){$message=$lang['nomessage'];}
	$resolve_open_access=false;
	
	for ($n=0;$n<count($emails);$n++)
		{
		$key="";
		# Do we need to add an external access key for this user (e-mail specified rather than username)?
		if ($key_required[$n])
			{
			$k=generate_resource_access_key($resource,$userref,$access,$expires,$emails[$n],$group);
			$key="&k=". $k;
			}
                elseif ($useraccess==0 && $open_internal_access && !$resolve_open_access)
                    {debug("smart_groups: going to resolve open access");
					# get this all done at once
					resolve_open_access((isset($userlist_with_groups)?$userlist_with_groups:$userlist),$resource,$expires);
					$resolve_open_access=true;
                    }
		
		# make vars available to template
		global $watermark;       
		$templatevars['thumbnail']=get_resource_path($resource,true,"thm",false,"jpg",$scramble=-1,$page=1,($watermark)?(($access==1)?true:false):false);
		if (!file_exists($templatevars['thumbnail'])){
			$resourcedata=get_resource_data($resource);
			$templatevars['thumbnail']="../gfx/".get_nopreview_icon($resourcedata["resource_type"],$resourcedata["file_extension"],false);
		}
		$templatevars['url']=$baseurl . "/?r=" . $resource . $key;
		$templatevars['fromusername']=$fromusername;
		$templatevars['message']=$message;
		$templatevars['resourcename']=$resourcename;
		$templatevars['from_name']=$from_name;
		if(isset($k)){
			if($expires==""){
				$templatevars['expires_date']=$lang["email_link_expires_never"];
				$templatevars['expires_days']=$lang["email_link_expires_never"];
			}
			else{
				$day_count=round((strtotime($expires)-strtotime('now'))/(60*60*24));
				$templatevars['expires_date']=$lang['email_link_expires_date'].nicedate($expires);
				$templatevars['expires_days']=$lang['email_link_expires_days'].$day_count;
				if($day_count>1){
					$templatevars['expires_days'].=" ".$lang['expire_days'].".";
				}
				else{
					$templatevars['expires_days'].=" ".$lang['expire_day'].".";
				}
			}
		}
		else{
			# Set empty expiration tempaltevars
			$templatevars['expires_date']='';
			$templatevars['expires_days']='';
		}
		
		# Build message and send.
		if (count($emails > 1) && $list_recipients===true) {
			$body = $lang["list-recipients"] ."\n". implode("\n",$emails) ."\n\n";
			$templatevars['list-recipients']=$lang["list-recipients"] ."\n". implode("\n",$emails) ."\n\n";
		}
		else {
			$body = "";
		}
		$body.=$templatevars['fromusername']." ". $lang["hasemailedyouaresource"]."\n\n" . $templatevars['message']."\n\n" . $lang["clicktoviewresource"] . "\n\n" . $templatevars['url'];
		send_mail($emails[$n],$subject,$body,$fromusername,$useremail,"emailresource",$templatevars,$from_name,$cc);
		
		# log this
		resource_log($resource,"E","",$notes=$unames[$n]);
		
		}
	hook("additional_email_resource","",array($resource,$resourcename,$fromusername,$userlist,$message,$access,$expires,$useremail,$from_name,$cc,$templatevars));
	# Return an empty string (all OK).
	return "";
	}
}

function delete_resource($ref)
	{
	# Delete the resource, all related entries in tables and all files on disk
	
	if ($ref<0) {return false;} # Can't delete the template

	$resource=get_resource_data($ref);
	if (!$resource) {return false;} # Resource not found in database
	
	$current_state=$resource['archive'];
	
	global $resource_deletion_state, $staticsync_allow_syncdir_deletion, $storagedir;
	if (isset($resource_deletion_state) && $current_state!=$resource_deletion_state) # Really delete if already in the 'deleted' state.
		{
		# $resource_deletion_state is set. Do not delete this resource, instead move it to the specified state.
		sql_query("update resource set archive='" . $resource_deletion_state . "' where ref='" . $ref . "'");

        # log this so that administrator can tell who requested deletion
        resource_log($ref,'x','');
		
		# Remove the resource from any collections
		sql_query("delete from collection_resource where resource='$ref'");
			
		return true;
		}
	
	# Get info
	
	# Is transcoding
	if ($resource['is_transcoding']==1) {return false;} # Can't delete when transcoding

	# Delete files first
	$extensions = array();
	$extensions[]=$resource['file_extension']?$resource['file_extension']:"jpg";
	$extensions[]=$resource['preview_extension']?$resource['preview_extension']:"jpg";
	$extensions[]=$GLOBALS['ffmpeg_preview_extension'];
	$extensions[]='icc'; // also remove any extracted icc profiles
	$extensions=array_unique($extensions);
	
	foreach ($extensions as $extension)
		{
		$sizes=get_image_sizes($ref,true,$extension);
		foreach ($sizes as $size)
			{
			if (file_exists($size['path']) && ($staticsync_allow_syncdir_deletion || false !== strpos ($size['path'],$storagedir))) // Only delete if file is in filestore
				 {unlink($size['path']);}
			}
		}
	
	# Delete any alternative files
	$alternatives=get_alternative_files($ref);
	for ($n=0;$n<count($alternatives);$n++)
		{
		delete_alternative_file($ref,$alternatives[$n]['ref']);
		}

	
	// remove metadump file, and attempt to remove directory
	$dirpath = dirname(get_resource_path($ref, true, "", true));
	if (file_exists("$dirpath/metadump.xml")){
		unlink("$dirpath/metadump.xml");
	}
	@rmdir($dirpath); // try to delete directory, but if it has stuff in it fail silently for now
			  // fixme - should we try to handle if there are other random files still there?
	
	# Log the deletion of this resource for any collection it was in. 
	$in_collections=sql_query("select * from collection_resource where resource = '$ref'");
	if (count($in_collections)>0){
		if (!function_exists("collection_log")){include ("collections_functions.php");}
		for($n=0;$n<count($in_collections);$n++)
			{
			collection_log($in_collections[$n]['collection'],'d',$in_collections[$n]['resource']);
			}
		}

	hook("beforedeleteresourcefromdb","",array($ref));

	# Delete all database entries
	sql_query("delete from resource where ref='$ref'");
	sql_query("delete from resource_data where resource='$ref'");
	sql_query("delete from resource_dimensions where resource='$ref'");
	sql_query("delete from resource_keyword where resource='$ref'");
	sql_query("delete from resource_related where resource='$ref' or related='$ref'");
	sql_query("delete from collection_resource where resource='$ref'");
	sql_query("delete from resource_custom_access where resource='$ref'");
	sql_query("delete from external_access_keys where resource='$ref'");
	sql_query("delete from resource_alt_files where resource='$ref'");
    delete_all_resource_nodes($ref);
		
	hook("afterdeleteresource");
	
	return true;
	}

function get_max_resource_ref()
	{
	# Returns the highest resource reference in use.
	return sql_value("select max(ref) value from resource",0);
	}

function get_resource_ref_range($lower,$higher)
	{
	# Returns an array of resource references in the range $lower to $upper.
	return sql_array("select ref value from resource where ref>='$lower' and ref<='$higher' and archive=0 order by ref",0);
	}
	
function copy_resource($from,$resource_type=-1)
	{
	# Create a new resource, copying all data from the resource with reference $from.
	# Note this copies only the data and not any attached file. It's very unlikely the
	# same file would be in the system twice, however users may want to clone an existing resource
	# to avoid reentering data if the resource is very similar.
	# If $resource_type if specified then the resource type for the new resource will be set to $resource_type
	# rather than simply copied from the $from resource.
	
	# Check that the resource exists
	if (sql_value("select count(*) value from resource where ref='$from'",0)==0) {return false;}
	
	# copy joined fields to the resource column
	$joins=get_resource_table_joins();

	// Filter the joined columns so we only have the ones relevant to this resource type
	$query = sprintf('
			    SELECT rtf.ref AS value
			      FROM resource_type_field AS rtf
			INNER JOIN resource AS r ON (rtf.resource_type != r.resource_type AND rtf.resource_type != 0)
			     WHERE r.ref = "%s";
		',
		$from
	);
	$irrelevant_rtype_fields = sql_array($query);
	$irrelevant_rtype_fields = array_values(array_intersect($joins, $irrelevant_rtype_fields));
	$filtered_joins = array_values(array_diff($joins, $irrelevant_rtype_fields));

	$joins_sql="";
	foreach ($filtered_joins as $join){
		$joins_sql.=",field$join ";
	}
	
	$add="";

	# Determine if the user has access to the template archive status
	$archive=sql_value("select archive value from resource where ref='$from'",0);
	if (!checkperm("e" . $archive))
		{
		# Find the right permission mode to use
		for ($n=-2;$n<3;$n++)
			{
			if (checkperm("e" . $n)) {$archive=$n;break;}
			}
		}

	# First copy the resources row
	sql_query("insert into resource($add resource_type,creation_date,rating,archive,access,created_by $joins_sql) select $add" . (($resource_type==-1)?"resource_type":("'" . $resource_type . "'")) . ",now(),rating,'" . $archive . "',access,created_by $joins_sql from resource where ref='$from';");
	$to=sql_insert_id();
	
	# Set that this resource was created by this user. 
	# This needs to be done if either:
	# 1) The user does not have direct 'resource create' permissions and is therefore contributing using My Contributions directly into the active state
	# 2) The user is contributiting via My Contributions to the standard User Contributed pre-active states.
	global $userref;
	global $always_record_resource_creator;
	if ((!checkperm("c")) || $archive<0 || (isset($always_record_resource_creator) && $always_record_resource_creator))
		{
		# Update the user record
		sql_query("update resource set created_by='$userref' where ref='$to'");

		# Also add the user's username and full name to the keywords index so the resource is searchable using this name.
		global $username,$userfullname;
		add_keyword_mappings($to,$username . " " . $userfullname,-1);
		}

	# Now copy all data
	sql_query("insert into resource_data(resource,resource_type_field,value) select '$to',rd.resource_type_field,rd.value from resource_data rd join resource r on rd.resource=r.ref join resource_type_field rtf on rd.resource_type_field=rtf.ref and (rtf.resource_type=r.resource_type or rtf.resource_type=999 or rtf.resource_type=0) where rd.resource='$from'");
    
    # Copy nodes
    copy_resource_nodes($from,$to);
	
	# Copy relationships
	sql_query("insert into resource_related(resource,related) select '$to',related from resource_related where resource='$from'");

	# Copy access
	sql_query("insert into resource_custom_access(resource,usergroup,access) select '$to',usergroup,access from resource_custom_access where resource='$from'");

	# Set any resource defaults
	set_resource_defaults($to);
	
	# Autocomplete any blank fields.
	autocomplete_blank_fields($to);

	# Reindex the resource so the resource_keyword entries are created
	reindex_resource($to);
	
	# Copying a resource of the 'pending review' state? Notify, if configured.
	global $send_collection_to_admin;
	if ($archive==-1 && !$send_collection_to_admin)
		{
		notify_user_contributed_submitted(array($to));
		}
	
	# Log this			
	daily_stat("Create resource",$to);
	resource_log($to,'c',0);

	hook("afternewresource", "", array($to));
	
	return $to;
	}
	
function resource_log($resource, $type, $field, $notes="", $fromvalue="", $tovalue="", $usage=-1, $purchase_size="", $purchase_price=0)
	{
	global $userref,$k,$lang,$resource_log_previous_ref;

    if(($resource===RESOURCE_LOG_APPEND_PREVIOUS && !isset($resource_log_previous_ref)) || ($resource!==RESOURCE_LOG_APPEND_PREVIOUS && $resource<0))
        {
        return false;
        }

	if ($fromvalue===$tovalue)
		{
        $diff="";
		}
    else
        {
        switch ($type)
            {
            case LOG_CODE_STATUS_CHANGED:
                $diff = $lang["status" . $fromvalue] . " -> " . $lang["status" . $tovalue];
                break;

            case LOG_CODE_ACCESS_CHANGED:
                $diff = $lang["access" . $fromvalue] . " -> " . $lang["access" . $tovalue];
                break;

            // do not do a diff, just dump out whole new value (this is so we can cleanly append transform output)
            case LOG_CODE_TRANSFORMED:
                $diff = $tovalue;
                break;

            default:
                $diff = log_diff($fromvalue, $tovalue);
            }
        }

	$modifiedlogtype=hook("modifylogtype","",array($type));
	if ($modifiedlogtype)
        {
        $type=$modifiedlogtype;
        }
	
	$modifiedlognotes=hook("modifylognotes","",array($notes,$type,$resource));
	if($modifiedlognotes)
        {
        $notes=$modifiedlognotes;
        }

    if ($resource===RESOURCE_LOG_APPEND_PREVIOUS)
        {
        sql_query("UPDATE `resource_log` SET `diff`=concat(`diff`,'\n','" . escape_check($diff) . "') WHERE `ref`=" . $resource_log_previous_ref);
        return $resource_log_previous_ref;
        }
    else
        {
        sql_query("INSERT INTO `resource_log` (`date`, `user`, `resource`, `type`, `resource_type_field`, `notes`, `diff`, `usageoption`, `purchase_size`, " .
            "`purchase_price`, `access_key`, `previous_value`) VALUES (now()," .
            (($userref != "") ? "'$userref'" : "null") . ",'{$resource}','{$type}'," . (($field=="") ? "null" : "'{$field}'") . ",'" . escape_check($notes) . "','" .
            escape_check($diff) . "','{$usage}','{$purchase_size}','{$purchase_price}'," . (isset($k) ? "'{$k}'" : "null") . ",'" . escape_check($fromvalue) . "')");
        $log_ref=sql_insert_id();
        $resource_log_previous_ref=$log_ref;
        return $log_ref;
        }
	}

function get_resource_log($resource, $fetchrows=-1)
    {
    # Returns the log for a given resource.
    # The standard field titles are translated using $lang. Custom field titles are i18n translated.
    $extrafields=hook("get_resource_log_extra_fields");
    if (!$extrafields) {$extrafields="";}
    
    $log = sql_query("select distinct r.ref,r.date,u.username,u.fullname,r.type,f.title,r.notes,r.diff,r.usageoption,r.purchase_price,r.purchase_size,ps.name size, r.access_key,ekeys_u.fullname shared_by" . $extrafields . " from resource_log r left outer join user u on u.ref=r.user left outer join resource_type_field f on f.ref=r.resource_type_field left outer join external_access_keys ekeys on r.access_key=ekeys.access_key left outer join user ekeys_u on ekeys.user=ekeys_u.ref left join preview_size ps on r.purchase_size=ps.id where r.resource='$resource' order by r.date desc",false,$fetchrows);
    for ($n = 0;$n<count($log);$n++)
        {
        $log[$n]["title"] = lang_or_i18n_get_translated($log[$n]["title"], "fieldtitle-");
        }
    return $log;
    }

function get_resource_type_name($type)
	{
	global $lang;
	if ($type==999) {return $lang["archive"];}
	return lang_or_i18n_get_translated(sql_value("select name value from resource_type where ref='$type'",""),"resourcetype-");
	}
	
function get_resource_custom_access($resource)
	{
    # Return a list of usergroups with the custom access level for resource $resource (if set).
    # The standard usergroup names are translated using $lang. Custom usergroup names are i18n translated.
	$sql="";
	if (checkperm("E"))
		{
		# Restrict to this group and children groups only.
		global $usergroup,$usergroupparent;
		$sql="where g.parent='$usergroup' or g.ref='$usergroup' or g.ref='$usergroupparent'";
		}
    $resource_custom_access = sql_query("select g.ref,g.name,g.permissions,c.access from usergroup g left outer join resource_custom_access c on g.ref=c.usergroup and c.resource='$resource' $sql group by g.ref order by (g.permissions like '%v%') desc,g.name");
    for ($n = 0;$n<count($resource_custom_access);$n++)
        {
        $resource_custom_access[$n]["name"] = lang_or_i18n_get_translated($resource_custom_access[$n]["name"], "usergroup-");
        }
    return $resource_custom_access;
	}

function get_resource_custom_access_users_usergroups($resource)
    {
    # Returns only matching custom_access rows, with users and groups expanded
    return sql_query("select g.name usergroup,u.username user,c.access,c.user_expires expires from resource_custom_access c
        left outer join usergroup g on g.ref=c.usergroup
        left outer join user u on u.ref=c.user
        where c.resource='$resource' order by g.name,u.username");
    }
    
    
function save_resource_custom_access($resource)
	{
	$groups=get_resource_custom_access($resource);
	sql_query("delete from resource_custom_access where resource='$resource' and usergroup is not null");
	for ($n=0;$n<count($groups);$n++)
		{
		$usergroup=$groups[$n]["ref"];
		$access=getvalescaped("custom_" . $usergroup,0);
		sql_query("insert into resource_custom_access(resource,usergroup,access) values ('$resource','$usergroup','$access')");
		}
	}
	
function get_custom_access($resource,$usergroup,$return_default=true)
	{
	global $custom_access,$default_customaccess;
	if ($custom_access==false) {return 0;} # Custom access disabled? Always return 'open' access for resources marked as custom.

	$result=sql_value("select access value from resource_custom_access where resource='$resource' and usergroup='$usergroup'",'');
	if($result=='' && $return_default)
		{
		return $default_customaccess;
		}
	return $result;
	}
	
function get_themes_by_resource($ref)
	{
	global $theme_category_levels;

	$themestring="";
	for($n=1;$n<=$theme_category_levels;$n++){
		if ($n==1){$themeindex="";}else{$themeindex=$n;}
		$themestring.=",c.theme".$themeindex;
	}

	$themes=sql_query("select c.ref $themestring ,c.name,u.fullname from collection_resource cr join collection c on cr.collection=c.ref and cr.resource='$ref' and c.public=1 left outer join user u on c.user=u.ref order by length(theme) desc");
	# Combine the theme categories into one string so multiple category levels display correctly.
	$return=array();

	for ($n=0;$n<count($themes);$n++)
		{
		if (checkperm("j*") || checkperm("j" . $themes[$n]["theme"]))
			{
			$theme="";
			for ($x=1;$x<=$theme_category_levels;$x++){
				if ($x==1){$themeindex="";}else{$themeindex=$x;}
				if ($themes[$n]["theme".$themeindex]==""){break;}
				if ($themeindex!=""){$theme.=" / ";}

				if ($themes[$n]["theme".$themeindex]!="") {
					$theme.=$themes[$n]["theme".$themeindex];
				}
			}
			$themes[$n]["theme"]=$theme;
			$return[]=$themes[$n];
			}
		}
      
	return $return;
	}

function update_resource_type($ref,$type)
	{
	sql_query("update resource set resource_type='$type' where ref='$ref'");
	
	# Clear data that is no longer needed (data/keywords set for other types).
	sql_query("delete from resource_data where resource='$ref' and resource_type_field not in (select ref from resource_type_field where resource_type='$type' or resource_type=999 or resource_type=0)");
	sql_query("delete from resource_keyword where resource='$ref' and resource_type_field>0 and resource_type_field not in (select ref from resource_type_field where resource_type='$type' or resource_type=999 or resource_type=0)");	
	
        # Also index the resource type name, unless disabled
        global $index_resource_type;
        if ($index_resource_type)
                {
                $restypename=sql_value("select name value from resource_type where ref='" . escape_check($type) . "'","");
                remove_all_keyword_mappings_for_field($ref,-2);
                add_keyword_mappings($ref,$restypename,-2);
                }
        	
	}
	
function relate_to_array($ref,$array)	
	{
	# Relates a resource to each in a simple array of ref numbers
		sql_query("delete from resource_related where resource='$ref' or related='$ref'");  
		sql_query("insert into resource_related(resource,related) values ($ref," . join("),(" . $ref . ",",$array) . ")");
	}		

function get_exiftool_fields($resource_type)
	{
	# Returns a list of exiftool fields, which are basically fields with an 'exiftool field' set.
	return sql_query("select f.ref,f.type,f.exiftool_field,f.exiftool_filter,group_concat(n.name) as options,f.name from resource_type_field f left join node n on f.ref=n.resource_type_field where length(exiftool_field)>0 and (resource_type='$resource_type' or resource_type='0')  group by f.ref order by exiftool_field");
	}

function write_metadata($path, $ref, $uniqid="")
	{
	// copys the file to tmp and runs exiftool on it	
	// uniqid tells the tmp file to be placed in an isolated folder within tmp
	global $exiftool_remove_existing,$storagedir,$exiftool_write,$exiftool_no_process,$mysql_charset,$exiftool_write_omit_utf8_conversion;

    # Fetch file extension and resource type.
	$resource_data=get_resource_data($ref);
	$extension=$resource_data["file_extension"];
	$resource_type=$resource_data["resource_type"];

	$exiftool_fullpath = get_utility_path("exiftool");

    # Check if an attempt to write the metadata shall be performed.
	if (($exiftool_fullpath!=false) && ($exiftool_write) && !in_array($extension,$exiftool_no_process))
		{
		# Trust Exiftool's list of writable formats	
		$command=$exiftool_fullpath . " -listwf";
		$writable_formats=run_command($command);
		$writable_formats=str_replace("\n","",$writable_formats);
		$writable_formats_array=explode(" ",$writable_formats);
		if (!in_array(strtoupper($extension),$writable_formats_array)){return false;}
				
		$filename = pathinfo($path);
		$filename = $filename['basename'];	
		$randstring=md5(mt_rand()); // Added to make sure that simultaneous downloads are not attempting to write to the same file
		$tmpfile=get_temp_dir(false,$uniqid) . "/" . $randstring . "_" .  $filename;
		copy($path,$tmpfile);
		
        # Add the call to exiftool and some generic arguments to the command string.
        # Argument -overwrite_original: Now that we have already copied the original file, we can use exiftool's overwrite_original on the tmpfile.
        # Argument -E: Escape values for HTML. Used for handling foreign characters in shells not using UTF-8.
        # Arguments -EXIF:all= -XMP:all= -IPTC:all=: Remove the metadata in the tag groups EXIF, XMP and IPTC.
		$command = $exiftool_fullpath . " -m -overwrite_original -E ";
        if ($exiftool_remove_existing) {$command.= "-EXIF:all= -XMP:all= -IPTC:all= ";}

        //$write_to = get_exiftool_fields($resource_type); # Returns an array of exiftool fields for the particular resource type, which are basically fields with an 'exiftool field' set.
        $metadata_all=get_resource_field_data($ref, false,true,-1,getval("k","")!=""); // Using get_resource_field_data means we honour field permissions
		
        $write_to=array();
        foreach($metadata_all as $metadata_item)
            {
            if(trim($metadata_item["exiftool_field"])!="")
                {
                $write_to[]= $metadata_item;
                }
            }
        
        $writtenfields=array(); // Need to check if we are writing to an embedded field from more than one RS field, in which case subsequent values need to be appended, not replaced
           
        for($i = 0; $i<count($write_to); $i++) # Loop through all the found fields.
	    {
            $fieldtype = $write_to[$i]['type'];
            $writevalue = $write_to[$i]['value'];
            # Formatting and cleaning of the value to be written - depending on the RS field type.
            switch ($fieldtype)
                {
                case 2:
                case 3:
                case 9:
                case 12:
                    # Check box list, drop down, radio buttons or dynamic keyword list: remove initial comma if present
                    if (substr($writevalue, 0, 1)==",") {$writevalue = substr($writevalue, 1);}
                    break;                   
                case 4:
                case 6:
                case 10:
                    # Date / Expiry Date: write datetype fields in exiftool preferred format
                    $writevalue = date("Y:m:d H:i:sP", strtotime($writevalue));					
                    break;
                    # Other types, already set
                }
            $filtervalue=hook("additionalmetadatafilter", "", Array($write_to[$i]["exiftool_field"], $writevalue));
            if ($filtervalue) $writevalue=$filtervalue;
            # Add the tag name(s) and the value to the command string.
            $group_tags = explode(",", $write_to[$i]['exiftool_field']); # Each 'exiftool field' may contain more than one tag.
            foreach ($group_tags as $group_tag)
                {                
                $group_tag = strtolower($group_tag); # E.g. IPTC:Keywords -> iptc:keywords
                if (strpos($group_tag,":")===false) {$tag = $group_tag;} # E.g. subject -> subject
                else {$tag = substr($group_tag, strpos($group_tag,":")+1);} # E.g. iptc:keywords -> keywords
                
                $exifappend=false; // Need to replace values by default
                if(isset($writtenfields[$group_tag])) 
                        { 
                        // This embedded field is already being updated, we need to append values from this field                          
                        $exifappend=true;
                        debug("write_metadata - more than one field mappped to the tag '" . $group_tag . "'. Enabling append mode for this tag. ");
                        }
                        
                switch ($tag)
                    {
                    case "filesize":
                        # Do nothing, no point to try to write the filesize.
                        break;
                    case "filename":
                        # Do nothing, no point to try to write the filename either as ResourceSpace controls this.
                        break;
                    case "directory":
                        # Do nothing, we don't want metadata to control this
                        break;
                    case "keywords":                  
                        # Keywords shall be written one at a time and not all together.
						if(!isset($writtenfields["keywords"])){$writtenfields["keywords"]="";} 
						$keywords = explode(",", $writevalue); # "keyword1,keyword2, keyword3" (with or without spaces)
						if (implode("", $keywords) != "")
                        	{
                        	# Only write non-empty keywords/ may be more than one field mapped to keywords so we don't want to overwrite with blank
	                        foreach ($keywords as $keyword)
	                            {
                                $keyword = trim($keyword);
	                            if ($keyword != "")
	                            	{    
									debug("write_metadata - writing keyword:" . $keyword);
									$writtenfields[$group_tag].="," . $keyword;
										 
									# Convert the data to UTF-8 if not already.
									if (!$exiftool_write_omit_utf8_conversion && (!isset($mysql_charset) || (isset($mysql_charset) && strtolower($mysql_charset)!="utf8"))){$keyword = mb_convert_encoding($keyword, 'UTF-8');}
									$command.= escapeshellarg("-" . $group_tag . "-=" . htmlentities($keyword, ENT_QUOTES, "UTF-8")) . " "; // In case value is already embedded, need to manually remove it to prevent duplication
									$command.= escapeshellarg("-" . $group_tag . "+=" . htmlentities($keyword, ENT_QUOTES, "UTF-8")) . " ";
									}
	                            }
	                        }
                        break;
                    default:
                        if($exifappend && ($writevalue=="" || ($writevalue!="" && strpos($writtenfields[$group_tag],$writevalue)!==false)))
                            {                                                            
                            // The new value is blank or already included in what is being written, skip to next group tag
                            continue;                                
                            }                               
                        $writtenfields[$group_tag]=$writevalue;                          
                        debug ("write_metadata - updating tag " . $group_tag);
                        # Write as is, convert the data to UTF-8 if not already.
                        if (!$exiftool_write_omit_utf8_conversion && (!isset($mysql_charset) || (isset($mysql_charset) && strtolower($mysql_charset)!="utf8"))){$writevalue = mb_convert_encoding($writevalue, 'UTF-8');}
                        $command.= escapeshellarg("-" . $group_tag . "=" . htmlentities($writevalue, ENT_QUOTES, "UTF-8")) . " ";
                    }
                }
            }
            
            # Add the filename to the command string.
            $command.= " " . escapeshellarg($tmpfile);
            
            # Perform the actual writing - execute the command string.
            $output = run_command($command);
        return $tmpfile;
       }
    else
        {
        return false;
        }
    }

function delete_exif_tmpfile($tmpfile)
{
	if(file_exists($tmpfile)){unlink ($tmpfile);}
}

function update_resource($r,$path,$type,$title,$ingest=false,$createPreviews=true)
	{
	# Update the resource with the file at the given path
	# Note that the file will be used at it's present location and will not be copied.
	global $syncdir,$staticsync_prefer_embedded_title;

	update_resource_type($r, $type);

	# Work out extension based on path
	$extension=explode(".",$path);
        
        if(count($extension)>1)
            {
            $extension=trim(strtolower(end($extension)));
            }
        else
            {
            //No extension
            $extension="";
            }
            

	# file_path should only really be set to indicate a staticsync location. Otherwise, it should just be left blank.
	if ($ingest){$file_path="";} else {$file_path=escape_check($path);}

	# Store extension/data in the database
	sql_query("update resource set archive=0,file_path='".$file_path."',file_extension='$extension',preview_extension='$extension',file_modified=now() where ref='$r'");

	# Store original filename in field, if set
	if (!$ingest)
		{
		# This file remains in situ; store the full path in file_path to indicate that the file is stored remotely.
		global $filename_field;
		if (isset($filename_field))
			{

			$s=explode("/",$path);
			$filename=end($s);

			update_field($r,$filename_field,$filename);
			}
		}
	else
		{
		# This file is being ingested. Store only the filename.
		$s=explode("/",$path);
		$filename=end($s);

		global $filename_field;
		if (isset($filename_field))
			{
			update_field($r,$filename_field,$filename);
			}

		# Move the file
		global $syncdir;
		$destination=get_resource_path($r,true,"",true,$extension);
		$result=rename($syncdir . "/" . $path,$destination);
		if ($result===false)
			{
			# The rename failed. The file is possibly still being copied or uploaded and must be ignored on this pass.
			# Delete the resouce just created and return false.
			delete_resource($r);
			return false;
			}
		chmod($destination,0777);
		}

	# generate title and extract embedded metadata
	# order depends on which title should be the default (embedded or generated)
	if ($staticsync_prefer_embedded_title)
		{
		update_field($r,8,$title);
		extract_exif_comment($r,$extension);
		}
	else
		{
		extract_exif_comment($r,$extension);
		update_field($r,8,$title);
		}

	# Ensure folder is created, then create previews.
	get_resource_path($r,false,"pre",true,$extension);

	if ($createPreviews)
		{
		# Attempt autorotation
		global $autorotate_ingest;
		if($ingest && $autorotate_ingest){AutoRotateImage($destination);}
		# Generate previews/thumbnails (if configured i.e if not completed by offline process 'create_previews.php')
		global $enable_thumbnail_creation_on_upload;
		if ($enable_thumbnail_creation_on_upload) {create_previews($r,false,$extension,false,false,-1,false,$ingest);}
		}

	# Pass back the newly created resource ID.
	return $r;
	}

function import_resource($path,$type,$title,$ingest=false,$createPreviews=true)
	{
	# Import the resource at the given path
	# This is used by staticsync.php and Camillo's SOAP API
	# Note that the file will be used at it's present location and will not be copied.

	# Create resource
	$r=create_resource($type);
        return update_resource($r, $path, $type, $title, $ingest, $createPreviews);
	}

function get_alternative_files($resource,$order_by="",$sort="")
	{
	# Returns a list of alternative files for the given resource
	if ($order_by!="" && $sort!=""){
		$ordersort=$order_by." ".$sort.",";
	} else {
		$ordersort="";
	}
	$extrasql=hook("get_alternative_files_extra_sql","",array($resource));
	return sql_query("select ref,name,description,file_name,file_extension,file_size,creation_date,alt_type from resource_alt_files where resource='".escape_check($resource)."' $extrasql order by ".escape_check($ordersort)." name asc, file_size desc");
	}
	
function add_alternative_file($resource,$name,$description="",$file_name="",$file_extension="",$file_size=0,$alt_type='')
	{
	sql_query("insert into resource_alt_files(resource,name,creation_date,description,file_name,file_extension,file_size,alt_type) values ('$resource','" . escape_check($name) . "',now(),'" . escape_check($description) . "','" . escape_check($file_name) . "','" . escape_check($file_extension) . "','" . escape_check($file_size) . "','" . escape_check($alt_type) . "')");
	return sql_insert_id();
	}
	
function delete_alternative_file($resource,$ref)
	{
	# Delete any uploaded file.
	$info=get_alternative_file($resource,$ref);
	$path=get_resource_path($resource, true, "", true, $info["file_extension"], -1, 1, false, "", $ref);
	if (file_exists($path)) {unlink($path);}
	
        // run through all possible extensions/sizes
	$extensions = array();
	$extensions[]=$info['file_extension']?$info['file_extension']:"jpg";
	$extensions[]=isset($info['preview_extension'])?$info['preview_extension']:"jpg";
	$extensions[]=$GLOBALS['ffmpeg_preview_extension'];
        $extensions[]='jpg'; // always look for jpegs, just in case
	$extensions[]='icc'; // always look for extracted icc profiles
	$extensions=array_unique($extensions);
        $sizes = sql_array('select id value from preview_size');
	
        // in some cases, a jpeg original is generated for non-jpeg files like PDFs. Delete if it exists.
        $path=get_resource_path($resource, true,'', true, 'jpg', -1, 1, false, "", $ref);
        if (file_exists($path)) {
            unlink($path);
        }

        // in some cases, a mp3 original is generated for non-mp3 files like WAVs. Delete if it exists.
        $path=get_resource_path($resource, true,'', true, 'mp3', -1, 1, false, "", $ref);
        if (file_exists($path)) {
            unlink($path);
        }

        foreach ($extensions as $extension){
            foreach ($sizes as $size){
                $page = 1;
                $lastpage = 0;
                while ($page <> $lastpage){
                    $lastpage = $page;
                    $path=get_resource_path($resource, true, $size, true, $extension, -1, $page, false, "", $ref);
                    if (file_exists($path)) {
                        unlink($path);
                        $page++;
                    }
                }
            }
        }
        
	# Delete the database row
	sql_query("delete from resource_alt_files where resource='$resource' and ref='$ref'");
	
	# Log the deletion
	resource_log($resource,'y','');
	
	# Update disk usage
	update_disk_usage($resource);
	}
	
function get_alternative_file($resource,$ref)
	{
	# Returns the row for the requested alternative file
	$return=sql_query("select ref,name,description,file_name,file_extension,file_size,creation_date,alt_type from resource_alt_files where resource='$resource' and ref='$ref'");
	if (count($return)==0) {return false;} else {return $return[0];}
	}
	
function save_alternative_file($resource,$ref)
	{
	# Saves the 'alternative file' edit form back to the database
	$sql="";
	
	# Save data back to the database.
	sql_query("update resource_alt_files set name='" . getvalescaped("name","") . "',description='" . getvalescaped("description","") . "',alt_type='" . getvalescaped("alt_type","") . "' $sql where resource='$resource' and ref='$ref'");
    	}
	
if (!function_exists("user_rating_save")){	
function user_rating_save($userref,$ref,$rating)
	{
	# Save a user rating for a given resource
	$resource=get_resource_data($ref);
	
	# Recalculate the averate rating
	$total=$resource["user_rating_total"]; if ($total=="") {$total=0;}
	$count=$resource["user_rating_count"]; if ($count=="") {$count=0;}
	
	# modify behavior to allow only one current rating per user (which can be re-edited)
	global $user_rating_only_once;
	if ($user_rating_only_once){
		$ratings=array();
		$ratings=sql_query("select user,rating from user_rating where ref='$ref'");
		
		#Calculate ratings total and get current rating for user if available
		$total=0;
		$current="";
		for ($n=0;$n<count($ratings);$n++){
			$total+=$ratings[$n]['rating'];
			
			if ($ratings[$n]['user']==$userref){
				$current=$ratings[$n]['rating'];
				}
			}
		# Calculate Count
		$count=count($ratings);
		
		# if user has a current rating, subtract the old rating and add the new one.
		if ($current!=""){
			$total=$total-$current+$rating;
			if ($rating == 0) {  //rating remove feature
				sql_query("delete from user_rating where user='$userref' and ref='$ref'");
				$count--;
			} else {
				sql_query("update user_rating set rating='$rating' where user='$userref' and ref='$ref'");
			}
		}
		
		# if user does not have a current rating, add it 
		else {
			if ($rating != 0) {  //rating remove feature
				$total=$total+$rating;
				$count++;
				sql_query("insert into user_rating (user,ref,rating) values ('$userref','$ref','$rating')");
			}
		}

	}	
	else {
		# If not using $user_rating_only_once, Increment the total and count 
		$total+=$rating;
		$count++;
	}
	
	if ($count==0){
		# avoid division by zero
		$average=$total;
	} else {
	# work out a new average.
	$average=ceil($total/$count);
	}	
	
	# Save to the database
	sql_query("update resource set user_rating='$average',user_rating_total='$total',user_rating_count='$count' where ref='$ref'");
		
	}
}

function process_notify_user_contributed_submitted($ref,$htmlbreak)
	{
	global $use_phpmailer,$baseurl;
	$url="";
	$url=$baseurl . "/?r=" . $ref;
	
	if ($use_phpmailer){$url="<a href=\"$url\">$url</a>";}
	
	// Get the user (or username) of the contributor:
	$query = "SELECT user.username, user.fullname FROM resource INNER JOIN user ON user.ref = resource.created_by WHERE resource.ref ='".$ref."'";
	$result = sql_query($query);
	$user = '';
	if(trim($result[0]['fullname']) != '') 
		{
		$user = $result[0]['fullname'];
		} 
	else 
		{
		$user = $result[0]['username'];
		}
	return $htmlbreak . $user . ': ' . $url;
	}

function notify_user_contributed_submitted($refs,$collection=0)
	{
	// Send a notification mail to the administrators when resources are moved from "User Contributed - Pending Submission" to "User Contributed - Pending Review"
	global $notify_user_contributed_submitted,$applicationname,$email_notify,$baseurl,$lang,$use_phpmailer;
	if (!$notify_user_contributed_submitted) {return false;} # Only if configured.
	$htmlbreak="\r\n";
	if ($use_phpmailer){$htmlbreak="<br><br>";}
	
	$list="";
	if(is_array($refs))
		{
		for ($n=0;$n<count($refs);$n++)
			{
			$list .= process_notify_user_contributed_submitted($refs[$n],$htmlbreak);
			}
		}
	else
		{
		$list=process_notify_user_contributed_submitted($refs,$htmlbreak);
		}
		
	$list.=$htmlbreak;	
	
	$templatevars['url']=$baseurl . "/pages/search.php?search=!userpending";	
	$templatevars['list']=$list;
		
	$message=$lang["userresourcessubmitted"] . "\n\n". $templatevars['list'] . "\n\n" . $lang["viewalluserpending"] . "\n\n" . $templatevars['url'];
	$notificationmessage=$lang["userresourcessubmittednotification"];
	$notify_users=get_notification_users(array("e-1","e0")); 
	$message_users=array();
	foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_notifications', $send_message);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
				send_mail($notify_user["email"],$applicationname . ": " . $lang["status-1"],$message,"","","emailnotifyresourcessubmitted",$templatevars);
				}        
			else
				{
				$message_users[]=$notify_user["ref"];
				}
			}
	if (count($message_users)>0)
		{
		global $userref;
		if($collection!=0)
			{
			message_add($message_users,$notificationmessage,$baseurl . "/pages/search.php?search=!contributions" . $userref . "&archive=-1",$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,SUBMITTED_COLLECTION,$collection);
			}
		else
			{
			message_add($message_users,$notificationmessage,$baseurl . "/pages/search.php?search=!contributions" . $userref . "&archive=-1",$userref,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,MESSAGE_DEFAULT_TTL_SECONDS,SUBMITTED_RESOURCE,(is_array($refs)?$refs[0]:$refs));
			}
		}
	}
function notify_user_contributed_unsubmitted($refs,$collection=0)
	{
	// Send a notification mail to the administrators when resources are moved from "User Contributed - Pending Submission" to "User Contributed - Pending Review"
	
	global $notify_user_contributed_unsubmitted,$applicationname,$email_notify,$baseurl,$lang,$use_phpmailer;
	if (!$notify_user_contributed_unsubmitted) {return false;} # Only if configured.
	
	$htmlbreak="\r\n";
	if ($use_phpmailer){$htmlbreak="<br><br>";}
	
	$list="";
	if(is_array($refs))
		{
		for ($n=0;$n<count($refs);$n++)
			{
			$url="";	
			$url=$baseurl . "/?r=" . $refs[$n];
			
			if ($use_phpmailer){$url="<a href=\"$url\">$url</a>";}
			
			$list.=$htmlbreak . $url . "\n\n";
			}
		}
	else
		{
		$url="";	
		$url=$baseurl . "/?r=" . $refs;
		if ($use_phpmailer){$url="<a href=\"$url\">$url</a>";}
		$list.=$htmlbreak . $url . "\n\n";
		}
	
	$list.=$htmlbreak;		

	$templatevars['url']=$baseurl . "/pages/search.php?search=!userpending";	
	$templatevars['list']=$list;
		
	$message=$lang["userresourcesunsubmitted"]."\n\n". $templatevars['list'] . $lang["viewalluserpending"] . "\n\n" . $templatevars['url'];

	$notificationmessage=$lang["userresourcessubmittednotification"];
	$notify_users=get_notification_users(array("e-1","e0")); 
	$message_users=array();
	foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_notifications', $send_message);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
				send_mail($notify_user["email"],$applicationname . ": " . $lang["status-2"],$message,"","","emailnotifyresourcesunsubmitted",$templatevars);
				}        
			else
				{
				$message_users[]=$notify_user["ref"];
				}
			}
	if (count($message_users)>0)
		{
		global $userref;
        message_add($message_users,$notificationmessage,$baseurl . "/pages/search.php?search=!contributions" . $userref . "&archive=-2");
		}
	
	# Clear any outstanding notifications relating to submission of these resources
	message_remove_related(SUBMITTED_RESOURCE,$refs);
	if($collection!=0)
		{
		message_remove_related(SUBMITTED_COLLECTION,$collection);
		}
	}		
	
function get_fields_with_options()
{
    # Returns a list of fields that have option lists (checking user permissions).
    # The standard field titles are translated using $lang. Custom field titles are i18n translated.
    # Used for 'manage field options' page.

    # Executes query.
    $fields = sql_query("select ref, name, title, type, order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, display_as_dropdown from resource_type_field where type in (2,3,9,12) order by resource_type,order_by");

    # Applies permissions and translates field titles in the newly created array.
    $return = array();
    for ($n = 0;$n<count($fields);$n++) {
        if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
        && !checkperm("f-" . $fields[$n]["ref"])) {
            $fields[$n]["title"] = lang_or_i18n_get_translated($fields[$n]["title"], "fieldtitle-");
            $return[] = $fields[$n];
        }
    }
    return $return;
}

function get_field($field)
{
    # A standard field title is translated using $lang.  A custom field title is i18n translated.

    # Executes query.
    $r = sql_query("select ref, name, title, type, order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, display_as_dropdown from resource_type_field where ref='$field'");

    # Translates the field title if the searched field is found.
    if (count($r)==0) {
        return false;
    }
    else {
        $r[0]["title"] = lang_or_i18n_get_translated($r[0]["title"], "fieldtitle-");
        return $r[0];
    }
}

function get_field_options_with_stats($field)
	{
	# For a given field, list all options with usage stats.
	# This is for the 'manage field options' page.

	//$rawoptions=sql_value("select options value from resource_type_field where ref='$field'","");
	//$options=trim_array(explode(",",i18n_get_translated($rawoptions)));
    //$rawoptions=trim_array(explode(",",$rawoptions));

    $rawoptions=array();
    node_field_options_override($rawoptions,$field);

	# For the given field, fetch a stats count for each keyword.
	$usage=sql_query("
		  SELECT rk.resource_type_field,
		         k.keyword,
		         count(DISTINCT rk.resource) c
		    FROM resource_keyword rk
		    JOIN keyword k ON rk.keyword = k.ref
		   WHERE rk.resource > 0
		     AND resource_type_field = '$field'
		GROUP BY k.keyword;
	");
	
	$return=array();
	for ($n=0;$n<count($options);$n++)
		{
		if($options[$n]!=''){
			# Find the option in the usage array and extract the count
			$count=0;
			for ($m=0;$m<count($usage);$m++)
				{
				$keyword=get_keyword_from_option($options[$n]);
				if ($keyword==$usage[$m]["keyword"]) {$count=$usage[$m]["c"];}
				}
				
			$return[]=array("option"=>$options[$n],"rawoption"=>$rawoptions[$n],"count"=>$count);
			}
		}
	return $return;
	}
	
function save_field_options($field)
	{
	# Save the field options after editing.
	global $languages,$defaultlanguage;
	
	$fielddata=get_field($field);
	$options=get_nodes($field);
	//$options=trim_array(explode(",",$fielddata["options"]));

	for ($n=0;$n<count($options);$n++)
		{
		hook("before_save_field_options","",array($field,$options,$n));
		if (getval("submit_field_" . $n,"")!="")
			{
			# This option/language combination is being renamed.

			# Construct a new option from the posted languages
			$new="";$count=0;
			foreach ($languages as $langcode=>$langname)
				{
				$val=getvalescaped("field_" . $langcode . "_" . $n,"");
				if ($val!="") {$new.="~" . $langcode . ":" . $val;$count++;}
				}
			# Only one language, do not use language syntax.
			if ($count==1) {$new=getvalescaped("field_" . $defaultlanguage . "_" . $n,"");}
			
			# Construct a new options value by creating a new array replacing the item in position $n
			$newoptions=array_merge(array_slice($options,0,$n),array($new),array_slice($options,$n+1));

			# Update the options field.
			//sql_query("update resource_type_field set options='" . escape_check(join(", ",$newoptions)) . "' where ref='$field'");

            foreach ($newoptions as $no)
                {
                set_node(null,$field,$no,null,null);
                }

			# Loop through all matching resources.
			# The matches list uses 'like' so could potentially return values that do not have this option set. However each value list split out and analysed separately.
			$matching=sql_query("select resource,value from resource_data where resource_type_field='$field' and value like '%" . escape_check($options[$n]) . "%'");
			for ($m=0;$m<count($matching);$m++)
				{
				$ref=$matching[$m]["resource"];
				#echo "Processing $ref to update " . $options[$n] . "<br>existing value is " . $matching[$m]["value"] . "<br/>";
								
				$set=trim_array(explode(",",$matching[$m]["value"]));
				
				# Construct a new value omitting the old and adding the new.
				$newval=array();
				for ($s=0;$s<count($set);$s++)
					{
					if ($set[$s]!==$options[$n]) {$newval[]=$set[$s];}
					}
				$newval[]=$new; # Set the new value on the end of this string
				$newval=join(",",$newval);
				
				#echo "Old value = '" . $matching[$m]["value"] . "', new value = '" . $newval . "'";
				
				if ($matching[$m]["value"]!== $newval)
					{
					# Value has changed. Update.

					# Delete existing keywords index for this field.
					sql_query("delete from resource_keyword where resource='$ref' and resource_type_field='$field'");
					
					# Store value and reindex
					update_field($ref,$field,$newval);
					}
				}
			
			}


		if (getval("delete_field_" . $n,"")!="")
			{
			# This field option is being deleted.
			
			# Construct a new options value by creating a new array ommitting the item in position $n
			$new=array_merge(array_slice($options,0,$n),array_slice($options,$n+1));
			
			//sql_query("update resource_type_field set options='" . escape_check(join(", ",$new)) . "' where ref='$field'");

            foreach ($new as $new_option)
                {
                set_node(null,$field,escape_check(trim($new_option)),null,null);
                }
			
			# Loop through all matching resources.
			# The matches list uses 'like' so could potentially return values that do not have this option set. However each value list split out and analysed separately.
			$matching=sql_query("select resource,value from resource_data where resource_type_field='$field' and value like '%" . escape_check($options[$n]) . "%'");
			for ($m=0;$m<count($matching);$m++)
				{
				$ref=$matching[$m]["resource"];
				#echo "Processing $ref to remove " . $options[$n] . "<br>existing value is " . $matching[$m]["value"] . "<br/>";
								
				$set=trim_array(explode(",",$matching[$m]["value"]));
				$new=array();
				for ($s=0;$s<count($set);$s++)
					{
					if ($set[$s]!==$options[$n]) {$new[]=$set[$s];}
					}
				$new=join(",",$new);
				
				if ($matching[$m]["value"]!== $new)
					{
					# Value has changed. Update.

					# Delete existing keywords index for this field.
					sql_query("delete from resource_keyword where resource='$ref' and resource_type_field='$field'");
					
					# Store value and reindex
					update_field($ref,$field,$new);
					}
				}
			}
		}
	}
	
function get_resources_matching_keyword($keyword,$field)
	{
	# Returns an array of resource references for resources matching the given keyword string.
	$keyref=resolve_keyword($keyword);
	return sql_array("select distinct resource value from resource_keyword where keyword='$keyref' and resource_type_field='$field'");
	}
	
function get_keyword_from_option($option)
	{
	# For the given field option, return the keyword that will be indexed.
	$keywords=split_keywords("," . $option);

	global $stemming;
	if($stemming && function_exists('GetStem')) {
		$keywords[1] = GetStem($keywords[1]);
	}

	return $keywords[1];
	}
	
function add_field_option($field,$option)
	{
	//sql_query("update resource_type_field set options=concat(ifnull(options,''),', " . escape_check($option) . "') where ref='$field'");
    set_node(null,$field,escape_check(trim($option)),null,null);
	return true;
	}

if (!function_exists("get_resource_access")){	
function get_resource_access($resource)
	{
	# $resource may be a resource_data array from a search, in which case, many of the permissions checks are already done.	
		
	# Returns the access that the currently logged-in user has to $resource.
	# Return values:
	# 0 = Full Access (download all sizes)
	# 1 = Restricted Access (download only those sizes that are set to allow restricted downloads)
	# 2 = Confidential (no access)
	
	# Load the 'global' access level set on the resource
	# In the case of a search, resource type and global,group and user access are passed through to this point, to avoid multiple unnecessary get_resource_data queries.
	# passthru signifies that this is the case, so that blank values in group or user access mean that there is no data to be found, so don't check again .
	$passthru="no";

	// get_resource_data doesn't contain permissions, so fix for the case that such an array could be passed into this function unintentionally.
	if (is_array($resource) && !isset($resource['group_access']) && !isset($resource['user_access'])){$resource=$resource['ref'];}
	
	if (!is_array($resource))
                {
                $resourcedata=get_resource_data($resource,true);
                }
	else
                {
                $resourcedata=$resource;
                $passthru="yes";
                }
                
	$ref=$resourcedata['ref'];
	$access=$resourcedata["access"];
	$resource_type=$resourcedata['resource_type'];
	
	// Set a couple of flags now that we can check later on if we need to check whether sharing is permitted based on whether access has been specifically granted to user/group
    global $customgroupaccess,$customuseraccess;
	$customgroupaccess=false;
	$customuseraccess=false;
	
	global $k;
	if ($k!="")
		{
        global $internal_share_access;
		# External access - check how this was shared.
		$extaccess=sql_value("select access value from external_access_keys where resource=".$ref." and access_key='" . escape_check($k) . "' and (expires is null or expires>now())",-1);
		if ($extaccess!=-1 && (!$internal_share_access || ($internal_share_access && $extaccess<$access))) {return $extaccess;}
		}
	
	global $uploader_view_override, $userref;
	if (checkperm("z" . $resourcedata['archive']) && !($uploader_view_override && $resourcedata['created_by'] == $userref))
		{
		// User has no access to this archive state 
		return 2;
		}
	
	if (checkperm("v"))
		{
		# Permission to access all resources
		# Always return 0
		return 0; 
		}	

	if ($access==3)
		{
		$customgroupaccess=true;
		# Load custom access level
		if ($passthru=="no"){ 
			global $usergroup;
			$access=get_custom_access($resource,$usergroup);
			} 
		else {
			$access=$resource['group_access'];
		}
	}

	global $prevent_open_access_on_edit_for_active;
	if ($access == 1 && get_edit_access($ref,$resourcedata['archive'],false,$resourcedata) && !$prevent_open_access_on_edit_for_active)
		{
		# If access is restricted and user has edit access, grant open access.
		$access = 0;
		}

	global $open_access_for_contributor;
	if ($open_access_for_contributor && $access == 1 && $resourcedata['created_by'] == $userref)
		{
		# If access is restricted and user has contributed resource, grant open access.
		$access = 0;
		}


	# Check for user-specific and group-specific access (overrides any other restriction)
	global $userref,$usergroup;

	// We need to check for custom access either when access is set to be custom or
	// when the user group has restricted access to all resource types or specific resource types
	// are restricted
    if ($access!=0 || !checkperm('g') || checkperm('X' . $resource_type))
        {
        if ($passthru=="no")
            {
            $userspecific=get_custom_access_user($resource,$userref);
            $groupspecific=get_custom_access($resource,$usergroup,false);	
            } 
        else
            {
            $userspecific=$resourcedata['user_access'];
            $groupspecific=$resourcedata['group_access'];
            }
        }
	
	if (isset($userspecific) && $userspecific!="")
		{
		$customuseraccess=true;
		return $userspecific;
		}
	if (isset($groupspecific) && $groupspecific!="")
		{
		$customgroupaccess=true;
		return $groupspecific;
		}
        
	if (checkperm('T'.$resource_type))
		{
		// this resource type is always confidential/hidden for this user group
		return 2;
		}
		
	global $usersearchfilter, $search_filter_strict; 
	if ((trim($usersearchfilter)!="") && $search_filter_strict)
		{
		# A search filter has been set. Perform filter processing to establish if the user can view this resource.		
		# Always load metadata, because the provided metadata may be missing fields due to permissions.
                
                
                /*
                
                # ***** OLD METHOD ***** - used filter_match() - required duplication and was very difficult to implement OR matching for the field name supporting OR across fields
                
		$metadata=get_resource_field_data($ref,false,false);
		for ($n=0;$n<count($metadata);$n++)
			{
			$name=$metadata[$n]["name"];
			$value=$metadata[$n]["value"];			
			if ($name!="")
				{
				$match=filter_match($usersearchfilter,$name,$value);
                                echo "<br />$name/$value = $match";
				if ($match==1) {return 2;} # The match for this field was incorrect, always show as confidential in this event.
				}
			}
			
		# Also check resource type	
		# Disabled until also implented in do_search() - future feature - syntax supported in edit filter only for now.
		/*
		$match=filter_match($usersearchfilter,"resource_type",$resource_type);
		if ($match==1) {return 2;} # The match for this field was incorrect, always show as confidential in this event.
		*/
                
                # ***** NEW METHOD ***** - search for the resource, utilising the existing filter matching in do_search to avoid duplication.
                global $search_all_workflow_states;
                $search_all_workflow_states_cache = $search_all_workflow_states;
                $search_all_workflow_states = TRUE;
                $results=do_search("!resource" . $ref);
                $search_all_workflow_states = $search_all_workflow_states_cache;
                if (count($results)==0) {return 2;} # Not found in results, so deny
                }
		
	if ($access==0 && !checkperm("g") && !$customgroupaccess && !$customuseraccess)
		{
		# User does not have the 'g' permission. Return restricted for active resources unless group has been granted overide access.
		$access=1; 
		}
	
	if ($access==0 && checkperm('X'.$resource_type)){
		// this resource type is always restricted for this user group
		$access=1;
		}
		
	// Check derestrict filter
	global $userderestrictfilter;
	if ($access==1 && trim($userderestrictfilter)!="")
		{		
		# A filter has been set to derestrict access when certain metadata criteria are met
		if(!isset($metadata))
                   {
                    #  load metadata if not already loaded
                   $metadata=get_resource_field_data($ref,false,false);
                   }
		$matchedfilter=false;
		for ($n=0;$n<count($metadata);$n++)
			{
			$name=$metadata[$n]["name"];
			$value=$metadata[$n]["value"];			
			if ($name!="")
				{
				$match=filter_match($userderestrictfilter,$name,$value);
				if ($match==1) {$matchedfilter=false;break;} 
				if ($match==2) {$matchedfilter=true;} 
				}
			}
			
		if($matchedfilter){$access=0;}
        }
		
	return $access;	
	}
}
	
function get_custom_access_user($resource,$user)
	{
	return sql_value("select access value from resource_custom_access where resource='$resource' and user='$user' and (user_expires is null or user_expires>now())",false);
	}

function edit_resource_external_access($key,$access=-1,$expires="",$group="")
	{
	global $userref,$usergroup;
	if ($group=="" || !checkperm("x")) {$group=$usergroup;} # Default to sharing with the permission of the current usergroup if not specified OR no access to alternative group selection.
	if ($key==""){return false;}
	# Update the expiration and acccess
	sql_query("update external_access_keys set access='$access', expires=" . (($expires=="")?"null":"'" . $expires . "'") . ",date=now(),usergroup='$group' where access_key='$key'");
	hook('edit_resource_external_access','',array($key,$access,$expires,$group));
	return true;
	}

if (!function_exists("resource_download_allowed")){
function resource_download_allowed($resource,$size,$resource_type,$alternative=-1)
	{
	
	# For the given resource and size, can the curent user download it?
	# resource type and access may already be available in the case of search, so pass them along to get_resource_access to avoid extra queries
	# $resource can be a resource-specific search result array.
	$access=get_resource_access($resource);

	if ((checkperm('X' . $resource_type . "_" . $size) || checkperm('T' . $resource_type . "_" . $size)) && $alternative==-1)
		{
		# Block access to this resource type / size? Not if an alternative file
		# Only if no specific user access override (i.e. they have successfully requested this size).
		global $userref, $usergroup;
		$usercustomaccess = get_custom_access_user($resource,$userref);
		$usergroupcustomaccess = get_custom_access($resource,$usergroup);
		if (($usercustomaccess === false || !($usercustomaccess==='0')) && ($usergroupcustomaccess === false || !($usergroupcustomaccess==='0'))) {return false;}
		}

	# Full access
	if ($access==0)
		{
		return true;
		}

	# Special case for purchased downloads.
	global $userref;
	if (isset($userref))
		{
		$complete=sql_value("select cr.purchase_complete value from collection_resource cr join collection c on cr.collection=c.ref where c.user='$userref' and cr.resource='$resource' and cr.purchase_size='" . escape_check($size) . "'",0);
		if ($complete==1) {return true;}
		}

	# Restricted
	if ($access==1)
		{
		if ($size=="")
			{
			# Original file - access depends on the 'restricted_full_download' config setting.
			global $restricted_full_download;
			return $restricted_full_download;
			}
		else
			{
			# Return the restricted access setting for this resource type.
			return (sql_value("select allow_restricted value from preview_size where id='" . escape_check($size) . "'",0)==1);
			}
		}
		
	# Confidential
	if ($access==2)
		{
		return false;
		}
	
	}
}

function get_edit_access($resource,$status=-999,$metadata=false,&$resourcedata="")
	{
	# For the provided resource and metadata, does the  edit access does the current user have to this resource?
	# Checks the edit permissions (e0, e-1 etc.) and also the group edit filter which filters edit access based on resource metadata.
	
	global $userref,$usereditfilter,$edit_access_for_contributor;
	if (hook("customediteaccess")) {return true;}
	if (!is_array($resourcedata)) # Resource data  may not be passed 
		{
		$resourcedata=get_resource_data($resource);		
		}	
		
	if ($status==-999) # Archive status may not be passed 
		{$status=$resourcedata["archive"];}
		
	if ($resource==0-$userref) {return true;} # Can always edit their own user template.

        # If $edit_access_for_contributor is true in config then users can always edit their own resources.
        if ($edit_access_for_contributor && $userref==$resourcedata["created_by"]) {return true;}
        
	if (!checkperm("e" . $status)) {return false;} # Must have edit permission to this resource first and foremost, before checking the filter.
	
	if (checkperm("z" . $status) || ($status<0 && !(checkperm("t") || $resourcedata['created_by'] == $userref))) {return false;} # Cannot edit if z permission, or if other user uploads pending approval and not admin
	
	$gotmatch=false;
	if (trim($usereditfilter)=="" || $status<0) # No filter set, or resource is still in a User Contributed state in which case the edit filter should not be applied.
		{
		$gotmatch = true;
		}
	else
		{
		# An edit filter has been set. Perform edit filter processing to establish if the user can edit this resource.
		
		# Always load metadata, because the provided metadata may be missing fields due to permissions.
		$metadata=get_resource_field_data($resource,false,false);
				
		for ($n=0;$n<count($metadata);$n++)
			{
			$name=$metadata[$n]["name"];
			$value=$metadata[$n]["value"];			
			if ($name!="")
				{
				$match=filter_match($usereditfilter,$name,$value);
				if ($match==1) {return false;} # The match for this field was incorrect, always fail in this event.
				if ($match==2) {$gotmatch=true;} # The match for this field was correct.
				}
			}

		# Also check resource type, if specified.
		if (strpos($usereditfilter,"resource_type")!==false)
			{
			$resource_type=$resourcedata['resource_type'];

			$match=filter_match($usereditfilter,"resource_type",$resource_type);
			if ($match==1) {return false;} # Resource type was specified but the value did not match. Disallow edit access.
			if ($match==2) {$gotmatch=true;}
			}
			
		}
	
	if ($gotmatch) {
	  $gotmatch = !hook("denyafterusereditfilter");
	}
	
	# Default after all filter operations, allow edit.
	return $gotmatch;
	}


function filter_match($filter,$name,$value)
	{
	# In the given filter string, does name/value match?
	# Returns:
	# 0 = no match for name
	# 1 = matched name but value was not present
	# 2 = matched name and value was correct
	$s=explode(";",$filter);
	foreach ($s as $condition)
		{
		$s=explode("=",$condition);
		# Support for "NOT" matching. Return results only where the specified value or values are NOT set.
		$checkname=$s[0];$filter_not=false;
		if (substr($checkname,-1)=="!")
			{
			$filter_not=true;
			$checkname=substr($checkname,0,-1);# Strip off the exclamation mark.
			}
		if ($checkname==$name)
			{
			$checkvalues=$s[1];
			
			$s=explode("|",strtoupper($checkvalues));
			$v=trim_array(explode(",",strtoupper($value)));
			foreach ($s as $checkvalue)
				{
				if (in_array($checkvalue,$v))
					{
					return $filter_not ? 1 : 2;
					}
				}
			return $filter_not ? 2 : 1;
			}
		}
	return 0;
	}
	
function log_diff($fromvalue,$tovalue)	
	{
	# Forumlate descriptive text to describe the change made to a metadata field.

	# Remove any database escaping
	$fromvalue=str_replace("\\","",$fromvalue);
	$tovalue=str_replace("\\","",$tovalue);
	
	if (substr($fromvalue,0,1)==",")
		{
		# Work a different way for checkbox lists.
		$fromvalue=explode(",",i18n_get_translated($fromvalue));
		$tovalue=explode(",",i18n_get_translated($tovalue));
		
		# Get diffs
		$inserts=array_diff($tovalue,$fromvalue);
		$deletes=array_diff($fromvalue,$tovalue);

		# Process array diffs into meaningful strings.
		$return="";
		if (count($deletes)>0)
			{
			$return.="- " . join("\n- " , $deletes);
			}
		if (count($inserts)>0)
			{
			if ($return!="") {$return.="\n";}
			$return.="+ " . join("\n+ ", $inserts);
			}
		
		#debug($return);
		return $return;
		}

	# For standard strings, use Text_Diff
		
	require_once dirname(__FILE__).'/../lib/Text_Diff/Diff.php';
	require_once dirname(__FILE__).'/../lib/Text_Diff/Diff/Renderer/inline.php';

	$lines1 = explode("\n",$fromvalue);
	$lines2 = explode("\n",$tovalue);

	$diff     = new Text_Diff('native', array($lines1, $lines2));
	$renderer = new Text_Diff_Renderer_inline();
	$diff=$renderer->render($diff);
	
	$return="";

	# The inline diff syntax places inserts within <ins></ins> tags and deletes within <del></del> tags.

	# Handle deletes
	if (strpos($diff,"<del>")!==false)
		{
		$s=explode("<del>",$diff);
		for ($n=1;$n<count($s);$n++)
			{
			$t=explode("</del>",$s[$n]);
			if ($return!="") {$return.="\n";}
			$return.="- " . trim(i18n_get_translated($t[0]));
			}
		}
	# Handle inserts
	if (strpos($diff,"<ins>")!==false)
		{
		$s=explode("<ins>",$diff);
		for ($n=1;$n<count($s);$n++)
			{
			$t=explode("</ins>",$s[$n]);
			if ($return!="") {$return.="\n";}
			$return.="+ " . trim(i18n_get_translated($t[0]));
			}
		}


	#debug ($return);
	return $return;
	}
	
function update_xml_metadump($resource)
	{
	# Updates the XML metadata dump file when the resource has been altered.
	global $xml_metadump,$xml_metadump_dc_map;
	if (!$xml_metadump || $resource < 0) {return true;} # Only execute when configured and when not a template
	
	$path=dirname(get_resource_path($resource,true,"pre",true)) . "/metadump.xml";
	if (file_exists($path)){$wait=unlink($path);}
	
	$ext = htmlspecialchars(sql_value("select file_extension value from resource where ref = '$resource'",''),ENT_QUOTES);
	
	if ($result = sql_query("select resource_type, name from resource left join resource_type on resource.resource_type = resource_type.ref where resource.ref = '$resource'",false)){
		$rtype = $result[0]['resource_type'];
		$rtypename = htmlspecialchars($result[0]['name'],ENT_QUOTES);
	} else {
		$rtype = '';
		$rtypename = '';
	}

	$f=fopen($path,"w+");
	fwrite($f,"<?xml version=\"1.0\"?>\n");
	fwrite($f,"<record xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" resourcespace:resourceid=\"$resource\"");
	fwrite($f," resourcespace:extension=\"$ext\" resourcespace:resourcetype=\"$rtypename\" resourcespace:resourcetypeid=\"$rtype\" ");
	fwrite($f,">\n\n");
  
  	$data=get_resource_field_data($resource,false,false); # Get field data ignoring permissions
  	for ($n=0;$n<count($data);$n++)
	  	{
	  	if (array_key_exists($data[$n]["name"],$xml_metadump_dc_map))
	  		{
	  		# Dublin Core field
	  		fwrite($f,"<dc:" . $xml_metadump_dc_map[$data[$n]["name"]] . " ");
	  		$endtag="</dc:" . $xml_metadump_dc_map[$data[$n]["name"]] . ">";
	  		}
	  	else
	  		{
	  		# No Dublin Core mapping. RS specific field format.
	  		fwrite($f,"<resourcespace:field ");
	  		$endtag="</resourcespace:field>";
	  		}
	  		
	  	# Value processing
	  	$value=$data[$n]["value"];
	  	if (substr($value,0,1)==",") {$value=substr($value,1);} # Checkbox lists / dropdowns; remove initial comma
	  	
	  	# Write metadata
	  	fwrite($f,"rsfieldtitle=\"" . htmlspecialchars($data[$n]["title"]) . "\" rsembeddedequiv=\"" . htmlspecialchars($data[$n]["exiftool_field"]) . "\" rsfieldref=\"" . htmlspecialchars($data[$n]["resource_type_field"]) . "\" rsfieldtype=\"" . htmlspecialchars($data[$n]["type"]) . "\">" . htmlspecialchars($value) . $endtag . "\n\n");
	  	}

	fwrite($f,"</record>\n");
	fclose($f);
	//chmod($path,0777); // fixme - temporarily make world readable/writable until we have better solution for file permissions

	}

function get_metadata_templates()
	{
	# Returns a list of all metadata templates; i.e. resources that have been set to the resource type specified via '$metadata_template_resource_type'.
	global $metadata_template_resource_type,$metadata_template_title_field;
	return sql_query("select ref,field$metadata_template_title_field from resource where ref>0 and resource_type='$metadata_template_resource_type' order by field$metadata_template_title_field");
	}
 
function get_resource_collections($ref)
	{
	global $userref, $anonymous_user, $username;
	if (checkperm('b') || (isset($anonymous_login) && $username==$anonymous_login))
		{return array();}
	# Returns a list of collections that a resource is used in for the $view_resource_collections option
	$sql="";
   
    # Include themes in my collections? 
    # Only filter out themes if $themes_in_my_collections is set to false in config.php
   	global $themes_in_my_collections;
   	if (!$themes_in_my_collections)
   		{
   		if ($sql!="") {$sql.=" and ";}
   		$sql.="(length(c.theme)=0 or c.theme is null) ";
   		}
	if ($sql!="") {$sql="where " . $sql;}
   
	$return=sql_query ("select * from 
	(select c.*,u.username,u.fullname,count(r.resource) count from user u join collection c on u.ref=c.user and c.user='$userref' left outer join collection_resource r on c.ref=r.collection group by c.ref
	union
	select c.*,u.username,u.fullname,count(r.resource) count from user_collection uc join collection c on uc.collection=c.ref and uc.user='$userref' and c.user<>'$userref' left outer join collection_resource r on c.ref=r.collection left join user u on c.user=u.ref group by c.ref) clist where clist.ref in (select collection from collection_resource cr where cr.resource=$ref)");
	
	return $return;
	}
	
function download_summary($resource)
	{
	# Returns a summary of downloads by usage type
	return sql_query("select usageoption,count(*) c from resource_log where resource='$resource' and type='D' group by usageoption order by usageoption");
	}
	
	
function check_use_watermark(){
	# access status must be available prior to this.
	# This function checks whether to use watermarks or not.
	# Three cases:
	# if access is restricted and the group has "w"
	# if $watermark_open is true and the group has "w"
	# if $watermark is set and it's an external share.
	global $access,$k,$watermark,$watermark_open,$pagename,$watermark_open_search;
	if (($watermark_open && ($pagename == "preview" || $pagename == "view" || ($pagename == "search" && $watermark_open_search)) || $access==1) && (checkperm('w') || ($k!="" && isset($watermark)))){return true;} else {return false;}
}

function autocomplete_blank_fields($resource)
	{
	# Fill in any blank fields for the resource
	
	# Fetch resource type
	$resource_type=sql_value("select resource_type value from resource where ref='$resource'",0);
	
	# Fetch field list
	$fields=sql_query("select ref,autocomplete_macro from resource_type_field where (resource_type=0 || resource_type='$resource_type') and length(autocomplete_macro)>0");
	foreach ($fields as $field)
		{
		$value=sql_value("select value from resource_data where resource='$resource' and resource_type_field='" . $field["ref"] . "'","");
		if (strlen(trim($value))==0)
			{
			# Empty value. Autocomplete and set.
			$value=eval($field["autocomplete_macro"]);	
			update_field($resource,$field["ref"],$value);
			}
		}
	}


function get_resource_files($ref,$includeorphan=false){
    // returns array of all files associated with a resource
    // if $includeorphan set to true, will also return all files in the
    // resource dir even if the system doesn't understand why they're there.

    $filearray = array();
    $file_checklist = array();

    global $config_windows;
    if ($config_windows){ $sep = "\\"; } else { $sep = "/"; }


    $sizearray = sql_array("select id value from preview_size",false);
    $original_ext = sql_value("select file_extension value from resource where ref = '".escape_check($ref)."'",'');

    $rootpath=dirname(get_resource_path($ref,true,"pre",true));

    // get listing of all files in resource dir to compare mark off as we find them
    if (is_dir($rootpath)) {
    if ($dh = opendir($rootpath)) {
            while (($file = readdir($dh)) !== false) {
                if (!($file == '.' || $file == '..')){
                    $file_checklist[$rootpath.$sep.$file] = 1;
                }
            }
            closedir($dh);
        }
    }

    // first get the resource itself
    $original = get_resource_path($ref,true,'',false,$original_ext);
    if (file_exists($original)){
	    array_push($filearray,$original);
	    unset($file_checklist[$original]);
    }

    // in some cases, the system also generates a jpeg equivalent of the original, so check for that
    $original = get_resource_path($ref,true,'',false,'jpg');
    if (file_exists($original)){
	    array_push($filearray,$original);
    	unset($file_checklist[$original]);
    }

    // in some cases, the system also generates an mp3 equivalent of the original, so check for that
    $original = get_resource_path($ref,true,'',false,'mp3');
    if (file_exists($original)){
    	array_push($filearray,$original);
    	unset($file_checklist[$original]);
    }

    // in some cases, the system also generates an extracted icc profile, so check for that
    $original = get_resource_path($ref,true,'',false,'icc');
    if (file_exists($original)){
    	array_push($filearray,$original);
    	unset($file_checklist[$original]);
    }


    # check for pages
    $page = 1;
    $misscount = 0;
    // just to be safe, we'll try at least 4 pages ahead to make sure none got skipped
    while($misscount < 4){
        $thepath = get_resource_path($ref,true,"scr",false,'jpg',-1,$page,"","","");
        if (file_exists($thepath)){
            array_push($filearray,$thepath);
            unset($file_checklist[$thepath]);
            $page++;
        } else {
            $misscount++;
            $page++;
        }
    }        

    // now look for other sizes
    foreach($sizearray as $size){
        $thepath = get_resource_path($ref,true,$size,false,'jpg');
        if (file_exists($thepath)){
            array_push($filearray,$thepath);
            unset($file_checklist[$thepath]);
        }
    }


    // get alternative files
    $altfiles = get_alternative_files($ref);
    foreach($altfiles as $altfile){
        // first get original
        $alt_ext = sql_value("select file_extension value from resource_alt_files where ref = '" . $altfile['ref'] . "'",'');
        $thepath = get_resource_path($ref,true,'',false,$alt_ext,-1,1,false,"",$altfile["ref"]);
        if (file_exists($thepath)){
            array_push($filearray,$thepath);
            unset($file_checklist[$thepath]);
        }


        // now check for previews
        foreach($sizearray as $size){
            $thepath = get_resource_path($ref,true,$size,false,"jpg",-1,1,false,"",$altfile["ref"]);
            if (file_exists($thepath)){
                array_push($filearray,$thepath);
                unset($file_checklist[$thepath]);
            }
        }

        # check for pages
        $page = 1;
        while($page <> 0){
            $thepath = get_resource_path($ref,true,"scr",false,'jpg',-1,$page,"","",$altfile['ref']);
            if (file_exists($thepath)){
                array_push($filearray,$thepath);
                unset($file_checklist[$thepath]);
                $page++;
            } else {
                $page = 0;
            }
        }
        // in some cases, the system also generates a jpeg equivalent of the original, so check for that
        $original = get_resource_path($ref,true,'',false,'jpg',-1,1,'','',$altfile['ref']);
	if (file_exists($original)){
	        array_push($filearray,$original);
        	unset($file_checklist[$original]);
    	}

        // in some cases, the system also generates a mp3 equivalent of the original, so check for that
        $original = get_resource_path($ref,true,'',false,'mp3',-1,1,'','',$altfile['ref']);
	if (file_exists($original)){
	        array_push($filearray,$original);
       		unset($file_checklist[$original]);
	}

        // in some cases, the system also generates an extracted icc profile, so check for that
        $original = get_resource_path($ref,true,'',false,'icc',-1,1,'','',$altfile['ref']);
	if (file_exists($original)){
	        array_push($filearray,$original);
       		unset($file_checklist[$original]);
	}
    }


    // check for metadump
    $thefile="$rootpath/metadump.xml";
    if (file_exists($thefile)){
        array_push($filearray,$thefile);
        unset($file_checklist[$thefile]);
    }

    // check for ffmpeg previews
    global $ffmpeg_preview_extension;
    $flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
    if (file_exists($flvfile)){
        array_push($filearray,$flvfile);
        unset($file_checklist[$flvfile]);
    }


    if (count($file_checklist)>0){
	foreach (array_keys($file_checklist) as $thefile){
		debug("ResourceSpace: Orphaned file, resource $ref: $thefile");
	        if ($includeorphan) {
			array_push($filearray,$thefile);
		}
       }
    }
    return array_unique($filearray);
}

if (!function_exists("reindex_resource")){
function reindex_resource($ref)
	{
	global $index_contributed_by, $index_resource_type;
	# Reindex a resource. Delete all resource_keyword rows and create new ones.
	
	# Delete existing keywords
	sql_query("delete from resource_keyword where resource='$ref'");

	# Index fields
	$data=get_resource_field_data($ref,false,false); # Fetch all fields and do not use permissions.
	for ($m=0;$m<count($data);$m++)
		{
		if ($data[$m]["keywords_index"]==1)
			{
			#echo $data[$m]["value"];
			$value=$data[$m]["value"];
			if ($data[$m]["type"]==3 || $data[$m]["type"]==2)
				{
				# Prepend a comma when indexing dropdowns
				$value="," . $value;
				}
			
			# Date field? These need indexing differently.
			$is_date=($data[$m]["type"]==4 || $data[$m]["type"]==6);

			$is_html=($data[$m]["type"]==8);					
			add_keyword_mappings($ref,i18n_get_indexable($value),$data[$m]["ref"],$data[$m]["partial_index"],$is_date,'','',$is_html);		
			}
		}
	
	# Also index contributed by field, unless disabled
	if ($index_contributed_by)
		{
		$resource=get_resource_data($ref);
		$userinfo=get_user($resource["created_by"]);
		add_keyword_mappings($ref,$userinfo["username"] . " " . $userinfo["fullname"],-1);
		}

        # Also index the resource type name, unless disabled
	if ($index_resource_type)
		{
		$restypename=sql_value("select name value from resource_type where ref in (select resource_type from resource where ref='" . escape_check($ref) . "')","");
		add_keyword_mappings($ref,$restypename,-2);
		}
                
	# Always index the resource ID as a keyword
	add_keyword_mappings($ref, $ref, -1);
	
	hook("afterreindexresource","all",array($ref));
	}
}

function get_page_count($resource,$alternative=-1)
    {
    # gets page count for multipage previews from resource_dimensions table.
    # also handle alternative file multipage previews by switching $resource array if necessary
    # $alternative specifies an actual alternative file
    $ref=$resource['ref'];
    if ($alternative!=-1)
        {
        $pagecount=sql_value("select page_count value from resource_alt_files where ref=$alternative","");
        $resource=get_alternative_file($ref,$alternative);
        }
    else
        {
        $pagecount=sql_value("select page_count value from resource_dimensions where resource=$ref","");
        }
    if (!empty($pagecount)) { return $pagecount; }
    # or, populate this column with exiftool or image magick (for installations with many pdfs already
	# previewed and indexed, this allows pagecount updates on the fly when needed):
    # use exiftool. 
	if ($resource['file_extension']=="pdf" && $alternative==-1)
		{
		$file=get_resource_path($ref,true,"",false,"pdf");
		}
	else if ($alternative==-1)
		{
		# some unoconv files are not pdfs but this needs to use the auto-alt file
		$alt_ref=sql_value("select ref value from resource_alt_files where resource=$ref and unoconv=1","");
		$file=get_resource_path($ref,true,"",false,"pdf",-1,1,false,"",$alt_ref);
		}
	else
		{
		$file=get_resource_path($ref,true,"",false,"pdf",-1,1,false,"",$alternative);
		}

	# locate exiftool
    $exiftool_fullpath = get_utility_path("exiftool");
    if ($exiftool_fullpath==false)
		{
		# Try with ImageMagick instead
		$command = get_utility_path("im-identify") . ' -format %n ' . $file;
		$pages = trim(run_command($command));
		}
    else
        {
        $command = $exiftool_fullpath;
    	
        $command=$command." -sss -pagecount $file";
        $output=run_command($command);
        $pages=str_replace("Page Count","",$output);
        $pages=str_replace(":","",$pages);
        $pages=trim($pages);
		}

	if (!is_numeric($pages)){ $pages = 1; } // default to 1 page if we didn't get anything back

	if ($alternative!=-1)
		{
		sql_query("update resource_alt_files set page_count='$pages' where ref=$alternative");
		}
	else
		{
		sql_query("update resource_dimensions set page_count='$pages' where resource=$ref");
		}
	return $pages;
	}


function update_disk_usage($resource)
	{

	# we're also going to record the size of the primary resource here before we do the entire folder
	$ext = sql_value("select file_extension value from resource where ref = '$resource'",'jpg');
	$path = get_resource_path($resource,true,'',false,$ext);
	if (file_exists($path)){
		$rsize = filesize_unlimited($path);
	} else {
		$rsize = 0;
	}

	# Scan the appropriate filestore folder and update the disk usage fields on the resource table.
	$dir=dirname(get_resource_path($resource,true,"",false));
	if (!file_exists($dir)) {return false;} # Folder does not yet exist.
	$d = dir($dir); 
	$total=0;
	while ($f = $d->read())
		{
		if ($f!=".." && $f!=".")
			{
			$s=filesize_unlimited($dir . "/" .$f);
			#echo "<br/>-". $f . " : " . $s;
			$total+=$s;
			}
		}
	#echo "<br/>total=" . $total;
	sql_query("update resource set disk_usage='$total',disk_usage_last_updated=now(),file_size='$rsize' where ref='$resource'");
	return true;
	}

function update_disk_usage_cron()
	{
	# Update disk usage for all resources that have not yet been updated or have not been updated in the past 30 days.
	# Limit to a reasonable amount so that this process is spread over several cron intervals for large data sets.
	$resources=sql_array("select ref value from resource where ref>0 and disk_usage_last_updated is null or datediff(now(),disk_usage_last_updated)>30 limit 20000");
	foreach ($resources as $resource)
		{
		update_disk_usage($resource);
		}
	}

function get_total_disk_usage()
	{
	# Returns sum of all resource disk usage
	return sql_value("select sum(disk_usage) value from resource",0);
	}

function overquota()
	{
	# Return true if the system is over quota
	global $disksize;
	if (isset($disksize))
		{
		# Disk quota functionality. Calculate the usage by the $storagedir folder only rather than the whole disk.
		# Unix only due to reliance on 'du' command
		$avail=$disksize*(1024*1024*1024);
		$used=get_total_disk_usage();
		
		$free=$avail-$used;
		if ($free<=0) {return true;}
		}
	return false;
	}

function notify_user_resources_approved($refs)
	{
	// Send a notification mail to the user when resources have been approved
	global $applicationname,$baseurl,$lang;	
	debug("Emailing user notifications of resource approvals");	
	$htmlbreak="\r\n";
	global $use_phpmailer,$userref,$templatevars;
	if ($use_phpmailer){$htmlbreak="<br><br>";}
	$notifyusers=array();
	
    if(!is_array($refs))
        {
        $refs=array($refs);    
        }
	for ($n=0;$n<count($refs);$n++)
		{
		$ref=$refs[$n];
		$contributed=sql_value("select created_by value from resource where ref='$ref'",0);
		if($contributed!=0 && $contributed!=$userref)
			{
			if(!isset($notifyusers[$contributed])) // Add new array entry if not already present
				{
				$notifyusers[$contributed]=array();
				$notifyusers[$contributed]["list"]="";
				$notifyusers[$contributed]["resources"]=array();
				$notifyusers[$contributed]["url"]=$baseurl . "/pages/search.php?search=!contributions" . $contributed . "&archive=0";
				}		
			$notifyusers[$contributed]["resources"][]=$ref;
			$url=$baseurl . "/?r=" . $refs[$n];		
			if ($use_phpmailer){$url="<a href=\"$url\">$url</a>";}
			$notifyusers[$contributed]["list"].=$htmlbreak . $url . "\n\n";
			}		
		}
	foreach($notifyusers as $key=>$notifyuser)	
		{
		$templatevars['list']=$notifyuser["list"];
		$templatevars['url']=$notifyuser["url"];			
		$message=$lang["userresourcesapproved"] . "\n\n". $templatevars['list'] . "\n\n" . $lang["viewcontributedsubittedl"] . "\n\n" . $notifyuser["url"];
		$notificationmessage=$lang["userresourcesapproved"];
		
		// Does the user want these messages?
		get_config_option($key,'user_pref_resource_notifications', $send_message);		  
        if($send_message==false){continue;}		
       
		// Does the user want an email or notification?
		get_config_option($key,'email_user_notifications', $send_email);    
		if($send_email)
			{
			$notify_user=sql_value("select email value from user where ref='$key'","");
			if($notify_user!='')
				{
				send_mail($notify_user,$applicationname . ": " . $lang["approved"],$message,"","","emailnotifyresourcesapproved",$templatevars);
				}
			}        
		else
			{
			global $userref;
			message_add($key,$notificationmessage,$notifyuser["url"]);
			}
		}
	}
	
		

function get_original_imagesize($ref="",$path="", $extension="jpg", $forcefromfile=false)
	{
	$fileinfo=array();
	if($ref=="" || $path==""){return false;}
	global $imagemagick_path, $imagemagick_calculate_sizes;
	$file=$path;
	
	$o_size=sql_query("select * from resource_dimensions where resource={$ref}");
	if(!empty($o_size))
		{
		if(count($o_size)>1)
			{
			# delete all the records and start fresh. This is a band-aid should there be multiple records as a result of using api_search
			sql_query("delete from resource_dimensions where resource={$ref}");
			$o_size=false;
			$forcefromfile=true;
			}
		else
			{
			$o_size=$o_size[0];
			}
		}
	else
		{
		$o_size=false;
		}
		
	if($o_size!==false && !$forcefromfile){
		
		$fileinfo[0]=$o_size['file_size'];
		$fileinfo[1]=$o_size['width'];
		$fileinfo[2]=$o_size['height'];
		return $fileinfo;
	}
	
	$filesize=filesize_unlimited($file);
	
	# imagemagick_calculate_sizes is normally turned off 
	if (isset($imagemagick_path) && $imagemagick_calculate_sizes)
		{
		# Use ImageMagick to calculate the size
		
		$prefix = '';
		# Camera RAW images need prefix
		if (preg_match('/^(dng|nef|x3f|cr2|crw|mrw|orf|raf|dcr)$/i', $extension, $rawext)) { $prefix = $rawext[0] .':'; }

		# Locate imagemagick.
		$identify_fullpath = get_utility_path("im-identify");
		if ($identify_fullpath==false) {exit("Could not find ImageMagick 'identify' utility at location '$imagemagick_path'.");}	
		# Get image's dimensions.
		$identcommand = $identify_fullpath . ' -format %wx%h '. escapeshellarg($prefix . $file) .'[0]';
		$identoutput=run_command($identcommand);
		preg_match('/^([0-9]+)x([0-9]+)$/ims',$identoutput,$smatches);
		@list(,$sw,$sh) = $smatches;
		if (($sw!='') && ($sh!=''))
			{
			if(!$o_size)
				{
				sql_query("insert into resource_dimensions (resource, width, height, file_size) values('". $ref ."', '". $sw ."', '". $sh ."', '" . $filesize . "')");
				}
			else
				{
				sql_query("update resource_dimensions set width='". $sw ."', height='". $sh ."', file_size='" . $filesize . "' where resource={$ref}");
				}
			}
		}	
	else 
		{
		# check if this is a raw file.	
		$rawfile = false;
		if (preg_match('/^(dng|nef|x3f|cr2|crw|mrw|orf|raf|dcr)$/i', $extension, $rawext)){$rawfile=true;}
			
		# Use GD to calculate the size
		if (!((@list($sw,$sh) = @getimagesize($file))===false)&& !$rawfile)
			{
			if(!$o_size)
				{	
				sql_query("insert into resource_dimensions (resource, width, height, file_size) values('". $ref ."', '". $sw ."', '". $sh ."', '" . $filesize . "')");
				}
			else
				{
				sql_query("update resource_dimensions set width='". $sw ."', height='". $sh ."', file_size='" . $filesize . "' where resource={$ref}");
				}
			}
		else
			{

			# Assume size cannot be calculated.
			$sw="?";$sh="?";

			global $ffmpeg_supported_extensions;
			if (in_array(strtolower($extension), $ffmpeg_supported_extensions) && function_exists('json_decode'))
			    {
			    $ffprobe_fullpath = get_utility_path("ffprobe");

			    $file=get_resource_path($ref,true,"",false,$extension);
			    $ffprobe_output=run_command($ffprobe_fullpath . " -v 0 " . escapeshellarg($file) . " -show_streams -of json");
			    $ffprobe_array=json_decode($ffprobe_output, true);
			    # Different versions of ffprobe store the dimensions in different parts of the json output. Test both.
			    if (!empty($ffprobe_array['width'] )) { $sw = intval($ffprobe_array['width']);  }
			    if (!empty($ffprobe_array['height'])) { $sh = intval($ffprobe_array['height']); }
			    if (isset($ffprobe_array['streams']) && is_array($ffprobe_array['streams']))
					{
					foreach( $ffprobe_array['streams'] as $stream )
						{
						if (!empty($stream['codec_type']) && $stream['codec_type'] === 'video')
							{
							$sw = intval($stream['width']);
							$sh = intval($stream['height']);
							break;
							}
						}
					}
				}

			if ($sw!=='?' && $sh!=='?')
			    {
			    # Size could be calculated after all
			    if(!$o_size)
					{
					sql_query("insert into resource_dimensions (resource, width, height, file_size) values('". $ref ."', '". $sw ."', '". $sh ."', '" . $filesize . "')");
					}
				else
					{
					sql_query("update resource_dimensions set width='". $sw ."', height='". $sh ."', file_size='" . $filesize . "' where resource={$ref}");
					}
			    }
			else
			    {

			    # Size cannot be calculated.
			    $sw="?";$sh="?";
				if(!$o_size)
					{
					# Insert a dummy row to prevent recalculation on every view.
					sql_query("insert into resource_dimensions (resource, width, height, file_size) values('". $ref ."','0', '0', '" . $filesize . "')");
					}
				else
					{
					sql_query("update resource_dimensions set width='0', height='0', file_size='" . $filesize . "' where resource={$ref}");
					}
				}
			}
		}
		
		
		$fileinfo[0]=$filesize;
		$fileinfo[1]=$sw;
		$fileinfo[2]=$sh;
		return $fileinfo;
	
	}
        
function generate_resource_access_key($resource,$userref,$access,$expires,$email,$group="")
        {
        if(checkperm("noex"))
            {
            // Shouldn't ever happen, but catch in case not already checked
            return false;
            }
                
        global $userref,$usergroup;
		if ($group=="" || !checkperm("x")) {$group=$usergroup;} # Default to sharing with the permission of the current usergroup if not specified OR no access to alternative group selection.
        $k=substr(md5(time()),0,10);
		sql_query("insert into external_access_keys(resource,access_key,user,access,expires,email,date,usergroup) values ('$resource','$k','$userref','$access'," . (($expires=="")?"null":"'" . $expires . "'"). ",'" . escape_check($email) . "',now(),'$group');");
		hook("generate_resource_access_key","",array($resource,$k,$userref,$email,$access,$expires,$group));
        return $k;
        }

if(!function_exists("get_resource_external_access")){
function get_resource_external_access($resource)
	{
	# Return all external access given to a resource 
	# Users, emails and dates could be multiple for a given access key, an in this case they are returned comma-separated.
	return sql_query("select access_key,group_concat(DISTINCT user ORDER BY user SEPARATOR ', ') users,group_concat(DISTINCT email ORDER BY email SEPARATOR ', ') emails,max(date) maxdate,max(lastused) lastused,access,expires,collection,usergroup from external_access_keys where resource='$resource' group by access_key order by date");
	}
}
        
function delete_resource_access_key($resource,$access_key)
    {
    sql_query("delete from external_access_keys where access_key='$access_key' and resource='$resource'");
    }

function resource_type_config_override($resource_type)
    {
    # Pull in the necessary config for a given resource type
    # As this could be called many times, e.g. during search result display, only execute if the passed resourcetype is different from the previous.
    global $resource_type_config_override_last,$resource_type_config_override_snapshot;
    
    # If the resource type has changed or if this is the first resource....
    if (!isset($resource_type_config_override_last) || $resource_type_config_override_last!=$resource_type)
        {
        # Look for config and execute.
        $config_options=sql_value("select config_options value from resource_type where ref='" . escape_check($resource_type) . "'","");
        if ($config_options!="")
            {
            # Switch to global context and execute.
            extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
            eval($config_options);
            }
        $resource_type_config_override_last=$resource_type;
        }
    }

function update_archive_status($resource,$archive)
    {
    sql_query("update resource set archive='" . escape_check($archive) .  "' where ref='" . escape_check($resource) . "'");  
    }

function delete_resources_in_collection($collection) {

	global $resource_deletion_state;

	// Always find all resources in deleted state and delete them permanently:
	// Note: when resource_deletion_state is null it will find all resources in collection and delete them permanently
	$query = sprintf("
				SELECT ref AS value
				  FROM resource
			INNER JOIN collection_resource ON collection_resource.resource = resource.ref AND collection_resource.collection = '%s'
				 %s;
	",
		$collection,
		isset($resource_deletion_state) ? "WHERE archive = '" . $resource_deletion_state . "'" : ''
	);

	$resources_in_deleted_state = array();
	$resources_in_deleted_state = sql_array($query);

	if(!empty($resources_in_deleted_state)) {
		foreach ($resources_in_deleted_state as $resource_in_deleted_state) {
			delete_resource($resource_in_deleted_state);
		}
		collection_log($collection,'D', '', 'Resource ' . $resource_in_deleted_state . ' deleted permanently.');
	}

	// Create a comma separated list of all resources remaining in this collection:
	$resources = sql_array("SELECT resource AS value FROM collection_resource WHERE collection = '" . $collection . "';");
	$resources = implode(',', $resources);
	
	// If all resources had their state the same as resource_deletion_state, stop here:
	// Note: when resource_deletion_state is null it will always stop here
	if(empty($resources)) {
		return TRUE;
	}

	// Delete (ie. move to resource_deletion_state set in config):
	if(isset($resource_deletion_state)) {
		$query = sprintf("
				    UPDATE resource
				INNER JOIN collection_resource ON collection_resource.resource = resource.ref AND collection_resource.collection = '%s'
				       SET archive = '%s';
		",
			$collection,
			$resource_deletion_state
		);
		sql_query($query);

		collection_log($collection,'D', '', 'All resources of this collection have been deleted by moving them to state ' . $resource_deletion_state);

		$query = sprintf("
				DELETE FROM collection_resource 
				      WHERE resource IN (%s);
		",
			$resources
		);
		sql_query($query);

	}

	return TRUE;
	}
function update_related_resource($ref,$related,$add=true)
	{	
	if (!is_int($ref) || !is_int($related)){return false;}
	$currentlyrelated=sql_value("select count(resource) value from resource_related where (resource='$ref' and related='$related') or (resource='$related' and related='$ref')",0);  
	if($currentlyrelated!=0 && !$add)
		{
		// Relationship exists and we want to remove
		sql_query("delete from resource_related where (resource='$ref' and related='$related') or (resource='$related' and related='$ref')");  
		}
	elseif ($currentlyrelated==0 && $add)
		{
		// Relationship does not exist and we want to add
		sql_query("insert into resource_related(resource,related) values ('$ref','$related')");
		}
	return true;
	}

function can_share_resource($ref, $access="")
	{
	global $allow_share, $restricted_share, $customgroupaccess,$customuseraccess, $allow_custom_access_share;
	if($access=="" || !isset($customgroupaccess)){$access=get_resource_access($ref);}
	
	if(!$allow_share || $access==2 || ($access==1 && !$restricted_share))
		{return false;} // return false asap
	
	if ($restricted_share){return true;} // If sharing of restricted resources is permitted we should allow sharing whether access is open or restricted
	
	// User is not permitted to share if open access has been specifically granted for an otherwise restrcited resource to the user/group.	
	if(!$allow_custom_access_share && ($customgroupaccess || $customuseraccess)){return false;} 
	
	// Must have open access and sharing is permitted
	return true;	
	}

function delete_resource_custom_access_usergroups($ref)
        {
        # delete all usergroup specific access to resource $ref
        sql_query("delete from resource_custom_access where resource='" . escape_check($ref) . "' and usergroup is not null");
        }


