<?php
#
# simplesaml setup page
#

include '../../../include/db.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}
include '../../../include/general.php';

if ((getval('submit','')!='') || (getval('save','')!=''))
	{
		
	$simplesaml['simplesaml_site_block'] = getvalescaped('simplesaml_site_block','');
	$simplesaml['simplesaml_allow_public_shares'] = getvalescaped('simplesaml_allow_public_shares','');
	$simplesaml['simplesaml_allowedpaths'] = explode(",",getvalescaped('simplesaml_allowedpaths',''));
	$simplesaml['simplesaml_allow_standard_login'] = getvalescaped('simplesaml_allow_standard_login','');
	$simplesaml['simplesaml_sp'] = getvalescaped('simplesaml_sp','');
	
	
	$simplesaml['simplesaml_username_attribute'] = getvalescaped('simplesaml_username_attribute','');
	$simplesaml['simplesaml_fullname_attribute'] = getvalescaped('simplesaml_fullname_attribute','');
	$simplesaml['simplesaml_email_attribute'] = getvalescaped('simplesaml_email_attribute','');
	$simplesaml['simplesaml_group_attribute'] = getvalescaped('simplesaml_group_attribute','');	
	$simplesaml['simplesaml_fallback_group'] = getvalescaped('simplesaml_fallback_group','');
	$simplesaml['simplesaml_update_group'] = getvalescaped('simplesaml_update_group','');
	
	$samlgroups = $_REQUEST['samlgroup'];
	$rsgroups = $_REQUEST['rsgroup'];
	$priority = $_REQUEST['priority'];

	if (count($samlgroups) > 0){	
		$simplesaml_groupmap=array();	
		$mappingcount=0;
		}
	
	for ($i=0; $i < count($samlgroups); $i++)
		{
		if ($samlgroups[$i] <> '' && $rsgroups[$i] <> '' && is_numeric($rsgroups[$i]))
			{
			$simplesaml_groupmap[$mappingcount]=array();
			$simplesaml_groupmap[$mappingcount]["samlgroup"]=$samlgroups[$i];
			$simplesaml_groupmap[$mappingcount]["rsgroup"]=$rsgroups[$i];
			if(isset($priority[$i])){$simplesaml_groupmap[$mappingcount]["priority"]=$priority[$i];}
			$mappingcount++;
			}			
		}
	
	$simplesaml["simplesaml_groupmap"]=$simplesaml_groupmap;
	set_plugin_config("simplesaml",$simplesaml);
	if (getval('submit','')!=''){redirect('pages/team/team_plugins.php');}
	}

global $baseurl;


// Retrieve list of groups for use in mapping dropdown
$rsgroups = sql_query('select ref, name from usergroup order by name asc');


include "../../../include/header.php";

?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang['simplesaml_configuration'] ?></h1>

<form id="form1" name="form1" method="post" action="">

<?php echo config_section_header($lang['simplesaml_main_options'],'');?>
<?php echo config_boolean_field("simplesaml_site_block",$lang['simplesaml_site_block'],$simplesaml_site_block,30);?>
<?php echo config_boolean_field("simplesaml_allow_public_shares",$lang['simplesaml_allow_public_shares'],$simplesaml_allow_public_shares,30);?>
<?php echo config_text_input("simplesaml_sp",$lang['simplesaml_service_provider'],$simplesaml_sp);?>

<?php echo config_text_input("simplesaml_allowedpaths",$lang['simplesaml_allowedpaths'],implode(',',$simplesaml_allowedpaths));?>
<?php echo config_boolean_field("simplesaml_allow_standard_login",$lang['simplesaml_allow_standard_login'],$simplesaml_allow_standard_login,30);?>
<?php echo config_boolean_field("simplesaml_prefer_standard_login",$lang['simplesaml_prefer_standard_login'],$simplesaml_prefer_standard_login,30);?>


<?php echo config_section_header($lang['simplesaml_idp_configuration'],$lang['simplesaml_idp_configuration_description']);?>

<?php echo config_text_input("simplesaml_username_attribute",$lang['simplesaml_username_attribute'],$simplesaml_username_attribute);?>
<?php echo config_text_input("simplesaml_fullname_attribute",$lang['simplesaml_fullname_attribute'],$simplesaml_fullname_attribute);?>
<?php echo config_text_input("simplesaml_email_attribute",$lang['simplesaml_email_attribute'],$simplesaml_email_attribute);?>
<?php echo config_text_input("simplesaml_group_attribute",$lang['simplesaml_group_attribute'],$simplesaml_group_attribute);?>


<?php echo config_boolean_field("simplesaml_update_group",$lang['simplesaml_update_group'],$simplesaml_update_group,30);?>

<?php
$rsgroupoption=array();
foreach($rsgroups as $rsgroup)
	{$rsgroupoption[$rsgroup["ref"]]=$rsgroup["name"];}
echo config_single_select("simplesaml_fallback_group",$lang['simplesaml_fallback_group'],$simplesaml_fallback_group,$rsgroupoption, true);?>


<div class="Question">
<h3><?php echo $lang['simplesaml_groupmapping']; ?></h3>
<table id='groupmaptable'>
<tr><th>
<strong><?php echo $lang['simplesaml_samlgroup']; ?></strong>
</th><th>
<strong><?php echo $lang['simplesaml_rsgroup']; ?></strong>
</th><th>
<strong><?php echo $lang['simplesaml_priority']; ?></strong>
</th>
</tr>

<?php
	for($i = 0; $i < count($simplesaml_groupmap)+1; $i++){
		if ($i >= count($simplesaml_groupmap)){
			$thegroup = array();
			$thegroup['samlgroup'] = '';
			$thegroup['rsgroup'] = '';
			$thegroup['priority'] = '';
			$rowid = 'groupmapmodel';
		} else {
			$thegroup = $simplesaml_groupmap[$i];
			$rowid = "row$i";
		}
?>
<tr id='<?php echo $rowid; ?>'>
   <td><input type='text' name='samlgroup[]' value='<?php echo $thegroup['samlgroup']; ?>' /></td>
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

<a onclick='addGroupMapRow()'><?php echo $lang['simplesaml_addrow']; ?></a>
</div>

<div class="Question">  
<label for="submit"></label>
<input type="submit" name="save" id="save" value="<?php echo $lang['plugins-saveconfig']?>">
<input type="submit" name="submit" id="submit" value="<?php echo $lang['plugins-saveandexit']?>">
</div><div class="clearerleft"></div>

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
<?php

include '../../../include/footer.php';
