<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';
if(!checkperm('a'))
    {
    header('HTTP/1.1 401 Unauthorized');
    exit('Permission denied.');
    }
include '../../include/admin_functions.php';
include '../../include/resource_functions.php';

$slideshow_files = get_slideshow_files_data();

$ajax         = getvalescaped('ajax', '');
$action       = getvalescaped('action', '');
$slideshow_id = getvalescaped('slideshow_id', null, true);
$manageurl = $baseurl . "/pages/admin/admin_manage_slideshow.php";

/* Re-order */
if('true' === $ajax && ('moveup' === $action || 'movedown' === $action) && !is_null($slideshow_id))
    {
    $response['sibling']           = null;
    $response['is_first_sibling']  = false;
    $response['is_last_sibling'] = false;

    $allow_reorder = false;

    // Set array pointer to the slideshow ID
    reset($slideshow_files);
    while(!in_array(key($slideshow_files), array($slideshow_id, null)))
        {
        next($slideshow_files);
        }
    // We found the key and set the pointer!
    if(false !== current($slideshow_files))
        {
        key($slideshow_files);
        }

    // Based on current pointer and direction of movement we can find the "to" element
    switch($action)
        {
        case 'moveup':
            prev($slideshow_files);
            $to = key($slideshow_files);

            if(!is_null($to))
                {
                $allow_reorder       = true;
                $response['sibling'] = $to;
                }

            // Check if the new location will be the first one
            prev($slideshow_files);
            if(is_null(key($slideshow_files)))
                {
                $response['is_first_sibling'] = true;
                }
            break;

        case 'movedown':
            next($slideshow_files);
            $to = key($slideshow_files);

            if(!is_null($to))
                {
                $allow_reorder       = true;
                $response['sibling'] = $to;
                }

            // Check if the new location will be the last one
            next($slideshow_files);
            if(is_null(key($slideshow_files)))
                {
                $response['is_last_sibling'] = true;
                }
            break;
        }

    if($allow_reorder)
        {
        reorder_slideshow_images($slideshow_id, $to);
        }

    echo json_encode($response);
    exit();
    }

/* Delete */
if('true' === $ajax && 'delete' === $action && !is_null($slideshow_id))
    {
    $response['error']   = '';
    $response['success'] = true;

    if(file_exists($slideshow_files[$slideshow_id]['file_path']) && unlink($slideshow_files[$slideshow_id]['file_path']) === false)
        {
        $response['error']   = 'Failed to delete "' . $slideshow_files[$slideshow_id]['file_path']. '"';
        $response['success'] = false;
        }

    if(isset($slideshow_files[$slideshow_id]['link_file_path']) && file_exists($slideshow_files[$slideshow_id]['link_file_path']) && unlink($slideshow_files[$slideshow_id]['link_file_path']) === false)
        {
        $response['error']   = 'Failed to delete "' . $slideshow_files[$slideshow_id]['link_file_path']. '"';
        $response['success'] = false;
        }

    echo json_encode($response);
    exit();
    }

if('true' === $ajax && getval("static","")!="")
    {
    if(getval("static","")=="true")
        {
        set_config_option($userref, 'static_slideshow_image', true);
        }
    else
        {
        set_config_option($userref, 'static_slideshow_image', false);       
        }
    }
    
include '../../include/header.php';
?>
<div class="BasicsBox">
    <p>
        <a href="<?php echo $baseurl_short; ?>pages/admin/admin_home.php" onClick="return CentralSpaceLoad(this, true);">&lt;&nbsp;<?php echo $lang['back']; ?></a>
    </p>
    <h1><?php echo $lang['manage_slideshow']; ?></h1>

