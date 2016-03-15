<?php
include_once __DIR__ . '/../include/search_functions.php';

function HookLegacy_actionsSearchRender_sort_order_differently($orderFields)
    {
    foreach($orderFields as $order => $label)
        {
        display_sort_order($order, $label);
        }

    return true;
    }

function HookLegacy_actionsSearchAdd_search_title_links()
    {
    global $baseurl, $baseurl_short, $lang, $search, $k, $userrequestmode, $collection_download, $contact_sheet,
           $manage_collections_contact_sheet_link, $manage_collections_share_link, $allow_share,
           $manage_collections_remove_link, $username, $collection_purge, $show_edit_all_link, $edit_all_checkperms,
           $preview_all, $order_by, $sort, $archive, $collectiondata, $result, $search_title, $display, $search_title_links;

    // extra collection title links
    if(substr($search, 0, 11) == "!collection")
        {
        if($k == "" && !checkperm("b") && ($userrequestmode != 2 && $userrequestmode != 3))
            {
            $search_title_links = "<div class='CollectionTitleLinks'>";
            
            if($k == "" && !checkperm("b") && ($userrequestmode != 2 && $userrequestmode != 3))
                {
                $search_title_links.='<a href="#" onclick="ChangeCollection(' . $collectiondata["ref"] . ', \'\');">&gt;&nbsp;'.$lang["selectcollection"].'</a>';
                }
            
            if(isset($zipcommand) || $collection_download) 
                {
                $search_title_links.="<a onClick='return CentralSpaceLoad(this,true);' href='" . $baseurl_short . "pages/terms.php?url=" . urlencode("pages/collection_download.php?collection=" . $collectiondata["ref"])."'>&gt;&nbsp;".$lang["action-download"]."</a>";
                }
            
            if($contact_sheet == true && $manage_collections_contact_sheet_link) 
                {
                $search_title_links.="<a onClick='return CentralSpaceLoad(this,true);' href='" . $baseurl_short . "pages/contactsheet_settings.php?ref=" . urlencode($collectiondata["ref"]) . "'>&gt;&nbsp;".$lang["contactsheet"]."</a>";
                }
            
            if ($manage_collections_share_link && $allow_share && (checkperm("v") || checkperm ("g"))) 
                {
                $search_title_links.="&nbsp;<a href='".$baseurl_short."pages/collection_share.php?ref=" . $collectiondata["ref"] . "' onClick='return CentralSpaceLoad(this,true);'>&gt;&nbsp;".$lang["share"]."</a>";
                }
            
            if($manage_collections_remove_link && $username != $collectiondata["username"])
                {
                $search_title_links.="&nbsp;<a href='#' onclick=\" if(confirm('".$lang["removecollectionareyousure"]."')){document.getElementById('collectionremove').value='" . urlencode($collectiondata["ref"]) . "';document.getElementById('collectionform').submit();} return false;\">&gt;&nbsp;".$lang["action-remove"]."</a>";
                }
            
            if((($username==$collectiondata["username"]) || checkperm("h")) && ($collectiondata["cant_delete"]==0)) 
                {
                $search_title_links.="&nbsp;<a href='#'' onclick=\"if (confirm('".$lang["collectiondeleteconfirm"]."')) {document.getElementById('collectiondelete').value='" . urlencode($collectiondata["ref"]) . "';CentralSpacePost(document.getElementById('collectionform'),false);} return false;\">&gt;&nbsp;".$lang["action-delete"]."</a>";
                }

            if($collection_purge)
                { 
                if($n == 0) 
                    {
                    $search_title_links.="<input type=hidden name='purge' id='collectionpurge' value=''>"; 
                    }
                if(isset($collections) && checkperm("e0") && $collectiondata["cant_delete"] == 0) 
                    {
                    $search_title_links.="&nbsp;<a href='#' onclick=\"if (confirm('".$lang["purgecollectionareyousure"]."')){document.getElementById('collectionpurge').value='".urlencode($collections[$n]["ref"])."';document.getElementById('collectionform').submit();} return false;\">&gt;&nbsp;".$lang["purgeanddelete"]."</a>"; 
                    }
                }

            hook('additionalcollectiontool');
            
            if(($username == $collectiondata["username"]) || (checkperm("h"))) 
                {
                $search_title_links.="<a href='".$baseurl_short."pages/collection_edit.php?ref=" . urlencode($collectiondata["ref"]) . "' onClick='return CentralSpaceLoad(this,true);' >&gt;&nbsp;".$lang["action-edit"]."</a>";
                }
            
            # If this collection is (fully) editable, then display an edit all link
            if($show_edit_all_link && (count($result) > 0))
                {
                if(!$edit_all_checkperms || allow_multi_edit($collectiondata["ref"])) 
                    { 
                    $search_title_links.="&nbsp;<a href='".$baseurl_short."pages/edit.php?collection=" . urlencode($collectiondata["ref"]) . "' onClick='return CentralSpaceLoad(this,true);'>&gt;&nbsp;".$lang["action-editall"]."</a>";
                    } 
                }

            if(($username == $collectiondata["username"]) || (checkperm("h"))) 
                {
                $search_title_links.="<a href='".$baseurl_short."pages/collection_log.php?ref=" . urlencode($collectiondata["ref"]) . "' onClick='return CentralSpaceLoad(this,true);'>&gt;&nbsp;".$lang["log"]."</a>"; 
                }

            hook("addcustomtool");
            
            $search_title_links.="</div>";
            // END INSERT
            }

        if(count($result) != 0 && $k == "" && $preview_all)
            {
            $search_title_links.='<a href="' . $baseurl_short.'pages/preview_all.php?ref=' . $collectiondata["ref"] . '&amp;order_by=' . urlencode($order_by) . '&amp;sort=' . urlencode($sort) . '&amp;archive=' . urlencode($archive) . '&amp;k=' . urlencode($k) . '">&gt;&nbsp;'.$lang['preview_all'].'</a>';
            }

        if($display != "list")
            {
            $search_title_links .= '<br />';
            }
        }
    }


