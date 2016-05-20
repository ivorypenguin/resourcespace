<?php
# General functions, useful across the whole solution

include_once ("language_functions.php");
include_once "message_functions.php";
include_once 'node_functions.php';

$GLOBALS['get_resource_path_fpcache'] = array();
function get_resource_path($ref,$getfilepath,$size,$generate=true,$extension="jpg",$scramble=-1,$page=1,$watermarked=false,$file_modified="",$alternative=-1,$includemodified=true)
	{
	# returns the correct path to resource $ref of size $size ($size==empty string is original resource)
	# If one or more of the folders do not exist, and $generate=true, then they are generated
	if(!preg_match('/^[a-zA-Z0-9]+$/', $extension)){$extension="jpg";}
	$override=hook("get_resource_path_override","",array($ref,$getfilepath,$size,$generate,$extension,$scramble,$page,$watermarked,$file_modified,$alternative,$includemodified));
	if (is_string($override)) {return $override;}

	global $storagedir,$originals_separate_storage;

	if ($size=="")
		{
		# For the full size, check to see if the full path is set and if so return that.
		global $get_resource_path_fpcache;
		truncate_cache_arrays();

		if (!isset($get_resource_path_fpcache[$ref])) {$get_resource_path_fpcache[$ref]=sql_value("select file_path value from resource where ref='$ref'","");}
		$fp=$get_resource_path_fpcache[$ref];
		
		# Test to see if this nosize file is of the extension asked for, else skip the file_path and return a $storagedir path. 
		# If using staticsync, file path will be set already, but we still want the $storagedir path for a nosize preview jpg.
		# Also, returning the original filename when a nosize 'jpg' is looked for is no good, since preview_preprocessing.php deletes $target.
		
		$test_ext = explode(".",$fp);$test_ext=trim(strtolower($test_ext[count($test_ext)-1]));
		
        if (($test_ext == $extension || $alternative > 0) && strlen($fp)>0 && strpos($fp,"/")!==false)
			{				
			if ($getfilepath)
				{
				global $syncdir; 
            	$syncdirmodified=hook("modifysyncdir","all",array($ref)); if ($syncdirmodified!=""){return $syncdirmodified;}
                if(!($alternative>0))
                    {return $syncdir . "/" . $fp;}
                elseif(!$generate)
                    {
                    // Alternative file and using staticsync. Would not be generating path if checking for an existing file.
                    // Check if file is present in syncdir, else continue to get the $storagedir location
                    $altfile = get_alternative_file($ref,$alternative);
                    if($altfile["file_extension"]==$extension && file_exists($altfile["file_name"]))
                        {return $altfile["file_name"];}
                    }
				}
			else 
				{
				global $baseurl_short, $k;
				return $baseurl_short . "pages/download.php?ref={$ref}&size={$size}&ext={$extension}&noattach=true&k={$k}&page={$page}&alternative={$alternative}"; 
				}
			}
		}

	global $scramble_key;	
	if ($scramble===-1)
		{
		# Find the system default scramble setting if not specified
		if (isset($scramble_key) && ($scramble_key!="")) {$scramble=true;} else {$scramble=false;}
		}
	
	if ($scramble)
		{
		# Create a scrambled path using the scramble key
		# It should be very difficult or impossible to work out the scramble key, and therefore access
		# other resources, based on the scrambled path of a single resource.
		$scramblepath=substr(md5($ref . "_" . $scramble_key),0,15);
		}
	
	if ($extension=="") {$extension="jpg";}
	
	$folder="";
	#if (!file_exists(dirname(__FILE__) . $folder)) {mkdir(dirname(__FILE__) . $folder,0777);}
	
	# Original separation support
	if($originals_separate_storage && $size=="")
		{
		# Original file (core file or alternative)
		$path_suffix="/original/";
		}
	elseif($originals_separate_storage)
		{
		# Preview or thumb
		$path_suffix="/resized/";
		}
	else
		{
		$path_suffix="/";
		}
	
	for ($n=0;$n<strlen($ref);$n++)
		{
		$folder.=substr($ref,$n,1);
		if (($scramble) && ($n==(strlen($ref)-1))) {$folder.="_" . $scramblepath;}
		$folder.="/";
		#echo "<li>" . $folder;
		if ((!(file_exists($storagedir . $path_suffix . $folder))) && $generate) {@mkdir($storagedir . $path_suffix . $folder,0777);chmod($storagedir . $path_suffix . $folder,0777);}
		}
		
	# Add the page to the filename for everything except page 1.
	if ($page==1) {$p="";} else {$p="_" . $page;}
	
	# Add the alternative file ID to the filename if provided
	if ($alternative>0) {$a="_alt_" . $alternative;} else {$a="";}
	
	# Add the watermarked url too
	if ($watermarked) {$p.="_wm";}
	
	
		
	$filefolder=$storagedir . $path_suffix . $folder;
	
	# Fetching the file path? Add the full path to the file
	if ($getfilepath)
	    {
	    $folder=$filefolder; 
	    }
	else
	    {
	    global $storageurl;
	    $folder=$storageurl . $path_suffix . $folder;
	    }
	
	if ($scramble)
		{
		$file_old=$filefolder . $ref . $size . $p . $a . "." . $extension;
		$file_new=$filefolder . $ref . $size . $p . $a . "_" . substr(md5($ref . $size . $p . $a . $scramble_key),0,15) . "." . $extension;
		$file=$folder . $ref . $size . $p . $a . "_" . substr(md5($ref . $size . $p . $a . $scramble_key),0,15) . "." . $extension;
		if (file_exists($file_old))
		  	{
			rename($file_old, $file_new);
		  	}
		}
	else
		{
		$file=$folder . $ref . $size . $p . $a . "." . $extension;
		}

# Append modified date/time to the URL so the cached copy is not used if the file is changed.
	if (!$getfilepath && $includemodified)
		{
		if ($file_modified=="")
			{
			$data=get_resource_data($ref);
			$file .= "?v=" . urlencode($data['file_modified']);
			}
		else
			{
			# Use the provided value
			$file .= "?v=" . urlencode($file_modified);
			}
		}
	
	return  $file;
	}
	
$GLOBALS['get_resource_data_cache'] = array();
function get_resource_data($ref,$cache=true)
	{
	if ($ref==""){return false;}
	# Returns basic resource data (from the resource table alone) for resource $ref.
	# For 'dynamic' field data, see get_resource_field_data
	global $default_resource_type, $get_resource_data_cache,$always_record_resource_creator;
	truncate_cache_arrays();
	if ($cache && isset($get_resource_data_cache[$ref])) {return $get_resource_data_cache[$ref];}
	$resource=sql_query("select *,mapzoom from resource where ref='$ref'");
	if (count($resource)==0) 
		{
		if ($ref>0)
			{
			return false;
			}
		else
			{
            # For upload templates (negative reference numbers), generate a new resource if upload permission.
            if (!(checkperm("c") || checkperm("d"))) {return false;}
            else
                {
                if (isset($always_record_resource_creator) && $always_record_resource_creator)
                    {
                    global $userref;
                    $user = $userref;
                    }
                else {$user = -1;}
                $wait = sql_query("insert into resource (ref,resource_type,created_by) values ('$ref','$default_resource_type','$user')");
                $resource = sql_query("select *,mapzoom from resource where ref='$ref'");
                }
            }
        }
	$get_resource_data_cache[$ref]=$resource[0];
	return $resource[0];
	}

function update_hitcount($ref)
	{
	global $resource_hit_count_on_downloads;
	
	# update hit count if not tracking downloads only
	if (!$resource_hit_count_on_downloads) 
		{ 
		# greatest() is used so the value is taken from the hit_count column in the event that new_hit_count is zero to support installations that did not previously have a new_hit_count column (i.e. upgrade compatability).
		sql_query("update resource set new_hit_count=greatest(hit_count,new_hit_count)+1 where ref='$ref'",false,-1,true,0);
		}
	}	
	
function get_resource_type_field($field)
	{
	# Returns field data from resource_type_field for the given field.
	$return=sql_query("select * from resource_type_field where ref='$field'");
	if (count($return)==0)
		{
		return false;
		}
	else
		{
		return $return[0];
		}
	}
if (!function_exists('get_resource_field_data')) {
function get_resource_field_data($ref,$multi=false,$use_permissions=true,$originalref=-1,$external_access=false,$ord_by=false)
	{
    # Returns field data and field properties (resource_type_field and resource_data tables)
    # for this resource, for display in an edit / view form.
    # Standard field titles are translated using $lang.  Custom field titles are i18n translated.

    # Find the resource type.
    if ($originalref==-1) {$originalref = $ref;} # When a template has been selected, only show fields for the type of the original resource ref, not the template (which shows fields for all types)
    $rtype = sql_value("select resource_type value from resource where ref='$originalref'",0);

    # If using metadata templates, 
    $templatesql = "";
    global $metadata_template_resource_type;
    if (isset($metadata_template_resource_type) && $metadata_template_resource_type==$rtype) {
        # Show all resource fields, just as with editing multiple resources.
        $multi = true;
    }

    $return = array();
	$fieldsSQL = "select d.value,d.resource_type_field,f.*,f.required frequired,f.ref fref from resource_type_field f left join (select * from resource_data where resource='$ref') d on d.resource_type_field=f.ref and d.resource='$ref' where ( " . (($multi)?"1=1":"f.resource_type=0 or f.resource_type=999 or f.resource_type='$rtype'") . ") group by f.ref order by ";
    if ($ord_by) {
    	$fieldsSQL .= "f.order_by,f.resource_type,f.ref";
    } else {
		$fieldsSQL .= "f.resource_type,f.order_by,f.ref";
	    debug("use perms: ".!$use_permissions);
    }
	$fields = sql_query($fieldsSQL);
  
    # Build an array of valid types and only return fields of this type. Translate field titles. 
    $validtypes = sql_array("select ref value from resource_type");
    $validtypes[] = 0; $validtypes[] = 999; # Support archive and global.
    for ($n = 0;$n<count($fields);$n++) {
        if
	(
		(
		!$use_permissions || 
			(
			# Upload only edit access to this field?
			$ref<0 && checkperm("P" . $fields[$n]["fref"])
			)    
		||
			(
				(
				checkperm("f*") || checkperm("f" . $fields[$n]["fref"])
				)
			&& !checkperm("f-" . $fields[$n]["fref"]) && !checkperm("T" . $fields[$n]["resource_type"])
			)
		)
        && in_array($fields[$n]["resource_type"],$validtypes) &&
		(
		!
			(
			$external_access && !$fields[$n]["external_user_access"]
			)
		)
	) {    
	debug("field".$fields[$n]["title"]."=".$fields[$n]["value"]);
            $fields[$n]["title"] = lang_or_i18n_get_translated($fields[$n]["title"], "fieldtitle-"); 
            $return[] = $fields[$n];
        }
    }
    return $return;
	}
}

function get_resource_field_data_batch($refs)
	{
	# Returns field data and field properties (resource_type_field and resource_data tables)
	# for all the resource references in the array $refs.
	# This will use a single SQL query and is therefore a much more efficient way of gathering
	# resource data for a list of resources (e.g. search result display for a page of resources).
	if (count($refs)==0) {return array();} # return an empty array if no resources specified (for empty result sets)
	$refsin=join(",",$refs);
	$results=sql_query("select d.resource,f.*,d.value from resource_type_field f left join resource_data d on d.resource_type_field=f.ref and d.resource in ($refsin) where (f.resource_type=0 or f.resource_type in (select resource_type from resource where ref=d.resource)) order by d.resource,f.order_by,f.ref");
	$return=array();
	$res=0;
	for ($n=0;$n<count($results);$n++)
		{
		if ($results[$n]["resource"]!=$res)
			{
			# moved on to the next resource
			if ($res!=0) {$return[$res]=$resdata;}
			$resdata=array();
			$res=$results[$n]["resource"];
			}
		# copy name/value into resdata array
		$resdata[$results[$n]["ref"]]=$results[$n];
		}
	$return[$res]=$resdata;
	return $return;
	}
	
function get_resource_types($types = "", $translate = true)
	{
	# Returns a list of resource types. The standard resource types are translated using $lang. Custom resource types are i18n translated.
	// support getting info for a comma-delimited list of restypes (as in a search)
	if ($types==""){$sql="";} else
		{
		# Ensure $types are suitably quoted and escaped
		$cleantypes="";
		$s=explode(",",$types);
		foreach ($s as $type)
			{
			if (is_numeric(str_replace("'","",$type))) # Process numeric types only, to avoid inclusion of collection-based filters (mycol, public, etc.)
				{
				if (strpos($type,"'")===false) {$type="'" . $type . "'";}
				if ($cleantypes!="") {$cleantypes.=",";}
				$cleantypes.=$type;
				}
			}
		$sql=" where ref in ($cleantypes) ";
		}
	
	$r=sql_query("select * from resource_type $sql order by order_by,ref");
	$return=array();
	# Translate names (if $translate==true) and check permissions
	for ($n=0;$n<count($r);$n++)
		{
		if (!checkperm('T' . $r[$n]['ref']))
			{
			if ($translate==true) {$r[$n]["name"]=lang_or_i18n_get_translated($r[$n]["name"], "resourcetype-");} # Translate name
			$return[]=$r[$n]; # Add to return array
			}
		}
	return $return;
	}

function get_resource_top_keywords($resource,$count)
	{
	# Return the top $count keywords (by hitcount) used by $resource.
	# This is for the 'Find Similar' search.

        # These are now derived from resource data for fixed keyword lists, rather than from the resource_keyword table
        # which produced very mixed results and didn't work with stemming or diacritic normalisation.
        
    $return=array();
	$keywords=sql_query("select distinct rd.value keyword,f.ref field,f.resource_type from resource_data rd,resource_type_field f where rd.resource='$resource' and f.ref=rd.resource_type_field and f.type in (2,3,7,9,11,12) and f.keywords_index=1 and f.use_for_similar=1 and length(rd.value)>0 limit $count");
	foreach ($keywords as $keyword)
		{
		# Apply permissions and strip out any results the user does not have access to.
		if ((checkperm("f*") || checkperm("f" . $keyword["field"]))
		&& !checkperm("f-" . $keyword["field"]) && !checkperm("T" . $keyword["resource_type"]))
			{
			# Has access to this field.
                        $r=$keyword["keyword"];
                        if (substr($r,0,1)==",") {$r=substr($r,1);}
                        $s=explode(",",$r);
                        foreach ($s as $a)
                            {
                            if(!empty($a))
                            	{$return[]=$a;}
                            }
			}
		}
	return $return;
	}

if (!function_exists("split_keywords")){
function split_keywords($search,$index=false,$partial_index=false,$is_date=false,$is_html=false)
	{
	# Takes $search and returns an array of individual keywords.
	global $config_trimchars;

	if ($index && $is_date)
		{
		# Date handling... index a little differently to support various levels of date matching (Year, Year+Month, Year+Month+Day).
		$s=explode("-",$search);
		if (count($s)>=3)
			{
			return (array($s[0],$s[0] . "-" . $s[1],$search));
			}
		else
			{
			return $search;
			}
		}
		
	# Remove any real / unescaped lf/cr
	$search=str_replace("\r"," ",$search);
	$search=str_replace("\n"," ",$search);
	$search=str_replace("\\r"," ",$search);
	$search=str_replace("\\n"," ",$search);

	$ns=trim_spaces($search);
	
	if ((substr($ns,0,1)==",") ||  ($index==false && strpos($ns,":")!==false)) # special 'constructed' query type, split using comma so
	# we support keywords with spaces.
		{
		if (strpos($ns,"startdate")==false && strpos($ns,"enddate")==false)
			{$ns=cleanse_string($ns,true,!$index,$is_html);}
	
		$return=explode(",",$ns);
		# If we are indexing, append any values that contain spaces.
        
		# Important! Solves the searching for keywords with spaces issue.
		# Consider: for any keyword that has spaces, append to the array each individual word too
		# so for example: South Asia,USA becomes South Asia,USA,South,Asia
		# so a plain search for 'south asia' will match those with the keyword 'south asia' because the resource
		# will also be linked to the words 'south' and 'asia'.
		if ($index)
			{
			$return2=$return;
			for ($n=0;$n<count($return);$n++)
				{
				$keyword=trim($return[$n]);
				if (strpos($keyword," ")!==false)
					{
					# append each word
					$words=explode(" ",$keyword);
					for ($m=0;$m<count($words);$m++) {$return2[]=trim($words[$m]);}
					}
				}
				
			$return2=trim_array($return2,$config_trimchars);
			if ($partial_index) {return add_partial_index($return2);}
			return $return2;
			}
		else
			{
			return trim_array($return,$config_trimchars);
			}
		}
	else
		{
		# split using spaces and similar chars (according to configured whitespace characters)
		$ns=explode(" ",cleanse_string($ns,false,!$index,$is_html));                
		
        $ns=trim_array($ns,$config_trimchars);
		if ($index && $partial_index) {
			return add_partial_index($ns);
		}
		return $ns;
		}

	}
}

if (!function_exists("cleanse_string")){
function cleanse_string($string,$preserve_separators,$preserve_hyphen=false,$is_html=false)
        {
        # Removes characters from a string prior to keyword splitting, for example full stops
        # Also makes the string lower case ready for indexing.
        global $config_separators;
        $separators=$config_separators;

		  if($is_html)
		  	{
		  	$string= html_entity_decode($string,ENT_QUOTES,'UTF-8');
		  	} 			       
        
        if ($preserve_hyphen)
        	{
        	# Preserve hyphen - used when NOT indexing so we know which keywords to omit from the search.
			if ((substr($string,0,1)=="-" /*support minus as first character for simple NOT searches */ || strpos($string," -")!==false) && strpos($string," - ")==false)
				{
					$separators=array_diff($separators,array("-")); # Remove hyphen from separator array.
				}
        	}
        if (substr($string,0,1)=="!" && strpos(substr($string,1),"!")===false) 
                {
                // If we have the exclamation mark configured as a config separator but we are doing a special search we don't want to remove it
                $separators=array_diff($separators,array("!")); 
                }
        
        if ($preserve_separators)
                {
                return mb_strtolower(trim_spaces(str_replace($separators," ",$string)),'UTF-8');
                }
        else
                {
                # Also strip out the separators used when specifying multiple field/keyword pairs (comma and colon)
                $s=$separators;
                $s[]=",";
                $s[]=":";
                return mb_strtolower(trim_spaces(str_replace($s," ",$string)),'UTF-8');
                }
        }
}

if (!function_exists("resolve_keyword")){
function resolve_keyword($keyword,$create=false)
	{
	debug("resolving keyword " . $keyword  . ". Create=" . (($create)?"true":"false"));
	
        $keyword=substr($keyword,0,100); # Trim keywords to 100 chars for indexing, as this is the length of the keywords column.
    
	global $quoted_string;	
	if(!$quoted_string)
		{
		$keyword=normalize_keyword($keyword);		
		debug("resolving normalized keyword " . $keyword  . ".");
		}
	
        # Stemming support. If enabled and a stemmer is available for the current language, index the stem of the keyword not the keyword itself.
        # This means plural/singular (and other) forms of a word are treated as equivalents.
        global $stemming;
        if ($stemming && function_exists("GetStem"))
            {
            $keyword=GetStem($keyword);
            }

	# Returns the keyword reference for $keyword, or false if no such keyword exists.
	$return=sql_value("select ref value from keyword where keyword='" . trim(escape_check($keyword)) . "'",false);
	if ($return===false && $create)
		{
		# Create a new keyword.
		debug("Creating new keyword for " . $keyword);
		sql_query("insert into keyword (keyword,soundex,hit_count) values ('" . escape_check($keyword) . "',left('".soundex(escape_check($keyword))."',10),0)");
		$return=sql_insert_id();
		}
	return $return;
	}
}

function add_partial_index($keywords)
	{
	# For each keywords in the supplied keywords list add all possible infixes and return the combined array.
	# This therefore returns all keywords that need indexing for the given string.
	# Only for fields with 'partial_index' enabled.
	$return=array();
	$position=0;
	$x=0;
	for ($n=0;$n<count($keywords);$n++)
		{
		$keyword=trim($keywords[$n]);
		$return[$x]['keyword']=$keyword;
		$return[$x]['position']=$position;
		$x++;
		if (strpos($keyword," ")===false) # Do not do this for keywords containing spaces as these have already been broken to individual words using the code above.
			{
			global $partial_index_min_word_length;
			# For each appropriate infix length
			for ($m=$partial_index_min_word_length;$m<strlen($keyword);$m++)
				{
				# For each position an infix of this length can exist in the string
				for ($o=0;$o<=strlen($keyword)-$m;$o++)
					{
					$infix=mb_substr($keyword,$o,$m);
					$return[$x]['keyword']=$infix;
					$return[$x]['position']=$position; // infix has same position as root
					$x++;
					}
				}
			} # End of no-spaces condition
		$position++; // end of root keyword
		} # End of partial indexing keywords loop
	return $return;
	}


function trim_spaces($text)
	{
	# replace multiple spaces with a single space
	while (strpos($text,"  ")!==false)
		{
		$text=str_replace("  "," ",$text);
		}
	return trim($text);
	}	
		

if (!function_exists("update_resource_keyword_hitcount")){	
function update_resource_keyword_hitcount($resource,$search)
	{
	# For the specified $resource, increment the hitcount for each matching keyword in $search
	# This is done into a temporary column first (new_hit_count) so existing results are not affected.
	# copy_hitcount_to_live() is then executed at a set interval to make this data live.
	$keywords=split_keywords($search);
	$keys=array();
	for ($n=0;$n<count($keywords);$n++)
		{
		$keyword=$keywords[$n];
		if (strpos($keyword,":")!==false)
			{
			$k=explode(":",$keyword);
			$keyword=$k[1];
			}
		$found=resolve_keyword($keyword);
		if ($found!==false) {$keys[]=resolve_keyword($keyword);}
		}	
	if (count($keys)>0)
        {
        // Get all nodes matching these keywords
		$nodes = get_nodes_from_keywords($keys);
        update_resource_node_hitcount($resource,$nodes);
        sql_query("update resource_keyword set new_hit_count=new_hit_count+1 where resource='$resource' and keyword in (" . join(",",$keys) . ")",false,-1,true,0);
        }
	}
}
	
function copy_hitcount_to_live()
	{
	# Copy the temporary hit count used for relevance matching to the live column so it's activated (see comment for
	# update_resource_keyword_hitcount())
	sql_query("update resource_keyword set hit_count=new_hit_count");
	
	# Also update the resource table
	# greatest() is used so the value is taken from the hit_count column in the event that new_hit_count is zero to support installations that did not previously have a new_hit_count column (i.e. upgrade compatability)
	sql_query("update resource set hit_count=greatest(hit_count,new_hit_count)");
    
	# Also now update resource_node_hitcount())
	sql_query("update resource_node set hit_count=new_hit_count");
	}
