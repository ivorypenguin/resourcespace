<?php
function HookLegacy_actionsResource_collection_listRender_resource_collection_list_list_tools($collection_data)
    {
    global $baseurl_short, $lang, $autoshow_thumbs, $collection_download, $contact_sheet, $allow_share,
           $username, $show_edit_all_link;
    ?>
    <a href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $collection_data['ref']); ?>">&gt;&nbsp;<?php echo $lang['viewall']; ?></a>
    
    <?php
    if(!checkperm('b'))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collections.php?collection=<?php echo $collection_data['ref']; if($autoshow_thumbs) {echo "&amp;thumbs=show";}?>" onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang['action-select']; ?></a>
        <?php
        }

    if(isset($zipcommand) || $collection_download)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/terms.php?url=<?php echo urlencode('pages/collection_download.php?collection=' . $collection_download['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-download']; ?></a>
        <?php
        }

    if($contact_sheet == true)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['contactsheet']; ?></a>
        <?php
        }

    if($allow_share && (checkperm('v') || checkperm ('g')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_share.php?ref=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['share']; ?></a>
        <?php
        }

    if(($username == $collection_data['username']) || (checkperm('h')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_edit.php?ref=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-edit']; ?></a>
        <?php
        }

    # If this collection is (fully) editable, then display an edit all link
    if(($collection_data['count'] > 0) && allow_multi_edit($collection_data['ref']) && $show_edit_all_link)
        {
        ?> 
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/edit.php?collection=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-editall']; ?></a>
        <?php
        }
        if(($username == $collection_data['username']) || (checkperm('h')))
            {
            ?>
            &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_log.php?ref=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['log']?></a>
            <?php
            }
    }