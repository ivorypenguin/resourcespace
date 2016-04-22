<?php

function HookNewsAllPreheaderoutput() 
	{
	if (getvalescaped("ajax",false))
  		{
		?>
		<script>ReloadSearchBar();</script>
		<?php
		}
	}
