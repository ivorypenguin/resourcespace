<?php

function HookFilterboxAllSearchbarreplace()
	{
	global $lang, $search, $archive, $baseurl, $autocomplete_search, $baseurl_short, $k, $quicksearch;
	include_once(dirname(__FILE__)."/../../../include/search_functions.php");
	?>

	<h2><?php echo $lang["filtertitle"]?></h2>
	<p><?php echo $lang["filtertext"]?></p>

	<form method="post" action="<?php echo $baseurl_short?>pages/search.php?k=<?php echo $k ?>" onSubmit="return CentralSpacePost (this,true);">
	<div class="Question" id="question_related" style="border-top:none;">
	<input class="SearchWidth" type=text id="refine_keywords" name="refine_keywords" autofocus />

	<?php if ($autocomplete_search)
		{
		# Auto-complete search functionality
		?>
		<script type="text/javascript">
		jQuery(document).ready(function () {
		jQuery("#refine_keywords").autocomplete( { source: "<?php echo $baseurl?>/plugins/filterbox/ajax/autocomplete_filter.php" } );
			})
		</script>
	<?php
		}
	?>
	<input type=hidden name="archive" value="<?php echo $archive?>" />
<input type=hidden name="search" value="<?php echo htmlspecialchars(stripslashes(@$quicksearch))?>"
    />
	</div>

	<div class="QuestionSubmit"
		 style="padding-top:0;margin-top:0;margin-bottom:0;padding-bottom:0;">
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php
			echo $lang["filterbutton"]?>&nbsp;&nbsp;" />
	</div>
	</form>

	</div>
	</div>
	<br />
	<div id="SearchBoxPanel">
	<div class="SearchSpace">
	<?php
	return false;
	}

global $basic_simple_search;
if ($basic_simple_search)
	{
	function HookFilterboxAllSearchbarbeforebottomlinks()
		{
		global $lang;
		?>
		<p><a onClick="document.getElementById('ssearchbox').value=''; document.getElementById('basicyear').value='';document.getElementById('basicmonth').value='';">&gt; <?php echo $lang['clearbutton']?></a></p>
		<?php
		}
	}

function HookFilterboxSearchSearchstringprocessing()
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
