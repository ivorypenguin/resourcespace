<?php

function HookTransformViewAfterresourceactions (){
	global $ref,$access,$lang,$resource,$cropper_allowed_extensions,$baseurl_short,$resourcetoolsGT;

	// fixme - for some reason this isn't pulling from config default for plugin even when set as global
	// hack below makes it work, but need to figure this out at some point
	// this is something to do with hook architecture -- think it is now fixed by above includes. But this
	// code isn't hurting anything, so leave it for now. -Dwiggins, 5/2010
	if (!isset($cropper_allowed_extensions)){
		$cropper_allowed_extensions = array('TIF','TIFF','JPG','JPEG','PNG','GIF','BMP','PSD'); // file formats that can be transformed
	} else {
		// in case these have been overriden, make sure these are all in uppercase.
		for($i=0;$i<count($cropper_allowed_extensions);$i++){
			$cropper_allowed_extensions[$i] = strtoupper($cropper_allowed_extensions[$i]);
		}	
	}

	if ($access==0 && $resource['has_image']==1 && in_array(strtoupper($resource['file_extension']),$cropper_allowed_extensions)){
		?>
		<li><a onClick='return CentralSpaceLoad(this,true);' href='<?php echo $baseurl_short;?>plugins/transform/pages/crop.php?ref=<?php echo $ref?>'>
		<?php echo "<i class='fa fa-crop'></i>&nbsp;" .$lang['transform'];?>
		</a></li>
		<?php
		return true;
	}

}

?>
