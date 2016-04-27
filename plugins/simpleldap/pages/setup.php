<?php
include "../../../include/db.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}
include "../../../include/general.php";

$plugin_name="simpleldap";
$upload_status="";

if (getval('upload','')!='')
       {
       $upload_status=handle_rsc_upload($plugin_name);
       }
elseif (getval("submit","")!="" || getval("save","")!="" || getval("testConnflag","")!="")
	{

	$simpleldap['fallbackusergroup'] = getvalescaped('fallbackusergroup','');
	$simpleldap['domain'] = getvalescaped('domain','');
	$simpleldap['emailsuffix'] = getvalescaped('emailsuffix','');
	$simpleldap['ldapserver'] = getvalescaped('ldapserver','');
	$simpleldap['port'] = getvalescaped('port','');
	$simpleldap['basedn']= getvalescaped('basedn','');
	$simpleldap['loginfield'] = getvalescaped('loginfield','');
	$simpleldap['usersuffix'] = getvalescaped('usersuffix','');
	$simpleldap['createusers'] = getvalescaped('createusers','');
	$simpleldap['ldapgroupfield'] = getvalescaped('ldapgroupfield','');
	$simpleldap['email_attribute'] = getvalescaped('email_attribute','');
	$simpleldap['phone_attribute'] = getvalescaped('phone_attribute','');
	$simpleldap['update_group'] = getvalescaped('update_group','');
	$simpleldap['create_new_match_email'] = getvalescaped('create_new_match_email','');
	$simpleldap['allow_duplicate_email'] = getvalescaped('allow_duplicate_email','');
	$simpleldap['notification_email'] = getvalescaped('notification_email','');
	$simpleldap['ldaptype'] = getvalescaped('ldaptype','');
	
	
	
	$ldapgroups = $_REQUEST['ldapgroup'];
	$rsgroups = $_REQUEST['rsgroup'];
	$priority = $_REQUEST['priority'];

	if (count($ldapgroups) > 0)
		{
		sql_query('delete from simpleldap_groupmap where rsgroup is not null');
		}

	for ($i=0; $i < count($ldapgroups); $i++)
		{
		if ($ldapgroups[$i] <> '' && $rsgroups[$i] <> '' && is_numeric($rsgroups[$i]))
			{
			$query = "replace into simpleldap_groupmap (ldapgroup,rsgroup,priority) values ('" . escape_check($ldapgroups[$i]) . "','" . $rsgroups[$i] . "' ," . (($priority[$i]!="")?"'" . $priority[$i] . "'":"NULL") .")";
			sql_query($query);		
			}
		} 


	//$config['simpleldap'] = $simpleldap;
	if (getval("submit","")!="" || getval("save","")!="")
		{
		set_plugin_config("simpleldap",array("simpleldap"=>$simpleldap));
		}
		
	if (getval("submit","")!="")
		{
		redirect("pages/team/team_plugins.php");
		}
	}



// retrieve list if groups for use in mapping dropdown
$rsgroups = sql_query('select ref, name from usergroup order by name asc');

include "../../../include/header.php";

// if some of the values aren't set yet, fudge them so we don't get an undefined error
// this may be important for updates to the plugin that introduce new variables
foreach (array('ldapserver','domain','port','basedn','loginfield','usersuffix','emailsuffix','fallbackusergroup','email_attribute','phone_attribute','update_group','create_new_match_email','allow_duplicate_email','notification_email','ldaptype') as $thefield){
	if (!isset($simpleldap[$thefield])){
		$simpleldap[$thefield] = '';
	}
}


