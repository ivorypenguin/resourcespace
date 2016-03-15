<?php 

function HookTransformAllAdditionalheaderjs(){
global $baseurl,$baseurl_short;?>
<link rel="stylesheet" href="<?php echo $baseurl_short?>plugins/transform/lib/jcrop/css/jquery.Jcrop.min.css" type="text/css" />
<script type="text/javascript" src="<?php echo $baseurl?>/plugins/transform/lib/jcrop/js/jquery.Jcrop.min.js" language="javascript"></script>
<?php } 
function HookTransformAllRender_actions_add_collection_option($top_actions,$options){
	global $cropper_enable_batch,$count_result,$lang,$collection_data,$baseurl_short, $userref;

	// it seems we can no longer access $collection_data as a global variable in order to determine the currently selected collection.
	// so, as an ugly hack, we're going to scan through existing options to determine this
	$selectedcoll = '';
	foreach ($options as $theoption){
		if (isset($theoption['extra_tag_attributes'])){
			if (preg_match("/\/pages\/collection_log\.php\?ref=(\\d+)/",$theoption['extra_tag_attributes'],$thiscoll)){
				$selectedcoll = $thiscoll[1];
			}
		}
	}
	

	$c=count($options);
	
	if ($cropper_enable_batch && $count_result>0 && ($userref == $collection_data['user'] || $collection_data['allow_changes'] == 1 || checkperm('h'))){
		$data_attribute['url'] = sprintf('%splugins/transform/pages/collection_transform.php?collection=%s',
            $baseurl_short,
            urlencode($selectedcoll)
        );
        $options[$c]['value']='transform';
		$options[$c]['label']=$lang["transform"];
		$options[$c]['data_attr']=$data_attribute;
		return $options;
	}
}
function HookTransformAllAdditional_title_pages_array(){
        return array("crop","collection_transform");
}
function HookTransformAllAdditional_title_pages(){
        global $pagename,$lang,$applicationname;
        switch($pagename){
			case "crop":
				global $original;
				if($original){
					$pagetitle=$lang['transform_original'];
				}
				else{
					$pagetitle=$lang['transformimage'];
                }
                break;
            case "collection_transform":
				$pagetitle=$lang['batchtransform'];
				break;
		}
        if(isset($pagetitle)){
                echo "<script language='javascript'>\n";
                echo "document.title = \"$applicationname - $pagetitle\";\n";
                echo "</script>";
        }
}
