<?php
include '../include/db.php';
include_once '../include/general.php';

$k   = getvalescaped('k', '');
$ref = getvalescaped('ref', '', true);

// External access support (authenticate only if no key provided, or if invalid access key provided)
if('' == $k || !check_access_key($ref, $k))
    {
    include '../include/authenticate.php';
    }

include_once '../include/resource_functions.php';
include_once '../include/collections_functions.php';
include_once '../include/pdf_functions.php';

$resource = get_resource_data($ref);

// fetch the current search (for finding similar matches)
$search   = getvalescaped('search', '');
$order_by = getvalescaped('order_by', 'relevance');
$offset   = getvalescaped('offset', 0, true);
$restypes = getvalescaped('restypes', '');
if(strpos($search, '!') !== false)
    {
    $restypes='';
    }

$archive      = getvalescaped('archive', 0, true);
$starsearch   = getvalescaped('starsearch', '');
$default_sort_direction = 'DESC';
if(substr($order_by, 0, 5) == 'field')
    {
    $default_sort_direction = 'ASC';
    }

$sort               = getval('sort', $default_sort_direction);
$metadata           = get_resource_field_data($ref, false, true, -1, getval('k', '') != ''); 
$filename           = $ref;
$download           = getval('download', '') != '';
$download_file_type = getval('fileType_option', '');
$language           = getval('language', 'en');
$data_only          = 'true' === trim(getval('data_only', ''));
$pdf_template       = getvalescaped('pdf_template', '');

// Process text file download
if ($download && $download_file_type == 'text')
	{
	header("Content-type: application/octet-stream");
	header("Content-disposition: attachment; filename=" . $lang["metadata"]."_". $filename . ".txt");

	foreach ($metadata as $metadata_entry) // Go through each entry
		{
		if (!empty($metadata_entry['value']))
			{
			// This is the field title - the function got this by joining to the resource_type_field in the sql query
			echo $metadata_entry['title'] . ': ';
			// This is the value for the field from the resource_data table
			echo tidylist(i18n_get_translated($metadata_entry['value'])) . "\r\n";
			}
		}

	ob_flush();
	exit();
	}

// Process PDF file download
if($download && $download_file_type === 'pdf') {
	$html2pdf_path = dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php';
	ob_start();
	if(!file_exists($html2pdf_path)) {
		die('html2pdf class file is missing. Please make sure you have it under lib folder.');
	}
	require_once($html2pdf_path);

	$logo_src_path = $baseurl . '/gfx/titles/title.png';
	if(isset($metadata_download_pdf_logo) && trim($metadata_download_pdf_logo) != '') {
		$logo_src_path = $baseurl . $metadata_download_pdf_logo;
	}

	$PDF_filename = $lang['metadata'] .'_' . $filename . '.pdf';

	$content = '';

	?>
	<!-- Start structure of PDF file in HTML -->
	<page backtop="25mm" backbottom="10mm" backleft="5mm" backright="5mm">
		<page_header>
			<table cellspacing="0" style="width: 100%;">
		        <tr>
		            <td style="width: 75%;"><h1><?php echo $metadata_download_header_title; ?></h1></td>
		            <td style="width: 25%; <?php if(!isset($metadata_download_pdf_logo)) { ?> background-color: #383838; <?php } ?>" align=right>
		                <img style="height: 40px; max-width: 100%" src="<?php echo $logo_src_path; ?>" alt="Logo" >
		            </td>
		        </tr>
		    </table>
		</page_header>
		<page_footer>
			<table style="width: 100%;">
			<?php
			if(isset($metadata_download_footer_text) && trim($metadata_download_footer_text) != '')
				{
				?>
				<tr>
					<td colspan="3"><?php echo $metadata_download_footer_text; ?></td>
				</tr>
				<?php
				}
				?>
				<tr>
					<td style="text-align: left; width: 33%"><?php echo $PDF_filename; ?></td>
					<td style="text-align: center; width: 34%">page [[page_cu]]/[[page_nb]]</td>
					<td style="text-align: right; width: 33%"><?php echo date('d/m/Y'); ?></td>
				</tr>
			</table>
		</page_footer>
		<!-- Real content starts here -->
		<h2><?php echo $lang['metadata-pdf-title'] . ' ' . $ref; ?></h2>
		<table style="width: 90%;" align="center" cellspacing="15">
			<tbody>
			<?php
			foreach ($metadata as $metadata_entry)
			{
			$metadatavalue=trim(tidylist(i18n_get_translated($metadata_entry['value'])));
			if(!empty($metadatavalue))
				{
				?>
					<tr>
						<td valign="top" style="text-align: left;"><b><?php echo $metadata_entry['title']; ?></b></td>
						<td style="width: 2%;"></td>
						<td style="width: 70%; text-align: left;"><?php echo $metadatavalue; ?></td>
					</tr>
				<?php
				}
			}
			?>
			</tbody>
		</table>


	</page>
	<!-- End of structure of PDF file in HTML -->
	<?php

	$content = ob_get_clean();

	$html2pdf = new HTML2PDF('P', 'A4', $language);
	$html2pdf->WriteHTML($content);
	$html2pdf->Output($PDF_filename);
}

/*
Data only PDFs generation
These PDFs will be based on templates found on the server which will be interpreted and then rendered
*/
if($download && $data_only)
    {
    $pdf_template_path = get_pdf_template_path($resource['resource_type'], $pdf_template);
    $PDF_filename      = 'data_only_resource_' . $ref . '.pdf';

    // Go through fields and decide which ones we add to the template
    $placeholders = array(
        'resource_type_name' => get_resource_type_name($resource['resource_type'])
    );
    foreach($metadata as $metadata_field)
        {
        $metadata_field_value = trim(tidylist(i18n_get_translated($metadata_field['value'])));

        // Skip if empty
        if('' == $metadata_field_value)
            {
            continue;
            }

        $placeholders['metadatafield-' . $metadata_field['ref'] . ':title'] = $metadata_field['title'];
        $placeholders['metadatafield-' . $metadata_field['ref'] . ':value'] = $metadata_field_value;
        }

    if(!generate_pdf($pdf_template_path, $PDF_filename, $placeholders))
        {
        trigger_error('ResourceSpace could not generate PDF for data only type!');
        }
    }

include "../include/header.php";
?>

<body>
	<div class="BasicsBox">
	<p><a href="<?php echo $baseurl_short; ?>pages/view.php?ref=<?php echo urlencode($ref); ?>&search=<?php echo urlencode($search); ?>&offset=<?php echo urlencode($offset); ?>&order_by=<?php echo urlencode($order_by); ?>&sort=<?php echo urlencode($sort); ?>&archive=<?php echo urlencode($archive); ?>"  onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]; ?></a></p>

	<h1><?php echo $lang["downloadingmetadata"]?></h1>

	<p><?php echo $lang["file-contains-metadata"]?></p>

	<form id="metadataDownloadForm" name="metadataDownloadForm" method=post action="<?php echo $baseurl_short; ?>pages/metadata_download.php" >
		<input name="ref" type="hidden" value="<?php echo $ref; ?>">
		<div class="Question" id="fileType">
			<label for="fileType_option">Download file type</label>
			<select id="fileType_option" class="stdwidth" name="fileType_option">
				<option value="">Please select...</option>
				<option value="text">Text</option>
				<option value="pdf">PDF</option>
			</select>
			<div class="clearerleft"></div>
		</div>

		<div class="QuestionSubmit">
			<label for="buttons"></label>	
			<input name="download" type="submit" value="<?php echo $lang['download']; ?>" />
		</div>
	</form>

	</div>
</body>

<?php
include "../include/footer.php";
?>
