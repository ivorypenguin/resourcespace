<?php

function HookFilterboxAllAdditionalheaderjs()
	{
	?><script type="text/javascript">
		function showHideFilterboxPanel() {
			// We check for the existance of this panel, as that seems to be the only reliable way
			if (jQuery('.TopInpageNav').get(0))
				jQuery('.FilterBox#SearchBoxPanel').fadeIn(150);
			else
				jQuery('.FilterBox#SearchBoxPanel').fadeOut(150);
		}
		jQuery(window).bind('popstate', showHideFilterboxPanel);
	</script><?php
	}

function HookFilterboxAllPreheaderoutput()
	{
	if (getval('ajax', '') == '')
		return;

	?>
	<script type="text/javascript">
		jQuery(document).ready(showHideFilterboxPanel);
	</script>
	<?php
	}

function HookFilterboxAllAddsearchbarpanel()
	{
	global $lang, $search, $archive, $autocomplete_search, $baseurl_short, $k, $quicksearch, $pagename, $filter_keywords, $filter_pos, $filterbox_instant_update;
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

	<form id="FilterForm" method="post" action="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($original_search); ?>&noreload=true" onSubmit="return CentralSpacePost(this,true);">
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
		if (!empty($filterbox_instant_update)) {
	?>
	<script type="text/javascript">
		// Update filter with a delay after each change
		var filterUpdateInterval;
		var oldFilterValue = jQuery('#filter_keywords').val();
		var previousFilterValue = oldFilterValue;
		var lastFilterChange;

		function getCursor(node) {
	        if ('selectionStart' in node) {
	            // Standard-compliant browsers
	            return node.selectionStart;
			} else if (document.selection) {
				// IE
				node.focus();
	            var sel = document.selection.createRange();
		        var selLen = document.selection.createRange().text.length;
			    sel.moveStart('character', -node.value.length);
				return sel.text.length - selLen;
			}
		}

		function setCursor(node, pos) {
			if (node.createTextRange) {
				var textRange = node.createTextRange();
				textRange.collapse(true);
				textRange.moveEnd(pos);
				textRange.moveStart(pos);
				textRange.select();
				return true;
			}
			if (node.setSelectionRange) {
				node.setSelectionRange(pos, pos);
				return true;
			}

			return false;
		}
		<?php if (!empty($filter_pos)) { ?>
			setCursor(jQuery('#filter_keywords').get(0), <?php echo $filter_pos; ?>);
		<?php } ?>

		function updateFilter() {
			var newValue = jQuery('#filter_keywords').val();
			jQuery('input[name="cursorpos"]').val(getCursor(jQuery('#filter_keywords').get(0)));
			if (oldFilterValue != newValue) {
				if (previousFilterValue != newValue)
					SetCookie('filter', newValue);

				var now = Date.now();
				if (!lastFilterChange)
					lastFilterChange = now;
				if (now > lastFilterChange + 500) {
					oldFilterValue = newValue;
					CentralSpacePost(document.getElementById('FilterForm'), true);
				}
				if (previousFilterValue != newValue) {
					previousFilterValue = newValue;
					lastFilterChange = now;
				}
			}
		}

		jQuery('#filter_keywords')
			.keyup(updateFilter)
			.change(updateFilter)
			.focus(function() {
				filterUpdateInterval = setInterval(updateFilter, 50);
			})
			.blur(function() {
				clearInterval(filterUpdateInterval);
				updateFilter();
			});
	</script>
	<?php } ?>
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
