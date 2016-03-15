<?php

function HookPdf_splitViewAfterresourceactions()
	{
	global $lang,$ref,$resource,$resourcetoolsGT;

	if (strtoupper($resource["file_extension"])!="PDF") {return false;} # PDF files only.
	?>
	<li><a href="../plugins/pdf_split/pages/pdf_split.php?ref=<?php echo $ref ?>"><?php echo ($resourcetoolsGT?"&gt; ":"").$lang["splitpdf"]?></a></li>
	<?php
		
	return false; # Allow other plugins to also use this hook.
	}

