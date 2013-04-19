<?php

# display collection title if option set.
$search_title = "";
$search_title_links = "";
$display_user_and_access=false;
global $baseurl_short;
# Display a title of the search (if there is a title)
$searchcrumbs="";
if ($search_titles_searchcrumbs && $use_refine_searchstring){
$refinements=str_replace(" -",",-",urldecode($search));
$refinements=explode(",",$search);	
if (substr($search,0,1)=="!"){$startsearchcrumbs=1;} else {$startsearchcrumbs=0;}
if ($refinements[0]!=""){
	for ($n=$startsearchcrumbs;$n<count($refinements);$n++){
		$search_title_element=str_replace(";"," OR ",$refinements[$n]);
		if ($n!=0 || $archive!=0){$searchcrumbs.=" > </count> </count> </count> ";}
		$searchcrumbs.="<a href=".$baseurl_short."pages/search.php?search=";
		for ($x=0;$x<=$n;$x++){
			$searchcrumbs.=urlencode($refinements[$x]);
			if ($x!=$n && substr($refinements[$x+1],0)!="-"){$searchcrumbs.=",";}		
		}
		if (!$search_titles_shortnames){
			$search_title_element=explode(":",$refinements[$n]);
			if (isset($search_title_element[1])){
				if (!isset($cattreefields)){$cattreefields=array();}
				if (in_array($search_title_element[0],$cattreefields)){$search_title_element=$lang['fieldtype-category_tree'];}
				else {$search_title_element=str_replace(";"," OR ",$search_title_element[1]);}
				}
			else{
				$search_title_element=$search_title_element[0];
				}
		}
		$searchcrumbs.="&order_by=" . $order_by . "&sort=".$sort."&offset=" . $offset . "&archive=" . $archive."&sort=".$sort.">".$search_title_element."</a>";
	}
}
}

