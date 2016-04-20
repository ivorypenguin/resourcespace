<?php
# Search functions
# Functions to perform searches (read only)
#  - For resource indexing / keyword creation, see resource_functions.php

include_once 'node_functions.php';

if (!function_exists("do_search")) {
function do_search($search,$restypes="",$order_by="relevance",$archive=0,$fetchrows=-1,$sort="desc",$access_override=false,$starsearch=0,$ignore_filters=false,$return_disk_usage=false,$recent_search_daylimit="", $go=false, $stats_logging=true)
    {
    debug("search=$search $go $fetchrows restypes=$restypes archive=$archive daylimit=$recent_search_daylimit");

    # globals needed for hooks
    global $sql,$order,$select,$sql_join,$sql_filter,$orig_order,$collections_omit_archived,$search_sql_double_pass_mode,$usergroup,$search_filter_strict,$default_sort,$superaggregationflag;

	$superaggregation = isset($superaggregationflag) && $superaggregationflag===true ? ' WITH ROLLUP' : '';

    $alternativeresults = hook("alternativeresults", "", array($go));
    if ($alternativeresults) {return $alternativeresults; }
    
    $modifyfetchrows = hook("modifyfetchrows", "", array($fetchrows));
    if ($modifyfetchrows) {$fetchrows=$modifyfetchrows; }
    
    
    # Takes a search string $search, as provided by the user, and returns a results set
    # of matching resources.
    # If there are no matches, instead returns an array of suggested searches.
    # $restypes is optionally used to specify which resource types to search.
    # $access_override is used by smart collections, so that all all applicable resources can be judged regardless of the final access-based results
    
    # Check valid sort
    if(!in_array(strtolower($sort),array("asc","desc"))){$sort="asc";};
    
    # resolve $order_by to something meaningful in sql
    $orig_order=$order_by;
    global $date_field;
    $order = array(
        "relevance"       => "score $sort, user_rating $sort, hit_count $sort, field$date_field $sort,r.ref $sort",
        "popularity"      => "user_rating $sort,hit_count $sort,field$date_field $sort,r.ref $sort",
        "rating"          => "r.rating $sort, user_rating $sort, score $sort,r.ref $sort",
        "date"            => "field$date_field $sort,r.ref $sort",
        "colour"          => "has_image $sort,image_blue $sort,image_green $sort,image_red $sort,field$date_field $sort,r.ref $sort",
        "country"         => "country $sort,r.ref $sort",
        "title"           => "title $sort,r.ref $sort",
        "file_path"       => "file_path $sort,r.ref $sort",
        "resourceid"      => "r.ref $sort",
        "resourcetype"    => "resource_type $sort,r.ref $sort",
        "titleandcountry" => "title $sort,country $sort",
        "random"          => "RAND()",
        "status"          => "archive $sort"
    );
    if (!in_array($order_by,$order)&&(substr($order_by,0,5)=="field") ) 
        {
        if (!is_numeric(str_replace("field","",$order_by))) {exit("Order field incorrect.");}
        $order[$order_by]="$order_by $sort";
        }

    hook("modifyorderarray");

    # Recognise a quoted search, which is a search for an exact string
    global $quoted_string;
    $quoted_string=false;
    if (substr($search,0,1)=="\"" && substr($search,-1,1)=="\"") {$quoted_string=true;$search=substr($search,1,-1);}

    $order_by=isset($order[$order_by]) ? $order[$order_by] : $order['relevance'];       // fail safe by falling back to default if not found

    # Extract search parameters and split to keywords.
    $search_params=$search;
    if (substr($search,0,1)=="!")
        {
        # Special search, discard the special search identifier when splitting keywords and extract the search paramaters
        $s=strpos($search," ");
        if ($s===false)
            {
            $search_params=""; # No search specified
            }
        else
            {
            $search_params=substr($search,$s+1); # Extract search params            
            }
        }
    $keywords=split_keywords($search_params);

    foreach (get_indexed_resource_type_fields() as $resource_type_field)
        {
        add_verbatim_keywords($keywords,$search,$resource_type_field,true);      // add any regex matched verbatim keywords for those indexed resource type fields
        }

    $search=trim($search);
    # Dedupe keywords (not for quoted strings as the user may be looking for the same word multiple times together in this instance)
    if (!$quoted_string) {$keywords=array_values(array_unique($keywords));}
        
    $modified_keywords=hook('dosearchmodifykeywords', '', array($keywords));
    if ($modified_keywords)
        {
        $keywords=$modified_keywords;
        }

    # -- Build up filter SQL that will be used for all queries
    $sql_filter=search_filter($search,$archive,$restypes,$starsearch,$recent_search_daylimit,$access_override,$return_disk_usage);

    # Initialise variables.
    $sql="";
    $sql_keyword_union_whichkeys   = array();
    $sql_keyword_union             = array();
    $sql_keyword_union_aggregation = array();
    $sql_keyword_union_criteria    = array();
    $sql_keyword_union_sub_query   = array();

    # If returning disk used by the resources in the search results ($return_disk_usage=true) then wrap the returned SQL in an outer query that sums disk usage.
    $sql_prefix="";$sql_suffix="";
    if ($return_disk_usage) {$sql_prefix="select sum(disk_usage) total_disk_usage,count(*) total_resources from (";$sql_suffix=") resourcelist";}
    
    # ------ Advanced 'custom' permissions, need to join to access table.
    $sql_join="";
    global $k;
    if ((!checkperm("v")) &&!$access_override)
        {
        global $usergroup;global $userref;
        # one extra join (rca2) is required for user specific permissions (enabling more intelligent watermarks in search view)
        # the original join is used to gather group access into the search query as well.
        $sql_join=" left outer join resource_custom_access rca2 on r.ref=rca2.resource and rca2.user='$userref'  and (rca2.user_expires is null or rca2.user_expires>now()) and rca2.access<>2  ";  
        $sql_join.=" left outer join resource_custom_access rca on r.ref=rca.resource and rca.usergroup='$usergroup' and rca.access<>2 ";
        
        if ($sql_filter!="") {$sql_filter.=" and ";}
        # If rca.resource is null, then no matching custom access record was found
        # If r.access is also 3 (custom) then the user is not allowed access to this resource.
        # Note that it's normal for null to be returned if this is a resource with non custom permissions (r.access<>3).
        $sql_filter.=" not(rca.resource is null and r.access=3)";
        }
        
    # Join thumbs_display_fields to resource table  
    $select="r.ref, r.resource_type, r.has_image, r.is_transcoding, r.hit_count, r.creation_date, r.rating, r.user_rating, r.user_rating_count, r.user_rating_total, r.file_extension, r.preview_extension, r.image_red, r.image_green, r.image_blue, r.thumb_width, r.thumb_height, r.archive, r.access, r.colour_key, r.created_by, r.file_modified, r.file_checksum, r.request_count, r.new_hit_count, r.expiry_notification_sent, r.preview_tweaks, r.file_path ";  
    
    $modified_select=hook("modifyselect");
    if ($modified_select){$select.=$modified_select;}   
    $modified_select2=hook("modifyselect2");
    if ($modified_select2){$select.=$modified_select2;} 

    # Return disk usage for each resource if returning sum of disk usage.
    if ($return_disk_usage) {$select.=",r.disk_usage";}

    # select group and user access rights if available, otherwise select null values so columns can still be used regardless
    # this makes group and user specific access available in the basic search query, which can then be passed through access functions
    # in order to eliminate many single queries.
    if ((!checkperm("v")) &&!$access_override)
        {
        $select.=",rca.access group_access,rca2.access user_access ";
        }
    else 
        {
        $select.=",null group_access, null user_access ";
        }
    
    # add 'joins' to select (adding them 
    $joins=get_resource_table_joins();
    foreach( $joins as $datajoin)
        {
        $select.=",r.field".$datajoin." ";
        }   

    # Prepare SQL to add join table for all provided keywods
    
    $suggested=$keywords; # a suggested search
    $fullmatch=true;
    $c=0;
    $t="";
    $t2="";
    $score="";
    $skipped_last=false;
        
    $keysearch=true;
    
     # Do not process if a numeric search is provided (resource ID)
    global $config_search_for_number, $category_tree_search_use_and;
    if ($config_search_for_number && is_numeric($search)) {$keysearch=false;}
    
    
    # Fetch a list of fields that are not available to the user - these must be omitted from the search.
    $hidden_indexed_fields=get_hidden_indexed_fields();

	# This is a performance enhancement that will discard any keyword matches for fields that are not supposed to be indexed.
	$sql_restrict_by_field_types="";
	global $search_sql_force_field_index_check;
	if (isset($search_sql_force_field_index_check) && $search_sql_force_field_index_check && $restypes!="")
		{
		$sql_restrict_by_field_types = sql_value("select group_concat(ref) as value from resource_type_field where keywords_index=1 and resource_type in ({$restypes})","");
		if ($sql_restrict_by_field_types != "")
			{
			$sql_restrict_by_field_types = "-1," . $sql_restrict_by_field_types;  // -1 needed for global search
			}
		}

    if ($keysearch)
        {
        for ($n=0;$n<count($keywords);$n++)
            {           
            $keyword=$keywords[$n];

            if (substr($keyword,0,1)!="!" || substr($keyword,0,6)=="!empty")
                {
                global $date_field;
                $field=0;#echo "<li>$keyword<br/>";
                if (strpos($keyword,":")!==false && !$ignore_filters)
                    {
                    $kw=explode(":",$keyword,2);
                    global $datefieldinfo_cache;
                    if (isset($datefieldinfo_cache[$kw[0]]))
                        {
                        $datefieldinfo=$datefieldinfo_cache[$kw[0]];
                        } 
                    else 
                        {
                        $datefieldinfo=sql_query("select ref from resource_type_field where name='" . escape_check($kw[0]) . "' and type IN (4,6,10)",0);
                        $datefieldinfo_cache[$kw[0]]=$datefieldinfo;
                        }

                    if (count($datefieldinfo) && substr($kw[1],0,5)!="range")
                        {
                        $c++;
                        $datefieldinfo=$datefieldinfo[0];
                        $datefield=$datefieldinfo["ref"];
                        if ($sql_filter!="") {$sql_filter.=" and ";}
                        $val=str_replace("n","_", $kw[1]);
                        $val=str_replace("|","-", $val);
                        $sql_filter.="rd" . $c . ".value like '". $val . "%' "; 
                        $sql_join.=" join resource_data rd" . $c . " on rd" . $c . ".resource=r.ref and rd" . $c . ".resource_type_field='" . $datefield . "'";
                        }
                    elseif ($kw[0]=="day")
                        {
                        if ($sql_filter!="") {$sql_filter.=" and ";}
                        $sql_filter.="r.field$date_field like '____-__-" . $kw[1] . "%' ";
                        }
                    elseif ($kw[0]=="month")
                        {
                        if ($sql_filter!="") {$sql_filter.=" and ";}
                        $sql_filter.="r.field$date_field like '____-" . $kw[1] . "%' ";
                        }
                    else if('year' == $kw[0])
                        {
                        if('' != $sql_filter)
                            {
                            $sql_filter .= ' AND ';
                            }
                        $sql_filter.= "rd{$c}.resource_type_field = {$date_field} AND rd{$c}.value LIKE '{$kw[1]}%' ";

                        $sql_join .= " INNER JOIN resource_data rd{$c} ON rd{$c}.resource = r.ref AND rd{$c}.resource_type_field = '{$date_field}'";
                        }
                    elseif ($kw[0]=="startdate")
                        {
                        if ($sql_filter!="") {$sql_filter.=" and ";}
                        $sql_filter.="r.field$date_field >= '" . $kw[1] . "' ";
                        }
                    elseif ($kw[0]=="enddate")
                        {
                        if ($sql_filter!="") {$sql_filter.=" and ";}
                        $sql_filter.="r.field$date_field <= '" . $kw[1] . " 23:59:59' ";
                        }
                    # Additional date range filtering
                    elseif (count($datefieldinfo) && substr($kw[1],0,5)=="range")
                        {
                        $c++;
                        $rangefield=$datefieldinfo[0]["ref"];
                        $daterange=false;
                        $rangestring=substr($kw[1],5);
                        if (strpos($rangestring,"start")!==FALSE )
                            {
                            $rangestart=str_replace(" ","-",$rangestring);
                            if ($sql_filter!="") {$sql_filter.=" and ";}
                            $sql_filter.="rd" . $c . ".value >= '" . substr($rangestart,strpos($rangestart,"start")+5,10) . "'";
                            }
                        if (strpos($kw[1],"end")!==FALSE )
                            {
                            $rangeend=str_replace(" ","-",$rangestring);
                            if ($sql_filter!="") {$sql_filter.=" and ";}
                            $sql_filter.="rd" . $c . ".value <= '" . substr($rangeend,strpos($rangeend,"end")+3,10) . " 23:59:59'";
                            }
                        $sql_join.=" join resource_data rd" . $c . " on rd" . $c . ".resource=r.ref and rd" . $c . ".resource_type_field='" . $rangefield . "'";
                        }
                    elseif (!hook('customsearchkeywordfilter', null, array($kw)))
                        {


                        # Fetch field info
                        global $fieldinfo_cache;
                        if (isset($fieldinfo_cache[$kw[0]])){
                            $fieldinfo=$fieldinfo_cache[$kw[0]];
                        } else {
                            $fieldinfo=sql_query("select ref,type from resource_type_field where name='" . escape_check($kw[0]) . "'",0);
                            $fieldinfo_cache[$kw[0]]=$fieldinfo;
                        }

                        if ($fieldinfo[0]["type"] == 7)
                            {
                            $ckeywords=preg_split('/[\|;]/',$kw[1]);
                            }
                        else
                            {
                            $ckeywords=explode(";",$kw[1]);
                            }

                        if (count($fieldinfo)==0)
                            {
                            debug("Field short name not found.");return false;
                            }
                        
                        # Create an array of matching field IDs.
                        $fields=array();foreach ($fieldinfo as $fi)
                            {
                            if (in_array($fi["ref"], $hidden_indexed_fields))
                                {
                                # Attempt to directly search field that the user does not have access to.
                                return false;
                                }
                            
                            # Add to search array
                            $fields[]=$fi["ref"];
                            }
                        
                        # Special handling for dates
                        if ($fieldinfo[0]["type"]==4 || $fieldinfo[0]["type"]==6 || $fieldinfo[0]["type"]==10) 
                            {
                            $ckeywords=array(str_replace(" ","-",$kw[1]));
                            }

                        #special SQL generation for category trees to use AND instead of OR
                        if ($fieldinfo[0]["type"] == 7 && $category_tree_search_use_and)
                            {
                            for ($m=0;$m<count($ckeywords);$m++) {
                                $keyref=resolve_keyword($ckeywords[$m]);
                                if (!($keyref===false)) 
                                    {
                                    $c++;

                                    # Add related keywords
                                    $related=get_related_keywords($keyref);
                                    $relatedsql="";
                                    for ($r=0;$r<count($related);$r++)
                                        {
                                        $relatedsql.=" or k" . $c . ".keyword='" . $related[$r] . "'";
                                        }
                                    # Form join
                                    $sql_join.=" join resource_keyword k" . $c . " on k" . $c . ".resource=r.ref and k" . $c . ".resource_type_field in ('" . join("','",$fields) . "') and (k" . $c . ".keyword='$keyref' $relatedsql)";

                                    if ($score!="") {$score.="+";}
                                    $score.="k" . $c . ".hit_count";

                                    # Log this
                                    if ($stats_logging) {daily_stat("Keyword usage",$keyref);}
                                    }                           
                                
                                }
                            } 
                        else 
                            {
                            $c++;
                     
                            # work through all options in an OR approach for multiple selects on the same field
                            $searchkeys=array();
                            for ($m=0;$m<count($ckeywords);$m++)
                                {
                                $keyref=resolve_keyword($ckeywords[$m]);
                                if ($keyref===false) {$keyref=-1;}
                                
                                $searchkeys[]=$keyref;
                
                                # Also add related.
                                $related=get_related_keywords($keyref);
                                for ($o=0;$o<count($related);$o++)
                                    {
                                    $searchkeys[]=$related[$o];
                                    }
                                    
                                # Log this
                                if ($stats_logging) {daily_stat("Keyword usage",$keyref);}
                                }
    
                            $union="select resource,";
                            for ($p=1;$p<=count($keywords);$p++)
                                {
                                if ($p==$c) {$union.="true";} else {$union.="false";}
                                $union.=" as keyword_" . $p . "_found,";
                                }
                            $union.="hit_count as score from resource_keyword k" . $c . " where (k" . $c . ".keyword='$keyref' or k" . $c . ".keyword in ('" . join("','",$searchkeys) . "')) and k" . $c . ".resource_type_field in ('" . join("','",$fields) . "')";
                            
                            if (!empty($sql_exclude_fields)) 
                                {
                                $union.=" and k" . $c . ".resource_type_field not in (". $sql_exclude_fields .")";
                                }
                            if (count($hidden_indexed_fields)>0)
                                {
                                $union.=" and k" . $c . ".resource_type_field not in ('". join("','",$hidden_indexed_fields) ."')";                         
                                }

							$sql_keyword_union_aggregation[] = "bit_or(keyword_" . $c . "_found) as keyword_" . $c . "_found";
							$sql_keyword_union_criteria[] = "h.keyword_" . $c . "_found";
							$sql_keyword_union[] = $union;
                            }
                        }
                    }
                else
                    {

                    # Normal keyword (not tied to a field) - searches all fields that the user has access to
                    
                    # If ignoring field specifications then remove them.
                    if (strpos($keyword,":")!==false && $ignore_filters)
                        {
                        $s=explode(":",$keyword);$keyword=$s[1];
                        }

                    # Omit resources containing this keyword?
                    $omit=false;
                    if (substr($keyword,0,1)=="-") {$omit=true;$keyword=substr($keyword,1);}
                    
                    # Search for resources with an empty field, ex: !empty18  or  !emptycaption
                    $empty=false;
                    if (substr($keyword,0,6)=="!empty"){
                        $nodatafield=str_replace("!empty","",$keyword);
                        
                        if (!is_numeric($nodatafield)){$nodatafield=sql_value("select ref value from resource_type_field where name='".escape_check($nodatafield)."'","");}
                        if ($nodatafield=="" || !is_numeric($nodatafield)){exit('invalid !empty search');}
                        $empty=true;
                        }
                    
                    global $noadd, $wildcard_always_applied, $wildcard_always_applied_leading;
                    if (in_array($keyword,$noadd)) # skip common words that are excluded from indexing
                        {
                        $skipped_last=true;
                        }
                    else
                        {
                        # Handle wildcards
                        $wildcards=array();
                        if (strpos($keyword,"*")!==false || $wildcard_always_applied)
                            {
                            if ($wildcard_always_applied && strpos($keyword,"*")===false)
                                {
                                # Suffix asterisk if none supplied and using $wildcard_always_applied mode.
                                $keyword=$keyword."*";

                                if($wildcard_always_applied_leading)
                                    {
                                    $keyword = '*' . $keyword;
                                    }
                                }
                            
                            # Keyword contains a wildcard. Expand.
                            global $wildcard_expand_limit;
                            $wildcards=sql_array("select ref value from keyword where keyword like '" . escape_check(str_replace("*","%",$keyword)) . "' order by hit_count desc limit " . $wildcard_expand_limit);
                            }       

                        $keyref=resolve_keyword(str_replace('*','',$keyword)); # Resolve keyword. Ignore any wildcards when resolving. We need wildcards to be present later but not here.
                        if ($keyref===false && !$omit && !$empty && count($wildcards)==0)
                            {
                            $fullmatch=false;
                            $soundex=resolve_soundex($keyword);
                            if ($soundex===false)
                                {
                                # No keyword match, and no keywords sound like this word. Suggest dropping this word.
                                $suggested[$n]="";
                                }
                            else
                                {
                                # No keyword match, but there's a word that sounds like this word. Suggest this word instead.
                                $suggested[$n]="<i>" . $soundex . "</i>";
                                }
                            }
                        else
                            {
                            if($keyref===false){
                                # make a new keyword
                                $keyref=resolve_keyword(str_replace('*','',$keyword),true);
                            }
                            # Key match, add to query.
                            $c++;

                            $relatedsql="";
                            if (!$quoted_string) # Do not use related fields or wildcard for quoted string search - the keywords are treated as literal in this case.
                                {
                                # Add related keywords
                                $related=get_related_keywords($keyref);
                                
                                # Merge wildcard expansion with related keywords
                                $related=array_merge($related,$wildcards);
                                if (count($related)>0)
                                    {
                                    $relatedsql=" or k" . $c . ".keyword IN ('" . join ("','",$related) . "')";
                                    }
                                }
                                    
                            # Form join
                            $sql_exclude_fields = hook("excludefieldsfromkeywordsearch");
                            
                            if (!$omit)
                                {
                                # Include in query

								// --------------------------------------------------------------------------------
								// Start of normal union for resource keywords
								// --------------------------------------------------------------------------------

								// add false for keyword matches other than the current one
								$bit_or_condition = "";
                                for ($p=1;$p<=count($keywords);$p++)
                                    {
                                    if ($p==$c) {
										$bit_or_condition.="true";
									} else
										{
										$bit_or_condition.="false";
										}
									$bit_or_condition.=" as keyword_" . $p . "_found,";
                                    }

								// these restrictions apply to both !empty searches as well as normal keyword searches (i.e. both branches of next if statement)
								$union_restriction_clause="";
								if (!empty($sql_exclude_fields))
									{
									$union_restriction_clause.=" and k" . $c . ".resource_type_field not in (". $sql_exclude_fields .")";
									}
								if (count($hidden_indexed_fields)>0)
									{
									$union_restriction_clause.=" and k" . $c . ".resource_type_field not in ('". join("','",$hidden_indexed_fields) ."')";
									}
								if ($empty)  // we are dealing with a special search checking if a field is empty
                                    {
                                    $rtype=sql_value("select resource_type value from resource_type_field where ref='$nodatafield'",0);
                                    if ($rtype!=0)
                                        {
                                        if ($rtype==999)
                                            {
                                            $restypesql="and (r" . $c . ".archive=1 or r" . $c . ".archive=2) and ";
                                            if ($sql_filter!="") {$sql_filter.=" and ";}
                                            $sql_filter.=str_replace("r" . $c . ".archive='0'","(r" . $c . ".archive=1 or r" . $c . ".archive=2)",$sql_filter);
                                            } 
                                        else 
                                            {
                                            $restypesql="and r" . $c . ".resource_type ='$rtype' ";
                                            } 
                                        } 
                                    else 
                                        {
                                        $restypesql="";
                                        }
                                    $union="select ref as resource, {$bit_or_condition} 1 as score from resource r" . $c . " left outer join resource_data rd" . $c . " on r" . $c . ".ref=rd" . $c .
										".resource and rd" . $c . ".resource_type_field='$nodatafield' where  (rd" . $c . ".value ='' or rd" . $c .
										".value is null or rd" . $c . ".value=',') $restypesql  and r" . $c . ".ref>0 group by r" . $c . ".ref ";
									$union.=$union_restriction_clause;

									$sql_keyword_union[]=$union;
                                    } 
                                else  // we are dealing with a standard keyword match
                                    {
									$filter_by_resource_field_type="";
									if ($sql_restrict_by_field_types!="")
										{
										$filter_by_resource_field_type = "and k{$c}.resource_type_field in ({$sql_restrict_by_field_types})";  // -1 needed for global search
										}
									$union="SELECT resource, {$bit_or_condition} SUM(hit_count) AS score FROM resource_keyword k{$c}
									WHERE (k{$c}.keyword={$keyref} {$filter_by_resource_field_type} {$relatedsql} {$union_restriction_clause})
									GROUP BY resource{$superaggregation}";
									$sql_keyword_union[]=$union;
									}

								$sql_keyword_union_aggregation[]="bit_or(keyword_" . $c . "_found) as keyword_" . $c . "_found";
                                $sql_keyword_union_criteria[]="h.keyword_" . $c . "_found";

								// --------------------------------------------------------------------------------

                                # Quoted search? Also add a specific join to check that the positions add up.
                                # The UNION / bit_or() approach doesn't support position checking hence the need for additional joins to do this.
                                if ($quoted_string)
                                    {
                                    $sql_join.=" join resource_keyword qrk_$c on qrk_$c.resource=r.ref and qrk_$c.keyword='$keyref' ";

                                    # Exclude fields from the quoted search join also                        
                                    if (!empty($sql_exclude_fields)) 
                                        {
                                        $sql_join.=" and qrk_" . $c . ".resource_type_field not in (". $sql_exclude_fields .")";
                                        }

                                    if (count($hidden_indexed_fields)>0)
                                        {
                                        $sql_join.=" and qrk_" . $c . ".resource_type_field not in ('". join("','",$hidden_indexed_fields) ."')";
                                        }
                                    
                                    # For keywords other than the first one, check the position is next to the previous keyword.
                                    if ($c>1)
                                        {
                                        $last_key_offset=1;
                                        if (isset($skipped_last) && $skipped_last) {$last_key_offset=2;} # Support skipped keywords - if the last keyword was skipped (listed in $noadd), increase the allowed position from the previous keyword. Useful for quoted searches that contain $noadd words, e.g. "black and white" where "and" is a skipped keyword.
                                        # Also check these occurances are within the same field.
                                        $sql_join.=" and qrk_" . $c . ".position>0 and qrk_" . $c . ".position=qrk_" . ($c-1) . ".position+" . $last_key_offset . " and qrk_" . $c . ".resource_type_field=qrk_" . ($c-1) . ".resource_type_field";
                                        }
                                    }
                                }
                            else if ($omit)
                                {                         
                                # Exclude matching resources from query (omit feature)
                                if ($sql_filter!="") {$sql_filter.=" and ";}
                                $sql_filter .= "r.ref not in (select resource from resource_keyword where keyword='$keyref')"; # Filter out resources that do contain the keyword.
                                }

                            # Log this
                            if ($stats_logging) {daily_stat("Keyword usage",$keyref);}
                            }
                        $skipped_last=false;
                        }
                    }
                }
            }
        }

    # Could not match on provided keywords? Attempt to return some suggestions.
    if ($fullmatch==false)
        {
        if ($suggested==$keywords)
            {
            # Nothing different to suggest.
            debug("No alternative keywords to suggest.");
            return "";
            }
        else
            {
            # Suggest alternative spellings/sound-a-likes
            $suggest="";
            if (strpos($search,",")===false) {$suggestjoin=" ";} else {$suggestjoin=", ";}
            for ($n=0;$n<count($suggested);$n++)
                {
                if ($suggested[$n]!="")
                    {
                    if ($suggest!="") {$suggest.=$suggestjoin;}
                    $suggest.=$suggested[$n];
                    }
                }
            debug ("Suggesting $suggest");
            return $suggest;
            }
        }

    hook("additionalsqlfilter");
    hook("parametricsqlfilter", '', array($search));
    
    # ------ Search filtering: If search_filter is specified on the user group, then we must always apply this filter.
    global $usersearchfilter;
    $sf=explode(";",$usersearchfilter);
    if (strlen($usersearchfilter)>0)
        {
        for ($n=0;$n<count($sf);$n++)
            {
            $s=explode("=",$sf[$n]);
            if (count($s)!=2) {exit ("Search filter is not correctly configured for this user group.");}

            # Support for "NOT" matching. Return results only where the specified value or values are NOT set.
            $filterfield=$s[0];$filter_not=false;
            if (substr($filterfield,-1)=="!")
                {
                $filter_not=true;
                $filterfield=substr($filterfield,0,-1);# Strip off the exclamation mark.
                }
            
            # Support for multiple fields on the left hand side, pipe separated - allows OR matching across multiple fields in a basic way
            $filterfields=explode("|",escape_check($filterfield));
                
            # Find field(s) - multiple fields can be returned to support several fields with the same name.
            $f=sql_array("select ref value from resource_type_field where name in ('" . join("','",$filterfields) . "')");
            if (count($f)==0) {exit ("Field(s) with short name '" . $filterfield . "' not found in user group search filter.");}
            
            # Find keyword(s)
            $ks=explode("|",strtolower(escape_check($s[1])));
            for($x=0;$x<count($ks);$x++)
                {
                # Cleanse the string as keywords are stored without special characters
                $ks[$x]=cleanse_string($ks[$x],true);
                
                global $stemming;
                if ($stemming && function_exists("GetStem")) // Stemming enabled. Highlight any words matching the stem.
                    {
                    $ks[$x]=GetStem($ks[$x]);
                    } 
                } 
            
            $modifiedsearchfilter=hook("modifysearchfilter");
            if ($modifiedsearchfilter){$ks=$modifiedsearchfilter;} 
            $kw=sql_array("select ref value from keyword where keyword in ('" . join("','",$ks) . "')");
                    
            if (!$filter_not)
                {
                # Standard operation ('=' syntax)
                $sql_join.=" join resource_keyword filter" . $n . " on r.ref=filter" . $n . ".resource and filter" . $n . ".resource_type_field in ('" . join("','",$f) . "') and ((filter" . $n . ".keyword in ('" .     join("','",$kw) . "')) ";
		
		# Option for custom access to override search filters.
		# For this resource, if custom access has been granted for the user or group, nullify the search filter for this particular resource effectively selecting "true".
		global $custom_access_overrides_search_filter;
		if (!checkperm("v") && !$access_override && $custom_access_overrides_search_filter) # only for those without 'v' (which grants access to all resources)
			{
			$sql_join.="or ((rca.access is not null and rca.access<>2) or (rca2.access is not null and rca2.access<>2))";
			}
		$sql_join.=")";
		
		
                if ($search_filter_strict > 1)
                    {
                    $sql_join.=" join resource_data dfilter" . $n . " on r.ref=dfilter" . $n . ".resource and dfilter" . $n . ".resource_type_field in ('" . join("','",$f) . "') and (find_in_set('". join ("', dfilter" . $n . ".value) or find_in_set('", explode("|",escape_check($s[1]))) ."', dfilter" . $n . ".value))";
                    }
                }
            else
                {
                # Inverted NOT operation ('!=' syntax)
                if ($sql_filter!="") {$sql_filter.=" and ";}
                $sql_filter .= "((r.ref not in (select resource from resource_keyword where resource_type_field in ('" . join("','",$f) . "') and keyword in ('" .    join("','",$kw) . "'))) "; # Filter out resources that do contain the keyword(s)
		
		# Option for custom access to override search filters.
		# For this resource, if custom access has been granted for the user or group, nullify the search filter for this particular resource effectively selecting "true".
		global $custom_access_overrides_search_filter;
		if (!checkperm("v") && !$access_override && $custom_access_overrides_search_filter) # only for those without 'v' (which grants access to all resources)
			{
			$sql_filter.="or ((rca.access is not null and rca.access<>2) or (rca2.access is not null and rca2.access<>2))";
			}
		
		$sql_filter.=")";
                }
            }
        }
        
    $userownfilter= hook("userownfilter");
    if ($userownfilter){$sql_join.=$userownfilter;} 
    
    # Handle numeric searches when $config_search_for_number=false, i.e. perform a normal search but include matches for resource ID first
    global $config_search_for_number;
    if (!$config_search_for_number && is_numeric($search))
        {
        # Always show exact resource matches first.
        $order_by="(r.ref='" . $search . "') desc," . $order_by;
        }

    # ---------------------------------------------------------------
    # Keyword union assembly.
    # Use UNIONs for keyword matching instead of the older JOIN technique - much faster
    # Assemble the new join from the stored unions
    # ---------------------------------------------------------------

	if (count($sql_keyword_union)>0)
        {
		$sql_join .= " join (
		select resource,sum(score) as score,
		" . join(", ", $sql_keyword_union_aggregation) . " from
		(" . join(" union ", $sql_keyword_union) . ") as hits group by resource{$superaggregation}) as h on h.resource=r.ref ";

        if ($sql_filter!="") {$sql_filter.=" and ";}
		$sql_filter .= join(" and ", $sql_keyword_union_criteria);

        # Use amalgamated resource_keyword hitcounts for scoring (relevance matching based on previous user activity)
        $score="h.score";
        }
                

    # Can only search for resources that belong to themes
    if (checkperm("J"))
        {
        $sql_join=" join collection_resource jcr on jcr.resource=r.ref join collection jc on jcr.collection=jc.ref and length(jc.theme)>0 " . $sql_join;
        }
 	   
    # --------------------------------------------------------------------------------
    # Special Searches (start with an exclamation mark)
    # --------------------------------------------------------------------------------
    $special_results=search_special($search,$sql_join,$fetchrows,$sql_prefix,$sql_suffix,$order_by,$orig_order,$select,$sql_filter,$archive,$return_disk_usage);
    if ($special_results!==false) {return $special_results;}

    # -------------------------------------------------------------------------------------
    # Standard Searches
    # -------------------------------------------------------------------------------------
    
    # We've reached this far without returning.
    # This must be a standard (non-special) search.
    
    # Construct and perform the standard search query.
    #$sql="";
    if ($sql_filter!="")
        {
        if ($sql!="") {$sql.=" and ";}
        $sql.=$sql_filter;
        }

    # Append custom permissions 
    $t.=$sql_join;
    
    if ($score=="") {$score="r.hit_count";} # In case score hasn't been set (i.e. empty search)
    global $max_results;
    if (($t2!="") && ($sql!="")) {$sql=" and " . $sql;}
    
    # Compile final SQL

    # Performance enhancement - set return limit to number of rows required
    if ($search_sql_double_pass_mode && $fetchrows!=-1) {$max_results=$fetchrows;}

    $results_sql=$sql_prefix . "select distinct $score score, $select from resource r" . $t . "  where $t2 $sql group by r.ref order by $order_by limit $max_results" . $sql_suffix;

    # Debug
    debug('$results_sql=' . $results_sql);

    # Execute query
    $result=sql_query($results_sql,false,$fetchrows);

    # Performance improvement - perform a second count-only query and pad the result array as necessary
    if ($search_sql_double_pass_mode && count($result)>=$max_results)
        {
        $count_sql="select count(distinct r.ref) value from resource r" . $t . "  where $t2 $sql";      
        $count=sql_value($count_sql,0);
        $result=array_pad($result,$count,0);
        }

    debug("Search found " . count($result) . " results");
    if (count($result)>0) 
        {
        hook("beforereturnresults","",array($result, $archive));   
        return $result;
        }

    hook('zero_search_results');
    
    # (temp) - no suggestion for field-specific searching for now - TO DO: modify function below to support this
    if (strpos($search,":")!==false) {return "";}
    
    # All keywords resolved OK, but there were no matches
    # Remove keywords, least used first, until we get results.
    $lsql="";
    $omitmatch=false;
    for ($n=0;$n<count($keywords);$n++)
        {
        if (substr($keywords[$n],0,1)=="-")
            {
            $omitmatch=true;
            $omit=$keywords[$n];
            }
        if ($lsql!="") {$lsql.=" or ";}
        $lsql.="keyword='" . escape_check($keywords[$n]) . "'";
        }
    if ($omitmatch)
        {
        return trim_spaces(str_replace(" " . $omit . " "," "," " . join(" ",$keywords) . " "));     
        }
    if ($lsql!="")
        {
        $least=sql_value("select keyword value from keyword where $lsql order by hit_count asc limit 1","");
        return trim_spaces(str_replace(" " . $least . " "," "," " . join(" ",$keywords) . " "));
        }
    else
        {
        return array();
        }
    }
}

