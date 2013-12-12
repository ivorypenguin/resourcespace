<?php

function HookImage_textViewDownloadbuttonreplace()
	{
	global $lang, $baseurl_short, $ext, $resource, $image_text_restypes, $baseurl, $ref, $size, $altfiles, $n, $usergroup, $image_text_override_groups,$image_text_filetypes;
	global $order_by,  $k, $search,	$offset, $archive, $sort;
	# Return if not configured for this resource type or user does not have the option to download without overlay
	if(!in_array($resource['resource_type'], $image_text_restypes) || !in_array($usergroup, $image_text_override_groups)){return false;}
	
	if (isset($altfiles[$n]["file_extension"]) && in_array(strtoupper($altfiles[$n]["file_extension"]), $image_text_filetypes) )
            {   
            ?>          
            
            <a id="downloadlink" href="<?php echo $baseurl ?>/pages/terms.php?ref=<?php echo urlencode($ref)?>&search=<?php
                                    echo urlencode($search) ?>&k=<?php echo urlencode($k)?>&url=<?php
                                    echo urlencode("pages/download_progress.php?ref=" . $ref . "&size=" . $size["id"]
                                    . "&ext=" . $altfiles[$n]["file_extension"] . "&k=" . $k . "&search=" . urlencode($search)
                                    . "&offset=" . $offset . "&alternative=" . $altfiles[$n]["ref"] . "&archive=" . $archive . "&sort=".$sort."&order_by="
                                    . urlencode($order_by)."&nooverlay=true")?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["image_text_download_clear"]?></a></td><td class="DownloadButton"><?php
            }
            
        else if (in_array(strtoupper($resource["file_extension"]), $image_text_filetypes))
            {
            if(isset($size["extension"])){$dlext=$size["extension"];}else{$dlext=$resource["file_extension"];}
                
            ?><a id="downloadlink" href="<?php echo $baseurl ?>/pages/terms.php?ref=<?php echo urlencode($ref)?>&search=<?php
			echo urlencode($search) ?>&k=<?php echo urlencode($k)?>&url=<?php
                        echo urlencode("pages/download_progress.php?ref=" . $ref . "&size=" . $size["id"]
			. "&ext=" . $dlext . "&k=" . $k . "&search=" . urlencode($search)
			. "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by="
			. urlencode($order_by) ."&nooverlay=true")?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["image_text_download_clear"]?></a></td><td class="DownloadButton"><?php    
            }
	return false;
	}

    
       