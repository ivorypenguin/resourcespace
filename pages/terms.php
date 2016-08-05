<?php
include "../include/db.php";
include_once "../include/general.php";
# External access support (authenticate only if no key provided)
# No need to check access key for this page as it merely redirects to other pages
$k=getvalescaped("k","");if ($k=="") {include "../include/authenticate.php";}

$url=getvalescaped("url","pages/home.php?login=true");

$newurl = hook("beforeredirectchangeurl");
if(is_string($newurl))
    {
    $url = $newurl;
    }

if('' != getval('save', ''))
    {
    if('on' == getvalescaped('iaccept', ''))
        {
        sql_query("UPDATE user SET accepted_terms = 1 WHERE ref = '{$userref}'");
        }

    if(false !== strpos($url, 'http'))
        {
        header("Location: {$url}");
        exit();
        }
    else
        {
        redirect($url);
        }
    }

if($terms_download == false && getval("noredir","") == "")
    {
    redirect($url);
    }

include "../include/header.php";
?>
<div class="BasicsBox"> 
  <h1><?php echo $lang["termsandconditions"]?></h1>
  <p><?php echo text("introtext")?></p>
  
 	<div class="Question">
	<label><?php echo $lang["termsandconditions"]?></label>
	<div class="Terms"><?php 
		$termstext=text("terms");
		$modified_termstext=hook('modified_termstext');
		if($modified_termstext!=''){$termstext=$modified_termstext;}
		if (is_html($termstext)){
			echo $termstext;
		} else {
			echo txt2html($termstext);
	}?></div>
	<div class="clearerleft"> </div>
	</div>
	
	<form method="post" action="<?php echo $baseurl_short?>pages/terms.php?k=<?php echo urlencode($k); ?>" onSubmit="if (!document.getElementById('iaccept').checked) {alert('<?php echo $lang["mustaccept"] ?>');return false;}">
	<input type=hidden name="url" value="<?php echo htmlspecialchars($url)?>">
	
	<div class="Question">
	<label for="iaccept"><?php echo $lang["iaccept"] ?></label>
	<input type="checkbox" name="iaccept" id="iaccept" />
	<div class="clearerleft"> </div>
	</div>
	
	<div class="QuestionSubmit">
	<label></label>
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["proceed"]?>&nbsp;&nbsp;" />
	</div>
	</form>
	
</div>

<?php
include "../include/footer.php";
?>