if ($search_titles)
    {

    $parameters_string = '&order_by=' . $order_by . '&sort='.$sort.'&offset=' . $offset . '&archive=' . $archive.'&sort='.$sort . '&k=' . $k;

    if (substr($search,0,11)=="!collection")
        {
        if ($collection_dropdown_user_access_mode)
            {    
            $colusername = $collectiondata['fullname'];
                
            # Work out the correct access mode to display
            if (!hook('collectionaccessmode'))
                {
                if ($collectiondata["public"]==0)
                    {
                    $colaccessmode = $lang["private"];
                    }
                else
                    {
                    if (strlen($collectiondata["theme"])>0)
                        {
                        $colaccessmode = $lang["theme"];
                        }
                    else
                        {
                        $colaccessmode = $lang["public"];
                        }
                    }
                $display_user_and_access = true;
                }
            }

        // add a tooltip to Smart Collection titles (which provides a more detailed view of the searchstring.    
        $alt_text = '';
        if ($pagename=="search" && isset($collectiondata['savedsearch']) && $collectiondata['savedsearch']!='')
            {
            $smartsearch = sql_query("select * from collection_savedsearch where ref=".$collectiondata['savedsearch']);
            if (isset($smartsearch[0]))
                {
                $alt_text = "title='search=" . $smartsearch[0]['search'] . "&restypes=" . $smartsearch[0]['restypes'] . "&archive=" . $smartsearch[0]['archive'] . "&starsearch=" . $smartsearch[0]['starsearch'] . "'";
                }
            } 
        hook("collectionsearchtitlemod");
        $search_title.= '<div align="left"><h1><div class="searchcrumbs"><span id="coltitle'.$collection.'"><a '.$alt_text.' href='.$baseurl_short.'pages/search.php?search=!collection'.$collection.$parameters_string.'>'.i18n_get_collection_name($collectiondata).($display_user_and_access?" (".$colusername."/".$colaccessmode.")":"").'</a></span>'.$searchcrumbs.'</div></h1> ';
        }
    elseif ($search=="" && $archive==0)
        {
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=">'.$lang["allresources"].'</a></h1> ';
        }
    elseif (substr($search,0,6)=="!empty")
        {
		$searchq=substr($search,6);
		$searchq=explode(" ",$searchq);
		$searchq=rtrim(trim($searchq[0]),",");
		if (is_numeric($searchq)){
			$fref=$searchq;
			$ftitle=sql_value("select title value from resource_type_field where ref='" . $searchq . "'","");}
		else {
			$ftitleref=sql_query("select title,ref from resource_type_field where name='" . $searchq . "'","");
			if (!isset($ftitleref[0])){exit ("invalid !empty search");}
			$ftitle=$ftitleref[0]['title'];
			$fref=$ftitleref[0]['ref'];
		}
		if ($ftitle==""){exit ("invalid !empty search");}
		
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!nodata'.$fref.$parameters_string.'>'.str_replace("%field",i18n_get_translated($ftitle),$lang["untaggedresources"]).'</a>'.$searchcrumbs.'</h1> ';
        }    
    elseif (substr($search,0,5)=="!last")
        {
		$searchq=substr($search,5);
		$searchq=explode(",",$searchq);
		$searchq=$searchq[0];
		if (!is_numeric($searchq)){$searchq=1000;}  # 'Last' must be a number. SQL injection filter.
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!last'.$searchq.$parameters_string.'>'.str_replace('%qty',$searchq,$lang["n_recent"]).'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,8)=="!related")
        {
        $resource=substr($search,8);
		$resource=explode(",",$resource);
		$resource=$resource[0];
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!related'.$resource.$parameters_string.'>'.str_replace('%id%', $resource, $lang["relatedresources-id"]).'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,7)=="!unused")
        {
		$refinements=str_replace(","," / ",substr($search,7,strlen($search)));	
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!unused'.$parameters_string.'>'.$lang["uncollectedresources"].'</a>'.$searchcrumbs.'</h1>';
        }
    elseif (substr($search,0,11)=="!duplicates")
        {
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!duplicates'.$parameters_string.'>'.$lang["duplicateresources"].'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,5)=="!list")
        {
		$resources=substr($search,5);
		$resources=explode(",",$resources);
		$resources=$resources[0];	
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!list'.$resources.$parameters_string.'>'.$lang["listresources"]." ".$resources.'</a>'.$searchcrumbs.'</h1> ';
        }    
    elseif (substr($search,0,15)=="!archivepending")
        {
        $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!archivepending'.$parameters_string.'>'.$lang["resourcespendingarchive"].'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,12)=="!userpending")
		{
		$search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!userpending'.$parameters_string.'>'.$lang["userpending"].'</a>'.$searchcrumbs.'</h1> ';
		}
	elseif (substr($search,0,10)=="!nopreview")
		{
		$search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!nopreview'.$parameters_string.'>'.$lang["nopreviewresources"].'</a>'.$searchcrumbs.'</h1> ';
		}	
    elseif (substr($search,0,14)=="!contributions")
        {
		$cuser=substr($search,14);
		$cuser=explode(",",$cuser);
		$cuser=$cuser[0];	

        if ($cuser==$userref)
            {
            switch ($archive)
                {
                case -2:
                    $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'>'.$lang["contributedps"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                case -1:
                    $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'>'.$lang["contributedpr"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                case -0:
                    $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'>'.$lang["contributedsubittedl"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                }
            }
        }
    else if ($archive!=0)
        {
        switch ($archive)
            {
            case -2:
                $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search='.$parameters_string.'>'.$lang["userpendingsubmission"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case -1:
                $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search='.$parameters_string.'>'.$lang["userpending"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case 2:
                $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search='.$parameters_string.'>'.$lang["archiveonlysearch"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case 3:
                $search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search='.$parameters_string.'>'.$lang["deletedresources"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            }
        }
    else if (substr($search,0,1)!="!")
		{ 
		$search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search='.$parameters_string.'></a>'.$searchcrumbs.'</h1> '; 
		}   
	
	hook("addspecialsearchtitle");
	
	// extra collection title links
	if (substr($search,0,11)=="!collection"){
		if ($k=="" && !checkperm("b") && ($userrequestmode!=2 && $userrequestmode!=3)){$search_title_links='<a href="#" onclick="ChangeCollection(' . $collectiondata["ref"] . ', \'\');">&gt;&nbsp;'.$lang["selectcollection"].'</a>&nbsp;&nbsp;';}
		if (count($result)!=0 && $k==""&&$preview_all){$search_title_links.='<a href="'.$baseurl_short.'pages/preview_all.php?ref='.$collectiondata["ref"].'&order_by='.$order_by.'&sort='.$sort.'&archive='.$archive.'&k='.$k.'">&gt;&nbsp;'.$lang['preview_all'].'</a>';}
		$search_title.='</div>';
		if ($display!="list"){$search_title_links.= '<br /><br />';}
	}
}  