function resolve_soundex($keyword)
    {
    # returns the most commonly used keyword that sounds like $keyword, or failing a soundex match,
    # the most commonly used keyword that starts with the same few letters.
    global $soundex_suggest_limit;
    $soundex=sql_value("select keyword value from keyword where soundex='".soundex($keyword)."' and keyword not like '% %' and hit_count>'" . $soundex_suggest_limit . "' order by hit_count desc limit 1",false);
    if (($soundex===false) && (strlen($keyword)>=4))
        {
        # No soundex match, suggest words that start with the same first few letters.
        return sql_value("select keyword value from keyword where keyword like '" . substr($keyword,0,4) . "%' and keyword not like '% %' order by hit_count desc limit 1",false);
        }
    return $soundex;
    }
    
function suggest_refinement($refs,$search)
    {
    # Given an array of resource references ($refs) and the original
    # search query ($search), produce a list of suggested search refinements to 
    # reduce the result set intelligently.
    $in=join(",",$refs);
    $suggest=array();
    # find common keywords
    $refine=sql_query("select k.keyword,count(*) c from resource_keyword r join keyword k on r.keyword=k.ref and r.resource in ($in) and length(k.keyword)>=3 and length(k.keyword)<=15 and k.keyword not like '%0%' and k.keyword not like '%1%' and k.keyword not like '%2%' and k.keyword not like '%3%' and k.keyword not like '%4%' and k.keyword not like '%5%' and k.keyword not like '%6%' and k.keyword not like '%7%' and k.keyword not like '%8%' and k.keyword not like '%9%' group by k.keyword order by c desc limit 5");
    for ($n=0;$n<count($refine);$n++)
        {
        if (strpos($search,$refine[$n]["keyword"])===false)
            {
            $suggest[]=$search . " " . $refine[$n]["keyword"];
            }
        }
    return $suggest;
    }

