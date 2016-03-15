<?php

function HookLegacy_stylingAllHeadblock()
	{
	global $pagename, $baseurl, $css_reload_key;
	if ($pagename!="preview_all")
		{
		?><!--[if lte IE 7]> <link href="<?php echo $baseurl?>/plugins/legacy_styling/css/legacyIE.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css"  media="screen,projection,print" /> <![endif]-->
		<?php 
		}
	
	}