<?php
function HookVimeo_publishViewAfterresourceactions()
    {
    // Adds a "Publish to Vimeo" link under Resource Tools
    global $baseurl, $lang, $ref, $access, $resource, $resourcetoolsGT, $vimeo_publish_restypes;
    
    if(0 == $access && in_array($resource['resource_type'], $vimeo_publish_restypes))
        {
        $gt_sign = '';
        if($resourcetoolsGT)
            {
            $gt_sign = '&gt; ';
            }
        ?>
        <li>
            <a href="<?php echo $baseurl?>/plugins/vimeo_publish/pages/vimeo_api.php?resource=<?php echo $ref; ?>"><?php echo $gt_sign . $lang['vimeo_publish_resource_tool_link']; ?></a>
        </li>
        <?php
        }
    }