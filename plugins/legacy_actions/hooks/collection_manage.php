<?php
function HookLegacy_actionsCollection_manageLegacy_list_tools($collection_data)
    {
    global $baseurl_short, $lang, $autoshow_thumbs, $download_usage, $collection_download, $contact_sheet,
           $manage_collections_contact_sheet_link, $manage_collections_share_link, $allow_share, $manage_collections_remove_link,
           $username, $collection_purge, $home_dash, $anonymous_login, $show_edit_all_link, $edit_all_checkperms;
    ?>
    <a href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['viewall']; ?></a>
    &nbsp;
    <a href="<?php echo $baseurl_short; ?>pages/collections.php?collection=<?php echo urlencode($collection_data['ref']); if($autoshow_thumbs) {echo '&amp;thumbs=show';} ?>" onClick="ChangeCollection(<?php echo $collection_data['ref']; ?>, ''); return false;">&gt;&nbsp;<?php echo $lang['action-select']; ?></a>
    <?php
    if($download_usage && (isset($zipcommand) || $collection_download))
        {
        ?>
        <a href="<?php echo $baseurl_short; ?>pages/terms.php?url=<?php echo urlencode('pages/download_usage.php?collection=' . $collection_data['ref']); ?>">&gt;&nbsp;<?php echo $lang['action-download']; ?></a>
        <?php
        }
    else if(isset($zipcommand) || $collection_download)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/terms.php?url=<?php echo urlencode('pages/collection_download.php?collection=' . $collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-download']; ?></a>
        <?php
        }
    
    if($contact_sheet == true && $manage_collections_contact_sheet_link)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo urlencode($collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['contactsheet']; ?></a>
        <?php
        }

    if($manage_collections_share_link && $allow_share && (checkperm('v') || checkperm ('g')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_share.php?ref=<?php echo $collection_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['share']; ?></a>
        <?php
        }

    /*Remove Shared Collection*/
    if($manage_collections_remove_link && $username != $collection_data['username'])
        {
        ?>&nbsp;<a href="#" onclick="if(confirm('<?php echo $lang["removecollectionareyousure"]; ?>')) {document.getElementById('collectionremove').value='<?php echo urlencode($collection_data["ref"]); ?>'; document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang['action-remove']; ?></a>
        <?php
        }

    if((($username == $collection_data['username']) || checkperm('h')) && ($collection_data['cant_delete'] == 0))
        {
        ?>
        &nbsp;<a href="#" onclick="if(confirm('<?php echo $lang["collectiondeleteconfirm"]; ?>')) {document.getElementById('collectiondelete').value='<?php echo urlencode($collection_data["ref"]); ?>'; passQueryStrings(['paging', 'col_order_by', 'sort', 'find', 'go', 'offset'], 'collectionform'); CentralSpacePost(document.getElementById('collectionform'),false);} return false;">&gt;&nbsp;<?php echo $lang['action-delete']; ?></a>
        <?php
        }

    if($collection_purge)
        {
        if($n == 0)
            {
            ?>
            <input id="collectionpurge" type=hidden name="purge" value="">
            <?php
            }
        if(checkperm('e0') && $collection_data['cant_delete'] == 0)
            {
            ?>
            &nbsp;<a href="#" onclick="if(confirm('<?php echo $lang["purgecollectionareyousure"]; ?>')) {document.getElementById('collectionpurge').value='<?php echo urlencode($collection_data["ref"]); ?>';document.getElementById('collectionform').submit();} return false;">&gt;&nbsp;<?php echo $lang['purgeanddelete']; ?></a>
            <?php
            }
        }

    hook('additionalcollectiontool');

    if(($username == $collection_data['username']) || (checkperm('h')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_edit.php?ref=<?php echo urlencode($collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);" >&gt;&nbsp;<?php echo $lang['action-edit']; ?></a>
        <?php
        }

    # If this collection is (fully) editable, then display an edit all link
    if($show_edit_all_link && ($collection_data['count'] > 0))
        {
        if(!$edit_all_checkperms || allow_multi_edit($collection_data['ref']))
            {
            ?>
            &nbsp;<a href="<?php echo $baseurl_short; ?>pages/edit.php?collection=<?php echo urlencode($collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-editall']; ?></a>
            <?php
            }
        }

    #Home_dash is on, And not Anonymous use, And (Dash tile user (Not with a managed dash) || Dash Tile Admin)
    if($home_dash && checkPermission_dashcreate())
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/dash_tile.php?create=true&tltype=srch&promoted_resource=true&freetext=true&all_users=1&link=/pages/search.php?search=!collection<?php echo urlencode($collection_data['ref']); ?>&order_by=relevance&sort=DESC" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['dashtile']; ?></a>
        <?php
        }

    if(($username == $collection_data['username']) || (checkperm('h')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_log.php?ref=<?php echo urlencode($collection_data['ref']); ?>&order_by=relevance&sort=DESC" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['log']; ?></a>
        <?php
        }

    hook('addcustomtool');

    }