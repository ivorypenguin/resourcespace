<?php
include "../../../include/db.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ($lang['error-permissiondenied']);}
include "../../../include/general.php";

$usergroups = sql_query("SELECT ref,name FROM usergroup");
/* Set the following debug flag to true for more debugging information
*/
$ldap_debug = true;


// for test
//$ldapauth['ldapgroupfield'] = 'memberUid';

if (getval("submit","")!="") {

	$ldapauth=array();
	$ldapauth['enable'] = isset($_POST['enable']);
        $ldapauth['ldapserver'] = $_POST['ldapserver'];
	$ldapauth['port'] = $_POST['port'];
	$ldapauth['basedn']= $_POST['basedn'];
	$ldapauth['loginfield'] = $_POST['loginfield'];
	$ldapauth['usersuffix'] = $_POST['usersuffix'];
	$ldapauth['createusers'] = isset($_POST['createusers']);
	$ldapauth['groupbased'] = isset($_POST['groupbased']);
	$ldapauth['newusergroup'] = $_POST['newusergroup'];
	$ldapauth['ldapusercontainer'] = $_POST['ldapusercontainer'];
	$ldapauth['ldaptype'] = $_POST['ldaptype'];
	$ldapauth['rootdn'] = $_POST['rootdn'];
	$ldapauth['rootpass'] = $_POST['rootpass'];
	$ldapauth['addomain'] = $_POST['addomain'];
	$ldapauth['ldapgroupcontainer'] = $_POST['ldapgroupcontainer'];
	$ldapauth['ldapmemberfield'] = $_POST['ldapmemberfield'];
	$ldapauth['ldapmemberfieldtype'] = $_POST['ldapmemberfieldtype'];
	
	if (isset($_POST['ldapGroupName']))
	{
		$ldapGroupCount = count($_POST['ldapGroupName']);
		
		for ($ti= 0; $ti < $ldapGroupCount; $ti++)
		{
			$grpName = $_POST['ldapGroupName'][$ti];
			$ldapauth['groupmap'][$grpName]['rsGroup'] = $_POST['ldapmaptors'][$grpName];
			$ldapauth['groupmap'][$grpName]['enabled'] = isset($_POST['ldapGroupEnable'][$grpName]);
		}
	}
		
	set_plugin_config("posixldapauth", $ldapauth);

	redirect("pages/team/team_home.php");

} else {
	
	$ldapauth = get_plugin_config("posixldapauth");
	if ($ldapauth == null){
	    $ldapauth['enable'] = false;
	    $ldapauth['ldapserver'] = 'localhost';
	    $ldapauth['port'] = '389';
	    $ldapauth['basedn']= 'dc=mydomain,dc=net';
	    $ldapauth['loginfield'] = 'uid';
	    $ldapauth['usersuffix'] = '';
	    $ldapauth['createusers'] = true;
	    $ldapauth['groupbased'] = false;
	    $ldapauth['newusergroup'] = '2';
	    $ldapauth['ldapusercontainer'] = 'cn=users';
	    $ldapauth['ldaptype'] = 0;
		$ldapauth['rootdn'] ="admin@example.com";
		$ldapauth['rootpass'] = "";
		$ldapauth['addomain'] = "example.com";
	}
	if (!isset($ldapauth['ldapgroupcontainer']))
	{
		$ldapauth['ldapgroupcontainer'] = "";	
	}
	if (!isset($ldapauth['ldapmemberfield']))
	{
		$ldapauth['ldapmemberfield'] = "";	
	}
	if (!isset($ldapauth['ldapmemberfieldtype']))
	{
		$ldapauth['ldapmemberfieldtype'] = 0;	
	}
}

//$ldapauth['ldaptype'] = 1;
if ($ldapauth['enable'])
{
  $enabled = "checked";
  // we get a list of groups from the LDAP;
  include_once ("../hooks/ldap_class.php");
  $ldapConf['host'] = $ldapauth['ldapserver'];
	$ldapConf['basedn'] = $ldapauth['basedn'];
	
	$objLDAP = new ldapAuth($ldapConf);
	if ($ldap_debug) { $objLDAP->ldap_debug = true; };
	
	if ($objLDAP->connect())
	{
		// we need to check for the kind of LDAP we are talking to here!
		if ($ldapauth['ldaptype'] == 1 )
		{
			// we need to bind!
			if (!$objLDAP->auth($ldapauth['rootdn'],$ldapauth['rootpass'],1,$ldapauth['addomain']))
			{
				$errmsg["auth"] = $lang['posixldapauth_could_not_bind_to_ad_check_credentials'];
			}	
		}
		
		if (!isset ($errmsg))
		{
			// get the groups
			error_log(   __FILE__ . " " . __METHOD__ . " " . __LINE__ ." GOT TO THE GROUP SELECT ");
			$ldapGroupList = $objLDAP->listGroups($ldapauth['ldaptype'],$ldapauth['ldapgroupcontainer']);
			if (is_array($ldapGroupList)) {
				$ldapGroupsFound = true;
			} else { 
				$ldapGroupsFound = false;
			}
		}
		
				
	} else {
		echo $lang['posixldapauth_connection_to_ldap_server_failed'];	
	}

}  
else
{ 
	 $enabled = "";
}
if ($ldapauth['createusers'])
  $createusers = "checked";