if(getval("testConnflag","")!="" && getval("submit","")=="" && getval("save","")=="")
		{
		?>
		<div class="BasicsBox"> 
		<?php
		echo "<h1>" . $lang["simpleldap_test"] . " " . $simpleldap['ldapserver'] . ":" . $simpleldap['port'] ."</h1>";
		
		debug("LDAP - Connecting to LDAP server: " . $simpleldap['ldapserver'] . " on port " . $simpleldap['port']);
		$dstestconn=  @fsockopen($simpleldap['ldapserver'], $simpleldap['port'], $errno, $errstr, 5);
		
		if($dstestconn)
			{
			fclose($dstestconn);
			debug("LDAP - Connected to LDAP server ");
			?>
			<div class="Question">
			<label for="ldapuser"><?php echo $lang["simpleldap_username"] ?></label><input id='ldapuser' type="text" name='ldapuser'>
			</div>
			
			<div class="Question">
			<label for="ldappassword"><?php echo $lang["simpleldap_password"] ?></label><input id='ldappassword' type="password" name='ldappassword'>
			</div>		

			<?php
			if(!isset($simpleldap['ldaptype']) || $simpleldap['ldaptype']==1) 
				{?>
				<div class="Question">
				<label for="ldapdomain"><?php echo $lang["simpleldap_domain"] ?></label>
					<select id='ldapdomain' name='ldapdomain'>
					<?php
					$binddomains=explode(";",$simpleldap['domain']);
					foreach ($binddomains as $binddomain)
						{
						echo "<option value'" . htmlspecialchars($binddomain)  . "'>" . htmlspecialchars($binddomain) . "</option>";
						}				
					?>
					</select>
				</div>	
				<?php
				}
			}
			?>
		
		<input type="submit" onClick="simpleldap_test();return false;" name="testauth" value="<?php echo $lang["simpleldap_test_auth"]; ?>" <?php if (!$dstestconn){echo "disabled='true'";} ?>>		
		<input type="submit" onClick="ModalClose();return false;" name="cancel" value="<?php echo $lang["cancel"]; ?>">
		
		<br /><br />
		<!--<textarea id="simpleldaptestresults" class="Fixed" rows=15 cols=100 style="display: none; width: 100%; border: solid 1px;" ></textarea>-->
		
		<script>
		function simpleldap_test()
			{
			jQuery('.resultrow').remove();
			jQuery('#testgetuserresult').html('');
			testurl= '<?php echo get_plugin_path("simpleldap",true) . "/pages/ajax_test_auth.php";?>',
			user = jQuery('#ldapuser').val();
			password = jQuery('#ldappassword').val();
			userdomain = jQuery('#ldapdomain').val();
			var post_data = {
				ajax: true,
				ldapserver: '<?php echo htmlspecialchars($simpleldap['ldapserver']) ?>',
				port: '<?php echo htmlspecialchars($simpleldap['port']) ?>',
				ldaptype: '<?php echo htmlspecialchars($simpleldap['ldaptype']) ?>',
				domain: '<?php echo htmlspecialchars($simpleldap['domain']) ?>',
				loginfield: '<?php echo htmlspecialchars($simpleldap['loginfield']) ?>',				
				basedn: '<?php echo htmlspecialchars($simpleldap['basedn']) ?>',	
				ldapgroupfield: '<?php echo htmlspecialchars($simpleldap['ldapgroupfield']) ?>',
				email_attribute: '<?php echo htmlspecialchars($simpleldap['email_attribute']) ?>',
				phone_attribute: '<?php echo htmlspecialchars($simpleldap['phone_attribute']) ?>',		
				ldapuser: user,
				ldappassword: password,
				userdomain: userdomain				
				
			};
			
			jQuery.ajax({
				  type: 'POST',
				  url: testurl,
				  data: post_data,
				  dataType: 'json', 
				  success: function(response){
						if(response.complete === true){
						
						jQuery('#testbindresult').html(response.bindsuccess);
						if(response.success){
							jQuery('#testgetuserresult').html('<?php echo $lang["status-ok"]; ?> (' + response.binduser + ')');
						}
						else {
							jQuery('#testgetuserresult').html('<?php echo $lang["status-fail"]; ?>');
						}
							
												
						returnmessage = response.message;
						if(response.success) {						
							returnmessage += "<tr class='resultrow'><td><?php echo $lang["email"]; ?>: </td><td>" + response.email + "</td></tr>";
							returnmessage += "<tr class='resultrow'><td><?php echo $lang["simpleldap_telephone"]; ?>: </td><td>" + response.phone + "</td></tr>";
							returnmessage += "<tr class='resultrow'><td><?php echo $lang["simpleldap_memberof"]; ?>";
							for (var i = 0, len = response.memberof.length; i < len; i++) {
							  returnmessage += "</td><td>" + response.memberof[i]  + "</td></tr><tr class='resultrow'><td>";
							}		
							returnmessage += "</td></tr>";
						}
						jQuery('#blankrow').before(returnmessage);
					}
					else if(response.complete === false && response.message && response.message.length > 0) {
						jQuery('#testgetuserdata').html('<?php echo $lang["error"]; ?> : ' + response.message);
					}
					else {
						jQuery('#testgetuserdata').html('<?php echo $lang["error"]; ?>');
					}
				},
				  error: function(xhr, textStatus, error){
					  jQuery('#simpleldaptestresults').html(textStatus + ":&nbsp;" + xhr.status    + "&nbsp;" + error  );
				}
			});
			
			}
		
		</script>
		<?php
		
		echo "<table class='InfoTable' style='width: 100%' ><tbody>";
		echo "<tr><td width='40%'><h2>" .  $lang["simpleldap_test_title"] . "</h2></td><td width='60%'><h2>" . $lang["simpleldap_result"] . "</h2></td></tr>";
		echo "<tr><td>" . $lang["simpleldap_connection"] . " " . $simpleldap['ldapserver'] . ":" . $simpleldap['port'] . "</td><td id='testconnectionresult'>" . (($dstestconn)?$lang["status-ok"]:$lang["status-fail"]) . "</td></tr>";
		echo "<tr><td>" . $lang["simpleldap_bind"] . "</td><td id='testbindresult'></td></tr>";
		echo "<tr><td>" . $lang["simpleldap_retrieve_user"] . "</td><td id='testgetuserresult'></td></tr>";
		echo "<tr id='blankrow'><td colspan='2' ></td></tr>";				
		echo "</tbody></table>";
		?>
		</div>
		<?php
		exit();
		}	
		


