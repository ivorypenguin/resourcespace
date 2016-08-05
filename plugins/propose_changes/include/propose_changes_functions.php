<?php

function save_proposed_changes($ref)
	{
    global $userref, $auto_order_checkbox,$multilingual_text_fields,$languages,$language;

    # Loop through the field data and save (if necessary)
	$errors=array();
	$fields=get_resource_field_data($ref,false);
	$resource_data=get_resource_data($ref);
        
        for ($n=0;$n<count($fields);$n++)
            {
            node_field_options_override($fields[$n]);

            if ($fields[$n]["type"]==2)
                {
                # construct the value from the ticked boxes
                $val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                $options = trim_array($fields[$n]['node_options']);

                for ($m=0;$m<count($options);$m++)
                        {
                        $name=$fields[$n]["ref"] . "_" . md5($options[$m]);
                        if (getval($name,"")=="yes")
                                {
                                if ($val!=",") {$val.=",";}
                                $val.=$options[$m];
                                }
                        }
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
           elseif ($fields[$n]["type"] == 3 || $fields[$n]["type"] == 12)
				{
				$val=getvalescaped("field_" . $fields[$n]["ref"],"");				
				// if it doesn't already start with a comma, add one
				if (substr($val,0,1) != ',')
					{
					$val = ','.$val;
					}
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
            $error=hook("additionalvalcheck", "all", array($fields, $fields[$n]));
            if ($error) 
                {
                global $lang;               
                $errors[$fields[$n]["ref"]]=$error;
                continue;
                }
            if (str_replace("\r\n","\n",$fields[$n]["value"])!== str_replace("\r\n","\n",unescape($val)))
                    {
                    
                    # This value is different from the value we have on record. 
                    # Add this to the proposed changes table for the user                    
                    sql_query("insert into propose_changes_data(resource,user,resource_type_field,value) values('$ref','$userref','" . $fields[$n]["ref"] . "','" . escape_check($val) ."')");
                    
                    }            
            
            }
        return true;
        }
        
function get_proposed_changes($ref, $userid)
	{
        //Get all the changes proposed by a user
        $query = sprintf('
                    SELECT d.value,
                           d.resource_type_field,
                           f.*,
                           f.required AS frequired,
                           f.ref AS fref
                      FROM resource_type_field AS f
                 LEFT JOIN (
                                SELECT *
                                  FROM propose_changes_data
                                 WHERE resource = "%1$s"
                                   AND user = "%2$s"
                           ) AS d ON d.resource_type_field = f.ref AND d.resource = "%1$s"
                 GROUP BY f.ref
                 ORDER BY f.resource_type, f.order_by, f.ref;
            ',
            $ref,
            $userid
        );
        $changes = sql_query($query);

        return $changes;  
        }