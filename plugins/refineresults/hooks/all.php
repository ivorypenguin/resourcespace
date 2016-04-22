<?php

function HookRefineresultsSearchBeforesearchresults()
	{
	global $baseurl_short,$result,$lang,$search,$k,$archive,$parameters_string, $collections;

	// Only time when this would be needed is when legacy_actions is enabled otherwise we do it through dropdown actions
	$query = 'SELECT inst_version AS `value` FROM plugins WHERE name = \'legacy_actions\';';
	if(trim(sql_value($query, '')) === '')
		{
		return false;
		}

	$results=0;
	if (is_array($result)) $results=count($result);
	if (is_array($collections)) $results+=count($collections);
	#if ($k!="" || $results==0) {return false;}
	#if ($results==0||$results==1) {return false;}
	
	# External sharing search support. Clear search drops back to the collection only search.
	$default_search="";
	if ($k!="") {$s=explode(" ",$search);$default_search=$s[0];}
	
	# dropping back to a special search seems like appropriate behavior in general.
	if ($k=="" && substr($search,0,1)=="!") {
		$s=explode(" ",$search);
		# Should a second Clear be allowed to blank out the special search? 
		# if (count($s)>1){  
			$default_search=$s[0];
		#}
	}
	

	#if (substr($search,0,1)=="!") {return false;} # Only work for normal (non 'special') searches
	?>
	<div class="SearchOptionNav"><?php if ($results!=0 && $results!=1){?><a href="#" onClick="
	if (jQuery('#RefinePlus').html()=='+')
		{
		jQuery('#RefineResults').slideToggle();
		jQuery('#RefinePlus').html('&minus;');
		jQuery('#refine_keywords').focus();
		}
	else
		{
		jQuery('#RefineResults').slideToggle();
		jQuery('#RefinePlus').html('+');
		}
	"><span id='RefinePlus'>+</span> <?php echo $lang["refineresults"]?></a>&nbsp;&nbsp;<?php } ?><?php if ($search!=""){?><a href='<?php echo $baseurl_short?>pages/search.php?search=<?php echo $default_search ?><?php echo $parameters_string?>'>&gt;&nbsp;<?php echo $lang["clearsearch"]?></a><?php } ?></div>
	<?php
	return true;
	}
	
function HookRefineresultsSearchBeforesearchresultsexpandspace()
	{
	global $baseurl_short,$lang,$search,$k,$archive;

	# Slightly different behaviour when allowing external share searching. Show the full search string in the box.
	$value="";
	if ($k!="")
		{
		$s=explode(" ",$search);
		if (count($s)>1)
			{
			array_shift($s);
			$value=join(" ",$s);
			}
		}
	?>
	
	<div class="RecordBox clearerleft" id="RefineResults" style="display:none;">
	<div class="RecordPanel" id="refine_panel">  
	
	<form method="post" action="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($search) ?>&amp;k=<?php echo $k ?>" onSubmit="return CentralSpacePost (this,true);">
	<div class="Question Inline" id="question_refine" style="border-top:none;">
	<label id="label_refine" for="refine_keywords"><?php echo $lang["additionalkeywords"]?></label>
	<input class="medwidth Inline" type=text id="refine_keywords" name="refine_keywords" value="<?php echo $value ?>">
	<input type=hidden name="archive" value="<?php echo $archive?>">
	<input class="vshrtwidth Inline" name="save" type="submit" id="refine_submit" value="&nbsp;&nbsp;<?php echo $lang["refine"]?>&nbsp;&nbsp;" />
	<div class="clearerleft"> </div>
	</div>

	</form>
	
	</div>
	<div class="PanelShadow"></div>
	</div>
	<?php
	
	return true;
	}

function HookRefineresultsSearchSearchstringprocessing()
	{
	global $search,$k;
	$refine=trim(getvalescaped("refine_keywords",""));
	if ($refine!="")
		{
		if ($k!="")
			{
			# Slightly different behaviour when searching within external shares. There is no search bar, so the provided string is the entirity of the search.
			$s=explode(" ",$search);
			$search=$s[0] . " " . $refine;	
			}
		else
			{
			$search.=", " . $refine;	
			}
		}
	$search=refine_searchstring($search);	
	}

?>
