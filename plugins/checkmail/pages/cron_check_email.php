<?php 
include '../../../include/db.php';
include '../../../include/general.php';
include '../../../include/image_processing.php';
include '../../../include/resource_functions.php';
include '../../../include/collections_functions.php';
include '../include/checkmail_functions.php';




// required: check that this plugin is activated
$activated=sql_value("select inst_version value from plugins where name='checkmail'","");
if ($activated==""){die("checkmail plugin deactivated\r\n");}




// command line options
if ($argc == 2)
{
	if ( in_array($argv[1], array('--help', '-help', '-h', '-?')) )
	{
		echo "To clear the lock after a failed run, ";
  		echo "pass in '--clearlock', '-clearlock', '-c' or '--c'.\n";
  		exit("Bye!\n");
  	}
	else if ( in_array($argv[1], array('--clearlock', '-clearlock', '-c', '--c')) )
	{
		if ( is_process_lock("checkmail") )
		{
			clear_process_lock("checkmail");
		}
	}
	else
	{
		exit("Unknown argv: " . $argv[1]);
	}
} 


# Check for a process lock
# This script checks one e-mail at a time.
if (is_process_lock("checkmail")) {
	if ($email_errors){
		send_mail($email_errors_address,$applicationname."- Checkmail blocked by process lock","Your IMAP account will not be checked until you clear this. An error may have caused this. Run the process manually with the -c switch to clear the lock and check for any errors.",$email_from);
	}
	exit("Process lock is in place. Deferring\r\n");
}
set_process_lock("checkmail");


// manually include plugin config since authenticate isn't being run
$config = sql_value("select config value from plugins where name='checkmail'","");
include_plugin_config("checkmail",$config);

$temp_dir=$storagedir."/tmp/checkmail_in";
if (!is_dir($temp_dir)){mkdir($temp_dir,0777);}



$delete=false; // set to true only after all files are transferred
$build_collection=false;
$collection="";




// get the first unseen message, one email is processed in this script
$imap=imap_open("{".$checkmail_imap_server. "}INBOX", $checkmail_email, $checkmail_password ) or die("can't connect: " . imap_last_error() );

sql_query("delete from sysvars where name='last_checkmail'");
sql_query("insert into sysvars (value,name) values (now(),'last_checkmail')");

$msgnos=imap_search($imap, 'UNSEEN');
if ($msgnos==null){
	skip_mail($imap,"","No new mail on ". date('l jS \of F Y h:i:s A').".");
}
$current_message=$msgnos[0];
echo "\r\n\r\nChecking Latest Unread Message\r\n";





// get the header info
echo "Fetching header info...\r\n";
$headerinfo=imap_headerinfo($imap,$current_message);

// get the subject
if (isset($headerinfo->subject)){
	$subject=$headerinfo->subject; 
}
else if (isset($headerinfo->Subject)){
	$subject=$headerinfo->Subject; 
}
else {
	$subject="";
	echo "No Subject...\r\n"; 
}
$subject=imap_mime_header_decode($subject);
$flattenedsubject="";
foreach ($subject as $key=>$part){
	$charset=$part->charset;
	$flattenedsubject.=$part->text;
}

if ($charset!="default"){
	$subject=iconv($charset, "UTF-8",$flattenedsubject);
} else { $subject=$flattenedsubject;}




// get the from address
$fromaddress=$headerinfo->from[0];
$fromaddress=$fromaddress->mailbox."@".$fromaddress->host;

// check that the user exists
$fromuser=get_user_by_email($fromaddress); 
if (isset($fromuser[0])){
	$fromuser=$fromuser[0];echo "Matched User: ".$fromuser['username']." (".$fromuser['fullname'].")\r\n";
	$fromuser_ref=$fromuser['ref'];
	$userref=$fromuser_ref; // so that create_resource will work (doesn't accept the parameter, but grabs the global;
	$fromusername=$fromuser['username'];
	}	
else {
	skip_mail($imap,$current_message,"Could not find $fromaddress among Users on ". date('l jS \of F Y h:i:s A').".", true);
}
	
if (!in_array($userref,$checkmail_users)){	
	skip_mail($imap,$current_message,$fromuser['fullname']."($fromusername), user $userref with e-mail $fromaddress is not included in checkmail_users on ". date('l jS \of F Y h:i:s A').".",true);
}
	
// check that the user can create resources
if (isset($fromuser['groupref'])){ 
	$fromusergroup=get_usergroup($fromuser['groupref']);
	
	$permissions=explode(",",$fromusergroup['permissions']);
	if (! (in_array("c",$permissions) || in_array("d",$permissions))){
		skip_mail($imap,$current_message,"No Permissions to upload for $fromusername, user $userref on ". date('l jS \of F Y h:i:s A').".",true);
	} 
}



// check structure
$structure = imap_fetchstructure($imap, $current_message);   
echo "Fetching Structure...\r\n";

