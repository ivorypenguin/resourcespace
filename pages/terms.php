<?php
include "../include/db.php";
include "../include/general.php";
# External access support (authenticate only if no key provided)
# No need to check access key for this page as it merely redirects to other pages
$k=getvalescaped("k","");if ($k=="") {include "../include/authenticate.php";}

$url=getvalescaped("url","pages/home.php");

$newurl = hook("beforeredirectchangeurl");
if (is_string($newurl)) {$url = $newurl;}

if ($terms_download==false && getval("noredir","")=="") {redirect($url);}

if (getval("save","")!="")
	{
	if (strpos($url,"http")!==false)
		{
		header("Location: " . $url);
		exit();
		}
	else
		{
		redirect($url);
		}
	}
include "../include/header.php";
?>

<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["termsandconditions"]?></h1>
  <p><?php echo text("introtext")?></p>
  
 	<div class="Question">
	<label><?php echo $lang["termsandconditions"]?></label>
	<div class="Terms"><?php 
		$termstext=text("terms"); 
		if (is_html($termstext)){
			echo $termstext;
		} else {
			echo txt2html($termstext);
	}?></div>
	<div class="clearerleft"> </div>
	</div>
	
	<form method="post" action="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k); ?>">
	<input type=hidden name="url" value="<?php echo htmlspecialchars($url)?>">
	<div class="QuestionSubmit">
	<label for="buttons"> </label>		
	<input name="decline" type="button" value="&nbsp;&nbsp;<?php echo $lang["idecline"]?>&nbsp;&nbsp;" onClick="history.go(-1);return false;"/>

	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["iaccept"]?>&nbsp;&nbsp;" />
	</div>
	</form>
	
</div>

<?php
include "../include/footer.php";
?>
