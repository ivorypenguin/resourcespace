<?php 
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';
if(!checkperm('ex'))
    {
    header('HTTP/1.1 401 Unauthorized');
    exit('Permission denied!');
    }
include_once '../../include/resource_functions.php';
include_once '../../include/collections_functions.php';
include_once '../../include/render_functions.php';

$ajax              = ('true' == getval('ajax', '') ? true : false);
$delete_access_key = getvalescaped('delete_access_key', '');


// Process access key deletion
if($ajax && '' != $delete_access_key)
    {
    $resource   = getvalescaped('resource', '');
    $collection = getvalescaped('collection', '');
    $response   = array(
        'success' => false
    );

    if('' != $resource)
        {
        delete_resource_access_key($resource, $delete_access_key);
        $response['success'] = true;
        }
    
    if('' != $collection)
        {
        delete_collection_access_key($collection, $delete_access_key);
        $response['success'] = true;
        }

    exit(json_encode($response));
    }




$external_access_keys_query = 
"     SELECT access_key,
             resource,
             collection,
             group_concat(DISTINCT user ORDER BY user SEPARATOR ', ') AS users,
             group_concat(DISTINCT email ORDER BY email SEPARATOR ', ') AS emails,
             max(date) AS maxdate,
             max(lastused) AS lastused,
             access,
             expires,
             usergroup
        FROM external_access_keys
    GROUP BY access_key
    ORDER BY date
";
$external_shares = sql_query($external_access_keys_query);


// TODO in a few days: add a filter at the top of the page and pager with lazy load

include '../../include/header.php';
?>
<div class="BasicsBox">
    <p>
        <a href="<?php echo $baseurl; ?>/pages/team/team_home.php" onclick="return CentralSpaceLoad(this, true);">&lt;&nbsp;<?php echo $lang['backtoteamhome']; ?></a>
    </p>
    <h1><?php echo $lang['manage_external_shares']; ?></h1>

    <div class="Question">
        <div class="Listview">
            <table class="ListviewStyle" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr class="ListviewTitleStyle">
                        <td><?php echo $lang['accesskey']; ?></td>
                        <td><?php echo $lang['type']; ?></td>
                        <td><?php echo $lang['sharedby']; ?></td>
                        <td><?php echo $lang['sharedwith']; ?></td>
                        <td><?php echo $lang['lastupdated']; ?></td>
                        <td><?php echo $lang['lastused']; ?></td>
                        <td><?php echo $lang['expires']; ?></td>
                        <td><?php echo $lang['access']; ?></td>
                        <td><div class="ListTools"><?php echo $lang['tools']; ?></div></td>
                    </tr>
                    <?php
                    foreach($external_shares as $external_share)
                        {
                        render_access_key_tr($external_share);
                        }
                    ?>
                </tbody>
            </table>
        </div><!-- end of Listview -->
    </div><!-- end of Question -->

</div><!-- end of BasicBox -->
<script>
function delete_access_key(access_key, resource, collection)
    {
    var confirmationMessage = "<?php echo $lang['confirmdeleteaccessresource']; ?>";
    var post_data = {
        ajax: true,
        delete_access_key: access_key,
        resource: resource
    };

    if(collection != '')
        {
        confirmationMessage = "<?php echo $lang['confirmdeleteaccess']; ?>";

        delete post_data.resource;
        post_data.collection = collection;
        }

    if(confirm(confirmationMessage))
        {
        jQuery.post('<?php echo $baseurl; ?>/pages/team/team_external_shares.php', post_data, function(response) {
            if(response.success === true)
                {
                jQuery('#access_key_' + access_key).remove();
                }
        }, 'json');
        
        return false;
        }

    return true;
    }
</script>
<?php
include '../../include/footer.php';