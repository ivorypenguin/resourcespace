<?php
/**
* Returns the path to a pdf template
*
* @param  string  $resource_type  ID of the resource type
* @param  string  $template_name  Known template name already found in the array
*
* @return string
*/
function get_pdf_template_path($resource_type, $template_name = '')
    {
    global $storagedir, $pdf_resource_type_templates;

    $templates     = $pdf_resource_type_templates[$resource_type];
    $template      = '';

    if(
        !array_key_exists($resource_type, $pdf_resource_type_templates) ||
        (array_key_exists($resource_type, $pdf_resource_type_templates) && empty($templates))
    )
        {
        trigger_error('There are no PDF templates set for resource type "' . $resource_type . '"');
        }

    // Client code wants a specific template name but there isn't one
    if('' !== $template_name && !in_array($template_name, $templates))
        {
        trigger_error('PDF template "' . $template_name . '" could not be found in $pdf_resource_type_templates');
        }

    // Client code wants a specific template name
    if('' !== $template_name && in_array($template_name, $templates))
        {
        $template_array_key = array_search($template_name, $templates);
        if(false !== $template_array_key)
            {
            $template = $templates[$template_array_key];
            }
        }

    // Provide a default one if template name is empty
    if('' === $template && '' === $template_name)
        {
        $template = $templates[0];
        }

    return $storagedir . '/system/pdf_templates/' . $template . '.html';
    }


/**
* Takes an HTML template suitable for HTML2PDF library and generates a PDF file if successfull
*
* @param  string   $html_template_path  HTML template path
* @param  string   $filename            The file name of the generated PDF file. If this is an actual path,
*                                       and $save_on_server = true, it will be save on the server
* @param  array    $bind_placeholders   A map of all the values that are meant to replace any 
*                                       placeholders found in the HTML template
* @param  boolean  $save_on_server      If true, PDF file will be saved to the filename path
*
* @return boolean
*/
function generate_pdf($html_template_path, $filename, array $bind_placeholders = array(), $save_on_server = false)
    {
    global $baseurl, $baseurl_short, $storagedir;

    $html2pdf_path = dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php';
    if(!file_exists($html2pdf_path))
        {
        trigger_error('html2pdf class file is missing. Please make sure you have it under lib folder!');
        }
    require_once($html2pdf_path);

    // Do we have a physical HTML template
    if(!file_exists($html_template_path))
        {
        trigger_error('File "' . $html_template_path . '" does not exist!');
        }

    $html = file_get_contents($html_template_path);
    if(false === $html)
        {
        return false;
        }

    // General placeholders available to HTML templates
    $general_params = array(
        'baseurl'       => $baseurl,
        'baseurl_short' => $baseurl_short,
        'filestore'     => $storagedir,
        'filename'      => (!$save_on_server ? $filename : basename($filename)),
        'date'          => date('d/m/Y'),
    );
    $bind_params = array_merge($general_params, $bind_placeholders);

    foreach($bind_params as $param => $param_value)
        {
        // Bind [%param%] placeholders to their values
        $html = str_replace('[%' . $param . '%]', $param_value, $html);
        
        // replace \r\n with <br>. This is how they do it at the moment at html2pdf.fr
        $html = str_replace("\r\n", '<br>', $html);
        }

    // Handle [%if var is set%] and [%endif%] statements
    preg_match_all('/\[%if (.*?) is set%\]/', $html, $if_isset_matches);
    foreach($if_isset_matches[0] as $if_isset_match)
        {
        $remove_placeholder_elements = array('[%if ', ' is set%]');
        $var_name = str_replace($remove_placeholder_elements, '', $if_isset_match);

        $if_isset_match_position = strpos($html, $if_isset_match);
        $endif_position          = strpos($html, '[%endif%]', $if_isset_match_position);
        $substr_lenght           = $endif_position - $if_isset_match_position;

        $substr_html_one   = substr($html, 0, $if_isset_match_position);
        $substr_html_two   = substr($html, $if_isset_match_position, $substr_lenght + 9);
        $substr_html_three = substr($html, $endif_position + 9);

        if(!array_key_exists($var_name, $bind_params))
            {
            $html = $substr_html_one . $substr_html_three;

            continue;
            }

        $substr_html_two = str_replace(array($if_isset_match, '[%endif%]'), '', $substr_html_two);

        $html = $substr_html_one . $substr_html_two . $substr_html_three;
        }

    // Last resort to clean up PDF templates by searching for all remaining placeholders
    $html = preg_replace('/\[%.*%\]/', '', $html);

    $html2pdf = new HTML2PDF('P', 'A4', 'en');
    $html2pdf->WriteHTML($html);

    if($save_on_server)
        {
        $html2pdf->Output($filename, 'F');
        }
    else
        {
        $html2pdf->Output($filename);
        }

    return true;
    }