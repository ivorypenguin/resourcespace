<?php
function HookLegacy_actionsThemesRender_themes_list_tools($theme_data)
    {
    global $baseurl_short, $lang, $download_usage, $collection_download, $home_dash,$anonymous_login,
           $username, $managed_home_dash, $contact_sheet, $allow_share;
    ?>
    <a href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $theme_data['ref']); ?>"
       title="<?php echo $lang['collectionviewhover']; ?>"
       onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['viewall']; ?></a>
    <?php
    if(!checkperm('b'))
        {
        echo '&nbsp;' . change_collection_link($theme_data['ref']);
        }

    if($download_usage && (isset($zipcommand) || $collection_download))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/terms.php?url=<?php echo urlencode('pages/download_usage.php?collection=' .  $theme_data['ref']); ?>">&gt;&nbsp;<?php echo $lang['action-download']; ?></a>
        <?php
        }
    else if(isset($zipcommand) || $collection_download)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/terms.php?url=<?php echo urlencode('pages/collection_download.php?collection=' . $theme_data['ref']); ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-download']; ?></a>
        <?php
        }

    if($contact_sheet == true)
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo $theme_data['ref']; ?>"
                 title="<?php echo $lang['collectioncontacthover']; ?>"
                 onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['contactsheet']; ?></a>
        <?php
        }

    #Home_dash is on, And not Anonymous use, And (Dash tile user (Not with a managed dash) || Dash Tile Admin)
    if($home_dash && checkPermission_dashcreate())
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/dash_tile.php?create=true&tltype=srch&promoted_resource=true&freetext=true&all_users=1&link=/pages/search.php?search=!collection<?php echo urlencode($theme_data['ref']); ?>&order_by=relevance&sort=DESC"  onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['dashtile']; ?></a>
        <?php
        }

    if($allow_share && (checkperm('v') || checkperm ('g')))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_share.php?ref=<?php echo $theme_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['share']; ?></a>
        <?php
        }

    if(checkperm('h'))
        {
        ?>
        &nbsp;<a href="<?php echo $baseurl_short; ?>pages/collection_edit.php?ref=<?php echo $theme_data['ref']; ?>" onClick="return CentralSpaceLoad(this, true);">&gt;&nbsp;<?php echo $lang['action-edit']; ?></a>
        <?php
        }

    hook('addcustomtool', '', array($theme_data['ref']));
    }