if(!function_exists("get_image_sizes")){
function get_image_sizes($ref,$internal=false,$extension="jpg",$onlyifexists=true)
	{
	# Returns a table of available image sizes for resource $ref. The standard image sizes are translated using $lang. Custom image sizes are i18n translated.
	# The original image file assumes the name of the 'nearest size (up)' in the table

	global $imagemagick_calculate_sizes;

	# Work out resource type
	$resource_type=sql_value("select resource_type value from resource where ref='$ref'","");

	# add the original image
	$return=array();
	$lastname=sql_value("select name value from preview_size where width=(select max(width) from preview_size)",""); # Start with the highest resolution.
	$lastpreview=0;$lastrestricted=0;
	$path2=get_resource_path($ref,true,'',false,$extension);

	if (file_exists($path2) && !checkperm("T" . $resource_type . "_"))
	{ 
		$returnline=array();
		$returnline["name"]=lang_or_i18n_get_translated($lastname, "imagesize-");
		$returnline["allow_preview"]=$lastpreview;
		$returnline["allow_restricted"]=$lastrestricted;
		$returnline["path"]=$path2;
		$returnline["id"]="";
		$dimensions = sql_query("select width,height,file_size,resolution,unit from resource_dimensions where resource=". $ref);
		
		if (count($dimensions))
			{
			$sw = $dimensions[0]['width']; if ($sw==0) {$sw="?";}
			$sh = $dimensions[0]['height']; if ($sh==0) {$sh="?";}
			$filesize=$dimensions[0]['file_size'];
			# resolution and unit are not necessarily available, set to empty string if so.
			$resolution = ($dimensions[0]['resolution'])?$dimensions[0]['resolution']:"";
			$unit = ($dimensions[0]['unit'])?$dimensions[0]['unit']:"";
			}
		else
			{
			$fileinfo=get_original_imagesize($ref,$path2,$extension);
			$filesize = $fileinfo[0];
			$sw = $fileinfo[1];
			$sh = $fileinfo[2];
			}
		if (!is_numeric($filesize)) {$returnline["filesize"]="?";$returnline["filedown"]="?";}
		else {$returnline["filedown"]=ceil($filesize/50000) . " seconds @ broadband";$returnline["filesize"]=formatfilesize($filesize);}
		$returnline["width"]=$sw;			
		$returnline["height"]=$sh;
		$returnline["extension"]=$extension;
		(isset($resolution))?$returnline["resolution"]=$resolution:$returnline["resolution"]="";
		(isset($unit))?$returnline["unit"]=$unit:$returnline["unit"]="";
		$return[]=$returnline;
	}
	# loop through all image sizes
	$sizes=sql_query("select * from preview_size order by width desc");
	for ($n=0;$n<count($sizes);$n++)
		{
		$path=get_resource_path($ref,true,$sizes[$n]["id"],false,"jpg");

		$file_exists = file_exists($path);
		if (($file_exists || (!$onlyifexists)) && !checkperm("T" . $resource_type . "_" . $sizes[$n]["id"]))
			{
			if (($sizes[$n]["internal"]==0) || ($internal))
				{
				$returnline=array();
				$returnline["name"]=lang_or_i18n_get_translated($sizes[$n]["name"], "imagesize-");
				$returnline["allow_preview"]=$sizes[$n]["allow_preview"];

				# The ability to restrict download size by user group and resource type.
				if (checkperm("X" . $resource_type . "_" . $sizes[$n]["id"]))
					{
					# Permission set. Always restrict this download if this resource is restricted.
					$returnline["allow_restricted"]=false;
					}
				else
					{
					# Take the restriction from the settings for this download size.
					$returnline["allow_restricted"]=$sizes[$n]["allow_restricted"];
					}
				$returnline["path"]=$path;
				$returnline["id"]=$sizes[$n]["id"];
				if ((list($sw,$sh) = @getimagesize($path))===false) {$sw=0;$sh=0;}
				if ($file_exists)
					$filesize=@filesize_unlimited($path);
				else
					$filesize=0;
				if ($filesize===false) {$returnline["filesize"]="?";$returnline["filedown"]="?";}
				else {$returnline["filedown"]=ceil($filesize/50000) . " seconds @ broadband";$filesize=formatfilesize($filesize);}
				$returnline["filesize"]=$filesize;			
				$returnline["width"]=$sw;			
				$returnline["height"]=$sh;
				$returnline["extension"]='jpg';
				$return[]=$returnline;
				}
			}
		$lastname=lang_or_i18n_get_translated($sizes[$n]["name"], "imagesize-");
		$lastpreview=$sizes[$n]["allow_preview"];
		$lastrestricted=$sizes[$n]["allow_restricted"];
		}
	return $return;
	}
}
function get_preview_quality($size)
	{
	global $imagemagick_quality,$preview_quality_unique;
	$preview_quality=$imagemagick_quality; // default
	if($preview_quality_unique)
		{
		debug("convert: select quality value from preview_size where id='$size'");
		$quality_val=sql_value("select quality value from preview_size where id='{$size}'",'');
		if($quality_val!='')
			{
			$preview_quality=$quality_val;
			}
		}
	debug("convert: preview quality for $size=$preview_quality");
	return $preview_quality;
	}

function trim_array($array,$trimchars='')
	{
	if(isset($array[0]) && empty($array[0]) && !(emptyiszero($array[0]))){$unshiftblank=true;}
    $array = array_filter($array,'emptyiszero');
	$array_trimmed=array();
	$index=0;
	# removes whitespace from the beginning/end of all elements in an array
	foreach($array as $el)
		{
		$el=trim($el);
		if (strlen($trimchars) > 0)
			{
			// also trim off extra characters they want gone
			$el=trim($el,$trimchars);
			}
		$array_trimmed[$index]=$el;
		$index++;
		}
	if(isset($unshiftblank)){array_unshift($array_trimmed,"");}
	return $array_trimmed;
	}


function tidylist($list)
	{
	# Takes a value as returned from a check-list field type and reformats to be more display-friendly.
	# Check-list fields have a leading comma.
	$list=trim($list);
	if (strpos($list,",")===false) {return $list;}
	$list=explode(",",$list);
	if (trim($list[0])=="") {array_shift($list);} # remove initial comma used to identify item is a list
	$op=join(", ",trim_array($list));
	#if (strpos($op,".")!==false) {$op=str_replace(", ","<br/>",$op);}
	return $op;
	}

function tidy_trim($text,$length)
	{
	# Trims $text to $length if necessary. Tries to trim at a space if possible. Adds three full stops
	# if trimmed...
	$text=trim($text);
	if (strlen($text)>$length)
		{
		$text=mb_substr($text,0,$length-3,'utf-8');
		# Trim back to the last space
		$t=strrpos($text," ");
		$c=strrpos($text,",");
		if ($c!==false) {$t=$c;}
		if ($t>5) 
            {
            $text=substr($text,0,$t);
            }
		$text=$text . "...";
		}
	return $text;
	}

function get_related_resources($ref)
	{
	# Return an array of resource references that are related to resource $ref
	return sql_array("select related value from resource_related where resource='$ref' union select resource value from resource_related where related='$ref'");
	}
	
function average_length($array)
	{
	# Returns the average length of the strings in an array
        if (count($array)==0) {return 0;}
	$total=0;
	for ($n=0;$n<count($array);$n++)
		{
		$total+=strlen(i18n_get_translated($array[$n]));
		}
	return ($total/count($array));
	}
	
function get_field_options($ref)
	{
	# For the field with reference $ref, return a sorted array of options.

	//$options=sql_value("select options value from resource_type_field where ref='$ref'","");

    $options = array();
    node_field_options_override($options,$ref);

	# Translate all options
	for ($m=0;$m<count($options);$m++)
		{
		$options[$m]=i18n_get_translated($options[$m]);
		}

	global $auto_order_checkbox,$auto_order_checkbox_case_insensitive;
	if ($auto_order_checkbox) {
		if($auto_order_checkbox_case_insensitive){natcasesort($options);$options=array_values($options);}
		else{sort($options);}
	}
	
	return $options;
	}
	
function get_data_by_field($resource,$field){
	# Return the resource data for field $field in resource $resource
	# $field can also be a shortname
	global $rt_fieldtype_cache;
	if (is_numeric($field)){
		$value=sql_value("select value from resource_data where resource='$resource' and resource_type_field='".escape_check($field)."'","");
		if (!isset($rt_fieldtype_cache[$field])){
			$rt_fieldtype_cache[$field]=sql_value("select type value from resource_type_field where ref='".escape_check($field)."'","");
		} 
			
	} else {
		$value=sql_value("select value from resource_data where resource='$resource' and resource_type_field=(select ref from resource_type_field where name='".escape_check($field)."' limit 1)","");
		if (!isset($rt_fieldtype_cache[$field])){
			$rt_fieldtype_cache[$field]=sql_value("select type value from resource_type_field where name='".escape_check($field)."'","");
		}
	}

	if($rt_fieldtype_cache[$field]==8){
		$value=strip_tags($value);
		$value=str_replace("&nbsp;"," ",$value);
	}
	return $value;
}
	
if (!function_exists("get_users")){		
function get_users($group=0,$find="",$order_by="u.username",$usepermissions=false,$fetchrows=-1)
{
    # Returns a user list. Group or search term is optional.
    # The standard user group names are translated using $lang. Custom user group names are i18n translated.
    global $usergroup, $U_perm_strict;

    $sql = "";
	$find=strtolower($find);
    if ($group != 0) {$sql = "where usergroup IN ($group)";}
    if (strlen($find)>1)
      {
      if ($sql=="") {$sql = "where ";} else {$sql.= " and ";}
      $sql .= "(LOWER(username) like '%$find%' or LOWER(fullname) like '%$find%' or LOWER(email) like '%$find%' or LOWER(comments) like '%$find%')";
      }
    if (strlen($find)==1)
      {
      if ($sql=="") {$sql = "where ";} else {$sql.= " and ";}
      $sql .= "LOWER(username) like '$find%'";
      }
    if ($usepermissions && checkperm("U") && $U_perm_strict) {
        # Only return users in children groups to the user's group
        if ($sql=="") {$sql = "where ";} else {$sql.= " and ";}
        $sql.= "find_in_set('" . $usergroup . "',g.parent) ";
        $sql.= hook("getuseradditionalsql");
    }

    // Return users in both user's user group and children groups
    if ($usepermissions && checkperm('U') && !$U_perm_strict) {
    	$sql .= sprintf('
    			%1$s (g.ref = "%2$s" OR find_in_set("%2$s", g.parent))
    		',
    		($sql == '') ? 'WHERE' : ' AND',
    		$usergroup
    	);
    }
    $query = "select u.*,g.name groupname,g.ref groupref,g.parent groupparent,u.approved,u.created from user u left outer join usergroup g on u.usergroup=g.ref $sql order by $order_by";
    # Executes query.
    $r = sql_query($query, false, $fetchrows);

    # Translates group names in the newly created array.
    for ($n = 0;$n<count($r);$n++) {
        if (!is_array($r[$n])) {break;} # The padded rows can't be and don't need to be translated.
        $r[$n]["groupname"] = lang_or_i18n_get_translated($r[$n]["groupname"], "usergroup-");
    }

    return $r;

}
}	

function get_users_with_permission($permission)
{
    # Returns all the users who have the permission $permission.
    # The standard user group names are translated using $lang. Custom user group names are i18n translated.	

    # First find all matching groups.
    $groups = sql_query("select ref,permissions from usergroup");
    $matched = array();
    for ($n = 0;$n<count($groups);$n++) {
        $perms = trim_array(explode(",",$groups[$n]["permissions"]));
        if (in_array($permission,$perms)) {$matched[] = $groups[$n]["ref"];}
    }
    # Executes query.
	$r = sql_query("select u.*,g.name groupname,g.ref groupref,g.parent groupparent from user u left outer join usergroup g on u.usergroup=g.ref where g.ref in ('" . join("','",$matched) . "') order by username",false);

    # Translates group names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["groupname"] = lang_or_i18n_get_translated($r[$n]["groupname"], "usergroup-");
        $return[] = $r[$n]; # Adds to return array.
    }

    return $return;
}

function get_user_by_email($email)
{
	$r = sql_query("select u.*,g.name groupname,g.ref groupref,g.parent groupparent from user u left outer join usergroup g on u.usergroup=g.ref where u.email like '%$email%' order by username",false);

    # Translates group names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["groupname"] = lang_or_i18n_get_translated($r[$n]["groupname"], "usergroup-");
        $return[] = $r[$n]; # Adds to return array.
    }

    return $return;
}

function get_usergroups($usepermissions = false, $find = '', $id_name_pair_array = false)
{
    # Returns a list of user groups. The standard user groups are translated using $lang. Custom user groups are i18n translated.
    # Puts anything starting with 'General Staff Users' - in the English default names - at the top (e.g. General Staff).

    # Creates a query, taking (if required) the permissions  into account.
    $sql = "";
    if ($usepermissions && checkperm("U")) {
        # Only return users in children groups to the user's group
        global $usergroup,$U_perm_strict;
        if ($sql=="") {$sql = "where ";} else {$sql.= " and ";}
        if ($U_perm_strict) {
            //$sql.= "(parent='$usergroup')";
            $sql.= "find_in_set('" . $usergroup . "',parent)";
        }
        else {
            //$sql.= "(ref='$usergroup' or parent='$usergroup')";
            $sql.= "(ref='$usergroup' or find_in_set('" . $usergroup . "',parent))";
        }
    }

    # Executes query.
    global $default_group;
    $r = sql_query("select * from usergroup $sql order by (ref='$default_group') desc,name");

    # Translates group names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["name"] = lang_or_i18n_get_translated($r[$n]["name"], "usergroup-");
        $return[] = $r[$n]; # Adds to return array.
    }

    if (strlen($find)>0) {
        # Searches for groups with names which contains the string defined in $find.
        $initial_length = count($return);
        for ($n = 0;$n<$initial_length;$n++) {
            if (strpos(strtolower($return[$n]["name"]),strtolower($find))===false) {
                unset($return[$n]); # Removes this group.
            }
        }
        $return = array_values($return); # Reassigns the indices.
    }

    // Return only an array with ref => name pairs
    if($id_name_pair_array)
        {
        $return_id_name_array = array();

        foreach($return as $user_group)
            {
            $return_id_name_array[$user_group['ref']] = $user_group['name'];
            }

        return $return_id_name_array;
        }

    return $return;

}    

function get_usergroup($ref)
{
    # Returns the user group corresponding to the $ref. A standard user group name is translated using $lang. A custom user group name is i18n translated.

    $return = sql_query("select ref,name,permissions,fixed_theme,parent,search_filter,edit_filter,ip_restrict,resource_defaults,config_options,welcome_message,request_mode,allow_registration_selection,derestrict_filter,group_specific_logo from usergroup where ref='$ref'");
    if (count($return)==0) {return false;}
    else {
        $return[0]["name"] = lang_or_i18n_get_translated($return[0]["name"], "usergroup-");
        return $return[0];
    }
}

if (!function_exists("get_user")){
function get_user($ref)
	{
	global $udata_cache;
        if (isset($udata_cache[$ref])){
          $return=$udata_cache[$ref];
        } else {
	$udata_cache[$ref]=sql_query("select u.*, g.permissions, g.fixed_theme, g.parent, g.search_filter, g.edit_filter, g.ip_restrict ip_restrict_group, g.name groupname, u.ip_restrict ip_restrict_user, u.search_filter_override, resource_defaults,g.config_options,g.request_mode, g.derestrict_filter from user u left join usergroup g on u.usergroup=g.ref where u.ref='$ref'");
    }
    
	# Return a user's credentials.
	if (count($udata_cache[$ref])>0) {return $udata_cache[$ref][0];} else {return false;}
	}
}
	
