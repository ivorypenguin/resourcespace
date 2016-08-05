<?php
#
# tms_link setup page
#

include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include_once '../../../include/general.php';


$tms_link_field_mappings=unserialize(base64_decode($tms_link_field_mappings_saved));

// Save column/field mappings here as we can't do it using standard plugin functions
if (getval("submit","")!="" || getval("save","")!="")
	{
	// Decode the mappings variable so we can amend it
	
	$tmscolumns = $_REQUEST['tms_column'];
	$rsfields = $_REQUEST['rs_field'];

	// Store in a new array
	for ($i=0; $i < count($tmscolumns); $i++)
		{
		if ($tmscolumns[$i] <> '')
			{			
			if ($rsfields[$i]!="-1")
				{
				$tms_link_field_mappings_new[$tmscolumns[$i]]=$rsfields[$i];
				}			
			
			//$query = "replace into simpleldap_groupmap (ldapgroup,rsgroup,priority) values ('" . escape_check($ldapgroups[$i]) . "','" . $rsgroups[$i] . "' ,'" . $priority[$i] ."')";
			//sql_query($query);		
			}
		}
	// Re-encode the mappings variable so we can post it with the form
	$tms_link_field_mappings = $tms_link_field_mappings_new;
	$tms_link_field_mappings_saved=base64_encode(serialize($tms_link_field_mappings_new));
	}

$scriptlastran=sql_value("select value from sysvars where name='last_tms_import'","");

global $baseurl, $tms_link_field_mappings_saved;
// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'tms_link';
$plugin_page_heading = $lang['tms_link_configuration'];
// Build the $page_def array of descriptions of each configuration variable the plugin uses.

$page_def[] = config_add_section_header($lang['tms_link_database_setup']);

$page_def[] = config_add_text_input('tms_link_dsn_name',$lang['tms_link_dsn_name']);
$page_def[] = config_add_text_input('tms_link_table_name',$lang['tms_link_table_name']);
$page_def[] = config_add_text_input('tms_link_user',$lang['tms_link_user']);
$page_def[] = config_add_text_input('tms_link_password',$lang['tms_link_password'],true);
$page_def[] = config_add_multi_rtype_select("tms_link_resource_types",$lang['tms_link_resource_types']);
$page_def[] = config_add_text_input('tms_link_email_notify',$lang['tms_link_email_notify']);

$page_def[] = config_add_section_header($lang['tms_link_enable_update_script_info']);
$tmsscriptstatushtml = $lang["tms_link_last_run_date"] . (($scriptlastran!="")?date("l F jS Y @ H:i:s",strtotime($scriptlastran)):$lang["status-never"]) . "<br /><br />";
$page_def[] = config_add_html($tmsscriptstatushtml);
$page_def[] = config_add_boolean_select('tms_link_enable_update_script', $lang['tms_link_enable_update_script']);


$page_def[] = config_add_section_header($lang['tms_link_performance_options']);



$page_def[] = config_add_text_input('tms_link_script_failure_notify_days',$lang['tms_link_script_failure_notify_days']);
$page_def[] = config_add_text_input('tms_link_query_chunk_size',$lang['tms_link_query_chunk_size']);
$page_def[] = config_add_boolean_select('tms_link_test_mode', $lang['tms_link_test_mode']);
$page_def[] = config_add_text_input('tms_link_test_count',$lang['tms_link_test_count']);

$page_def[] = config_add_text_input('tms_link_log_directory',$lang['tms_link_log_directory']);
$page_def[] = config_add_text_input('tms_link_log_expiry',$lang['tms_link_log_expiry']);

$page_def[] = config_add_section_header($lang['tms_link_metadata_setup']);
$page_def[] = config_add_single_ftype_select('tms_link_checksum_field',$lang["tms_link_checksum_field"]);
$page_def[] = config_add_single_ftype_select('tms_link_object_id_field',$lang["tms_link_object_id_field"]);

