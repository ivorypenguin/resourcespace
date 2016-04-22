<?php
function HookLegacy_actionsAllPrevent_running_render_actions()
    {
    return true;
    }

function HookLegacy_actionsAllCollectiontool()
    {
    global $baseurl_short, $lang, $usercollection, $contact_sheet, $contact_sheet_link_on_collection_bar, $allow_share,
           $cinfo, $userref, $preview_all, $feedback, $result, $show_edit_all_link, $edit_all_checkperms, $count_result,
           $download_usage, $collection_download, $k, $emptycollection, $remove_resources_link_on_collection_bar;

    if((!collection_is_research_request($usercollection)) || (!checkperm('r')))
        { 
        hook('beforecollectionlinks');
        
        if(checkperm('s'))
            {
            ?>
            <li>
                <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_manage.php">&gt; <?php echo $lang['managemycollections']; ?></a>
            </li>
            
            <?php
            if($contact_sheet == true && $contact_sheet_link_on_collection_bar)
                {
                ?>
                <li>
                    <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo urlencode($usercollection); ?>">&gt;&nbsp;<?php echo $lang['contactsheet']; ?></a>
                </li>
                <?php
                }

            if($allow_share)
                {
                ?>
                <li>
                    <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_share.php?ref=<?php echo urlencode($usercollection); ?>">&gt; <?php echo $lang['share']; ?></a>
                </li>
                <?php 
                hook('aftershareinbottomtoolbar', '', array($usercollection));
                }

            if(($userref == $cinfo['user']) || (checkperm('h')))
                {
                ?>
                <li>
                    <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_edit.php?ref=<?php echo urlencode($usercollection); ?>">&gt;&nbsp;<?php echo $allow_share ? $lang['action-edit'] : $lang['editcollection']; ?></a>
                </li>
                <?php
                }
            
            if($preview_all)
                {
                ?>
                <li>
                    <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/preview_all.php?ref=<?php echo urlencode($usercollection); ?>">&gt;&nbsp;<?php echo $lang['preview_all']; ?></a>
                </li>
                <?php
                }

            hook('collectiontool2');
            
            if($feedback)
                {
                ?>
                <li>
                    <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection); ?>&k=<?php echo urlencode($k); ?>">&gt;&nbsp;<?php echo $lang['sendfeedback']; ?></a>
                </li>
                <?php
                }
            }
        }
    else
        {
        if(!hook('replacecollectionsresearchlinks'))
            {  
            $research = sql_value('SELECT ref value FROM research_request WHERE collection = "' . $usercollection . '"', 0);
            ?>
            <li>
                <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/team/team_research.php">&gt; <?php echo $lang['manageresearchrequests']; ?></a>
            </li>
            <li>
                <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/team/team_research_edit.php?ref=<?php echo urlencode($research); ?>">&gt; <?php echo $lang['editresearchrequests']; ?></a>
            </li>    
            <?php
            }/* end hook replacecollectionsresearchlinks */
        }

    # If this collection is (fully) editable, then display an extra edit all link
    if((count($result) > 0) && $show_edit_all_link && (!$edit_all_checkperms || allow_multi_edit($result))) 
        {
        ?>
        <li>
            <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>">&gt; <?php echo $lang['viewall']; ?></a>
        </li>
        <li>
            <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/edit.php?collection=<?php echo urlencode($usercollection); ?>">&gt; <?php echo $lang['action-editall']; ?></a>
        </li>
        <?php 
        }
    else
        {
        ?>
        <li>
            <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>">&gt; <?php echo $lang['viewall']; ?></a>
        </li>
        <?php
        }

    echo (isset($emptycollection) && $remove_resources_link_on_collection_bar && collection_writeable($usercollection)) ? '<li><a href="'.$baseurl_short.'pages/collections.php?emptycollection='.urlencode($usercollection).'&removeall=true&submitted=removeall&ajax=true" onclick="if(!confirm(\''.$lang['emptycollectionareyousure'].'\')){return false;}return CollectionDivLoad(this);"> &gt; '.$lang["emptycollection"].'</a></li>' : "";
    
    if($count_result > 0)
        {
        # Ability to request a whole collection (only if user has restricted access to any of these resources)
        $min_access = collection_min_access($result);
        if($min_access != 0)
            {
            ?>
            <li>
                <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_request.php?ref=<?php echo urlencode($usercollection); ?>&k=<?php echo urlencode($k); ?>">&gt; <?php echo $lang['requestall']; ?></a>
            </li>
            <?php
            }
        }

    if($download_usage && ((isset($zipcommand) || $collection_download) && $count_result > 0))
        {
        ?>
        <li>
            <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/terms.php?k=<?php echo urlencode($k); ?>&url=<?php echo urlencode("pages/download_usage.php?collection=" .  $usercollection . "&k=" . $k); ?>">&gt; <?php echo $lang['action-download']; ?></a>
        </li>
        <?php
        }
    else if((isset($zipcommand) || $collection_download) && $count_result > 0)
        {
        ?>
        <li>
            <a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/terms.php?k=<?php echo urlencode($k); ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k); ?>">&gt; <?php echo $lang['action-download']; ?></a>
        </li>
        <?php
        }
    }

