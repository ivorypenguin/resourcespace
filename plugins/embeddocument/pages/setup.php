<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("u")) {exit ("Permission denied.");}


$plugin_name = 'embeddocument';
$page_heading = $lang['embeddocument_heading'];

$page_def[]= config_add_single_rtype_select("embeddocument_resourcetype",$lang["embeddocument_specify_resourcetype"]);

// Do the page generation ritual
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading);
include '../../../include/footer.php';