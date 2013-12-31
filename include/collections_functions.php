<?php
# Collections functions
# Functions to manipulate collections

if (!function_exists("get_user_collections")){
function get_user_collections($user,$find="",$order_by="name",$sort="ASC",$fetchrows=-1,$auto_create=true)
	{
	# Returns a list of user collections.
	$sql="";
	$keysql="";
	if (strlen($find)==1 && !is_numeric($find))
		{
		# A-Z search
		$sql=" where c.name like '$find%'";
		}
	elseif (strlen($find)>1 || is_numeric($find))
		{  
		$keywords=split_keywords($find);
		$keyrefs=array();
		$keysql="";
		for ($n=0;$n<count($keywords);$n++)
			{
			$keyref=resolve_keyword($keywords[$n],false);
			if ($keyref!==false) {$keyrefs[]=$keyref;}

			$keysql.=" join collection_keyword k" . $n . " on k" . $n . ".collection=ref and (k" . $n . ".keyword='$keyref')";	
			//$keysql="or keyword in (" . join (",",$keyrefs) . ")";
			}

 
		//$sql.="and (c.name rlike '$search' or u.username rlike '$search' or u.fullname rlike '$search' $spcr )";
		}
    
    # Include themes in my collecions? 
    # Only filter out themes if $themes_in_my_collections is set to false in config.php
   	global $themes_in_my_collections;
   	if (!$themes_in_my_collections)
   		{
		if ($sql==""){$sql=" where ";} else {$sql.=" and ";}	
   		$sql.=" (length(c.theme)=0 or c.theme is null) ";
   		}
   
	$order_sort="";
	if ($order_by!="name"){$order_sort=" order by $order_by $sort";}
   
	$return="select * from (select c.*,u.username,u.fullname,count(r.resource) count from user u join collection c on u.ref=c.user and c.user='$user' left outer join collection_resource r on c.ref=r.collection $sql group by c.ref
	union
	select c.*,u.username,u.fullname,count(r.resource) count from user_collection uc join collection c on uc.collection=c.ref and uc.user='$user' and c.user<>'$user' left outer join collection_resource r on c.ref=r.collection left join user u on c.user=u.ref $sql group by c.ref) clist $keysql group by ref $order_sort";

	$return=sql_query($return);
	
	if ($order_by=="name"){
		if ($sort=="ASC"){usort($return, 'collections_comparator');}
		else if ($sort=="DESC"){usort($return,'collections_comparator_desc');}
	}
	
	// To keep My Collection creation consistent: Check that user has at least one collection of his/her own  (not if collection result is empty, which may include shares), 
	$hasown=false;
	for ($n=0;$n<count($return);$n++){
		if ($return[$n]['user']==$user){
			$hasown=true;
		}
	}
	if ($find!=""){$hasown=true;} // if doing a search in collections, assume My Collection already exists (to avoid creating new collections due to an empty search result).

	if (!$hasown && $auto_create)
		{
		# No collections of one's own? The user must have at least one My Collection
		global $usercollection;
		$name=get_mycollection_name($user);
		$usercollection=create_collection ($user,$name,0,1); // make not deletable
		set_user_collection($user,$usercollection);
		
		# Recurse to send the updated collection list.
		return get_user_collections($user,$find,$order_by,$sort,$fetchrows,false);
		}

	return $return;
	}
}	

function get_collection($ref)
	{
	# Returns all data for collection $ref
	$return=sql_query("select c.*,c.theme2,c.theme3,c.keywords,u.fullname,c.home_page_publish,home_page_text,home_page_image from collection c left outer join user u on u.ref=c.user where c.ref='$ref'");
	if (count($return)==0) {return false;} else 
		{
		$return=$return[0];
		$return["users"]=join(", ",sql_array("select u.username value from user u,user_collection c where u.ref=c.user and c.collection='$ref' order by u.username"));
			
		global $userref,$k;
		$request_feedback=0;
		if ($return["user"]!=$userref)
			{
			# If this is not the user's own collection, fetch the user_collection row so that the 'request_feedback' property can be returned.
			$request_feedback=sql_value("select request_feedback value from user_collection where collection='$ref' and user='$userref'",0);
			}
		if ($k!="")
			{
			# If this is an external user (i.e. access key based) then fetch the 'request_feedback' value from the access keys table
			$request_feedback=sql_value("select request_feedback value from external_access_keys where access_key='$k' and request_feedback=1",0);
			}
		
		$return["request_feedback"]=$request_feedback;
		return $return;}
	}

function get_collection_resources($collection)
	{
	# Returns all resources in collection
	# For many cases (e.g. when displaying a collection for a user) a search is used instead so permissions etc. are honoured.
	return sql_array("select resource value from collection_resource where collection='$collection' order by sortorder asc, date_added desc"); 
	}
	
function add_resource_to_collection($resource,$collection,$smartadd=false,$size="")
	{
	global $collection_allow_not_approved_share;
	if (collection_writeable($collection)||$smartadd)
		{	
		# Check if this collection has already been shared externally. If it has, we must fail if not permitted or add a further entry
		# for this specific resource, and warn the user that this has happened.
		$keys=get_collection_external_access($collection);
		if (count($keys)>0)
			{
			$archivestatus=sql_value("select archive as value from resource where ref='$resource'","");
			if ($archivestatus<0 && !$collection_allow_not_approved_share) {global $lang; $lang["cantmodifycollection"]=$lang["notapprovedresources"] . $resource;return false;}

			# Set the flag so a warning appears.
			global $collection_share_warning;
			$collection_share_warning=true;
			
			for ($n=0;$n<count($keys);$n++)
				{
				# Insert a new access key entry for this resource/collection.
				global $userref;
				sql_query("insert into external_access_keys(resource,access_key,user,collection,date,expires,access) values ('$resource','" . escape_check($keys[$n]["access_key"]) . "','$userref','$collection',now()," . ($keys[$n]["expires"]==''?'null':"'" . escape_check($keys[$n]["expires"]) . "'") . ",'" . escape_check($keys[$n]["access"]) . "')");
				#log this
				collection_log($collection,"s",$resource, $keys[$n]["access_key"]);
				}
			
			}
		
		hook("Addtocollectionsuccess", "", array( "resourceId" => $resource, "collectionId" => $collection ) );
		sql_query("delete from collection_resource where resource='$resource' and collection='$collection'");
		sql_query("insert into collection_resource(resource,collection,purchase_size) values ('$resource','$collection','$size')");
		
		#log this
		collection_log($collection,"a",$resource);
		
		

		return true;
		}
	else
		{
		hook("Addtocollectionfail", "", array( "resourceId" => $resource, "collectionId" => $collection ) );
		return false;
		}
	}

function remove_resource_from_collection($resource,$collection,$smartadd=false)
	{
	if (collection_writeable($collection)||$smartadd)
		{	
		hook("Removefromcollectionsuccess", "", array( "resourceId" => $resource, "collectionId" => $collection ) );
		sql_query("delete from collection_resource where resource='$resource' and collection='$collection'");
		sql_query("delete from external_access_keys where resource='$resource' and collection='$collection'");
		
		#log this
		collection_log($collection,"r",$resource);
		return true;
		}
	else
		{
		hook("Removefromcollectionfail", "", array( "resourceId" => $resource, "collectionId" => $collection ) );
		return false;
		}
	}
	
function collection_writeable($collection)
	{
	# Returns true if the current user has write access to the given collection.
	$collectiondata=get_collection($collection);
	global $userref;
	global $allow_smart_collections;
	if ($allow_smart_collections){ 
		if (isset($collectiondata['savedsearch'])&&$collectiondata['savedsearch']!=null){
			return false; // so "you cannot modify this collection"
			}
	}
	return $userref==$collectiondata["user"] || $collectiondata["allow_changes"]==1 || checkperm("h");
	}
	
function set_user_collection($user,$collection)
	{
	global $usercollection;
	sql_query("update user set current_collection='$collection' where ref='$user'");
	$usercollection=$collection;
	}
	
if (!function_exists("create_collection")){	
function create_collection($userid,$name,$allowchanges=0,$cant_delete=0)
	{
	# Creates a new collection and returns the reference
	sql_query("insert into collection (name,user,created,allow_changes,cant_delete) values ('" . escape_check($name) . "','$userid',now(),'$allowchanges','$cant_delete')");
	$ref=sql_insert_id();

	index_collection($ref);
	
	
	return $ref;
	}	
}
	