if (!function_exists("get_advanced_search_fields")) {
function get_advanced_search_fields($archive=false, $hiddenfields="")
    {
    # Returns a list of fields suitable for advanced searching. 
    $return=array();

    $hiddenfields=explode(",",$hiddenfields);

    $fields=sql_query("select *, ref, name, title, type ,order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, tooltip_text, display_as_dropdown, display_condition from resource_type_field where advanced_search=1 and keywords_index=1 and length(name)>0 " . (($archive)?"":"and resource_type<>999") . " order by resource_type,order_by");
    # Apply field permissions and check for fields hidden in advanced search
    for ($n=0;$n<count($fields);$n++)
        {
        if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
            && !checkperm("f-" . $fields[$n]["ref"]) && !checkperm("T" . $fields[$n]["resource_type"]) && !in_array($fields[$n]["ref"], $hiddenfields))
            {$return[]=$fields[$n];}
        }

    return $return;
    }
}

function get_advanced_search_collection_fields($archive=false, $hiddenfields="")
    {
    # Returns a list of fields suitable for advanced searching. 
    $return=array();
   
    $hiddenfields=explode(",",$hiddenfields);

    $fields[]=Array ("ref" => "collection_title", "name" => "collectiontitle", "display_condition" => "", "tooltip_text" => "", "title"=>"Title", "type" => 0);
    $fields[]=Array ("ref" => "collection_keywords", "name" => "collectionkeywords", "display_condition" => "", "tooltip_text" => "", "title"=>"Keywords", "type" => 0);
    $fields[]=Array ("ref" => "collection_owner", "name" => "collectionowner", "display_condition" => "", "tooltip_text" => "", "title"=>"Owner", "type" => 0);
    # Apply field permissions and check for fields hidden in advanced search
    for ($n=0;$n<count($fields);$n++)
        {
    
        if (!in_array($fields[$n]["ref"], $hiddenfields))
        {$return[]=$fields[$n];}
        }
    
    return $return;
    }
