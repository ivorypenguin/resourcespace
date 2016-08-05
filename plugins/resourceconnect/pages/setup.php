<?php
include "../../../include/db.php";
include "../../../include/general.php";
include "../../../include/authenticate.php";if (!checkperm("a")) {exit("Access denied");}
include "../../../include/search_functions.php";

include "../../../include/header.php";

# The access key is used to sign all inbound queries, the remote system must therefore know the access key.
$access_key=md5("resourceconnect" . $scramble_key);

?>
<h1 style="padding-bottom:10px;"><?php echo $lang["resourceconnect_plugin_heading"] ?></h1>

<p><?php echo $lang["resourceconnect_you_must_give_permission"] ?></p>

<p><?php echo str_replace("%key", $access_key, $lang["resourceconnect_access_key_for_installation"]) ?></p>


<?php
include "../../../include/footer.php";