?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
 
<?php 
if (!function_exists('ldap_connect'))
	{
	echo "<div class=\"PageInformal\">" . $lang["simpleldap_externsion_required"] . "</div>";
	}
	
?>
 <h1>SimpleLDAP Configuration</h1>
  
 <form id="form1" name="form1" enctype= "multipart/form-data" method="post" action="<?php echo get_plugin_path("simpleldap",true) . "/pages/setup.php";?>">

<?php echo config_single_select("ldaptype", $lang['simpleldap_ldaptype'], $simpleldap['ldaptype'], array(1=>"Active Directory",2=>"Oracle Directory")); ?>
<?php echo config_text_field("ldapserver",$lang['ldapserver'],$simpleldap['ldapserver'],60);?>
<?php echo config_text_field("domain",$lang['domain'],$simpleldap['domain'],60);?>
<?php echo config_text_field("emailsuffix",$lang['emailsuffix'],$simpleldap['emailsuffix'],60);?>
<?php echo config_text_field("email_attribute",$lang['email_attribute'],$simpleldap['email_attribute'],60);?>
<?php echo config_text_field("phone_attribute",$lang['phone_attribute'],$simpleldap['phone_attribute'],60);?>
<?php echo config_text_field("port",$lang['port'],$simpleldap['port'],5);?>
<?php echo config_text_field("basedn",$lang['basedn'],$simpleldap['basedn'],60);?>
<?php echo config_text_field("loginfield",$lang['loginfield'],$simpleldap['loginfield'],30);?>
<?php echo config_text_field("usersuffix",$lang['usersuffix'],$simpleldap['usersuffix'],30);?>
<?php echo config_text_field("ldapgroupfield",$lang['groupfield'],$simpleldap['ldapgroupfield'],30);?>
<?php echo config_boolean_field("createusers",$lang['createusers'],$simpleldap['createusers'],30);?>
<?php echo config_boolean_field("create_new_match_email",$lang['simpleldap_create_new_match_email'],$simpleldap['create_new_match_email'],30);?>
<?php echo config_boolean_field("allow_duplicate_email",$lang['simpleldap_allow_duplicate_email'],$simpleldap['allow_duplicate_email'],30);?>
<?php echo config_boolean_field("update_group",$lang['simpleldap_update_group'],$simpleldap['update_group'],30);?>
<?php echo config_text_field("notification_email",$lang['simpleldap_notification_email'],$simpleldap['notification_email'],60);?>