function render_search_field($field,$value="",$autoupdate,$class="stdwidth",$forsearchbar=false,$limit_keywords=array())
    {
    # Renders the HTML for the provided $field for inclusion in a search form, for example the
    # advanced search page. Standard field titles are translated using $lang.  Custom field titles are i18n translated.
    #
    # $field    an associative array of field data, i.e. a row from the resource_type_field table.
    # $name     the input name to use in the form (post name)
    # $value    the default value to set for this field, if any

    node_field_options_override($field);
    
    global $auto_order_checkbox,$auto_order_checkbox_case_insensitive,$lang,$category_tree_open,$minyear,$daterange_search,$is_search,$values,$n;
    $name="field_" . $field["ref"];
    
    #Check if field has a display condition set
    $displaycondition=true;
    if ($field["display_condition"]!="")
        {
        $s=explode(";",$field["display_condition"]);
        $condref=0;
        foreach ($s as $condition) # Check each condition
            {
            $displayconditioncheck=false;
            $s=explode("=",$condition);
            global $fields;
            for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
                {
                if ($s[0]==$fields[$cf]["name"] && ($fields[$cf]["resource_type"]==0 || $fields[$cf]["resource_type"]==$field["resource_type"])) # this field needs to be checked
                    {
                    $scriptconditions[$condref]["field"] = $fields[$cf]["ref"];  # add new jQuery code to check value
                    $scriptconditions[$condref]['type'] = $fields[$cf]['type'];

                    //$scriptconditions[$condref]['options'] = $fields[$cf]['options'];

                    $scriptconditions[$condref]['node_options'] = array();
                    node_field_options_override($scriptconditions[$condref]['node_options'],$fields[$cf]['ref']);

                    $checkvalues=$s[1];
                    $validvalues=explode("|",strtoupper($checkvalues));
                    $scriptconditions[$condref]["valid"]= "\"";
                    $scriptconditions[$condref]["valid"].= implode("\",\"",$validvalues);
                    $scriptconditions[$condref]["valid"].= "\"";
                    if(isset($values[$fields[$cf]["name"]])) // Check if there is a matching value passed from search
                        {
                        $v=trim_array(explode(" ",strtoupper($values[$fields[$cf]["name"]])));
                        foreach ($validvalues as $validvalue)
                            {
                            if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value
                            }
                        }
                    if (!$displayconditioncheck) {$displaycondition=false;}
                    #add jQuery code to update on changes
                        if (($fields[$cf]['type'] == 2 || $fields[$cf]['type'] == 3) && $fields[$cf]['display_as_dropdown'] == 0) # add onchange event to each checkbox field
                            {
                            # construct the value from the ticked boxes
                            $val=","; # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                            //$options=trim_array(explode(",",$fields[$cf]["options"]));

                            $options=array();
                            node_field_options_override($options,$fields[$cf]['ref']);

                            ?><script type="text/javascript">
                            jQuery(document).ready(function() {<?php
                                for ($m=0;$m<count($options);$m++)
                                    {
                                    $checkname=$fields[$cf]["ref"] . "_" . md5($options[$m]);
                                    echo "
                                    jQuery('input[name=\"" . $checkname . "\"]').change(function (){
                                        checkDisplayCondition" . $field["ref"] . "();
                                        });";
                                    }
                                    ?>
                                });
                            </script><?php
                            }
                        # Handle Radio Buttons type:
                        else if($fields[$cf]['type'] == 12 && $fields[$cf]['display_as_dropdown'] == 0) {
                        ?>
                            <script type="text/javascript">
                            jQuery(document).ready(function() {
                                // Check for radio buttons (default behaviour)
                                jQuery('input[name=field_<?php echo $fields[$cf]["ref"]; ?>]:radio').change(function() {
                                    checkDisplayCondition<?php echo $field["ref"];?>();
                                });

                                <?php

                                $options=array();
                                node_field_options_override($options,$fields[$cf]['ref']);

                                //$options = trim_array(explode(',', ['options']));

                                foreach ($options as $option) {
                                    $name = 'field_' . $fields[$cf]['ref'] . '_' . sha1($option); ?>
                                    
                                    // Check for checkboxes (advanced search behaviour)
                                    jQuery('input[name=<?php echo $name; ?>]:checkbox').change(function() {
                                        checkDisplayCondition<?php echo $field['ref']; ?>();
                                    });

                                <?php
                                }
                                ?>
                            });
                            </script>

                        <?php 
                        } 
                        else
                            {
                            ?>
                            <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery('#field_<?php echo $fields[$cf]["ref"];?>').change(function (){
                                checkDisplayCondition<?php echo $field["ref"];?>();
                                });
                            });
                            </script>
                        <?php
                            }
                    }
                } # see if next field needs to be checked

            $condref++;
            } # check next condition

        ?>
        <script type="text/javascript">
        function checkDisplayCondition<?php echo $field["ref"];?>() {
            var questionField          = jQuery('#question_<?php echo $n; ?>');
            var fieldStatus            = questionField.css('display');
            var newFieldStatus         = 'none';
            var newFieldProvisional    = true;
            var newFieldProvisionalTest;
            <?php
            foreach ($scriptconditions as $scriptcondition)
                { ?>

                newFieldProvisionalTest = false;

                if (jQuery('#field_<?php echo $scriptcondition["field"]; ?>').length != 0) {
                    fieldValues<?php echo $scriptcondition["field"]; ?> = jQuery('#field_<?php echo $scriptcondition["field"]; ?>').val().toUpperCase().split(',');
                } else {
                <?php
                    # Handle Radio Buttons type:
                    if($scriptcondition['type'] == 12) 
                        {
                        //$scriptcondition["options"] = explode(',', $scriptcondition["options"]);

                        $scriptcondition["options"]=array();
                        node_field_options_override($scriptcondition["options"],$scriptcondition["field"]);

                        foreach ($scriptcondition["options"] as $key => $radio_button_value)
                            {
                            $scriptcondition["options"][$key] = sha1($radio_button_value);
                            }
                        $scriptcondition["options"] = implode(',', $scriptcondition["options"]);

                        ?>

                        var options_string = '<?php echo $scriptcondition["options"]; ?>';
                        var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
                        var checked = null;
                        var fieldOkValues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

                        for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++) {
                            if(jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) {
                                checked = jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val().toUpperCase();
                                if(jQuery.inArray(checked, fieldOkValues<?php echo $scriptcondition["field"]; ?>) > -1) {
                                    newFieldProvisionalTest = true;
                                }
                            }
                        }

                        <?php
                        } # end of handling radio buttons type
                        ?>

                    fieldValues<?php echo $scriptcondition["field"]; ?> = new Array();
                    checkedVals<?php echo $scriptcondition["field"]; ?> = jQuery('input[name^=<?php echo $scriptcondition["field"]; ?>_]');
                
                    jQuery.each(checkedVals<?php echo $scriptcondition["field"]; ?>, function() {
                        if (jQuery(this).is(':checked')) {
                            checkText<?php echo $scriptcondition["field"]; ?> = jQuery(this).parent().next().text().toUpperCase();
                            fieldValues<?php echo $scriptcondition["field"]; ?>.push(jQuery.trim(checkText<?php echo $scriptcondition["field"]; ?>));
                        }
                    });
                }
                    
                fieldOkValues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];
                jQuery.each(fieldValues<?php echo $scriptcondition["field"]; ?>,function(f,v) {
                    if ((jQuery.inArray(v,fieldOkValues<?php echo $scriptcondition["field"]; ?>))>-1 || (fieldValues<?php echo $scriptcondition["field"]; ?> == fieldOkValues<?php echo $scriptcondition["field"]; ?> )) {
                        newFieldProvisionalTest = true;
                    }
                });

                if (newFieldProvisionalTest == false) {
                    newFieldProvisional = false;
                }
                    
                <?php
                } ?>
            
            if (newFieldProvisional == true) {
                newFieldStatus = 'block'
            }
            if (newFieldStatus != fieldStatus) {
                questionField.slideToggle();
                if (questionField.css('display') == 'block') {
                    questionField.css('border-top','');
                } else {
                    questionField.css('border-top','none');
                }
            }
        }
        </script>
    <?php
        }

    $is_search = true;

    if (!$forsearchbar)
        {
        ?>
        <div class="Question" id="question_<?php echo $n ?>" <?php if (!$displaycondition) {?>style="display:none;border-top:none;"<?php } ?><?php
        if (strlen($field["tooltip_text"])>=1)
            {
            echo "title=\"" . htmlspecialchars(lang_or_i18n_get_translated($field["tooltip_text"], "fieldtooltip-")) . "\"";
            }
        ?>>
        <label><?php echo htmlspecialchars(lang_or_i18n_get_translated($field["title"], "fieldtitle-")) ?></label>
        <?php
        }
    else
        {
        ?>
        <div class="SearchItem">
        <?php echo htmlspecialchars(lang_or_i18n_get_translated($field["title"], "fieldtitle-")) ?></br>
        <?php
        }

    //hook("rendersearchhtml", "", array($field, $class, $value, $autoupdate));

    switch ($field["type"]) {
        case 0: # -------- Text boxes
        case 1:
        case 5:
        case 8:
        ?><input class="<?php echo $class ?>" type=text name="field_<?php echo $field["ref"]?>" id="field_<?php echo $field["ref"]?>" value="<?php echo htmlspecialchars($value)?>" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?> onKeyPress="if (!(updating)) {setTimeout('UpdateResultCount()',2000);updating=true;}"><?php
        break;
    
        case 2: 
        case 3:
        if(!hook("customchkboxes", "", array($field, $value, $autoupdate, $class, $forsearchbar, $limit_keywords)))
            {
            # -------- Show a check list or dropdown for dropdowns and check lists?
            # By default show a checkbox list for both (for multiple selections this enabled OR functionality)
            
            # Translate all options
            $adjusted_dropdownoptions=hook("adjustdropdownoptions");
            if ($adjusted_dropdownoptions){$options=$adjusted_dropdownoptions;}
            
            $option_trans=array();
            $option_trans_simple=array();
            for ($m=0;$m<count($field["node_options"]);$m++)
                {
                $trans=i18n_get_translated($field["node_options"][$m]);
                $option_trans[$field["node_options"][$m]]=$trans;
                $option_trans_simple[]=$trans;
                }

            if ($auto_order_checkbox && !hook("ajust_auto_order_checkbox","",array($field))) {
                if($auto_order_checkbox_case_insensitive){natcasesort($option_trans);}
                else{asort($option_trans);}
            }
            $options=array_keys($option_trans); # Set the options array to the keys, so it is now effectively sorted by translated string       
            
            if ($field["display_as_dropdown"])
                {
                # Show as a dropdown box
                $set=trim_array(explode(";",cleanse_string($value,true)));
                ?><select class="<?php echo $class ?>" name="field_<?php echo $field["ref"]?>" id="field_<?php echo $field["ref"]?>" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>><option value=""></option><?php
                foreach ($option_trans as $option=>$trans)
                    {
                    if (trim($trans)!="")
                        {
                        ?>
                        <option value="<?php echo htmlspecialchars(trim($trans))?>" <?php if (in_array(cleanse_string($trans,true),$set)) {?>selected<?php } ?>><?php echo htmlspecialchars(trim($trans))?></option>
                        <?php
                        }
                    }
                ?></select><?php
                }
            else
                {
                # Show as a checkbox list (default)
                
                $set=trim_array(explode(";",cleanse_string($value,true)));
                $wrap=0;

                $l=average_length($option_trans_simple);
                $cols=10;
                if ($l>5)  {$cols=6;}
                if ($l>10) {$cols=4;}
                if ($l>15) {$cols=3;}
                if ($l>25) {$cols=2;}
                # Filter the options array for blank values and ignored keywords.
                $newoptions=array();
                foreach ($options as $option)
                    {
                    if ($option!=="" && (count($limit_keywords)==0 || in_array(strval($option), $limit_keywords)))
                        {
                        $newoptions[]=$option;
                        }
                    }
					
                $options=$newoptions;
				
                $height=ceil(count($options)/$cols);

                global $checkbox_ordered_vertically, $checkbox_vertical_columns;
                if ($checkbox_ordered_vertically)
                    {                   
                    if(!hook('rendersearchchkboxes'))
                        {
                        # ---------------- Vertical Ordering (only if configured) -----------
                        ?><table cellpadding=2 cellspacing=0><tr><?php
                        for ($y=0;$y<$height;$y++)
                            {
                            for ($x=0;$x<$cols;$x++)
                                {
                                # Work out which option to fetch.
                                $o=($x*$height)+$y;
                                if ($o<count($options))
                                    {
                                    $option=$options[$o];
                                    $trans=$option_trans[$option];

                                    $name=$field["ref"] . "_" . md5($option);
                                    if ($option!=="")
                                        {
                                        ?>
                                        <td valign=middle><input type=checkbox id="<?php echo htmlspecialchars($name) ?>" name="<?php echo ($name) ?>" value="yes" <?php if (in_array(cleanse_string($trans,true),$set)) {?>checked<?php } ?> <?php if ($autoupdate) { ?>onClick="UpdateResultCount();"<?php } ?>></td><td valign=middle><?php echo htmlspecialchars($trans)?>&nbsp;&nbsp;</td>

                                        <?php
                                        }
                                    else
                                        {
                                        ?><td></td><td></td><?php
                                        }
                                    }
                                }?></tr><tr><?php
                            }
                        ?></tr></table><?php
                        }
                    }
                else
                    {
                    # ---------------- Horizontal Ordering (Standard) ---------------------             
                    ?><table cellpadding=2 cellspacing=0><tr><?php
                    foreach ($option_trans as $option=>$trans)
                        {
                        $wrap++;if ($wrap>$cols) {$wrap=1;?></tr><tr><?php }
                        $name=$field["ref"] . "_" . md5($option);
                        if ($option!=="")
                            {
                            ?>
                            <td valign=middle><input type=checkbox id="<?php echo htmlspecialchars($name) ?>" name="<?php echo htmlspecialchars($name) ?>" value="yes" <?php if (in_array(cleanse_string(i18n_get_translated($option),true),$set)) {?>checked<?php } ?> <?php if ($autoupdate) { ?>onClick="UpdateResultCount();"<?php } ?>></td><td valign=middle><?php echo htmlspecialchars($trans)?>&nbsp;&nbsp;</td>
                            <?php
                            }
                        }
                    ?></tr></table><?php
                    }
                    
                }
            }
        break;
        
        case 4:
        case 6: 
        case 10: # ----- Date types
        $found_year='';$found_month='';$found_day='';$found_start_year='';$found_start_month='';$found_start_day='';$found_end_year='';$found_end_month='';$found_end_day='';
        if ($daterange_search)
            {
            $startvalue=substr($value,strpos($value,"start")+5,10);
            $ss=explode(" ",$startvalue);
            if (count($ss)>=3)
                {
                $found_start_year=$ss[0];
                $found_start_month=$ss[1];
                $found_start_day=$ss[2];
                }
            $endvalue=substr($value,strpos($value,"end")+3,10);
            $se=explode(" ",$endvalue);
            if (count($se)>=3)
                {
                $found_end_year=$se[0];
                $found_end_month=$se[1];
                $found_end_day=$se[2];
                }
            ?>
            <!--  date range search start -->           
            <div><label class="InnerLabel"><?php echo $lang["fromdate"]?></label>
            <select name="<?php echo htmlspecialchars($name) ?>_startyear" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$y;$d>=$minyear;$d--)
                {
                ?><option <?php if ($d==$found_start_year) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_startmonth" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_start_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_startday" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyday"]?></option>
              <?php
              for ($d=1;$d<=31;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_start_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
                }
              ?>
            </select>   
            </div><br><div><label></label><label class="InnerLabel"><?php echo $lang["todate"]?></label><select name="<?php echo $name?>_endyear" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$y;$d>=$minyear;$d--)
                {
                ?><option <?php if ($d==$found_end_year ) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_endmonth" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              $md=date("n");
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_end_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_endday" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyday"]?></option>
              <?php
              $td=date("d");
              for ($d=1;$d<=31;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_end_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
                }
              ?>
            </select>
            <!--  date range search end date-->         
            </div>
            <?php }
        else
            {
            $s=explode("|",$value);
            if (count($s)>=3)
            {
            $found_year=$s[0];
            $found_month=$s[1];
            $found_day=$s[2];
            }
            ?>      
            <select name="<?php echo $name?>_year" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyyear"]?></option>
              <?php
              $y=date("Y");
              for ($d=$minyear;$d<=$y;$d++)
                {
                ?><option <?php if ($d==$found_year) { ?>selected<?php } ?>><?php echo $d?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_month" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anymonth"]?></option>
              <?php
              for ($d=1;$d<=12;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_month) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $lang["months"][$d-1]?></option><?php
                }
              ?>
            </select>
            <select name="<?php echo $name?>_day" class="SearchWidth" style="width:100px;" <?php if ($autoupdate) { ?>onChange="UpdateResultCount();"<?php } ?>>
              <option value=""><?php echo $lang["anyday"]?></option>
              <?php
              for ($d=1;$d<=31;$d++)
                {
                $m=str_pad($d,2,"0",STR_PAD_LEFT);
                ?><option <?php if ($d==$found_day) { ?>selected<?php } ?> value="<?php echo $m?>"><?php echo $m?></option><?php
                }
              ?>
            </select>
            <?php }
                    
        break;
        
        
        case 7: # ----- Category Tree
        $options=$field["options"];

        //$set=trim_array(explode(";",cleanse_string($value,true)));
        $set=preg_split('/[;\|]/',cleanse_string($value,true));

        if ($forsearchbar)
            {
            # On the search bar?
            # Produce a smaller version of the category tree in a single dropdown - max two levels
            ?>
            <select class="<?php echo $class ?>" name="field_<?php echo $field["ref"]?>"><option value=""></option><?php
            $class=explode("\n",$options);

            for ($t=0;$t<count($class);$t++)
                {
                $s=explode(",",$class[$t]);
                if (count($s)==3 && $s[1]==0)
                    {
                    # Found a first level
                    ?>
                    <option <?php if (in_array(cleanse_string($s[2],true),$set)) {?>selected<?php } ?>><?php echo htmlspecialchars($s[2]) ?></option>
                    <?php
                    
                    # Parse tree again looking for level twos at this point
                    for ($u=0;$u<count($class);$u++)
                        {
                        $v=explode(",",$class[$u]);
                        if (count($v)==3 && $v[1]==$s[0])
                            {
                            # Found a first level
                            ?>
                            <option value="<?php echo htmlspecialchars($s[2]) . "," . htmlspecialchars($v[2]) ?>" <?php if (in_array(cleanse_string($s[2],true),$set) && in_array(cleanse_string($v[2],true),$set)) {?>selected<?php } ?>>&nbsp;-&nbsp;<?php echo htmlspecialchars($v[2]) ?></option>
                            <?php
                            }                       
                        }
                    }
                }           
            ?>
            </select>
            <?php
            }
        else
            {
            # For advanced search and elsewhere, include the category tree.
            include "../pages/edit_fields/7.php";
            }
        break;
        
        case 9: #-- Dynamic keywords list
        $value=str_replace(";",",",$value); # Different syntax used for keyword separation when searching.
        include "../pages/edit_fields/9.php";
        break;      

        // Radio buttons:
        case 12:
            // auto save is not needed when searching
            $edit_autosave = FALSE;
             
            $display_as_radiobuttons = FALSE;
            $display_as_checkbox = TRUE;

            if($field['display_as_dropdown']) {
                $display_as_dropdown = TRUE;
                $display_as_checkbox = FALSE;
            }
            
            include '../pages/edit_fields/12.php';
        break;
        }
    ?>
    <div class="clearerleft"> </div>
    </div>
    <?php
    }