function HookLegacy_actionsSearchAdd_bottom_in_page_nav_left()
    {
    global $baseurl_short, $search, $restypes, $archive, $daylimit, $lang, $home_dash, $url, $allow_smart_collections,
           $starsearch, $resources_count, $show_searchitemsdiskusage, $offset, $order_by, $sort, $k, $allow_save_search;
    ?>
    <div class="BottomInpageNavLeft">
    <?php 
    if(!hook("replacesearchbottomnav"))
        {
        if (!checkperm("b") && $k=="") 
            {
            if($allow_save_search) { ?><div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsearch=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["savethissearchtocollection"]?></a></div><?php }
            #Home_dash is on, And not Anonymous use, And (Dash tile user (Not with a managed dash) || Dash Tile Admin)
            if($home_dash && checkPermission_dashcreate()) 
                {?> 
                <div class="InpageNavLeftBlock">
                    <a onClick="return CentralSpaceLoad(this);" href="<?php echo $baseurl_short;?>pages/dash_tile.php?create=true&tltype=srch&freetext=true&link=<?php echo $url;?>" onClick="jQuery('this').href='<?php echo $baseurl_short;?>pages/dash_tile.php?create=true&tltype=srch&link='+window.location.href;">
                        &gt;&nbsp;<?php echo $lang["savethissearchtodash"];?>
                    </a>
                </div>
                <?php 
                }
            if($allow_smart_collections && substr($search,0,11)!="!collection") { ?><div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsmartcollection=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;starsearch=<?php echo urlencode($starsearch) ?>">&gt;&nbsp;<?php echo $lang["savesearchassmartcollection"]?></a></div><?php }
            global $smartsearch; 
            if($allow_smart_collections && substr($search,0,11)=="!collection" && (is_array($smartsearch[0]) && !empty($smartsearch[0]))) { $smartsearch=$smartsearch[0];?><div class="InpageNavLeftBlock"><a onClick="return CentralSpaceLoad(this,true);" href="search.php?search=<?php echo urlencode($smartsearch['search'])?>&amp;restypes=<?php echo urlencode($smartsearch['restypes'])?>&amp;archive=<?php echo urlencode($smartsearch['archive']) ?>&amp;starsearch=<?php echo urlencode($smartsearch['starsearch']) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["dosavedsearch"]?></a></div><?php }
            if ($resources_count!=0)
                {
                if (!hook('replacesavesearchitemstocollection')) { ?>
                <div class="InpageNavLeftBlock"><a onClick="return CollectionDivLoad(this);" href="<?php echo $baseurl_short?>pages/collections.php?addsearch=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;mode=resources&amp;daylimit=<?php echo urlencode($daylimit) ?>">&gt;&nbsp;<?php echo $lang["savesearchitemstocollection"]?></a></div>
                <?php
                }
                if($show_searchitemsdiskusage) 
                    {?>
                    <div class="InpageNavLeftBlock"><a onClick="return CentralSpaceLoad(this, true);" href="<?php echo $baseurl_short?>pages/search_disk_usage.php?search=<?php echo urlencode($search)?>&amp;restypes=<?php echo urlencode($restypes)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive) ?>&amp;daylimit=<?php echo urlencode($daylimit) ?>&amp;k=<?php echo urlencode($k)?>&amp;restypes=<?php echo urlencode($restypes) ?>">&gt;&nbsp;<?php echo $lang["searchitemsdiskusage"]?></a></div>
                    <?php 
                    }
                }
            }

        if($k == '')
            {
            ?>
            <div class="InpageNavLeftBlock">
                <a href="/pages/csv_export_results_metadata.php?search=<?php echo urlencode($search); ?>&restype=<?php echo urlencode($restypes); ?>&order_by=<?php echo urlencode($order_by); ?>&archive=<?php echo urlencode($archive); ?>&sort=<?php echo urlencode($sort); ?>&starsearch=<?php echo urlencode($starsearch); ?>">&gt;&nbsp;<?php echo $lang['csvExportResultsMetadata']; ?></a>
            </div>
            <?php
            }
        hook("resultsbottomtoolbar");
        }?>
    <!--End of hook("replacesearchbottomnav")-->
    </div>
    <?php
    }