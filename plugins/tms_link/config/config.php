<?php
$tms_link_test_mode=false;
$tms_link_email_notify="";
$tms_link_test_count=500;
// Number of resources to retrieve from TMS in each query - can be tweaked for performance
$tms_link_query_chunk_size=50;

// SQL Server connection settings
$tms_link_dsn_name='TMS SQL Server';
$tms_link_user='';
$tms_link_password='';
$tms_link_table_name='';

$tms_link_resource_types=array(12);

// Field to use for storing checksum values 
$tms_link_checksum_field=0;

// Field that is used to store TMS object ID
$tms_link_object_id_field=0;

$tms_link_enable_update_script=true;
$tms_link_script_failure_notify_days=3;

/* The names have not been changed for legacy reasons
* IMPORTANT: $tms_link_text_columns and $tms_link_numeric_columns are now used to distinguish between UTF-16 and UTF-8
* - UTF-16 => $tms_link_text_columns
* - UTF-8 => $tms_link_numeric_columns
*/
$tms_link_text_columns=array("ObjectStatus","Department","Classification","Curator","Cataloguer","ObjectName","SubjectKeywords","Creators","Titles","StylePeriod","CulturalContext","Medium","Geography","CreditLine","Description","RelatedObjects","Inscription","Provenance","CurrLocDisplay","Copyright","Dimensions","Restrictions","CreditLineRepro","ObjRightsType");
$tms_link_numeric_columns=array("ObjectID","ObjectNumber","CuratorRevISODate","Dated","RowChecksum");

$tms_link_field_mappings_saved=base64_encode(serialize(array(
"Dimensions"=>0,
"ObjectStatus"=>0,
"Department"=>0,
"Classification"=>0,
"Curator"=>0,
"Cataloguer"=>0,
"ObjectName"=>0,
"SubjectKeywords"=>0,
"Creators"=>0,
"Titles"=>0,
"StylePeriod"=>0,
"CulturalContext"=>0,
"Medium"=>0,
"Geography"=>0,
"CreditLine"=>0,
"Description"=>0,
"RelatedObjects"=>0,
"Inscription"=>0,
"Provenance"=>0,
"CurrLocDisplay"=>0,
"Copyright"=>0,
"Dimensions"=>0,
"Restrictions"=>0,
"CreditLineRepro"=>0,
"ObjRightsType"=>0,
"ObjectNumber"=>0,
"CuratorRevISODate"=>0,
"Dated"=>0,
"RowChecksum"=>0
)));

$tms_link_log_directory="";
$tms_link_log_expiry=7;

