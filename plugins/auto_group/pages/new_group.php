<?php

include '../../../include/db.php';
include '../../../include/general.php';
include '../../../include/authenticate.php';
if (!checkperm('u')){
	exit ($lang['error-permissiondenied']);
}
include '../../../include/resource_functions.php';
include '../include/auto_group_functions.php';

$auto_groups=auto_group_get_group_templates($auto_group_templates);
if(count($auto_groups)==1){$single_group_option=true;}

$group_parents=auto_group_get_group_parents();
if(count($group_parents)==1){$single_parent_option=true;}

$default_group=sql_value("select ref value from usergroup where name='$auto_group_template_default'",'');

# ----- Save ----- #
if (getval("save","")!=""){
	$errors=false;
	
	$group_name=getvalescaped("auto_group_name","");
	if($group_name==''){
		$errors=true;
		$error_name=$lang['auto_group_no_name'];
	}
	$group_username=getvalescaped("username",'');
	$group_copy=getvalescaped("auto_group_copy",0);
	if($group_copy==0){
		$errors=true;
		$error_copy=$lang['auto_group_no_copy'];
	}
	$group_parent=getvalescaped("auto_group_parent",$auto_group_parent);
	
	# check to see if group name exists under parent
	$c=sql_value("select count(*) value from usergroup where name='$group_name' and parent='$group_parent'",0);
	if($c>0){
		$errors=true;
		$error_name=$lang['auto_group_group_exists'];
	}
	
	if($errors==false){
		$new=auto_group_create_new_group($group_name,$group_username,$group_copy,$group_parent);
		hook("afterusercreated"); # trying to preserve this hook from base on team_user.php
		if(!empty($new)){
			redirect($baseurl_short."pages/team/team_user_edit.php?ref=".$new."&backurl=".$baseurl_short."pages/team/team_user.php");
		}
	}
}

$auto_group_field_name=get_field($auto_group_field);
$auto_group_field_name=$auto_group_field_name['title'];

include '../../../include/header.php';
?>

<div class="BasicsBox"> 
	<h2>&nbsp;</h2>
	<h1><?php echo $lang["auto_group_add_new_group"]?></h1>
	
	<?php if(isset($errors) && $errors=true){
		?><div class="FormError"><?php echo $lang['auto_group_new_group_error']?></div><?php
	} ?>
	
	<form method="post">
		
		<div class="Question">
			<label for="auto_group_name"><?php echo str_replace("%c",$auto_group_field_name,$lang["auto_group_name"])?> <sup>*</sup></label>
			<input type=text name="auto_group_name" id="auto_group_name" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped('auto_group_name',''))?>">
			<div class="clearerleft"> </div>
			<?php if (isset($error_name)) { ?><div class="FormError">!! <?php echo $error_name?> !!</div><?php } ?>
		</div>
		
		<div class="Question">
			<label for="auto_group_copy"><?php echo $lang["auto_group_copy"]?> <sup>*</sup></label>
			<select name="auto_group_copy" id="auto_group_copy" class="stdwidth">
				<?php if(!isset($single_group_option) && !isset($auto_group_template_default)){
					?><option value="0"><?php echo "Select..."?></option><?php
				}
				for($n=0;$n<count($auto_groups);$n++){
					?><option <?php if($auto_groups[$n]['ref']==getvalescaped("auto_group_copy",$default_group)){?> selected <?php } ?> value="<?php echo $auto_groups[$n]['ref']?>"><?php echo htmlspecialchars($auto_groups[$n]["name"])?></option><?php
				}?>
			</select>
			<div class="clearerleft"> </div>
			<?php if (isset($error_copy)) { ?><div class="FormError">!! <?php echo $error_copy?> !!</div><?php } ?>
		</div>
		
		<div class="Question">
			<label for="auto_group_parent"><?php echo $lang["auto_group_parent"]?> <sup></sup></label>
			<?php if(count($group_parents)!=0){?>
				<select name="auto_group_parent" id="auto_group_parent" class="stdwidth">
					<option value="0"><?php echo "Select..."?></option><?php
					for($n=0;$n<count($group_parents);$n++){
						?><option value="<?php echo $group_parents[$n]['ref']?>"><?php echo htmlspecialchars($group_parents[$n]["name"])?></option><?php
					}?>
				</select><?php
				}
				else{?>
					<div><?php echo $lang['auto_group_error_no_parents']?></div><?php
				}?>
			<div class="clearerleft"> </div>
		</div>
		
	<?php if (!hook('replacesubmitbuttons')) { ?>
		<div class="QuestionSubmit">
			<input name="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
			<input name="save" type="submit" value="<?php echo $lang["save"]?>"/>
			<div class="clearerleft"></div>
		</div>
	<?php } ?>
	</form>
</div>

<?php
include '../../../include/footer.php';
?>
