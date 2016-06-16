<?php
function HookVimeo_publishViewAfterresourceactions()
    {
    // Adds a "Publish to Vimeo" link under Resource Tools
    global $baseurl, $lang, $ref, $access, $resource, $vimeo_publish_restypes;
    
    if(0 == $access && in_array($resource['resource_type'], $vimeo_publish_restypes))
        {
        ?>
        <li>
            <a href="<?php echo $baseurl?>/plugins/vimeo_publish/pages/vimeo_api.php?resource=<?php echo $ref; ?>"><?php echo "<i class='fa fa-share-alt'></i>&nbsp;" . $lang['vimeo_publish_resource_tool_link']; ?></a>
        </li>
        <?php
        }
    }