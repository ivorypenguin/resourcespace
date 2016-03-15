<?php
function display_sort_order($name, $label)
    {
    global $order_by;
    if(isset($GLOBALS['display_fields_added']))
        echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
    else
        $GLOBALS['display_fields_added'] = true;

    $fixedOrder = $name == 'relevance';
    $selected   = $order_by == $name;

    if($selected && $fixedOrder)
        {
        ?>
        <span class="Selected"><?php echo $label?></span>
        <?php
        }
    else
        {
        global $baseurl_short, $revsort, $search, $archive, $restypes, $k, $sort;
        if($selected)
            {
            ?>
            <span class="Selected">
            <?php
            }
            ?>
        <a href="<?php echo $baseurl_short?>pages/search.php?search=<?php
            echo urlencode($search) . '&amp;order_by=' . $name . '&amp;archive='
            . urlencode($archive) . '&amp;k=' . urlencode($k) . '&amp;restypes='
            . urlencode($restypes);
            if($selected)
                {
                echo '&amp;sort=' . urlencode($revsort);
                }
            ?>" onClick="return CentralSpaceLoad(this);"><?php echo $label ?></a><?php
        if(!$fixedOrder && $selected)
            {
            ?>
            <div class="<?php echo urlencode($sort); ?>">&nbsp;</div>
            <?php
            }
            
        if($selected)
            {
            ?>
            </span>
            <?php
            }
        }
    }