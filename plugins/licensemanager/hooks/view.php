<?php

function HookLicensemanagerViewCustompanels()
	{
	global $lang,$baseurl_short,$ref,$edit_access;
	
	$licenses=sql_query("select ref,outbound,holder,license_usage,description,expires from resource_license where resource='$ref'");
	?>
    <!-- Begin Geolocation Section -->
    <div class="RecordBox">
    <div class="RecordPanel">
    <div class="Title"><?php echo $lang["license_management"] ?></div>

    <?php if ($edit_access) { ?>    
    <p>&gt;&nbsp;<a href="<?php echo $baseurl_short ?>plugins/licensemanager/pages/edit.php?ref=new&resource=<?php echo $ref ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["new_license"] ?></a></p>	
    <?php } ?>
   
	<?php if (count($licenses)>0) { ?>
		<div class="Listview">
		<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
		<tr class="ListviewTitleStyle">
		<td><?php echo $lang["license_id"] ?></a></td>
		<td><?php echo $lang["type"] ?></a></td>
		<td><?php echo $lang["licensor_licensee"] ?></a></td>
		<td><?php echo $lang["indicateusagemedium"] ?></a></td>
		<td><?php echo $lang["description"] ?></a></td>
		<td><?php echo $lang["fieldtitle-expiry_date"] ?></a></td>

		<?php if ($edit_access) { ?>
		<td><div class="ListTools"><?php echo $lang["tools"] ?></div></td>
		<?php } ?>
		
		</tr>
	
		<?php
		foreach ($licenses as $license)
			{
			?>
			<tr>
			<td><?php echo $license["ref"] ?></td>
			<td><?php echo ($license["outbound"]?$lang["outbound"]:$lang["inbound"]) ?></td>
			<td><?php echo $license["holder"] ?></td>
			<td><?php echo $license["license_usage"] ?></td>
			<td><?php echo $license["description"] ?></td>
			<td><?php echo nicedate($license["expires"]) ?></td>
		
			<?php if ($edit_access) { ?>
			<td><div class="ListTools">
			<a href="<?php echo $baseurl_short ?>plugins/licensemanager/pages/edit.php?ref=<?php echo $license["ref"] ?>&resource=<?php echo $ref ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;Edit</a>
			<a href="<?php echo $baseurl_short ?>plugins/licensemanager/pages/delete.php?ref=<?php echo $license["ref"] ?>&resource=<?php echo $ref ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;Delete</a>
			</div></td>
			<?php } ?>
						
			</tr>
			<?php
			}
		?>
		
		</table>
		</div>
	<?php } ?>

    
    </div>
    <div class="PanelShadow"></div>
    </div>
    <?php
	return false; # Allow further custom panels
	}