<?php
include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php';
include_once '../../../include/resource_functions.php';
include_once '../include/vimeo_publish_functions.php';


use Vimeo\Vimeo;
use Vimeo\Exceptions\VimeoUploadException;


$ref           = getvalescaped('resource', getvalescaped('vimeo_publish_resource', ''));
$access        = get_resource_access($ref);
$resource_data = get_resource_data($ref);
$error         = '';


if(0 != $access)
    {
    exit($lang['vimeo_publish_access_denied']);
    }


if(getvalescaped('delete_token', false))
    {
    delete_vimeo_token($userref);

    // Delete cookie
    rs_setcookie('vimeo_publish_resource', $ref, -1, 'plugins/vimeo_publish/pages/');
    }

// Store resource ID in a cookie while we authenticate to not lose it
if('' != $ref)
    {
    rs_setcookie('vimeo_publish_resource', $ref, 1, 'plugins/vimeo_publish/pages/');
    }

// Initialize VIMEO
init_vimeo_api($vimeo_publish_client_id, $vimeo_publish_client_secret, $vimeo_callback_url);
$vimeo_publish_access_token = get_access_token($vimeo_publish_client_id, $vimeo_publish_client_secret, $vimeo_callback_url);
$vimeo_user_data            = array();


// Try uplpoading resource to Vimeo
$successfully_uploaded = false;
if(getvalescaped('upload', false))
    {
    $video_id   = '';
    $file_path  = get_resource_path($ref, true, '', false, $resource_data['file_extension'], -1, 1, false, '', -1);
    $parameters = array(
        'name'        => getvalescaped('video_title', ''),
        'description' => getvalescaped('video_description', '')
    );

    if(vimeo_upload($vimeo_publish_client_id, $vimeo_publish_client_secret, $vimeo_publish_access_token, $ref, $file_path, $vimeo_publish_vimeo_link_field, $video_id, $error)
        && set_video_information($vimeo_publish_client_id, $vimeo_publish_client_secret, $vimeo_publish_access_token, $video_id, $parameters)
    )
        {
        $successfully_uploaded = true;
        }
    }


if(0 < $vimeo_publish_vimeo_link_field)
    {
    $vimeo_url = sql_value("SELECT `value` FROM resource_data WHERE resource = '{$ref}' AND resource_type_field = '{$vimeo_publish_vimeo_link_field}'", '');

    // Show confirmation message
    if($successfully_uploaded && '' !== $vimeo_url)
        {
        $error = str_replace('[vimeo_url]', "<a href=\"{$vimeo_url}\" target=\"_blank\">{$vimeo_url}</a>", $lang['vimeo_publish_resource_published']);
        }
    // Show warning that video has already been published to Vimeo
    else if('' !== $vimeo_url)
        {
        $error = str_replace(array('[ref]', '[vimeo_url]'), array($ref, "<a href=\"{$vimeo_url}\" target=\"_blank\">{$vimeo_url}</a>"), $lang['vimeo_publish_resource_already_published']);
        }
    }

if(0 < $vimeo_publish_video_title_field)
    {
    $default_video_title = sql_value("SELECT `value` FROM resource_data WHERE resource = '{$ref}' AND resource_type_field = '{$vimeo_publish_video_title_field}'", '');
    }

if(0 < $vimeo_publish_video_description_field)
    {
    $default_video_description = sql_value("SELECT `value` FROM resource_data WHERE resource = '{$ref}' AND resource_type_field = '{$vimeo_publish_video_description_field}'", '');
    }

if(0 < $vimeo_publish_video_tags_field)
    {
    $default_video_tags = sql_value("SELECT `value` FROM resource_data WHERE resource = '{$ref}' AND resource_type_field = '{$vimeo_publish_video_tags_field}'", '');
    }


include '../../../include/header.php';
?>
<a href="<?php echo $baseurl_short; ?>pages/view.php?ref=<?php echo $ref; ?>" onClick="return CentralSpaceLoad(this, true);"><?php echo LINK_CARET_BACK ?><?php echo $lang['backtoresourceview']; ?></a></p>

<?php
if('' != $error)
    {
    ?>
    <div class="PageInformal"><?php echo $error; ?></div>
    <?php
    include '../../../include/footer.php';

    exit();
    }

// Show which user we will be publishing as...
if(get_vimeo_user($vimeo_publish_client_id, $vimeo_publish_client_secret, $vimeo_publish_access_token, $vimeo_user_data))
    {
    ?>
    <div class="Question">
        <p><?php echo $lang['vimeo_publish_publish_as_user']; ?><a href="<?php echo $vimeo_user_data['link']; ?>" target="_blank"><strong><?php echo $vimeo_user_data['name']; ?></strong> (<?php echo ucfirst($vimeo_user_data['account']); ?> account - <?php echo formatfilesize($vimeo_user_data['upload_quota_free']); ?> free)</a></p>
        <p>
            <a href="<?php echo $vimeo_callback_url; ?>?resource=<?php echo $ref; ?>&delete_token=true">&gt;&nbsp;<?php echo $lang['vimeo_publish_delete_token']; ?></a>
        </p>
    </div>
    <?php
    }
    ?>

<form method="post" action="<?php echo $vimeo_callback_url; ?>?resource=<?php echo $ref; ?>">
    <input type="hidden" name="upload" value="true"/>
    <div class="Question">
        <br>
        <h2><?php echo $lang['vimeo_publish_video_details']; ?></h2>
    </div>
    <div class="Question">
        <label for="video_title"><?php echo $lang['vimeo_publish_video_title'] ?></label>
        <input type="text" class="stdwidth" name="video_title" value="<?php echo $default_video_title; ?>"/>
        <br>
        <label for="video_description"><?php echo $lang['vimeo_publish_video_description']; ?></label>
        <textarea class="stdwidth" rows="6" columns="50" id="video_description" name="video_description"><?php echo strip_tags($default_video_description); ?></textarea>
        <br>
        <label></label>
        <input type="submit" value="<?php echo $lang['vimeo_publish_button_text']; ?>" onClick="return confirmSubmit();"/>
    </div>
    <script>
    function confirmSubmit()
        {
        var agree = confirm("<?php echo $lang['vimeo_publish_legal_warning']; ?>");

        if(agree)
            {
            return true;
            }

        return false;
        }
    </script>
</form>
<?php
include '../../../include/footer.php';