if(!function_exists('save_user')){
/**
* Function used to update or delete a user.
* Note: data is taken from the submitted form
* 
* @param string $ref ID of the user
* 
* @return boolean|string
*/
function save_user($ref)
    {
    global $lang, $allow_password_email, $home_dash;

    # Save user details, data is taken from the submitted form.
    if(getval('deleteme', '') != '')
        {
        sql_query("DELETE FROM user WHERE ref='$ref'");
        include dirname(__FILE__) ."/dash_functions.php";
        empty_user_dash($ref);
        log_activity(null, LOG_CODE_DELETED, null, 'user', null, $ref);

        return true;
        }
    else
        {
        $current_user_data = get_user($ref);

        // Get submitted values
        $username               = trim(getvalescaped('username', ''));
        $password               = trim(getvalescaped('password', ''));
        $fullname               = trim(getvalescaped('fullname', ''));
        $email                  = trim(getvalescaped('email', ''));
        $expires                = "'" . getvalescaped('account_expires', '') . "'";
        $usergroup              = trim(getvalescaped('usergroup', ''));
        $ip_restrict            = trim(getvalescaped('ip_restrict', ''));
        $search_filter_override = trim(getvalescaped('search_filter_override', ''));
        $comments               = trim(getvalescaped('comments', ''));

        $suggest = getval('suggest', '');

        # Username or e-mail address already exists?
        $c = sql_value("SELECT count(*) value FROM user WHERE ref <> '$ref' AND (username = '" . $username . "' OR email = '" . $email . "')", 0);
        if($c > 0 && $email != '')
            {
            return false;
            }

        // Password checks:
        if($suggest != '')
            {
            $password = make_password();
            }
        elseif($password != $lang['hidden'])	
            {
            $message = check_password($password);
            if($message !== true)
                {
                return $message;
                }
            }

        if($expires == "''")
            {
            $expires = 'null';
            }

        $passsql = '';
        if($password != $lang['hidden'])
            {
            # Save password.
            if($suggest == '')
                {
                $password = hash('sha256', md5('RS' . $username . $password));
                }

            $passsql = ",password='" . $password . "',password_last_change=now()";
            }

        // Full name checks
        if('' == $fullname && '' == $suggest)
            {
            return $lang['setup-admin_fullname_error'];
            }

        $additional_sql = hook('additionaluserfieldssave');

        log_activity(null, LOG_CODE_EDITED, $username, 'user', 'username', $ref);
        log_activity(null, LOG_CODE_EDITED, $fullname, 'user', 'fullname', $ref);
        log_activity(null, LOG_CODE_EDITED, $email, 'user', 'email', $ref);

        if(isset($current_user_data['usergroup']) && $current_user_data['usergroup'] != $usergroup)
            {
            log_activity(null, LOG_CODE_EDITED, $usergroup, 'user', 'usergroup', $ref);
            }

        log_activity(null, LOG_CODE_EDITED, $ip_restrict, 'user', 'ip_restrict', $ref, null, '');
        log_activity(null, LOG_CODE_EDITED, $search_filter_override, 'user', 'search_filter_override', $ref, null, '');
        log_activity(null, LOG_CODE_EDITED, $expires, 'user', 'account_expires', $ref);
        log_activity(null, LOG_CODE_EDITED, $comments, 'user', 'comments', $ref);
        log_activity(null, LOG_CODE_EDITED, ((getval('approved', '') == '') ? '0' : '1'), 'user', 'approved', $ref);

        sql_query("update user set
        username='" . $username . "'" . $passsql . ",
        fullname='" . $fullname . "',
        email='" . $email . "',
        usergroup='" . $usergroup . "',
        account_expires=$expires,
        ip_restrict='" . $ip_restrict . "',
        search_filter_override='" . $search_filter_override . "',
        comments='" . $comments . "',
        approved='" . ((getval('approved', '') == "") ? '0' : '1') . "' $additional_sql where ref='$ref'");
        }

        // Add user group dash tiles as soon as we've changed the user group
        if($home_dash)
            {
            // If user group has changed, remove all user dash tiles that were valid for the old user group
            if((isset($current_user_data['usergroup']) && '' != $current_user_data['usergroup']) && $current_user_data['usergroup'] != $usergroup)
                {
                sql_query("DELETE FROM user_dash_tile WHERE user = '{$ref}' AND dash_tile IN (SELECT dash_tile FROM usergroup_dash_tile WHERE usergroup = '{$current_user_data['usergroup']}')");
                }

            include __DIR__ . '/dash_functions.php';
            build_usergroup_dash($usergroup, $ref);
            }

    if($allow_password_email && getval('emailme', '') != '')
        {
        email_user_welcome(getval('email', ''), getval('username', ''), getval('password', ''), $usergroup);
        }
    elseif(getval('emailresetlink', '') != '')
        {
        email_reset_link($email, true);
        }
		
	if(getval('approved', '')!='')
		{
		# Clear any user request messages
	    message_remove_related(USER_REQUEST,$ref);
		}

    return true;
    }
}

function email_user_welcome($email,$username,$password,$usergroup)
	{
	global $applicationname,$email_from,$baseurl,$lang,$email_url_save_user;
	
	# Fetch any welcome message for this user group
	$welcome=sql_value("select welcome_message value from usergroup where ref='" . $usergroup . "'","");
	if (trim($welcome)!="") {$welcome.="\n\n";}
	
	$templatevars['welcome']=$welcome;
	$templatevars['username']=$username;
	
        $templatevars['password']=$password;
        if (trim($email_url_save_user)!=""){$templatevars['url']=$email_url_save_user;}
        else {$templatevars['url']=$baseurl;}
        $message=$templatevars['welcome'] . $lang["newlogindetails"] . "\n\n" . $lang["username"] . ": " . $templatevars['username'] . "\n" . $lang["password"] . ": " . $templatevars['password'] . "\n\n". $templatevars['url'];
          	
	send_mail($email,$applicationname . ": " . $lang["youraccountdetails"],$message,"","","emaillogindetails",$templatevars);
	}
        


if (!function_exists("email_reminder")){
function email_reminder($email)
	{
	# Send a password reminder.
	global $password_brute_force_delay, $allow_password_email;
	if ($allow_password_email || $email=="") {return false;}
	$details=sql_query("select username from user where email like '$email' and approved=1");
	if (count($details)==0) {sleep($password_brute_force_delay);return false;}
	$details=$details[0];
	global $applicationname,$email_from,$baseurl,$lang,$email_url_remind_user;
	$password=make_password();
	$password_hash=md5("RS" . $details["username"] . $password);
	
	sql_query("update user set password='$password_hash' where username='" . escape_check($details["username"]) . "'");
	
	$templatevars['username']=$details["username"];
	$templatevars['password']=$password;
    if (trim($email_url_remind_user)!=""){$templatevars['url']=$email_url_remind_user;}
    else {$templatevars['url']=$baseurl;}

	
	$message=$lang["newlogindetails"] . "\n\n" . $lang["username"] . ": " . $templatevars['username'] . "\n" . $lang["password"] . ": " . $templatevars['password'] . "\n\n". $templatevars['url'];
	send_mail($email,$applicationname . ": " . $lang["newpassword"],$message,"","","emailreminder",$templatevars);
	return true;
	}
}

if (!function_exists("email_reset_link")){
function email_reset_link($email,$newuser=false)
	{
	debug("password_reset - checking for email: " . $email);
	# Send a link to reset password
	global $password_brute_force_delay, $scramble_key;

	if($email == '')
		{
		return false;
		}

	$details = sql_query("SELECT ref, username, usergroup FROM user WHERE email LIKE '" . escape_check($email) . "' AND approved = 1 AND (account_expires IS NULL OR account_expires > now());");

	if(count($details) == 0)
		{
		sleep($password_brute_force_delay);
		return false;
		}

	$details = $details[0];

	global $applicationname, $email_from, $baseurl, $lang, $email_url_remind_user;

	$password_reset_url_key = create_password_reset_key($details['username']);        

	$templatevars['url'] = $baseurl . '/?rp=' . $details['ref'] . $password_reset_url_key;
        
	if($newuser)
        {
        $templatevars['username']=$details["username"];

        // Fetch any welcome message for this user group
        $welcome = sql_value('SELECT welcome_message AS value FROM usergroup WHERE ref = \'' . $details['usergroup'] . '\'', '');

        if(trim($welcome) != '')
            {
            $welcome .= "\n\n";
            }

        $templatevars['welcome']=$welcome;

        $message = $templatevars['welcome'] . $lang["newlogindetails"] . "\n\n" . $baseurl . "\n\n" . $lang["username"] . ": " . $templatevars['username'] . "\n\n" .  $lang["passwordnewemail"] . "\n" . $templatevars['url'];
        send_mail($email,$applicationname . ": " . $lang["newlogindetails"],$message,"","","passwordnewemailhtml",$templatevars);
        }
    else
        {
        $templatevars['username']=$details["username"];
        $message=$lang["username"] . ": " . $templatevars['username'];
        $message.="\n\n" . $lang["passwordresetemail"] . "\n\n" . $templatevars['url'];
        send_mail($email,$applicationname . ": " . $lang["resetpassword"],$message,"","","password_reset_email_html",$templatevars);
        }	
	
	return true;
	}
}

if (!function_exists("auto_create_user_account")){
function auto_create_user_account()
	{
	# Automatically creates a user account (which requires approval unless $auto_approve_accounts is true).
	global $applicationname, $user_email, $baseurl, $email_notify, $lang, $user_account_auto_creation_usergroup, $registration_group_select, 
           $auto_approve_accounts, $auto_approve_domains, $customContents, $language, $home_dash;

	# Work out which user group to set. Allow a hook to change this, if necessary.
	$altgroup=hook("auto_approve_account_switch_group");
	if ($altgroup!==false)
		{
		$usergroup=$altgroup;
		}
	else
		{
		$usergroup=$user_account_auto_creation_usergroup;
		}

	if ($registration_group_select)
		{
		$usergroup=getvalescaped("usergroup","",true);
		# Check this is a valid selectable usergroup (should always be valid unless this is a hack attempt)
		if (sql_value("select allow_registration_selection value from usergroup where ref='$usergroup'",0)!=1) {exit("Invalid user group selection");}
		}

	$newusername=escape_check(make_username(getval("name","")));

	#check if account already exists
	$check=sql_value("select email value from user where email = '$user_email'","");
	if ($check!=""){return $lang["useremailalreadyexists"];}

	# Prepare to create the user.
	$email=trim(getvalescaped("email","")) ;
	$password=make_password();

	# Work out if we should automatically approve this account based on $auto_approve_accounts or $auto_approve_domains
	$approve=false;
        
	# Block immediate reset
	$bypassemail=false;
        
	if ($auto_approve_accounts==true)
		{
		$approve=true;
		$bypassemail=true; // We can send user  direct to password reset page
		}
	elseif (count($auto_approve_domains)>0)
		{
		# Check e-mail domain.
		foreach ($auto_approve_domains as $domain=>$set_usergroup)
			{
			// If a group is not specified the variables don't get set correctly so we need to correct this
			if (is_numeric($domain)){$domain=$set_usergroup;$set_usergroup="";}
			if (substr(strtolower($email),strlen($email)-strlen($domain)-1)==("@" . strtolower($domain)))
				{
				# E-mail domain match.
				$approve=true;                                

				# If user group is supplied, set this
				if (is_numeric($set_usergroup)) {$usergroup=$set_usergroup;}
				}
			}
		}

	# Create the user
	sql_query("insert into user (username,password,fullname,email,usergroup,comments,approved,lang) values ('" . $newusername . "','" . $password . "','" . getvalescaped("name","") . "','" . $email . "','" . $usergroup . "','" . ( escape_check($customContents) . "\n" . getvalescaped("userrequestcomment","")  ) . "'," . (($approve)?1:0) . ",'$language')");
	$new = sql_insert_id();

    // Create dash tiles for the new user
    if($home_dash)
        {
        include_once dirname(__FILE__) . '/dash_functions.php';

        create_new_user_dash($new);
        build_usergroup_dash($usergroup, $new);
        }

    hook("afteruserautocreated", "all",array("new"=>$new));
	global $anonymous_login;
    if(isset($anonymous_login))
        {
        global $rs_session;
        $rs_session=get_rs_session_id();
        if($rs_session!==false)
            {				
            # Copy any anonymous session collections to the new user account 
            if (!function_exists("get_session_collections"))
                {
                include_once dirname(__FILE__) . "/../include/collections_functions.php";
                }

            global $username, $userref;

            if(is_array($anonymous_login) && array_key_exists($baseurl, $anonymous_login))
                {
                $anonymous_login = $anonymous_login[$baseurl];
                }

            $username=$anonymous_login;
            $userref=sql_value("SELECT ref value FROM user where username='$anonymous_login'","");
            $sessioncollections=get_session_collections($rs_session,$userref,false);
            if(count($sessioncollections)>0)
                {
                foreach($sessioncollections as $sessioncollection)
                    {
                    update_collection_user($sessioncollection,$new);
                    }
                sql_query("UPDATE user SET current_collection='$sessioncollection' WHERE ref='$new'");
                }
            }
        }
    if ($approve)
		{
		# Auto approving		
		if($bypassemail)
			{
			// No requirement to check anything else e.g. a valid email domain. We can take user direct to the password reset page to set the new account
			$password_reset_url_key=create_password_reset_key($newusername);
			redirect($baseurl . "?rp=" . $new . $password_reset_url_key);			
			exit();
			}
		else
			{
			email_reset_link($email, true);
			redirect($baseurl."/pages/done.php?text=user_request");
			exit();
			}			
		}
	else
		{
		# Not auto approving.
		# Build a message to send to an admin notifying of unapproved user (same as email_user_request(),
		# but also adds the new user name to the mail)
		
		$templatevars['name']=getval("name","");
		$templatevars['email']=getval("email","");
		$templatevars['userrequestcomment']=getval("userrequestcomment","");
		$templatevars['userrequestcustom']=$customContents;
		$templatevars['linktouser']="$baseurl?u=$new";

		$message=$lang["userrequestnotification1"] . "\n\n" . $lang["name"] . ": " . $templatevars['name'] . "\n\n" . $lang["email"] . ": " . $templatevars['email'] . "\n\n" . $lang["comment"] . ": " . $templatevars['userrequestcomment'] . "\n\n" . $lang["ipaddress"] . ": '" . $_SERVER["REMOTE_ADDR"] . "'\n\n" . $customContents . "\n\n" . $lang["userrequestnotification3"] . "\n$baseurl?u=$new";
		
		$notificationmessage=$lang["userrequestnotification1"] . "\n" . $lang["name"] . ": " . $templatevars['name'] . "\n" . $lang["email"] . ": " . $templatevars['email'] . "\n" . $lang["comment"] . ": " . $templatevars['userrequestcomment'] . "\n" . $lang["ipaddress"] . ": '" . $_SERVER["REMOTE_ADDR"] . "'\n" . $customContents . "\n" . $lang["userrequestnotification3"];
       
	   // Need to global the usergroup so that we can find the appropriate admins
	   global $usergroup;
       $approval_notify_users=get_notification_users("USER_ADMIN"); 
       $message_users=array();
	   global $user_pref_user_management_notifications, $email_user_notifications;
	   foreach($approval_notify_users as $approval_notify_user)
			{
			get_config_option($approval_notify_user['ref'],'user_pref_user_management_notifications', $send_message, $user_pref_user_management_notifications);
			if(!$send_message){continue;} 
			
			get_config_option($approval_notify_user['ref'],'email_user_notifications', $send_email, $email_user_notifications);    
			if($send_email && $approval_notify_user["email"]!="")
				{
				send_mail($approval_notify_user["email"],$applicationname . ": " . $lang["requestuserlogin"] . " - " . getval("name",""),$message,"",$user_email,"emailuserrequest",$templatevars,getval("name",""));
				}        
			else
				{
				$message_users[]=$approval_notify_user["ref"];
				}
			}
		if (count($message_users)>0)
			{
			// Send a message with long timeout (30 days)
			message_add($message_users,$notificationmessage,$templatevars['linktouser'],$new,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,60 * 60 *24 * 30, USER_REQUEST,$new );
			}
		}

	return true;
	}
} //end function replace hook

function email_user_request()
	{
	# E-mails the submitted user request form to the team.
	global $applicationname,$user_email,$baseurl,$email_notify,$lang,$customContents;

	# Build a message

	$message=$lang["userrequestnotification1"] . "\n\n" . $lang["name"] . ": " . getval("name","") . "\n\n" . $lang["email"] . ": " . getval("email","") . "\n\n" . $lang["comment"] . ": " . getval("userrequestcomment","") . "\n\n" . $lang["ipaddress"] . ": '" . $_SERVER["REMOTE_ADDR"] . "'\n\n" . $customContents . "\n\n" . $lang["userrequestnotification2"] . "\n$baseurl";
	
	$notificationmessage=$lang["userrequestnotification1"] . "\n" . $lang["name"] . ": " . getvalescaped("name","") . "\n" . $lang["email"] . ": " . getvalescaped("email","") . "\n" . $lang["comment"] . ": " . getvalescaped("userrequestcomment","") . "\n" . $lang["ipaddress"] . ": '" . $_SERVER["REMOTE_ADDR"] . "'\n" . escape_check($customContents) . "\n";
	
	$approval_notify_users=get_notification_users("USER_ADMIN"); 
	$message_users=array();
	foreach($approval_notify_users as $approval_notify_user)
			{
			get_config_option($approval_notify_user['ref'],'user_pref_user_management_notifications', $send_message);		  
            if($send_message==false){continue;}		
			
			get_config_option($approval_notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $approval_notify_user["email"]!="")
				{
				send_mail($approval_notify_user["email"],$applicationname . ": " . $lang["requestuserlogin"] . " - " . getval("name",""),$message,"",$user_email,"","",getval("name",""));
				}        
			else
				{
				$message_users[]=$approval_notify_user["ref"];
				}
			}
		if (count($message_users)>0)
			{
			// Send a message with long timeout (30 days)
            message_add($message_users,$notificationmessage,"",0,MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN,60 * 60 *24 * 30);
			}
	return true;
	}

function new_user($newuser)
	{
	global $lang,$home_dash;
	# Username already exists?
	$c=sql_value("select count(*) value from user where username='$newuser'",0);
	if ($c>0) {return false;}
	
	# Create a new user with username $newuser. Returns the created user reference.
	sql_query("insert into user(username) values ('" . escape_check($newuser) . "')");
	
	$newref=sql_insert_id();
	
	#Create Default Dash for the new user
	if($home_dash)
		{
		include_once dirname(__FILE__)."/dash_functions.php";
		create_new_user_dash($newref);
		}
	
	# Create a collection for this user, the collection name is translated when displayed!
	$new=create_collection($newref,"My Collection",0,1); # Do not translate this string!
	# set this to be the user's current collection
	sql_query("update user set current_collection='$new' where ref='$newref'");
	log_activity($lang["createuserwithusername"],LOG_CODE_CREATED,$newuser,'user','ref',$newref,null,'');
	
	return $newref;
	}

function get_stats_activity_types()
	{
	# Returns a list of activity types for which we have stats data (Search, User Session etc.)
	return sql_array("SELECT DISTINCT activity_type `value` FROM daily_stat ORDER BY activity_type");
	}

function get_stats_years()
	{
	# Returns a list of years for which we have statistics.
	return sql_array("select distinct year value from daily_stat order by year");
	}

function newlines($text)
	{
	# Replace escaped newlines with real newlines.
	$text=str_replace("\\n","\n",$text);
	$text=str_replace("\\r","\r",$text);
	return $text;
	}

function get_active_users()
	{
    global $usergroup, $U_perm_strict;
    $sql = "where logged_in=1 and unix_timestamp(now())-unix_timestamp(last_active)<(3600*2)";
    if (checkperm("U") && $U_perm_strict)
        {
        $sql.= " and find_in_set('" . $usergroup . "',g.parent) ";
        }

    // Return users in both user's user group and children groups
    elseif (checkperm('U') && !$U_perm_strict)
        {
    	$sql .= " and (g.ref = '" . $usergroup . "' OR find_in_set('" . $usergroup . "', g.parent))";
        }
    
    # Returns a list of all active users, i.e. users still logged on with a last-active time within the last 2 hours.
    return sql_query("select u.username,round((unix_timestamp(now())-unix_timestamp(u.last_active))/60,0) t from user u left outer join usergroup g on u.usergroup=g.ref $sql order by t;");
    }

function get_all_site_text($findpage="",$findname="",$findtext="")
	{
	# Returns a list of all available editable site text (content).
	# If $find is specified a search is performed across page, name and text fields.
	global $defaultlanguage,$languages,$applicationname,$storagedir,$homeanim_folder;	
	$findname=trim($findname);
	$findpage=trim($findpage);
	$findtext=trim($findtext);
	
        $return=array();
        
        if ($findtext!="")
            {
            # When searching text, search all languages to pick up matches for languages other than the default. Add array so that default is first then we can skip adding duplicates.
			$search_languages=array($defaultlanguage);
			$search_languages = $search_languages + array_keys($languages);	
			}
        else
            {
            # Process only the default language when not searching.
            $search_languages=array($defaultlanguage);
            }
			
		
		global $language, $lang; // Need to save these for later so we can revert after search
		$languagesaved=$language;
		$langsaved=$lang;
		
        foreach ($search_languages as $search_language)
            {
            # Reset $lang and include the appropriate file to search.
            $lang=array();
            include dirname(__FILE__)."/../languages/" . safe_file_name($search_language) . ".php";
            
			# Include plugin languages in reverse order as per db.php
			global $plugins;
			$language = $search_language;
			for ($n=count($plugins)-1;$n>=0;$n--)
				{				
				register_plugin_language($plugins[$n]);
				}		
			
            # Find language strings.
            ksort($lang);
            foreach ($lang as $key=>$text)
                {
                $pagename="";
                $s=explode("__",$key);
                if (count($s)>1) {$pagename=$s[0];$key=$s[1];}
                
                if
                    (
                    !is_array($text) # Do not support overrides for array values (used for months)... complex UI needed and very unlikely to need overrides.
                    &&
                    ($findname=="" || stripos($key,$findname)!==false)
                    &&            
                    ($findpage=="" || stripos($pagename,$findpage)!==false)
                    &&
                    ($findtext=="" || stripos($text,$findtext)!==false)
                    )
                    {
					$testrow=array();
                    $testrow["page"]=$pagename;
                    $testrow["name"]=$key;
                    $testrow["text"]=$text;
                    $testrow["language"]=$defaultlanguage;
                    $testrow["group"]="";
					// Make sure this isn't already set for default/another language
					if(!in_array($testrow,$return))
						{
						$row["page"]=$pagename;
						$row["name"]=$key;
						$row["text"]=$text;
						$row["language"]=$search_language;
						$row["group"]="";
						$return[]=$row;
						}
                    }
                }
            }
		
		// Need to revert to saved values
		$language=$languagesaved;
		$lang=$langsaved;
        
        # If searching, also search overridden text in site_text and return that also.
        if ($findtext!="" || $findpage!="" || $findname!="")
            {
            if ($findtext!="") {$search="text like '%" . escape_check($findtext) . "%'";}
            if ($findpage!="") {$search="page like '%" . escape_check($findpage) . "%'";}         
            if ($findname!="") {$search="name like '%" . escape_check($findname) . "%'";}          
            
            $site_text=sql_query ("select * from site_text where $search");
			
            foreach ($site_text as $text)
                {
                $row["page"]=$text["page"];
                $row["name"]=$text["name"];
                $row["text"]=$text["text"];
                $row["language"]=$text["language"];
                $row["group"]=$text["specific_to_group"];
				// Make sure we dont'include the default if we have overwritten 
                $customisedtext=false;
				for($n=0;$n<count($return);$n++)
					{
					if ($row["page"]==$return[$n]["page"] && $row["name"]==$return[$n]["name"] && $row["language"]==$return[$n]["language"] && $row["group"]==$return[$n]["group"])
						{
						$customisedtext=true;
						$return[$n]=$row;
						}						
					}
				if(!$customisedtext)
					{$return[]=$row;}				
                }
            }  
        return $return;
	}

function get_site_text($page,$name,$getlanguage,$group)
	{
	# Returns a specific site text entry.
    global $defaultlanguage, $lang, $language; // Registering plugin text uses $language and $lang 	
    // Need to save these globals for later so we can revert after search
	$languagesaved=$language;
	$langsaved=$lang;
        
	if ($group=="") {$g="null";$gc="is";} else {$g="'" . $group . "'";$gc="=";}
	
	$text=sql_query ("select * from site_text where page='$page' and name='$name' and language='$getlanguage' and specific_to_group $gc $g");
	if (count($text)>0)
		{
                return $text[0]["text"];
                }
        # Fall back to default language.
	$text=sql_query ("select * from site_text where page='$page' and name='$name' and language='$defaultlanguage' and specific_to_group $gc $g");
	if (count($text)>0)
		{
                return $text[0]["text"];
                }
                
        # Fall back to default group.
	$text=sql_query ("select * from site_text where page='$page' and name='$name' and language='$defaultlanguage' and specific_to_group is null");
	if (count($text)>0)
		{
                return $text[0]["text"];
                }
        
        # Fall back to language strings.
        if ($page=="") {$key=$name;} else {$key=$page . "__" . $name;}
        
        # Include specific language(s)
        @include dirname(__FILE__)."/../languages/" . safe_file_name($defaultlanguage) . ".php";
        @include dirname(__FILE__)."/../languages/" . safe_file_name($getlanguage) . ".php";
		
		# Include plugin languages in reverse order as per db.php
		global $plugins;	
		$language = $defaultlanguage;
		for ($n=count($plugins)-1;$n>=0;$n--)
			{				
			register_plugin_language($plugins[$n]);
			}
        $language = $getlanguage;
		for ($n=count($plugins)-1;$n>=0;$n--)
			{				
			register_plugin_language($plugins[$n]);
			}
        
        // Revert globals to saved values
		$language=$languagesaved;
		$lang=$langsaved;
        
		if (array_key_exists($key,$lang)) {return $lang[$key];} else {return "";}
	}

function check_site_text_custom($page,$name)
	{
	# Check if site text section is custom, i.e. deletable.
	
	$check=sql_query ("select custom from site_text where page='$page' and name='$name'");
	if (isset($check[0]["custom"])){return $check[0]["custom"];}
	}

function save_site_text($page,$name,$language,$group)
	{
	global $lang;
	# Saves the submitted site text changes to the database.

	if ($group=="") {$g="null";$gc="is";} else {$g="'" . $group . "'";$gc="=";}
	
	global $custom,$newcustom,$defaultlanguage;
	
	if($newcustom)
		{
		$test=sql_query("select * from site_text where page='$page' and name='$name'");
		if (count($test)>0){return true;}
		}
	if ($custom==""){$custom=0;}
	if (getval("deletecustom","")!="")
		{
		sql_query("delete from site_text where page='$page' and name='$name'");
		}
	elseif (getval("deleteme","")!="")
		{
		sql_query("delete from site_text where page='$page' and name='$name' and specific_to_group $gc $g");
		}
	elseif (getval("copyme","")!="")
		{
		sql_query("insert into site_text(page,name,text,language,specific_to_group,custom) values ('$page','$name','" . getvalescaped("text","") . "','$language',$g,'$custom')");
		}
	elseif (getval("newhelp","")!="")
		{
		global $newhelp;
		$check=sql_query("select * from site_text where page = 'help' and name='$newhelp'");
		if (!isset($check[0])){
			sql_query("insert into site_text(page,name,text,language,specific_to_group) values ('$page','$newhelp','','$language',$g)");
			}
		}	
	else
		{
		$text=sql_query ("select * from site_text where page='$page' and name='$name' and language='$language' and specific_to_group $gc $g");
		if (count($text)==0)
			{
			# Insert a new row for this language/group.
			sql_query("insert into site_text(page,name,language,specific_to_group,text,custom) values ('$page','$name','$language',$g,'" . getvalescaped("text","") . "','$custom')");
			log_activity($lang["text"],LOG_CODE_CREATED,getvalescaped("text",""),'site_text',null,"'{$page}','{$name}','{$language}',{$g}");
			}
		else
			{
			# Update existing row
			sql_query("update site_text set text='" . getvalescaped("text","") . "' where page='$page' and name='$name' and language='$language' and specific_to_group $gc $g");
			log_activity($lang["text"],LOG_CODE_EDITED,getvalescaped("text",""),'site_text',null,"'{$page}','{$name}','{$language}',{$g}");
			}
                        
                # Language clean up - remove all entries that are exactly the same as the default text.
                $defaulttext=sql_value ("select text value from site_text where page='$page' and name='$name' and language='$defaultlanguage' and specific_to_group $gc $g","");
                sql_query("delete from site_text where page='$page' and name='$name' and language!='$defaultlanguage' and trim(text)='" . trim(escape_check($defaulttext)) . "'");
                
		}
	}
	
function string_similar($string1,$string2)
	{
	# Returns an integer score based on how similar the two strings are.
	# This was used when importing data for "fuzzy" keyword/option matching.
	$score=0;
	$string1=trim(strtolower($string1));$string2=trim(strtolower($string2));
	if ($string1==$string2) {return 9999;}
	if (substr($string1,0,1)==substr($string2,0,1)) {$score+=10;}
	for ($n=0;$n<strlen($string1)-1;$n++)
		{
		$pair=substr($string1,$n,2);
		for ($m=0;$m<strlen($string2)-1;$m++)
			{
			if ($pair==substr($string2,$m,2)) {$score++;}
			}
		}
	
	return $score;
	}

function formatfilesize($bytes)
	{
	# Return a human-readable string representing $bytes in either KB or MB.
	
	global $lang;
	if ($bytes<1024)
		{
		return number_format((double)$bytes) . "&nbsp;".$lang["byte-symbol"];
		}
	elseif ($bytes<pow(1024,2))
		{
		return number_format((double)ceil($bytes/1024)) . "&nbsp;".$lang["kilobyte-symbol"];
		}
	elseif ($bytes<pow(1024,3))
		{
		return number_format((double)$bytes/pow(1024,2),1) . "&nbsp;".$lang["megabyte-symbol"];
		}
	elseif ($bytes<pow(1024,4))
		{
		return number_format((double)$bytes/pow(1024,3),1) . "&nbsp;".$lang["gigabyte-symbol"];
		}
	else
		{
		return number_format((double)$bytes/pow(1024,4),1) . "&nbsp;".$lang["terabyte-symbol"];
		}
	}


function filesize2bytes($str) {
/**
 * Converts human readable file size (e.g. 10 MB, 200.20 GB) into bytes.
 *
 * @param string $str
 * @return int the result is in bytes
 * @author Svetoslav Marinov
 * @author http://slavi.biz
 */
    $bytes = 0;

    $bytes_array = array(
        'b' => 1,
        'kb' => 1024,
        'mb' => 1024 * 1024,
        'gb' => 1024 * 1024 * 1024,
        'tb' => 1024 * 1024 * 1024 * 1024,
        'pb' => 1024 * 1024 * 1024 * 1024 * 1024,
    );

    $bytes = floatval($str);

    if (preg_match('#([KMGTP]?B)$#si', $str, $matches) && !empty($bytes_array[strtolower($matches[1])])) {
        $bytes *= $bytes_array[strtolower($matches[1])];
    }

    $bytes = intval(round($bytes, 2));
	
	#add leading zeroes (as this can be used to format filesize data in resource_data for sorting)
    return sprintf("%010d",$bytes);
} 

function get_mime_type($path, $ext = null)
	{
	global $mime_type_by_extension;
	if (empty($ext))
		$ext = pathinfo($path, PATHINFO_EXTENSION);
	if (isset($mime_type_by_extension[$ext]))
		{
		return $mime_type_by_extension[$ext];
		}

	# Get mime type via exiftool if possible
	$exiftool_fullpath = get_utility_path("exiftool");
	if ($exiftool_fullpath!=false)
		{
		$command=$exiftool_fullpath . " -s -s -s -t -mimetype " . escapeshellarg($path);
		return run_command($command);
		}

	return "application/octet-stream";
	}

if (!function_exists("change_password")){
function change_password($password)
	{
	# Sets a new password for the current user.
	global $userref,$username,$lang,$userpassword, $password_reset_mode;

	# Check password
	$message=check_password($password);
	if ($message!==true) {return $message;}

	# Generate new password hash
	$password_hash=hash('sha256', md5("RS" . $username . $password));
	
	# Check password is not the same as the current
	if ($userpassword==$password_hash) {return $lang["password_matches_existing"];}
	
	sql_query("update user set password='$password_hash', password_reset_hash=NULL, password_last_change=now() where ref='$userref' limit 1");
        return true;
	}
}
	
function make_password()
	{
	# Generate a password using the configured settings.
	
	global $password_min_length, $password_min_alpha, $password_min_uppercase, $password_min_numeric, $password_min_special;

	$lowercase="abcdefghijklmnopqrstuvwxyz";
	$uppercase="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$alpha=$uppercase . $lowercase;
	$numeric="0123456789";
	$special="!@$%^&*().?";
	
	$password="";
	
	# Add alphanumerics
	for ($n=0;$n<$password_min_alpha;$n++)
		{
		$password.=substr($alpha,rand(0,strlen($alpha)-1),1);
		}
	
	# Add upper case
	for ($n=0;$n<$password_min_uppercase;$n++)
		{
		$password.=substr($uppercase,rand(0,strlen($uppercase)-1),1);
		}
	
	# Add numerics
	for ($n=0;$n<$password_min_numeric;$n++)
		{
		$password.=substr($numeric,rand(0,strlen($numeric)-1),1);
		}
	
	# Add special
	for ($n=0;$n<$password_min_special;$n++)
		{
		$password.=substr($special,rand(0,strlen($special)-1),1);
		}

	# Pad with lower case
	$padchars=$password_min_length-strlen($password);
	for ($n=0;$n<$padchars;$n++)
		{
		$password.=substr($lowercase,rand(0,strlen($lowercase)-1),1);
		}
		
	# Shuffle the password.
	$password=str_shuffle($password);
	
	# Check the password
	$check=check_password($password);
	if ($check!==true) {exit("Error: unable to automatically produce a password that met the criteria. Please check the password criteria in config.php. Generated password was '$password'. Error was: " . $check);}
	
    return $password;
	}

function bulk_mail($userlist,$subject,$text,$html=false,$message_type=MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL,$url="")
    {
    global $email_from,$lang,$applicationname;
    
    # Attempt to resolve all users in the string $userlist to user references.
    if (trim($userlist)=="") {return ($lang["mustspecifyoneuser"]);}
    $userlist=resolve_userlist_groups($userlist);
    $ulist=trim_array(explode(",",$userlist));

	$templatevars['text']=stripslashes(str_replace("\\r\\n","\n",$text));
	$body=$templatevars['text'];
	
	if ($message_type==MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL || $message_type==(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN))
		{
		$emails=resolve_user_emails($ulist);
		$emails=$emails['emails'];

		# Send an e-mail to each resolved user
		for ($n=0;$n<count($emails);$n++)
			{
			if ($emails[$n]!="")
				{
				send_mail($emails[$n],$subject,$body,$applicationname,$email_from,"emailbulk",$templatevars,$applicationname,"",$html);
				}
			}
		}
	if ($message_type==MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN || $message_type==(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN))
		{
		$user_refs = array();
		foreach ($ulist as $user)
			{
			$user_ref = sql_value("SELECT ref AS value FROM user WHERE username='" . escape_check($user) . "'", false);
			if ($user_ref !== false)
				{
				array_push($user_refs,$user_ref);
				}
			}
		if($message_type==(MESSAGE_ENUM_NOTIFICATION_TYPE_EMAIL | MESSAGE_ENUM_NOTIFICATION_TYPE_SCREEN) && $html)
			{
			# strip the tags out
			$body=strip_tags($body);
			}
		message_add($user_refs,$body,$url,null,$message_type);
		}

    # Return an empty string (all OK).
    return "";
    }

function send_mail($email,$subject,$message,$from="",$reply_to="",$html_template="",$templatevars=null,$from_name="",$cc="",$bcc="")
	{
	# Send a mail - but correctly encode the message/subject in quoted-printable UTF-8.
	
	# NOTE: $from is the name of the user sending the email,
	# while $from_name is the name that should be put in the header, which can be the system name
	# It is necessary to specify two since in all cases the email should be able to contain the user's name.
	
	# old mail function remains the same to avoid possible issues with phpmailer
	# send_mail_phpmailer allows for the use of text and html (multipart) emails,
	# and the use of email templates in Manage Content 

	global $always_email_from_user;
	if($always_email_from_user)
		{
		global $username, $useremail, $userfullname;
		$from_name=($userfullname!="")?$userfullname:$username;
		$from=$useremail;
		$reply_to=$useremail;
		}

	global $always_email_copy_admin;
	if($always_email_copy_admin)
		{
		global $email_notify;
		$bcc.="," . $email_notify;
		}

	# Send a mail - but correctly encode the message/subject in quoted-printable UTF-8.
	global $use_phpmailer;
	if ($use_phpmailer){
		send_mail_phpmailer($email,$subject,$message,$from,$reply_to,$html_template,$templatevars,$from_name,$cc,$bcc); 
		return true;
		}
	
	# No email address? Exit.
	if (trim($email)=="") {return false;}
	
	# Include footer
	global $email_footer;
	global $disable_quoted_printable_enc;
	
	# Work out correct EOL to use for mails (should use the system EOL).
	if (defined("PHP_EOL")) {$eol=PHP_EOL;} else {$eol="\r\n";}
	
	$message.=$eol.$eol.$eol . $email_footer;
	
	if ($disable_quoted_printable_enc==false){
	$message=rs_quoted_printable_encode($message);
	$subject=rs_quoted_printable_encode_subject($subject);
	}
	
	global $email_from;
	if ($from=="") {$from=$email_from;}
	if ($reply_to=="") {$reply_to=$email_from;}
	global $applicationname;
	if ($from_name==""){$from_name=$applicationname;}
	
	if (substr($reply_to,-1)==","){$reply_to=substr($reply_to,0,-1);}
	
	$reply_tos=explode(",",$reply_to);
	
	# Add headers
	$headers="";
	#$headers .= "X-Sender:  x-sender" . $eol;
   	$headers .= "From: ";
   	#allow multiple emails, and fix for long format emails
   	for ($n=0;$n<count($reply_tos);$n++){
		if ($n!=0){$headers.=",";}
		if (strstr($reply_tos[$n],"<")){ 
			$rtparts=explode("<",$reply_tos[$n]);
			$headers.=$rtparts[0]." <".$rtparts[1];
		}
		else {
			mb_internal_encoding("UTF-8");
			$headers.=mb_encode_mimeheader($from_name, "UTF-8") . " <".$reply_tos[$n].">";
		}
 	}
 	$headers.=$eol;
 	$headers .= "Reply-To: $reply_to" . $eol;
 	
	if ($cc!=""){
		global $userfullname;
		#allow multiple emails, and fix for long format emails
		$ccs=explode(",",$cc);
		$headers .= "Cc: ";
		for ($n=0;$n<count($ccs);$n++){
			if ($n!=0){$headers.=",";}
			if (strstr($ccs[$n],"<")){ 
				$ccparts=explode("<",$ccs[$n]);
				$headers.=$ccparts[0]." <".$ccparts[1];
			}
			else {
				mb_internal_encoding("UTF-8");
				$headers.=mb_encode_mimeheader($userfullname, "UTF-8"). " <".$ccs[$n].">";
			}
		}
		$headers.=$eol;
	}
	
	if ($bcc!=""){
		global $userfullname;
		#add bcc 
		$bccs=explode(",",$bcc);
		$headers .= "Bcc: ";
		for ($n=0;$n<count($bccs);$n++){
			if ($n!=0){$headers.=",";}
			if (strstr($bccs[$n],"<")){ 
				$bccparts=explode("<",$bccs[$n]);
				$headers.=$bccparts[0]." <".$bccparts[1];
			}
			else {
				mb_internal_encoding("UTF-8");
				$headers.=mb_encode_mimeheader($userfullname, "UTF-8"). " <".$bccs[$n].">";
			}
		}
		$headers.=$eol;
	}
	
	$headers .= "Date: " . date("r") .  $eol;
   	$headers .= "Message-ID: <" . date("YmdHis") . $from . ">" . $eol;
   	#$headers .= "Return-Path: returnpath" . $eol;
   	//$headers .= "Delivered-to: $email" . $eol;
   	$headers .= "MIME-Version: 1.0" . $eol;
   	$headers .= "X-Mailer: PHP Mail Function" . $eol;
   	if (!is_html($message))
   		{
		$headers .= "Content-Type: text/plain; charset=\"UTF-8\"" . $eol;
		}
	else
		{
		$headers .= "Content-Type: text/html; charset=\"UTF-8\"" . $eol;
		}
	$headers .= "Content-Transfer-Encoding: quoted-printable" . $eol;
	mail ($email,$subject,$message,$headers);
	}

if (!function_exists("send_mail_phpmailer")){
function send_mail_phpmailer($email,$subject,$message="",$from="",$reply_to="",$html_template="",$templatevars=null,$from_name="",$cc="",$bcc="")
	{
        # if ($use_phpmailer==true) this function is used instead.
	# Mail templates can include lang, server, site_text, and POST variables by default
	# ex ( [lang_mycollections], [server_REMOTE_ADDR], [text_footer] , [message]
	
	# additional values must be made available through $templatevars
	# For example, a complex url or image path that may be sent in an 
	# email should be added to the templatevars array and passed into send_mail.
	# available templatevars need to be well-documented, and sample templates
	# need to be available.

	# Include footer
	global $email_footer,$storagedir;
	$phpversion=phpversion();
	if ($phpversion>='5.3') {
	if (file_exists(dirname(__FILE__)."/../lib/phpmailer_v5.2.6/class.phpmailer.php")){
		include_once(dirname(__FILE__)."/../lib/phpmailer_v5.2.6/class.phpmailer.php");
		include_once(dirname(__FILE__)."/../lib/phpmailer_v5.2.6/extras/class.html2text.php");
		}
	} else {
	// less than 5.3
	if (file_exists(dirname(__FILE__)."/../lib/phpmailer/class.phpmailer.php")){
		include_once(dirname(__FILE__)."/../lib/phpmailer/class.phpmailer.php");
		include_once(dirname(__FILE__)."/../lib/phpmailer/class.html2text.php");
		}
	}
		
	global $email_from;
	if ($from=="") {$from=$email_from;}
	if ($reply_to=="") {$reply_to=$email_from;}
	global $applicationname;
	if ($from_name==""){$from_name=$applicationname;}
	
	#check for html template. If exists, attempt to include vars into message
	if ($html_template!="")
		{
		# Attempt to verify users by email, which allows us to get the email template by lang and usergroup
		$to_usergroup=sql_query("select lang,usergroup from user where email ='" . escape_check($email) . "'","");
        
		if (count($to_usergroup)!=0)
			{
			$to_usergroupref=$to_usergroup[0]['usergroup'];
			$to_usergrouplang=$to_usergroup[0]['lang'];
			}
		else 
			{
			$to_usergrouplang="";	
			}
			
		if ($to_usergrouplang==""){global $defaultlanguage; $to_usergrouplang=$defaultlanguage;}
			
		if (isset($to_usergroupref))
			{	
			$modified_to_usergroupref=hook("modifytousergroup","",$to_usergroupref);
			if (is_int($modified_to_usergroupref)){$to_usergroupref=$modified_to_usergroupref;}
			$results=sql_query("select language,name,text from site_text where page='all' and name='$html_template' and specific_to_group='$to_usergroupref'");
			}
		else 
			{	
			$results=sql_query("select language,name,text from site_text where page='all' and name='$html_template' and specific_to_group is null");
			}
			
		global $site_text;
		for ($n=0;$n<count($results);$n++) {$site_text[$results[$n]["language"] . "-" . $results[$n]["name"]]=$results[$n]["text"];} 
				
		$language=$to_usergrouplang;
                                
		if (array_key_exists($language . "-" . $html_template,$site_text)) 
			{
			$template=$site_text[$language . "-" .$html_template];
			} 
		else 
			{
			global $languages;

			# Can't find the language key? Look for it in other languages.
			reset($languages);
			foreach ($languages as $key=>$value)
				{
				if (array_key_exists($key . "-" . $html_template,$site_text)) {$template= $site_text[$key . "-" . $html_template];break;} 		
				}
                        // Fall back to language file if not in site text
                        global $lang;
                        if(isset($lang[$html_template])){$template=$lang[$html_template];}
			}		


		if (isset($template) && $template!="")
			{
			preg_match_all('/\[[^\]]*\]/',$template,$test);
			foreach($test[0] as $variable)
				{
			
				$variable=str_replace("[","",$variable);
				$variable=str_replace("]","",$variable);
			
				
				# get lang variables (ex. [lang_mycollections])
				if (substr($variable,0,5)=="lang_"){
					global $lang;
					$$variable=$lang[substr($variable,5)];
				}
				
				# get server variables (ex. [server_REMOTE_ADDR] for a user request)
				else if (substr($variable,0,7)=="server_"){
					$$variable=$_SERVER[substr($variable,7)];
				}
				
				# [embed_thumbnail] (requires url in templatevars['thumbnail'])
				else if (substr($variable,0,15)=="embed_thumbnail"){
					$thumbcid=uniqid('thumb');
					$$variable="<img style='border:1px solid #d1d1d1;' src='cid:$thumbcid' />";
				}
				
				# deprecated by improved [img_] tag below
				# embed images (find them in relation to storagedir so that templates are portable)...  (ex [img_storagedir_/../gfx/whitegry/titles/title.gif])
				else if (substr($variable,0,15)=="img_storagedir_"){
					$$variable="<img src='cid:".basename(substr($variable,15))."'/>";
					$images[]=dirname(__FILE__).substr($variable,15);
				}
				
				# embed images - ex [img_gfx/whitegry/titles/title.gif]
				else if (substr($variable,0,4)=="img_"){
					
					$image_path=substr($variable,4);
					if (substr($image_path,0,1)=="/"){ // absolute paths
						$images[]=$image_path;
					}
					else { // relative paths
						$image_path=str_replace("../","",$image_path);
						$images[]=dirname(__FILE__)."/../".$image_path;
					}
					$$variable="<img src='cid:".basename($image_path)."'/>";
					$images[]=$image_path;
				}
				
				# attach files (ex [attach_/var/www/resourcespace/gfx/whitegry/titles/title.gif])
				else if (substr($variable,0,7)=="attach_"){
					$$variable="";
					$attachments[]=substr($variable,7);
				}
				
				# get site text variables (ex. [text_footer], for example to 
				# manage html snippets that you want available in all emails.)
				else if (substr($variable,0,5)=="text_"){
					$$variable=text(substr($variable,5));
				}

				# try to get the variable from POST
				else{
					$$variable=getval($variable,"");
				}
				
				# avoid resetting templatevars that may have been passed here
				if (!isset($templatevars[$variable])){$templatevars[$variable]=$$variable;}
				}

			if (isset($templatevars))
				{
				foreach($templatevars as $key=>$value)
					{
					$template=str_replace("[" . $key . "]",nl2br($value),$template);
					}
				}
			$body=$template;	
			} 
		}		

	if (!isset($body)){$body=$message;}

	global $use_smtp,$smtp_secure,$smtp_host,$smtp_port,$smtp_auth,$smtp_username,$smtp_password;
	$mail = new PHPMailer();
	// use an external SMTP server? (e.g. Gmail)
	if ($use_smtp) {
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = $smtp_auth;  // authentication enabled/disabled
		$mail->SMTPSecure = $smtp_secure; // '', 'tls' or 'ssl'
		$mail->Host = $smtp_host; // hostname
		$mail->Port = $smtp_port; // port number
		$mail->Username = $smtp_username; // username
		$mail->Password = $smtp_password; // password
	}
	$reply_tos=explode(",",$reply_to);

	// only one from address is possible, so only use the first one:
	if (strstr($reply_tos[0],"<")){
		$rtparts=explode("<",$reply_tos[0]);
		$mail->From = str_replace(">","",$rtparts[1]);
		$mail->FromName = $rtparts[0];
	}
	else {
		$mail->From = $reply_tos[0];
		$mail->FromName = $from_name;
	}
	
	// if there are multiple addresses, that's what replyto handles.
	for ($n=0;$n<count($reply_tos);$n++){
		if (strstr($reply_tos[$n],"<")){
			$rtparts=explode("<",$reply_tos[$n]);
			$mail->AddReplyto(str_replace(">","",$rtparts[1]),$rtparts[0]);
		}
		else {
			$mail->AddReplyto($reply_tos[$n],$from_name);
		}
	}
	
	# modification to handle multiple comma delimited emails
	# such as for a multiple $email_notify
	$emails = $email;
	$emails = explode(',', $emails);
	$emails = array_map('trim', $emails);
	foreach ($emails as $email){
		if (strstr($email,"<")){
			$emparts=explode("<",$email);
			$mail->AddAddress(str_replace(">","",$emparts[1]),$emparts[0]);
		}
		else {
			$mail->AddAddress($email);
		}
	}
	
	if ($cc!=""){
		# modification for multiple is also necessary here, though a broken cc seems to be simply removed by phpmailer rather than breaking it.
		$ccs = $cc;
		$ccs = explode(',', $ccs);
		$ccs = array_map('trim', $ccs);
		global $userfullname;
		foreach ($ccs as $cc){
			if (strstr($cc,"<")){
				$ccparts=explode("<",$cc);
				$mail->AddCC(str_replace(">","",$ccparts[1]),$ccparts[0]);
			}
			else{
				$mail->AddCC($cc,$userfullname);
			}
		}
	}
	if ($bcc!=""){
		# modification for multiple is also necessary here, though a broken cc seems to be simply removed by phpmailer rather than breaking it.
		$bccs = $bcc;
		$bccs = explode(',', $bccs);
		$bccs = array_map('trim', $bccs);
		global $userfullname;
		foreach ($bccs as $bccemail){
			if (strstr($bccemail,"<")){
				$bccparts=explode("<",$bccemail);
				$mail->AddBCC(str_replace(">","",$bccparts[1]),$bccparts[0]);
			}
			else{
				$mail->AddBCC($bccemail,$userfullname);
			}
		}
	}
	
	
	$mail->CharSet = "utf-8"; 
	
	if (is_html($body)) {$mail->IsHTML(true);}  	
	else {$mail->IsHTML(false);}
	
	$mail->Subject = $subject;
	$mail->Body    = $body;
	
	if (isset($embed_thumbnail)&&isset($templatevars['thumbnail'])){
		$mail->AddEmbeddedImage($templatevars['thumbnail'],$thumbcid,$thumbcid,'base64','image/jpeg'); 
		}
	if (isset($images)){
		foreach ($images as $image){	
		$mail->AddEmbeddedImage($image,basename($image),basename($image),'base64','image/gif');}
	}	
	if (isset($attachments)){
		foreach ($attachments as $attachment){
		$mail->AddAttachment($attachment,basename($attachment));}
	}	
	if (is_html($body)){
		$h2t = new html2text($body); 
		$text = $h2t->get_text(); 
		$mail->AltBody = $text; 
		}	 
	if(!$mail->Send())
		{
		echo "Message could not be sent. <p>";
		echo "Mailer Error: " . $mail->ErrorInfo;
		exit;
		}
	hook("aftersendmailphpmailer","",$email);	
}
}

function rs_quoted_printable_encode($string, $linelen = 0, $linebreak="=\r\n", $breaklen = 0, $encodecrlf = false) {
        // Quoted printable encoding is rather simple.
        // Each character in the string $string should be encoded if:
        //  Character code is <0x20 (space)
        //  Character is = (as it has a special meaning: 0x3d)
        //  Character is over ASCII range (>=0x80)
        $len = strlen($string);
        $result = '';
        for($i=0;$i<$len;$i++) {
                if (($linelen >= 76) && (false)) { // break lines over 76 characters, and put special QP linebreak
                        $linelen = $breaklen;
                        $result.= $linebreak;
                }
                $c = ord($string[$i]);
                if (($c==0x3d) || ($c>=0x80) || ($c<0x20)) { // in this case, we encode...
                        if ((($c==0x0A) || ($c==0x0D)) && (!$encodecrlf)) { // but not for linebreaks
                                $result.=chr($c);
                                $linelen = 0;
                                continue;
                        }
                        $result.='='.str_pad(strtoupper(dechex($c)), 2, '0');
                        $linelen += 3;
                        continue;
                }
                $result.=chr($c); // normal characters aren't encoded
                $linelen++;
        }
        return $result;
}


function rs_quoted_printable_encode_subject($string, $encoding='UTF-8')
	{
	// use this function with headers, not with the email body as it misses word wrapping
       $len = strlen($string);
       $result = '';
       $enc = false;
       for($i=0;$i<$len;++$i) {
        $c = $string[$i];
        if (ctype_alpha($c))
            $result.=$c;
        else if ($c==' ') {
            $result.='_';
            $enc = true;
        } else {
            $result.=sprintf("=%02X", ord($c));
            $enc = true;
        }
       }
       //L: so spam agents won't mark your email with QP_EXCESS
       if (!$enc) return $string;
       return '=?'.$encoding.'?q?'.$result.'?=';
	}

if (!function_exists("highlightkeywords")){
function highlightkeywords($text,$search,$partial_index=false,$field_name="",$keywords_index=1)
	{
	# do not highlight if the field is not indexed, so it is clearer where results came from.	
	if ($keywords_index!=1){return $text;}

	# Highlight searched keywords in $text
	# Optional - depends on $highlightkeywords being set in config.php.
	global $highlightkeywords;
	# Situations where we do not need to do this.
	if (!isset($highlightkeywords) || ($highlightkeywords==false) || ($search=="") || ($text=="")) {return $text;}


        # Generate the cache of search keywords (no longer global so it can test against particular fields.
        # a search is a small array so I don't think there is much to lose by processing it.
        $hlkeycache=array();
        $wildcards_found=false;
        $s=split_keywords($search);
        for ($n=0;$n<count($s);$n++)
                {
                if (strpos($s[$n],":")!==false) {
                        $c=explode(":",$s[$n]);
                        # only add field specific keywords
                        if($field_name!="" && $c[0]==$field_name){
                                $hlkeycache[]=$c[1];			
                        }	
                }
                # else add general keywords
                else {
                        $keyword=$s[$n];
            
                        global $stemming;
                        if ($stemming && function_exists("GetStem")) // Stemming enabled. Highlight any words matching the stem.
                            {
                            $keyword=GetStem($keyword);
                            }
                        
                        if (strpos($keyword,"*")!==false) {$wildcards_found=true;$keyword=str_replace("*","",$keyword);}
                        $hlkeycache[]=$keyword;
                }	
             }
        
	# Parse and replace.
	return str_highlight ($text,$hlkeycache,STR_HIGHLIGHT_SIMPLE);
	}
}
# These lines go with str_highlight (next).
define('STR_HIGHLIGHT_SIMPLE', 1);
define('STR_HIGHLIGHT_WHOLEWD', 2);
define('STR_HIGHLIGHT_CASESENS', 4);
define('STR_HIGHLIGHT_STRIPLINKS', 8);

function str_highlight($text, $needle, $options = null, $highlight = null)
	{
	# Thanks to Aidan Lister <aidan@php.net>
	# Sourced from http://aidanlister.com/repos/v/function.str_highlight.php on 2007-10-09
	# License on the website reads: "All code on this website resides in the Public Domain, you are free to use and modify it however you wish."
	# http://aidanlister.com/repos/license/

	$text=str_replace("_","♠",$text);// underscores are considered part of words, so temporarily replace them for better \b search.
    $text=str_replace("#zwspace;","♣",$text);
    
    // Default highlighting
    if ($highlight === null) {
        $highlight = '||<||\1||>||';
    }
 
    // Select pattern to use
    if ($options & STR_HIGHLIGHT_SIMPLE) {
        $pattern = '#(%s)#';
        $sl_pattern = '#(%s)#';
    } else {
        $pattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
        $sl_pattern = '#<a\s(?:.*?)>(%s)</a>#';
    }
 
    // Case sensitivity
    if (!($options & STR_HIGHLIGHT_CASESENS)) {
        $pattern .= 'i';
        $sl_pattern .= 'i';
    }
 
    $needle = (array) $needle;

    usort($needle, "sorthighlights");

    foreach ($needle as $needle_s) {
    	if (strlen($needle_s) > 0) {
	        $needle_s = preg_quote($needle_s);
	        $needle_s = str_replace("#","\\#",$needle_s);
	 
	        // Escape needle with optional whole word check
	        if ($options & STR_HIGHLIGHT_WHOLEWD) {
	            $needle_s = '\b' . $needle_s . '\b';
	        }
	 
	        // Strip links
	        if ($options & STR_HIGHLIGHT_STRIPLINKS) {
	            $sl_regex = sprintf($sl_pattern, $needle_s);
	            $text = preg_replace($sl_regex, '\1', $text);
	        }
	 
	        $regex = sprintf($pattern, $needle_s);
	        $text = preg_replace($regex, $highlight, $text);
	    }
    }
	$text=str_replace("♠","_",$text);
	$text=str_replace("♣","#zwspace;",$text);

	# Fix - do the final replace at the end - fixes a glitch whereby the highlight HTML itself gets highlighted if it matches search terms, and you get nested HTML.
	$text=str_replace("||<||",'<span class="highlight">',$text);
	$text=str_replace("||>||",'</span>',$text);

    return $text;
	}

function sorthighlights($a, $b)
    {
    # fixes an odd problem for str_highlight related to the order of keywords
    if (strlen($a) < strlen($b)) {
        return 0;
        }
    return ($a < $b) ? -1 : 1;
    }

function pager($break=true)
	{
	global $curpage,$url,$totalpages,$offset,$per_page,$lang,$jumpcount,$pager_dropdown,$pagename;

    $modal  = ('true' == getval('modal', ''));

	$jumpcount++;
	if(!hook("replace_pager")){
		if ($totalpages!=0 && $totalpages!=1){?>     
			<span class="TopInpageNavRight"><?php if ($break) { ?>&nbsp;<br /><?php } hook("custompagerstyle"); if ($curpage>1) { ?><a class="prevPageLink" href="<?php echo $url?>&amp;go=prev&amp;offset=<?php echo urlencode($offset-$per_page) ?>" <?php if(!hook("replacepageronclick_prev")){?>onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this, true);" <?php } ?>><?php } ?>&lt;&nbsp;<?php echo $lang["previous"]?><?php if ($curpage>1) { ?></a><?php } ?>&nbsp;|

			<?php if ($pager_dropdown){
				$id=rand();?>
				<select id="pager<?php echo $id;?>" class="ListDropdown" style="width:50px;" <?php if(!hook("replacepageronchange_drop","",array($id))){?>onChange="var jumpto=document.getElementById('pager<?php echo $id?>').value;if ((jumpto>0) && (jumpto<=<?php echo $totalpages?>)) {return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load('<?php echo $url?>&amp;go=page&amp;offset=' + ((jumpto-1) * <?php echo urlencode($per_page) ?>), true);}" <?php } ?>>
				<?php for ($n=1;$n<$totalpages+1;$n++){?>
					<option value='<?php echo $n?>' <?php if ($n==$curpage){?>selected<?php } ?>><?php echo $n?></option>
				<?php } ?>
				</select>
			<?php } else { ?>
				<a href="#" title="<?php echo $lang["jumptopage"]?>" onClick="p=document.getElementById('jumppanel<?php echo $jumpcount?>');if (p.style.display!='block') {p.style.display='block';document.getElementById('jumpto<?php echo $jumpcount?>').focus();} else {p.style.display='none';}; return false;"><?php echo $lang["page"]?>&nbsp;<?php echo htmlspecialchars($curpage) ?>&nbsp;<?php echo $lang["of"]?>&nbsp;<?php echo $totalpages?></a>
			<?php } ?>

			|&nbsp;<?php if ($curpage<$totalpages) { ?><a class="nextPageLink" href="<?php echo $url?>&amp;go=next&amp;offset=<?php echo urlencode($offset+$per_page) ?>" <?php if(!hook("replacepageronclick_next")){?>onClick="return <?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load(this, true);" <?php } ?>><?php } ?><?php echo $lang["next"]?>&nbsp;&gt;<?php if ($curpage<$totalpages) { ?></a><?php } hook("custompagerstyleend"); ?>
			</span>
			<?php if (!$pager_dropdown){?>
				<div id="jumppanel<?php echo $jumpcount?>" style="display:none;margin-top:5px;"><?php echo $lang["jumptopage"]?>: <input type="text" size="3" id="jumpto<?php echo $jumpcount?>" onkeydown="var evt = event || window.event;if (evt.keyCode == 13) {var jumpto=document.getElementById('jumpto<?php echo $jumpcount?>').value;if (jumpto<1){jumpto=1;};if (jumpto><?php echo $totalpages?>){jumpto=<?php echo $totalpages?>;};<?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load('<?php echo $url?>&amp;go=page&amp;offset=' + ((jumpto-1) * <?php echo urlencode($per_page) ?>), true);}">
			&nbsp;<input type="submit" name="jump" value="<?php echo $lang["jump"]?>" onClick="var jumpto=document.getElementById('jumpto<?php echo $jumpcount?>').value;if (jumpto<1){jumpto=1;};if (jumpto><?php echo $totalpages?>){jumpto=<?php echo $totalpages?>;};<?php echo $modal ? 'Modal' : 'CentralSpace'; ?>Load('<?php echo $url?>&amp;offset=' + ((jumpto-1) * <?php echo urlencode($per_page) ?>), true);"></div>
			<?php } ?>
		<?php } else { ?><span class="HorizontalWhiteNav">&nbsp;</span><div <?php if ($pagename=="search"){?>style="display:block;"<?php } else { ?>style="display:inline;"<?php }?>>&nbsp;</div><?php } ?>
		<?php
		}
	}
	
function get_all_image_sizes($internal=false,$restricted=false)
{
    # Returns all image sizes available.
    # Standard image sizes are translated using $lang.  Custom image sizes are i18n translated.

    # Executes query.
    $r = sql_query("select * from preview_size " . (($internal)?"":"where internal!=1") . (($restricted)?" and allow_restricted=1":"") . " order by width asc");

    # Translates image sizes in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["name"] = lang_or_i18n_get_translated($r[$n]["name"], "imagesize-");
        $return[] = $r[$n];
    }
    return $return;

}
	
function image_size_restricted_access($id)
	{
	# Returns true if the indicated size is allowed for a restricted user.
	return sql_value("select allow_restricted value from preview_size where id='$id'",false);
	}
	
function get_user_log($user, $fetchrows=-1)
	{
    # Returns a user action log for $user.
    # Standard field titles are translated using $lang.  Custom field titles are i18n translated.
	global $view_title_field;
    # Executes query.
    $r = sql_query("select r.ref resourceid,r.field".$view_title_field." resourcetitle,l.date,l.type,f.title,l.purchase_size,l.purchase_price, l.notes,l.diff from resource_log l left outer join resource r on l.resource=r.ref left outer join resource_type_field f on f.ref=l.resource_type_field where l.user='$user' order by l.date desc",false,$fetchrows);

    # Translates field titles in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
		if (is_array($r[$n])) {$r[$n]["title"] = lang_or_i18n_get_translated($r[$n]["title"], "fieldtitle-");}
        $return[] = $r[$n];
    }
    return $return;
	}
	
