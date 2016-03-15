<?php

function unistr_to_ords($str, $encoding = 'UTF-8'){        
    // Turns a string of unicode characters into an array of ordinal values,
    // Even if some of those characters are multibyte.
    $str = mb_convert_encoding($str,"UCS-4BE",$encoding);
    $ords = "";;
    
    // Visit each unicode character
    for($i = 0; $i < mb_strlen($str,"UCS-4BE"); $i++){        
        // Now we have 4 bytes. Find their total
        // numeric value.
        $s2 = mb_substr($str,$i,1,"UCS-4BE");                    
        $val = unpack("N",$s2);            
        $ords.= $val[1];                
    }        
    return($ords);
}

function getEncodingOrder()
   {
   $ary[] = 'UTF-32';
   $ary[] = 'UTF-32BE';
   $ary[] = 'UTF-32LE';
   $ary[] = 'UTF-16';
   $ary[] = 'UTF-16BE';
   $ary[] = 'UTF-16LE';
   $ary[] = 'UTF-8';
   $ary[] = 'ASCII';
   $ary[] = 'ISO-2022-JP';
   $ary[] = 'JIS';
   $ary[] = 'windows-1252';
   $ary[] = 'windows-1251';
   $ary[] = 'UCS-2LE';
   $ary[] = 'SJIS-win';
   $ary[] = 'EUC-JP';
    
   return $ary;
   }

function tms_convert_value($value, $key)
    {
    global $tms_link_numeric_columns, $tms_link_text_columns;

    $encoding = mb_detect_encoding($value, getEncodingOrder(), true);

    // Check if field is defined as UTF-16 or it's not an UTF-8 field
    if(in_array($key, $tms_link_text_columns) || !in_array($key, $tms_link_numeric_columns))
        {
        return mb_convert_encoding($value, 'UTF-8', 'UCS-2LE');
        }

    return $value;
    }


function tms_link_get_tms_data($resource,$tms_object_id="",$resourcechecksum="")
	{
	global $tms_link_dsn_name,$tms_link_user,$tms_link_password, $tms_link_checksum_field, $tms_link_table_name,$tms_link_object_id_field, $tms_link_text_columns, $tms_link_numeric_columns;
	
	$tms_link_columns;
	
	$conn=odbc_connect($tms_link_dsn_name, $tms_link_user, $tms_link_password);
	
	if($conn)
		{
		// Get checksum if if we haven't been passed it
		if($resourcechecksum==""){$resourcechecksum=get_data_by_field($resource, $tms_link_checksum_field);}
		//echo "Checksum=" . $resourcechecksum;
		
		// Get TMS if if we haven't been passed it
		if($tms_object_id==""){$tms_object_id=get_data_by_field($resource, $tms_link_object_id_field);}	
		
		if($tms_object_id==""){return false;} // We don't have any ID to get data for
		
		if(is_array($tms_object_id))
			{
			$conditionsql = " where ObjectID in ('" . implode("','", $tms_object_id) . "')";
			}		
		else
			{
			$conditionsql = " where ObjectID ='" . $tms_object_id . "'";
			}
		
		
		//echo "TMS Object ID =" . $tms_object_id;
				
		//$tmssql="SELECT * FROM " . $tms_link_table_name . " where ObjectID ='" . $tms_object_id . "';";
		
		// Add normal value fields
		$columnsql = implode(", ", $tms_link_numeric_columns);
		
		// Add SQL to get back text fields as VARBINARY(MAX) so we can sort out encoding later
		foreach ($tms_link_text_columns as $tms_link_text_column)
			{
			$columnsql.=", CAST (" . $tms_link_text_column . " AS VARBINARY(MAX)) " . $tms_link_text_column;
			}
		
		
		$tmssql = "SELECT " . $columnsql . " FROM " . $tms_link_table_name . $conditionsql . " ;";
		
		//exit($tmssql);
		
		// Execute the query to get the data from TMS
		$tmsresultset = odbc_exec($conn,$tmssql);
		
		$resultcount=odbc_num_rows ($tmsresultset);
		if($resultcount==0){global $lang;return $lang["tms_link_no_tms_data"];}
		
		$convertedtmsdata=array();
		for ($r=1;$r<=$resultcount;$r++)
			{		
			$tmsdata=odbc_fetch_array ($tmsresultset,$r);
			
			if(is_array($tms_object_id))
				{
				foreach($tmsdata as $key=>$value)
					{
					$convertedtmsdata[$r][$key]=tms_convert_value($value, $key);
					}
				}
			else
				{
				foreach($tmsdata as $key=>$value)
					{
					$convertedtmsdata[$key]=tms_convert_value($value, $key);
					}
				}
				
			}
			//exit(print_r($convertedtmsdata));
			return $convertedtmsdata;
		}
	else
		{
		
		$error=odbc_errormsg();
		exit($error);
		return $error;
		}
		
	}

function tms_link_get_tms_resources()
	{
	global $tms_link_checksum_field,$tms_link_object_id_field, $tms_test_count, $tms_link_resource_types;
	
	
	$tms_resources=sql_query("select rd.resource as resource, rd.value as objectid, rd2.value as checksum from resource_data rd left join resource_data rd2 on rd2.resource=rd.resource and rd2.resource_type_field='" . $tms_link_checksum_field . "' WHERE rd.resource>0 and rd.resource_type_field='" . $tms_link_object_id_field . "' order by rd.resource");
		
	return $tms_resources;	
	
	}
	
	
	
	
function tms_link_test()
	{
	global $tms_link_dsn_name,$tms_link_user,$tms_link_password, $tms_link_checksum_field, $tms_link_table_name,$tms_link_object_id_field, $tms_link_text_columns, $tms_link_numeric_columns;
	
	$tms_link_columns;
	
	$conn=odbc_connect($tms_link_dsn_name, $tms_link_user, $tms_link_password);
	
	if($conn)
		{	
		
				
		// Add normal value fields
		$columnsql = implode(", ", $tms_link_numeric_columns);
		
		// Add SQL to get back text fields as VARBINARY(MAX) so we can sort out encoding later
		foreach ($tms_link_text_columns as $tms_link_text_column)
			{
			$columnsql.=", CAST (" . $tms_link_text_column . " AS VARBINARY(MAX)) " . $tms_link_text_column;
			}
		
		
		$tmssql = "SELECT TOP 10 * FROM " . $tms_link_table_name . " ;";
		
		// Execute the query to get the data from TMS
		$tmsresultset = odbc_exec($conn,$tmssql);
		
		$resultcount=odbc_num_rows ($tmsresultset);
		if($resultcount==0){global $lang;return $lang["tms_link_no_tms_data"];}
		
		$convertedtmsdata=array();
		for ($r=1;$r<=$resultcount;$r++)
			{		
			$tmsdata=odbc_fetch_array ($tmsresultset,$r);
			
		
			foreach($tmsdata as $key=>$value)
				{				
				$convertedtmsdata[$key]=$value;
				}
				
				
			}
			//exit(print_r($convertedtmsdata));
			return $convertedtmsdata;
		}
	else
		{
		
		$error=odbc_errormsg();
		exit($error);
		return $error;
		}
		
	}	
	
	
	
	
	
	