<?php

function csv_upload_process($filename,&$meta,$resource_types,&$messages,$override="",$max_error_count=100,$processcsv=false)
	{
	// echo "csv_upload_process(" . $filename . ", Resource types: ";
	// foreach($resource_types as $restype) {echo $restype. ", ";}
	// echo "Override:" . $override . "<br>";
	// if($processcsv){echo "Processing CSV file<br>";}
	
	$file=fopen($filename,'r');
	$line_count=0;

	if (($header = fgetcsv($file))==false)
		{
		array_push($messages, "No header found");
		fclose($file);
		return false;		
		}			
		
	for($i=0; $i<count($header); $i++)
		{
		$header[$i]=strtoupper($header[$i]);
		}
				
	# ----- start of header row checks -----

	$resource_types_allowed=array();
	$resource_type_filter=getvalescaped("resource_type","",true);
	if(getvalescaped("add_to_collection","")!="")
		{
		include dirname(__FILE__)."/../../../include/collections_functions.php";
		global $usercollection;
		$add_to_collection=true;
		}
	else
		{$add_to_collection=false;}
	

	foreach (array_keys($resource_types) as $resource_type)		// check what fields are supported by comparing header fields with required fields per resource_type
		{
		if (!isset($meta[$resource_type])) continue;
		$missing_fields=array();
		foreach ($meta[$resource_type] as $field_name=>$field_attributes)
			{
			if ($override!="" && $resource_type_filter!=$resource_type && $resource_type!=0)
				{
				continue;
				}
			if ($field_attributes['required'] && array_search($field_name, $header)===false)
				{			
				$meta[$resource_type][$field_name]['missing']=true;
				array_push($missing_fields, $meta[$resource_type][$field_name]['nicename']);
				}
			}
			
			//if (count($missing_fields)==0 || $override==0 || ($override=="" || ($override==0 && $resource_type==$resource_type_filter)))
			if ($override==0 || (count($missing_fields)==0 && ($override=="" || $resource_type==$resource_type_filter)))
				{
				array_push($messages,"Info: Found correct field headers for resource_type {$resource_type}({$resource_types[$resource_type]})");
				array_push($resource_types_allowed,$resource_type);	
				}
			else
				{
				array_push($messages,"Warning: resource_type {$resource_type}({$resource_types[$resource_type]}) has missing field headers (" . implode(",",$missing_fields) . ") and will be ignored");
				}
		}
		
	if ($override!="" && (array_search($resource_type_filter,$resource_types_allowed)===false))
		{
		array_push($messages, "Error: override resource_type {$resource_type_filter}({$resource_types[$resource_type_filter]}) not found or headers are incomplete");
		fclose($file);
		return false;
		}
	else if ($override!="")
		{
		array_push ($messages, "Info: Override resource_type {$resource_type_filter}({$resource_types[$resource_type_filter]}) is valid");
		}
	
	if (count($header)==count(array_unique($header)))
		{
		array_push($messages,"Info: No duplicate header fields found");
		}
	else
		{
		array_push($messages,"Error: duplicate header fields found");
		fclose($file);
		return false;		
		}
	
		
	# ----- end of header row checks, process each of the rows checking data -----
	$resource_type_index=array_search("RESOURCE_TYPE",$header);		// index of column that contains the resource type
	
	$error_count=0;
	
	echo "Processing " . count($header) . " columns<br>";
	
				
	while ((($line=fgetcsv($file))!==false) && $error_count<$max_error_count)
		{
		
		$line_count++;
	
		if (!$processcsv && count($line)!=count($header))	// check that the current row has the correct number of columns
			{
			
			array_push ($messages,"Error: Incorrect number of columns(" . count($line) . ") found on line " . $line_count . " (should be " . count($header) . ")");
			$error_count++;
			continue;
			}
		
		// important! this is where the override happens
		if($resource_type_index!==false && $override!=1)
			{
			$resource_type= $line[$resource_type_index];
			if($override===0 && $resource_type_filter!=$resource_type){continue;} // User has selected to only import a specific resource type
			}
		else
			{$resource_type=$resource_type_filter;} 	
	
		//echo "Resource type: " . $resource_type . "<br>";
		if (array_search($resource_type,$resource_types_allowed)===false)		// continue to the next line if this type is not allowed or valid.
			{
			
			if($processcsv)	{array_push($messages, "Skipping resource type " . $resource_type );}
			continue;		
			}
		
		if($processcsv)	
			{
			// Create the new resource
			$newref=create_resource($resource_type);
			array_push ($messages,"Created new resource: #" . $newref . " (" . $resource_types[$resource_type] . ")");
			
			if($add_to_collection)
				{add_resource_to_collection($newref,$usercollection);}
			}
			
		$cell_count=-1;		
		
		global $additional_archive_states;
		$valid_archive_states=array_merge (array(-2,-1,0,1,2,3),$additional_archive_states);
					
		
		// Now process the actual data
		
		foreach ($header as $field_name)	
			{			
			if($field_name=="RESOURCE_TYPE"){$cell_count++;continue;}							
			
			//echo "Getting data for " . $field_name . "<br>";
			$cell_count++;
			$cell_value=trim($line[$cell_count]);		// important! we trim values, as options may contain a space after the comma
			//echo "Found value for " . $field_name . ": " . $cell_value . "<br>";
			if($field_name=="ACCESS" && $processcsv)
				{
				//echo "Checking access<br>";
				$selectedaccess=(in_array(getvalescaped("access","",true),array(0,1,2))) ? getvalescaped("access","",true) : "default"; // Must be a valid access value						
				if($selectedaccess=="default"){continue 2;} // Ignore this and the system will use default				
				$cellaccess=(in_array($cell_value,array(0,1,2))) ? $cell_value : ""; // value from CSV
				$accessaction=getvalescaped("access_action","",true); // Do we always override or only use the user selected value if missing or invalid CSV value
				
				if($accessaction==2 || $cellaccess==""){$access=$selectedaccess;} // Override or missing, use the user selected value
				else
					{$access=$cellaccess;} // use the cell value
				
				//echo "Updating the resource access: " . $access . "<br>";
				sql_query("update resource set access='$access' where ref='$newref'");
				
				continue;
				}
			if($field_name=="STATUS" && $processcsv)
				{
				//echo "Checking status<br>";
				$selectedarchivestatus=(in_array(getvalescaped("status","",true),$valid_archive_states)) ? getvalescaped("status","",true) : "default"; // Must be a valid status value						
				if($selectedarchivestatus=="default"){continue 2;} // Ignore this and the system will use default				
				$cellarchivestatus=(in_array($cell_value,$valid_archive_states)) ? $cell_value : ""; // value from CSV
				$statusaction=getvalescaped("status_action","",true); // Do we always override or only use the user selected value if missing or invalid CSV value
				
				if($statusaction==2 || $cellarchivestatus==""){$status=$selectedarchivestatus;} // Override or missing, use the user selected value
				else
					{$status=$cellarchivestatus;} // use the cell value
				
				//echo "Updating the resource archive status: " . $status . "<br>";
				update_archive_status($newref,$status);
				continue;
				}
				
			
			if (!isset($meta[$resource_type][$field_name])) // field name not found (and is not required for this type) so skip to the next one
				{
				if(isset($meta[0][$field_name])) // This maps to a global field, not a resource type specific one
					{
					$field_resource_type=0;
					}
				else
					{
					//echo "Field not found : " . $field_name . "<br>";
					continue;
					}
				}
			else
				{
				$field_resource_type=$resource_type;
				}
			
			if(!($field_name=="ACCESS" || $field_name=="RESOURCE_TYPE" || $field_name=="STATUS"))
				{
				// Check for multiple options
				if(strpos($cell_value,",")>0 && count($meta[$field_resource_type][$field_name]['options'])>0 && !in_array($meta[$field_resource_type][$field_name]['type'],array(3,12))) // cell value may be a series of values, but not for radio or drop down types
						{
						$cell_values=explode(",",$cell_value);
						}
					else
						{
						// Make single value into a dummy array
						$cell_values=array($cell_value);
						}
				$update_dynamic_field=false;
				
				
				if ($meta[$field_resource_type][$field_name]['required'])		// this field is required
					{
					if ($cell_value==null or $cell_value=="")		// this field is empty
							{
							array_push($messages, "Error: Empty value for \"{$field_name}\" required field not allowed - found on line {$line_count}");
							$error_count++;
							continue;
							}
					foreach($cell_values as $cell_actual_value)
						{
						if (count($meta[$field_resource_type][$field_name]['options'])>0 && (array_search($cell_actual_value,$meta[$field_resource_type][$field_name]['options'])===false))	// there are options but value does not match any of them
							{
							if($meta[$field_resource_type][$field_name]['type']==9)
								{
								// Need to add to options table
								$meta[$field_resource_type][$field_name]['options'][]=trim($cell_actual_value);
								$update_dynamic_field=true;
								}
							else
								{
								array_push($messages, "Error: Value \"{$cell_actual_value}\" not found in lookup for \"{$field_name}\" required field - found on line {$line_count}");					
								$error_count++;
								continue;
								}
							}							
						}
					}
				else	// field is not required
					{
					if ($cell_value==null or $cell_value=="")		// a value wasn't specified for non-required field so move on
						{
						continue;
						}		
					foreach($cell_values as $cell_actual_value)
						{		
						if (count($meta[$field_resource_type][$field_name]['options'])>0 && array_search(trim($cell_actual_value),$meta[$field_resource_type][$field_name]['options'])===false) // there are options but value does not match any of them
							{
							if($meta[$field_resource_type][$field_name]['type']==9)
								{
								// Need to add to options table
								$meta[$field_resource_type][$field_name]['options'][]=trim($cell_actual_value);						
								$update_dynamic_field=true;
								array_push($messages,"Adding option for field " . $meta[$field_resource_type][$field_name]['remote_ref'] . ": " . $cell_actual_value);
								}
							else
								{
								array_push($messages, "Error: Value \"{$cell_actual_value}\" not found in lookup for \"{$field_name}\" field - found on line {$line_count}");
								$error_count++;
								continue;
								}		
							}
						}
					}				
							
				if($processcsv)	
					{	
					$cell_value = mb_convert_encoding($cell_value, "UTF-8");
					// Prefix value with comma as this is required for indexing and rendering selected options
					if (in_array($meta[$field_resource_type][$field_name]['type'], array(2,3,7,9,12)) && substr($cell_value,0,1) <> ',')
						{
						$cell_value = ','.$cell_value;
						}
					update_field($newref,$meta[$field_resource_type][$field_name]['remote_ref'],$cell_value);
					
					if($meta[$field_resource_type][$field_name]['type']==9 && $update_dynamic_field) 
						{
						debug("updating dynamic field options for field " . $field_name);

                        /*
                        sql_query("update resource_type_field set options='," . escape_check(implode(",",$meta[$field_resource_type][$field_name]['options'])) .
                            "' where ref='" . $meta[$field_resource_type][$field_name]['remote_ref'] .  "'");
                        */

                        foreach ($meta[$field_resource_type][$field_name]['options'] as $option)
                            {
                            set_node(null,$meta[$field_resource_type][$field_name]['remote_ref'],$option,null,null);
                            }

                        }
					}
				}
				
		ob_flush();	
			}	// end of cell loop
		
		
		// Set archive state if no header found in CSV
		if($processcsv && !in_array("STATUS",$header)) // We don't have a value but we still need to process the selected value
			{
			$selectedarchivestatus=(in_array(getvalescaped("status",""),$valid_archive_states)) ? getvalescaped("status","") : "default"; // Must be a valid status value						
						
			if($selectedarchivestatus!="default")
				{
				update_archive_status($newref,$selectedarchivestatus);
				}
			}
				
		// Set access if no header found in CSV
		if($processcsv && !in_array("ACCESS",$header)) // We don't have a value but we still need to process the selected value
			{
			$selectedaccess=(in_array(getvalescaped("access","",true),array(0,1,2))) ? getvalescaped("access","",true) : "default"; // Must be a valid access value						
			if($selectedaccess!="default")
				{
				sql_query("update resource set access='$selectedaccess' where ref='$newref'");
				}
			}
		
		}  // end of loop through lines
	
	fclose($file);

    // add an error if there are no lines of data to process (i.e. just the header)
	if (0 == $line_count && !$processcsv)
		{
		array_push($messages,"Error: No lines of data found in file");		
		}

	if ($error_count>0)
		{
		if ($error_count==$max_error_count)
			{
			array_push($messages,"Warning: Showing first {$max_error_count} data validation errors only - more may exist");
			}
		return false;		
		}
	
	array_push($messages,"Info: data successfully validated");
		
	return true;
}
