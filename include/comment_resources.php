<!--Begin Resource Comments -->

<div class="RecordBox">	
	<div class="RecordPanel">	
	    <div id="Comments">				
    		<div id="CommentsPanelHeader">
    			<div id="CommentsPanelHeaderRow">
    				<div id="CommentsPanelHeaderRowTitle">
    					<div class="Title"><?php echo $lang['comments_box-title']; ?></div>
    				</div>							
    				<div id="CommentsPanelHeaderRowPolicyLink">				
    					<?php
    						if (isset ($comments_policy_external_url) &&  $comments_policy_external_url!="") 
    							{
    							echo "<a href='$comments_policy_external_url' target='_blank'>" . LINK_CARET . "${lang['comments_box-policy']}</a>";
    							}
    						else
    							{
    							if (text("comments_policy")!="") 
    								{								
    								echo "<a href='content.php?content=comments_policy' target='_blank'>" . LINK_CARET . "${lang['comments_box-policy']}</a>";
    								}
    							else
    								{
    								// show placeholder only if user has permission to change site text to sort it							
    								if (checkPerm("o"))
    									{							
    									echo "<a href=\"javascript: void(0)\" onclick=\"alert ('${lang['comments_box-policy-placeholder']}');\">" . LINK_CARET . "${lang['comments_box-policy']}</a>";									
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
</div>
<?php
if (!$view_panels) {
?>
<script type="text/javascript">
	jQuery(document).ready(function () {		
		jQuery("#CommentsContainer").load(
			"../pages/ajax/comments_handler.php?ref=<?php echo $ref;?>", 
			function() {
			if (jQuery.type(jQuery(window.location.hash)[0])!=="undefined")				
				jQuery(window.location.hash)[0].scrollIntoView();
			}						
		);	
	});			
</script>	
<?php
}
?>
<!-- End Resource Comments -->
