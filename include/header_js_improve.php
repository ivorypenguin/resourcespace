<?php 

$theme=((isset($userfixedtheme) && $userfixedtheme!=""))?$userfixedtheme:getval("colourcss",$defaulttheme);

hook ("preheaderoutput");
 
# Do not display header / footer when dynamically loading CentralSpace contents.
$ajax=getval("ajax","");

if ($ajax=="" && !hook("replace_header")) { 

if(!isset($thumbs) && ($pagename!="login") && ($pagename!="user_password") && ($pagename!="user_request"))
    {
    $thumbs=getval("thumbs","unset");
    if($thumbs == "unset")
        {
        $thumbs = $thumbs_default;
        rs_setcookie("thumbs", $thumbs, 1000,"","",false,false);
        }
    }
// blank starsearch cookie in case $star_search was turned off
setcookie("starsearch","",0,'','',false,true);
if ($display_user_rating_stars && $star_search){
	# if seardch is not a special search (ie. !recent), use starsearchvalue.
	if (getval("search","")!="" && strpos(getval("search",""),"!")!==false)
		{
		$starsearch="";
		}
	else
		{
		$starsearch=getvalescaped("starsearch","");	
		setcookie("starsearch",$starsearch,0,'','',false,true);
	    }
	}
	
?><!DOCTYPE html>
<html>	<?php if ($include_rs_header_info){?>
<!--<?php hook("copyrightinsert");?>
ResourceSpace version <?php echo $productversion?>

For copyright and license information see documentation/licenses/resourcespace.txt
http://www.resourcespace.org/
-->
<?php } ?>
<head>
<?php if(!hook("customhtmlheader")): ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

<?php hook("responsivemeta"); ?>

<title><?php echo htmlspecialchars($applicationname)?></title>
<link rel="icon" type="image/png" href="<?php echo $baseurl."/".$header_favicon?>" />

<!-- Load all required JS -->
<script src="<?php echo $baseurl?>/lib/js/include_js.php?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>

<link type="text/css" href="<?php echo $baseurl?>/css/ui-lightness/jquery-ui-1.8.20.custom.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" />

<!--[if lte IE 9]><script src="<?php echo $baseurl?>/lib/historyapi/history.min.js"></script><![endif]-->

<?php if ($slideshow_big) { ?>
<link type="text/css" href="<?php echo $baseurl?>/css/slideshow_big.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" />
<?php } ?>

<?php if ($load_ubuntu_font) { 
	$urlprefix="http://";
	if (strpos($baseurl,"https://")!==false) // Change prefix as mixed content prevents linking in Firefox
		{$urlprefix="https://";}
	echo "<link href='" . $urlprefix . "fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>";
	}

if ($pagename=="login") { ?>
<script type="text/javascript">
jQuery(document).ready(function() {

    /* 
    * Bind to capslockstate events and update display based on state 
    */
    jQuery(window).bind("capsOn", function(event) {
        if (jQuery("#password:focus").length > 0) {
            jQuery("#capswarning").show();
        }
    });
    jQuery(window).bind("capsOff capsUnknown", function(event) {
        jQuery("#capswarning").hide();
    });
    jQuery("#password").bind("focusout", function(event) {
        jQuery("#capswarning").hide();
    });
    jQuery("#password").bind("focusin", function(event) {
        if (jQuery(window).capslockstate("state") === true) {
            jQuery("#capswarning").show();
        }
    });

    /* 
    * Initialize the capslockstate plugin.
    * Monitoring is happening at the window level.
    */
    jQuery(window).capslockstate();

});
</script>
<?php } ?>
<!-- end of jQuery / jQueryUI load -->



<?php if ($plupload_widget){?>
	<link href="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.ui.plupload/css/jquery.ui.plupload.css?<?php echo $css_reload_key;?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />	
<?php } else { ?>
	<link href="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.plupload.queue/css/jquery.plupload.queue.css?<?php echo $css_reload_key;?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />
<?php } ?>



<script type="text/javascript">
var baseurl_short="<?php echo $baseurl_short?>";
var baseurl="<?php echo $baseurl?>";
var pagename="<?php echo $pagename?>";
var errorpageload = "<h1><?php echo $lang["error"] ?></h1><p><?php echo $lang["error-pageload"] ?></p>" ;
var applicationname = "<?php echo $applicationname?>";
var branch_limit="<?php echo $cat_tree_singlebranch?>";
var global_cookies = "<?php echo $global_cookies?>";
</script>



<?php if ($keyboard_navigation) { 
global $k;?>
<script type="text/javascript">

jQuery(document).ready(function() {
 jQuery.fn.reverse = [].reverse;
 jQuery(document).keyup(function (e)
  { 
    if(jQuery("input,textarea").is(":focus"))
    {
       // don't listen to keyboard arrows when focused on form elements
    }
    else
    { 
        var share='<?php echo $k ?>';
        var modAlt=e.altKey;
        var modShift=e.shiftKey;
        var modCtrl=e.ctrlKey;
        var modMeta=e.metaKey;
        var modOn=(modAlt || modShift || modCtrl || modMeta);
        
         switch (e.which) 
         {
			 
		    <?php hook ("addhotkeys"); //this comes first so overriding the below is possible ?>
            // left arrow
            case <?php echo $keyboard_navigation_prev; ?>: if ((jQuery('.prevLink').length > 0)<?php if ($pagename=="view") { ?>&&(jQuery("#fancybox-content").html()=='')<?php } ?>) jQuery('.prevLink').click();
              if (<?php if ($keyboard_navigation_pages_use_alt) echo "modAlt&&"; ?>(jQuery('.prevPageLink').length > 0)) jQuery('.prevPageLink').click();
              
                     <?php 
                     if (($pagename=="preview_all") && $keyboard_scroll_jump) { ?>
                     currentX=jQuery(window).scrollLeft();
                     jQuery('.ResourceShel_').reverse().each(function(index) {
                         offset = jQuery(this).offset();
                         if (offset.left-20<currentX) {
                            jQuery(window).scrollLeft(offset.left-20)
                            return false;
                         }
                     });                     
                     <?php } ?>
                     break;
            // right arrow
            case <?php echo $keyboard_navigation_next; ?>: if ((jQuery('.nextLink').length > 0)<?php if ($pagename=="view") { ?>&&(jQuery("#fancybox-content").html()=='')<?php } ?>) jQuery('.nextLink').click();
              if (<?php if ($keyboard_navigation_pages_use_alt) echo "modAlt&&"; ?>(jQuery('.nextPageLink').length > 0)) jQuery('.nextPageLink').click();
                     <?php 
                     if (($pagename=="preview_all") && $keyboard_scroll_jump) { ?>
                     currentX=jQuery(window).scrollLeft();
                     jQuery('.ResourceShel_').each(function(index) {
                         offset = jQuery(this).offset();
                         if (offset.left-40>currentX) {
                            jQuery(window).scrollLeft(offset.left-20)
                            return false;
                         }
                     });                     
                     <?php } ?>
                     break;   
            case <?php echo $keyboard_navigation_add_resource; ?>: if (jQuery('.addToCollection').length > 0) jQuery('.addToCollection').click();
                     break;
            case <?php echo $keyboard_navigation_remove_resource; ?>: if (jQuery('.removeFromCollection').length > 0) jQuery('.removeFromCollection').click();
                     break;  
            case <?php echo $keyboard_navigation_prev_page; ?>: if (jQuery('.pagePrev').length > 0) jQuery('.pagePrev').click();
                     break;
            case <?php echo $keyboard_navigation_next_page; ?>: if (jQuery('.pageNext').length > 0) jQuery('.pageNext').click();
                     break;
            case <?php echo $keyboard_navigation_all_results; ?>: if (jQuery('.upLink').length > 0) jQuery('.upLink').click();
                     break;
            case <?php echo $keyboard_navigation_toggle_thumbnails; ?>: if (jQuery('#toggleThumbsLink').length > 0) jQuery('#toggleThumbsLink').click();
                     break;
            case <?php echo $keyboard_navigation_zoom; ?>: if (jQuery('.enterLink').length > 0) window.location=jQuery('.enterLink').attr("href");
                     break;
            case <?php echo $keyboard_navigation_view_all; ?>: CentralSpaceLoad('<?php echo $baseurl;?>/pages/search.php?search=!collection'+document.getElementById("currentusercollection").innerHTML+'&k='+share,true);
                     break;
          
         }
         
     }
 });
});
</script>
<?php }

if (!$disable_geocoding)
    {
    ?>
    <script src="https://maps.google.com/maps/api/js?v=3.2&amp;sensor=false"></script>
    <?php
    }
?>
<?php hook("additionalheaderjs");?>

<?php
echo $headerinsert;
$extrafooterhtml="";
?>

<link href="<?php echo $baseurl?>/css/global.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" />
<?php if (!hook("adjustcolortheme")){ ?>
<link href="<?php echo $baseurl?>/css/Col-<?php echo (isset($userfixedtheme) && $userfixedtheme!="")?$userfixedtheme:getval("colourcss",$defaulttheme)?>.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" id="colourcss" />
<?php } ?>
<?php if ($pagename!="preview_all"){?><!--[if lte IE 7]> <link href="<?php echo $baseurl?>/css/globalIE.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css"  media="screen,projection,print" /> <![endif]--><?php } ?>
<!--[if lte IE 5.6]> <link href="<?php echo $baseurl?>/css/globalIE5.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css"  media="screen,projection,print" /> <![endif]-->

<?php 
#SlimHeader global structure changes
if($slimheader)
    {
    ?>
    <link href="<?php echo $baseurl?>/css/globalslimheader.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" />
    <?php
    }
    
echo get_plugin_css($theme)
// after loading these tags we change the class on them so a new set can be added before they are removed (preventing flickering of overridden theme)
?>
<script>jQuery('.plugincss').attr('class','plugincss0');</script>
<?php

if(isset($usergroup))
    {
    //Get group logo value
    $curr_group = get_usergroup($usergroup);
    if (!empty($curr_group["group_specific_logo"]))
        {
        $linkedheaderimgsrc = isset($storageurl)? $storageurl : $baseurl."/filestore";
        $linkedheaderimgsrc.= "/admin/groupheaderimg/group".$usergroup.".".$curr_group["group_specific_logo"];
        if(!$slimheader)
            {
            ?>
            <style>#Header{background-image: url(<?php echo $linkedheaderimgsrc; ?>);}</style>
            <?php
            }
        }
    }

hook("headblock");
 
endif; # !hook("customhtmlheader") ?>
</head>

<body lang="<?php echo $language ?>" <?php if (isset($bodyattribs)) { ?><?php echo $bodyattribs?><?php } if($infobox) {?> onmousemove="InfoBoxMM(event);"<?php } ?>>

<?php hook("bodystart"); ?>

<?php
# Commented as it was causing IE to 'jump'
# <body onLoad="if (document.getElementById('searchbox')) {document.getElementById('searchbox').focus();}">
?>

<!--Global Header-->
<div id="UICenter" class="ui-layout-center">
<?php
if (($pagename=="terms") && (getval("url","")=="index.php")) {$loginterms=true;} else {$loginterms=false;}
if ($pagename!="preview" && $pagename!="preview_all") { ?>

<?php
$homepage_url=$baseurl."/pages/".$default_home_page;
if ($use_theme_as_home){$homepage_url=$baseurl."/pages/themes.php";}
if ($use_recent_as_home){$homepage_url=$baseurl."/pages/search.php?search=".urlencode('!last'.$recent_search_quantity);}
if ($pagename=="login" || $pagename=="user_request" || $pagename=="user_password"){$homepage_url=$baseurl."/index.php";}


hook("beforeheader");

if(!$slimheader)
    {
    ?>
    <div id="Header" <?php if ($header_text_title){?>style="background-image:none;"<?php } ?>>
    <?php hook("responsiveheader");

    if ($header_link && !$header_text_title && getval("k","")=="") 
        {
	   if(isset($header_link_height) || isset($header_link_width)){
		   # compile style attribute for headerlink
		   $headerlink_style='';
		   if(isset($header_link_height)){$headerlink_style.="height:".$header_link_height."px;";}
		   if(isset($header_link_width)){$headerlink_style.="width:".$header_link_width."px;";}
	   }
       $linkUrl=isset($header_link_url) ? $header_link_url : $homepage_url;
       $onclick = (substr($linkUrl, 0, strlen($baseurl)) === $baseurl || substr($linkUrl, 0, strlen($baseurl_short)) === $baseurl_short) ? "" : ' onclick="return CentralSpaceLoad(this,true);"';
        ?><a class="headerlink" <?php if(isset($headerlink_style)){?> style="<?php echo $headerlink_style?>" <?php } ?> href="<?php echo $linkUrl ?>"<?php echo $onclick?>></a><?php
        }
    if ($header_text_title)
        {?>
        <div id="TextHeader"><?php if (getval("k","")==""){?><a href="<?php echo $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo $applicationname;?><?php if (getval("k","")==""){?></a><?php } ?></div>
        <?php if ($applicationdesc!="")
            {?>
            <div id="TextDesc"><?php echo i18n_get_translated($applicationdesc);?></div>
            <?php 
            }
        }
    }
else
    {
    $currenttheme = (isset($userfixedtheme)&&$userfixedtheme!='') ? $userfixedtheme : $defaulttheme;
    $colourcss = getval('colourcss',''); 
    $currenttheme = $colourcss!='' ? $colourcss : $currenttheme;
    ?>
    <div id="Header" <?php echo ($currenttheme=="whitegry"||$currenttheme==="multi") ? "class='slimheader_darken'":"";?>>
    <?php hook("responsiveheader");
    if($header_text_title) 
        {?>
        <div id="TextHeader"><?php if (getval("k","")==""){?><a href="<?php echo $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo $applicationname;?><?php if (getval("k","")==""){?></a><?php } ?></div>
        <?php if ($applicationdesc!="")
            {?>
            <div id="TextDesc"><?php echo i18n_get_translated($applicationdesc);?></div>
            <?php 
            }
        }
    else
        {
        $linkUrl=isset($header_link_url) ? $header_link_url : $homepage_url;
        if($linkedheaderimgsrc !="") 
            {
            $header_img_src = $linkedheaderimgsrc;
            }
        else 
            {
            $header_img_src = $baseurl.'/gfx/titles/title.png';
            }
        ?>
        <a href="<?php echo $linkUrl; ?>" onClick="return CentralSpaceLoad(this,true);" class="HeaderImgLink"><img src="<?php echo $header_img_src; ?>" id="HeaderImg"></img></a>
        <?php
        }
    }

hook("headertop");

if (!isset($allow_password_change)) {$allow_password_change=true;}

if (isset($username) && ($pagename!="login") && ($loginterms==false) && getval("k","")=="") { ?>
<div id="HeaderNav1" class="HorizontalNav ">

<?php
hook("beforeheadernav1");
if (isset($anonymous_login) && ($username==$anonymous_login))
	{
	if (!hook("replaceheadernav1anon")) {
	?>
	<ul>
	<li><a href="<?php echo $baseurl?>/login.php"><?php echo $lang["login"]?></a></li>
	<?php if ($contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
	</ul>
	<?php
	} /* end replaceheadernav1anon */
	}
else
	{
	if (!hook("replaceheadernav1")) {
	?>
	<ul>
	<?php if (!hook("replaceheaderfullnamelink")){?>
	<li><?php if ($allow_password_change && !checkperm("p")) { ?>
	<a href="<?php echo $baseurl?>/pages/user_preferences.php"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo htmlspecialchars(($userfullname=="" ? $username : $userfullname)) ?><?php } /* end replacefullnamelink */?><?php if ($allow_password_change && !checkperm("p")) { ?></a><?php } ?></li>
	<?php hook("addtoplinks");?>
	<li><a href="<?php echo $baseurl?>/login.php?logout=true&amp;nc=<?php echo time()?>"><?php echo $lang["logout"]?></a></li>
	<?php hook("addtologintoolbarmiddle");?>
	<?php if ($contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
	</ul>
	<?php
	} /* end replaceheadernav1 */
	}
hook("afterheadernav1");
?>
</div>
<?php hook("midheader"); ?>
<div id="HeaderNav2" class="HorizontalNav HorizontalWhiteNav">
<?php
include (dirname(__FILE__) . "/header_links.php");
?>
</div> 

<?php } else if (!hook("replaceloginheader")) { # Empty Header?>
<div id="HeaderNav1" class="HorizontalNav ">&nbsp;</div>
<div id="HeaderNav2" class="HorizontalNav HorizontalWhiteNav">&nbsp;</div>
<?php } ?>

<?php } ?>

<?php hook("headerbottom"); ?>

<div class="clearer"></div><?php if ($pagename!="preview" && $pagename!="preview_all") { ?></div><?php } #end of header ?>

<?php
# Include simple search sidebar?
$omit_searchbar_pages=array("index","preview_all","search_advanced","preview","admin_header","login");
$modified_omit_searchbar_pages=hook("modifyomitsearchbarpages");
if ($modified_omit_searchbar_pages){$omit_searchbar_pages=$modified_omit_searchbar_pages;}
if (!in_array($pagename,$omit_searchbar_pages) && ($loginterms==false) && getvalescaped('k', '') == '') 	
	{
	?>
    <div id="SearchBarContainer">
    <?php
	include dirname(__FILE__)."/searchbar.php";
	
	?>
    </div>
    <?php
    }	
    ?>

<?php
# Determine which content holder div to use
if (($pagename=="login") || ($pagename=="user_password") || ($pagename=="user_request")) {$div="CentralSpaceLogin";}
else {$div="CentralSpace";}
?>
<!--Main Part of the page-->
<?php if (($pagename!="login") && ($pagename!="user_password") && ($pagename!="user_request")) { ?><div id="CentralSpaceContainer"><?php } ?>

<!-- Loading graphic -->
<div id="LoadingBox"><?php echo $lang["pleasewait"] ?><img src="<?php echo $baseurl_short ?>gfx/interface/loading.gif"></div>

<div id="<?php echo $div?>">


<?php

hook("afterheader");

} // end if !ajax


	

if (getval("k","")!="") { ?>
<style>
#CentralSpaceContainer  {padding-right:0;margin: 0px 10px 20px 25px;}
</style>
<?php }
// Ajax specific hook
if ($ajax) {hook("afterheaderajax");}
?>