else
  $createusers = "";

if ($ldapauth['groupbased'])
  $groupbased = "checked";
else
  $groupbased = "";


$headerinsert.="
	<script src=\"ldap_functions.js\" language=\"JavaScript1.2\"></script>
    ";

/*
<script type=\"text/javascript\">
    status_error_in = '" . preg_replace("/\r?\n/", "\\n", addslashes($lang['posixldapauth_status_error_in'])) . "';
    server_error = '" . preg_replace("/\r?\n/", "\\n", addslashes($lang['posixldapauth_server_error'])) . "';
*/

include "../../../include/header.php";

?>
<script type="text/javascript">
	
</script>
<div class="BasicsBox"> 

  <h2>&nbsp;</h2>

  <h1><?php echo $lang['posixldapauth_plugin_heading'] ?></h1>

  <div class="VerticalNav">

    <form id="form1" name="form1" method="post" action="">

      <p><label for="enable"><?php echo $lang['posixldapauth_enabled'] ?></label><input type="checkbox" name="enable" id="enable" accesskey="e" tabindex="1" <?php echo $enabled ?> /></p>

      <p><label for="ldapserver"><?php echo $lang['posixldapauth_ldap_server'] ?></label><input id="ldapserver" name="ldapserver" type="text" value="<?php echo $ldapauth['ldapserver']; ?>" size="30" />
      <label for="ldapauth">:</label><input name="port" type="text" value="<?php echo $ldapauth['port']; ?>" size="6" /></p>
		
      <fieldset>
        <legend><?php echo $lang['posixldapauth_ldap_information'] ?></legend>
	  <table id='tableldaptype'>
	  	<tr>
	  		<th><label for="ldaptype"><?php echo $lang['posixldapauth_ldap_type'] ?></label></th>
	  		<td>
	  			<select id='ldaptype' name='ldaptype' style="width:150px" onclick='ldapsetDisplayFields()'>
	  			<option value=0 <?php if($ldapauth['ldaptype'] == 0) {echo "selected"; } ?> ><?php echo $lang['posixldapauth_open_directory'] ?></option>
	  			<option value=1 <?php if($ldapauth['ldaptype'] == 1) {echo "selected"; } ?> ><?php echo $lang['posixldapauth_active_directory'] ?></option>
	  			</select>
	  		</td>
	  	</tr>
	  
	    <tr id="trootdn">
	    	<th><label id='lrootdn' for="rootdn"><?php echo $lang['posixldapauth_ad_admin'] ?></label></th>
	    	<td><input id="rootdn" name="rootdn" type="text" value="<?php if (isset($ldapauth['rootdn'])) { echo $ldapauth['rootdn']; }?>" size="30" /></td>
	    </tr>
	    <tr id="trootpass">
	    	<th><label for="rootpass"><?php echo $lang['posixldapauth_ad_password'] ?></label></th>
	    	<td><input id="rootpass" name="rootpass" type="password" value="<?php if (isset($ldapauth['rootpass'])) { echo $ldapauth['rootpass']; } ?>" size="30" /></td>
	    </tr>
	   	<tr id="taddomain">
	   		<th><label for="addomian"><?php echo $lang['posixldapauth_ad_domain'] ?></label></th>
	   		<td><input id="addomain"  name="addomain" type="text" value="<?php if (isset($ldapauth['addomain'])) { echo $ldapauth['addomain']; }?>" size="30" /></td>
	   	</tr>
	   	<tr id="tbasedn">
	    	<th><label for="basedn"><?php echo $lang['posixldapauth_base_dn'] ?></label></th>
	    	<td><input id="basedn" name="basedn" type="text" value="<?php echo $ldapauth['basedn']; ?>" size="50" /></td>
	    </tr>
	    <tr id="tldapusercontainer">
	    	<th><label for="ldapusercontainer"><?php echo $lang['posixldapauth_user_container'] ?></label></th>
	    	<td><input id="ldapusercontainer" name="ldapusercontainer" type="text" value="<?php echo $ldapauth['ldapusercontainer']; ?>" size="30" /><?php echo " " . $lang['posixldapauth_this_is_added_to_base_dn'] ?></td>
	    </tr>
	       <tr id="tldapgroupcontainer">
	    	<th><label for="ldapgroupcontainer"><?php echo $lang['posixldapauth_group_container'] ?></label></th>
	    	<td><input id="ldapgroupcontainer" name="ldapgroupcontainer" type="text" value="<?php echo $ldapauth['ldapgroupcontainer']; ?>" size="30" /><?php echo " " . $lang['posixldapauth_leave_blank_for_default_osx_server_mapping'] ?></td>
	    </tr>
	    <tr id="tldapmemberfield">
	    	<th><label for="ldapmemberfield"><?php echo $lang['posixldapauth_member_field'] ?></label></th>
	    	<td><input id="ldapmemberfield" name="ldapmemberfield" type="text" value="<?php echo $ldapauth['ldapmemberfield']; ?>" size="30" /><?php echo " " . $lang['posixldapauth_use_to_overide_group_containers_member_field'] ?></td>
	    </tr>
	    <tr>
	  		<th><label for="ldaptype"><?php echo $lang['posixldapauth_member_field_type'] ?></label></th>
	  		<td>
	  			<select id='ldapmemberfieldtype' name='ldapmemberfieldtype' style="width:150px">
	  			<option value=0 <?php if($ldapauth['ldapmemberfieldtype'] == 0) {echo "selected"; } ?> ><?php echo $lang['posixldapauth_default'] ?></option>
	  			<option value=1 <?php if($ldapauth['ldapmemberfieldtype'] == 1) {echo "selected"; } ?> ><?php echo $lang['posixldapauth_user_name'] ?></option>
	  			<option value=1 <?php if($ldapauth['ldapmemberfieldtype'] == 2) {echo "selected"; } ?> ><?php echo $lang['posixldapauth_rdn'] ?></option>
	  			</select> 
	  			<?php echo $lang['posixldapauth_use_to_change_content_of_group_member_field'] ?>
	  		</td>
	  	</tr>
	    <tr id="tloginfield">
	    	<th><label for="loginfield"><?php echo $lang['posixldapauth_login_field'] ?></label></th>
	    	<td><input id="loginfield" name="loginfield" type="text" value="<?php echo $ldapauth['loginfield']; ?>" size="30" /></td>
	    </tr>
	    <tr>
	    	<th><label for="testConn"><?php echo $lang['posixldapauth_test_connection'] ?></label></th>
	    	<td><button name="testConn" type="button" onclick="testLdapConn()">&nbsp;&nbsp;<?php echo $lang['posixldapauth_test'] ?>&nbsp;&nbsp;</button></td>
	    </tr>
	  </table>
	</fieldset>

	<fieldset><legend><?php echo $lang['posixldapauth_resourcespace_configuration'] ?></legend>
	  <table>
            <tr>
            	<th><label for="usersuffix"><?php echo $lang['posixldapauth_user_suffix'] ?></label></th>
            	<td><input name="usersuffix" type="text" value="<?php echo $ldapauth['usersuffix']; ?>" size="30" /></td>
            </tr>
            <tr>
            	<th><label for="createusers"><?php echo $lang['posixldapauth_create_users'] ?></label></th>
            	<td><input name="createusers" type="checkbox" <?php echo $createusers; ?> /></td>
            </tr>
            <tbody id="ldapconf-cu">
             	<tr><th><label for="groupbased"><?php echo $lang['posixldapauth_group_based_user_creation'] ?></label></th><td><input name="groupbased" type="checkbox" <?php echo $groupbased; ?> /></td></tr>
              <tbody id="group-false">
                <tr><th><label for="newusergroup"><?php echo $lang['posixldapauth_new_user_group'] ?></label></th>
        	  <td>
                    <select name="newusergroup" style="width:300px">
        	      <?php
        	      
        		foreach ($usergroups as $usergroup){
			  $ref = $usergroup['ref'];
        		  echo '<option value="'.$ref.'"';
			  if ($ref == $ldapauth['newusergroup'])
                            echo "selected";

			  echo '>' . lang_or_i18n_get_translated($usergroup['name'], "usergroup-") . '</option>';
        		}
        		
                      ?>
		    </select>
                  </td>
		</tr>
              </tbody>
            </tbody>
          </table>
        </fieldset>
        <?php
        if ($enabled && !isset($errmsg))
        {
	     		
	     
	        echo '<fieldset><legend>' . $lang['posixldapauth_group_mapping'] . '</legend>';
	        
	        // Check to see if we found any groups!
	        if ($ldapGroupsFound)
	        {
	        	
		        // here we display the group mapping for the LDAP user groups:
		        echo "<table>";
		        // header row
		        echo '<tr><th>' . $lang['posixldapauth_group_name'] . '</th>';
		        echo '<th>' . $lang['posixldapauth_map_to'] . '</th>';
		        echo '<th>' . $lang['posixldapauth_enable_group'] . '</th>';
		        echo "</tr>";
		        
		        // now display each group
		        $tmpx = count($ldapGroupList);	
				for ($i=0; $i < $tmpx; $i++) 
				{
				    //echo $ldapGroupList[$i]['cn'] ." : " . $info[$i]['gidnumber']. "<br>";
					echo "<tr>";
					echo '<td><input name="ldapGroupName[]" type="text" value="'. $ldapGroupList[$i]['cn'] . '" size="30" readonly="readonly"></td>';
					echo "<td>";
					$lGroupName = $ldapGroupList[$i]['cn'];
					// create the usergroup list
					echo '<select name="ldapmaptors['.$lGroupName.']">';
		     		foreach ($usergroups as $usergroup)
		     		{
				 		$ref = $usergroup['ref'];
	        		  	echo '<option value="'.$ref.'"';
	        		  	// check mapping;
	        		  	if (isset($ldapauth['groupmap'][$lGroupName]['rsGroup']))
	        		  	{
					  		if ($ref == $ldapauth['groupmap'][$lGroupName]['rsGroup'])
		                    {
		                          echo "selected";
		                    }
	        		  	}
				  		echo '>' . lang_or_i18n_get_translated($usergroup['name'], "usergroup-") . '</option>';	
	        		}
	        		echo "</select>";
					echo "</td>";
					echo "<td>";
					echo '<input name="ldapGroupEnable['.$lGroupName.']" type="checkbox" ';
					// check to see if the enabled exists and if it has a value!
					if (isset($ldapauth['groupmap'][$lGroupName]['enabled']))
					{
						if( $ldapauth['groupmap'][$lGroupName]['enabled']) 
						{
							echo "checked";	
						}
					}
					echo ' />'; 
					echo "</td>";
					echo "</tr>";
				}
		        
		        
		        echo "</table>";
		       
	        } else {
	        	
	        	echo "<p>" . $ldapGroupList ."</p>"; 	
	        }
	        
	         
	        echo "</fieldset>";
        } else {
        	
        	if (isset($errmsg))
        	{
        		foreach ($errmsg as $msg)
        		{
        			echo str_replace("%msg%", $msg, $lang['posixldapauth_error-msg']) . " <br>";
        		}	
        	}
        }
        ?>
        
   		<input id="lang_status_error" name="lang_status_error" type="hidden" value="<?php echo $lang['posixldapauth_status_error_in']; ?>" size="30" />
        <input id="lang_server_error" name="lang_server_erro" type="hidden" value="<?php echo $lang['posixldapauth_server_error']; ?>" size="30" />
        <input id="lang_passed" name="lang_passed" type="hidden" value="<?php echo $lang['posixldapauth_passed']; ?>" size="30" />
        <input id="lang_could_not_connect" name="lang_could_not_connect" type="hidden" value="<?php echo $lang['posixldapauth_could_not_connect_to_ldap_server']; ?>" size="30" />
        <input id="lang_could_not_bind" name="lang_could_not_bind" type="hidden" value="<?php echo $lang['posixldapauth_passed']; ?>" size="30" />
        <input id="lang_test_passed" name="lang_test_passed" type="hidden" value="<?php echo $lang['posixldapauth_tests_passed_save_settings_and_set_group_mapping'] ; ?>" size="30" />
        <input id="lang_test_failed" name="lang_test_failed" type="hidden" value="<?php echo $lang['posixldapauth_tests_failed_check_settings_and_test_again']; ?>" size="30" />
        <input type="submit" name="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;"/>
		
    </form>
  </div>	
<script type="text/javascript">
	ldapsetDisplayFields();
	</script>