function search_form_to_search_query($fields,$fromsearchbar=false)
    {
    # Take the data in the the posted search form that contained $fields, and assemble
    # a search query string that can be used for a standard search.
    #
    # This is used to take the advanced search form and assemble it into a search query.
    
    global $auto_order_checkbox,$checkbox_and;
    $search="";
    if (getval("year","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="year:" . getval("year","");   
        }
    if (getval("month","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="month:" . getval("month",""); 
        }
    if (getval("day","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="day:" . getval("day",""); 
        }
    if (getval("startdate","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="startdate:" . getval("startdate","");         
        }
    if (getval("enddate","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="enddate:" . getval("enddate",""); 
        }
    if (getval("startyear","")!="")
        {       
        if ($search!="") {$search.=", ";}
        $search.="startdate:" . getval("startyear","");
        if (getval("startmonth","")!="")
            {
            $search.="-" . getval("startmonth","");
            if (getval("startday","")!="")
                {
                $search.="-" . getval("startday","");
                }
            else
                {
                $search.="-01";
                }
            }
        else
            {
            $search.="-01-01";
            }
        }   
    if (getval("endyear","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.="enddate:" . getval("endyear","");
        if (getval("endmonth","")!="")
            {
            $search.="-" . getval("endmonth","");
            if (getval("endday","")!="")
                {
                $search.="-" . getval("endday","");
                }
            else
                {
                $search.="-31";
                }
            }
        else
            {
            $search.="-12-31";
            }
        }
    if (getval("allfields","")!="")
        {
        if ($search!="") {$search.=", ";}
        $search.=join(", ",explode(" ",getvalescaped("allfields",""))); # prepend 'all fields' option
        }
    if (getval("resourceids","")!="")
        {
        $listsql="!list" . join(":",trim_array(split_keywords(getvalescaped("resourceids",""))));
        $search=$listsql . " " . $search;
        }

    // Disabled as was killing search
    //$tmp = hook("richeditsearchquery", "", array($search, $fields, $n)); if($tmp) $search .= $tmp;
    
    for ($n=0;$n<count($fields);$n++)
        {
        switch ($fields[$n]["type"])
            {
            case 0: # -------- Text boxes
            case 1:
            case 5:
            case 8:
            $name="field_" . $fields[$n]["ref"];
            $value=getvalescaped($name,"");
            if ($value!="")
                {
                $vs=split_keywords($value);
                for ($m=0;$m<count($vs);$m++)
                    {
                    if ($search!="") {$search.=", ";}
                    $search.=$fields[$n]["name"] . ":" . strtolower($vs[$m]);
                    }
                }
            break;
            
            case 2: # -------- Dropdowns / check lists
            case 3:                
            if ($fields[$n]["display_as_dropdown"])
                {
                # Process dropdown box
                $name="field_" . $fields[$n]["ref"];
                $value=getvalescaped($name,"");
                if ($value!=="")
                    {
                    /*
                    $vs=split_keywords($value);
                    for ($m=0;$m<count($vs);$m++)
                        {
                        if ($search!="") {$search.=", ";}
                        $search.=$fields[$n]["name"] . ":" . strtolower($vs[$m]);
                        }
                    */
                    if ($search!="") {$search.=", ";}
                    $search.=$fields[$n]["name"] . ":" . $value;                    
                    }
                }
            else
                {
                # Process checkbox list
                //$options=trim_array(explode(",",$fields[$n]["options"]));
                $options=array();                
                node_field_options_override($options,$fields[$n]['ref']);

                $p="";
                $c=0;
                for ($m=0;$m<count($options);$m++)
                    {
                    $name=$fields[$n]["ref"] . "_" . md5($options[$m]);
                    $value=getvalescaped($name,"");
                    if ($value=="yes")
                        {
                        $c++;
                        if ($p!="") {$p.=";";}
                        $p.=mb_strtolower(i18n_get_translated($options[$m]), 'UTF-8');
                        }
                    }
                if (($c==count($options) && !$checkbox_and) && (count($options)>1))
                    {
                    # all options ticked - omit from the search (unless using AND matching, or there is only one option intended as a boolean selection)
                    $p="";
                    }
                if ($p!="")
                    {
                    if ($search!="") {$search.=", ";}
					if($checkbox_and)
						{
						$p=str_replace(";",", {$fields[$n]["name"]}:",$p);	// this will force each and condition into a separate union in do_search (which will AND)
						}
                    $search.=$fields[$n]["name"] . ":" . $p;
                    }
                }
            break;

            case 4: # --------  Date and optional time
            case 6: # --------  Expiry Date
            case 10: #--------  Date
            $name="field_" . $fields[$n]["ref"];
            $datepart="";
            $value="";
            if (strpos($search, $name.":")===false) 
                {
                $key_year=$name."_year";
                $value_year=getvalescaped($key_year,"");
                if ($value_year!="") $value=$value_year;
                else $value="nnnn";
                
                $key_month=$name."_month";
                $value_month=getvalescaped($key_month,"");
                if ($value_month=="") $value_month.="nn";
                
                $key_day=$name."_day";
                $value_day=getvalescaped($key_day,"");
                if ($value_day!="") $value.="|" . $value_month . "|" . $value_day;
                elseif ($value_month!="nn") $value.="|" . $value_month;
                
                if (($value!=="nnnn|nn|nn")&&($value!=="nnnn")) 
                    {
                    if ($search!="") {$search.=", ";}
                    $search.=$fields[$n]["name"] . ":" . $value;
                    }
                

                }
//          if (getval($name . "_year","")!="")
//              {
//              $datepart.=getval($name . "_year","");
//              if (getval($name . "_month","")!="")
//                  {
//                  $datepart.="-" . getval($name . "_month","");
//                  if (getval($name . "_day","")!="")
//                      {
//                      $datepart.="-" . getval($name . "_day","");
//                      }
//                  }
//              }           
                
            #Date range search -  start date
            if (getval($name . "_startyear","")!="")
                {
                $datepart.= "start" . getval($name . "_startyear","");
                if (getval($name . "_startmonth","")!="")
                    {
                    $datepart.="-" . getval($name . "_startmonth","");
                    if (getval($name . "_startday","")!="")
                        {
                        $datepart.="-" . getval($name . "_startday","");
                        }
                    else
                        {
                        $datepart.="-01";
                        }
                    }
                else
                    {
                    $datepart.="-01-01";
                    }
                }           
                
            #Date range search -  end date  
            if (getval($name . "_endyear","")!="")
                {
                $datepart.= "end" . getval($name . "_endyear","");
                if (getval($name . "_endmonth","")!="")
                    {
                    $datepart.="-" . getval($name . "_endmonth","");
                    if (getval($name . "_endday","")!="")
                        {
                        $datepart.="-" . getval($name . "_endday","");
                        }
                    else
                        {
                        $datepart.="-31";
                        }
                    }
                else
                    {
                    $datepart.="-12-31";
                    }
                }   
                
            if ($datepart!="")
                {               
                if ($search!="") {$search.=", ";}
                $search.=$fields[$n]["name"] . ":range" . $datepart;
                }

            break;

            case 7:  # -------- Category tree
            $name="field_" . $fields[$n]["ref"];
            $value=getvalescaped($name,"");
            $selected=trim_array(explode(",",$value));
            $p="";
            for ($m=0;$m<count($selected);$m++)
                {
                if ($selected[$m]!="")
                    {
                    if ($p!="") {$p.=";";}
                    $p.=$selected[$m];
                    }

                # Resolve keywords to make sure that the value has been indexed prior to including in the search string.
                $keywords=split_keywords($selected[$m]);
                foreach ($keywords as $keyword) {resolve_keyword($keyword,true);}
                }
            if ($p!="")
                {
                if ($search!="") {$search.=", ";}
                $search.=$fields[$n]["name"] . ":" . $p;
                }
            break;
        
            case 9: # -------- Dynamic keywords
            $name="field_" . $fields[$n]["ref"];
            $value=getvalescaped($name,"");
            $selected=trim_array(explode("|",$value));
            $p="";
            for ($m=0;$m<count($selected);$m++)
                {
                if ($selected[$m]!="")
                    {
                    if ($p!="") {$p.=";";}
                    $p.=$selected[$m];
                    }

                # Resolve keywords to make sure that the value has been indexed prior to including in the search string.
                $keywords=split_keywords($selected[$m]);
                foreach ($keywords as $keyword) {resolve_keyword($keyword,true);}
                }
            if ($p!="")
                {
                if ($search!="") {$search.=", ";}
                $search.=$fields[$n]["name"] . ":" . $p;
                }
            break;
        
            // Radio buttons:
            case 12:
                if($fields[$n]['display_as_dropdown']) {
                    
                    // Process dropdown or checkboxes behaviour (with only one option ticked):
                    $value = getvalescaped('field_' . $fields[$n]['ref'], '');
                    if($value != '') {
                        if ($search != '') { 
                            $search .= ', ';
                        }
                        $search .= $fields[$n]['name'] . ':' . $value;
                    }
                
                } else {

                    //Process checkbox behaviour (multiple options selected create a logical AND condition):
                    //$options = trim_array(explode(',', $fields[$n]['options']));

                    $options=array();
                    node_field_options_override($options,$fields[$n]['ref']);
                    
                    $p = '';
                    $c = 0;
                    foreach ($options as $option) {
                        $name = 'field_' . $fields[$n]['ref'] . '_' . sha1($option);
                        $value = getvalescaped($name, '');

                        if($value == $option) {
                            if($p != '' || ($p=='' && emptyiszero($value)))
                                {
                                $c++;
                                $p .= ';';
                                }
                            $p .= mb_strtolower(i18n_get_translated($option), 'UTF-8');
                        }
                    }
        
                    // All options ticked - omit from the search (unless using AND matching, or there is only one option intended as a boolean selection)
                    if(($c == count($options) && !$checkbox_and) && (count($options) > 1)) {
                        $p = '';
                    }

                    if($p != '') {
                        if($search != '') {
                            $search .= ', ';
                        }
						if($checkbox_and)
							{
							$p=str_replace(";",", {$fields[$n]["name"]}:",$p);	// this will force each and condition into a separate union in do_search (which will AND)
							}
                        $search .= $fields[$n]['name'] . ':' . $p;
                    }

                }
            break;
            }
        }
		
        $propertysearchcodes=array();
        global $advanced_search_properties;
        foreach($advanced_search_properties as $advanced_search_property=>$code)
            {
            $propval=getvalescaped($advanced_search_property,"");
            if($propval!="")
                {$propertysearchcodes[] =$code . ":" . $propval;}
            }
        if(count($propertysearchcodes)>0)
            {
            $search = '!properties' . implode(';', $propertysearchcodes) . ' ' . $search;
            }
        else
            {
            // Allow a single special search to be prepended to the search string.  For example, !contributions<user id>
            foreach ($_POST as $key=>$value)
                {
                if ($key[0]=='!' && strlen($value) > 0)
                    {
                    $search=$key . $value . ',' . $search;
                    //break;
                    }
                }
            }
            
        return $search;
    }

if (!function_exists("refine_searchstring")){
function refine_searchstring($search){
    #
    # DISABLED TEMPORARILY
    #
    # This causes an issue when using advanced search with check boxes.
    # A keyword containing spaces will break the search when used with another keyword. 
    #
    
    # This function solves several issues related to searching.
    # it eliminates duplicate terms, helps the field content to carry values over into advanced search correctly, fixes a searchbar bug where separators (such as in a pasted filename) cause an initial search to fail, separates terms for searchcrumbs.
    
    global $use_refine_searchstring;
    
    if (!$use_refine_searchstring){return $search;}
    
    if (substr($search,0,1)=="\"" && substr($search,-1,1)=="\"") {return $search;} // preserve string search functionality.
    
    global $noadd;
    $search=str_replace(",-",", -",$search);
    $search=str_replace ("\xe2\x80\x8b","",$search);// remove any zero width spaces.
    
    $keywords=split_keywords($search);

    $orfields=get_OR_fields(); // leave checkbox type fields alone
    
    $fixedkeywords=array();
    foreach ($keywords as $keyword){
        if (strpos($keyword,"startdate")!==false || strpos($keyword,"enddate")!==false)
            {$keyword=str_replace(" ","-",$keyword);}
        if (strpos($keyword,":")>0){
            $keywordar=explode(":",$keyword,2);
            $keyname=$keywordar[0];
            if (substr($keyname,0,1)!="!"){
                if(substr($keywordar[1],0,5)=="range"){$keywordar[1]=str_replace(" ","-",$keywordar[1]);}
                if (!in_array($keyname,$orfields)){
                    $keyvalues=explode(" ",str_replace($keywordar[0].":","",$keywordar[1]));
                } else {
                    $keyvalues=array($keywordar[1]);
                }
                foreach ($keyvalues as $keyvalue){
                    if (!in_array($keyvalue,$noadd)){ 
                        $fixedkeywords[]=$keyname.":".$keyvalue;
                    }
                }
            }
            else if (!in_array($keyword,$noadd)){
                $keywords=explode(" ",$keyword);
                $fixedkeywords[]=$keywords[0];} // for searches such as !list
        }
        else {
            if (!in_array($keyword,$noadd)){ 
                $fixedkeywords[]=$keyword;
            }
        }
    }
    $keywords=$fixedkeywords;
    $keywords=array_unique($keywords);
    $search=implode(", ",$keywords);
    $search=str_replace(",-"," -",$search); // support the omission search
    return $search;
}
}

function compile_search_actions($top_actions)
    {
    $options = array();
	$o=0;

    global $baseurl_short, $lang, $k, $search, $restypes, $order_by, $archive, $sort, $daylimit, $home_dash, $url,
           $allow_smart_collections, $resources_count, $show_searchitemsdiskusage, $offset, $allow_save_search,
           $collection, $usercollection, $internal_share_access;

	if(!isset($internal_share_access)){$internal_share_access=false;}
	

    // globals that could also be passed as a reference
    global $starsearch;

    if(!checkperm('b') && ($k == '' || $internal_share_access)) 
        {
        if($top_actions && $allow_save_search && $usercollection != $collection)
            {
            $extra_tag_attributes = sprintf('
                    data-url="%spages/collections.php?addsearch=%s&restypes=%s&archive=%s&daylimit=%s"
                ',
                $baseurl_short,
                urlencode($search),
                urlencode($restypes),
                urlencode($archive),
                urlencode($daylimit)
            );

            $options[$o]['value']='save_search_to_collection';
			$options[$o]['label']=$lang['savethissearchtocollection'];
			$options[$o]['data_attr']=array();
			$options[$o]['extra_tag_attributes']=$extra_tag_attributes;
			$o++;
            }

        #Home_dash is on, AND NOT Anonymous use, AND (Dash tile user (NOT with a managed dash) || Dash Tile Admin)
        if($top_actions && $home_dash && checkPermission_dashcreate())
            {
            $option_name = 'save_search_to_dash';
            $data_attribute = array(
                'url'  => $baseurl_short . 'pages/dash_tile.php?create=true&tltype=srch&freetext=true"',
                'link' => $url
            );

            if(substr($search, 0, 11) == '!collection')
                {
                $option_name = 'save_collection_to_dash';
                $data_attribute['url'] = sprintf('
                    %spages/dash_tile.php?create=true&tltype=srch&promoted_resource=true&freetext=true&all_users=1&link=/pages/search.php?search=%s&order_by=%s&sort=%s
                    ',
                    $baseurl_short,
                    $search,
                    $order_by,
                    $sort
                );
                }

            $options[$o]['value']=$option_name;
			$options[$o]['label']=$lang['savethissearchtodash'];
			$options[$o]['data_attr']=$data_attribute;
			$o++;
            }

        // Save search as Smart Collections
        if($allow_smart_collections && substr($search, 0, 11) != '!collection')
            {
            $extra_tag_attributes = sprintf('
                    data-url="%spages/collections.php?addsmartcollection=%s&restypes=%s&archive=%s&starsearch=%s"
                ',
                $baseurl_short,
                urlencode($search),
                urlencode($restypes),
                urlencode($archive),
                urlencode($starsearch)
            );

            $options[$o]['value']='save_search_smart_collection';
			$options[$o]['label']=$lang['savesearchassmartcollection'];
			$options[$o]['data_attr']=array();
			$options[$o]['extra_tag_attributes']=$extra_tag_attributes;
			$o++;
            }

        /*// Wasn't able to see this working even in the old code
        // so I left it here for reference. Just uncomment it and it should work
        global $smartsearch;
        if($allow_smart_collections && substr($search, 0, 11) == '!collection' && (is_array($smartsearch[0]) && !empty($smartsearch[0])))
            {
            $smartsearch = $smartsearch[0];

            $extra_tag_attributes = sprintf('
                    data-url="%spages/search.php?search=%s&restypes=%s&archive=%s&starsearch=%s&daylimit=%s"
                ',
                $baseurl_short,
                urlencode($smartsearch['search']),
                urlencode($smartsearch['restypes']),
                urlencode($smartsearch['archive']),
                urlencode($smartsearch['starsearch']),
                urlencode($daylimit)
            );

            $options[$o]['value']='do_saved_search';
			$options[$o]['label']=$lang['dosavedsearch'];
			$options[$o]['data_attr']=array();
			$options[$o]['extra_tag_attributes']=$extra_tag_attributes;
			$o++;
            }*/

        if($resources_count != 0)
            {
            if($usercollection != $collection)
                {
                $extra_tag_attributes = sprintf('
                        data-url="%spages/collections.php?addsearch=%s&restypes=%s&archive=%s&mode=resources&daylimit=%s"
                    ',
                    $baseurl_short,
                    urlencode($search),
                    urlencode($restypes),
                    urlencode($archive),
                    urlencode($daylimit)
                );

                $options[$o]['value']='save_search_items_to_collection';
    			$options[$o]['label']=$lang['savesearchitemstocollection'];
    			$options[$o]['data_attr']=array();
    			$options[$o]['extra_tag_attributes']=$extra_tag_attributes;
    			$o++;
                }

            if($show_searchitemsdiskusage) 
                {
                $extra_tag_attributes = sprintf('
                        data-url="%spages/search_disk_usage.php?search=%s&restypes=%s&offset=%s&order_by=%s&sort=%s&archive=%s&daylimit=%s&k=%s"
                    ',
                    $baseurl_short,
                    urlencode($search),
                    urlencode($restypes),
                    urlencode($offset),
                    urlencode($order_by),
                    urlencode($sort),
                    urlencode($archive),
                    urlencode($daylimit),
                    urlencode($k)
                );

                $options[$o]['value']='search_items_disk_usage';
				$options[$o]['label']=$lang['searchitemsdiskusage'];
				$options[$o]['data_attr']=array();
				$options[$o]['extra_tag_attributes']=$extra_tag_attributes;
				$o++;
                }
            }
        }

    if($top_actions && ($k == '' || $internal_share_access))
        {
        $options[$o]['value']            = 'csv_export_results_metadata';
		$options[$o]['label']            = $lang['csvExportResultsMetadata'];
		$options[$o]['data_attr']['url'] = sprintf('%spages/csv_export_results_metadata.php?search=%s&restype=%s&order_by=%s&archive=%s&sort=%s&starsearch=%s',
            $baseurl_short,
            urlencode($search),
            urlencode($restypes),
            urlencode($order_by),
            urlencode($archive),
            urlencode($sort),
            urlencode($starsearch)
        );

		$o++;
        }

    // Add extra search actions or modify existing options through plugins
    $modified_options = hook('render_search_actions_add_option','',array($options));
	if($top_actions && !empty($modified_options))
		{
        $options=$modified_options;
		}

    return $options;
    }

function search_filter($search,$archive,$restypes,$starsearch,$recent_search_daylimit,$access_override,$return_disk_usage)
	{
	# Convert the provided search parameters into appropriate SQL, ready for inclusion in the do_search() search query.
	
	 # Start with an empty string = an open query.
	$sql_filter="";
	
	# Apply resource types
	if (($restypes!="")&&(substr($restypes,0,6)!="Global") && substr($search, 0, 11) != '!collection')
	    {
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    $restypes_x=explode(",",$restypes);
	    $sql_filter.="resource_type in ('" . join("','",$restypes_x) . "')";
	    }
	
	# Apply star search
	if ($starsearch!="" && $starsearch!=0 && $starsearch!=-1)
	    {
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    $sql_filter.="user_rating >= '$starsearch'";
	    }   
	if ($starsearch==-1)
	    {
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    $sql_filter.="user_rating = '-1'";
	    }
	
	# Apply day limit
	if($recent_search_daylimit!="")
	    {
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    $sql_filter.= "creation_date > (curdate() - interval " . $recent_search_daylimit . " DAY)";
	    }
	
	# The ability to restrict access by the user that created the resource.
	global $resource_created_by_filter;
	if (isset($resource_created_by_filter) && count($resource_created_by_filter)>0)
	    {
	    $created_filter="";
	    foreach ($resource_created_by_filter as $filter_user)
		{
		if ($filter_user==-1) {global $userref;$filter_user=$userref;} # '-1' can be used as an alias to the current user. I.e. they can only see their own resources in search results.
		if ($created_filter!="") {$created_filter.=" or ";} 
		$created_filter.= "created_by = '" . $filter_user . "'";
		}    
	    if ($created_filter!="")
		{
		if ($sql_filter!="") {$sql_filter.=" and ";}            
		$sql_filter.="(" . $created_filter . ")";
		}
	    }
	
	
	# Geo zone exclusion
	# A list of upper/lower long/lat bounds, defining areas that will be excluded from geo search results.
	# Areas are defined as southwest lat, southwest long, northeast lat, northeast long
	global $geo_search_restrict;    
	if (count($geo_search_restrict)>0 && substr($search,0,4)=="!geo")
	    {
	    foreach ($geo_search_restrict   as $zone)
		{
		if ($sql_filter!="") {$sql_filter.=" and ";}
		$sql_filter.= "(geo_lat is null or geo_long is null or not(geo_lat >= '" . $zone[0] . "' and geo_lat<= '" . $zone[2] . "'";
		$sql_filter.= "and geo_long >= '" . $zone[1] . "' and geo_long<= '" . $zone[3] . "'))";
		}
	    }
	
	# append resource type restrictions based on 'T' permission 
	# look for all 'T' permissions and append to the SQL filter.
	global $userpermissions;
	$rtfilter=array();
	for ($n=0;$n<count($userpermissions);$n++)
	    {
	    if (substr($userpermissions[$n],0,1)=="T")
		{
		$rt=substr($userpermissions[$n],1);
		if (is_numeric($rt)&&!$access_override) {$rtfilter[]=$rt;}
		}
	    }
	if (count($rtfilter)>0)
	    {
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    $sql_filter.="resource_type not in (" . join(",",$rtfilter) . ")";
	    }
	
	# append "use" access rights, do not show confidential resources unless admin
	if (!checkperm("v")&&!$access_override)
	    {
	    global $userref;
	    if ($sql_filter!="") {$sql_filter.=" and ";}
	    # Check both the resource access, but if confidential is returned, also look at the joined user-specific or group-specific custom access for rows.
	    $sql_filter.="(r.access<>'2' or (r.access=2 and ((rca.access is not null and rca.access<>2) or (rca2.access is not null and rca2.access<>2))))";
	    }
	    
	# append archive searching. Updated Jan 2016 to apply to collections as resources in a pending state that are in a shared collection could bypass approval process
	if (!$access_override)
	    {
	    global $pending_review_visible_to_all,$search_all_workflow_states, $userref, $pending_submission_searchable_to_all;
	    if(substr($search,0,11)=="!collection" || substr($search,0,5)=="!list")
			{
			# Resources in a collection or list may be in any archive state
			global $collections_omit_archived;
			if(substr($search,0,11)=="!collection" && $collections_omit_archived && !checkperm("e2"))
				{
				$sql_filter.= (($sql_filter!="")?" and ":"") . "archive<>2";
				}			
			}
		elseif ($search_all_workflow_states)
			{hook("search_all_workflow_states_filter");}   
		elseif ($archive==0 && $pending_review_visible_to_all)
            {
            # If resources pending review are visible to all, when listing only active resources include
            # pending review (-1) resources too.
            if ($sql_filter!="") {$sql_filter.=" and ";}
            $sql_filter.="archive in('0','-1')";
            } 
		else
            {
            # Append normal filtering - extended as advanced search now allows searching by archive state
            if ($sql_filter!="") {$sql_filter.=" and ";}
            $sql_filter.="archive = '$archive'";
            }
        global $k, $collection_allow_not_approved_share ;
        if (!checkperm("v") && !(substr($search,0,11)=="!collection" && $k!='' && $collection_allow_not_approved_share)) 
            {
            # Append standard filtering to hide resources in a pending state, whatever the search
            if (!$pending_submission_searchable_to_all) {$sql_filter.= (($sql_filter!="")?" and ":"") . "(r.archive<>-2 or r.created_by='" . $userref . "')";}
            if (!$pending_review_visible_to_all){$sql_filter.=(($sql_filter!="")?" and ":"") . "(r.archive<>-1 or r.created_by='" . $userref . "')";}
            }
		}
		
	# Add code to filter out resoures in archive states that the user does not have access to due to a 'z' permission
	$filterblockstates="";
	for ($n=-2;$n<=3;$n++)
	    {
	    if(checkperm("z" . $n)&&!$access_override)
		{           
		if ($filterblockstates!="") {$filterblockstates.="','";}
		$filterblockstates .= $n;
		}
	    }
	
	global $additional_archive_states;
	foreach ($additional_archive_states as $additional_archive_state)
	    {
	    if(checkperm("z" . $additional_archive_state))
		{
		if ($filterblockstates!="") {$filterblockstates.="','";}
		$filterblockstates .= $additional_archive_state;
		}
	    }
	
	if ($filterblockstates!=""&&!$access_override)
	    {
	    global $uploader_view_override, $userref;
	    if ($uploader_view_override)
		{
		if ($sql_filter!="") {$sql_filter.=" and ";}
		$sql_filter.="(archive not in ('$filterblockstates') or created_by='" . $userref . "')";
		}
	    else
		{
		if ($sql_filter!="") {$sql_filter.=" and ";}
		$sql_filter.="archive not in ('$filterblockstates')";
		}
	    }
	
	
	# Append media restrictions
	global $heightmin,$heightmax,$widthmin,$widthmax,$filesizemin,$filesizemax,$fileextension,$haspreviewimage;
	
	if ($heightmin!='')
		{		
		if ($sql_filter!="") {$sql_filter.=" and ";}
		$sql_filter.= "dim.height>='$heightmin'";
		}
		
		
	# append ref filter - never return the batch upload template (negative refs)
	if ($sql_filter!="") {$sql_filter.=" and ";}
	$sql_filter.="r.ref>0";

	return $sql_filter;
	}

function search_special($search,$sql_join,$fetchrows,$sql_prefix,$sql_suffix,$order_by,$orig_order,$select,$sql_filter,$archive,$return_disk_usage)
	{
	# Process special searches. These return early with results.

	
    # View Last
    if (substr($search,0,5)=="!last") 
        {
        # Replace r2.ref with r.ref for the alternative query used here.

        $order_by=str_replace("r.ref","r2.ref",$order_by);
        if ($orig_order=="relevance")
            {
            # Special case for ordering by relevance for this query.
            $direction=((strpos($order_by,"DESC")===false)?"ASC":"DESC");
            $order_by="r2.ref " . $direction;
            }
       
        
        # Extract the number of records to produce
        $last=explode(",",$search);
        $last=str_replace("!last","",$last[0]);
        
        if (!is_numeric($last)) {$last=1000;$search="!last1000";} # 'Last' must be a number. SQL injection filter.
        
        # Fix the order by for this query (special case due to inner query)
        $order_by=str_replace("r.rating","rating",$order_by);
                
        return sql_query($sql_prefix . "select distinct *,r2.hit_count score from (select $select from resource r $sql_join where $sql_filter order by ref desc limit $last ) r2 order by $order_by" . $sql_suffix,false,$fetchrows);
        }
    
     # Collections containing resources
     # NOTE - this returns collections not resources! Not intended for use in user searches.
     # This is used when the $collection_search_includes_resource_metadata option is enabled and searches collections based on the contents of the collections.
    if (substr($search,0,19)=="!contentscollection")
        {
        $flags=substr($search,19,((strpos($search," ")!==false)?strpos($search," "):strlen($search)) -19); # Extract User/Public/Theme flags from the beginning of the search parameter.
    	
        if ($flags=="") {$flags="TP";} # Sensible default

        # Add collections based on the provided collection type flags.
        $collection_filter="(";
        if (strpos($flags,"T")!==false) # Include themes
            {
            if ($collection_filter!="(") {$collection_filter.=" or ";}
            $collection_filter.=" (c.public=1 and (length(c.theme)>0))";
            }
	
	 if (strpos($flags,"P")!==false) # Include public collections
            {
            if ($collection_filter!="(") {$collection_filter.=" or ";}
            $collection_filter.=" (c.public=1 and (length(c.theme)=0 or c.theme is null))";
            }
        
        if (strpos($flags,"U")!==false) # Include the user's own collections
            {
            if ($collection_filter!="(") {$collection_filter.=" or ";}
            global $userref;
            $collection_filter.=" (c.public=0 and c.user='$userref')";
            }
        $collection_filter.=")";
        
        # Formulate SQL
        $sql="select distinct c.* from collection c join resource r $sql_join join collection_resource cr on cr.resource=r.ref and cr.collection=c.ref where $sql_filter and $collection_filter group by c.ref order by $order_by ";#echo $search . " " . $sql;
        return sql_query($sql);
        }
    
    # View Resources With No Downloads
    if (substr($search,0,12)=="!nodownloads") 
        {
        if ($orig_order=="relevance") {$order_by="ref desc";}

        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where $sql_filter and ref not in (select distinct object_ref from daily_stat where activity_type='Resource download') order by $order_by" . $sql_suffix,false,$fetchrows);
        }
    
    # Duplicate Resources (based on file_checksum)
    if (substr($search,0,11)=="!duplicates") 
        {
        # find duplicates of a given resource
        
        # Extract the resource ID
        $ref=explode(" ",$search);
        $ref=str_replace("!duplicates","",$ref[0]);
        $ref=explode(",",$ref);// just get the number
        $ref=escape_check($ref[0]);

        if ($ref!="") 
            {
            $results=sql_query("select distinct r.hit_count score, $select from resource r $sql_join  where $sql_filter and file_checksum= (select file_checksum from (select file_checksum from resource where archive = 0 and ref=$ref and file_checksum is not null)r2) order by file_checksum",false,$fetchrows);
            $count=count($results);
            if ($count>1) 
                {
                return $results;
                }
            else 
                {
                return false;
                }
            }
        else
            {
            return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where $sql_filter and file_checksum in (select file_checksum from (select file_checksum from resource where archive = 0 and file_checksum <> '' and file_checksum is not null group by file_checksum having count(file_checksum)>1)r2) order by file_checksum" . $sql_suffix,false,$fetchrows);
            }
        }
    
    # View Collection
    if (substr($search, 0, 11) == '!collection')
        {
        if($orig_order == 'relevance')
            {
            $order_by = 'c.sortorder ASC, c.date_added DESC, r.ref DESC';
            }

        $colcustperm   = $sql_join;
        $colcustfilter = $sql_filter; // to avoid allowing this sql_filter to be modified by the $access_override search in the smart collection update below!!!
        
              
        # Special case if a key has been provided.
        if(getval('k', '') != '')
            {
            $sql_filter = 'r.ref > 0';
            }

        # Extract the collection number
        $collection = explode(' ', $search);
        $collection = str_replace('!collection', '', $collection[0]);
        $collection = explode(',', $collection); // just get the number
        $collection = escape_check($collection[0]);

        # Check access
        if(!collection_readable($collection))
            {
	    return array();
            }

        # Smart collections update
        global $allow_smart_collections, $smart_collections_async;
        if($allow_smart_collections)
            {
            global $smartsearch_ref_cache;
            if(isset($smartsearch_ref_cache[$collection]))
                {
                $smartsearch_ref = $smartsearch_ref_cache[$collection]; // this value is pretty much constant
                }
            else
                {
                $smartsearch_ref = sql_value('SELECT savedsearch value FROM collection WHERE ref="' . $collection . '"', '');
                $smartsearch_ref_cache[$collection] = $smartsearch_ref;
                }

            global $php_path;
            if($smartsearch_ref != '' && !$return_disk_usage)
                {
                if($smart_collections_async && isset($php_path) && file_exists($php_path . '/php'))
                    {
                    exec($php_path . '/php ' . dirname(__FILE__) . '/../pages/ajax/update_smart_collection.php ' . escapeshellarg($collection) . ' ' . '> /dev/null 2>&1 &');
                    }
                else 
                    {
                    include (dirname(__FILE__) . '/../pages/ajax/update_smart_collection.php');
                    }
                }   
            }   
        $searchsql = $sql_prefix . "select distinct c.date_added,c.comment,c.purchase_size,c.purchase_complete,r.hit_count score,length(c.comment) commentset, $select from resource r  join collection_resource c on r.ref=c.resource $colcustperm  where c.collection='" . $collection . "' and $colcustfilter group by r.ref order by $order_by" . $sql_suffix;
        $collectionsearchsql=hook('modifycollectionsearchsql','',array($searchsql));
        if($collectionsearchsql)
            {$searchsql=$collectionsearchsql;}
        $result = sql_query($searchsql,false,$fetchrows);
        hook('beforereturnresults', '', array($result, $archive)); 
        
        return $result;
        }
    
    # View Related - Pushed Metadata (for the view page)
    if (substr($search,0,14)=="!relatedpushed")
        {
        # Extract the resource number
        $resource=explode(" ",$search);$resource=str_replace("!relatedpushed","",$resource[0]);
        $order_by=str_replace("r.","",$order_by); # UNION below doesn't like table aliases in the order by.
        
        return sql_query($sql_prefix . "select distinct r.hit_count score,rt.name resource_type_name, $select from resource r join resource_type rt on r.resource_type=rt.ref and rt.push_metadata=1 join resource_related t on (t.related=r.ref and t.resource='" . $resource . "') $sql_join  where 1=1 and $sql_filter group by r.ref 
        UNION
        select distinct r.hit_count score, rt.name resource_type_name, $select from resource r join resource_type rt on r.resource_type=rt.ref and rt.push_metadata=1 join resource_related t on (t.resource=r.ref and t.related='" . $resource . "') $sql_join  where 1=1 and $sql_filter group by r.ref 
        order by $order_by" . $sql_suffix,false,$fetchrows);
        }
        
    # View Related
    if (substr($search,0,8)=="!related")
        {
        # Extract the resource number
        $resource=explode(" ",$search);$resource=str_replace("!related","",$resource[0]);
        $order_by=str_replace("r.","",$order_by); # UNION below doesn't like table aliases in the order by.
        
        global $pagename, $related_search_show_self;
        $sql_self = '';
        if ($related_search_show_self && $pagename == 'search') 
            {
            $sql_self = " select distinct r.hit_count score, $select from resource r $sql_join where r.ref=$resource and $sql_filter group by r.ref UNION ";
            }

        return sql_query($sql_prefix . $sql_self . "select distinct r.hit_count score, $select from resource r join resource_related t on (t.related=r.ref and t.resource='" . $resource . "') $sql_join  where 1=1 and $sql_filter group by r.ref 
        UNION
        select distinct r.hit_count score, $select from resource r join resource_related t on (t.resource=r.ref and t.related='" . $resource . "') $sql_join  where 1=1 and $sql_filter group by r.ref 
        order by $order_by" . $sql_suffix,false,$fetchrows);
        }
        

        
    # Geographic search
    if (substr($search,0,4)=="!geo")
        {
        $geo=explode("t",str_replace(array("m","p"),array("-","."),substr($search,4))); # Specially encoded string to avoid keyword splitting
        $bl=explode("b",$geo[0]);
        $tr=explode("b",$geo[1]);   
        $sql="select r.hit_count score, $select from resource r $sql_join where 

                    geo_lat > '" . escape_check($bl[0]) . "'
              and   geo_lat < '" . escape_check($tr[0]) . "'        
              and   geo_long > '" . escape_check($bl[1]) . "'       
              and   geo_long < '" . escape_check($tr[1]) . "'       
                          
         and $sql_filter group by r.ref order by $order_by";
        return sql_query($sql_prefix . $sql . $sql_suffix,false,$fetchrows);
        }

    # Colour search
    if (substr($search,0,7)=="!colour")
        {
        $colour=explode(" ",$search);$colour=str_replace("!colour","",$colour[0]);

        $sql="select r.hit_count score, $select from resource r $sql_join
                where 
                    colour_key like '" . escape_check($colour) . "%'
                or  colour_key like '_" . escape_check($colour) . "%'
                          
         and $sql_filter group by r.ref order by $order_by";
        return sql_query($sql_prefix . $sql . $sql_suffix,false,$fetchrows);
        }       
        
    # Similar to a colour
    if (substr($search,0,4)=="!rgb")
        {
        $rgb=explode(":",$search);$rgb=explode(",",$rgb[1]);
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where has_image=1 and $sql_filter group by r.ref order by (abs(image_red-" . $rgb[0] . ")+abs(image_green-" . $rgb[1] . ")+abs(image_blue-" . $rgb[2] . ")) asc limit 500" . $sql_suffix,false,$fetchrows);
        }
        
    # Has no preview image
    if (substr($search,0,10)=="!nopreview")
        {
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where has_image=0 and $sql_filter group by r.ref" . $sql_suffix,false,$fetchrows);
        }       
        
    # Similar to a colour by key
    if (substr($search,0,10)=="!colourkey")
        {
        # Extract the colour key
        $colourkey=explode(" ",$search);$colourkey=str_replace("!colourkey","",$colourkey[0]);
        
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where has_image=1 and left(colour_key,4)='" . $colourkey . "' and $sql_filter group by r.ref" . $sql_suffix,false,$fetchrows);
        }
    
    global $config_search_for_number;
    if (($config_search_for_number && is_numeric($search)) || substr($search,0,9)=="!resource")
        {
        $theref = escape_check($search);
        $theref = preg_replace("/[^0-9]/","",$theref);
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where r.ref='$theref' and $sql_filter group by r.ref" . $sql_suffix);
        }

    # Searching for pending archive
    if (substr($search,0,15)=="!archivepending")
        {
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where archive=1 and ref>0 group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }
    
    if (substr($search,0,12)=="!userpending")
        {
        if ($orig_order=="rating") {$order_by="request_count desc," . $order_by;}
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where archive=-1 and ref>0 group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }
        
    # View Contributions
    if (substr($search,0,14)=="!contributions")
        {
        global $userref;
        
        # Extract the user ref
        $cuser=explode(" ",$search);$cuser=str_replace("!contributions","",$cuser[0]);
        
        $select=str_replace(",rca.access group_access,rca2.access user_access ",",null group_access, null user_access ",$select);
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where created_by='" . $cuser . "' and r.ref > 0 and $sql_filter group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }
    
    # Search for resources with images
    if ($search=="!images") 
        {
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where has_image=1 group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }

    # Search for resources not used in Collections
    if (substr($search,0,7)=="!unused") 
        {
        return sql_query($sql_prefix . "SELECT distinct $select FROM resource r $sql_join  where r.ref>0 and r.ref not in (select c.resource from collection_resource c) and $sql_filter" . $sql_suffix,false,$fetchrows);
        }   
    
    # Search for a list of resources
    # !listall = archive state is not applied as a filter to the list of resources.
    if (substr($search,0,5)=="!list") 
        {   
        $resources=explode(" ",$search);
        if (substr($search,0,8)=="!listall")
            {
            $resources=str_replace("!listall","",$resources[0]);
            } 
        else 
            {
            $resources=str_replace("!list","",$resources[0]);
            }
        $resources=explode(",",$resources);// separate out any additional keywords
        $resources=escape_check($resources[0]);
        if (strlen(trim($resources))==0)
            {
            $resources="where r.ref IS NULL";
            }
        else 
            {  
            $resources="where (r.ref='".str_replace(":","' OR r.ref='",$resources) . "')";
            }
    
        return sql_query($sql_prefix . "SELECT distinct r.hit_count score, $select FROM resource r $sql_join $resources and $sql_filter order by $order_by" . $sql_suffix,false,$fetchrows);
        }   

    # View resources that have data in the specified field reference - useful if deleting unused fields
    if (substr($search,0,8)=="!hasdata") 
        {       
        $fieldref=intval(trim(substr($search,8)));
        $sql_join.=" join resource_data on r.ref=resource_data.resource and resource_data.resource_type_field=$fieldref and resource_data.value<>'' ";
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join and r.ref > 0 and $sql_filter group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }
        
    # Search for resource properties
    if (substr($search,0,11)=="!properties")
        {
        // Note: in order to combine special searches with normal searches, these are separated by space (" ")
        $searches_array = explode(' ', $search);
        $properties     = explode(';', substr($searches_array[0], 11));
        $sql_join.=" left join resource_dimensions rdim on r.ref=rdim.resource";
        
        foreach ($properties as $property)
            {
            $propertycheck=explode(":",$property);
            if(count($propertycheck)==2)
                {
                $propertyname=$propertycheck[0];
                $propertyval=escape_check($propertycheck[1]);
                if($sql_filter==""){$sql_filter .= " where ";}else{$sql_filter .= " and ";}
                switch($propertyname)
                    {
                    case "hmin":
                        $sql_filter.=" rdim.height>='".  intval($propertyval) . "'";
                    break;
                    case "hmax":
                        $sql_filter.=" rdim.height<='".  intval($propertyval) . "'";
                    break;
                    case "wmin":
                        $sql_filter.=" rdim.width>='".  intval($propertyval) . "'";
                    break;
                    case "wmax":
                        $sql_filter.=" rdim.width<='".  intval($propertyval) . "'";
                    break;
                    case "fmin":
                        // Need to convert MB value to bytes
                        $sql_filter.=" r.file_size>='".  (floatval($propertyval) * 1024 * 1024) . "'";
                    break;
                    case "fmax":
                        // Need to convert MB value to bytes
                        $sql_filter.=" r.file_size<='". (floatval($propertyval) * 1024 * 1024) . "'";
                    break;
                    case "fext":
                        $propertyval=str_replace("*","%",$propertyval);
                        $sql_filter.=" r.file_extension ";
                        if(substr($propertyval,0,1)=="-")
                            {
                            $propertyval = substr($propertyval,1);
                            $sql_filter.=" not ";                            
                            }
                        if(substr($propertyval,0,1)==".")
                            {
                            $propertyval = substr($propertyval,1);
                            }
                        $sql_filter.=" like '". escape_check($propertyval) . "'";
                    break;
                    case "pi":
                        $sql_filter.=" r.has_image='".  intval($propertyval) . "'";
                    break;
                    case "cu":
                        $sql_filter.=" r.created_by='".  intval($propertyval) . "'";
                    break;
                    }
                }
            }
            
        return sql_query($sql_prefix . "select distinct r.hit_count score, $select from resource r $sql_join  where r.ref > 0 and $sql_filter group by r.ref order by $order_by" . $sql_suffix,false,$fetchrows);
        }

    # Within this hook implementation, set the value of the global $sql variable:
    # Since there will only be one special search executed at a time, only one of the
    # hook implementations will set the value.  So, you know that the value set
    # will always be the correct one (unless two plugins use the same !<type> value).
    $sql=hook("addspecialsearch", "", array($search));
    
    if($sql != "")
        {
        debug("Addspecialsearch hook returned useful results.");
        return sql_query($sql_prefix . $sql . $sql_suffix,false,$fetchrows);
        }

     # Arrived here? There were no special searches. Return false.
     return false;
     }
