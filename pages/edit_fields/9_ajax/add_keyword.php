<?php
include dirname(__FILE__) . "/../../../include/db.php";
include_once dirname(__FILE__) . "/../../../include/general.php";
include dirname(__FILE__) . "/../../../include/authenticate.php";
include_once dirname(__FILE__) . "/../../../include/node_functions.php";

$field=getvalescaped("field","");
$keyword=getvalescaped("keyword","");

if(!checkperm('bdk' . $field))
    {
    # Append the option and update the field
    //sql_query("update resource_type_field set options=concat(ifnull(options,''), ', " . escape_check($keyword) . "') where ref='$field'");
    set_node(null,$field,$keyword,null,null);
    }