//$page_def[] = config_add_hidden($tms_link_field_mappings);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now we need to  add all the mappings
///////////////////////////////////////////////////////////////////////////////////////////////////
$tmsmaphtml="<div class='Question'>
<h3>" .  $lang['tms_link_field_mappings'] . "</h3>
<table id='tmsmappingtable'>
<tr><th>
	<strong>" . $lang['tms_link_column_name'] . "</strong>
	</th><th>
	<strong>" . $lang['tms_link_resourcespace_field'] . "</strong>
	</th>
	</tr>";

$fields=sql_query('select * from resource_type_field order by title, name');

foreach ($tms_link_field_mappings as $tms_column=>$fieldid)
	{
	$rowid = "row" . htmlspecialchars($tms_column);
	$tmsmaphtml.="<tr id ='" . $rowid . "'><td>
	<input type='text' name='tms_column[]' value='" . $tms_column . "' />
	</td><td>
	<select name='rs_field[]' style='width:300px'>
	<option value=''"  . (($fieldid==0)?" selected":"") . "></option>
	<option value='-1'>--- " . $lang['action-delete']  . "---</option>";
	
	
	foreach($fields as $field)
		{
		$tmsmaphtml.="<option value='" . $field['ref'] . "' " .  (($fieldid==$field['ref'])?' selected':'') . ">" . lang_or_i18n_get_translated($field['title'],'fieldtitle-') . "</option>";
		}
		
	$tmsmaphtml.="</select>	
	</td>
	</tr>";
	}
$tmsmaphtml.="<tr id ='newrow'><td>
<input type='text' name='tms_column[]' value='' />
</td><td>
<select name='rs_field[]' style='width:300px'>
<option value='' selected></option>";


foreach($fields as $field)
	{
	$tmsmaphtml.="<option value='" . $field['ref'] . "' >" . lang_or_i18n_get_translated($field['title'],'fieldtitle-') . "</option>";
	}
	
$tmsmaphtml.="</select>	
</td>
</tr>
</table>
<a onclick='addTMSMappingRow()'>" . $lang['tms_link_add_mapping'] . "</a>
</div>

<script>
 function addTMSMappingRow() {
 
            var table = document.getElementById('tmsmappingtable');
 
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);
 
            row.innerHTML = document.getElementById('newrow').innerHTML;
        }
</script>
";

$page_def[] = config_add_html($tmsmaphtml);

$page_def[] = config_add_section_header($lang['tms_link_colum_type_required']);

$page_def[] = config_add_text_list_input('tms_link_text_columns', $lang["tms_link_text_columns"]);
$page_def[] = config_add_text_list_input('tms_link_numeric_columns', $lang["tms_link_numeric_columns"]);

$page_def[] =config_add_hidden("tms_link_field_mappings_saved");
		

// End of mappings section
///////////////////////////////////////////////////////////////////////////////////////////////////

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);

if(trim($tms_link_log_directory)!="" && (getval("save","")!="" || getval("submit","")!=""))
	{
	if (!is_dir($tms_link_log_directory))
		{
		@mkdir($tms_link_log_directory, 0755, true);
		if (!is_dir($tms_link_log_directory))
			{
			$errortext = 'Invalid log directory: ' . htmlspecialchars($tms_link_log_directory);
			}
		}
	else
		{
		$logfilepath=$tms_link_log_directory . DIRECTORY_SEPARATOR . "tms_import_log_test.log";
		$logfile=@fopen($logfilepath,a);
		if(!file_exists($logfilepath))
			{
			$errortext = 'Unable to create log file in directory: ' . htmlspecialchars($tms_link_log_directory);			
			}
		else
			{
			fclose($logfile);
			unlink($logfilepath);
			}
		}
	}
	
	
include '../../../include/header.php';
if(isset($errortext))
	{
	echo "<div class=\"PageInformal\">" . $errortext . "</div>";
	}
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
