<?php
include "../include/db.php";
include_once "../include/general.php";
if (!hook("authenticate")){include "../include/authenticate.php";}

include "../include/header.php";
?>

<div class="BasicsBox">
  <h1><?php echo $lang["contactus"]?></h1>
  <p><?php echo text("contact")?></p>
</div>

<?php
include "../include/footer.php";
?>
