<?php
include "../include/db.php";
include_once "../include/general.php";

$url=getval("url","");
$url=str_replace(" ","%20",$url);
$url=str_replace("\"","",$url); # Prevent HTML injection

include "../include/header.php";
?>

<div class="BasicsBox">
    <h1><?php echo $lang["downloadresource"]?></h1>
    <p><?php echo text("introtext")?></p>
    
    <p style="font-weight:bold;">&gt;&nbsp;<a href="<?php echo $url?>"><?php echo $lang["rightclicktodownload"]?></a></p>
    
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    
    <p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php">&lt;&nbsp;<?php echo $lang["backtosearch"]?></a></p>
    <p><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/home.php">&lt;&nbsp;<?php echo $lang["backtohome"]?></a></p>
</div>

<?php
include "../include/footer.php";
?>
