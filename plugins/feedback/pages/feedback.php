<?php
include "../../../include/db.php";


if (array_key_exists("user",$_COOKIE))
   	{
	# Check to see if this user is logged in.
	$session_hash=$_COOKIE["user"];
	$loggedin=sql_value("select count(*) value from user where session='$session_hash' and approved=1 and timestampdiff(second,last_active,now())<(30*60)",0);
	if ($loggedin>0 || $session_hash=="|") // Also checks for dummy cookie used in external authentication
        	{
	        # User is logged in. Proceed to full authentication.
		include "../../../include/authenticate.php";
		}
	}

if (!isset($userref))
	{
	# User is not logged in. Fetch username from posted form value.
	$username=getval("username","");
	$usergroupname="(Not logged in)";
	$userfullname="";
	$anonymous_login=$username;
	$pagename="terms";
	$plugins=array();
	}
	
include "../../../include/general.php";

$error="";
$errorfields=array();
$sent=false;

if (getval("send","")!="")
	{
	$csvheaders="\"date\"";
	$csvline="\"" . date("Y-m-d") . "\"";
	$message="Date: ". date("Y-m-d")."\n";
	for ($n=1;$n<=count($feedback_questions);$n++)
		{
		$type=$feedback_questions[$n]["type"];	
		
		if ($type!=4) # Do not run for labels
			{
			$value=getval("question_" . $n,"");
			
			# Check required fields
			if ($type==3 && trim($value)=="") {$error=$lang["requiredfields"];$errorfields[]=$n;}
				
			if ($type==5)
				{
				# Multi select: contruct value from options
				$s=explode(",",$feedback_questions[$n]["options"]);
				$value="";
				for ($m=0;$m<count($s);$m++)
					{
					if (getval("question_" .$n . "_" . $m,"")!="") # Option is selected, add to value
						{
						if ($value!="") {$value.=",";}
						$value.=$s[$m];
						}
					}
				}
	
			# Append to CSV line
			if ($csvline!="") {$csvline.=",";}
			if ($csvheaders!="") {$csvheaders.=",";}
			$csvline.="\"" . str_replace("\"","'",$value) . "\"";
			$csvheaders.="\"".str_replace("\"","'",str_replace("\n","",$feedback_questions[$n]['text']))."\"";
			if ($value!=""){$message.=$feedback_questions[$n]['text'].": \n". $value."\n\n";}
			}
		}
	
	# Append user name and group to CSV file
	$message="\n\nUser: " .$username."\nUsergroup: ".$usergroupname."\n".$message;
	$csvline="\"$username\",\"$usergroupname\"," . $csvline;
	$csvheaders="\"username\",\"usergroupname\"," . $csvheaders;
	if ($error=="")
		{
		# Write results.
		$sent=true;
		$f=fopen("../data/results.csv","r+b");
		
		# avoid writing headers again
		$line = file('../data/results.csv');
		if (isset($line[0])){$line=$line[0];} 
		if ($line==$csvheaders."\n"){$csvheaders="";} else {$csvheaders=$csvheaders."\n";}
		
		fwrite($f, $csvheaders .file_get_contents('../data/results.csv').$csvline."\n" );
		fclose($f);
		
		# install email template
		//sql_query("delete from site_text where name='emailfeedback'");
		$result=sql_query("select * from site_text where page='all' and name='emailfeedback'");
		if (count($result)==0){$wait=sql_query('insert into site_text (page,name,text,language) values ("all","emailfeedback","[img_storagedir_/../gfx/whitegry/titles/title.gif] [message] [text_footer][attach_../data/results.csv]","en-US")');}
		
		# send form results and results.csv to email_notify
		if ($use_phpmailer){
			$templatevars['message']=$message . "Survey results attached\n\n";
			send_mail($email_notify,$username." has submitted feedback for ".$applicationname,$message,"","","emailfeedback",$templatevars);
			} 
		}

	}

include "../../../include/header.php";
?>
<style>
h2 {font-size:18px;}
<?php if (!isset($userref)) { ?>
#SearchBox {display:none;}
<?php } ?>
</style>

<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["feedback_user_survey"]?></h1>

<?php if ($sent) { ?><p><?php echo $lang["feedback_thank_you"]?></p><?php 
} else { ?>

<form method=post action="<?php echo $baseurl_short?>plugins/feedback/pages/feedback.php">
<?php if ($error) { ?><div class="FormError">!! <?php echo $error ?> !!</div><br /><?php } ?>
<?php 
	
for ($n=1;$n<=count($feedback_questions);$n++)
	{
	$type=$feedback_questions[$n]["type"];
	$text=$feedback_questions[$n]["text"];	
	
	if ($type==4)
		{
		# Label type - just output the HTML.
		echo $feedback_questions[$n]["text"];
		}
	else
		{
		if (in_array($n,$errorfields)) {$text="<strong style='color:red;'>" . $text . "</strong>";}	# Highlight fields with errors.
		?>
		<div class="Question" style="border-top:none;">
		<label style="width:250px;padding-right:5px;" for="question_<?php echo $n?>"><?php echo $text;?></label>
		
		<?php if ($type==1) {  # Normal text box
		?>
		<input type=text name="question_<?php echo $n?>" id="question_<?php echo $n?>" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped("question_" . $n,""))?>">
		<?php } ?>

		<?php if ($type==2) { # Large text box 
		?>
		<textarea name="question_<?php echo $n?>" id="question_<?php echo $n?>" class="stdwidth" rows="5"><?php echo htmlspecialchars(getvalescaped("question_" . $n,""))?></textarea>
		<?php } ?>

		<?php if ($type==3) { # Single Select List
				?>
		<table cellpadding=2 cellspacing=0>
		<?php foreach (explode(",",$feedback_questions[$n]["options"]) as $option)
			{
			?>
			<tr><td width="1"><input type="radio" name="question_<?php echo $n?>" value="<?php echo htmlspecialchars($option);?>" <?php if ($option==getvalescaped("question_" . $n,"")) { ?>checked<?php } ?>></td><td><?php echo htmlspecialchars($option);?></td></tr>
			<?php
			}
		?>
		</table>
		<?php } ?>
		
		<?php if ($type==5) { # Multi Select List
		?>
		<table cellpadding=2 cellspacing=0>
		<?php $opt=0;foreach (explode(",",$feedback_questions[$n]["options"]) as $option)
			{
			?>
			<tr><td width="1"><input type="checkbox" name="question_<?php echo $n?>_<?php echo $opt?>" value="yes" <?php if (getvalescaped("question_" . $n . "_" . $opt,"")!="") { ?>checked<?php } ?>></td><td><?php echo htmlspecialchars($option);?></td></tr>
			<?php
			$opt++;
			}
		?>
		</table>
		<?php } ?>

		
		<div class="clearerleft"> </div>
		</div>
		<?php
		}
	}

if (!isset($userref))
	{
	# User is not logged in. Ask them for their user name
	?>
	<br><br>
		<div class="Question" style="border-top:none;">
		<label style="width:250px;padding-right:5px;" for="username"><?php echo $lang["feedback_your_full_name"]?></label>
		
		<input type=text name="username" id="username" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped("username",""))?>">
		<div class="clearerleft"> </div>
		</div>
	<?php
	}
?>

<div class="QuestionSubmit">
<?php if ($error) { ?><div class="FormError">!! <?php echo $error ?> !!</div><br /><?php } ?>
<label style="width:250px;" for="buttons"> </label>			
<input name="send" type="submit" onclick="return CentralSpacePost(this,true);" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["send"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" />
</div>
</form>
<?php } ?>

</div>
<?php include "../../../include/footer.php"; ?>
