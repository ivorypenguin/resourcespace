<?php

function HookConditional_termsViewDownloadlink($baseparams)
    {
    global $baseurl, $conditional_terms_field, $conditional_terms_value, $fields, $search, $order_by, $archive, $sort, $offset;
    $showterms=false;
    foreach($fields as $field)
        {
        if($field['fref']==$conditional_terms_field && (trim($field['value'])==$conditional_terms_value || trim($field['value'])=="," . $conditional_terms_value))
            {$showterms=true;}
        }
    
    if(!$showterms){return false;}
    
    ?>href="<?php echo $baseurl ?>/pages/terms.php?<?php echo $baseparams ?>&amp;search=<?php
            echo urlencode($search) ?>&amp;url=<?php
            echo urlencode("pages/download_progress.php?" . $baseparams . "&search=" . urlencode($search)
                    . "&offset=" . $offset . "&archive=" . $archive . "&sort=".$sort."&order_by="
                    . urlencode($order_by))?>&noredir=true"<?php
    
    return true;
    }
