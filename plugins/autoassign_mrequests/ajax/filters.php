<?php
include dirname(__FILE__) . '/../../../include/db.php';
include dirname(__FILE__) . '/../../../include/authenticate.php';
include_once dirname(__FILE__) . '/../../../include/general.php';

$user_group_id = getvalescaped('user_group_id', '');

$filtered_users = get_users($user_group_id);

echo json_encode($filtered_users);
exit();
?>