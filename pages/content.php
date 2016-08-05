<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/header.php";

$content=getvalescaped("content","");
if ($content!=""){$content=text($content);}else{$content="This is default content text. You can create text (including html) in Team Centre->Manage Content and display it here.";}
?>

<div class="BasicsBox"> 
  <?php echo $content ?>
</div>

<?php
include "../include/footer.php";
?>