if ($structure->type!=1){ 
	skip_mail($imap,$current_message,"No attachments in $subject on ". date('l jS \of F Y h:i:s A').".",true);
}

// Display main email type
echo "MULTIPART";
if ($structure->ifsubtype!=0) {
echo "/".$structure->subtype."\r\n";
}

// Loop through parts
// found function to do the recursion and make it simpler:
// http://www.electrictoolbox.com/php-imap-message-parts/
function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {

	foreach($messageParts as $part) {
		$flattenedParts[$prefix.$index] = $part;
		if(isset($part->parts)) {
			if($part->type == 2) {
				$flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.', 0, false);
			}
			elseif($fullPrefix) {
				$flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix.$index.'.');
			}
			else {
				$flattenedParts = flattenParts($part->parts, $flattenedParts, $prefix);
			}
			unset($flattenedParts[$prefix.$index]->parts);
		}
		$index++;
	}

	return $flattenedParts;
			
}

$parts = flattenParts($structure->parts); 
//print_r($parts);
// count attachments now, collect body
$att_count=0;
$body="";
$body_html="";
foreach ($parts as $key=>$part){
	echo "Part $key: ";
	echo $part->subtype." ";
	$charset="";
	if ($part->ifparameters){
		foreach ($part->parameters as $parameter){
			echo $parameter->attribute."=".$parameter->value;
			if (strtoupper($parameter->attribute)=="CHARSET"){
				$charset=$parameter->value;
			}	
		}
	}

 	if ($part->ifdisposition){echo " ".$part->disposition;}

	if (strtoupper($part->subtype)=="PLAIN"){echo " retrieving plain text body...";
		$body_part=getdecodevalue(imap_fetchbody($imap,$current_message,$key),$part->encoding);
		if ($charset!="default"){
			$body_part=iconv($charset,"UTF-8",$body_part);
		}
		echo $body_part;
		$body.=$body_part; // this is for apple mails I've seen which have multiple text parts
		// of different charsets
		//echo $body;
	}
	if (strtoupper($part->subtype)=="HTML"){echo " retrieving HTML body...";
		$body_html=getdecodevalue(imap_fetchbody($imap,$current_message,$key),$part->encoding);
		if ($charset!="default"){
		$body_html=iconv($charset,"UTF-8",$body_html);
		}
		echo $body_html;
	}
	// ignore related. Instead, use HTML and inline attachments
	//if ($part->subtype=="RELATED"){echo " retrieving RELATED body..";
	//	$related=imap_fetchbody($imap,$current_message,$key);
	//}

	if ($part->ifdisposition){
		// save inline image data
		if (strtoupper($part->disposition)=="INLINE" && $part->type==5){
			// store inline attachments info
			$file['key']=$key;
			if (isset($part->id)){$file['id']=str_replace(">","",str_replace("<","",$part->id));}
			$file['filename']=$part->dparameters[0]->value;
			$file['extension']=pathinfo($file['filename'],PATHINFO_EXTENSION);
			$file['extension']=strtolower($part->subtype);
			$file['encoding']=$part->encoding;
			$files[]=$file;
			$att_count++;
		}
	
		if (strtoupper($part->disposition)=="ATTACHMENT"){
			$file['key']=$key;			
			$file['filename']=$part->dparameters[0]->value;
			$file['extension']=strtolower(pathinfo($file['filename'],PATHINFO_EXTENSION));
			$file['encoding']=$part->encoding;
			$files[]=$file;
			$att_count++;	
		}
	}
	
	echo "\r\n";
}


// decide whether to skip, create a single resource, or create a collection of resources
switch($att_count){
	case 0: 
		skip_mail($imap,$current_message,"Nothing to upload",true);
		break;
	case 1: 
		echo "Found $att_count attachment\r\n";
		break;
	default: 
		echo "Found $att_count attachments. Collection will be created\r\n";
		$build_collection=true;
		$collection=create_collection($fromuser_ref,$subject);
		echo "Creating Collection $collection : $subject \r\n";	
} 


// save attachments
$checkmail_archive_state=$checkmail_default_archive;
$access=$checkmail_default_access;
$resource_types=get_resource_types();

