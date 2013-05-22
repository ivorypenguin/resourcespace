<?php 

$theme=((isset($userfixedtheme) && $userfixedtheme!=""))?$userfixedtheme:getval("colourcss",$defaulttheme);

hook ("preheaderoutput");
 
# Do not display header / footer when dynamically loading CentralSpace contents.
if (getval("ajax","")=="") { 

// blank starsearch cookie in case $star_search was turned off
setcookie("starsearch","");

// cookies have to go above the header output
if ($display_user_rating_stars && $star_search){
	# if seardch is not a special search (ie. !recent), use starsearchvalue.
	if (getval("search","")!="" && strpos(getval("search",""),"!")!==false)
		{
		$starsearch="";
		}
	else
		{
		$starsearch=getvalescaped("starsearch","");	
		setcookie("starsearch",$starsearch);
	    }
	}
?><!DOCTYPE html>
<html>	<?php if ($include_rs_header_info){?>
<!--<?php hook("copyrightinsert");?>
ResourceSpace version <?php echo $productversion?>

Copyright Oxfam GB, Montala, WWF International, Tom Gleason, David Dwiggins, Historic New England, Colorhythm LLC, Worldcolor, Henrik Frizén 2006-2013
http://www.resourcespace.org/
-->
<?php } ?>
<head>
<?php if(!hook("customhtmlheader")): ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<title><?php echo htmlspecialchars($applicationname)?></title>
<link rel="icon" type="image/png" href="<?php echo $baseurl."/".$header_favicon?>" />

<!-- Load jQuery and jQueryUI -->
<script src="<?php echo $baseurl?>/lib/js/jquery-1.7.2.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>

<script src="<?php echo $baseurl?>/lib/js/jquery-ui-1.8.20.custom.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<script src="<?php echo $baseurl?>/lib/js/jquery.layout.min.js"></script>
<script src="<?php echo $baseurl?>/lib/js/easyTooltip.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<link type="text/css" href="<?php echo $baseurl?>/css/ui-lightness/jquery-ui-1.8.20.custom.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" />
<script src="<?php echo $baseurl?>/lib/js/jquery.ui.touch-punch.min.js"></script>
<?php if ($image_preview_zoom) { ?><script src="<?php echo $baseurl?>/lib/js/jquery.zoom.js"></script><?php } ?>

<?php if ($use_zip_extension){?><script type="text/javascript" src="<?php echo $baseurl?>/lib/js/jquery-periodical-updater.js"></script><?php } ?>
<?php if ($contact_sheet){?>
<script type="text/javascript" src="<?php echo $baseurl?>/lib/js/contactsheet.js"></script>
<script>
contactsheet_previewimage_prefix = '<?php echo addslashes($storageurl)?>';
</script>
<?php if ($pagename=="login") { ?><script type="text/javascript" src="<?php echo $baseurl?>/lib/js/jquery.capslockstate.js"></script><?php } ?>

<script type="text/javascript">
jQuery.noConflict();
</script>

<?php } ?>
<?php if ($pagename=="login") { ?>
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

<script type="text/javascript">
<?php if ($ajax_url_rewrites){ ?>
	rewriteUrls=true;
	<?php if ($ajax_url_rewrite_debug){?>rewriteUrlsDebug=true;<?php } ?>
<?php } else { ?>
	rewriteUrls=false;rewriteUrlsDebug=false;
<?php } ?>
	ajaxLoadingTimer=<?php echo $ajax_loading_timer;?>;
</script>

<script src="<?php echo $baseurl?>/lib/js/category_tree.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $baseurl?>/lib/ckeditor/ckeditor.js"></script>
<?php if (!$disable_geocoding) { ?>
<script src="<?php echo $baseurl ?>/lib/OpenLayers/OpenLayers.js"></script>
<script src="https://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
<?php } ?>

<script src="<?php echo $baseurl;?>/lib/js/ajax_collections.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>

<link href="<?php echo $baseurl_short;?>lib/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css?<?php echo $css_reload_key;?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />
<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/js/browserplus-min.js?<?php echo $css_reload_key;?>"></script>
<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/plupload/plupload.full.js?<?php echo $css_reload_key;?>"></script>
<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/plupload/jquery.plupload.queue/jquery.plupload.queue.js?<?php echo $css_reload_key;?>"></script>

<script type="text/javascript">
var baseurl_short="<?php echo $baseurl_short?>";
var baseurl="<?php echo $baseurl?>";
var errorpageload = "<h1><?php echo $lang["error"] ?></h1><p><?php echo $lang["error-pageload"] ?></p>" ;
var applicationname = "<?php echo $applicationname?>";
</script>


<script src="<?php echo $baseurl_short?>lib/js/global.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<?php if ($keyboard_navigation) { ?>
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
        
         switch (e.which) 
         {
			 
		    <?php hook ("addhotkeys"); //this comes first so overriding the below is possible ?>
            // left arrow
            case <?php echo $keyboard_navigation_prev; ?>: if (jQuery('.prevLink').length > 0) jQuery('.prevLink').click();
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
            case <?php echo $keyboard_navigation_next; ?>: if (jQuery('.nextLink').length > 0) jQuery('.nextLink').click();
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
            case <?php echo $keyboard_navigation_toggle_previews; ?>: if (jQuery('.hideLink').length > 0) jQuery('.hideLink').click();
                     break;
            case <?php echo $keyboard_navigation_zoom; ?>: if (jQuery('.enterLink').length > 0) window.location=jQuery('.enterLink').attr("href");
                     break;
          
         }
         
     }
 });
});
</script>
<?php } ?>
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
echo get_plugin_css($theme)
// after loading these tags we change the class on them so a new set can be added before they are removed (preventing flickering of overridden theme)
?>
<script>jQuery('.plugincss').attr('class','plugincss0');</script>