<?php
$i = 0;
foreach($slideshow_files as $slideshow_image => $slideshow_file_info)
    {
    if(file_exists($slideshow_file_info['file_path']))
        {
		$login_image=false;
		if($login_background && $i==0){$login_image=true;}
        ++$i;
        $slideshow_image_src = $baseurl_short . $homeanim_folder . '/' . $slideshow_image . '.jpg' . '?nc=' . time();
        ?>
    <div id="slideshow_<?php echo $slideshow_image; ?>" class="Question">
        <label>
            <img id="slideshow_img_<?php echo $slideshow_image; ?>" <?php if($login_image){echo "class=\"highlighted\"";} ?> src="<?php echo $slideshow_image_src; ?>" alt="Slideshow Image <?php echo $slideshow_image; ?>" width="150" height="80">
        </label>
		<?php if($login_image){echo $lang["login_slideshow_image_notes"] . "<br /><br />";} ?>
        <div class="AutoSaveStatus">
            <span id="AutoSaveStatus-<?php echo $slideshow_image; ?>" style="display:none;"></span>
        </div>
        <span class="stdwidth">
            <button id="slideshow_<?php echo $slideshow_image; ?>_moveup"
                    type="submit"
                    onclick="ReorderSlideshowImage(<?php echo $slideshow_image; ?>, 'moveup');"
                    <?php if(1 === $i) { echo 'disabled'; } ?>><?php echo $lang['action-move-up']; ?></button>
            <button id="slideshow_<?php echo $slideshow_image; ?>_movedown"
                    type="submit"
                    onclick="ReorderSlideshowImage(<?php echo $slideshow_image; ?>, 'movedown');"
                    <?php if(count($slideshow_files) === $i) { echo 'disabled'; } ?>><?php echo $lang['action-move-down']; ?></button>
            <?php hook('render_replace_button_for_manage_slideshow', '', array($slideshow_image)); ?>
            <button id="slideshow_<?php echo $slideshow_image; ?>_delete"
                    type="submit" onclick="DeleteSlideshowImage(<?php echo $slideshow_image; ?>);"<?php if(count($slideshow_files)==1) { echo 'disabled'; } ?>><?php echo $lang['action-delete']; ?></button>
            <?php hook('render_replace_slideshow_form_for_manage_slideshow', '', array($slideshow_image, $slideshow_files)); ?>
        </span>
		<div class="clearerleft"></div>
    </div>
        <?php
        }
    }
    
    if($slideshow_big)
        {?>
        <div id="slideshow_static_image" class="Question">
            <label>
            <?php echo $lang["slideshow_use_static_image"]; ?>    
            </label>
            <input type="checkbox" name="slideshow_static_image" id="slideshow_static_image_checkbox" <?php if($static_slideshow_image){echo "checked";} ?> onchange="if(this.checked){jQuery.post('<?php echo $manageurl ?>?ajax=true&static=true');}else{jQuery.post('<?php echo $manageurl ?>?ajax=true&static=false');}"></input>
        </div>
        <div class="clearerleft"></div>
        <?php
        }
    hook('render_new_element_for_manage_slideshow', '', array($slideshow_files));
?>
</div>
<script>
function ReorderSlideshowImage(id, direction)
    {
    var post_url  = '<?php echo $manageurl ?>';
    var post_data =
        {
        ajax: true,
        action: direction,
        slideshow_id: id
        };

    jQuery.post(post_url, post_data, function(response)
        {
        if(response.sibling && response.sibling > 0)
            {
            var from_img_elem = jQuery('#slideshow_img_' + id);
            var to_img_elem   = jQuery('#slideshow_img_' + response.sibling);
            var from_img_src  = from_img_elem.attr('src');
            var to_img_src    = to_img_elem.attr('src');

            // Swap the images to reflect reordering visually
            to_img_elem.attr('src', from_img_src);
            from_img_elem.attr('src', to_img_src);

            jQuery('#slideshow_' + response.sibling + '_moveup').prop('disabled', false);
            jQuery('#slideshow_' + response.sibling + '_movedown').prop('disabled', false);

            // Check response for any information regarding position
            // if first then disable Move up button
            if(response.is_first_sibling)
                {
                jQuery('#slideshow_' + response.sibling + '_moveup').prop('disabled', true);
                }

            // if last then disable Move down button
            if(response.is_last_sibling)
                {
                jQuery('#slideshow_' + response.sibling + '_movedown').prop('disabled', true);
                }
            }
        }, 'json');

    return false;
    }

function DeleteSlideshowImage(id)
    {
    var post_url  = '<?php echo $manageurl ?>';
    var post_data =
        {
        ajax: true,
        action: 'delete',
        slideshow_id: id
        };

    jQuery.post(post_url, post_data, function(response)
        {
        if(response.success)
            {
            jQuery('#slideshow_' + id).remove();

            // Make sure, appropriate buttons are still getting disabled
            var slideshow_ids = jQuery('div[id*="slideshow_"].Question');
            slideshow_ids.first().find('button[id*="_moveup"').prop('disabled', true);
            slideshow_ids.last().find('button[id*="_movedown"').prop('disabled', true);
            if (slideshow_ids.find('button[id*="_delete"').length==1)
                {
                slideshow_ids.find('button[id*="_delete"').prop('disabled', true);
                }

            }
        }, 'json');

    return false;
    }
</script>
<?php
include '../../include/footer.php';
