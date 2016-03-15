<?php

# display collection title if option set.
$search_title = "";
$search_title_links = "";
$display_user_and_access=false;
$is_theme=false;
global $baseurl_short,$filename_field;
# Display a title of the search (if there is a title)
$searchcrumbs="";
if ($search_titles_searchcrumbs && $use_refine_searchstring){
$refinements=str_replace(" -",",-",rawurldecode($search));
$refinements=explode(",",$search);
if (substr($search,0,1)=="!" && substr($search,0,6)!="!empty"){$startsearchcrumbs=1;} else {$startsearchcrumbs=0;}
if ($refinements[0]!=""){
	for ($n=$startsearchcrumbs;$n<count($refinements);$n++){
		# strip the first semi-colon so it's not swapped with an " OR "
		$semi_pos = strpos($refinements[$n],":;");
		if ($semi_pos !== false) {
			$refinements[$n] = substr_replace($refinements[$n],": ",$semi_pos,strlen(":;"));
		}
		
		$search_title_element=str_replace(";"," OR ",$refinements[$n]);
		if ($n!=0 || $archive!=0){$searchcrumbs.=" > </count> </count> </count> ";}
		$searchcrumbs.="<a href=\"".$baseurl_short."pages/search.php?search=";
		for ($x=0;$x<=$n;$x++){
			$searchcrumbs.=urlencode($refinements[$x]);
			if ($x!=$n && substr($refinements[$x+1],0)!="-"){$searchcrumbs.=",";}		
		}
		if (!$search_titles_shortnames){
			$search_title_element=explode(":",$refinements[$n]);
			if (isset($search_title_element[1])){
			$datefieldinfo=sql_query("select ref from resource_type_field where name='" . trim(escape_check($search_title_element[0])) . "' and type IN (4,6,10)",0);
			if (count($datefieldinfo)) 
			    {
			    $search_title_element[1]=str_replace("|", "-", $search_title_element[1]);
			    $search_title_element[1]=str_replace("nn", "??", $search_title_element[1]);
			    }
				if (!isset($cattreefields)){$cattreefields=array();}
				if (in_array($search_title_element[0],$cattreefields)){$search_title_element=$lang['fieldtype-category_tree'];}
				else {$search_title_element=str_replace(";"," OR ",$search_title_element[1]);}
				}
			else{
				$search_title_element=$search_title_element[0];
				}
		}
		if (substr(trim($search_title_element),0,6)=="!empty")
        {   // superspecial !empty search  
			$search_title_elementq=trim(str_replace("!empty","",rawurldecode($search_title_element)));
			if (is_numeric($search_title_elementq)){
				$fref=$search_title_elementq;
				$ftitle=sql_value("select title value from resource_type_field where ref='" .$search_title_elementq . "'","");
				}
			else {
				$ftitleref=sql_query("select title,ref from resource_type_field where name='" . $search_title_elementq . "'","");
				if (!isset($ftitleref[0])){exit ("invalid !empty search. No such field: $search_title_elementq");}
				$ftitle=$ftitleref[0]['title'];
				$fref=$ftitleref[0]['ref'];
			}
			if ($ftitle==""){exit ("invalid !empty search");}
			$search_title_element=str_replace("%field",lang_or_i18n_get_translated($ftitle, "fieldtitle-"),$lang["untaggedresources"]);
		}
		
		
		$searchcrumbs.="&amp;order_by=" . htmlspecialchars($order_by) . "&amp;sort=" . htmlspecialchars($sort) . "&amp;offset=" . htmlspecialchars($offset) . "&amp;archive=" . htmlspecialchars($archive) . "&amp;sort=" . htmlspecialchars($sort) . "\" onClick='return CentralSpaceLoad(this,true);'>".$search_title_element."</a>";
	}
}
}

