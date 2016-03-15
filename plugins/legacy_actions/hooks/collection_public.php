<?php
function HookLegacy_actionsCollection_publicRender_collections_public_list_tools($collection_data)
    {
    global $baseurl_short, $lang, $contact_sheet, $home_dash, $anonymous_login, $username;
    ?>
    <a href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['viewall']; ?></a>
    <?php
    
    if($contact_sheet == true)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo urlencode($collection_data['ref']); ?>" onClick="return CentralSpaceLoad(this);">&gt;&nbsp;<?php echo $lang['contactsheet']; ?></a>
        <?php
        }

    if(!checkperm('b'))
        {
        ?>
        &nbsp;<a href="#" onclick="document.getElementById('collectionadd').value='<?php echo urlencode($collection_data['ref']) ?>'; document.getElementById('collectionform').submit(); return false;">&gt;&nbsp;<?php echo $lang['addtomycollections']; ?></a>
        <?php
        }

    if($home_dash && checkPermission_dashcreate())
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/dash_tile.php?create=true&tltype=srch&promoted_resource=true&freetext=true&all_users=1&link=/pages/search.php?search=!collection<?php echo urlencode($collection_data['ref']); ?>&order_by=relevance&sort=DESC" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['dashtile']; ?></a>
        <?php
        }
    }