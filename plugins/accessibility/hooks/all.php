<?php

function HookAccessibilityAllAfterheaderajax()
{
?>
	<script type="text/javascript">
		jQuery(document).ready(function(){

			var homepanel_sizes = [];

			// Get all heights of the homepanels on the screen:
			jQuery('.HomePanelIN').each(function(index, value) {
				homepanel_sizes.push(jQuery(value).height());
			});

			// Get the highest height of homepanels:
			var highest_homepanel = Math.max.apply(Math, homepanel_sizes);

			jQuery('.HomePanelIN').each(function(index, value) {
				jQuery(value).height(highest_homepanel);
			});

		});
	</script>
<?php
}

function HookAccessibilityAllAdditionalheaderjs() {
	// Have the same functionality when ajax is not involved:
	HookAccessibilityAllAfterheaderajax();
}