<?php hook("headblock"); ?>

<?php 
if ($collections_compact_style && $pagename!="login"){ include dirname(__FILE__)."/../lib/js/colactions.js";}

# Infobox JS include
if ($infobox)
	{
?>
	<script type="text/javascript">
	var InfoBoxImageMode=<?php echo ($infobox_image_mode?"true":"false")?>;
	</script>
	<script src="<?php echo $baseurl_short;?>lib/js/infobox.js?css_reload_key=<?php echo $css_reload_key ?>" type="text/javascript"></script>
<?php
	}
?>
<?php endif; # !hook("customhtmlheader") ?>
</head>

<body <?php if (isset($bodyattribs)) { ?><?php echo $bodyattribs?><?php } if($infobox) {?> onmousemove="InfoBoxMM(event);"<?php } ?>>

<?php hook("bodystart"); ?>

<?php
# Commented as it was causing IE to 'jump'
# <body onLoad="if (document.getElementById('searchbox')) {document.getElementById('searchbox').focus();}">
?>

<!--Global Header-->
<div id="UICenter" class="ui-layout-center" style="height:100%">
<?php
if (($pagename=="terms") && (getval("url","")=="index.php")) {$loginterms=true;} else {$loginterms=false;}
if ($pagename!="preview" && $pagename!="preview_all") { ?>

<?php
$homepage_url=$baseurl."/pages/".$default_home_page;
if ($use_theme_as_home){$homepage_url=$baseurl."/pages/themes.php";}
if ($use_recent_as_home){$homepage_url=$baseurl."/pages/search.php?search=".urlencode('!last'.$recent_search_quantity);}
if ($pagename=="login" || $pagename=="user_request" || $pagename=="user_password"){$homepage_url=$baseurl."/index.php";}
?>

<div id="Header" <?php if ($header_text_title){?>style="background:none;"<?php } ?>>
<?php if ($header_link && !$header_text_title && getval("k","")==""){?><a class="headerlink" href="<?php echo isset($header_link_url) ? $header_link_url : $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"></a><?php } ?>
<?php if ($header_text_title){?>
    <div id="TextHeader"><?php if (getval("k","")==""){?><a href="<?php echo $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo $applicationname;?><?php if (getval("k","")==""){?></a><?php } ?></div>
    <?php if ($applicationdesc!=""){?>
        <div id="TextDesc"><?php echo i18n_get_translated($applicationdesc);?></div>
    <?php } ?>
<?php }


hook("headertop");

if (!isset($allow_password_change)) {$allow_password_change=true;}

if (isset($username) && ($pagename!="login") && ($loginterms==false) && getval("k","")=="") { ?>
<div id="HeaderNav1" class="HorizontalNav ">

<?php
hook("beforeheadernav1");
if (isset($anonymous_login) && ($username==$anonymous_login))
	{
	?>
	<ul>
	<li><a href="<?php echo $baseurl?>/login.php"><?php echo $lang["login"]?></a></li>
	<?php if ($contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
	</ul>
	<?php
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
	<li><a href="<?php echo $baseurl?>/login.php?logout=true&nc=<?php echo time()?>"><?php echo $lang["logout"]?></a></li>
	<?php hook("addtologintoolbarmiddle");?>
	<?php if ($contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
	</ul>
	<?php
	} /* end replaceheadernav1 */
	}
?>
</div>

<div id="HeaderNav2" class="HorizontalNav HorizontalWhiteNav">

<?php if ($breadcrumbs) { ?>
<div class="Breadcrumbs"><?php echo get_breadcrumbs()?></div>
<?php } ?>
		<?php if (!hook("replaceheadernav2")) { ?>
		<ul>
		<?php if (!$use_theme_as_home && !$use_recent_as_home) { ?><li><a href="<?php echo $baseurl?>/pages/<?php echo $default_home_page?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["home"]?></a></li><?php }  
		hook("topnavlinksafterhome");
		?>
		<?php if ($advanced_search_nav) { ?><li><a href="<?php echo $baseurl?>/pages/search_advanced.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["advancedsearch"]?></a></li><?php }  ?>
		<?php if 	(
			(checkperm("s"))  && (! $disable_searchresults )
		&&
			(
				(isset($_COOKIE["search"]) && strlen($_COOKIE["search"])>0)
			||
				(isset($search) && (strlen($search)>0) && (strpos($search,"!")===false))
			)
		)
		{?>
		<?php if ($search_results_link){?><li><a href="<?php echo $baseurl?>/pages/search.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["searchresults"]?></a></li><?php } ?><?php } ?>
		<?php if (checkperm("s") && $enable_themes && !$theme_direct_jump) { ?><li><a href="<?php echo $baseurl?>/pages/themes.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["themes"]?></a></li><?php } ?>
		<?php if (checkperm("s") && ($public_collections_top_nav || $public_collections_header_only)) { ?><li><a href="<?php echo $baseurl?>/pages/collection_public.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["publiccollections"]?></a></li><?php } ?>
		<?php if (checkperm("s") && $mycollections_link && !checkperm("b")) { ?><li><a href="<?php echo $baseurl?>/pages/collection_manage.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycollections"]?></a></li><?php } ?>
		<?php if (!hook("replacerecentlink")) { ?>
		<?php if (checkperm("s") && $recent_link) { ?><li><a href="<?php echo $baseurl?>/pages/search.php?search=<?php echo urlencode("!last".$recent_search_quantity)?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["recent"]?></a></li><?php } ?>
		<?php } /* end hook replacerecentlink */?>
		<?php if (checkperm("s") && $myrequests_link && checkperm("q")) { ?><li><a href="<?php echo $baseurl?>/pages/requests.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["myrequests"]?></a></li><?php } ?>
		<?php if (!hook("replacemycontributionslink")) { ?>
		<?php if (checkperm("d")||(isset($mycontributions_link) && $mycontributions_link && checkperm("c"))) { ?><li><a href="<?php echo $baseurl?>/pages/contribute.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycontributions"]?></a></li><?php } ?>
		<?php } /* end hook replacemycontributionslink */?>
		<?php if (!hook("replaceresearchrequestlink")) { ?>
		<?php if (($research_request) && (checkperm("s")) && (checkperm("q"))) { ?><li><a href="<?php echo $baseurl?>/pages/research_request.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["researchrequest"]?></a></li><?php } ?>
		<?php } ?>
		<?php if ($speedtagging && checkperm("s") && checkperm("n")) { ?><li><a href="<?php echo $baseurl?>/pages/tag.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["tagging"]?></a></li><?php } ?>
		
		<?php 
		/* ------------ Customisable top navigation ------------------- */
		if (isset($custom_top_nav))
			{
			for ($n=0;$n<count($custom_top_nav);$n++)
				{
				
				if (preg_match("/^https?\:\/\/.+/",$custom_top_nav[$n]['link'])){
					$isextlink = true;
				} else {
					$isextlink = false;
				}
				if(strpos($custom_top_nav[$n]["title"],"(lang)")!==false){
					$custom_top_nav_title=str_replace("(lang)","",$custom_top_nav[$n]["title"]);
					$custom_top_nav[$n]["title"]=$lang[$custom_top_nav_title];
				}
				?>
				<li><a href="<?php echo $custom_top_nav[$n]["link"] ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo i18n_get_translated($custom_top_nav[$n]["title"]) ?></a></li>
				<?php
				}
			}
		?>
		
		
		<?php if ($help_link){?><li><a href="<?php echo $baseurl?>/pages/help.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["helpandadvice"]?></a></li><?php } ?>
		<?php if (($top_nav_upload && checkperm("c")) || ($top_nav_upload_user && checkperm("d"))) { ?><li><a href="<?php echo $baseurl?>/pages/edit.php?ref=-<?php echo @$userref?>&amp;uploader=<?php echo $top_nav_upload_type ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["upload"]?></a></li><?php } ?>
		<?php if (checkperm("t")) { ?><li><a href="<?php echo $baseurl?>/pages/team/team_home.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["teamcentre"]?></a>
		<?php if ($team_centre_alert_icon && (checkperm("R")||checkperm("r")) &&  (sql_value("select sum(thecount) value from (select count(*) thecount from request where status = 0 union select count(*) thecount from research_request where status = 0) as theunion",0) > 0)){
			echo "<img src='$baseurl/gfx/images/attention_16.png' width='16' height='16' style='position:relative;top:3px;' />";	
		} ?>
		</li><?php } ?>

<?php hook("toptoolbaradder"); ?>
		</ul>
<?php } /* end replaceheadernav1 */ ?>
		
</div>

<?php }  else { # Empty Header?>
<div id="HeaderNav1" class="HorizontalNav ">&nbsp;</div>
<div id="HeaderNav2" class="HorizontalNav HorizontalWhiteNav">&nbsp;</div>
<?php } ?>

<?php } ?>

<?php hook("headerbottom"); ?>

<div class="clearer"></div><?php if ($pagename!="preview" && $pagename!="preview_all") { ?></div><?php } ?>
<?php
# Include simple search sidebar?
$omit_searchbar_pages=array("index","preview_all","search_advanced","preview","admin_header");
$modified_omit_searchbar_pages=hook("modifyomitsearchbarpages");
if ($modified_omit_searchbar_pages){$omit_searchbar_pages=$modified_omit_searchbar_pages;}

if (!in_array($pagename,$omit_searchbar_pages) && ($loginterms==false)) 	
	{
	include "searchbar.php";
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
# Include theme bar?
if ($use_theme_bar && (getval("k","")=="") && !in_array($pagename,array("themes","preview_all","done","search_advanced","login","preview","admin_header","user_password","user_request")) && ($loginterms==false))
	{
	# Tables seem to be the only solution to having a left AND right side bar, due to the way the clear CSS attribute works.
	?>
	<table width="100%" style="margin:0;padding:0;"><tr><td width="185" valign="top" align="left" style="margin:0;padding:0;">
	<?php
	include "themebar.php";
	?>
	</td><td valign="top" style="margin:0;padding:0;">
	<?php
	}
	
hook("afterheader");

} // end if !ajax

?>
