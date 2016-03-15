<?php
function HookTransformAdmin_manage_slideshowRender_new_element_for_manage_slideshow(array $slideshow_files)
    {
    global $baseurl, $lang;

    $return_to_url = $baseurl . '/pages/admin/admin_manage_slideshow.php';

    // Calculate the next slideshow image ID (ie. filename will be ID.jpg)
    $new_slideshow_id = 1;
    if(false !== reset($slideshow_files))
        {
        end($slideshow_files);
        $new_slideshow_id = key($slideshow_files) + 1;
        }
    ?>
    <div id="add_new_slideshow" class="Question">
        <label></label>
        <span class="stdwidth">
            <button type="submit" onclick="jQuery('#new_slideshow_form').fadeIn(); return false;"><?php echo $lang['action-add-new']; ?></button>
            <form
                id="new_slideshow_form"
                method="POST"
                action="<?php echo $baseurl; ?>/plugins/transform/pages/crop.php"
                onsubmit="return CentralSpacePost(this);"
            >
                <input 
                    name="ref"
                    type="text"
                    value="<?php echo $lang['resourceid']; ?>"
                    onfocus="if(this.value == '<?php echo $lang['resourceid']; ?>') { this.value = ''; }"
                    onblur="if(this.value == '') {this.value = '<?php echo $lang['resourceid']; ?>';}"
                >
                <input name="manage_slideshow_action" type="hidden" value="add_new">
                <input name="manage_slideshow_id" type="hidden" value="<?php echo $new_slideshow_id; ?>">
                <input name="return_to_url" type="hidden" value="<?php echo $return_to_url; ?>">
                <button type="submit"><?php echo $lang['action-submit-button-label']; ?></button>
            </form>
        </span>
        <div class="clearerleft"></div>
    </div>
    <?php
    }


function HookTransformAdmin_manage_slideshowRender_replace_button_for_manage_slideshow($slideshow_image)
    {
    global $lang;
    ?>
    <button type="submit" onclick="jQuery('#replace_slideshow_image_form_<?php echo $slideshow_image; ?>').fadeIn(); return false;"><?php echo $lang['action-replace']; ?></button>
    <?php
    }


function HookTransformAdmin_manage_slideshowRender_replace_slideshow_form_for_manage_slideshow($slideshow_image, array $slideshow_files)
    {
    global $baseurl, $lang;

    $return_to_url = $baseurl . '/pages/admin/admin_manage_slideshow.php';

    // Calculate the next slideshow image ID (ie. filename will be ID.jpg)
    $replace_slideshow_id = null;
    if(false !== reset($slideshow_files))
        {
        while(!in_array(key($slideshow_files), array($slideshow_image, null)))
            {
            next($slideshow_files);
            }

        if(false !== current($slideshow_files))
            {
            $replace_slideshow_id = key($slideshow_files);
            }
        }

    // if there is no slideshow
    if(is_null($replace_slideshow_id))
        {
        return;
        }
    ?>
    <form
        id="replace_slideshow_image_form_<?php echo $slideshow_image; ?>"
        method="POST"
        action="<?php echo $baseurl; ?>/plugins/transform/pages/crop.php"
        onsubmit="return CentralSpacePost(this);"
    >
        <input
            name="ref"
            type="text"
            value="<?php echo $lang['resourceid']; ?>"
            onfocus="if(this.value == '<?php echo $lang['resourceid']; ?>') { this.value = ''; }"
            onblur="if(this.value == '') {this.value = '<?php echo $lang['resourceid']; ?>';}"
        >
        <input name="manage_slideshow_action" type="hidden" value="replace">
        <input name="manage_slideshow_id" type="hidden" value="<?php echo $replace_slideshow_id; ?>">
        <input name="return_to_url" type="hidden" value="<?php echo $return_to_url; ?>">
        <button type="submit"><?php echo $lang['action-submit-button-label']; ?></button>
    </form>
    <?php
    }