if (isset($collectiondata["theme"]) && strlen($collectiondata["theme"])>0)
		{
		$colaccessmode = $lang["themes"];
		$is_theme=true;						
		$theme_link="<a onClick='return CentralSpaceLoad(this,true);' href='" . $baseurl . "/pages/themes.php'>".$lang['themes']."</a> &gt; <a onClick='return CentralSpaceLoad(this,true);' href='".$baseurl . "/pages/themes.php?theme1=" . urlencode($collectiondata["theme"]) . "'>" . i18n_get_translated($collectiondata["theme"]) . "</a>";
		global $theme_category_levels;
		for($x=2;$x<=$theme_category_levels;$x++)
			{					
			if(isset($collectiondata['theme' . $x]) && strlen($collectiondata['theme' . $x]) > 0)
				{
				$theme_link_url = $baseurl . "/pages/themes.php?lastlevelchange=" . $x . "&theme1=" . urlencode($collectiondata["theme"]);
				for($l=2;$l<=$x;$l++)
					{
					$theme_link_url .= "&theme" . $l . "=" . urlencode($collectiondata['theme' . $l]);
					}
				$theme_link .= " &gt; <a onClick='return CentralSpaceLoad(this, true);' href='" . $theme_link_url . "'>" . i18n_get_translated($collectiondata['theme' . $x]) . "</a>";
				}
			}			
		}


