<!--Begin Resource Comments -->

<div class="RecordBox">	
	<div class="RecordPanel">					
		<div id="CommentsPanelHeader">
			<div id="CommentsPanelHeaderRow">
				<div id="CommentsPanelHeaderRowTitle">
					<h1><?php echo $lang['comments_box-title']; ?></h1>
				</div>							
				<div id="CommentsPanelHeaderRowPolicyLink">					
					<?php					
						if (text("comments_policy")!="" || checkPerm("o"))		// allow somebody with site text update permission to view no matter what
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
				if (text("comments_policy")!="") 
				{
					echo text("comments_policy");
				} else {
					if (checkPerm("o")) echo $lang['comments_box-policy-placeholder'];		// show placeholder only if user has permission to change site text to sort it
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