function resolve_userlist_groups($userlist)
	{
	# Given a comma separated user list (from the user select include file) turn all Group: entries into fully resolved list of usernames.
	# Note that this function can't decode default groupnames containing special characters.

	global $lang;
	$ulist=explode(",",$userlist);
	$newlist="";
	for ($n=0;$n<count($ulist);$n++)
		{
		$u=trim($ulist[$n]);
		if (strpos($u,$lang["group"] . ": ")===0)
			{
			# Group entry, resolve

			# Find the translated groupname.
			$translated_groupname = trim(substr($u,strlen($lang["group"] . ": ")));
			# Search for corresponding $lang indices.
			$default_group = false;
			$langindices = array_keys($lang, $translated_groupname);
			if (count($langindices)>0);
				{
				foreach ($langindices as $langindex)
					{
					# Check if it is a default group
					if (strstr($langindex, "usergroup-")!==false)
						{
						# Decode the groupname by using the code from lang_or_i18n_get_translated the other way around (it could be possible that someone have renamed the English groupnames in the language file).
						$untranslated_groupname = trim(substr($langindex,strlen("usergroup-")));
						$untranslated_groupname = str_replace(array("_", "and"), array(" "), $untranslated_groupname);
						$groupref = sql_value("select ref as value from usergroup where lower(name)='$untranslated_groupname'",false);
						if ($groupref!==false)
							{
							$default_group = true;
							break;
							}
						}
					}
				}
			if ($default_group==false)
				{
				# Custom group
				# Decode the groupname
				$untranslated_groups = sql_query("select ref, name from usergroup");
				foreach ($untranslated_groups as $group)
					{
					if (i18n_get_translated($group['name'])==$translated_groupname)
						{
						$groupref = $group['ref'];
						break;
						}
					}
				}

			# Find and add the users.
			$users = sql_array("select username value from user where usergroup='$groupref'");
			if ($newlist!="") {$newlist.=",";}
			$newlist.=join(",",$users);
			}
		else
			{
			# Username, just add as-is
			if ($newlist!="") {$newlist.=",";}
			$newlist.=$u;
			}
		}
	return $newlist;
	}
