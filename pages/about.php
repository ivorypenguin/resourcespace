<?php
include "../include/db.php";
include_once "../include/general.php";

if (!hook("authenticate")){include "../include/authenticate.php";}

include "../include/header.php";
?>

<div class="BasicsBox">
  <h1><?php echo $lang["aboutus"]?></h1>
  <p><?php echo text("about")?></p>
</div>

<?php
include "../include/footer.php";
?>