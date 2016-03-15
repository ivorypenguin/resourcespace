<?php 
function HookVideo_spliceAllRender_actions_add_collection_option($top_actions,$options){
	global $collection,$count_result,$lang,$pagename,$baseurl_short;
	
	$c=count($options);
	
	if ($pagename=="collections" && $count_result!=0)
		{
		$data_attribute['url'] = sprintf('%splugins/video_splice/pages/splice.php?collection=%s',
            $baseurl_short,
            urlencode($collection)
        );
        $options[$c]['value']='video_splice';
		$options[$c]['label']=$lang["action-splice"];
		$options[$c]['data_attr']=$data_attribute;
		
		return $options;
	}
}
