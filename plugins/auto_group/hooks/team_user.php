<?php
function HookAuto_groupTeam_userReplace_create_user(){
	?><!-- HookAuto_groupTeam_userReplace_create_user --><?php
	# replaces base create user with option to create new group as well
	global $baseurl_short,$lang,$auto_group_field;
	$auto_group_field_data=get_resource_type_field($auto_group_field);
	$auto_group_field_name=$auto_group_field_data['title'];
	?>
	<div class="BasicsBox">
    	<form method="post" action="<?php echo $baseurl_short?>pages/team/team_user.php">
			<div class="Question">
				<label for="newuser"><?php echo $lang["createuserwithusername"]?></label>
				<div class="tickset">
					<div class="Inline">
						<input type=text name="newuser" id="newuser" maxlength="100" class="shrtwidth" />
					</div>
				</div>
				<div class="clearerleft"> </div>
				<div class="auto_group_checkbox">
					<label><?php echo $lang["auto_group_create"]?></label>
					<input name="auto_group" type="checkbox" value="yes">
				</div>
				<div class="clearerleft"> </div>
			 	<div class="auto_group_submit">
			 		<input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" />
			 	</div>
				<div class="clearerleft"> </div>
			</div>
		</form>
	</div>
	<?php
	return true;
}
function HookAuto_groupTeam_userReplace_create_user_save(){
	# adjusts the new user creation to navigate to auto_group create page
	global $lang,$baseurl_short;
	if (getval("newuser","")!="" && getval("auto_group","")=="yes"){
		$new=getvalescaped("newuser","");
		# Username already exists?
		$c=sql_value("select count(*) value from user where username='$new'",0);
		if($c>0){
			$error=$lang["useralreadyexists"];
			return false;
		}
		else{
			redirect($baseurl_short."plugins/auto_group/pages/new_group.php?username=" . $new);
		}
	}
}