function resolve_userlist_groups_smart($userlist,$return_usernames=false)
	{
	# Given a comma separated user list (from the user select include file) turn all Group: entries into fully resolved list of usernames.
	# Note that this function can't decode default groupnames containing special characters.

	global $lang;
	$ulist=explode(",",$userlist);
	$newlist="";
	for ($n=0;$n<count($ulist);$n++)
		{
		$u=trim($ulist[$n]);
		if (strpos($u,$lang["groupsmart"] . ": ")===0)
			{
			# Group entry, resolve

			# Find the translated groupname.
			$translated_groupname = trim(substr($u,strlen($lang["groupsmart"] . ": ")));
			# Search for corresponding $lang indices.
			$default_group = false;
			$langindices = array_keys($lang, $translated_groupname);
			if (count($langindices)>0);
				{ 
				foreach ($langindices as $langindex)
					{
					# Check if it is a default group
					if (strstr($langindex, "usergroup-")!==false)
						{
						# Decode the groupname by using the code from lang_or_i18n_get_translated the other way around (it could be possible that someone have renamed the English groupnames in the language file).
						$untranslated_groupname = trim(substr($langindex,strlen("usergroup-")));
						$untranslated_groupname = str_replace(array("_", "and"), array(" "), $untranslated_groupname);
						$groupref = sql_value("select ref as value from usergroup where lower(name)='$untranslated_groupname'",false);
						if ($groupref!==false)
							{
							$default_group = true;
							break;
							}
						}
					}
				}
			if ($default_group==false)
				{ 
				# Custom group
				# Decode the groupname
				$untranslated_groups = sql_query("select ref, name from usergroup");
				
				foreach ($untranslated_groups as $group)
					{
					if (i18n_get_translated($group['name'])==$translated_groupname)
						{ 
						$groupref = $group['ref'];
						break;
						}
					}
				}
			if($return_usernames)
				{
				$users = sql_array("select username value from user where usergroup='$groupref'");
				if ($newlist!="") {$newlist.=",";}
				$newlist.=join(",",$users);
				}
			else
				{
				# Find and add the users.
				if ($newlist!="") {$newlist.=",";}
				$newlist.=$groupref;
				}
			}
		}
	return $newlist;
	}

function remove_groups_smart_from_userlist($ulist)
	{
	global $lang;
	
	$ulist=explode(",",$ulist);
	$new_ulist='';
	foreach($ulist as $option)
		{
		if(strpos($option,$lang["groupsmart"] . ": ")===false)
			{
			if($new_ulist!="")
				{
				$new_ulist.=",";
				}
			$new_ulist.=$option;
			}
		}
	return $new_ulist;
	}

function get_suggested_keywords($search,$ref="")
	{
	# For the given partial word, suggest complete existing keywords.
	global $autocomplete_search_items,$autocomplete_search_min_hitcount;
	if ($ref==""){
		return sql_array("select distinct keyword value from keyword where keyword like '" . escape_check($search) . "%' and hit_count >= '$autocomplete_search_min_hitcount' order by hit_count desc limit $autocomplete_search_items");
		}
	else 
		{
		return sql_array("select distinct k.keyword value,rk.resource_type_field from keyword k,resource_keyword rk where k.ref=rk.keyword and k.keyword like '" . escape_check($search) . "%' and rk.resource_type_field='".$ref."' and k.hit_count >= '$autocomplete_search_min_hitcount' order by k.hit_count desc limit $autocomplete_search_items");
		}
	}
	
function check_password($password)
	{
	# Checks that a password conforms to the configured paramaters.
	# Returns true if it does, or a descriptive string if it doesn't.
	global $lang, $password_min_length, $password_min_alpha, $password_min_uppercase, $password_min_numeric, $password_min_special;

	if (strlen($password)<$password_min_length) {return str_replace("?",$password_min_length,$lang["password_not_min_length"]);}

	$uppercase="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$alpha=$uppercase . "abcdefghijklmnopqrstuvwxyz";
	$numeric="0123456789";
	
	$a=0;$u=0;$n=0;$s=0;
	for ($m=0;$m<strlen($password);$m++)
		{
		$l=substr($password,$m,1);
		if (strpos($uppercase,$l)!==false) {$u++;}

		if (strpos($alpha,$l)!==false) {$a++;}
		elseif (strpos($numeric,$l)!==false) {$n++;}
		else {$s++;} # Not alpha/numeric, must be a special char.
		}
	
	if ($a<$password_min_alpha) {return str_replace("?",$password_min_alpha,$lang["password_not_min_alpha"]);}
	if ($u<$password_min_uppercase) {return str_replace("?",$password_min_uppercase,$lang["password_not_min_uppercase"]);}
	if ($n<$password_min_numeric) {return str_replace("?",$password_min_numeric,$lang["password_not_min_numeric"]);}
	if ($s<$password_min_special) {return str_replace("?",$password_min_special,$lang["password_not_min_special"]);}
	
	
	return true;
	}

function get_related_keywords($keyref)
	{
	# For a given keyword reference returns the related keywords
	# Also reverses the process, returning keywords for matching related words
	# and for matching related words, also returns other words related to the same keyword.
	global $keyword_relationships_one_way;
	global $related_keywords_cache;
	if (isset($related_keywords_cache[$keyref])){
		return $related_keywords_cache[$keyref];
	} else {
		if ($keyword_relationships_one_way){
			$related_keywords_cache[$keyref]=sql_array("select related value from keyword_related where keyword='$keyref'");
			return $related_keywords_cache[$keyref];
			}
		else {
			$related_keywords_cache[$keyref]=sql_array("select keyword value from keyword_related where related='$keyref' union select related value from keyword_related where (keyword='$keyref' or keyword in (select keyword value from keyword_related where related='$keyref')) and related<>'$keyref'");
			return $related_keywords_cache[$keyref];
			}
		}
	}
	
	
function get_grouped_related_keywords($find="",$specific="")
	{
	# Returns each keyword and the related keywords grouped, along with the resolved keywords strings.
	$sql="";
	if ($find!="") {$sql="where k1.keyword='" . escape_check($find) . "' or k2.keyword='" . escape_check($find) . "'";}
	if ($specific!="") {$sql="where k1.keyword='" . escape_check($specific) . "'";}
	
	return sql_query("
		select k1.keyword,group_concat(k2.keyword order by k2.keyword separator ', ') related from keyword_related kr
			join keyword k1 on kr.keyword=k1.ref
			join keyword k2 on kr.related=k2.ref
		$sql
		group by k1.keyword order by k1.keyword
		");
	}

function save_related_keywords($keyword,$related)
	{
	$keyref=resolve_keyword($keyword,true);
	$s=trim_array(explode(",",$related));

	# Blank existing relationships.
	sql_query("delete from keyword_related where keyword='$keyref'");
	if (trim($related)!="")
		{
		for ($n=0;$n<count($s);$n++)
			{
			sql_query("insert into keyword_related (keyword,related) values ('$keyref','" . resolve_keyword($s[$n],true) . "')");
			}
		}
	return true;
	}

function send_statistics()
	{
	# If configured, send two metrics to Montala.
	$last_sent=sql_value("select value from sysvars where name='last_sent_stats'","");
	
	# No need to send stats if already sent in last week.
	if ($last_sent!="" && time()-strtotime($last_sent)<(60*60*24*7)) {return false;}
	
	# Gather stats
	$total_users=sql_value("select count(*) value from user",0);
	$total_resources=sql_value("select count(*) value from resource",0);
	
	# Send stats
	@file("http://www.montala.net/rs_stats.php?users=" . $total_users . "&resources=" . $total_resources);
	
	# Update last sent date/time.
	sql_query("delete from sysvars where name='last_sent_stats'");
	sql_query("insert into sysvars(name,value) values ('last_sent_stats',now())");
	}

function resolve_users($users)
	{
	# For a given comma-separated list of user refs (e.g. returned from a group_concat()), return a string of matching usernames.
	if (trim($users)=="") {return "";}
	$resolved=sql_array("select concat(fullname,' (',username,')') value from user where ref in ($users)");
	return join(", ",$resolved);
	}

function get_simple_search_fields()
{
    # Returns a list of fields suitable for the simple search box.
    # Standard field titles are translated using $lang.  Custom field titles are i18n translated.

    $sql = "";

    # Include the country field even if not selected?
    # This is to provide compatibility for older systems on which the simple search box was not configurable
    # and had a simpler 'country search' option.
    global $country_search;
    if (isset($country_search) && $country_search) {$sql=" or ref=3";}

    # Executes query.
    $fields = sql_query("select *, ref, name, title, type, order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, display_as_dropdown, external_user_access, autocomplete_macro, hide_when_uploading, hide_when_restricted, value_filter, exiftool_filter, omit_when_copying, tooltip_text, display_condition from resource_type_field where (simple_search=1 $sql) and keywords_index=1 order by resource_type,order_by");

    # Applies field permissions and translates field titles in the newly created array.
    $return = array();
    for ($n = 0;$n<count($fields);$n++) {
        if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
        && !checkperm("f-" . $fields[$n]["ref"]) && !checkperm("T" . $fields[$n]["resource_type"] )) {
            $fields[$n]["title"] = lang_or_i18n_get_translated($fields[$n]["title"], "fieldtitle-");            
            $return[] = $fields[$n];
        }
    }
    return $return;
}

