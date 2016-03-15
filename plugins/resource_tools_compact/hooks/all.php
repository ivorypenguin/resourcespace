<?php
function HookResource_tools_compactAllAdditionalheaderjs() 
	{
	global $css_reload_key,$baseurl_short;
	?>
	<script type="text/javascript" src="<?php echo $baseurl_short;?>plugins/resource_tools_compact/js/scripts.js?<?php echo $css_reload_key;?>"></script>
	<?php
	}
?>