<div class="Question">
	<label for="fallbackusergroup"><?php echo $lang['fallbackusergroup']; ?></label>
	<select name='fallbackusergroup'><option value=''></option>
	<?php 	
		foreach ($rsgroups as $rsgroup){
			echo  "<option value='" . $rsgroup['ref'] . "'";
			if ($simpleldap['fallbackusergroup'] == $rsgroup['ref']){
				echo " selected";
			}
			echo ">". $rsgroup['name'] . "</option>\n";
		} 
 	?></select>
</div>
<div class="clearerleft"></div>



<div class="Question">
<h3><?php echo $lang['ldaprsgroupmapping']; ?></h3>
<table id='groupmaptable'>
<tr><th>
<strong><?php echo $lang['ldapvalue']; ?></strong>
</th><th>
<strong><?php echo $lang['rsgroup']; ?></strong>
</th><th>
<strong><?php echo $lang['simpleldappriority']; ?></strong>
</th>
</tr>

<?php
	$grouplist = sql_query('select ldapgroup,rsgroup, priority from simpleldap_groupmap order by priority desc');
	for($i = 0; $i < count($grouplist)+1; $i++){
		if ($i >= count($grouplist)){
			$thegroup = array();
			$thegroup['ldapgroup'] = '';
			$thegroup['rsgroup'] = '';
			$thegroup['priority'] = '';
			$rowid = 'groupmapmodel';
		} else {
			$thegroup = $grouplist[$i];
			$rowid = "row$i";
		}
?>
<tr id='<?php echo $rowid; ?>'>
   <td><input type='text' name='ldapgroup[]' value='<?php echo $thegroup['ldapgroup']; ?>' /></td>
   <td><select name='rsgroup[]'><option value=''></option>
	<?php 	
		foreach ($rsgroups as $rsgroup){
			echo  "<option value='" . $rsgroup['ref'] . "'";
			if ($thegroup['rsgroup'] == $rsgroup['ref']){
				echo " selected";
			}
			echo ">". $rsgroup['name'] . "</option>\n";
		} 
 	?></select>
    </td>
    <td><input type='text' name='priority[]' value='<?php echo $thegroup['priority']; ?>' /></td>
</tr>
<?php } ?>
</table>

<a onclick='addGroupMapRow()'><?php echo $lang['addrow']; ?></a>
</div>


<div class="Question">
	<input type="hidden" name="testConnflag" id="testConnflag" value="" />
	<input type="submit" name="testConn" onclick="jQuery('#testConnflag').val('true');ModalPost(this.form,true);return false;" value="<?php echo $lang['simpleldap_test'] ?>" />
 </div>
<div class="clearerleft"></div>

<div class="Question">
	<label for="submit"></label>
<input type="submit" name="save" value="<?php echo $lang["save"]?>">
<input type="submit" name="submit" value="<?php echo $lang["plugins-saveandexit"]?>">

</div>
<div class="clearerleft"></div>


<?php
    display_rsc_upload($upload_status);
?>

</form>
</div>	

<script language="javascript">
        function addGroupMapRow() {
 
            var table = document.getElementById("groupmaptable");
 
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);
 
            row.innerHTML = document.getElementById("groupmapmodel").innerHTML;
        }
</script> 



<?php include "../../../include/footer.php";
