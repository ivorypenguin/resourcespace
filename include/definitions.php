<?php

// ------------------------- FIELD TYPES -------------------------
$field_types=array(
		0=>"fieldtype-text_box_single_line",
		1=>"fieldtype-text_box_multi-line",
		2=>"fieldtype-check_box_list",
		3=>"fieldtype-drop_down_list",
		4=>"fieldtype-date_and_optional_time",
		5=>"fieldtype-text_box_large_multi-line",
		6=>"fieldtype-expiry_date",
		7=>"fieldtype-category_tree",
		8=>"fieldtype-text_box_formatted_and_ckeditor",
		9=>"fieldtype-dynamic_keywords_list",
		10=>"fieldtype-date",
		11=>"fieldtype-dynamic_tree_in_development",
		12=>"fieldtype-radio_buttons",
		13=>"fieldtype-warning_message"
		);

$FIXED_LIST_FIELD_TYPES = array(2,3,7,9,12);

// ------------------------- LOG_CODE_ -------------------------

// codes used for log entries (including resource and activity logs)

define ('LOG_CODE_ACCESS_CHANGED',		'a');
define ('LOG_CODE_ALTERNATIVE_CREATED',	'b');
define ('LOG_CODE_CREATED',				'c');
define ('LOG_CODE_COPIED',				'C');
define ('LOG_CODE_DOWNLOADED',			'd');
define ('LOG_CODE_EDITED',				'e');
define ('LOG_CODE_EMAILED',				'E');
define ('LOG_CODE_LOGGED_IN',			'l');
define ('LOG_CODE_MULTI_EDITED',		'm');
define ('LOG_CODE_PAYED',				'p');
define ('LOG_CODE_REVERTED_REUPLOADED',	'r');
define ('LOG_CODE_REORDERED',			'R');
define ('LOG_CODE_STATUS_CHANGED',		's');
define ('LOG_CODE_SYSTEM',				'S');
define ('LOG_CODE_TRANSFORMED',			't');
define ('LOG_CODE_UPLOADED',			'u');
define ('LOG_CODE_UNSPECIFIED',			'U');
define ('LOG_CODE_VIEWED',				'v');
define ('LOG_CODE_DELETED',				'x');

// validates LOG_CODE is legal
function LOG_CODE_validate($log_code)
	{
	return in_array($log_code,LOG_CODE_get_all());
	}

// returns all allowable LOG_CODEs
function LOG_CODE_get_all()
	{
	return definitions_get_by_prefix('LOG_CODE');
	}

// used internally
function definitions_get_by_prefix($prefix)
	{
	$return_definitions = array();
	foreach (get_defined_constants() as $key=>$value)
		{
		if (preg_match('/^' . $prefix . '/', $key))
			{
			$return_definitions[$key]=$value;
			}
		}
	return $return_definitions;
	}


// ------------------------- SYSTEM NOTIFICATION TYPES -------------------------
define ('MANAGED_REQUEST',		1);
define ('COLLECTION_REQUEST',	2);
define ('USER_REQUEST',			3);
define ('SUBMITTED_RESOURCE',	4);
define ('SUBMITTED_COLLECTION',	5);

// Advanced search mappings. USed to translate field names to !properties special search codes
$advanced_search_properties=array("media_heightmin"=>"hmin",
                                  "media_heightmax"=>"hmax",
                                  "media_widthmin"=>"wmin",
                                  "media_widthmax"=>"wmax",
                                  "media_filesizemin"=>"fmin",
                                  "media_filesizemax"=>"fmax",
                                  "media_fileextension"=>"fext",
                                  "properties_haspreviewimage"=>"pi",
                                  "properties_contributor"=>"cu"
                                  );
							  

// ------------------------- JOB STATUS / GENERIC STATUS CODES -------------------------
define ('STATUS_DISABLED',				0);
define ('STATUS_ACTIVE',				1);
define ('STATUS_COMPLETE',				2);	
define ('STATUS_ERROR',					5);
					  