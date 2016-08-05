<?php

function HookFilterboxSearchSearchaftersearchcookie()
	{
	global $filter_keywords, $perform_filter, $filter_pos, $search;
	$filter_keywords=getvalescaped("filter_keywords","");
	$filter_pos=getvalescaped("cursorpos","");
	setcookie('filter', $filter_keywords);
	setcookie('filter_pos', $filter_pos);
	setcookie('original_search', $search);
	$perform_filter=true;
	}

function HookFilterboxSearchDosearchmodifykeywords($keywords)
	{
	global $perform_filter, $filter_keywords;
	if (!empty($perform_filter) && !empty($filter_keywords))
		{
		$perform_filter=false;
		$filterArray=explode(',', $filter_keywords);
		foreach ($filterArray as $filterKeyword)
			{
			$filterKeyword=  strtolower(trim($filterKeyword));
			if (!strpos($filterKeyword, '*'))
				$filterKeyword='*'.$filterKeyword.'*';
			$keywords[]=$filterKeyword;
			}
		return $keywords;
		}

	return false;
	}

?>
