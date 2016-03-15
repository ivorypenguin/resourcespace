<?php

function HookYoutube_publishViewAfterresourceactions()
	{
	# Adds a Youtube link to the view page.
	global $baseurl, $lang, $ref, $access, $resource, $youtube_publish_restypes,$resourcetoolsGT;
	
	if ($access==0 && in_array($resource["resource_type"],$youtube_publish_restypes))
		{
		?>
		<li><a href="<?php echo $baseurl?>/plugins/youtube_publish/pages/youtube_upload.php?resource=<?php echo $ref?>"><?php echo ($resourcetoolsGT?"&gt; ":"").$lang["youtube_publish_linktext"]; ?></a></li>
		<?php
		}
	}

?>
