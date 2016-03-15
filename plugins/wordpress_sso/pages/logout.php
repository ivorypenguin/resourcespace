<?php
include "../../../include/db.php";
include_once "../../../include/general.php";

global $baseurl, $lang, $username, $wordpress_sso_url, $wordpress_sso_secret, $global_cookies;

 #blank cookies
sql_query("update user set logged_in=0,session='' where username='$username'");
	
setcookie("wordpress_sso","",0,"/");
setcookie("user","",0,"/");

include "../../../include/header.php";

?>
<div class=BasicsBox">
<h2><?php echo $lang["wordpress_sso_loggedout"] ?></h2>
<br>
<p>
<a class="" id="link" href="<?php echo $wordpress_sso_url ?>/wp-login.php?action=logout">&gt;&nbsp;<?php echo $lang["logout"] ;?></a>
</p>
</div>
<script type="text/javascript">
window.setTimeout(function() {
location.href = "<?php echo $wordpress_sso_url ?>/wp-login.php?action=logout";
}, 4000);	
</script>
<?php
	

include "../../../include/footer.php";
        

