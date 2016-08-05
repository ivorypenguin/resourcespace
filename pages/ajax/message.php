<?php

 	DEFINE ("MESSAGE_POLLING_ABSENT_USER_TIMEOUT_SECONDS",30);
 	DEFINE ("MESSAGE_FADEOUT_SECONDS",5);

	// check for callback, i.e. this file being called directly to get any new messages
	if (basename(__FILE__)==basename($_SERVER['PHP_SELF']))
		{
		include_once __DIR__ . "/../../include/db.php";
		include_once __DIR__ . "/../../include/general.php";
		

		// It is an acknowledgement so set as seen and get out of here
		if (isset($_GET['seen']))
			{
			message_seen($_GET['seen']);
			return;
			}
			
		if (isset($_GET['unseen']))
			{
			message_unseen($_GET['unseen']);
			return;
			}

		// Acknowledgement all messages then get out of here
		if (isset($_GET['allseen']))
			{
			message_seen_all($_GET['allseen']);
			return;
			}

		// Purge messages that have an expired TTL then get out of here
		if (isset($_GET['purge']))
			{
			message_purge();
			return;
			}

		if(isset($_GET['user']))
			{
			$user=$_GET['user'];
			}
		else
			{
			include __DIR__ . "/../../include/authenticate.php";	// no user specified so default to the current user
			$user=$userref;
			}

		// Check if there are messages
		$messages = array();
		message_get($messages,$user);	// note: messages are passed by reference
		ob_clean();	// just in case we have any stray whitespace at the start of this file
		echo json_encode($messages);
		return;
		}

?><script>

 	var activeSeconds=<?php echo MESSAGE_POLLING_ABSENT_USER_TIMEOUT_SECONDS; ?>;

	var message_timer = null;
	var message_refs = new Array();
	var message_poll_first_run = true;

	function message_poll()
	{
		if (message_timer != null)
		{
			clearTimeout(message_timer);
			message_timer = null;
		}
		activeSeconds-=<?php echo $message_polling_interval_seconds; ?>;
		<?php
		if ($message_polling_interval_seconds > 0)
			{
			?>if(activeSeconds < 0)
			{
				message_timer = window.setTimeout(message_poll,<?php echo $message_polling_interval_seconds; ?> * 1000);
				return;
			}
			<?php
			}
		?>
		jQuery.ajax({
			url: '<?php echo $baseurl; ?>/pages/ajax/message.php',
			type: 'GET',
			success: function(messages, textStatus, xhr) {
				if(xhr.status==200 && isJson(messages) && (messages=jQuery.parseJSON(messages)) && messages.length>0)
				{
					jQuery('span.MessageCountPill').html(messages.length).click(function() {
						CentralSpaceLoad('<?php echo $baseurl; ?>/pages/user/user_messages.php',true);
					}).fadeIn();
					if (activeSeconds > 0 || message_poll_first_run)
					{
						for(var i=0; i < messages.length; i++)
						{
							var ref = messages[i]['ref'];
							if (message_poll_first_run)
							{
								message_refs.push(ref);
								continue;
							}
							if (message_refs.indexOf(ref)!=-1)
							{
								continue;
							}
							message_refs.push(ref);
							var message = nl2br(messages[i]['message']);
							var url = messages[i]['url'];
							<?php
							if($user_pref_show_notifications)
								{
								?>
								message_display(message, url, ref, function (ref) {
									jQuery.get('<?php echo $baseurl; ?>/pages/ajax/message.php?seen=' + ref).done(function () {
									});
								});
								<?php
								}
								?>
								message_poll();
						}
					}
				}
				else
				{
					jQuery('span.MessageCountPill').hide();
				}
			}
		}).done(function() {
			<?php if ($message_polling_interval_seconds > 0)
			{
				?>message_timer = window.setTimeout(message_poll,<?php echo $message_polling_interval_seconds; ?> * 1000);
				<?php
			}
			?>
			message_poll_first_run = false;
		});
	}

	jQuery(document).bind("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error",
		function() {
			activeSeconds=<?php echo MESSAGE_POLLING_ABSENT_USER_TIMEOUT_SECONDS; ?>;
		});

	jQuery(document).ready(function () {
			message_poll();
		});

	function message_display(message, url, ref, callback)
	{
		if (typeof ref==="undefined")
		{
			ref=new Date().getTime();
		}
		if (typeof url==="undefined")
		{
			url="";
		}
		if (url!="")
		{
			url=decodeURIComponent(url);
			url="<a href='" + url + "'><?php echo $lang['link']; ?></a>";
		}
		var id='message' + ref;
		if (jQuery("#" + id).length)		// already being displayed
		{
			return;
		}
		jQuery('div#MessageContainer').append("<div class='MessageBox' style='display: none;' id='" + id + "'>" + nl2br(message) + "<br />" + url + "</div>").after(function()
		{
			var t = window.setTimeout(function()
			{
				jQuery("div#" + id).fadeOut("fast",function()
					{
						this.remove()
					}
				)
			},<?php echo MESSAGE_FADEOUT_SECONDS; ?>000);

			jQuery("div#" + id).show().bind("click",function()
			{
				jQuery("div#" + id).fadeOut("fast", function()
				{
					jQuery("div#" + id).remove();
					jQuery.get('<?php echo $baseurl; ?>/pages/ajax/message.php?seen=' + ref);
					if (typeof callback === 'function')
					{
						callback();
					}
				});
			});

			jQuery("div#" + id).bind("mouseenter",function()
			{
				window.clearTimeout(t);
				jQuery("div#" + id).fadeIn("fast");
			});

			jQuery("div#" + id).bind("mouseleave",function()
			{
				window.clearTimeout(t);
				t = window.setTimeout(function()
				{
					jQuery("div#" + id).fadeOut("fast",function()
						{
							this.remove();
						}
					)},<?php echo ceil(MESSAGE_FADEOUT_SECONDS / 2); ?>000);
			});
		});
	}
	
	function message_modal(message, url, ref, owner)
		{
		if (typeof ref==="undefined")
			{
				ref=new Date().getTime();
			}
		if (typeof url==="undefined")
			{
				url="";
			}
		if (url!="")
			{
				url=decodeURIComponent(url);
				url="<a href='" + url + "'><?php echo $lang['link']; ?></a>";
			}
		if (typeof owner==="undefined" || owner=='')
			{
			owner = '<?php echo htmlspecialchars($applicationname, ENT_QUOTES); ?>';
			}
		jQuery("#modal_dialog").html("<div class='MessageText'>" + nl2br(message) + "</div><br />" + url);
		jQuery("#modal_dialog").addClass('message_dialog');
		jQuery("#modal_dialog").dialog({
			title: '<?php echo $lang['message'] . " " . strtolower($lang["from"]) . " "; ?>' + owner,
			modal: true,
			resizable: false,
			buttons: [{text: '<?php echo $lang['ok'] ?>',
					  click: function() {
						jQuery( this ).dialog( "close" );
						}}],
			dialogClass: 'message',
			width:'auto',
			draggable: true,
			open: function(event, ui) { jQuery('.ui-widget-overlay').bind('click', function(){ jQuery("#modal_dialog").dialog('close'); }); },
			close: function( event, ui ) {
				jQuery('#modal_dialog').html('');
				jQuery("#modal_dialog").removeClass('message_dialog');
				jQuery.get('<?php echo $baseurl; ?>/pages/ajax/message.php?seen=' + ref);
				},
			dialogClass: 'no-close'
			});
			 
		}

</script>
