<!--Begin Resource Comments -->

<div class="RecordBox">	
	<div class="RecordPanel">					
		<div id="CommentsPanelHeader">
			<div id="CommentsPanelHeaderRow">
				<div id="CommentsPanelHeaderRowTitle" class="Title">
					<?php echo $lang['comments_box-title']; ?>
				</div>							
				<div id="CommentsPanelHeaderRowPolicyLink">					
					<?php					
						if ((isset ($site_text['comments_policy']) && $site_text['comments_policy']!="") || $userref==1)		// TODO: check $userref==1 always the Admin User?
							{
							echo "<a href='javascript: void()' onclick='jQuery(\"#CommentsPolicyContainer\").toggle(\"fast\");'>&gt;&nbsp;${lang['comments_box-policy']}</a>";
							}										
					?>
				</div>				
			</div>
		</div>			
		<div id="CommentsPolicyContainer">
			<div id="CommentsPolicyContainerBody">
			<?php
				if (isset ($site_text['comments_policy']) && $site_text['comments_policy']!="") 
				{
					echo $site_text['comments_policy'];
				} else {
					if ($userref==1) echo $lang['comments_box-policy-placeholder'];		// show placeholder only if Admin User	// TODO: check $userref==1 always the Admin User?
				}
			?>
			</div>
		</div>				
		<div id="CommentsContainer">
			<!-- populated on completion of DOM load -->
		</div>		
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function () {		
		jQuery("#CommentsContainer").load("../pages/ajax/comments_handler.php?ref=<?php echo $ref;?>", null);	
	});	
</script>	

<!-- End Resource Comments -->
