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
						if (isset ($comments_policy_external_url) &&  $comments_policy_external_url!="") 
							{
							echo "<a href='$comments_policy_external_url'>&gt;&nbsp;${lang['comments_box-policy']}</a>";
							}
						else
							{
							if (text("comments_policy")!="") 
								{								
								echo "<a href='content.php?content=comments_policy'>&gt;&nbsp;${lang['comments_box-policy']}</a>";
								}
							else
								{
								// show placeholder only if user has permission to change site text to sort it							
								if (checkPerm("o"))
									{							
									echo "<a href=\"javascript: void(0)\" onclick=\"alert ('${lang['comments_box-policy-placeholder']}');\">&gt;&nbsp;${lang['comments_box-policy']}</a>";									
									}							
								}
					}
					?>				
				</div>
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
