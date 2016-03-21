<?php
#
# video_tracks setup page
#

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

$video_tracks_output_formats=unserialize(base64_decode($video_tracks_output_formats_saved));
$errorfields=array();

// Save column/field mappings here as we can't do it using standard plugin functions
if (getval("submit","")!="" || getval("save","")!="")
	{
	// Decode the mappings variable so we can amend it
	
	$video_tracks_index = $_REQUEST['video_tracks_index'];
	$video_tracks_command = $_REQUEST['video_tracks_command'];
	$video_tracks_extension = $_REQUEST['video_tracks_extension'];

	// Store in a new array
	for ($i=0; $i < count($video_tracks_index); $i++)
		{
		if ($video_tracks_index[$i] <> '')
			{			
			if ($video_tracks_command[$i]!="-1")
				{
				$video_tracks_output_formats_new[$video_tracks_index[$i]]["command"]=$video_tracks_command[$i];
				$video_tracks_output_formats_new[$video_tracks_index[$i]]["extension"]=$video_tracks_extension[$i];
				}	
			}
		}
	// Re-encode the mappings variable so we can post it with the form
	$video_tracks_output_formats = $video_tracks_output_formats_new;
	$video_tracks_output_formats_saved=base64_encode(serialize($video_tracks_output_formats_new));
	}

	
// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'video_tracks';
$plugin_page_heading = $lang['video_tracks_title'];


///////////////////////////////////////////////////////////////////////////////////////////////////
// Now we need to  add all the mappings
///////////////////////////////////////////////////////////////////////////////////////////////////
$video_trackshtml="<div class='Question'>
<h3>" .  $lang['video_tracks_options'] . "</h3>
<table id='video_tracks_optiontable'>
<tr><th>
	<strong>" . $lang['video_tracks_option_name'] . "</strong>
	</th><th>
	<strong>" . $lang['video_tracks_command'] . "</strong>
	</th><th>
	<strong>" . $lang['file_extension_label'] . "</strong>
	</th>
	</tr>";

$fields=sql_query('select * from resource_type_field order by title, name');

foreach ($video_tracks_output_formats as $video_tracks_output_index=>$video_tracks_output_option)
	{
	$rowid = "row" . htmlspecialchars($video_tracks_output_index);
	$video_trackshtml.="<tr id ='" . $rowid . "'><td>
	<input type='text' name='video_tracks_index[]' value='" . $video_tracks_output_index . "' />
	</td><td>
	<input type='text' name='video_tracks_command[]' value='" . $video_tracks_output_option["command"] . "' />
	</td><td>
	<input type='text' name='video_tracks_extension[]' value='" . $video_tracks_output_option["extension"] . "' />
	</td>
	</tr>";
	}
$video_trackshtml.="<tr id ='newrow'><td>
<input type='text' name='video_tracks_index[]' value='' />
</td><td>
<input type='text' name='video_tracks_command[]' value='' />
</td><td>
<input type='text' name='video_tracks_extension[]' value='' />
</td>
</tr>
</table>
<a onclick='addVideo_tracksOptionRow()'>" . $lang["action-add-new"] . "</a>
</div>

<script>
 function addVideo_tracksOptionRow() {
 
            var table = document.getElementById('video_tracks_optiontable');
 
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);
 
            row.innerHTML = document.getElementById('newrow').innerHTML;
        }
</script>
";

$page_def[] = config_add_html("<h2>" . $lang['video_tracks_intro'] . "</h2>");
$page_def[] = config_add_boolean_select('video_tracks_convert_vtt', $lang['video_tracks_convert_vtt']);
$page_def[] = config_add_boolean_select('video_tracks_download_export', $lang['video_tracks_download_export']);
$page_def[] = config_add_text_list_input('video_tracks_permitted_video_extensions', $lang['video_tracks_permitted_video_extensions']);
$page_def[] = config_add_text_list_input('video_tracks_audio_extensions', $lang['video_tracks_audio_extensions']);
$page_def[] = config_add_text_list_input('video_tracks_subtitle_extensions', $lang['video_tracks_subtitle_extensions']);
$page_def[] = config_add_text_input('video_tracks_process_size_limit', $lang['video_tracks_process_size_limit']);
$page_def[] = config_add_text_input('video_tracks_export_folder', $lang['video_tracks_export_folder']);
$page_def[] = config_add_html($video_trackshtml);
$page_def[] =config_add_hidden("video_tracks_output_formats_saved");


// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);

if(!is_writable ($video_tracks_export_folder))
    {
    $errorfields[]="video_tracks_export_folder";   
    }
        
include '../../../include/header.php';

if (count($errorfields)>0)
    {
    echo "<div class=\"PageInformal\">";
    foreach($errorfields as $errorfield)
        {
        echo $lang["error_" . $errorfield] . PHP_EOL;
        ?>
        <script>
        jQuery(document).ready(function(){
            jQuery('#<?php echo $errorfield; ?>').addClass('highlighted');
        });
        </script>
        <?php
        }
    echo "</div>";
    }

config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