if ($search_titles)
    {

    $parameters_string = '&amp;order_by=' . urlencode($order_by) . '&amp;sort=' . urlencode($sort) . '&amp;offset=' . urlencode($offset) . '&amp;archive=' . urlencode($archive) . '&amp;sort=' . urlencode($sort) . '&amp;k=' . urlencode($k);

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
                    $display_user_and_access = true;
                    if ($colusername!=""){$colaccessmode="/".$colaccessmode;}
                    }
                else if (strlen($collectiondata["theme"])==0)
                    {
                    $colaccessmode = $lang["public"];
                    $display_user_and_access = true;
					if ($colusername!=""){$colaccessmode="/".$colaccessmode;}
                    }
                
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
        $search_title.= '<div align="left"><h1><div class="searchcrumbs">'.($is_theme?$theme_link." > ":"").'<span id="coltitle'.$collection.'"><a '.$alt_text.' href="'.$baseurl_short.'pages/search.php?search=!collection'.$collection.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.i18n_get_collection_name($collectiondata).($display_user_and_access?" <span class='CollectionUser'>(".$colusername.$colaccessmode.")</span>":"").'</a></span>'.$searchcrumbs.'</div></h1></div>';
        }
    elseif ($search=="" && $archive==0)
        {
        # Which resource types (if any) are selected?
        $searched_types_refs_array = explode(",", $restypes); # Searched resource types and collection types
        $resource_types_array = get_resource_types("", false); # Get all resource types, untranslated
        $searched_resource_types_names_array = array();
        for ($n = 0; $n < count($resource_types_array); $n++) 
            {
            if (in_array($resource_types_array[$n]["ref"], $searched_types_refs_array)) 
                {
                $searched_resource_types_names_array[] = htmlspecialchars(lang_or_i18n_get_translated($resource_types_array[$n]["name"], "resourcetype-", "-2"));
                }
            }
        if (count($searched_resource_types_names_array)==count($resource_types_array))
            {
            # All resource types are selected, don't list all of them
            unset($searched_resource_types_names_array);
            $searched_resource_types_names_array[0] = $lang["all-resourcetypes"];
            }

        # Which collection types (if any) are selected?
        $searched_collection_types_names_array = array();
        if (in_array("mycol", $searched_types_refs_array)) 
            {
            $searched_collection_types_names_array[] = $lang["mycollections"];
            }
        if (in_array("pubcol", $searched_types_refs_array)) 
            {
            $searched_collection_types_names_array[] = $lang["publiccollections"];
            }
        if (in_array("themes", $searched_types_refs_array)) 
            {
            $searched_collection_types_names_array[] = $lang["themes"];
            }
        if (count($searched_collection_types_names_array)==3)
            {
            # All collection types are selected, don't list all of them
            unset($searched_collection_types_names_array);
            $searched_collection_types_names_array[0] = $lang["all-collectiontypes"];
            }

        if (count($searched_resource_types_names_array)>0 && count($searched_collection_types_names_array)==0)
            {
            # Only (one or more) resource types are selected
            	$searchtitle=$lang["all"]." ".implode($lang["resourcetypes_separator"]." ",$searched_resource_types_names_array);
            //$searchtitle = str_replace_formatted_placeholder("%resourcetypes%", $searched_resource_types_names_array, $lang["resourcetypes-no_collections"], false, $lang["resourcetypes_separator"]);
            }
        elseif (count($searched_resource_types_names_array)==0 && count($searched_collection_types_names_array)>0)
            {
            # Only (one or more) collection types are selected
            $searchtitle = str_replace_formatted_placeholder("%collectiontypes%", $searched_collection_types_names_array, $lang["no_resourcetypes-collections"], false, $lang["collectiontypes_separator"]);
            }
        elseif (count($searched_resource_types_names_array)>0 && count($searched_collection_types_names_array)>0)
            {
            # Both resource types and collection types are selected
            # Step 1: Replace %resourcetypes%
            $searchtitle=$lang["all"]." ".implode($lang["resourcetypes_separator"]." ",$searched_resource_types_names_array);
            //$searchtitle = str_replace_formatted_placeholder("%resourcetypes%", $searched_resource_types_names_array, $lang["resourcetypes-collections"], false, //$lang["resourcetypes_separator"]);
            
            # Step 2: Replace %collectiontypes%
            $searchtitle = str_replace_formatted_placeholder("%collectiontypes%", $searched_collection_types_names_array, $searchtitle, false, $lang["collectiontypes_separator"]);
            }
        else
            {
            # No resource types and no collection types are selected – show all resource types and all collection types
            # Step 1: Replace %resourcetypes%
            $searchtitle = str_replace_formatted_placeholder("%resourcetypes%", $lang["all-resourcetypes"], $lang["resourcetypes-collections"], false, $lang["resourcetypes_separator"]);
            # Step 2: Replace %collectiontypes%
            $searchtitle = str_replace_formatted_placeholder("%collectiontypes%", $lang["all-collectiontypes"], $searchtitle, false, $lang["collectiontypes_separator"]);
            }

        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=" onClick="return CentralSpaceLoad(this,true);">'.$searchtitle.'</a></h1> ';
        } 
    elseif (substr($search,0,5)=="!last")
        {
		$searchq=substr($search,5);
		$searchq=explode(",",$searchq);
		$searchq=$searchq[0];
		if (!is_numeric($searchq)){$searchq=1000;}  # 'Last' must be a number. SQL injection filter.
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!last'.$searchq.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.str_replace('%qty',$searchq,$lang["n_recent"]).'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,8)=="!related")
        {
        $resource=substr($search,8);
		$resource=explode(",",$resource);
		$resource=$resource[0];
		$displayfield=get_data_by_field($resource,$related_search_searchcrumb_field);
		if($displayfield==''){
			$displayfield=get_data_by_field($resource,$filename_field);
		}
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!related'.$resource.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.str_replace('%id%', $displayfield, $lang["relatedresources-id"]).'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,7)=="!unused")
        {
		$refinements=str_replace(","," / ",substr($search,7,strlen($search)));	
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!unused'.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["uncollectedresources"].'</a>'.$searchcrumbs.'</h1>';
        }
    elseif (substr($search,0,11)=="!duplicates")
        {
        $ref=explode(" ",$search);$ref=str_replace("!duplicates","",$ref[0]);
		$ref=explode(",",$ref);// just get the number
		$ref=escape_check($ref[0]);
		$filename=get_data_by_field($ref,$filename_field);
		if ($ref!="") {
			$search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!duplicates'.$ref.$parameters_string.' onClick="return CentralSpaceLoad(this,true);">'.$lang["duplicateresourcesfor"].$filename.'</a>'.$searchcrumbs.'</h1> ';
        	}
        else {
        	$search_title = '<h1 class="searchcrumbs"><a href='.$baseurl_short.'pages/search.php?search=!duplicates'.$parameters_string.' onClick="return CentralSpaceLoad(this,true);">'.$lang["duplicateresources"].'</a>'.$searchcrumbs.'</h1> ';
        	}
        }
    elseif (substr($search,0,5)=="!list")
        {
		$resources=substr($search,5);
		$resources=explode(",",$resources);
		$resources=$resources[0];	
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!list'.$resources.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["listresources"]." ".$resources.'</a>'.$searchcrumbs.'</h1> ';
        }    
    elseif (substr($search,0,15)=="!archivepending")
        {
        $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!archivepending'.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["resourcespendingarchive"].'</a>'.$searchcrumbs.'</h1> ';
        }
    elseif (substr($search,0,12)=="!userpending")
		{
		$search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!userpending'.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["userpending"].'</a>'.$searchcrumbs.'</h1> ';
		}
	elseif (substr($search,0,10)=="!nopreview")
		{
		$search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!nopreview'.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["nopreviewresources"].'</a>'.$searchcrumbs.'</h1> ';
		}
	elseif (substr($search,0,4)=="!geo")
		{
		$search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!geo'.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["geographicsearchresults"].'</a>'.$searchcrumbs.'</h1> ';
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
                    $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["contributedps"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                case -1:
                    $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["contributedpr"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                case -0:
                    $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["contributedsubittedl"].'</a>'.$searchcrumbs.'</h1> ';
                    break;
                }
            }
            else 
            {
            $udata=get_user($cuser);
            $displayname=htmlspecialchars($udata["fullname"]);
            if (trim($displayname)=="") $displayname=htmlspecialchars($udata["username"]);
            $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!contributions'.$cuser.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["contributedby"]." ".$displayname." - ".$lang["status".intval($archive)].'</a>'.$searchcrumbs.'</h1> ';
            }
        }
	 elseif (substr($search,0,8)=="!hasdata")
        {		
		$fieldref=intval(trim(substr($search,8)));        
		$fieldinfo=get_resource_type_field($fieldref);
		$displayname=i18n_get_translated($fieldinfo["title"]);
		if (trim($displayname)=="") $displayname=$fieldinfo["ref"];
		$search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search=!hasdata'.$fieldref.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["search_title_hasdata"]." ".$displayname." - ".$lang["status".intval($archive)].'</a>'.$searchcrumbs.'</h1> ';            
        }
    else if ($archive!=0)
        {
        switch ($archive)
            {
            case -2:
                $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search='.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["userpendingsubmission"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case -1:
                $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search='.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["userpending"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case 2:
                $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search='.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["archiveonlysearch"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            case 3:
                $search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search='.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);">'.$lang["deletedresources"].'</a>'.$searchcrumbs.'</h1> ';
                break;
            }
        }
    else if (substr($search,0,1)!="!" || substr($search,0,6)=="!empty")
		{ 
		$search_title = '<h1 class="searchcrumbs"><a href="'.$baseurl_short.'pages/search.php?search='.$parameters_string.'" onClick="return CentralSpaceLoad(this,true);"></a>'.$searchcrumbs.'</h1> '; 
		}   
	
	hook("addspecialsearchtitle");
	}

    hook('add_search_title_links');

    if (!hook("replacenoresourcesfoundsearchtitle")){
    	if (!is_array($result) && empty($collections) && getvalescaped("addsmartcollection","") == '') {
    		$search_title = '<h1 class="searchcrumbs"><a href="' . $baseurl_short . 'pages/search.php">'.$lang["noresourcesfound"].'</a></h1>';
    	}
}  