function delete_collection($ref)
	{
	# Deletes the collection with reference $ref
	hook("beforedeletecollection");
	sql_query("delete from collection where ref='$ref'");
	sql_query("delete from collection_resource where collection='$ref'");
	sql_query("delete from collection_keyword where collection='$ref'");
		#log this
	collection_log($ref,"X",0, "");
	}
	
function refresh_collection_frame($collection="")
    {
    # Refresh the CollectionDiv
    global $baseurl, $headerinsert;

    if (getvalescaped("ajax",false))
	{
	echo "<script  type=\"text/javascript\">
	CollectionDivLoad(\"" . $baseurl . "/pages/collections.php" . ((getval("k","")!="")?"?collection=" . urlencode(getval("collection",$collection)) . "&k=" . urlencode(getval("k","")) . "&":"?") . "nc=" . time() . "\");	
	</script>";
	}
    else
	{
	$headerinsert.="<script  type=\"text/javascript\">
	CollectionDivLoad(\"" . $baseurl . "/pages/collections.php" . ((getval("k","")!="")?"?collection=" . urlencode(getval("collection",$collection)) . "&k=" . urlencode(getval("k","")) . "&":"?") . "nc=" . time() . "\");
	</script>";
	}

    }

if (!function_exists("search_public_collections")){	
function search_public_collections($search="", $order_by="name", $sort="ASC", $exclude_themes=true, $exclude_public=false, $include_resources=false, $override_group_restrict=false, $search_user_collections=false)
	{
	global $userref;

	# Performs a search for themes / public collections.
	# Returns a comma separated list of resource refs in each collection, used for thumbnail previews.
	$sql="";
	$keysql="";
	# Keywords searching?
	$keywords=split_keywords($search);  
	if (strlen($search)==1 && !is_numeric($search)) 
		{
		# A-Z search
		$sql="and c.name like '$search%'";
		}
	elseif (substr($search,0,16)=="collectiontitle:")
	    {
	    # A-Z specific title search
	    
	    $newsearch="";
	    for ($n=0;$n<count($keywords);$n++)
	    	{
	    	   if (substr($keywords[$n],0,16)=="collectiontitle:") $newsearch.=" ".substr($keywords[$n],16);    // wildcard * - %
	    	}
	    	if (strpos($newsearch,"*")===false) $newsearch.="%";
	    	else $newsearch=str_replace("*", "%", $newsearch);
	    	$newsearch=trim($newsearch);
	    	$sql="and c.name like '$newsearch'";
	    	
	    }
	if (strlen($search)>1 || is_numeric($search))
		{  
		
		$keyrefs=array();
		for ($n=0;$n<count($keywords);$n++)
			{
			if (substr($keywords[$n],0,16)!="collectiontitle:")
    		    {
    		    if (substr($keywords[$n],0,16)=="collectionowner:") 
    		        {
    			    $keywords[$n]=substr($keywords[$n],16);
	    		    $keyref=$keywords[$n];
                       $sql.=" and (u.username rlike '$keyref' or u.fullname rlike '$keyref')";	
                    }
                elseif (substr($keywords[$n],0,19)=="collectionownerref:") 
                    {
                    $keywords[$n]=substr($keywords[$n],19);
                    $keyref=$keywords[$n];
                       $sql.=" and (c.user='$keyref')";
                    } 
                else
                    {
                    if (substr($keywords[$n],0,19)=="collectionkeywords:") $keywords[$n]=substr($keywords[$n],19);
                    $keyref=resolve_keyword($keywords[$n],false);
                    if ($keyref!==false) {$keyrefs[]=$keyref;}
                    $keysql.="join collection_keyword k" . $n . " on k" . $n . ".collection=c.ref and (k" . $n . ".keyword='$keyref')";	
                    }
			    //$keysql="or keyword in (" . join (",",$keyrefs) . ")";
			    }
			}
        
        global $search_public_collections_ref;
        if ($search_public_collections_ref && is_numeric($search)){$spcr="or c.ref='$search'";} else {$spcr="";}    
		//$sql.="and (c.name rlike '$search' or u.username rlike '$search' or u.fullname rlike '$search' $spcr )";
		}

	if ($exclude_themes) # Include only public collections.
		{
		$sql.=" and (length(c.theme)=0 or c.theme is null)";
		}
	
	if (($exclude_public) && !$search_user_collections) # Exclude public only collections (return only themes)
		{
		$sql.=" and length(c.theme)>0";
		}
	
	# Restrict to parent, child and sibling groups?
	global $public_collections_confine_group,$userref,$usergroup;
	if ($public_collections_confine_group && !$override_group_restrict)
		{
		# Form a list of all applicable groups
		$groups=array($usergroup); # Start with user's own group
		$groups=array_merge($groups,sql_array("select ref value from usergroup where parent='$usergroup'")); # Children
		$groups=array_merge($groups,sql_array("select parent value from usergroup where ref='$usergroup'")); # Parent
		$groups=array_merge($groups,sql_array("select ref value from usergroup where parent<>0 and parent=(select parent from usergroup where ref='$usergroup')")); # Siblings (same parent)
		
		$sql.=" and u.usergroup in ('" . join ("','",$groups) . "')";
		}
	
	if ($search_user_collections) $sql_public="(c.public=1 or c.user=$userref)";
	else $sql_public="c.public=1";

	# Run the query
	if ($include_resources)
		{    
		 debug("select distinct c.*,u.username,u.fullname, group_concat(distinct cr.resource order by cr.rating desc,cr.date_added) resources, count( DISTINCT cr.resource ) count from collection c left join collection_resource cr on c.ref=cr.collection left outer join user u on c.user=u.ref left outer join collection_keyword k on c.ref=k.collection $keysql where $sql_public $sql group by c.ref order by $order_by $sort");
            return sql_query("select distinct c.*,u.username,u.fullname, group_concat(distinct cr.resource order by cr.rating desc,cr.date_added) resources, count( DISTINCT cr.resource ) count from collection c left join collection_resource cr on c.ref=cr.collection left outer join user u on c.user=u.ref left outer join collection_keyword k on c.ref=k.collection $keysql where $sql_public $sql group by c.ref order by $order_by $sort");
           
		}
	else
		{
		    return sql_query("select distinct c.*,u.username,u.fullname from collection c left outer join user u on c.user=u.ref left outer join collection_keyword k on c.ref=k.collection $keysql where $sql_public $sql group by c.ref order by $order_by $sort");
		    debug("restypes select distinct c.*,u.username,u.fullname from collection c left outer join user u on c.user=u.ref left outer join collection_keyword k on c.ref=k.collection $keysql where $sql_public $sql group by c.ref order by $order_by $sort");
		}
	}
}


function do_collections_search($search,$restypes,$archive=0)
    {
    global $search_includes_themes, $search_includes_public_collections, $search_includes_user_collections, $userref;
    $result=array();
    
    # Recognise a quoted search, which is a search for an exact string
    $quoted_string=false;
    if (substr($search,0,1)=="\"" && substr($search,-1,1)=="\"") 
        {
        $quoted_string=true;
        $search=substr($search,1,-1);
        } 
    $search_includes_themes_now=$search_includes_themes;
    $search_includes_public_collections_now=$search_includes_public_collections;
    $search_includes_user_collections_now=$search_includes_user_collections;
    if ($restypes!="") 
        {
        $restypes_x=explode(",",$restypes);
        $search_includes_themes_now=in_array("themes",$restypes_x);
        $search_includes_public_collections_now=in_array("pubcol",$restypes_x);
        $search_includes_user_collections_now=in_array("mycol",$restypes_x);
        } 

    if ($search_includes_themes_now || $search_includes_public_collections_now || $search_includes_user_collections_now)
        {
        
        $collections=search_public_collections($search,"theme","ASC",!$search_includes_themes_now,!$search_includes_public_collections_now,true,false, $search_includes_user_collections_now);
        $condensedcollectionsresults=array();
        $result=$collections;

    	}
       
    
    		
    return $result;
    }



function add_collection($user,$collection)
	{
	# Add a collection to a user's 'My Collections'
	
	# Remove any existing collection first
	remove_collection($user,$collection);
	# Insert row
	sql_query("insert into user_collection(user,collection) values ('$user','$collection')");
			#log this
	collection_log($collection,"S",0, sql_value ("select username as value from user where ref = $user",""));

	}

function remove_collection($user,$collection)
	{
	# Remove someone else's collection from a user's My Collections
	sql_query("delete from user_collection where user='$user' and collection='$collection'");
			#log this
	collection_log($collection,"T",0, sql_value ("select username as value from user where ref = $user",""));
	}

if (!function_exists("index_collection")){
function index_collection($ref,$index_string='')
	{
	# Update the keywords index for this collection
	sql_query("delete from collection_keyword where collection='$ref'"); # Remove existing keywords
	# Define an indexable string from the name, themes and keywords.

	global $index_collection_titles;

	if ($index_collection_titles)
		{
			$indexfields = 'c.ref,c.name,c.keywords';
		} else {
			$indexfields = 'c.ref,c.keywords';
		}
	global $index_collection_creator;
	if ($index_collection_creator)
		{
			$indexfields .= ',u.fullname';
		} 
		
	
	// if an index string wasn't supplied, generate one
	if (!strlen($index_string) > 0){
		$indexarray = sql_query("select $indexfields from collection c join user u on u.ref=c.user and c.ref = '$ref'");
		for ($i=0; $i<count($indexarray); $i++){
			$index_string = implode(' ',$indexarray[$i]);
		} 
	}

	$keywords=split_keywords($index_string,true);
	for ($n=0;$n<count($keywords);$n++)
		{
		$keyref=resolve_keyword($keywords[$n],true);
		sql_query("insert into collection_keyword values ('$ref','$keyref')");
		}
	// return the number of keywords indexed
	return $n;
	}
}

function save_collection($ref)
	{
	global $theme_category_levels;
	
	
	$allow_changes=(getval("allow_changes","")!=""?1:0);
	
	# Next line disabled as it seems incorrect to override the user's setting here. 20071217 DH.
	#if ($theme!="") {$allow_changes=0;} # lock allow changes to off if this is a theme
	
	# Update collection with submitted form data
	if (!hook('modifysavecollection')) {
	$sql="update collection set
				name='" . getvalescaped("name","") . "',
				".hook('savecollectionadditionalfields')."
				keywords='" . getvalescaped("keywords","") . "',
				public='" . getvalescaped("public","",true) . "',";
		
		for($n=1;$n<=$theme_category_levels;$n++){
			if ($n==1){$themeindex="";} else {$themeindex=$n;}
			$themes[$n]=getvalescaped("theme$themeindex","");
			if (getval("newtheme$themeindex","")!="") {
				$themes[$n]=trim(getvalescaped("newtheme$themeindex",""));
				}
			if (isset($themes[$n])){
				$sql.="theme".$themeindex."='" . $themes[$n]. "',";
				}
		}

	$sql.="allow_changes='" . $allow_changes . "'";
	
	if (checkperm("h"))
		{	
		$sql.="
			,home_page_publish='" . (getvalescaped("home_page_publish","")!=""?"1":"0") . "'
			,home_page_text='" . getvalescaped("home_page_text","") . "'";
		if (getval("home_page_image","")!="")
			{
			$sql.=",home_page_image='" . getvalescaped("home_page_image","") . "'";
			}
		}
		
	    $sql.=" where ref='$ref'";

	sql_query($sql);
	} # end replace hook - modifysavecollection
	
	index_collection($ref);
		
	# If 'users' is specified (i.e. access is private) then rebuild users list
	$users=getvalescaped("users",false);
	if ($users!==false)
		{
		sql_query("delete from user_collection where collection='$ref'");
		#log this
		collection_log($ref,"T",0, '#all_users');

		if (($users)!="")
			{
			# Build a new list and insert
			$users=resolve_userlist_groups($users);
			$ulist=array_unique(trim_array(explode(",",$users)));
			$urefs=sql_array("select ref value from user where username in ('" . join("','",$ulist) . "')");
			if (count($urefs)>0)
				{
				sql_query("insert into user_collection(collection,user) values ($ref," . join("),(" . $ref . ",",$urefs) . ")");
				}
			#log this
			collection_log($ref,"S",0, join(", ",$ulist));
			}
		}
		
	# Relate all resources?
	if (getval("relateall","")!="")
		{
		$rlist=get_collection_resources($ref);
		for ($n=0;$n<count($rlist);$n++)
			{
			for ($m=0;$m<count($rlist);$m++)
				{
				if ($rlist[$n]!=$rlist[$m]) # Don't relate a resource to itself
					{
					sql_query("delete from resource_related where resource='" . $rlist[$n] . "' and related='" . $rlist[$m] . "'");
					sql_query("insert into resource_related (resource,related) values ('" . $rlist[$n] . "','" . $rlist[$m] . "')");
					}
				}
			}
		}
	
	
	# Remove all resources?
	if (getval("removeall","")!="")
		{
		remove_all_resources_from_collection($ref);
		}
		
	# Delete all resources?
	if (getval("deleteall","")!="" && !checkperm("D"))
		{
		$resources=do_search("!collection" . $ref);
		for ($n=0;$n<count($resources);$n++)
			{
			if (get_edit_access($resources[$n]["ref"]))
				{
				delete_resource($resources[$n]["ref"]);	
				collection_log($ref,"D",$resources[$n]["ref"]);
				}
			}
		}
		
	# Update limit count for saved search
	if (isset($_POST["result_limit"]))
		{
		sql_query("update collection_savedsearch set result_limit='" . getvalescaped("result_limit","") . "' where collection='$ref'");
		
		}
	
	refresh_collection_frame();
	}

function get_max_theme_levels(){
	// return the maximum number of theme category levels (columns) present in the collection table
	$sql = "show columns from collection like 'theme%'";
	$results = sql_query($sql);
	foreach($results as $result) {
		if ($result['Field'] == 'theme'){
			$level = 1;
		} else {
			$thislevel = substr($result['Field'],5);
			if (is_numeric($thislevel) && $thislevel > $level){
				$level = $thislevel;
			}
		}
	}
	return $level;
}

function get_theme_headers($themes=array())
	{
	# Return a list of theme headers, i.e. theme categories
	#return sql_array("select theme value,count(*) c from collection where public=1 and length(theme)>0 group by theme order by theme");
	# Work out which theme category level we are selecting based on the higher selected levels provided.
	$selecting="theme";

	$theme_path = "";	
	$sql="";	
	for ($x=0;$x<count($themes);$x++){		
		if ($x>0) $theme_path .= "|";		
		$theme_path .= $themes[$x];		
		if (isset($themes[$x])){
			$selecting="theme".($x+2);
		}		
		if (isset($themes[$x]) && $themes[$x]!="" && $x==0) {
			$sql.=" and theme='" . escape_check($themes[$x]) . "'";
		}
		else if (isset($themes[$x])&& $themes[$x]!=""&& $x!=0) {
			$sql.=" and theme".($x+1)."='" . escape_check($themes[$x]) . "'";
		}
	}	
	$return=array();
	$themes=sql_query("select * from collection where public=1 and $selecting is not null and length($selecting)>0 $sql");
	for ($n=0;$n<count($themes);$n++)
		{		
		if (
				(!in_array($themes[$n][$selecting],$return)) &&					# de-duplicate as there are multiple collections per theme category				
				(checkperm("j*") || checkperm("j" . $themes[$n]["theme"])) &&	# and we have permission to access then add to array							
				(!checkperm ("j-${theme_path}|" . $themes[$n][$selecting]))		# path must not be in j-<path> exclusion				
			) 
			{											
				$return[]=$themes[$n][$selecting];
			}
		}
	usort($return,"themes_comparator");	
	return $return;
	}
	
function themes_comparator($a, $b)
	{
	return strnatcasecmp(i18n_get_collection_name($a), i18n_get_collection_name($b));
	}

function collections_comparator($a, $b)
	{
	return strnatcasecmp(i18n_get_collection_name($a), i18n_get_collection_name($b));
	}

function collections_comparator_desc($a, $b)
	{
	return strnatcasecmp(i18n_get_collection_name($b), i18n_get_collection_name($a));
	}		

if (!function_exists("get_themes")){
function get_themes($themes=array(""),$subthemes=false)
	{	
	$themes_order_by=getvalescaped("themes_order_by",getvalescaped("saved_themes_order_by","name"));
	$sort=getvalescaped("sort",getvalescaped("saved_themes_sort","ASC"));	
	global $themes_column_sorting;
	if (!$themes_column_sorting){$themes_order_by="name";$sort="ASC";} // necessary to avoid using a cookie that can't be changed if this is turned off.
		
	# Return a list of themes under a given header (theme category).
	$sql="select *,(select count(*) from collection_resource cr where cr.collection=c.ref) c from collection c  where c.theme='" . escape_check($themes[0]) . "' ";
	
	for ($x=1;$x<count($themes)+1;$x++){
		if (isset($themes[$x])&&$themes[$x]!=""){
			$sql.=" and theme".($x+1)."='" . escape_check($themes[$x]) . "' ";
		}
		else {
			global $theme_category_levels;
			if (($x+1)<=$theme_category_levels && !$subthemes){
			$sql.=" and (theme".($x+1)."='' or theme".($x+1)." is null) ";
			}
		}
	}

	$order_sort="";
	if ($themes_order_by!="name"){$order_sort=" order by $themes_order_by $sort";}
	$sql.=" and c.public=1    $order_sort;";

	$collections=sql_query($sql);
	if ($themes_order_by=="name"){
		if ($sort=="ASC"){usort($collections, 'collections_comparator');}
		else if ($sort=="DESC"){usort($collections,'collections_comparator_desc');}
	}
	
	return $collections;
	}
}

function get_smart_theme_headers()
	{
	# Returns a list of smart theme headers, which are basically fields with a 'smart theme name' set.
	return sql_query("select ref,name,smart_theme_name,type from resource_type_field where length(smart_theme_name)>0 order by smart_theme_name");
	}

if (!function_exists("get_smart_themes")){	
function get_smart_themes($field,$node=0,$themebar=false)
	{
	# Returns a list of smart themes (which are really field options).
	# The results are filtered so that only field options that are in use are returned.
	
	# Fetch field info
	$fielddata=sql_query("select * from resource_type_field where ref='$field'");
	if (count($fielddata)>0) {$fielddata=$fielddata[0];} else {return false;}
	
	# Return a list of keywords that are in use for this field
    global $smart_themes_omit_archived;
	$inuse=sql_array("select distinct k.keyword value from keyword k join resource_keyword rk on k.ref=rk.keyword ".(($smart_themes_omit_archived)?"join resource r on rk.resource=r.ref":"")." where resource_type_field='$field' and resource>0 ".(($smart_themes_omit_archived)?"and archive=0":""));

	if ($fielddata["type"]==7)
		{
		# Category tree style view
		$tree=explode("\n",$fielddata["options"]);

		$return=array();	
		
		global $themes_category_split_pages;
		if ($themes_category_split_pages && !$themebar)
			{
			# Return one level only, unless grabbing for themebar
			$levels=1;
			}
		else
			{
			# Return an infinite number of levels
			$levels=-1;
			}
		$return=populate_smart_theme_tree_node($tree,$node,$return,0,$levels);
		
		# For each option, if it is in use, add it to the return list.
		$out=array();
		for ($n=0;$n<count($return);$n++)
			{
			# Prepare a 'tidied' local language version of the name to use for the comparison
			# Only return items that are in use.
			$tidy=escape_check(cleanse_string(trim(strtolower(str_replace("-"," ",htmlspecialchars_decode(i18n_get_collection_name($return[$n]))))),false));
			if (in_array($tidy,$inuse))
				{
				$c=count($out);
				$out[$c]["indent"]=$return[$n]["indent"];
				$out[$c]["name"]=trim(htmlspecialchars_decode(i18n_get_collection_name($return[$n])));
				$out[$c]["node"]=$return[$n]["node"];
				$out[$c]["children"]=$return[$n]["children"];
				}
			}
		return $out;
		}
	else
		{
		# Standard checkbox list or drop-down box
		
		# Fetch raw options list
		$options=explode(",",$fielddata["options"]);
		
		# Tidy list so it matches the storage format used for keywords.
		# The translated version is fetched as each option will be indexed in the local language version of each option.
		$options_base=array();
		for ($n=0;$n<count($options);$n++) {$options_base[$n]=escape_check(trim(strtolower(i18n_get_translated($options[$n]))));}
		
		# For each option, if it is in use, add it to the return list.
		$return=array();
		for ($n=0;$n<count($options);$n++)
			{
			#echo "<li>Looking for " . $options_base[$n] . " in " . join (",",$inuse);
			if (in_array(str_replace("-"," ",$options_base[$n]),$inuse)) 		
				{
				$c=count($return);
				$return[$c]["name"]=trim(i18n_get_translated($options[$n]));
				$return[$c]["indent"]=0;
				$return[$c]["node"]=0;
				$return[$c]["children"]=0;
				}
			}
		return $return;
		}
	}
}

function populate_smart_theme_tree_node($tree,$node,$return,$indent,$levels)
	{
	
	# When displaying category trees as smart themes, this function is used to recursively
	# parse each node adding items sequentially with an appropriate indent level.
	for ($n=0;$n<count($tree);$n++)
		{
		$s=explode(",",$tree[$n]);
		if (isset($s[1]) && $s[1]==$node)
			{
			# Add this node
			$c=count($return);
			$return[$c]["indent"]=$indent;
			$return[$c]["name"]=$s[2];
			$return[$c]["node"]=$n+1;
			
			# Add child count
			$children=populate_smart_theme_tree_node($tree,$n+1,array(),0,1);
			$return[$c]["children"]=count($children);
			
			if ($levels>0) {$levels--;}
			if ($levels>0 || $levels==-1)
				{
				# Cascade
				$return=populate_smart_theme_tree_node($tree,$n+1,$return,$indent+1,$levels);
				}
			}
		}
	return $return;
	}

if (!function_exists("email_collection")){
function email_collection($colrefs,$collectionname,$fromusername,$userlist,$message,$feedback,$access=-1,$expires="",$useremail="",$from_name="",$cc="",$themeshare=false,$themename="",$themeurlsuffix="")
	{
	# Attempt to resolve all users in the string $userlist to user references.
	# Add $collection to these user's 'My Collections' page
	# Send them an e-mail linking to this collection
	#  handle multiple collections (comma seperated list)
	global $baseurl,$email_from,$applicationname,$lang,$userref, $email_multi_collections ;
	
	if ($useremail==""){$useremail=$email_from;}
	
	if (trim($userlist)=="") {return ($lang["mustspecifyoneusername"]);}
	$userlist=resolve_userlist_groups($userlist);
	$ulist=trim_array(explode(",",$userlist));
	$emails=array();
	$key_required=array();
	if ($feedback) {$feedback=1;} else {$feedback=0;}
	$reflist=trim_array(explode(",",$colrefs));
	
	$emails_keys=resolve_user_emails($ulist);
	$emails=$emails_keys['emails'];
	$key_required=$emails_keys['key_required'];

	# Add the collection(s) to the user's My Collections page
	$urefs=sql_array("select ref value from user where username in ('" . join("','",$ulist) . "')");
	if (count($urefs)>0)
		{
		# Delete any existing collection entries
		sql_query("delete from user_collection where collection in ('" .join("','", $reflist) . "') and user in ('" . join("','",$urefs) . "')");
		
		# Insert new user_collection row(s)
		#loop through the collections
			for ($nx1=0;$nx1<count($reflist);$nx1++)
			{	#loop through the users
				for ($nx2=0;$nx2<count($urefs);$nx2++)
				{
		sql_query("insert into user_collection(collection,user,request_feedback) values ($reflist[$nx1], $urefs[$nx2], $feedback )");
		if ($access == 0) {
			foreach (get_collection_resources($reflist[$nx1]) as $resource)	{
				open_access_to_user($urefs[$nx2],$resource,$expires);
			}
		}
				
			#log this
		collection_log($reflist[$nx1],"S",0, sql_value ("select username as value from user where ref = $urefs[$nx2]",""));

				}
			}
		}
	
	# Send an e-mail to each resolved user
	
	# htmlbreak is for composing list
	$htmlbreak="\r\n";
	global $use_phpmailer;
	if ($use_phpmailer){$htmlbreak="<br><br>";$htmlbreaksingle="<br>";} 
	
	if ($fromusername==""){$fromusername=$applicationname;} // fromusername is used for describing the sender's name inside the email
	if ($from_name==""){$from_name=$applicationname;} // from_name is for the email headers, and needs to match the email address (app name or user name)
	
	$templatevars['message']=str_replace(array("\\n","\\r","\\"),array("\n","\r",""),$message);	
	if (trim($templatevars['message'])==""){$templatevars['message']=$lang['nomessage'];} 
	
	$templatevars['fromusername']=$fromusername;
	$templatevars['from_name']=$from_name;
	
	if(count($reflist)>1){$subject=$applicationname.": ".$lang['mycollections'];}
	else { $subject=$applicationname.": ".$collectionname;}
	
	if ($fromusername==""){$fromusername=$applicationname;}
	
	$externalmessage=$lang["emailcollectionmessageexternal"];
	$internalmessage=$lang["emailcollectionmessage"];
	$viewlinktext=$lang["clicklinkviewcollection"];
	if ($themeshare) // Change the text if sharing a theme category
		{
		$externalmessage=$lang["emailthemecollectionmessageexternal"];
		$internalmessage=$lang["emailthememessage"];
		$viewlinktext=$lang["clicklinkviewcollections"];
		}
		
	##  loop through recipients
	for ($nx1=0;$nx1<count($emails);$nx1++)
		{
		## loop through collections
		$list="";
		$list2="";
		$origviewlinktext=$viewlinktext; // Save this text as we may change it for internal theme shares for this user
		if ($themeshare && !$key_required[$nx1]) # don't send a whole list of collections if internal, just send the theme category URL
			{
			$url="";
			$subject=$applicationname.": " . $themename;
			$url=$baseurl . "/pages/themes.php" . $themeurlsuffix;			
			$viewlinktext=$lang["clicklinkviewthemes"];
			$emailcollectionmessageexternal=false;
			if ($use_phpmailer){
					$link="<a href=\"$url\">" . $themename . "</a>";	
					
					$list.= $htmlbreak.$link;	
					// alternate list style				
					$list2.=$htmlbreak.$themename.' -'.$htmlbreaksingle.$url;
					$templatevars['list2']=$list2;					
					}
				else
					{
					$list.= $htmlbreak.$url;
					}
			for ($nx2=0;$nx2<count($reflist);$nx2++)
				{				
				#log this
				collection_log($reflist[$nx2],"E",0, $emails[$nx1]);
				}
			
			}
		else
			{
			for ($nx2=0;$nx2<count($reflist);$nx2++)
				{
				$url="";
				$key="";
				$emailcollectionmessageexternal=false;
				# Do we need to add an external access key for this user (e-mail specified rather than username)?
				if ($key_required[$nx1])
					{
					$k=generate_collection_access_key($reflist[$nx2],$feedback,$emails[$nx1],$access,$expires);
					$key="&k=". $k;
					$emailcollectionmessageexternal=true;
					}
				$url=$baseurl . 	"/?c=" . $reflist[$nx2] . $key;		
				$collection = array();
				$collection = sql_query("select name,savedsearch from collection where ref='$reflist[$nx2]'");
				if ($collection[0]["name"]!="") {$collection_name = i18n_get_collection_name($collection[0]);}
				else {$collection_name = $reflist[$nx2];}
				if ($use_phpmailer){
					$link="<a href=\"$url\">$collection_name</a>";	
					$list.= $htmlbreak.$link;	
					// alternate list style				
					$list2.=$htmlbreak.$collection_name.' -'.$htmlbreaksingle.$url;
					$templatevars['list2']=$list2;					
					}
				else
					{
					$list.= $htmlbreak . $collection_name . $htmlbreak . $url . $htmlbreak;
					}
				#log this
				collection_log($reflist[$nx2],"E",0, $emails[$nx1]);
				}
			}
		//$list.=$htmlbreak;	
		$templatevars['list']=$list;
		$templatevars['from_name']=$from_name;
		if ($emailcollectionmessageexternal ){
			$template=($themeshare)?"emailthemeexternal":"emailcollectionexternal";
		}
		else {
			$template=($themeshare)?"emailtheme":"emailcollection";
		}
		$body=$templatevars['fromusername']." " . (($emailcollectionmessageexternal)?$externalmessage:$internalmessage) . "\n\n" . $templatevars['message']."\n\n" . $viewlinktext ."\n\n".$templatevars['list'];
		#exit ($body . "<br>" . $viewlinktext);	
		send_mail($emails[$nx1],$subject,$body,$fromusername,$useremail,$template,$templatevars,$from_name,$cc);
		$viewlinktext=$origviewlinktext;
		}
		
	# Return an empty string (all OK).
	return "";
	}
}	


function generate_collection_access_key($collection,$feedback=0,$email="",$access=-1,$expires="")
	{
	# For each resource in the collection, create an access key so an external user can access each resource.
	global $userref;
	$k=substr(md5($collection . "," . time()),0,10);
	$r=get_collection_resources($collection);
	for ($m=0;$m<count($r);$m++)
		{
		# Add the key to each resource in the collection
		sql_query("insert into external_access_keys(resource,access_key,collection,user,request_feedback,email,date,access,expires) values ('" . $r[$m] . "','$k','$collection','$userref','$feedback','" . escape_check($email) . "',now(),$access," . (($expires=="")?"null":"'" . $expires . "'"). ");");
		}
		
	hook("generate_collection_access_key","",array($collection,$k,$userref,$feedback,$email,$access,$expires));
	return $k;
	}
	
function get_saved_searches($collection)
	{
	return sql_query("select * from collection_savedsearch where collection='$collection' order by created");
	}

function add_saved_search($collection)
	{
	sql_query("insert into collection_savedsearch(collection,search,restypes,archive) values ('$collection','" . getvalescaped("addsearch","") . "','" . getvalescaped("restypes","") . "','" . getvalescaped("archive","",true) . "')");
	}

function remove_saved_search($collection,$search)
	{
	sql_query("delete from collection_savedsearch where collection='$collection' and ref='$search'");
	}

function add_smart_collection()
 	{
	global $userref;

	$search=getvalescaped("addsmartcollection","");
	$restypes=getvalescaped("restypes","");
	$archive=getvalescaped("archive","",true);
	$starsearch=getvalescaped("starsearch",0);
	
	// more compact search strings should work with get_search_title
	$searchstring=array();
	if ($search!=""){$searchstring[]="search=$search";}
	if ($restypes!=""){$searchstring[]="restypes=$restypes";}
	if ($starsearch!=""){$searchstring[]="starsearch=$starsearch";}
	if ($archive!=0){$searchstring[]="archive=$archive";}
	$searchstring=implode("&",$searchstring);
	
	if ($starsearch==""){$starsearch=0;}
	$newcollection=create_collection($userref,get_search_title($searchstring),1);	

	sql_query("insert into collection_savedsearch(collection,search,restypes,archive,starsearch) values ('$newcollection','" . $search . "','" . $restypes . "','" . $archive . "','".$starsearch."')");
	$savedsearch=sql_insert_id();
	sql_query("update collection set savedsearch='$savedsearch' where ref='$newcollection'"); 
	set_user_collection($userref,$newcollection);
	}

function get_search_title($searchstring){
	// for naming smart collections, takes a full searchstring with the form 'search=restypes=archive=starsearch=' (all parameters optional)
	// and uses search_title_processing to autocreate a more informative title 
	$order_by="";
	$sort="";
	$offset="";
	$k=getvalescaped("k","");
	
	$search_titles=true;
	$search_titles_searchcrumbs=true;
	$use_refine_searchstring=true;
	$search_titles_shortnames=false;
	
	global $lang,$userref,$baseurl,$collectiondata,$result,$display,$pagename,$collection,$userrequestmode,$preview_all;
	
	parse_str($searchstring,$searchvars);
	if (isset($searchvars["archive"])){$archive=$searchvars["archive"];}else{$archive=0;}
	if (isset($searchvars["search"])){$search=$searchvars["search"];}else{$search="";}
	if (isset($searchvars["starsearch"])){$starsearch=$searchvars["starsearch"];}else{$starsearch="";}
	if (isset($searchvars["restypes"])){$restypes=$searchvars["restypes"];}else{$restypes="";}

	$collection_dropdown_user_access_mode=false;
	include(dirname(__FILE__)."/search_title_processing.php");

    if ($starsearch!=0){$search_title.="(".$starsearch;$search_title.=($starsearch>1)?" ".$lang['stars']:" ".$lang['star'];$search_title.=")";}
    if ($restypes!=""){ 
		$resource_types=get_resource_types($restypes);
		foreach($resource_types as $type){
			$typenames[]=$type['name'];
		}
		$search_title.=" [".implode(', ',$typenames)."]";
	}
	$title=str_replace(">","",strip_tags($search_title));
	return $title;
}

function add_saved_search_items($collection)
	{
	# Adds resources from a search to the collection.
	$results=do_search(getvalescaped("addsearch",""), getvalescaped("restypes",""), "relevance", getvalescaped("archive","",true),-1,'',false,'',false,false,getvalescaped("daylimit",""));
	
	# Check if this collection has already been shared externally. If it has, we must add a further entry
	# for this specific resource, and warn the user that this has happened.
	$keys=get_collection_external_access($collection);
	$resourcesnotadded=array(); # record the resources that are not added so we can display to the user
	if (count($keys)>0)
		{
		# Set the flag so a warning appears.
		global $collection_share_warning, $collection_allow_not_approved_share;
		$collection_share_warning=true;
		
		for ($n=0;$n<count($keys);$n++)
			{
			# Insert a new access key entry for this resource/collection.
			global $userref;
			
			for ($r=0;$r<count($results);$r++)
				{
				$resource=$results[$r]["ref"];
				$archivestatus=$results[$r]["archive"];
				if ($archivestatus<0 && !$collection_allow_not_approved_share) {array_push($resourcesnotadded,$resource);continue;}
				sql_query("insert into external_access_keys(resource,access_key,user,collection,date) values ('$resource','" . escape_check($keys[$n]["access_key"]) . "','$userref','$collection',now())");
				#log this
				collection_log($collection,"s",$resource, $keys[$n]["access_key"]);
				}
			}
		}

	if (is_array($results))
                {
                for ($n=0;$n<count($results);$n++)
                        {
                        $resource=$results[$n]["ref"];
       			if (!in_array($resource,$resourcesnotadded))
				{
				sql_query("delete from collection_resource where resource='$resource' and collection='$collection'");
				sql_query("insert into collection_resource(resource,collection) values ('$resource','$collection')");
				}
                        }
                }
	return $resourcesnotadded;
	}

if (!function_exists("allow_multi_edit")){
function allow_multi_edit($collection)
	{
	global $resource;
	# Returns true or false, can all resources in this collection be edited by the user?
	# also applies edit filter, since it uses get_resource_access

	if (!is_array($collection)){ // collection is an array of resource data
		$collection=do_search("!collection" . $collection);

	}
	for ($n=0;$n<count($collection);$n++){
		$resource = $collection[$n];
		if (!get_edit_access($collection[$n]["ref"],$collection[$n]["archive"],false,$collection[$n])){return false;}
		
	}

	if(hook('denyaftermultiedit', '', array($collection))) { return false; }

	return true;
	
	# Updated: 2008-01-21: Edit all now supports multiple types, so always return true.
	/*
	$types=sql_query("select distinct r.resource_type from collection_resource c left join resource r on c.resource=r.ref where c.collection='$collection'");
	if (count($types)!=1) {return false;}
	
	$status=sql_query("select distinct r.archive from collection_resource c left join resource r on c.resource=r.ref where c.collection='$collection'");
	if (count($status)!=1) {return false;}	
	
	return true;
	*/
	}
}	

function get_theme_image($themes=array())
	{
	# Returns an array of resource references that can be used as theme category images.
	global $theme_images_number;
	global $theme_category_levels;
	# First try to find resources that have been specifically chosen using the option on the collection comments page.
	$sql="select r.ref value from collection c join collection_resource cr on c.ref=cr.collection join resource r on cr.resource=r.ref where c.theme='" . escape_check($themes[0]) . "' ";
	for ($n=2;$n<=count($themes)+1;$n++){
		if (isset($themes[$n-1])){
			$sql.=" and theme".$n."='" . escape_check($themes[$n-1]) . "' ";
		} 
		else {
			if ($n<=$theme_category_levels){
				$sql.=" and (theme".$n."='' or theme".$n." is null) ";
			}
		}
	} 

	$sql.=" and r.has_image=1 and cr.use_as_theme_thumbnail=1 order by r.ref desc";
	$chosen=sql_array($sql,0);
	if (count($chosen)>0) {return $chosen;}
	
	# No chosen images? Manually choose a single image based on hit counts.
	$sql="select r.ref value from collection c join collection_resource cr on c.ref=cr.collection join resource r on cr.resource=r.ref where c.theme='" . escape_check($themes[0]) . "' ";
	for ($n=2;$n<=count($themes)+1;$n++){
		if (isset($themes[$n-1])){
			$sql.=" and theme".$n."='" . escape_check($themes[$n-1]) . "' ";
		} 
		else {
			if ($n<=$theme_category_levels){
			$sql.=" and (theme".$n."='' or theme".$n." is null) ";
			}
		}
	} 
	$sql.=" and r.has_image=1 order by r.hit_count desc limit " . $theme_images_number;
	$images=sql_array($sql,0);

	$tmp = hook("getthemeimage", "", array($themes)); if($tmp!==false and is_array($tmp) and count($tmp)>0) $images = $tmp;

	if (count($images)>0) {return $images;}
	return false;
	}

function swap_collection_order($resource1,$resource2,$collection)
	{
	# Inserts $resource1 into the position currently occupied by $resource2 

	// sanity check -- we should only be getting IDs here
	if (!is_numeric($resource1) || !is_numeric($resource2) || !is_numeric($collection)){
		exit ("Error: invalid input to swap collection function.");
	}
	//exit ("Swapping " . $resource1 . " for " . $resource2);
	
	$query = "select resource,date_added,sortorder  from collection_resource where collection='$collection' and resource in ('$resource1','$resource2')  order by sortorder asc, date_added desc";
	$existingorder = sql_query($query);

	$counter = 1;
	foreach ($existingorder as $record){
		$rec[$counter]['resource']= $record['resource'];		
		$rec[$counter]['date_added']= $record['date_added'];
		if (strlen($record['sortorder']) == 0){
			$rec[$counter]['sortorder'] = "NULL";
		} else {		
			$rec[$counter]['sortorder']= "'" . $record['sortorder'] . "'";
		}
			
		$counter++;	
	}

	
	$sql1 = "update collection_resource set date_added = '" . $rec[1]['date_added'] . "', 
		sortorder = " . $rec[1]['sortorder'] . " where collection = '$collection' 
		and resource = '" . $rec[2]['resource'] . "'";

	$sql2 = "update collection_resource set date_added = '" . $rec[2]['date_added'] . "', 
		sortorder = " . $rec[2]['sortorder'] . " where collection = '$collection' 
		and resource = '" . $rec[1]['resource'] . "'";

	sql_query($sql1);
	sql_query($sql2);

	}

function update_collection_order($neworder,$collection,$offset=0)
	{
	if (!is_array($neworder)) {
		exit ("Error: invalid input to update collection function.");
	}

	$updatesql= "update collection_resource set sortorder=(case resource ";
	$counter = 1 + $offset;
	foreach ($neworder as $colresource){
		$updatesql.= "when '$colresource' then '$counter' ";
		$counter++;
	}
	$updatesql.= "else sortorder END) WHERE collection='$collection'";
	sql_query($updatesql);
	$updatesql="update collection_resource set sortorder=99999 WHERE collection='$collection' and sortorder is NULL";
	sql_query($updatesql);
	}
	
function get_collection_resource_comment($resource,$collection)
	{
	$data=sql_query("select *,use_as_theme_thumbnail from collection_resource where collection='$collection' and resource='$resource'","");
	return $data[0];
	}
	
function save_collection_resource_comment($resource,$collection,$comment,$rating)
	{
	# get data before update so that changes can be logged.	
	$data=sql_query("select comment,rating from collection_resource where resource='$resource' and collection='$collection'");
	sql_query("update collection_resource set comment='" . escape_check($comment) . "',rating=" . (($rating!="")?"'$rating'":"null") . ",use_as_theme_thumbnail='" . (getval("use_as_theme_thumbnail","")==""?0:1) . "' where resource='$resource' and collection='$collection'");
	
	# log changes
	if ($comment!=$data[0]['comment']){collection_log($collection,"m",$resource);}
	if ($rating!=$data[0]['rating']){collection_log($collection,"*",$resource);}
	return true;
	}

function relate_to_collection($ref,$collection)	
	{
	# Relates every resource in $collection to $ref
		$colresources = get_collection_resources($collection);
		sql_query("delete from resource_related where resource='$ref' and related in ('" . join("','",$colresources) . "')");  
		sql_query("insert into resource_related(resource,related) values ($ref," . join("),(" . $ref . ",",$colresources) . ")");
	}	
	
function get_mycollection_name($userref)
	{
	# Fetches the next name for a new My Collection for the given user (My Collection 1, 2 etc.)
	global $lang;
	for ($n=1;$n<500;$n++)
		{
		# Construct a name for this My Collection. The name is translated when displayed!
		if ($n==1)
			{
			$name = "My Collection"; # Do not translate this string!
			}
		else
			{
			$name = "My Collection " . $n; # Do not translate this string!
			}
		$ref=sql_value("select ref value from collection where user='$userref' and name='$name'",0);
		if ($ref==0)
			{
			# No match!
			return $name;
			}
		}
	# Tried nearly 500 names(!) so just return a standard name 
	return "My Collection";
	}
	
function get_collection_comments($collection)
	{
	return sql_query("select * from collection_resource where collection='$collection' and length(comment)>0 order by date_added");
	}

function send_collection_feedback($collection,$comment)
	{
	# Sends the feedback to the owner of the collection.
	global $applicationname,$lang,$userfullname,$userref,$k,$feedback_resource_select;
	
	$cinfo=get_collection($collection);if ($cinfo===false) {exit("Collection not found");}
	$user=get_user($cinfo["user"]);
	$body=$lang["collectionfeedbackemail"] . "\n\n";
	
	if (isset($userfullname))
		{
		$body.=$lang["user"] . ": " . $userfullname . "\n";
		}
	else
		{
		# External user.
		$body.=$lang["fullname"] . ": " . getval("name","") . "\n";
		$body.=$lang["email"] . ": " . getval("email","") . "\n";
		}
	$body.=$lang["message"] . ": " . stripslashes(str_replace("\\r\\n","\n",trim($comment)));

	$f=get_collection_comments($collection);
	for ($n=0;$n<count($f);$n++)
		{
		$body.="\n\n" . $lang["resourceid"] . ": " . $f[$n]["resource"];
		$body.="\n" . $lang["comment"] . ": " . trim($f[$n]["comment"]);
		if (is_numeric($f[$n]["rating"]))
			{
			$body.="\n" . $lang["rating"] . ": " . substr("**********",0,$f[$n]["rating"]);
			}
		}
	
	if ($feedback_resource_select)
		{
		$body.="\n\n" . $lang["selectedresources"] . ": ";
		$file_list="";
		$result=do_search("!collection" . $collection);
		for ($n=0;$n<count($result);$n++)
			{
			$ref=$result[$n]["ref"];
			if (getval("select_" . $ref,"")!="")
				{
				global $filename_field;
				$filename=get_data_by_field($ref,$filename_field);
				$body.="\n" . $ref . " : " . $filename;

				# Append to a file list that is compatible with Adobe Lightroom
				if ($file_list!="") {$file_list.=", ";}
				$s=explode(".",$filename);
				$file_list.=$s[0];
				}
			}
		# Append Lightroom compatible summary.
		$body.="\n\n" . $lang["selectedresourceslightroom"] . "\n" . $file_list;
		}	
	
	
	$cc=getval("email","");
	If (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
		send_mail($user["email"],$applicationname . ": " . $lang["collectionfeedback"] . " - " . $cinfo["name"],$body,"","","",NULL,"",$cc);
		}
	else
		{
		send_mail($user["email"],$applicationname . ": " . $lang["collectionfeedback"] . " - " . $cinfo["name"],$body);
		}
	
	# Cancel the feedback request for this resource.
	/* - Commented out - as it may be useful to leave the feedback request in case the user wishes to leave
	     additional feedback or make changes.
	     
	if (isset($userref))
		{
		sql_query("update user_collection set request_feedback=0 where collection='$collection' and user='$userref'");
		}
	else
		{
		sql_query("update external_access_keys set request_feedback=0 where access_key='$k'");
		}
	*/
	}

function copy_collection($copied,$current,$remove_existing=false)
	{	
	# Get all data from the collection to copy.
	$copied_collection=sql_query("select * from collection_resource where collection='$copied'","");
	
	if ($remove_existing)
		{
		#delete all existing data in the current collection
		sql_query("delete from collection_resource where collection='$current'");
		collection_log($current,"R",0);
		}
	
	#put all the copied collection records in
	foreach($copied_collection as $col_resource)
		{
		# Use correct function so external sharing is honoured.
		add_resource_to_collection($col_resource['resource'],$current,true);
		}
	}

if (!function_exists("collection_is_research_request")){
function collection_is_research_request($collection)
	{
	# Returns true if a collection is a research request
	return (sql_value("select count(*) value from research_request where collection='$collection'",0)>0);
	}
}	

if (!function_exists("add_to_collection_link")){
function add_to_collection_link($resource,$search="",$extracode="",$size="")
    {
    # Generates a HTML link for adding a resource to a collection
    global $lang;

    return "<a class=\"addToCollection\" href=\"#\" title=\"" . $lang["addtocurrentcollection"] . "\" onClick=\"AddResourceToCollection(event,'" . $resource . "','" . $size . "');" . $extracode . "return false;\">";

    }
}

if (!function_exists("remove_from_collection_link")){		
function remove_from_collection_link($resource,$search="")
    {
    # Generates a HTML link for removing a resource to a collection
    global $lang, $pagename;

    return "<a class=\"removeFromCollection\" href=\"#\" title=\"" . $lang["removefromcurrentcollection"] . "\" onClick=\"RemoveResourceFromCollection('" . $resource . "','" . $pagename . "');return false;\">";

    }
}

function change_collection_link($collection)
    {
    # Generates a HTML link for adding a changing the current collection
    global $lang;
    return '<a onClick="return CollectionDivLoad(this);" href="collections.php?collection='.$collection.'">&gt;&nbsp;'.$lang["selectcollection"].'</a>';

    }

function get_collection_external_access($collection)
	{
	# Return all external access given to a collection.
	# Users, emails and dates could be multiple for a given access key, an in this case they are returned comma-separated.
	return sql_query("select access_key,group_concat(DISTINCT user ORDER BY user SEPARATOR ', ') users,group_concat(DISTINCT email ORDER BY email SEPARATOR ', ') emails,max(date) maxdate,max(lastused) lastused,access,expires from external_access_keys where collection='$collection' group by access_key order by date");
	}
	
function delete_collection_access_key($collection,$access_key)
	{
	# Get details for log
	$users = sql_value("select group_concat(DISTINCT email ORDER BY email SEPARATOR ', ') value from external_access_keys where collection='$collection' and access_key = '$access_key' group by access_key ", "");
	# Deletes the given access key.
	sql_query("delete from external_access_keys where access_key='$access_key' and collection='$collection'");
	# log changes
	collection_log($collection,"t","",$users);

	}
	
function collection_log($collection,$type,$resource,$notes = "")
	{
	global $userref;
	sql_query("insert into collection_log(date,user,collection,type,resource, notes) values (now()," . (($userref!="")?"'$userref'":"null") . ",'$collection','$type'," . (($resource!="")?"'$resource'":"null") . ", '$notes')");
	}
/*  Log entry types  
$lang["collectionlog-r"]="Removed resource";
$lang["collectionlog-R"]="Removed all resources";
$lang["collectionlog-D"]="Deleted all resources";
$lang["collectionlog-d"]="Deleted resource"; // this shows external deletion of any resources related to the collection.
$lang["collectionlog-a"]="Added resource";
$lang["collectionlog-c"]="Added resource (copied)";
$lang["collectionlog-m"]="Added resource comment";
$lang["collectionlog-*"]="Added resource rating";
$lang["collectionlog-S"]="Shared collection with "; //  + notes field
$lang["collectionlog-E"]="E-mailed collection to ";//  + notes field
$lang["collectionlog-s"]="Shared Resource with ";//  + notes field
$lang["collectionlog-T"]="Stopped sharing collection with ";//  + notes field
$lang["collectionlog-t"]="Stopped access to resource by ";//  + notes field
$lang["collectionlog-X"]="Collection deleted";
$lang["collectionlog-b"]="Batch transformed";
*/
function get_collection_log($collection)
	{
	global $view_title_field;	
	return sql_query("select c.date,u.username,u.fullname,c.type,r.field".$view_title_field." title,c.resource, c.notes from collection_log c left outer join user u on u.ref=c.user left outer join resource r on r.ref=c.resource where collection='$collection' order by c.date");
	}
	
function get_collection_videocount($ref)
	{
	global $videotypes;
    #figure out how many videos are in a collection. if more than one, can make a playlist
	$resources = do_search("!collection" . $ref);
	$videocount=0;
	foreach ($resources as $resource){if (in_array($resource['resource_type'],$videotypes)){$videocount++;}}
	return $videocount;
	}
	
function collection_max_access($collection)	
	{
	# Returns the maximum access (the most permissive) that the current user has to the resources in $collection.
	$maxaccess=2;
	$result=do_search("!collection" . $collection);
	for ($n=0;$n<count($result);$n++)
		{
		$ref=$result[$n]["ref"];
		# Load access level
		$access=get_resource_access($result[$n]);
		if ($access<$maxaccess) {$maxaccess=$access;}
		}
	return $maxaccess;
	}

function collection_min_access($collection)	
	{
	# Returns the minimum access (the least permissive) that the current user has to the resources in $collection.
	$minaccess=0;
	if (is_array($collection)){$result=$collection;}
	else {
		$result=do_search("!collection" . $collection,"","relevance",0,-1,"desc",false,"",false,"");
	}
	for ($n=0;$n<count($result);$n++)
		{
		$ref=$result[$n]["ref"];
		# Load access level
		$access=get_resource_access($result[$n]);
		if ($access>$minaccess) {$minaccess=$access;}
		}
	return $minaccess;
	}
	
function collection_set_public($collection)
	{
	// set an existing collection to be public
		if (is_numeric($collection)){
			$sql = "update collection set public = '1' where ref = '$collection'";
			sql_query($sql);
			return true;
		} else {
			return false;
		}
	}

function collection_set_private($collection)
	{
	// set an existing collection to be private
		if (is_numeric($collection)){
			$sql = "update collection set public = '0' where ref = '$collection'";
			sql_query($sql);
			return true;
		} else {
			return false;
		}
	}

function collection_set_themes($collection,$themearr)
	{
		// add theme categories to this collection
		if (is_numeric($collection) && is_array($themearr)){
			global $theme_category_levels;
			$clause = '';
			for ($i = 0; $i < $theme_category_levels; $i++){
				if ($i == 0) {
					$column = 'theme';
				} else {
					$column = "theme" . ($i + 1);
				}
				if (isset($themearr[$i])){
					if (strlen($clause) > 0) {
						$clause .= ", ";
					}
					$clause .= " $column = '" . escape_check($themearr[$i]) . "' ";
				}
			}
			if (strlen($clause) > 0){
				$sql = "update collection set $clause where ref = '$collection'";
				sql_query($sql);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}	
	}
	
function remove_all_resources_from_collection($ref){
	// abstracts it out of save_collection() for use in collections_compact_style
		# Remove all resources?
	if (getval("removeall","")!="")
		{
		sql_query("delete from collection_resource where collection='$ref'");
		collection_log($ref,"R",0);
		}
	}	


function get_home_page_promoted_collections()
	{
	return sql_query("select collection.ref,collection.home_page_publish,collection.home_page_text,collection.home_page_image,resource.thumb_height,resource.thumb_width from collection left outer join resource on collection.home_page_image=resource.ref where collection.public=1 and collection.home_page_publish=1 order by collection.ref desc");
	}

function draw_compact_style_selector($collection,$onhover=true){
	
	global $baseurl,
	$baseurl_short,
	$lang,
	$pagename,
	$collections,
	$collections_compact_style,
	$collections_compact_style_ajax,
	$display,
	$thumbs,
	$collection_dropdown_user_access_mode,
	$default_display,
	$resources,
	$n,
	$userref,
	$preview_all,
	$show_edit_all_link,
	$contact_sheet,
	$allow_share,
	$collection_purge,
	$collection_download,
	$usercollection,$result,
	$collection_dropdown_user_access_mode,
	$cinfo,
	$feedback,
	$colresult,
	$m,$getthemes;	
	if(preg_match('/(Android|iPhone|iPad)/', $_SERVER['HTTP_USER_AGENT'])) { 
		$collections_compact_style_ajax=false; // omit this optimization for mobile as the hover events it relies on sometimes cause the selector to not be loaded prior to clicking.
	}
	if (!$onhover || !$collections_compact_style_ajax ){
		include(dirname(__FILE__)."/../pages/collections_compact_style.php");
		return;
	}
	
	//draw a dummy selector and then ajax load options on hover
	
	$tag=$pagename."-coltools-".$collection;if ($pagename=="collections"){$tag.="_usercol";}
	if ($pagename!="collections"){$hovertag="#CentralSpace";} 
	if ($pagename=="collections"){$hovertag=".CollectBack";} 
	
	?>	<select readonly="readonly" <?php if ($pagename=="collections"){if ($collection_dropdown_user_access_mode){?>class="SearchWidthExp" style="margin:0;"<?php } else { ?> class="SearchWidth" style="margin:0;"<?php } } ?> class="ListDropdown" <?php if ($pagename=="search" && $display=="xlthumbs"){?>style="margin:-5px 0px 0px 5px"<?php } ?> <?php if ($pagename=="search" && ( $display=="thumbs" || $display=="smallthumbs")){?>style="margin:-5px 0px 0px 4px "<?php } ?> id="temp<?php echo urlencode($tag) ?>"><option id="tempoption<?php echo urlencode($tag) ?>"><?php echo $lang['select'];?></option></select>
	<script>
	<?php if (substr(getvalescaped("search",""),0,11)!="!collection" && $pagename=="search"){?>
		jQuery('#temp<?php echo urlencode($tag) ?>').hover(function(){
		<?php } ?>
	jQuery('#tempoption<?php echo urlencode($tag) ?>').html('<?php echo $lang['loading']?>');
	jQuery.ajax({
		type: 'GET',
		url:  '<?php echo $baseurl_short?>pages/collections_compact_style.php?collection=<?php echo urlencode($collection) ?>&pagename=<?php echo urlencode($pagename) ?>&colselectload=true',
		success: function(msg){
			if(msg != 0) {
				jQuery('#temp<?php echo urlencode($tag) ?>').replaceWith(msg);
				<?php if ($pagename=="collections"){?>jQuery('#collections-coltools-<?php echo $cinfo['ref']?>_usercol').clone().attr("id",'#collections-coltools-<?php echo $cinfo['ref']?>_usercolmin').attr('onChange',"colAction(jQuery(this).val());jQuery(this).prop('selectedIndex',0);").prependTo("#MinSearchItem");<?php } ?>
			} 
		}});
	<?php if (substr(getvalescaped("search",""),0,11)!="!collection" && $pagename=="search"){?>
		});
	<?php } ?>
	
		
		
		</script>
	
	
	<?php
	
}

function is_collection_approved($collection)
		{
		if (is_array($collection)){$result=$collection;}
		else {
			$result=do_search("!collection" . $collection,"","relevance",0,-1,"desc",false,"",false,"");
			}	
		if (!is_array($result)){return true;}
		
		$collectionstates=array();
		global $collection_allow_not_approved_share;
		for ($n=0;$n<count($result);$n++)
			{
			$archivestatus=$result[$n]["archive"];
			if ($archivestatus<0 && !$collection_allow_not_approved_share) {return false;}
			$collectionstates[]=$archivestatus;
			}
		return array_unique($collectionstates);
		}

function edit_collection_external_access($key,$access=-1,$expires="")
	{
	global $userref;
	if ($key==""){return false;}
	# Update the expiration and acccess
	sql_query("update external_access_keys set access='$access', expires=" . (($expires=="")?"null":"'" . $expires . "'") . ",date=now() where access_key='$key'");	
	return true;
	}
	