<?php
include "../../../include/db.php";
include "../../../include/authenticate.php"; if (!checkperm("a")) {exit ($lang['error-permissiondenied']);}
include "../../../include/general.php";

// whitelisting IPs

$error="";
if (getval("submit","")!="") {
	
	$newip=getvalescaped("newip","");
	if ($newip==""){$error.=$lang['apicore_noipaddress']."<br />";}
	$newuser=getvalescaped("newuser","",true);
	if ($newuser==""){$error.=$lang['apicore_nouser']."<br />";}
	$newapis=getvalescaped("newapis","");
	if ($newapis==""){$error.=$lang['apicore_noapi']."<br />";}
	if ($newapis!=""){
	$newapis=implode(",",$newapis);
	}
	if ($error==""){
	$result=sql_query("insert into api_whitelist (ip_domain,userref,apis) values ('".escape_check($newip)."',".escape_check($newuser).",'".escape_check($newapis)."')");
	} 
} 

include "../../../include/header.php";

if (getvalescaped("delete","")!=""){
	
	$wait=sql_query("delete from api_whitelist where ref='".getvalescaped("ref","",true)."'");

	
}

   
$current_whitelists=sql_query("select u.username,u.fullname,ug.name groupname,w.* from api_whitelist w join user u on w.userref=u.ref join usergroup ug on ug.ref=u.usergroup order by u.username");
?>

<div class="BasicsBox"> 

<?php if ($error!=""){echo "<div style='color:red;'>$error";}?>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">


  <h1><?php echo $lang['apicore_apiwhitelisting']?></h1>
  <p><?php echo $lang['apicore_apiwhitelistingdesc']?></p>
  
<?php if (count($current_whitelists)!=0){?>
<tr class="ListviewTitleStyle">
<td><?php echo $lang['apicore_ip']?></td>
<td><?php echo $lang['apicore_account']?></td>
<td><?php echo $lang['apicore_apis']?></td>
<td><div class="ListTools"><?php echo $lang['tools']?></div></div>
</td>
</tr>
<?php foreach ($current_whitelists as $whitelist){?>
<tr>
<td><?php echo $whitelist['ip_domain']?></td>
<td><?php echo $whitelist['fullname'] . " (" . lang_or_i18n_get_translated($whitelist['groupname'], "usergroup-") . ")";?></td>
<td><?php echo ($whitelist['apis']=="all"?$lang['all']:$whitelist['apis'])?></td>
<td><div class="ListTools"><a onclick="return confirm('<?php echo $lang['apicore_deletesure']?>')" href="<?php echo $baseurl_short?>plugins/api_core/pages/setup.php?delete=true&ref=<?php echo $whitelist['ref']?>">> <?php echo $lang['apicore_delete']?></a></div></td>
</tr>
<?php } ?>
<?php } ?>
</table>
</div>
<div class="clearerleft"></div>


<div class="BasicsBox">
    <form method="post" action="<?php echo $baseurl_short?>plugins/api_core/pages/setup.php">
    <div class="clearerleft"></div><div class="clearerleft"></div>
    <h1><?php echo $lang['apicore_newentry']?></h1>
		<div class="Question">
			<label for="newuser"><?php echo $lang['apicore_whitelistnew']?></label>
			<div class="Inline"><input type=text name="newip" id="newip" maxlength="100" class="medwidth" /></div>
			
		<div class="clearerleft"></div>
		</div>
		
		
		
		<div class="Question">
			<label for="newuser"><?php echo $lang['apicore_associateaccount']?></label>
			 <div class="Inline"><select class="medwidth" name="newuser" id="newuser">
				 <option value=""><?php echo $lang['apicore_selectaccount']?></option>
			<?php
			$users=get_users(0,"","u.fullname");
			foreach ($users as $user)
			{
			echo '<option value="' . $user['ref'] . '">' . $user['fullname'] . ' (' . $user['email'] . ')</option>';
			}
			?>
			</select></div>
		<div class="clearerleft"></div>
		</div>
		
		
		
		
		
		<div class="Question">
			<label for="newuser"><?php echo $lang['apicore_selectapis']?></label>
			<div class="Inline"><select class="medwidth" name="newapis[]" id="newapis[]" multiple>
			<option value="all"><?php echo $lang['all']?></option>
			<?php
			foreach ($plugins as $plugin)
			{
				if (substr($plugin,0,4)=="api_" && $plugin !="api_core"){
					echo '<option value="' . $plugin . '">' .$plugin .'</option>';
				}
			}
			?>
			</select></div>
		<div class="clearerleft"></div>		
		</div>
		
		
		
		<div class="Question">
			<label ></label>
			 <div class="Inline">
				 <input name="submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
			</div>
			<div class="clearerleft"> </div>
			
			
		</div>
	</form>








