<?php
/**
 * Functions used (mostly) to generate the content needed for CSV files
 */

/**
* Generates the CSV content of the metadata for resources passed in the array
*
* @param $resources
* @return string
*/
function generateResourcesMetadataCSV(array $resources)
{
    global $lang, $csv_export_add_original_size_url_column;

    $return                 = '';
    $csv_field_headers      = array();
    $resources_fields_data  = array();

    foreach($resources as $resource)
        {
        foreach(get_resource_field_data($resource['ref'], false, true, -1, '' != getval('k', '')) as $field_data)
            {
            $csv_field_headers[$field_data['resource_type_field']] = $field_data['title'];

            $resources_fields_data[$resource['ref']][$field_data['resource_type_field']] = $field_data['value'];
            }

        // Add original size URL column values
        if(!$csv_export_add_original_size_url_column)
            {
            continue;
            }

        /*Provide the original URL only if we have access to the resource or the user group
        doesn't have restricted access to the original size*/
        $access = get_resource_access($resource);
        if(0 != $access || checkperm("T{$resource['resource_type']}_"))
            {
            continue;
            }

        $filepath      = get_resource_path($resource['ref'], true, '', false, $resource['file_extension'], -1, 1, false, '', -1, false);
        $original_link = get_resource_path($resource['ref'], false, '', false, $resource['file_extension'], -1, 1, false, '', -1, false);
        if(file_exists($filepath))
            {
            $resources_fields_data[$resource['ref']]['original_link'] = $original_link;
            }
        }

    // Add original size URL column
    if($csv_export_add_original_size_url_column)
        {
        $csv_field_headers['original_link'] = $lang['collection_download_original'];
        }

    $csv_field_headers = array_unique($csv_field_headers);

    // Header
    $return = '"' . $lang['resourceids'] . '","' . implode('","', $csv_field_headers) . "\"\n";

    // Results
    $csv_row = '';
    foreach($resources_fields_data as $resource_id => $resource_fields)
        {
        // First column will always be Resource ID
        $csv_row = $resource_id . ',';

        // Field values
        foreach($csv_field_headers as $column_header => $column_header_title)
            {
            if(!array_key_exists($column_header, $resource_fields))
                {
                $csv_row .= '"",';
                continue;
                }

            foreach($resource_fields as $field_name => $field_value)
                {
                if($column_header == $field_name)
                    {
                    $csv_row .= '"' . str_replace(array("\\n","\\r","\""),"//",tidylist(i18n_get_translated($field_value))) . '",';
                    }
                }
            }
        $csv_row .= "\n";
        $return  .= $csv_row;
        }

    return $return;
}
