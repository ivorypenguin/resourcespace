<?php

function HookFilterboxAllPreheaderoutput()
	{
	global $pagename;

	if (getval('ajax', '') == '')
		return;

	?>
	<script type="text/javascript">
	var pagename="<?php echo $pagename?>";
	jQuery(document).ready(function() {
		if (pagename == 'search')
			jQuery('.FilterBox#SearchBoxPanel').fadeIn(150);
		else
			jQuery('.FilterBox#SearchBoxPanel').fadeOut(150);
	});
	</script>
	<?php
	}

function HookFilterboxAllAddsearchbarpanel()
	{
	global $lang, $search, $archive, $autocomplete_search, $baseurl_short, $k, $quicksearch, $pagename, $filter_keywords, $filter_pos;
	include_once(dirname(__FILE__)."/../../../include/search_functions.php");

	if (empty($filter_keywords) && !empty($_COOKIE['filter']))
		$filter_keywords = $_COOKIE['filter'];
	if (empty($filter_pos) && !empty($_COOKIE['filter_pos']))
		$filter_pos = intval($_COOKIE['filter_pos']);
	if (isset($_COOKIE['original_search']))
		$original_search = $_COOKIE['original_search'];
	else
		$original_search = $search;
	?>

	<div class="FilterBox" id="SearchBoxPanel" style="display: <?php echo $pagename == 'search' ? 'block' : 'none' ?>">
	<div class="SearchSpace FilterBox">
	<h2><?php echo $lang["filtertitle"]?></h2>
	<p><?php echo $lang["filtertext"]?></p>

	<form id="FilterForm" method="post" action="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($original_search); ?>" onSubmit="return CentralSpacePost(this,true);">
	<div class="Question" id="question_related" style="border-top:none;">
		<input class="SearchWidth" type=text id="filter_keywords" name="filter_keywords" value="<?php echo htmlspecialchars(stripslashes($filter_keywords)); ?>" autofocus />
	<?php if ($autocomplete_search)
		{
		# Auto-complete search functionality
		?>
		<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery("#filter_keywords").autocomplete({
				source: "<?php echo $baseurl_short?>/plugins/filterbox/ajax/autocomplete_filter.php"
			});
		});
		</script>
	<?php
		}
	?>
	<input type="hidden" name="archive" value="<?php echo $archive?>" />
	<input type="hidden" name="cursorpos" />
	<input type="hidden" name="search" value="<?php echo htmlspecialchars(stripslashes($original_search))?>" />
	</div>

	<div class="QuestionSubmit"
		 style="padding-top:0;margin-top:0;margin-bottom:0;padding-bottom:0;">
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php
			echo $lang["filterbutton"]?>&nbsp;&nbsp;" />
	</div>
	</form>
	<br />
	<p><a onClick="document.getElementById('filter_keywords').value=''; CentralSpacePost(document.getElementById('FilterForm'), true);">&gt; <?php echo $lang['clearbutton']?></a></p>

	</div>
	</div>
	<?php
	}

?>
