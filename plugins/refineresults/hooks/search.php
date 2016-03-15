<?php
include_once __DIR__ . '/../../../include/render_functions.php';

function HookRefineresultsSearchRender_search_actions_add_option($options)
    {
    global $baseurl_short, $lang, $k, $search, $parameters_string, $result, $collections;
	
	$c=count($options);
    $results = 0;
	$return=false;
    if(is_array($result))
        {
        $results = count($result);
        }

    if(is_array($collections))
        {
        $results += count($collections);
        }

    # External sharing search support. Clear search drops back to the collection only search.
    $default_search = '';
    if($k != '' || ($k == '' && substr($search, 0, 1) == '!'))
        {
        $s = explode(' ', $search);
        $default_search = str_replace(',','',$s[0]);
        }
	
    // Search within these results option
    if ($results > 1)
        {
        $options[$c]['value']='search_within_results';
		$options[$c]['label']=$lang['refineresults'];
		$c++;
		
		$return=true;
        }

    // Clear search terms option
    if($search != '')
        {
        $data_attribute['url'] = $baseurl_short . 'pages/search.php?search=' . $default_search . $parameters_string . '&k=' . urlencode($k);
		$options[$c]['value']='clear_search_terms';
		$options[$c]['label']=$lang['clearsearch'];
		$options[$c]['data_attr']=$data_attribute;
		$c++;
		
		$return=true;
        }
	if($return)
		{
		return $options;
		}
    }


function HookRefineresultsSearchRender_actions_add_option_js_case()
    {
    $cases  = '';
    $cases .= sprintf('
        case "%s":
            jQuery("#RefineResults").slideToggle();
            jQuery("#refine_keywords").focus();

            break;
        ',
        'search_within_results'
    );

    return $cases;
    }
