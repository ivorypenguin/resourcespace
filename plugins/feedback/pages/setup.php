<?php
include "../../../include/db.php";
include "../../../include/authenticate.php"; if (!checkperm("a")) {exit ("Permission denied.");}
include "../../../include/general.php";

function file_newname($path, $filename){
    if ($pos = strrpos($filename, '.')) {
           $name = substr($filename, 0, $pos);
           $ext = substr($filename, $pos);
    } else {
           $name = $filename;
    }

    $newpath = $path.'/'.$filename;
    $newname = $filename;
    $counter = 0;
    while (file_exists($newpath) && file_get_contents($newpath)!="") {
           $newname = $name .'_'. $counter . $ext;
           $newpath = $path.'/'.$newname;
           $counter++;
     }

    return $newname;
}


if (!isset($feedback_prompt_text)) {$feedback_prompt_text="";}

if (getval("submit","")!="" || getval("add","")!="")
	{
	rename('../data/results.csv','../data/'.file_newname('../data/','results.csv'));
	touch('../data/results.csv');
	chmod('../data/results.csv',0777);
	
	$f=fopen("../config/config.php","w");
	fwrite($f,"<?php\n\n\$feedback_questions=array();");

	fwrite($f,"\n\n\$feedback_prompt_text=\"" . str_replace("\"","\\\"",getval("feedback_prompt_text","")) . "\";\n\n");
	
	$readfrom=0;
	if (getval("delete_1","")!="") {$readfrom++;} # Delete first question.
			
	for ($n=1;$readfrom<count($feedback_questions);$n++)
		{
		$readfrom++;

		# Deleting next question? Skip ahead
		if (getval("delete_" . ($readfrom),"")=="")
			{	
			# Save question
			fwrite ($f,"\$feedback_questions[" . $n . "]['text']=\"" . str_replace("\"","\\\"",getval("text_" . $readfrom,"")) . "\";\n");
			fwrite ($f,"\$feedback_questions[" . $n . "]['type']=" . getval("type_" . $readfrom,1) . ";\n");
			fwrite ($f,"\$feedback_questions[" . $n . "]['options']=\"" . str_replace("\"","\\\"",getval("options_" . $readfrom,"")) . "\";\n");
			}		
		else
			{
			$n--;
			}

		# Add new question after this one?
		if (getval("add_" . $readfrom,"")!="")
			{
			$n++;
			fwrite ($f,"\$feedback_questions[" . $n . "]['text']=\"\";\n");
			fwrite ($f,"\$feedback_questions[" . $n . "]['type']=1;\n");
			fwrite ($f,"\$feedback_questions[" . $n . "]['options']=\"\";\n");
			}
		}
	
	$add="";
	if (getval("add","")!="")
		{
		# Add a new question
		fwrite ($f,"\$feedback_questions[" . $n . "]['text']=\"\";\n");
		fwrite ($f,"\$feedback_questions[" . $n . "]['type']=1;\n");
		fwrite ($f,"\$feedback_questions[" . $n . "]['options']=\"\";\n");
		$add="#add";
		}

	fwrite($f,"?>");
	fclose($f);
	redirect("plugins/feedback/pages/setup.php?nc=". time() . $add);
	}


include "../../../include/header.php";
?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["feedback_user feedback_configuration"]?></h1>

  <div class="VerticalNav">
 <form id="form1" name="form1" method="post" action="">

<p><?php echo $lang["feedback_pop-up_prompt_box_text"]?><br />
<textarea rows=6 cols=50 style="width:600px;" name="feedback_prompt_text"><?php echo $feedback_prompt_text ?></textarea>
</p>
<h2><?php echo $lang["feedback_questions"]?></h2>
<hr />

<?php for ($n=1;$n<=count($feedback_questions);$n++)
	{
	?>
   <p><?php echo $lang["feedback_type"]?>
   <select name="type_<?php echo $n?>" style="width:150px;">
   <option value="1" <?php if ($feedback_questions[$n]["type"]==1) { ?>selected<?php } ?>><?php echo $lang["feedback_small_text_field"]?></option>
   <option value="2" <?php if ($feedback_questions[$n]["type"]==2) { ?>selected<?php } ?>><?php echo $lang["feedback_large_text_field"]?></option>
   <option value="3" <?php if ($feedback_questions[$n]["type"]==3) { ?>selected<?php } ?>><?php echo $lang["feedback_list-single_selection"]?></option>
   <option value="5" <?php if ($feedback_questions[$n]["type"]==5) { ?>selected<?php } ?>><?php echo $lang["feedback_list-multiple_selection"]?></option>
   <option value="4" <?php if ($feedback_questions[$n]["type"]==4) { ?>selected<?php } ?>><?php echo $lang["feedback_label"]?></option>
   </select>
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <input type="checkbox" name="delete_<?php echo $n?>" value="yes"><?php echo $lang["feedback_delete_this_question"]?>
   <input type="checkbox" name="add_<?php echo $n?>" value="yes"><?php echo $lang["feedback_add_new_question_after"]?>
	</p>

	<p>
<?php echo $lang["feedback_text-html"]?><br/>
   <textarea rows=3 cols=50 style="width:600px;" name="text_<?php echo $n?>"><?php echo $feedback_questions[$n]["text"] ?></textarea>
   </p>
	
	<p><?php echo $lang["feedback_options-comma_separated"]?> <br />
   	<textarea rows=2 cols=50 style="width:600px;" name="options_<?php echo $n?>"><?php echo $feedback_questions[$n]["options"] ?></textarea>
   	</p>
   
	<hr />
	<?php
	}
?>
<br /><br /><a name="add"></a>
<input type="submit" name="add" value="<?php echo $lang["feedback_add_new_field"]?>">   

<input type="submit" name="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">   

<br/><br/>
<p>&lt;&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/team/team_plugins.php"><?php echo $lang["feedback_back_to_plugin_manager"]?></a></p>

</form>
</div>

<?php include "../../../include/footer.php"; ?>	
