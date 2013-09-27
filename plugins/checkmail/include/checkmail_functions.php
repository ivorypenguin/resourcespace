<?php

// found function
function getdecodevalue($message,$coding) {
		switch($coding) {
			case 0:
			case 1:
				$message = imap_8bit($message);
				break;
			case 2:
				$message = imap_binary($message);
				break;
			case 3:
			case 5:
				$message=imap_base64($message);
				break;
			case 4:
				$message = imap_qprint($message);
				break;
		}
		return $message;
	}

function skip_mail($imap,$current_message,$note,$mail=false){
	// display note, and clear process lock.
	global $lang,$applicationname,$email_errors, $imap, $current_message,$email_errors_address,$email_from;
	
	echo($note."\r\n");

	if ($current_message!=""){	
		imap_setflag_full($imap, "$current_message", "\\Seen \\Flagged");
		echo "Marked message as seen. It will be omitted on the next run.\r\n\r\n";
	}

	if ($mail && $email_errors){
		send_mail($email_errors_address,$applicationname." - ".$lang["checkmail_mail_skipped"],$note,$email_from);
	}
	
	clear_process_lock("checkmail");
	
	die();
}