function HookLegacy_actionsAllAftertogglethumbs()
    {
    global $baseurl_short, $lang, $usercollection, $contact_sheet, $allow_share, $cinfo, $userref,
           $preview_all, $feedback, $k, $result, $collection_download, $count_result, $disable_collection_toggle;

    ?>
    <ul style="float: right;">
    <?php
    if((!collection_is_research_request($usercollection)) || (!checkperm('r')))
        {
        hook('beforecollectionminlinks');

        if(checkperm('s'))
            {
            if($contact_sheet == true)
                {
                ?>
                <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/contactsheet_settings.php?ref=<?php echo urlencode($usercollection); ?>">&nbsp;<?php echo $lang['contactsheet']; ?></a></li>
                <?php
                }

            if($allow_share)
                {
                ?>
                <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_share.php?ref=<?php echo urlencode($usercollection); ?>"><?php echo $lang['share']; ?></a></li>
                <?php
                }

            if(($userref == $cinfo['user']) || (checkperm('h')))
                {
                ?>
                <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_edit.php?ref=<?php echo urlencode($usercollection); ?>">&nbsp;<?php echo $allow_share ? $lang['action-edit'] : $lang['editcollection']; ?></a></li>
                <?php
                }

            if($preview_all)
                {
                ?>
                <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/preview_all.php?ref=<?php echo urlencode($usercollection); ?>"><?php echo $lang['preview_all']; ?></a></li>
                <?php
                }

            hook('collectiontool2min');

            if($feedback)
                {
                ?>
                <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_feedback.php?collection=<?php echo urlencode($usercollection); ?>&k=<?php echo urlencode($k); ?>">&nbsp;<?php echo $lang['sendfeedback']; ?></a></li>
                <?php
                }
            }
        }
    else
        {
        if(!hook('replacecollectionsresearchlinks'))
            {
            $research = sql_value("SELECT ref value FROM research_request WHERE collection = '" . $usercollection . "'", 0);
            ?>
            <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/team/team_research.php"><?php echo $lang['manageresearchrequests']; ?></a></li>
            <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/team/team_research_edit.php?ref=<?php echo urlencode($research); ?>"><?php echo $lang['editresearchrequests']; ?></a></li>
            <?php
            } /* end hook replacecollectionsresearchlinks */
        }

    # If this collection is (fully) editable, then display an extra edit all link
    if((count($result) > 0) && checkperm('e' . $result[0]['archive']) && allow_multi_edit($result))
        {
        ?>
        <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $usercollection); ?>"><?php echo $lang['viewall']; ?></a></li>
        <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/edit.php?collection=<?php echo $usercollection; ?>"><?php echo $lang['action-editall']; ?></a></li>
        <?php
        }
    else
        {
        ?>
        <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/search.php?search=<?php echo urlencode('!collection' . $usercollection); ?>"><?php echo $lang['viewall']; ?></a></li>
        <?php
        }

    echo (isset($emptycollection) && $remove_resources_link_on_collection_bar && collection_writeable($usercollection)) ? '<li><a href="' . $baseurl_short . 'pages/collections.php?emptycollection=' . urlencode($usercollection) . '&removeall=true&submitted=removeall&ajax=true" onclick="if(!confirm(\'' . $lang['emptycollectionareyousure'] . '\')){return false;}return CollectionDivLoad(this);">' . $lang['emptycollection'] . '</a></li>' : '';

    if((isset($zipcommand) || $collection_download) && $count_result > 0)
        {
        ?>
        <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/terms.php?k=<?php echo $k; ?>&url=<?php echo urlencode("pages/collection_download.php?collection=" .  $usercollection . "&k=" . $k); ?>"><?php echo $lang['action-download']; ?></a></li>
        <?php
        }

    if($count_result > 0 && $k == '' && checkperm('q'))
        {
        # Ability to request a whole collection (only if user has restricted access to any of these resources)
        $min_access = collection_min_access($result);
        if($min_access != 0)
            {
            ?>
            <li><a onclick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short; ?>pages/collection_request.php?ref=<?php echo urlencode($usercollection); ?>"><?php echo $lang['action-request']; ?></a></li>
            <?php
            }
        }

    hook('collectiontoolmin');
    ?>
    </ul>
    <?php
    }