function check_display_condition($n, $field)
{
  global $fields, $scriptconditions, $required_fields_exempt, $blank_edit_template, $ref, $use;

  $displaycondition=true;
  $s=explode(";",$field["display_condition"]);
  $condref=0;
    foreach ($s as $condition) # Check each condition
    {
       $displayconditioncheck=false;
       $s=explode("=",$condition);
        for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
        {
            node_field_options_override($fields[$cf]);
            if ($s[0]==$fields[$cf]["name"]) # this field needs to be checked
            {
                $scriptconditions[$condref]["field"] = $fields[$cf]["ref"];  # add new jQuery code to check value
                $scriptconditions[$condref]['type'] = $fields[$cf]['type'];
                $scriptconditions[$condref]['options'] = (in_array($fields[$cf]['type'],array(2, 3, 7, 9, 12))?implode(",",$fields[$cf]['node_options']):$fields[$cf]['options']);

                $checkvalues=$s[1];
                $validvalues=explode("|",mb_strtoupper($checkvalues));
                $scriptconditions[$condref]["valid"]= "\"";
                $scriptconditions[$condref]["valid"].= implode("\",\"",$validvalues);
                $scriptconditions[$condref]["valid"].= "\"";
                $v=trim_array(explode(",",mb_strtoupper($fields[$cf]["value"])));

                // If blank edit template is used, on upload form the dependent fields should be hidden
                if($blank_edit_template && $ref < 0 && $use === '-1') {
                   $v = array();
                }
                
                foreach ($validvalues as $validvalue)
                {
                    if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value
                 }
                 if (!$displayconditioncheck) {$displaycondition=false;$required_fields_exempt[]=$field["ref"];}
                #add jQuery code to update on changes
                    if ($fields[$cf]["type"]==2) # add onchange event to each checkbox field
                    {
                        # construct the value from the ticked boxes
                        # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                     $options=trim_array($fields[$cf]["node_options"]);
                     ?><script type="text/javascript">
                     jQuery(document).ready(function() {<?php
                       for ($m=0;$m<count($options);$m++)
                       {
                         $checkname=$fields[$cf]["ref"] . "_" . md5($options[$m]);
                         echo "
                         jQuery('.Question input[name=\"" . $checkname . "\"]').change(function (){
                           checkDisplayCondition" . $field["ref"] . "();
                        });";
                  }
                  ?>
               });
                     </script><?php
                  }
                        # add onChange event to each radio button
                  else if($fields[$cf]['type'] == 12) {

                    $options = $fields[$cf]['node_options'];?>
					
                    <script type="text/javascript">
                    jQuery(document).ready(function() {

                       <?php
                       foreach ($options as $option) {
                         $element_id = 'field_' . $fields[$cf]['ref'] . '_' . sha1($option);
                         $jquery = sprintf('
                          jQuery("#%s").change(function() {
                            checkDisplayCondition%s();
                         });
                         ',
                         $element_id,
                         $field["ref"]
                         );
                         echo $jquery;
                      } ?>

                   });
                    </script>

                    <?php
                 }
                 else
                 {
                  ?>
                  <script type="text/javascript">
                  jQuery(document).ready(function() {
                    jQuery('.Question #field_<?php echo $fields[$cf]["ref"];?>').change(function (){

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
        function checkDisplayCondition<?php echo $field["ref"];?>()
			{
			field<?php echo $field["ref"]?>status=jQuery('#question_<?php echo $n ?>').css('display');
			newfield<?php echo $field["ref"]?>status='none';
			newfield<?php echo $field["ref"]?>provisional=true;
			
			<?php
			foreach ($scriptconditions as $scriptcondition)
				{
				?>
				newfield<?php echo $field["ref"]?>provisionaltest=false;
				if (jQuery('.Question #field_<?php echo $scriptcondition["field"]?>').length!=0)
					{
					<?php
					if($scriptcondition['type'] == 12) {
						?>
						
						var options_string = '<?php echo htmlspecialchars($scriptcondition["options"]); ?>';
						var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
						var checked = null;
						
						for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++)
							{
							if(jQuery('.Question #field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) 
								{
								checked = jQuery('.Question #field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val();
								checked = checked.toUpperCase();
								}
							}
						
						fieldvalues<?php echo $scriptcondition["field"]?>=checked.split(',');
						fieldokvalues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

						if(checked !== null && jQuery.inArray(checked, fieldokvalues<?php echo $scriptcondition["field"]; ?>) > -1) 
							{
							newfield<?php echo $field["ref"]; ?>provisionaltest = true;
							}
						<?php
						}
					else
						{
						?>
						fieldcheck<?php echo $scriptcondition["field"]?>=jQuery('.Question #field_<?php echo $scriptcondition["field"]?>').val().toUpperCase();
						fieldvalues<?php echo $scriptcondition["field"]?>=fieldcheck<?php echo $scriptcondition["field"]?>.split(',');
						//alert(fieldvalues<?php echo $scriptcondition["field"]?>);
						<?php
						}
					?>
					}
				else
					{
					<?php

					# Handle Radio Buttons type: not sure if this is needed here anymore
					if($scriptcondition['type'] == 12) {

						$scriptcondition["options"] = explode(',', $scriptcondition["options"]);

						foreach ($scriptcondition["options"] as $key => $value) 
							{
							$scriptcondition["options"][$key] = sha1($value);
							}

						$scriptcondition["options"] = implode(',', $scriptcondition["options"]);
						?>
						
						var options_string = '<?php echo $scriptcondition["options"]; ?>';
						var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
						var checked = null;
						
						for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++)
							{
							if(jQuery('.Question #field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) 
								{
								checked = jQuery('.Question #field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val();
								checked = checked.toUpperCase();
								}
							}

						fieldokvalues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

						if(checked !== null && jQuery.inArray(checked, fieldokvalues<?php echo $scriptcondition["field"]; ?>) > -1) 
							{
							newfield<?php echo $field["ref"]; ?>provisionaltest = true;
							}
						<?php
						}
					?>
					fieldvalues<?php echo $scriptcondition["field"]?>=new Array();
					checkedvals<?php echo $scriptcondition["field"]?>=jQuery('.Question input[name^=<?php echo $scriptcondition["field"]?>_]');
      
					jQuery.each(checkedvals<?php echo $scriptcondition["field"]?>,function()
						{
						if (jQuery(this).is(':checked'))
							{
							checktext<?php echo $scriptcondition["field"]?>=jQuery(this).parent().next().text().toUpperCase();
							checktext<?php echo $scriptcondition["field"]?> = jQuery.trim(checktext<?php echo $scriptcondition["field"]?>);
							fieldvalues<?php echo $scriptcondition["field"]?>.push(checktext<?php echo $scriptcondition["field"]?>);
							//alert(fieldvalues<?php echo $scriptcondition["field"]?>);
							}
						});
					}
		
				fieldokvalues<?php echo $scriptcondition["field"]?>=new Array();
				fieldokvalues<?php echo $scriptcondition["field"]?>=[<?php echo $scriptcondition["valid"]?>];
		
				jQuery.each(fieldvalues<?php echo $scriptcondition["field"]?>,function(f,v)
					{
					//alert("checking value " + fieldvalues<?php echo $scriptcondition["field"]?> + " against " + fieldokvalues<?php echo $scriptcondition["field"]?>);
					//alert(jQuery.inArray(fieldvalues<?php echo $scriptcondition["field"]?>,fieldokvalues<?php echo $scriptcondition["field"]?>));
					if ((jQuery.inArray(v,fieldokvalues<?php echo $scriptcondition["field"]?>))>-1 || (fieldvalues<?php echo $scriptcondition["field"]?> ==fieldokvalues<?php echo  $scriptcondition["field"]?>))
						{
						newfield<?php echo $field["ref"]?>provisionaltest=true;
						}
					});

				if (newfield<?php echo $field["ref"]?>provisionaltest==false)
					{
					newfield<?php echo $field["ref"]?>provisional=false;
					}
				<?php
				}
			?>
			exemptfieldsval=jQuery('#exemptfields').val();
			exemptfieldsarr=exemptfieldsval.split(',');
			
			if (newfield<?php echo $field["ref"]?>provisional==true)
				{
				if (jQuery.inArray(<?php echo $field["ref"]?>,exemptfieldsarr))
					{
					exemptfieldsarr.splice(jQuery.inArray(<?php echo $field["ref"]?>, exemptfieldsarr), 1 );
					}
				newfield<?php echo $field["ref"]?>status='block';
				}
			else
				{
				if ((jQuery.inArray(<?php echo $field["ref"]?>,exemptfieldsarr))==-1)
					{
					exemptfieldsarr.push(<?php echo $field["ref"]?>);
					}
				}
			jQuery('#exemptfields').val(exemptfieldsarr.join(","));

			if (newfield<?php echo $field["ref"]?>status!=field<?php echo $field["ref"]?>status)
				{
				jQuery('#question_<?php echo $n ?>').slideToggle();
				if (jQuery('#question_<?php echo $n ?>').css('display')=='block')
					{
					jQuery('#question_<?php echo $n ?>').css('border-top','');
					}
				else
					{
					jQuery('#question_<?php echo $n ?>').css('border-top','none');
					}
				}
			}
		</script>
		<?php
return $displaycondition;
}

function check_access_key($resource,$key)
	{
	# Verify a supplied external access key
	
	# Option to plugin in some extra functionality to check keys
	if (hook("check_access_key","",array($resource,$key))===true) {return true;}
	global $external_share_view_as_internal, $is_authenticated;
    	if($external_share_view_as_internal && (isset($_COOKIE["user"]) && !(isset($is_authenticated) && $is_authenticated))){return false;} // We want to authenticate the user if not already authenticated so we can show the page as internal
	
	$keys=sql_query("select user,usergroup,expires from external_access_keys where resource='$resource' and access_key='$key' and (expires is null or expires>now())");

	if (count($keys)==0)
		{
		return false;
		}
	else
		{
		# "Emulate" the user that e-mailed the resource by setting the same group and permissions
		
		$user=$keys[0]["user"];
		$expires=$keys[0]["expires"];
                
		# Has this expired?
		if ($expires!="" && strtotime($expires)<time())
			{
			global $lang;
			?>
			<script type="text/javascript">
			alert("<?php echo $lang["externalshareexpired"] ?>");
			history.go(-1);
			</script>
			<?php
			exit();
			}
		
		global $usergroup,$userpermissions,$userrequestmode,$userfixedtheme,$usersearchfilter,$external_share_groups_config_options; 
                $groupjoin="u.usergroup=g.ref";
                if ($keys[0]["usergroup"]!="")
                    {
                    # Select the user group from the access key instead.
                    $groupjoin="g.ref='" . escape_check($keys[0]["usergroup"]) . "'";
                    }
		$userinfo=sql_query("select g.ref usergroup,g.permissions,g.fixed_theme,g.search_filter,g.config_options,u.search_filter_override from user u join usergroup g on $groupjoin where u.ref='$user'");
		if (count($userinfo)>0)
			{
                        $usergroup=$userinfo[0]["usergroup"]; # Older mode, where no user group was specified, find the user group out from the table.
			$userpermissions=explode(",",$userinfo[0]["permissions"]);
			$usersearchfilter=$userinfo[0]["search_filter"];

            $usersearchfilter=isset($userinfo[0]["search_filter_override"]) && $userinfo[0]["search_filter_override"]!='' ? $userinfo[0]["search_filter_override"] : $userinfo[0]["search_filter"];


			if (trim($userinfo[0]["fixed_theme"])!="") {$userfixedtheme=$userinfo[0]["fixed_theme"];} # Apply fixed theme also

			if (hook("modifyuserpermissions")){$userpermissions=hook("modifyuserpermissions");}
			$userrequestmode=0; # Always use 'email' request mode for external users
			
			# Load any plugins specific to the group of the sharing user, but only once as may be checking multiple keys
			global $emulate_plugins_set;			
			if ($emulate_plugins_set!==true)
				{
				global $plugins;
				$enabled_plugins = (sql_query("SELECT name,enabled_groups, config, config_json FROM plugins WHERE inst_version>=0 AND length(enabled_groups)>0  ORDER BY priority"));
				foreach($enabled_plugins as $plugin)
					{
					$s=explode(",",$plugin['enabled_groups']);
					if (in_array($usergroup,$s))
						{
						include_plugin_config($plugin['name'],$plugin['config'],$plugin['config_json']);
						register_plugin($plugin['name']);
						$plugins[]=$plugin['name'];
						}
					}
				for ($n=count($plugins)-1;$n>=0;$n--)
				    {
				    register_plugin_language($plugins[$n]);
				    }
				$emulate_plugins_set=true;					
				}
				
			}
			
			if($external_share_groups_config_options || stripos(trim($userinfo[0]["config_options"]),"external_share_groups_config_options=true")!==false)
				{
				# Apply config override options
				$config_options=trim($userinfo[0]["config_options"]);
				if ($config_options!="")
					{
					$co=explode(";",$config_options);
					foreach($co as $ext_co)
						{
						$co_parts=explode("=",$ext_co);
						
						if($co_parts[0]!='' && isset($co_parts[1]))
							{
							$name=str_replace("$","",$co_parts[0]);
							$value=ltrim($co_parts[1]); 
							if(strtolower($value)=='false'){$value=0;}
							elseif(strtolower($value)=='true'){$value=1;}
							
							global $$name;
							$$name = $value;
							}
						}
					}
				}
		
		# Special case for anonymous logins.
		# When a valid key is present, we need to log the user in as the anonymous user so they will be able to browse the public links.
		global $anonymous_login;
		if (isset($anonymous_login))
			{
			global $username,$baseurl;
			if(is_array($anonymous_login))
			{
			foreach($anonymous_login as $key => $val)
				{
				if($baseurl==$key){$anonymous_login=$val;}
				}
			}
			$username=$anonymous_login;		
			}
		
		# Set the 'last used' date for this key
		sql_query("update external_access_keys set lastused=now() where resource='$resource' and access_key='$key'");
		
		return true;
		}
	}


/**
* Check access key for a collection
* 
* @param integer $collection        Collection ID
* @param string  $key               Access key
* 
* @return boolean
*/
function check_access_key_collection($collection, $key)
    {
    if('' == $collection || !is_numeric($collection))
        {
        return false;
        }
    
    global $external_share_view_as_internal;
    if($external_share_view_as_internal && isset($_COOKIE["user"]))
        {
        // We want to authenticate the user so we can show the page as internal
        return false;
        }

    $resources = get_collection_resources($collection);

    if(0 == count($resources))
        {
        return false;
        }

    $invalid_resources = array();
    foreach($resources as $resource_id)
        {
        // Verify a supplied external access key for all resources in a collection
        if(!check_access_key($resource_id, $key))
            {
            $invalid_resources[] = $resource_id;
            }
        }

    if(count($resources) === count($invalid_resources))
        {
        return false;
        }

    // Set the 'last used' date for this key
    sql_query("UPDATE external_access_keys SET lastused = now() WHERE collection = '{$collection}' AND access_key = '{$key}'");

    return true;
    }

function make_username($name)
	{
	# Generates a unique username for the given name
	
	# First compress the various name parts
	$s=trim_array(explode(" ",$name));
	
	$name=$s[count($s)-1];
	for ($n=count($s)-2;$n>=0;$n--)
		{
		$name=substr($s[$n],0,1) . $name;
		}
	$name=safe_file_name(strtolower($name));

	# Create fullname usernames:
	global $user_account_fullname_create;
	if($user_account_fullname_create) {
		$name = '';

		foreach ($s as $name_part) {
			$name .= '_' . $name_part;
		}
		
		$name = substr($name, 1);
		$name = safe_file_name($name);
	}
	
	# Check for uniqueness... append an ever-increasing number until unique.
	$unique=false;
	$num=-1;
	while (!$unique)
		{
		$num++;
		$c=sql_value("select count(*) value from user where username='" . escape_check($name . (($num==0)?"":$num)) . "'",0);
		$unique=($c==0);
		}
	return $name . (($num==0)?"":$num);
	}
	
function get_registration_selectable_usergroups()
{
    # Returns a list of  user groups selectable in the registration . The standard user groups are translated using $lang. Custom user groups are i18n translated.

    # Executes query.
    $r = sql_query("select ref,name from usergroup where allow_registration_selection=1 order by name");

    # Translates group names in the newly created array.
    $return = array();
    for ($n = 0;$n<count($r);$n++) {
        $r[$n]["name"] = lang_or_i18n_get_translated($r[$n]["name"], "usergroup-");
        $return[] = $r[$n]; # Adds to return array.
    }

    return $return;

}

function remove_extension($strName)
{
$ext = strrchr($strName, '.');
if($ext !== false)
{
$strName = substr($strName, 0, -strlen($ext));
}
return $strName;
}

function get_fields($field_refs)
	{
	# Returns a list of fields with refs matching the supplied field refs.
	if (!is_array($field_refs)) {print_r($field_refs);exit(" passed to get_fields() is not an array. ");}
	$return=array();
	$fields=sql_query("select *, ref, name, title, type, order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, display_as_dropdown,tooltip_text,display_condition, onchange_macro from resource_type_field where  ref in ('" . join("','",$field_refs) . "') order by order_by");
	# Apply field permissions
	for ($n=0;$n<count($fields);$n++)
		{
		if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
		&& !checkperm("f-" . $fields[$n]["ref"]))
		{$return[]=$fields[$n];}
		}
	return $return;
	}

function get_hidden_indexed_fields()
	{
	# Return an array of indexed fields to which the current user does not have access
	# Used by do_search to ommit fields when searching.
	$hidden=array();
	global $hidden_fields_cache;
	if (is_array($hidden_fields_cache)){
		return $hidden_fields_cache;
	} else { 
		$fields=sql_query("select ref from resource_type_field where keywords_index=1 and length(name)>0");
		# Apply field permissions
		for ($n=0;$n<count($fields);$n++)
			{
			if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
			&& !checkperm("f-" . $fields[$n]["ref"]))
				{
				# Visible field
				}
			else
				{
				# Hidden field
				$hidden[]=$fields[$n]["ref"];
				}
			}
		$hidden_fields_cache=$hidden;
		return $hidden;
		}
	}
	
function get_category_tree_fields()
	{
	# Returns a list of fields with refs matching the supplied field refs.
	global $cattreefields_cache;
	if (is_array($cattreefields_cache)){
		return $cattreefields_cache;
	} else {
		$fields=sql_query("select name from resource_type_field where type=7 and length(name)>0 order by order_by");
		$cattreefields=array();
		foreach ($fields as $field){
			$cattreefields[]=$field['name'];
		}
		$cattreefields_cache=$cattreefields;
		return $cattreefields;
		}
	}	

function get_OR_fields()
	{
	# Returns a list of fields that should retain semicolon separation of keywords in a search string
	global $orfields_cache;
	if (is_array($orfields_cache)){
		return $orfields_cache;
	} else {
		$fields=sql_query("select name from resource_type_field where type=7 or type=2 or type=3 and length(name)>0 order by order_by");
		$orfields=array();
		foreach ($fields as $field){
			$orfields[]=$field['name'];
		}
		$orfields_cache=$orfields;
		return $orfields;
		}
	}		
	
function get_fields_for_search_display($field_refs)
{
    # Returns a list of fields/properties with refs matching the supplied field refs, for search display setup
    # This returns fewer columns and doesn't require that the fields be indexed, as in this case it's only used to judge whether the field should be highlighted.
    # Standard field titles are translated using $lang.  Custom field titles are i18n translated.

    if (!is_array($field_refs)) {
        print_r($field_refs);
        exit(" passed to getfields() is not an array. ");
    }

    # Executes query.
    $fields = sql_query("select *, ref, name, type, title, keywords_index, partial_index, value_filter from resource_type_field where ref in ('" . join("','",$field_refs) . "')");

    # Applies field permissions and translates field titles in the newly created array.
    $return = array();
    for ($n = 0;$n<count($fields);$n++) {
        if ((checkperm("f*") || checkperm("f" . $fields[$n]["ref"]))
        && !checkperm("f-" . $fields[$n]["ref"])) {
            $fields[$n]["title"] = lang_or_i18n_get_translated($fields[$n]["title"], "fieldtitle-");
            $return[] = $fields[$n];
        }
    }
    return $return;
}

function verify_extension($filename,$allowed_extensions=""){
	# Allowed extension?
	$extension=explode(".",$filename);
    if(count($extension)>1){
    	$extension=trim(strtolower($extension[count($extension)-1]));
		} else { return false;}
		
	if ($allowed_extensions!=""){
		$allowed_extensions=explode(",",strtolower($allowed_extensions));
		if (!in_array($extension,$allowed_extensions)){ return false;}
	}
	
	
	return true;
}

function get_allowed_extensions($ref){
	$type = sql_value("select resource_type value from resource where ref=$ref","");
	$allowed_extensions=sql_value("select allowed_extensions value from resource_type where ref=$type","");
	return $allowed_extensions;
}
function get_allowed_extensions_by_type($resource_type){
	$allowed_extensions=sql_value("select allowed_extensions value from resource_type where ref='$resource_type'","");
	return $allowed_extensions;
}

/**
 * Detect if a path is relative or absolute.
 * If it is relative, we compute its absolute location by assuming it is
 * relative to the application root (parent folder).
 * 
 * @param string $path A relative or absolute path
 * @param boolean $create_if_not_exists Try to create the path if it does not exists. Default to False.
 * @access public
 * @return string A absolute path
 */
function getAbsolutePath($path, $create_if_not_exists = false)
	{
	if(preg_match('/^(\/|[a-zA-Z]:[\\/]{1})/', $path)) // If the path start by a '/' or 'c:\', it is an absolute path.
		{
		$folder = $path;
		}
	else // It is a relative path.
		{
		$folder = sprintf('%s%s..%s%s', dirname(__FILE__), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}

	if ($create_if_not_exists && !file_exists($folder)) // Test if the path need to be created.
		{
		mkdir($folder,0777);
		} // Test if the path need to be created.

	return $folder;
	} // getAbsolutePath()



/**
 * Find the files present in a folder, and sub-folder.
 * 
 * @param string $path The path to look into.
 * @param boolean $recurse Trigger the recursion, default to True.
 * @param boolean $include_hidden Trigger the listing of hidden files / hidden directories, default to False.
 * @access public
 * @return array A list of files present in the inspected folder (paths are relative to the inspected folder path).
 */
function getFolderContents($path, $recurse = true, $include_hidden = false)
	{
	if(!is_dir($path)) // Test if the path is not a folder.
		{
			return array();
		} // Test if the path is not a folder.

	$directory_handle = opendir($path);
	if($directory_handle === false) // Test if the directory listing failed.
		{
		return array();
		} // Test if the directory listing failed.

	$files = array();
	while(($file = readdir($directory_handle)) !== false) // For each directory listing entry.
		{
		if(! in_array($file, array('.', '..'))) // Test if file is not unix parent and current path.
			{
			if($include_hidden || ! preg_match('/^\./', $file)) // Test if the file can be listed.
				{
				$complete_path = $path . DIRECTORY_SEPARATOR . $file;
				if(is_dir($complete_path) && $recurse) // If the path is a directory, and need to be explored.
					{
					$sub_dir_files = getFolderContents($complete_path, $recurse, $include_hidden);
					foreach($sub_dir_files as $sub_dir_file) // For each subdirectory contents.
						{
						$files[] = $file . DIRECTORY_SEPARATOR . $sub_dir_file;
						} // For each subdirectory contents.
					}
				elseif(is_file($complete_path)) // If the path is a file.
					{
					$files[] = $file;
					}
				} // Test if the file can be listed.
			} // Test if file is not unix parent and current path.
		} // For each directory listing entry.

	// We close the directory handle:
	closedir($directory_handle);

	// We sort the files alphabetically.
	natsort($files);

	return $files;
	} // getPathFiles()



/**
 * Returns filename component of path
 * This version is UTF-8 proof.
 * Thanks to nasretdinov at gmail dot com
 * @link http://www.php.net/manual/en/function.basename.php#85369
 * 
 * @param string $file A path.
 * @access public
 * @return string Returns the base name of the given path.
 */
function mb_basename($file)
	{
	$exploded_path = preg_split('/[\\/]+/',$file);
	return end($exploded_path);
	} // mb_basename()



/**
 * Remove the extension part of a filename.
 * Thanks to phparadise
 * http://fundisom.com/phparadise/php/file_handling/strip_file_extension
 * 
 * @param string $name A file name.
 * @access public
 * @return string Return the file name without the extension part.
 */
function strip_extension($name)
	{
	$ext = strrchr($name, '.');
	if($ext !== false)
		{
		$name = substr($name, 0, -strlen($ext));
		}
	return $name;
	} // strip_extension()



function get_nopreview_icon($resource_type,$extension,$col_size,$deprecated1=false,$deprecated2=false)
	{
	# Returns the path (relative to the gfx folder) of a suitable folder to represent
	# a resource with the given resource type or extension
	# Extension matches are tried first, followed by resource type matches
	# Finally, if there are no matches then the 'type1' image will be used.
	# set contactsheet to true to cd up one more level.
	
	global $language;
	
	$col=($col_size?"_col":"");
	$folder=dirname(dirname(__FILE__)) . "/gfx/";
	$extension=strtolower($extension);

	# Metadata template? Always use icon for 'mdtr', although typically no file will be attached.
	global $metadata_template_resource_type;
	if (isset($metadata_template_resource_type) && $metadata_template_resource_type==$resource_type) {$extension="mdtr";}


	# Try extension (language specific)
	$try="no_preview/extension/" . $extension . $col . "_" . $language . ".png";
	if (file_exists($folder . $try))
		{
		return $try;
		}
	# Try extension (default)
	$try="no_preview/extension/" . $extension . $col . ".png";
	if (file_exists($folder . $try))
		{
		return $try;
		}
	
	# --- Legacy ---
	# Support the old location for resource type and GIF format (root of gfx folder)
	# Some installations use custom types in this location.
	$try="type" . $resource_type . $col . ".gif";
	if (file_exists($folder . $try))
		{
		return $try;
		}


	# Try resource type (language specific)
	$try="no_preview/resource_type/type" . $resource_type . $col . "_" . $language . ".png";
	if (file_exists($folder . $try))
		{
		return $try;
		}
	# Try resource type (default)
	$try="no_preview/resource_type/type" . $resource_type . $col . ".png";
	if (file_exists($folder . $try))
		{
		return $try;
		}
	# Try a plugin
	$try=hook('plugin_nopreview_icon','',array($resource_type,$col));
	if (false !== $try && file_exists($folder . $try))
		{
		return $try;
		}
	
	# Fall back to the 'no preview' icon used for type 1.
	return "no_preview/resource_type/type1" . $col . ".png";
	}
	
	
function is_process_lock($name)
	{
	# Checks to see if a process lock exists for the given process name.
	global $storagedir,$process_locks_max_seconds;
	
	# Check that tmp/process_locks exists, create if not.
	# Since the get_temp_dir() method does this checking, omit: if(!is_dir($storagedir . "/tmp")){mkdir($storagedir . "/tmp",0777);}
	if(!is_dir(get_temp_dir() . "/process_locks")){mkdir(get_temp_dir() . "/process_locks",0777);}
	
	# No lock file? return false
	if (!file_exists(get_temp_dir() . "/process_locks/" . $name)) {return false;}
	
	$time=trim(file_get_contents(get_temp_dir() . "/process_locks/" . $name));
	if ((time()-$time)>$process_locks_max_seconds) {return false;} # Lock has expired
	
	return true; # Lock is valid
	}
	
function set_process_lock($name)
	{
	# Set a process lock
	file_put_contents(get_temp_dir() . "/process_locks/" . $name,time());
	// make sure this is editable by the server in case a process lock could be set by different system users
	chmod(get_temp_dir() . "/process_locks/" . $name,0777);
	return true;
	}
	
function clear_process_lock($name)
	{
	# Clear a process lock
	if (!file_exists(get_temp_dir() . "/process_locks/" . $name)) {return false;}
        unlink(get_temp_dir() . "/process_locks/" . $name);
	return true;
	}
	
	
function open_access_to_user($user,$resource,$expires)
	{
	# Give the user full access to the given resource.
	# Used when approving requests.
	
	# Delete any existing custom access
	sql_query("delete from resource_custom_access where user='$user' and resource='$resource'");
	
	# Insert new row
	sql_query("insert into resource_custom_access(resource,access,user,user_expires) values ('$resource','0','$user'," . ($expires==""?"null":"'$expires'") . ")");
	
	return true;
	}

function open_access_to_group($group,$resource,$expires)
	{
	# Give the user full access to the given resource.
	# Used when approving requests.
	
	# Delete any existing custom access
	sql_query("delete from resource_custom_access where usergroup=$group and resource=$resource");
	
	# Insert new row
	sql_query("insert into resource_custom_access(resource,access,usergroup,user_expires) values ('$resource','0',$group," . ($expires==""?"null":"'$expires'") . ")");
	
	return true;
	}

function resolve_open_access($userlist,$resource,$expires)
	{
	global $open_internal_access,$lang;
	
	$groupids=resolve_userlist_groups_smart($userlist);
	debug("smart_groups: list=".$groupids);
	if($groupids!='')
		{
		$groupids=explode(",",$groupids);
		foreach ($groupids as $group)
			{
			open_access_to_group($group,$resource,$expires);
			}
		$userlist=remove_groups_smart_from_userlist($userlist); 
		}
	if($userlist!='')
		{
		$userlist_array=explode(",",$userlist);
		debug("smart_groups: userlist=".$userlist);
		foreach($userlist_array as $option)
			{
			#user
			$userid=sql_value("select ref value from user where username='$option'","");
			if($userid!="")
				{
				open_access_to_user($userid,$resource,$expires);   
				}
			}
		}
	}
	
function remove_access_to_user($user,$resource)
	{
	# Remove any user-specific access granted by an 'approve'.
	# Used when declining requests.
	
	# Delete any existing custom access
	sql_query("delete from resource_custom_access where user='$user' and resource='$resource'");
	
	return true;
	}
	
function user_email_exists($email)
	{
	# Returns true if a user account exists with e-mail address $email
	$email=escape_check(trim(strtolower($email)));
	return (sql_value("select count(*) value from user where email like '$email'",0)>0);
	}

function filesize_unlimited($path)
    { 
    # A resolution for PHP's issue with large files and filesize().
	
	hook("beforefilesize_unlimited","",array($path));
	
    if (PHP_OS=='WINNT')
        {
		if (class_exists("COM"))
			{
			try
				{
				$filesystem=new COM('Scripting.FileSystemObject');
				$file=$filesystem->GetFile($path);
				return $file->Size();
				}
			catch (com_exception $e)
				{
				return false;
				}
			}

		return exec('for %I in (' . escapeshellarg($path) . ') do @echo %~zI' );
        }
	else if(PHP_OS == 'Darwin') 
    	{
        $bytesize = exec("stat -f '%z' " . escapeshellarg($path));
    	}
    else 
    	{
		$bytesize = exec("stat -c '%s' " . escapeshellarg($path));
    	}
    	
	if(!is_int($bytesize))
		{
		$bytesize= @filesize($path); # Bomb out, the output wasn't as we expected. Return the filesize() output.
		}
		
	hook("afterfilesize_unlimited","",array($path));
	
	return $bytesize;
    }

function strip_leading_comma($val)
    {
    return preg_replace('/^\,/','',$val);
    }

// String EnCrypt + DeCrypt function
// Author: halojoy, July 2006
// Modified and commented by: laserlight, August 2006
//
// Exploratory implementation using bitwise ops on strings; Weedpacket September 2006

function convert($text, $key = '') {
    // return text unaltered if the key is blank
    if ($key == '') {
        return $text;
    }

    // remove the spaces in the key
    $key = str_replace(' ', '', $key);
    if (strlen($key) < 8) {
        exit('key error');
    }
    // set key length to be no more than 32 characters
    $key_len = strlen($key);
    if ($key_len > 32) {
        $key_len = 32;
    }

    // A wee bit of tidying in case the key was too long
    $key = substr($key, 0, $key_len);

    // We use this a couple of times or so
    $text_len = strlen($text);

    // fill key with the bitwise AND of the ith key character and 0x1F, padded to length of text.
    $lomask = str_repeat("\x1f", $text_len); // Probably better than str_pad
    $himask = str_repeat("\xe0", $text_len);
    $k = str_pad("", $text_len, $key); // this one _does_ need to be str_pad

    // {en|de}cryption algorithm
    $text = (($text ^ $k) & $lomask) | ($text & $himask);

    return $text;
} 

function make_api_key($username,$password){
	// this is simply an encryption for username and password that will work as an alternative way to log in for remote access pages such as rss and apis
	// this is simply to avoid sending username and password plainly in the url.
	global $api_scramble_key;
    if (extension_loaded('mcrypt') && extension_loaded('hash')){
        $cipher = new Cipher($api_scramble_key);
        return $cipher->encrypt($username."|".$password,$api_scramble_key);
        }
    else{
        return strtr(base64_encode(convert($username."|".$password,$api_scramble_key)), '+/=', '-_,');
        }
	}
	
function decrypt_api_key($key){
	global $api_scramble_key;
    if (extension_loaded('mcrypt') && extension_loaded('hash')){
        $cipher = new Cipher($api_scramble_key);
        $key=$cipher->decrypt($key);
        }
    else{
	$key=convert(base64_decode(strtr($key, '-_,', '+/=')),$api_scramble_key);
        }
	return explode("|",$key);
	}

// alternative encryption using mcrypt extension
//from http://php.net/manual/en/function.mcrypt-encrypt.php
// IMPORTANT: temp fix to avoid redeclaring issues. An autoloader should be used instead (currently not available)
include_once 'classes/Cipher.php';

function purchase_set_size($collection,$resource,$size,$price)
	{
	// Set the selected size for an item in a collection. This is used later on when the items are downloaded.
	sql_query("update collection_resource set purchase_size='" . escape_check($size) . "',purchase_price='" . escape_check($price) . "' where collection='$collection' and resource='$resource'");
	return true;
	}

function payment_set_complete($collection,$emailconfirmation="")
	{
	global $applicationname,$baseurl,$userref,$username,$useremail,$userfullname,$email_notify,$lang,$currency_symbol;
	// Mark items in the collection as paid so they can be downloaded.
	sql_query("update collection_resource set purchase_complete=1 where collection='$collection'");
	
	// For each resource, add an entry to the log to show it has been purchased.
	$resources=sql_query("select * from collection_resource where collection='$collection'");
	$summary="<style>.InfoTable td {padding:5px;}</style><table border=\"1\" class=\"InfoTable\"><tr><td><strong>" . $lang["property-reference"] . "</strong></td><td><strong>" . $lang["size"] . "</strong></td><td><strong>" . $lang["price"] . "</strong></td></tr>";
	foreach ($resources as $resource)
		{
		$purchasesize=$resource["purchase_size"];
		if ($purchasesize==""){$purchasesize=$lang["original"];}
		resource_log($resource["resource"],"p",0,"","","",0,$resource["purchase_size"],$resource["purchase_price"]);
		$summary.="<tr><td>" . $resource["resource"] . "</td><td>" . $purchasesize . "</td><td>" . $currency_symbol . $resource["purchase_price"] . "</td></tr>";
		}
	$summary.="</table>";
	// Send email or notification to admin
	$message=$lang["purchase_complete_email_admin_body"] . "<br>" . $lang["username"] . ": " . $username . "(" . $userfullname . ")<br>" . $summary . "<br><br>$baseurl/?c=" . $collection . "<br>";
	$notificationmessage=$lang["purchase_complete_email_admin_body"] . "\r\n" . $lang["username"] . ": " . $username . "(" . $userfullname . ")";
	$notify_users=get_notification_users("RESOURCE_ACCESS"); 
	$message_users=array();
	foreach($notify_users as $notify_user)
			{
			get_config_option($notify_user['ref'],'user_pref_resource_access_notifications', $send_message);		  
            if($send_message==false){continue;}		
			
			get_config_option($notify_user['ref'],'email_user_notifications', $send_email);    
			if($send_email && $notify_user["email"]!="")
				{
				send_mail($notify_user["email"],$applicationname . ": " . $lang["purchase_complete_email_admin"],$message);
				}        
			else
				{
				$message_users[]=$notify_user["ref"];
				}
			}
			
	if (count($message_users)>0)
		{		
        message_add($message_users,$notificationmessage,$baseurl . "/?c=" . $collection,$userref);
		}	
	
	// Send email to user (not a notification as may need to be kept for reference)
	$confirmation_address=($emailconfirmation!="")?$emailconfirmation:$useremail;	
	$userconfirmmessage= $lang["purchase_complete_email_user_body"] . $summary . "<br><br>$baseurl/?c=" . $collection . "<br>";
	send_mail($useremail,$applicationname . ": " . $lang["purchase_complete_email_user"] ,$userconfirmmessage);
	
	// Rename so that can be viewed on my purchases page
	sql_query("update collection set name= '" . date("Y-m-d H:i") . "' where ref='$collection'");
	
	return true;

	}


/**
 * Determines where the tmp directory is.  There are three options here:
 * 1. tempdir - If set in config.php, use this value.
 * 2. storagedir ."/tmp" - If storagedir is set in config.php, use it and create a subfolder tmp.
 * 3. generate default path - use filestore/tmp if all other attempts fail.
 * 4. if a uniqid is provided, create a folder within tmp and return the full path
 * @param bool $asUrl - If we want the return to be like http://my.resourcespace.install/path set this as true.
 * @return string Path to the tmp directory.
 */
function get_temp_dir($asUrl = false,$uniqid="")
{
    global $storagedir, $tempdir;
    // Set up the default.
    $result = dirname(dirname(__FILE__)) . "/filestore/tmp";
	
    // if $tempdir is explicity set, use it.
    if(isset($tempdir))
    {
        // Make sure the dir exists.
        if(!is_dir($tempdir))
        {
            // If it does not exist, create it.
            mkdir($tempdir, 0777);
        }
        $result = $tempdir;
    }
    // Otherwise, if $storagedir is set, use it.
    else if (isset($storagedir))
    {
        // Make sure the dir exists.
        if(!is_dir($storagedir . "/tmp"))
        {
            // If it does not exist, create it.
            mkdir($storagedir . "/tmp", 0777);
        }
        $result = $storagedir . "/tmp";
    }
    else
    {
        // Make sure the dir exists.
        if(!is_dir($result))
        {
            // If it does not exist, create it.
            mkdir($result, 0777);
        }
    }
    
    if ($uniqid!=""){
		$uniqid=str_replace("../","",$uniqid);//restrict to forward-only movements
		$result.="/$uniqid";
		if(!is_dir($result)){
            // If it does not exist, create it.
            mkdir($result, 0777,true);
        }
    }
    
    // return the result.
    if($asUrl==true)
    {
        $result = convert_path_to_url($result);
	$result = str_replace('\\','/',$result);
    }
    return $result;
}

/**
 * Converts a path to a url relative to the installation.
 * @param string $abs_path: The absolute path.
 * @return Url that is the relative path.
 */
function convert_path_to_url($abs_path)
{
    // Get the root directory of the app:
    $rootDir = dirname(dirname(__FILE__));
    // Get the baseurl:
    global $baseurl;
    // Replace the $rootDir with $baseurl in the path given:
    return str_ireplace($rootDir, $baseurl, $abs_path);
}

function run_command($command, $geterrors=false)
	{
	# Works like system(), but returns the complete output string rather than just the
	# last line of it.
	global $debug_log,$config_windows;
	debug("CLI command: $command");
	if($debug_log || $geterrors) 
		{
		if($config_windows===true) 
			{
			$process = @proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipe, NULL, NULL, array('bypass_shell' => true));
			}
		else 
			{
			$process = @proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipe, NULL, NULL, array('bypass_shell' => true));
			}
		}
	else
		{
		$process = @proc_open($command, array(1 => array('pipe', 'w')), $pipe, NULL, NULL, array('bypass_shell' => true));
		}

	if (is_resource($process)) 
		{
		$output = trim(stream_get_contents($pipe[1]));
	 	if($geterrors)
			{
			$output.= trim(stream_get_contents($pipe[2]));
			}
		if ($debug_log)
			{
			debug("CLI output: $output");
			debug("CLI errors: ". trim(stream_get_contents($pipe[2])));
			}
		return $output;  
		}
	return '';
	}

function run_external($cmd,&$code)
{
# Thanks to dk at brightbyte dot de
# http://php.net/manual/en/function.shell-exec.php
# Returns an array with the resulting output (stdout & stderr). 
    debug("CLI command: $cmd");

    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w") // stderr is a file to write to
    );

    $pipes = array();
    $process = proc_open($cmd, $descriptorspec, $pipes);

    $output = array();

    if (!is_resource($process)) {return false;}

    # Close child's input immediately
    fclose($pipes[0]);

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    while (true)
        {
        $read = array();
        if (!feof($pipes[1])) {$read[] = $pipes[1];}
        if (!feof($pipes[2])) {$read[] = $pipes[2];}

        if (!$read) {break;}

        $write = NULL;
        $ex = NULL;
        $ready = stream_select($read, $write, $ex, 2);

        if ($ready===false)
            {
            break; # Should never happen - something died
            }

        foreach ($read as $r)
            {
            $s = rtrim(fgets($r, 1024),"\r\n"); # Reads a line and strips newline and carriage return from the end. 
            $output[] = $s;
            }
        }

    fclose($pipes[1]);
    fclose($pipes[2]);
    
    debug("CLI output: ". implode("\n", $output));

    $code = proc_close($process);

    return $output;
}

function error_alert($error,$back=true){

	foreach ($GLOBALS as $key=>$value){
		$$key=$value;
	} 
	if ($back){include(dirname(__FILE__)."/header.php");}
	echo "<script type='text/javascript'>
        ModalClose();
	alert('$error');";
	if ($back){echo "history.go(-1);";}
	echo "</script>";
}
/**
 * Returns an xml compliant string in UTF-8
 *
 * Built upon a code snippet from steve at mcdragonsoftware dot com
 * @link http://php.net/manual/en/function.htmlentities.php#106535
 * 
 * @param string $string A string to be made xml compliant.
 * @param string $fromcharset The charset of $string.
 * @access public
 * @return string Returns the xml compliant UTF-8 encoded string.
 */
function xml_entities($string, $fromcharset="")
    {
    # Convert the data to UTF-8 if not already.
    if ($fromcharset=="")
        {
        global $mysql_charset;
        if (isset($mysql_charset)) {$fromcharset = $mysql_charset;}
        else {$fromcharset = "UTF-8";} # Default to UTF-8.
        }
    if (strtolower($fromcharset)!="utf-8") {$string = mb_convert_encoding($string, 'UTF-8', $fromcharset);}

    # Sanitize the string to comply with xml:
    # http://en.wikipedia.org/wiki/Valid_characters_in_XML?section=1#XML_1.0
    $not_in_list = "A-Z0-9a-z\s_-";
    return preg_replace_callback("/[^{$not_in_list}]/u", 'get_xml_entity_at_index_0', $string);
    }
function get_xml_entity_at_index_0($char)
    {
    if (!is_string($char[0]) || (mb_strlen($char[0], "UTF-8") > 1))
        {
        die("function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$char[0]}' does not match this type.");
        }
    switch ($char[0])
        {
        # http://en.wikipedia.org/wiki/List_of_XML_and_HTML_character_entity_references#Predefined_entities_in_XML
        case '"':
            return "&quot;";
            break;
        case '&':
            return "&amp;";
            break;
        case "'":
            return "&apos;";
            break;
        case '<':
            return "&lt;";
            break;
        case '>':
            return "&gt;";
            break;
        default:
            return sanitize_char($char[0]);
            break;
        }
    }
function sanitize_char($char)
    {
    # http://en.wikipedia.org/wiki/Valid_characters_in_XML?section=1#XML_1.0
    $mb_ord = trim(mb_encode_numericentity($char, array(0x0, 0x10FFFF, 0, 0x10FFFF), "UTF-8"), "&#;");
    if ($mb_ord==0x0009 || $mb_ord==0x000A || $mb_ord==0x000D) {return $char;}
    if (($mb_ord>=0x0020 && $mb_ord<=0xD7FF) || ($mb_ord>=0xE000 && $mb_ord<=0xFFFD)) {return $char;}
    if ($mb_ord>=0x10000 && $mb_ord<=0x10FFFF) {return $char;}
    return ""; # Not a valid char, return an empty string.
    }

function format_display_field($value){
	
	// applies trim/wordwrap/highlights 
	global $results_title_trim,$results_title_wordwrap,$df,$x,$search;
	if(isset($df[$x]['type']) && $df[$x]['type']==8){
		$value=strip_tags($value);
	}
	$string=i18n_get_translated($value);
	$string=TidyList($string);
	//$string=tidy_trim($string,$results_title_trim);
	$string=htmlspecialchars($string);
	$string=highlightkeywords($string,$search,$df[$x]['partial_index'],$df[$x]['name'],$df[$x]['indexed']);
	
	return $string;
}

// formats a string with a collapsible more / less section
function format_string_more_link($string,$max_words_before_more=-1)
    {
    $words=preg_split('/[\t\f ]/',$string);
    if ($max_words_before_more==-1)
        {
        global $max_words_before_more;
        }
    if (count($words) < $max_words_before_more)
        {
        return $string;
        }
    global $lang;
    $unique_id=uniqid();
    $return_value = "";
    for ($i=0; $i<count($words); $i++)
        {
        if ($i>0)
            {
            $return_value .= ' ';
            }
        if ($i==$max_words_before_more)
            {
            $return_value .= '<a id="' . $unique_id . 'morelink" href="#" onclick="jQuery(\'#' . $unique_id . 'morecontent\').show(); jQuery(this).hide();">' .
                strtoupper($lang["action-more"]) . ' &gt;</a><span id="' . $unique_id . 'morecontent" style="display:none;">';
            }
        $return_value.=$words[$i];
        }
    $return_value .= ' <a href="#" onclick="jQuery(\'#' . $unique_id . 'morelink\').show(); jQuery(\'#' . $unique_id . 'morecontent\').hide();">&lt; ' .
        strtoupper($lang["action-less"]) . '</a></span>';
    return $return_value;
    }

// found multidimensional array sort function to support the performance footer
// http://www.php.net/manual/en/function.sort.php#104464
 function sortmulti ($array, $index, $order, $natsort=FALSE, $case_sensitive=FALSE) {
        if(is_array($array) && count($array)>0) {
            foreach(array_keys($array) as $key)
            $temp[$key]=$array[$key][$index];
            if(!$natsort) {
                if ($order=='asc')
                    asort($temp);
                else   
                    arsort($temp);
            }
            else
            {
                if ($case_sensitive===true)
                    natsort($temp);
                else
                    natcasesort($temp);
            if($order!='asc')
                $temp=array_reverse($temp,TRUE);
            }
            foreach(array_keys($temp) as $key)
                if (is_numeric($key))
                    $sorted[]=$array[$key];
                else   
                    $sorted[$key]=$array[$key];
            return $sorted;
        }
    return $sorted;
}

if (!function_exists("draw_performance_footer")){
function draw_performance_footer(){
	global $config_show_performance_footer,$querycount,$querytime,$querylog,$pagename,$hook_cache_hits,$hook_cache;
	$performance_footer_id=uniqid("performance");
	if ($config_show_performance_footer){	
	$querylog=sortmulti ($querylog, "time", "desc", FALSE, FALSE);
	# --- If configured (for debug/development only) show query statistics
	?>
	<?php if ($pagename=="collections"){?><br/><br/><br/><br/><br/><br/><br/>
	<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><div style="float:left;"><?php } else { ?><div style="float:right; margin-right: 10px;"><?php } ?>
	<table class="InfoTable" style="float: right;margin-right: 10px;">
	<tr><td>Page Load</td><td><?php show_pagetime();?></td></tr>
	<?php 
		if(isset($hook_cache_hits) && isset($hook_cache)) {			
		?>
		<tr><td>Hook cache hits</td><td><?php echo $hook_cache_hits;?></td></tr>	
		<tr><td>Hook cache entries</td><td><?php echo count($hook_cache); ?></td></tr>
		<?php
		}
	?>
	<tr><td>Query count</td><td><?php echo $querycount?></td></tr>
	<tr><td>Query time</td><td><?php echo round($querytime,4)?></td></tr>
	<?php $dupes=0;
	foreach ($querylog as $query=>$values){
			if ($values['dupe']>1){$dupes++;}
		}
	?>
	<tr><td>Dupes</td><td><?php echo $dupes?></td></tr>
	<tr><td colspan=2><a href="#" onClick="document.getElementById('querylog<?php echo $performance_footer_id?>').style.display='block';return false;">&gt;&nbsp;details</a></td></tr>
	</table>
	<table class="InfoTable" id="querylog<?php echo $performance_footer_id?>" style="display: none; float: <?php if ($pagename=='collections'){?>left<?php } else {?>right<?php }?>; margin: 10px;">
	<?php

		foreach($querylog as $query=>$values){
		if (substr($query,0,7)!="explain" && $query!="show warnings"){
		$show_warnings=false;
		if (strtolower(substr($query,0,6))=="select"){
			$explain=sql_query("explain extended ".$query);
			/*$warnings=sql_query("show warnings");
			$show_warnings=true;*/
		}
		?>
		<tr><td align="left"><div style="word-wrap: break-word; width:350px;"><?php echo $query?><?php if ($show_warnings){ foreach ($warnings as $warning){echo "<br /><br />".$warning['Level'].": ".htmlentities($warning['Message']);}}?></div></td><td>&nbsp;
		<table class="InfoTable">
		<?php if (strtolower(substr($query,0,6))=="select"){
			?><tr>
			<?php
			foreach ($explain[0] as $explainitem=>$value){?>
				<td align="left">   
				<?php echo $explainitem?><br /></td><?php 
				}
			?></tr><?php

			for($n=0;$n<count($explain);$n++){
				?><tr><?php
				foreach ($explain[$n] as $explainitem=>$value){?>
				<td align="left">   
					<?php echo str_replace(",",", ",$value)?></td><?php 
					}
				?></tr><?php	
				}
			}	?>
		</table>
		</td><td><?php echo round($values['time'],4)?></td>
		</td><td><?php echo ($values['dupe']>1)?''.$values["dupe"].'X':'1'?></td></tr>
		<?php	
		}
		}
	?>
	</table>
	</div>
	<?php
	}
}
}

function sql_affected_rows(){
	global $use_mysqli;
	if ($use_mysqli){
		global $db;
		return mysqli_affected_rows($db);
	}
	else {
		return mysql_affected_rows();
	}
}

function get_utility_path($utilityname, &$checked_path = null)
    {
    # !!! Under development - only some of the utilities are implemented!!!

    # Returns the full path to a utility if installed, else returns false.
    # Note that this function doesn't check that the utility is working.

    global $imagemagick_path, $ghostscript_path, $ghostscript_executable, $ffmpeg_path, $exiftool_path, $antiword_path, $pdftotext_path, $blender_path, $archiver_path, $archiver_executable;

    $checked_path = null;

    switch (strtolower($utilityname))
        {
        case "im-convert":
            if (!isset($imagemagick_path)) {return false;} # ImageMagick convert path not configured.
            return get_executable_path($imagemagick_path, array("unix"=>"convert", "win"=>"convert.exe"), $checked_path);
            break;
        case "im-identify":
            if (!isset($imagemagick_path)) {return false;} # ImageMagick identify path not configured.
            return get_executable_path($imagemagick_path, array("unix"=>"identify", "win"=>"identify.exe"), $checked_path);
            break;
        case "im-composite":
            if (!isset($imagemagick_path)) {return false;} # ImageMagick composite path not configured.
            return get_executable_path($imagemagick_path, array("unix"=>"composite", "win"=>"composite.exe"), $checked_path);
            break;
        case "im-mogrify":
            if (!isset($imagemagick_path)) {return false;} # ImageMagick mogrify path not configured.
            return get_executable_path($imagemagick_path, array("unix"=>"mogrify", "win"=>"mogrify.exe"), $checked_path);
            break;
        case "ghostscript":
            if (!isset($ghostscript_path)) {return false;} # Ghostscript path not configured.
            if (!isset($ghostscript_executable)) {return false;} # Ghostscript executable not configured.
            return get_executable_path($ghostscript_path, array("unix"=>$ghostscript_executable, "win"=>$ghostscript_executable), $checked_path, true); # Note that $check_exe is set to true. In that way get_utility_path() becomes backwards compatible with get_ghostscript_command().
            break;
        case "ffmpeg":
            if (!isset($ffmpeg_path)) {return false;} # FFmpeg path not configured.
            $return=get_executable_path($ffmpeg_path, array("unix"=>"ffmpeg", "win"=>"ffmpeg.exe"), $checked_path);
            if ($return===false)
                {
                # Support 'avconv' also
                return get_executable_path($ffmpeg_path, array("unix"=>"avconv", "win"=>"avconv.exe"), $checked_path);
                }
            else { return $return; }
            break;
        case "ffprobe":
            if (!isset($ffmpeg_path)) {return false;} # FFmpeg path not configured.
            $return=get_executable_path($ffmpeg_path, array("unix"=>"ffprobe", "win"=>"ffprobe.exe"), $checked_path);
            if ($return===false)
                {
                # Support 'avconv' also
                return get_executable_path($ffmpeg_path, array("unix"=>"avprobe", "win"=>"avprobe.exe"), $checked_path);
                }
            else { return $return; }
            break;        
        case "exiftool":
            //if (!isset($exiftool_path)) {return false;} # Exiftool path not configured.
            return get_executable_path($exiftool_path, array("unix"=>"exiftool", "win"=>"exiftool.exe"), $checked_path);
            break;
        case "antiword":
            break;
        case "pdftotext":
            break;
        case "blender":
            break;
        case "archiver":
            if (!isset($archiver_path)) {return false;} # Archiver path not configured.
            if (!isset($archiver_executable)) {return false;} # Archiver executable not configured.
            return get_executable_path($archiver_path, array("unix"=>$archiver_executable, "win"=>$archiver_executable), $checked_path);
            break;
        }
    }

function get_executable_path($path, $executable, &$checked_path, $check_exe = false)
    {
    global $config_windows;
    $os = php_uname('s');
    if ($config_windows || stristr($os, 'windows'))
        {
        $checked_path = $path . "\\" . $executable["win"];
        if (file_exists($checked_path)) {return escapeshellarg($checked_path);}
        if ($check_exe)
            {
            # Also check the path with a suffixed ".exe".
            $checked_path_without_exe = $checked_path;
            $checked_path = $path . "\\" . $executable["win"] . ".exe"; 
            if (file_exists($checked_path)) {return escapeshellarg($checked_path);}
            $checked_path = $checked_path_without_exe; # Return the checked path without the suffixed ".exe".
            }
        }
    else
        {
        $checked_path = stripslashes($path) . "/" . $executable["unix"];
        if (file_exists($checked_path)) {return escapeshellarg($checked_path);}
        }
    return false; # No path found.
    }

if (!function_exists("resolve_user_emails")){
function resolve_user_emails($ulist){
	global $lang, $user_select_internal;
	// return an array of emails from a list of usernames and email addresses. 
	// with 'key_required' sibling array preserving the intent of internal/external sharing.
	$emails_key_required=array();
	for ($n=0;$n<count($ulist);$n++)
		{
		$uname=$ulist[$n];
		$email=sql_value("select email value from user where username='" . escape_check($uname) . "'",'');
		if ($email=='')
			{
			# Not a recognised user, if @ sign present, assume e-mail address specified
			if (strpos($uname,"@")===false || (isset($user_select_internal) && $user_select_internal)) {
				error_alert($lang["couldnotmatchallusernames"] . ": " . escape_check($uname));die();
			}
			$emails_key_required['unames'][$n]=$uname;
			$emails_key_required['emails'][$n]=$uname;
			$emails_key_required['key_required'][$n]=true;
			}
		else
			{
			# Add e-mail address from user account
			$emails_key_required['unames'][$n]=$uname;
			$emails_key_required['emails'][$n]=$email;
			$emails_key_required['key_required'][$n]=false;
			}
		}
	return $emails_key_required;
}	
}


function truncate_cache_arrays(){
    $cache_array_limit = 2000;
    // function to prevent cache arrays from going rogue
    // this will prevent long-running scripts from dying as these
    // caches exhaust available memory.
    if (count($GLOBALS['get_resource_data_cache']) > $cache_array_limit){
        $GLOBALS['get_resource_data_cache'] = array();
        // future improvement: get rid of only oldest, instead of clearing all?
        // this would require a way to guage the age of the entry.
    }
    if (count($GLOBALS['get_resource_path_fpcache']) > $cache_array_limit){
        $GLOBALS['get_resource_path_fpcache'] = array();
    }
}


function txt2html($txt) {
// Transforms txt in html
// based on http://blog.matrixresources.com/blog/using-php-html-ize-plain-text
  $txt = htmlentities($txt,ENT_COMPAT,"UTF-8");
  // keep whitespacing
  while( !( strpos($txt,'  ') === FALSE ) ) $txt = str_replace('  ','&nbsp; ',$txt);

  //Basic formatting
  $eol = ( strpos($txt,"\r") === FALSE ) ? "\n" : "\r\n";
  $html = str_replace("$eol"," <br/> ",$txt);


/* General rules for replacing images */ 
$imgReplacement = 
	"<img align=left width=180 src=../..$5$6$7$8 /><br/>";

/* Rules per supported file type */ 
$extArray = array (
//	".html" => "<" . "a href=../..$5$6$7$8>$4$5$6$7$8"."</a>",
//	".php" => "<" . "a href=../..$5$6$7$8>$4$5$6$7$8"."</a>",
	".jpg" => $imgReplacement,
	".png" => $imgReplacement,
	".gif" => $imgReplacement,
	"" => "<" . "a href=http://$4$5$6$7$8>$4$5$6$7$8"."</a>");
/* $1 = http:
 * $2 = http
 * $3 = //www.eilertech.com
 * $4 = www.eilertech.com
 * $5 = /stories/powernaut/ 
 * $6 = 1941
 * $7 = .htm
 * $8 = #1
 * $9 = 1
 * Excluded:  ?fn=britannia_beach.txt */ 
 
// For each supported file type, up to and including Blank 
foreach ($extArray as $ext => $replacement) {

  // Define the search pattern here 
  $pattern = 
  "|((http):)(//([^/?# ]*))([^?# ,\.\)]*/)([^\.]*)?(" . $ext
  //12       3  4          5               6        7  
  . "[^# ,\)]*)(#([^ ,\.]*))?|i";
  //           8 9  
  
  /* We have the pattern, the replacement, and the HTML being built;
   * do the replacement. */ 
  $html = preg_replace ($pattern, $replacement, $html);
}

$html=preg_replace('/\*(\w.*?)\*/','<b>$1</b>',$html);

  return $html;
}

function is_html($string)
{
  return preg_match("/<[^<]+>/",$string,$m) != 0;
}

function rs_setcookie($name, $value, $daysexpire = 0, $path = "", $domain = "", $secure = false, $httponly = true)
    {
    # Note! The argument $daysexpire is not the same as the argument $expire in the PHP internal function setcookie.
    # Note! The $path argument is not used if $global_cookies = true

    if (php_sapi_name()=="cli") {return true;} # Bypass when running from the command line (e.g. for the test scripts).
    
    global $baseurl_short, $global_cookies;
    if ($daysexpire==0) {$expire = 0;}
    else {$expire = time() + (3600*24*$daysexpire);}

    if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === getservbyname("https", "tcp")))
    	{
    	$secure=true;
    	}
     	
    if ($global_cookies)
        {
        # Remove previously set cookies to avoid clashes
        //setcookie($name, "", time() - 3600, $baseurl_short . "pages/", $domain, $secure, $httponly);
        //setcookie($name, "", time() - 3600, $baseurl_short, $domain, $secure, $httponly);
        # Set new cookie
        setcookie($name, $value, $expire, "/", $domain, $secure, $httponly);
        }
    else
        {
        # Set new cookie
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
    }

function get_editable_states($userref)
	// Get an array of all the states that a user has edit access to
	{
	global $additional_archive_states, $lang;
	if($userref==-1){return false;}
	$editable_states=array();
	$x=0;
	for ($n=-2;$n<=3;$n++)
		{
		if (checkperm("e" . $n)) {$editable_states[$x]['id']=$n;$editable_states[$x]['name']=$lang["status" . $n];$x++;}		
		}
	foreach ($additional_archive_states as $additional_archive_state)
		{
		if (checkperm("e" . $additional_archive_state)) { $editable_states[$x]['id']=$additional_archive_state;$editable_states[$x]['name']=$lang["status" . $additional_archive_state];$x++;}		
		}
	return $editable_states;
	}
        
function validate_html($html)
    {
    # Returns true if $html is valid HTML, otherwise an error string describing the problem.
    
    $parser=xml_parser_create();
    xml_parse_into_struct($parser,"<div>" . str_replace("&","&amp;",$html) . "</div>",$vals,$index);
    $errcode=xml_get_error_code($parser);
    if ($errcode!==0)
	{
	$line=xml_get_current_line_number($parser);
        
	$error=htmlspecialchars(xml_error_string($errcode)) . "<br>Line: " . $line . "<br><br>";
	$s=explode("\n",$html);
	$error.= "<pre>" . trim(htmlspecialchars(@$s[$line-2])) . "<br>";
	$error.= "<strong>" . trim(htmlspecialchars(@$s[$line-1])) . "</strong><br>";
	$error.= trim(htmlspecialchars(@$s[$line])) . "<br></pre>";		
	return $error;
	}
    else
        {
        return true;
        }
    }

function get_indexed_resource_type_fields()
	{
	return sql_array("select ref as value from resource_type_field where keywords_index=1");
	}

function get_resource_type_fields($restypes="", $field_order_by="ref", $field_sort="asc", $find="")
	{
	// Gets all metadata fields, optionally for a specified array of resource types 
	$conditionsql="";
	if(is_array($restypes))
		{
		$conditionsql = " where resource_type in (" . implode(",",$restypes) . ")";
		}
	if($find!="")
		{
		$find=escape_check($find);
		if($conditionsql!="")
			{
			$conditionsql.=" and ( ";
			}
		else
			{
			$conditionsql.=" where ( ";
			}
		$conditionsql.=" name like '%" . $find . "%' or title like '%" . $find . "%' or tab_name like '%" . $find . "%' or exiftool_field like '%" . $find . "%' or help_text like '%" . $find . "%' or ref like '%" . $find . "%' or tooltip_text like '%" .$find . "%' or display_template like '%" .$find . "%')";
		}
	// Allow for sorting, enabled for use by System Setup pages
	//if(!in_array($field_order_by,array("ref","name","tab_name","type","order_by","keywords_index","resource_type","display_field","required"))){$field_order_by="ref";}		
		
	$allfields = sql_query("select *, ref, name, title, type, order_by, keywords_index, partial_index, resource_type, resource_column, display_field, use_for_similar, iptc_equiv, display_template, tab_name, required, smart_theme_name, exiftool_field, advanced_search, simple_search, help_text, display_as_dropdown, tooltip_text from resource_type_field" . $conditionsql . " order by " . $field_order_by . " " . $field_sort);
	return $allfields;
	
	}


function generateURL($url,$parameters=array(),$setparams=array())
    {
    foreach($setparams as $setparam=>$setvalue)
        {
        if($setparam!="")
            {$parameters[$setparam]=$setvalue;}
        }
    $querystringparams=array();
    foreach($parameters as $parameter=>$parametervalue)
        {
        $querystringparams[]= $parameter . "=" . urlencode($parametervalue);
        }
    $querystring="?" . implode ("&", $querystringparams);
    
    $returnurl= $url . $querystring;
    return $returnurl;
     
    }

function notify_resource_change($resource)
	{
	debug("notify_resource_change " . $resource);
	global $notify_on_resource_change_days;
	// Check to see if we need to notify users of this change
	if($notify_on_resource_change_days==0 || !is_int($notify_on_resource_change_days))
		{
		return false;
		}
		
	debug("notify_resource_change - checking for users that have downloaded this resource " . $resource);
	$download_users=sql_query("select u.ref, u.email from resource_log rl left join user u on rl.user=u.ref where rl.type='d' and rl.resource=$resource and datediff(now(),date)<'$notify_on_resource_change_days'","");
	$message_users=array();
	if(count($download_users>0))
		{
		global $applicationname, $lang, $baseurl;
		foreach ($download_users as $download_user)
			{
			get_config_option($download_user['ref'],'user_pref_resource_notifications', $send_message);		  
            if($send_message==false){continue;}		
			
            get_config_option($download_user['ref'],'email_user_notifications', $send_email);
            if($send_email && $download_user["email"]!="")
                {
                send_mail($download_user['email'],$applicationname . ": " . $lang["notify_resource_change_email_subject"],str_replace(array("[days]","[url]"),array($notify_on_resource_change_days,$baseurl . "/?r=" . $resource),$lang["notify_resource_change_email"]),"","",'notify_resource_change_email',array("days"=>$notify_on_resource_change_days,"url"=>$baseurl . "/?r=" . $resource));
                }
            else
                {
				$message_users[]=$download_user["ref"];
                }
			}
		if (count($message_users)>0)
			{
            message_add($message_users,str_replace(array("[days]","[url]"),array($notify_on_resource_change_days,$baseurl . "/?r=" . $resource),$lang["notify_resource_change_notification"]),$baseurl . "/?r=" . $resource);
			}
		}
	}

# Takes a string and add verbatim regex matches to the keywords list on found matches (for that field)
# It solves the problem, for example, indexing an entire "nnn.nnn.nnn" string value when '.' are used as a keyword separator.
# Uses config option $resource_field_verbatim_keyword_regex[resource type field] = '/regex/'
# Also changes "field:<value>" type searches to "field:,<value>" for full matching for field types such as "Check box list" (config option to specify this)
function add_verbatim_keywords(&$keywords, $string, $resource_type_field, $called_from_search=false)
	{
	global $resource_field_verbatim_keyword_regex,$resource_field_checkbox_match_full;

	// add ",<string>" if specified resource_type_field is found within $resource_field_checkbox_match_full array.
	if( !$called_from_search &&
		isset($resource_field_checkbox_match_full) &&
		is_array($resource_field_checkbox_match_full) &&
		in_array($resource_type_field,$resource_field_checkbox_match_full))
		{
		preg_match_all('/,[^,]+/', $string, $matches);
		if (isset($matches[0][0]))
			{
			foreach ($matches[0] as $match)
				{
				$match=strtolower($match);
				array_push($keywords,$match);
				}
			}
		}

	// normal verbatim expansion of keywords as defined in config.php
	if (!empty($resource_field_verbatim_keyword_regex[$resource_type_field]))
		{
		preg_match_all($resource_field_verbatim_keyword_regex[$resource_type_field], $string, $matches);
		foreach ($matches as $match)
			{
			foreach ($match as $sub_match)
				{
				array_push($keywords, $sub_match);        // note that the keywords array is passed in by reference.
				}
			}
		}

	// when searching change "field:<string>" to "field:,<string>" if specified resource_type_field is found within $resource_field_checkbox_match_full array.
	if ($called_from_search &&
		isset($resource_field_checkbox_match_full) &&
		is_array($resource_field_checkbox_match_full) &&
		in_array($resource_type_field,$resource_field_checkbox_match_full))
		{
		$found_name = sql_value("SELECT `name` AS 'value' FROM `resource_type_field` WHERE `ref`='{$resource_type_field}'", "");
		preg_match_all('/' . $found_name . ':([^,]+)/', $string, $matches);
		if (isset($matches[1][0]))
			{
			foreach ($matches[1] as $match)
				{
				$match=strtolower($match);
				$remove = "{$found_name}:{$match}";
				if (in_array($remove,$keywords))
					{
					unset($keywords[array_search($remove,$keywords)]);
					}
				array_push($keywords, "{$found_name}:,{$match}");
				}
			}
		}
	}

# Tails a file using native PHP functions.
# First introduced with system console.
# Credit to:
# http://www.geekality.net/2011/05/28/php-tail-tackling-large-files
function tail($filename, $lines = 10, $buffer = 4096)
	{
	$f = fopen($filename, "rb");		// Open the file
	fseek($f, -1, SEEK_END);		// Jump to last character

	// Read it and adjust line number if necessary
	// (Otherwise the result would be wrong if file doesn't end with a blank line)
	if(fread($f, 1) != "\n") $lines -= 1;

	// Start reading
	$output = '';
	$chunk = '';

	// While we would like more
	while(ftell($f) > 0 && $lines >= 0)
		{
		$seek = min(ftell($f), $buffer);		// Figure out how far back we should jump
		fseek($f, -$seek, SEEK_CUR);		// Do the jump (backwards, relative to where we are)
		$output = ($chunk = fread($f, $seek)).$output;		// Read a chunk and prepend it to our output
		fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);		// Jump back to where we started reading
		$lines -= substr_count($chunk, "\n");		// Decrease our line counter
		}

	// While we have too many lines
	// (Because of buffer size we might have read too many)
	while($lines++ < 0)
		{
		// Find first newline and remove all text before that
		$output = substr($output, strpos($output, "\n") + 1);
		}

	// Close file and return
	fclose($f);
	return $output;
	}	
	
function create_password_reset_key($username)
    {
    global $scramble_key;
    $resetuniquecode=make_password();
    $password_reset_hash=hash('sha256', date("Ymd") . md5("RS" . $resetuniquecode . $username . $scramble_key));  
    sql_query("update user set password_reset_hash='$password_reset_hash' where username='" . escape_check($username) . "'");	
    $password_reset_url_key=substr(hash('sha256', date("Ymd") . $password_reset_hash . $username . $scramble_key),0,15);
    return $password_reset_url_key;
    }
	
function get_rs_session_id($create=false)
    {
	global $baseurl;
    // Note this is not a PHP session, we are using this to create an ID so we can distinguish between anonymous users
    if(isset($_COOKIE["rs_session"]))
        {
		rs_setcookie("rs_session",$_COOKIE["rs_session"], 7, "", "", substr($baseurl,0,5)=="https", true); // extend the life of the cookie
		return($_COOKIE["rs_session"]);
		}
    if ($create) 
        {
        // Create a new ID - numeric values only so we can search for it easily
        $rs_session= rand();
        global $baseurl;
        rs_setcookie("rs_session",$rs_session, 7, "", "", substr($baseurl,0,5)=="https", true);
		return $rs_session;
        }
    return false;
    }
	
        
function metadata_field_edit_access($field)
	{
	return (!checkperm("F*") || checkperm("F-" . $field))&& !checkperm("F" . $field);
	}


/**
* Utility function used to move the element of one array from a position 
* to another one in the same array
* Note: the manipulation is done on the same array
*
* @param  array    $array
* @param  integer  $from_index  Array index we are moving from
* @param  integer  $to_index    Array index we are moving to
*
* @return void
*/
function move_array_element(array &$array, $from_index, $to_index)
    {
    $out = array_splice($array, $from_index, 1);
    array_splice($array, $to_index, 0, $out);

    return;
    }
    
function emptyiszero($value)
    {
    return ($value !== null && $value !== false && trim($value) !== '');
    }


// Add array_column if <PHP 5.5
if(!function_exists("array_column"))
{
   function array_column($array,$column_name)
    {
        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);
    }
}


/**
* Get data for each image that should be used on the slideshow.
* The format of the returned array should be: 
* Array
* (
*   [1] => Array
*         (
*             [file_path] => /var/www/include/../gfx/homeanim/gfx/1.jpg
*             [checksum] => 1450107521
*             [link] => http://localhost/pages/view.php?ref=6019
*             [link_file_path] => /var/www/include/../gfx/homeanim/gfx/1.txt
*         )
*   [2] => Array
*        (
*            [file_path] => /var/www/include/../gfx/homeanim/gfx/2.jpg
*            [checksum] => 2900215034
*        )
*   [3] => Array
*        (
*            [file_path] => /var/www/include/../gfx/homeanim/gfx/3.jpg
*            [checksum] => 4350322559
*        )
* )
* 
* @return array
*/
function get_slideshow_files_data()
    {
    global $baseurl, $homeanim_folder;

    $dir = dirname(__FILE__) . '/../' . $homeanim_folder;
    $d   = scandir($dir);
    sort($d, SORT_NUMERIC);

    $filecount       = 0;
    $checksum        = 0;
    $slideshow_files = array();

    foreach($d as $file)
        {
        if(preg_match("/[0-9]+\.(jpg)$/", $file))
            {
            $filecount++;
            $slideshow_file_id = substr($file, 0, -4);
            $checksum += filemtime($dir . '/' . $file);

            $slideshow_files[$slideshow_file_id] = array();
            $slideshow_files[$slideshow_file_id]['file_path'] = $dir . '/' . $file;
            $slideshow_files[$slideshow_file_id]['checksum']  = $checksum;

            $linkref        = '';
            $linkfile       = substr($file, 0, (strlen($file) - 4)) . '.txt';
            $link_file_path = $dir . '/' . $linkfile;

            if(file_exists($link_file_path))
                {
                $linkref    = file_get_contents($link_file_path);
                $linkaccess = get_resource_access($linkref);
                if('' !== $linkaccess && (0 == $linkaccess || 1 == $linkaccess))
                    {
                    $slideshow_files[$slideshow_file_id]['link'] = $baseurl . "/pages/view.php?ref=" . $linkref;
                    $slideshow_files[$slideshow_file_id]['link_file_path'] = $link_file_path;
                    }
                }
            }
        }

    return $slideshow_files;
    }

	
	
function get_notification_users($userpermission="SYSTEM_ADMIN")
    {
    // Returns an array of users (refs and emails) for use when sending email notifications (messages that in the past went to $email_notify, which can be emulated by using $email_notify_usergroups)
	// Can be passed a specific user type or an array of permissions
	// Types supported:-
	// SYSTEM_ADMIN
	// RESOURCE_ACCESS
	// RESEARCH_ADMIN
	// USER_ADMIN
    // RESOURCE_ADMIN
	
    global $notification_users_cache, $usergroup,$email_notify_usergroups;
	$userpermissionindex=is_array($userpermission)?implode("_",$userpermission):$userpermission;
    if(isset($notification_users_cache[$userpermissionindex]))
        {return $notification_users_cache[$userpermissionindex];}
        
    if(is_array($email_notify_usergroups) && count($email_notify_usergroups)>0)
		{
		// If email_notify_usergroups is set we use these over everything else, as long as they have an email address set
        $notification_users_cache[$userpermissionindex] = sql_query("select ref, email from user where usergroup in (" . implode(",",$email_notify_usergroups) . ") and email <>''");
        return $notification_users_cache[$userpermissionindex];
		}
	
	if(!is_array($userpermission))
		{
		// We have been passed a specific type of administrator to find 
		switch($userpermission)
			{
			case "USER_ADMIN";
			// Return all users in groups with u permissions AND either no 'U' restriction, or with 'U' but in appropriate group
			$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where find_in_set(binary 'u',ug.permissions) <> 0 and u.ref<>''" . (is_int($usergroup)?" and (find_in_set(binary 'U',ug.permissions) = 0 or ug.ref =(select parent from usergroup where ref=" . $usergroup . "))":""));	
			return $notification_users_cache[$userpermissionindex];
			break;
			
			case "RESOURCE_ACCESS";
			// Notify users who can grant access to resources, get all users in groups with R permissions
			$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where find_in_set(binary 'R',ug.permissions) <> 0");	
			return $notification_users_cache[$userpermissionindex];		
			break;
			
			case "RESEARCH_ADMIN";
			// Notify research admins, get all users in groups with r permissions
			$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where find_in_set(binary 'r',ug.permissions) <> 0");	
			return $notification_users_cache[$userpermissionindex];		
			break;
					
			case "RESOURCE_ADMIN";
			// Get all users in groups with t and e0 permissions
			$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where find_in_set(binary 't',ug.permissions) <> 0 and find_in_set(binary 'e0',ug.permissions)");	
			return $notification_users_cache[$userpermissionindex];
			break;
            
            case "SYSTEM_ADMIN";
			default;
			// Get all users in groups with a permission (default if incorrect admin type has been passed)
			$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where find_in_set(binary 'a',ug.permissions) <> 0");	
			return $notification_users_cache[$userpermissionindex];
			break;
		
			}
		}
	else
		{
		// An array has been passed, find all users with these permissions
		$condition="";
		foreach ($userpermission as $permission)
			{
			if($condition!=""){$condition.=" and ";}
			$condition.="find_in_set(binary '" . $permission . "',ug.permissions) <> 0";
			}
		$notification_users_cache[$userpermissionindex] = sql_query("select u.ref, u.email from usergroup ug join user u on u.usergroup=ug.ref where $condition");	
		return $notification_users_cache[$userpermissionindex];
		}
	}
        
function form_value_display($row,$name,$default="")
    {
    # Returns a sanitised row from the table in a safe form for use in a form value, suitable overwritten by POSTed data if it has been supplied.
    if (array_key_exists($name,$row)) {$default=$row[$name];}
    return htmlspecialchars(getval($name,$default));
    }

function get_download_filename($ref,$size,$alternative,$ext)
	{
	# Constructs a filename for download
	global $original_filenames_when_downloading,$download_filenames_without_size,$download_id_only_with_size,$download_filename_id_only,$download_filename_field,$prefix_resource_id_to_filename,$filename_field,$prefix_filename_string;
	
	$filename = $ref . $size . ($alternative>0?"_" . $alternative:"") . "." . $ext;
	
	if ($original_filenames_when_downloading)
		{
		# Use the original filename.
		if ($alternative>0)
			{
			# Fetch from the resource_alt_files alternatives table (this is an alternative file)
			$origfile=get_alternative_file($ref,$alternative);
			$origfile=$origfile["file_name"];
			}
		else
			{
			# Fetch from field data or standard table	
			$origfile=get_data_by_field($ref,$filename_field);	
			}
		if (strlen($origfile)>0)
			{
			# do an extra check to see if the original filename might have uppercase extension that can be preserved.	
			$pathparts=pathinfo($origfile);
			if (isset($pathparts['extension'])){
				if (strtolower($pathparts['extension'])==$ext){$ext=$pathparts['extension'];}	
			} 
			
			# Use the original filename if one has been set.
			# Strip any path information (e.g. if the staticsync.php is used).
			# append preview size to base name if not the original
			if($size != '' && !$download_filenames_without_size)
				{
				$filename = strip_extension(mb_basename($origfile)) . '-' . $size . '.' . $ext;
				}
			else
				{
				$filename = strip_extension(mb_basename($origfile)) . '.' . $ext;
				}

			if($prefix_resource_id_to_filename)
				{
				$filename = $prefix_filename_string . $ref . "_" . $filename;
				}
			}
		}

	if ($download_filename_id_only){
		if(!hook('customdownloadidonly', '', array($ref, $ext, $alternative))) {
			$filename=$ref . "." . $ext;

			if($size != '' && $download_id_only_with_size) {
				$filename = $ref . '-' . $size . '.' . $ext;
			}

			if(isset($prefix_filename_string) && trim($prefix_filename_string) != '') {
				$filename = $prefix_filename_string . $filename;
			}

		}
	}
	
	if (isset($download_filename_field))
		{
		$newfilename=get_data_by_field($ref,$download_filename_field);
		if ($newfilename)
			{
			$filename = trim(nl2br(strip_tags($newfilename)));
			if($size != "" && !$download_filenames_without_size)
				{
				$filename = substr($filename, 0, 200) . '-' . $size . '.' . $ext;
				}
			else
				{
				$filename = substr($filename, 0, 200) . '.' . $ext;
				}

			if($prefix_resource_id_to_filename)
				{
				$filename = $prefix_filename_string . $ref . '_' . $filename;
				}
			}
		}

	# Remove critical characters from filename
	$altfilename=hook("downloadfilenamealt");
	if(!($altfilename)) $filename = preg_replace('/:/', '_', $filename);
	else $filename=$altfilename;

    hook("downloadfilename");
	return $filename;
	}

function job_queue_add($type="",$job_data=array(),$user="",$time="", $success_text="", $failure_text="", $job_code="")
	{
	// Adds a job to the job_queue table.
	if($time==""){$time=date('Y-m-d H:i:s');}
	if($type==""){return false;}
	if($user==""){global $userref;$user=isset($userref)?$userref:0;}
    $job_data_json=json_encode($job_data,JSON_UNESCAPED_SLASHES); // JSON_UNESCAPED_SLASHES is needed so we can effectively compare jobs
    // Check for existing job matching
    $existing_user_jobs=job_queue_get_jobs($type,STATUS_ACTIVE,"",$job_code);
	if(count($existing_user_jobs)>0)
            {
            global $lang;
            return $lang["job_queue_duplicate_message"];
            }
	sql_query("insert into job_queue (type,job_data,user,start_date,status,success_text,failure_text,job_code) values('" . escape_check($type) . "','" . escape_check($job_data_json) . "','" . $user . "','" . $time . "','" . STATUS_ACTIVE .  "','" . $success_text . "','" . $failure_text . "','" . escape_check($job_code) . "')");
    return true;
	}
	
function job_queue_update($ref,$job_data=array(),$newstatus="", $newtime="")
	{
	$sql="update  job_queue set job_data='" . escape_check(json_encode($job_data)) . "'";
	if($newtime!=""){$sql.=",start_date='" . $newtime . "'";}
	if($newstatus!=""){$sql.=",status='" . $newstatus . "'";}
	$sql.=" where ref='" . $ref . "'";
	sql_query($sql);
	}

function job_queue_delete($ref)
	{
	sql_query("delete from job_queue where ref='" . $ref . "'");
	}

function job_queue_get_jobs($type="", $status="", $user="", $job_code="", $job_order_by="ref", $job_sort="desc", $find="")
	{
	// Gets offline jobs
	$condition=array();
	if($type!=""){$condition[] = " type ='" . escape_check($type) . "'";}
	if($status!=""){$condition[] =" status ='" . escape_check($status) . "'";}
	if($user!=""){$condition[] =" user ='" . escape_check($user) . "'";}
	if($job_code!=""){$condition[] =" job_code ='" . escape_check($job_code) . "'";}
	if($find!="")
		{
		$find=escape_check($find);
		$condition[] = " (j.ref like '%" . $find . "%'  or j.job_data like '%" . $find . "%' or j.success_text like '%" . $find . "%' or j.failure_text like '%" . $find . "%' or j.user like '%" . $find . "%' or u.username like '%" . $find . "%' or u.fullname like '%" . $find . "%')";
		}
	$conditional_sql="";
	if (count($condition)>0){$conditional_sql=" where " . implode(" and ",$condition);}
		
	$sql = "select j.ref,j.type,j.job_data,j.user,j.status, j.start_date, j.success_text, j.failure_text,j.job_code, u.username, u.fullname from job_queue j left join user u on u.ref=j.user " . $conditional_sql . " order by " . escape_check($job_order_by) . " " . escape_check($job_sort);
	$jobs=sql_query($sql);
	return $jobs;
	}
	
function job_queue_run_job($job)
	{
	// Runs offline job using defined job handler
	$jobref = $job["ref"];
	$job_data=json_decode($job["job_data"], true);
	$jobuser = $job["user"];
    $job_success_text=$job["success_text"];
	$job_failure_text=$job["failure_text"];
	
	if(is_process_lock('job_' . $jobref)){return;}
	set_process_lock('job_' . $jobref);
	
	$logmessage =  " - Running job #" . $jobref . PHP_EOL;
	echo $logmessage;
	debug($logmessage);
	
	$logmessage =  " - Looking for " . __DIR__ . "/job_handlers/" . $job["type"] . ".php" . PHP_EOL;
	echo $logmessage;
	debug($logmessage);
		
	if (file_exists(__DIR__ . "/job_handlers/" . $job["type"] . ".php"))
		{
		$logmessage="Attempting to run job #" . $jobref . " using handler " . $job["type"]. PHP_EOL;
		echo $logmessage;
		debug($logmessage);
		include __DIR__ . "/job_handlers/" . $job["type"] . ".php";
		}
	else
		{
		$logmessage="Unable to find handlerfile: " . $job["type"]. PHP_EOL;
		echo $logmessage;
		debug($logmessage);
		job_queue_update($jobref,$job_data,STATUS_ERROR);
		}
	
	$logmessage =  " - Finished job #" . $jobref . PHP_EOL;
	echo $logmessage;
	debug($logmessage);
	
	clear_process_lock('job_' . $jobref);
	}
        
function user_set_usergroup($user,$usergroup)
    {
    sql_query("update user set usergroup='" . escape_check($usergroup) . "' where ref='" . escape_check($user) . "'");
    }
