<?php

function HookSearch_tilesSearchReplacedisplayselector($search="",$collections="")
    {
    if ((substr($search,0,11)!="!collection") && ($collections!="") && is_array($collections))
        {return true;}
    return false;
    }


function HookSearch_tilesSearchReplacesearchpublic($search="",$collections="")
    {
    if ((substr($search,0,11)!="!collection")&&($collections!="")&&is_array($collections))
        {
        global $baseurl_short, $collection_prefix, $search_tiles_text_shadow,$search_tiles_collection_count;
        $tile_height=180;
        $tile_width=250;
        for ($n=0;$n<count($collections);$n++)
            {
            $resources=do_search("!collection".$collections[$n]['ref'],"","relevance","",1);
            $hook_result=hook("process_search_results","",array("result"=>$resources,"search"=>"!collection".$collections[$n]['ref']));
            if ($hook_result!==false) {$resources=$hook_result;}
            
            echo "<a href=\"" . $baseurl_short . "pages/search.php?search=" . urlencode("!collection" . $collections[$n]["ref"]) . "\" onClick=\"return CentralSpaceLoad(this,true);\" 
			class=\"HomePanel DashTile\" id=\"search_tile_col" . $collections[$n]['ref'] . "\">
			<div id=\"contents_search_tile_col" . $collections[$n]['ref'] . "\" class=\"HomePanelIN HomePanelDynamicDash " . (($search_tiles_text_shadow)? "TileContentShadow":"") . "\">
			";
            $count=count($resources);
            if($count>0)
                {
                $previewresource=$resources[0];
                //exit(print_r($previewresource));
                $defaultpreview=false;
                $previewpath=get_resource_path($previewresource["ref"],true,"pre",false, "jpg", -1, 1, false);
                if (file_exists($previewpath))
                    {
                    $previewpath=get_resource_path($previewresource["ref"],false,"pre",false, "jpg", -1, 1, false);
                    ?>
                    <img 
                        src="<?php echo $previewpath ?>" 
                        <?php 
                        if($defaultpreview)
                            {
                            ?>
                            style="position:absolute;top:<?php echo ($tile_height-128)/2 ?>px;left:<?php echo ($tile_width-128)/2 ?>px;"
                            <?php
                            }
                        else 
                            {
                            #fit image to tile size
                            if(($previewresource["thumb_width"]*0.7)>=$previewresource["thumb_height"])
                                {
                                $ratio = $previewresource["thumb_height"] / $tile_height;
                                $width = $previewresource["thumb_width"] / $ratio;
                                if($width<$tile_width){echo "width='100%' ";}
                                else {echo "height='100%' ";}
                                }
                            else
                                {
                                $ratio = $previewresource["thumb_width"] / $tile_width;
                                $height = $previewresource["thumb_height"] / $ratio;
                                if($height<$tile_height){echo "height='100%' ";}
                                else {echo "width='100%' ";}
                                }
                            ?>
                            style="position:absolute;top:0;left:0;"
                            <?php
                            }?>
                        class="thmbs_tile_img"
                    />
                    <?php
                    }
                }?>
                <h2 class="title thmbs_tile">
                <?php echo htmlspecialchars(str_replace(array("\"","'"),"", $collection_prefix . i18n_get_collection_name($collections[$n]))); ?>
                </h2>
                <?php
                if($search_tiles_collection_count)
                    {?>
                    <p class="tile_corner_box">
                    <span class="count-icon"></span>
                    <?php echo $count; ?>
                    </p>
                    <?php
                    }
                    ?>
            </div></a>
            <?php            
            }
        return true;
        }
    return false;        
    }