for ($n=0;$n<count($files);$n++){

	$file=$files[$n];
	
	foreach ($resource_types as $resource_type){
		$safe_varname="resourcetype".$resource_type['ref'];
		if (!isset($$safe_varname)){$$safe_varname="";}
		$file_extensions=explode(",",strtolower($$safe_varname));
		if (in_array(strtolower($file['extension']),$file_extensions)){
			$resource_type=$resource_type['ref']; 
			break;
		} else {
			$resource_type=$checkmail_default_resource_type;
		}
	}
	
	$data=imap_fetchbody($imap,$current_message,$file['key']);
	$fp=fopen($temp_dir."/".$file['filename'],"w");
	$data=getdecodevalue($data,$file['encoding']);	
	fputs($fp,$data); echo "Downloading to filestore/tmp/checkmail_in \r\n";
	fclose($fp);

	// Get resource defaults for user's group
	$userresourcedefaults=sql_query("select resource_defaults from usergroup where ref='" . $fromuser['groupref'] . "'");
	if (isset($userresourcedefaults)){
		$userresourcedefaults=$userresourcedefaults[0];
		$userresourcedefaults=$userresourcedefaults["resource_defaults"];
		}

	// Create resource
	$r=create_resource($resource_type,$checkmail_archive_state,$fromuser_ref);  echo "Creating Resource $r \r\n";
	sql_query("update resource set access='".$access."',file_extension='".$file['extension']."',preview_extension='jpg',file_modified=now() where ref='$r'");
		
	// Update metadata fields  // HTML OPTIONS
	update_field($r,$filename_field,$file['filename']); 
	update_field($r,$checkmail_subject_field,$subject);
	if ($body_html!='' && $checkmail_html){
	update_field($r,$checkmail_body_field,$body_html);
	} else {
	update_field($r,$checkmail_body_field,$body);
	}
	echo "Updating Metadata \r\n";

	# Move the file
	$destination=get_resource_path($r,true,"",true,$file['extension']);	
	$result=rename($temp_dir."/".$file['filename'],$destination);  echo "Moving file to filestore \r\n";
	chmod($destination,0777);

	# get file metadata 
	extract_exif_comment($r,$file['extension']); echo "Extracting Metadata... \r\n";
	
	# Ensure folder is created, then create previews.
	get_resource_path($r,false,"pre",true,$file['extension']);
					
	if ($build_collection){
		# Add Resource to Collection
		echo "Adding Resource $r to Collection $collection \r\n";
		add_resource_to_collection($r,$collection,true);
	}
					
	# Generate previews/thumbnails (if configured i.e if not completed by offline process 'create_previews.php')
	global $enable_thumbnail_creation_on_upload;
	if ($enable_thumbnail_creation_on_upload) {
		create_previews($r,false,$file['extension']); 
		echo "Creating Previews... \r\n";
	}
		 
	if (!$build_collection && $checkmail_confirm){
		email_resource($r,$subject,$applicationname,$fromusername,$lang['yourresourcehasbeenuploaded'],0,$expires="",$fromaddress,$applicationname);
		echo "Email Confirmation sent. \r\n";
	}

	if ($checkmail_purge){$delete=true;}
	$files[$n]['ref']=$r;
	$refs[]=$r;
}

if ($build_collection && $checkmail_confirm){
	email_collection($collection,$subject,$email_from,$fromaddress,$lang['yourresourceshavebeenuploaded'],false,0,$expires="",$fromaddress,$applicationname);
	echo "Email Confirmation sent. \r\n";
}

if ($delete && $checkmail_purge) {
	if (strlen(strstr($checkmail_imap_server,"imap.gmail.com"))>0){
		imap_mail_move($imap,$current_message,'[Gmail]/Trash');
	}
	else {
		// for non-gmail
		imap_delete($imap,$current_message);
	}
	echo "Deleting Email... \r\n";
}


/*  experimental postprocessing of html (handling stylesheets, inline images?)
 * 
 *  basic html *should* be easy to handle, but I recommend sticking to plain text alts. 
 *  many issues are created by very complex html mails, scripts/links, necessary stylesheets that conflict with RS if included in display, etc...
 *  Even simple html emails can become unsupportable when e-mail clients do not write inline styles.
 *  This code is left here only to suggest some of the difficult problems that arise in trying to make use of HTML parts.
 * 
function get_tag( $attr, $value, $xml, $tag=null ) {
  if( is_null($tag) )
    $tag = '\w+';
  else
    $tag = preg_quote($tag);

  $attr = preg_quote($attr);
  $value = preg_quote($value);

  $tag_regex = "/<(".$tag.")[^>]*$attr\s*=\s*".
                "(['\"])$value\\2[^>]*>(.*?)<\/\\1>/";

  preg_match_all($tag_regex,
                 $xml,
                 $matches,
                 PREG_PATTERN_ORDER);

  return $matches[3];
}

// final pass for inline files
// fix replace inline images with new links
if (isset($body_html)){
foreach ($refs as $ref){
	echo "for resource $ref, ";
	foreach ($files as $file){
		if (isset($file['id'])){
			echo "trying to match ".$file['id'];
			$colpath=get_resource_path($file['ref'],false,"col",false);
			//print_r(get_tag("src","cid:".$file['id'],$body_html));
			$body_html=str_replace("cid:".$file['id'],$colpath,$body_html);	
		}
	}
	update_field($ref,$checkmail_body_field,$body_html);
}
}
*/



imap_close($imap);
clear_process_lock("checkmail");

echo "done\r\n";
