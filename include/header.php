<?php 
global $ctheme,$userfixedtheme,$defaulttheme;

$lastresorttheme = "";
if(isset($userfixedtheme) && $userfixedtheme!="")
    {
    if(!in_array("col-".$ctheme,$plugins))
        {
        $lastresorttheme = $userfixedtheme;
        }
    else
        {
        $lastresorttheme="";
        }
    }
else if($ctheme!="" && sql_value("SELECT name as value FROM plugins WHERE inst_version>=0 and name='col-".$ctheme."'",'')!="")
    {
    if(!in_array("col-".$ctheme,$plugins))
        {
        $lastresorttheme = $ctheme;
        }
    else
        {
        $lastresorttheme="";
        }
    }
// check for a cookie...useful on the login page where no authentication happens
else if(array_key_exists('colour_theme',$_COOKIE))
    {
	if(!in_array("col-".$_COOKIE['colour_theme'],$plugins))
        {
		$lastresorttheme = $_COOKIE['colour_theme'];
		}
	else
        {
        $lastresorttheme="";
        }
    }
else if(!isset($defaulttheme) || $defaulttheme=="")
    {
    #If only one plugin enabled
    $cplugins = sql_query("SELECT name FROM plugins WHERE inst_version>=0 AND name like'"."col-%' ORDER BY priority");
    if(count($cplugins)==1 && isset($cplugins[0]["name"]))
        {
        $lastresorttheme = preg_replace("/^col-/","",$cplugins[0]["name"]);
        }
    else
        {
        $lastresorttheme = "";
        }
    }
else if(isset($defaulttheme) && $defaulttheme!="")
    {
    $lastresorttheme = $defaulttheme;
    }
else
    {
    $lastresorttheme = "";
    }

# Process if required the colour theme.
if($lastresorttheme)
    {
    global $plugins;
    $lastresorttheme = preg_replace("/^col-/","", $lastresorttheme);
    switch($lastresorttheme)
            {
            case "blue":
            case "greyblu": $lastresorttheme = "blue"; break;
            case "charcoal":
            case "slimcharcoal": $lastresorttheme = "charcoal"; break;
            }
    if(!in_array("col-".$lastresorttheme,$plugins))
        {
        if(!function_exists("activate_plugin"))
            {include dirname(__FILE__). "/plugin_functions.php";}
        activate_plugin("col-".$lastresorttheme);
        $cplugin = (sql_query("SELECT name,enabled_groups, config, config_json FROM plugins WHERE inst_version>=0 AND name='"."col-".escape_check($lastresorttheme)."' ORDER BY priority"));
        if(isset($cplugin[0]))
            {
            $cplugin=$cplugin[0];
            include_plugin_config($cplugin['name'],$cplugin['config'],$cplugin['config_json']);
            register_plugin($cplugin['name']);
            register_plugin_language($cplugin['name']);
            $plugins[]=$cplugin['name'];
            $ctheme = "col-".$lastresorttheme;
            }
        }
    }
hook ("preheaderoutput");
 
$k=getvalescaped("k","");
if(!isset($internal_share_access))
	{
	// Set a flag for logged in users if $external_share_view_as_internal is set and logged on user is accessing an external share
	$internal_share_access = ($k!="" && $external_share_view_as_internal && isset($is_authenticated) && $is_authenticated);
	}


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
rs_setcookie('starsearch', '');
if ($display_user_rating_stars && $star_search)
    {
	# if seardch is not a special search (ie. !recent), use starsearchvalue.
	if (getval("search","")!="" && strpos(getval("search",""),"!")!==false)
		{
		$starsearch="";
		}
	else
		{
		$starsearch=getvalescaped("starsearch","");	
		rs_setcookie('starsearch', $starsearch);
	    }
	}
	
?><!DOCTYPE html>
<html>	
<?php 
if ($include_rs_header_info)
    {?>
    <!--<?php hook("copyrightinsert");?>
    ResourceSpace version <?php echo $productversion?>

    For copyright and license information see documentation/licenses/resourcespace.txt
    http://www.resourcespace.org/
    -->
    <?php 
    }
?>
<head>
<?php if(!hook("customhtmlheader")): ?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

<?php hook("responsivemeta"); ?>

<title><?php echo htmlspecialchars($applicationname)?></title>
<link rel="icon" type="image/png" href="<?php echo $baseurl."/".$header_favicon?>" />

<!-- Load jQuery and jQueryUI -->
<script src="<?php echo $baseurl?>/lib/js/jquery-1.7.2.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>

<script src="<?php echo $baseurl?>/lib/js/jquery-ui-1.10.2.custom.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<script src="<?php echo $baseurl?>/lib/js/jquery.layout.min.js"></script>
<script src="<?php echo $baseurl?>/lib/js/easyTooltip.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<link type="text/css" href="<?php echo $baseurl?>/css/ui-lightness/jquery-ui-1.8.20.custom.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" />
<script src="<?php echo $baseurl?>/lib/js/jquery.ui.touch-punch.min.js"></script>
<?php if ($pagename=="login") { ?><script type="text/javascript" src="<?php echo $baseurl?>/lib/js/jquery.capslockstate.js"></script><?php } ?>
<!--[if lte IE 9]><script src="<?php echo $baseurl?>/lib/historyapi/history.min.js"></script><![endif]-->
<?php if ($image_preview_zoom) { ?><script src="<?php echo $baseurl?>/lib/js/jquery.zoom.js"></script><?php } ?>

<script type="text/javascript" src="<?php echo $baseurl?>/lib/js/jquery-periodical-updater.js"></script>

<?php 
if ($slideshow_big) 
    { ?>
    <script type="text/javascript" src="<?php echo $baseurl?>/lib/js/slideshow_big.js"></script>
    <link type="text/css" href="<?php echo $baseurl?>/css/slideshow_big.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" />
    <?php 
    }
if ($load_ubuntu_font) 
    { 
	$urlprefix="http://";
	if (strpos($baseurl,"https://")!==false) // Change prefix as mixed content prevents linking in Firefox
		{$urlprefix="https://";}
	echo "<link href='" . $urlprefix . "fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>";
	}

if ($contact_sheet)
    {?>
    <script type="text/javascript" src="<?php echo $baseurl?>/lib/js/contactsheet.js"></script>
    <script>
    contactsheet_previewimage_prefix = '<?php echo addslashes($storageurl)?>';
    </script>
    <script type="text/javascript">
    jQuery.noConflict();
    </script>
    <?php 
    } ?>

<script type="text/javascript">
	ajaxLoadingTimer=<?php echo $ajax_loading_timer;?>;
</script>

<script src="<?php echo $baseurl?>/lib/js/category_tree.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $baseurl?>/lib/ckeditor/ckeditor.js"></script>
<?php if (!$disable_geocoding) { ?>
<script src="<?php echo $baseurl ?>/lib/OpenLayers/OpenLayers.js"></script>
<script src="https://maps.google.com/maps/api/js?v=3.2&amp;sensor=false"></script>
<?php } ?>
<?php if (!hook("ajaxcollections")) { ?>
<script src="<?php echo $baseurl;?>/lib/js/ajax_collections.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>
<?php } ?>

<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/plupload_2.1.8/plupload.full.min.js?<?php echo $css_reload_key;?>"></script>
<?php if ($plupload_widget){?>
	<link href="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.ui.plupload/css/jquery.ui.plupload.css?<?php echo $css_reload_key;?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />	
	<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.ui.plupload/jquery.ui.plupload.min.js?<?php echo $css_reload_key;?>"></script>
<?php } else { ?>
	<link href="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.plupload.queue/css/jquery.plupload.queue.css?<?php echo $css_reload_key;?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />
	<script type="text/javascript" src="<?php echo $baseurl_short;?>lib/plupload_2.1.8/jquery.plupload.queue/jquery.plupload.queue.min.js?<?php echo $css_reload_key;?>"></script>
<?php } ?>
<?php
if($videojs && ($pagename=='search' && $keyboard_navigation_video_search) || ($pagename=='view' && $keyboard_navigation_video_view) || (($pagename=='preview' || $pagename=='preview_all') && $keyboard_navigation_video_preview))
    {
    ?>
	<script type="text/javascript" src="<?php echo $baseurl_short?>lib/js/videojs-extras.js?<?php echo $css_reload_key?>"></script>
    <?php
    }
?>

<script type="text/javascript">
var baseurl_short="<?php echo $baseurl_short?>";
var baseurl="<?php echo $baseurl?>";
var pagename="<?php echo $pagename?>";
var errorpageload = "<h1><?php echo $lang["error"] ?></h1><p><?php echo str_replace(array("\r","\n"),'',nl2br($lang["error-pageload"])) ?></p>";
var applicationname = "<?php echo $applicationname?>";
var branch_limit="<?php echo $cat_tree_singlebranch?>";
var branch_limit_field = new Array();
var global_cookies = "<?php echo $global_cookies?>";
var global_trash_html = '<!-- Global Trash Bin (added through CentralSpaceLoad -->';
    global_trash_html += '<div id="trash_bin">';
    global_trash_html += '<span class="trash_bin_text"><?php echo $lang["trash_bin_title"]; ?></span>';
    global_trash_html += '</div>';
    global_trash_html += '<div id="trash_bin_delete_dialog" style="display: none;"></div>';
oktext="<?php echo $lang["ok"] ?>";
var scrolltopElementCentral='.ui-layout-center';
var scrolltopElementCollection='.ui-layout-south';
var scrolltopElementModal='#modal';
</script>

<script src="<?php echo $baseurl_short?>lib/js/global.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script>

<?php if ($keyboard_navigation)
    {
    include (dirname(__FILE__) . "/keyboard_navigation.php");
    }
hook("additionalheaderjs");?>

<?php
echo $headerinsert;
$extrafooterhtml="";
?>

<!-- Structure Stylesheet -->
<link href="<?php echo $baseurl?>/css/global.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" />
<!-- Colour stylesheet -->
<link href="<?php echo $baseurl?>/css/colour.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" />

<?php if ($pagename!="preview_all"){?><!--[if lte IE 7]> <link href="<?php echo $baseurl?>/css/globalIE.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css"  media="screen,projection,print" /> <![endif]--><?php } ?>
<!--[if lte IE 5.6]> <link href="<?php echo $baseurl?>/css/globalIE5.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css"  media="screen,projection,print" /> <![endif]-->

<?php
global $ctheme;
echo get_plugin_css($ctheme);
// after loading these tags we change the class on them so a new set can be added before they are removed (preventing flickering of overridden theme)
?>
<script>jQuery('.plugincss').attr('class','plugincss0');</script>
<?php

hook("headblock");
 
endif; # !hook("customhtmlheader") 
if($slimheader)
    {
    $body_classes[] = 'SlimHeader';
    }
?>
</head>
<body lang="<?php echo $language ?>" class="<?php echo implode(' ', $body_classes); ?>" <?php if (isset($bodyattribs)) { ?><?php echo $bodyattribs?><?php } ?>>

<!-- Loading graphic -->
<?php
if(!hook("customloadinggraphic"))
	{
	?>
	<div id="LoadingBox"><?php echo $lang["pleasewait"] ?><img src="<?php echo $baseurl_short ?>gfx/interface/loading.gif"></div>
	<?php
	}
?>

<?php hook("bodystart"); ?>

<?php
# Commented as it was causing IE to 'jump'
# <body onLoad="if (document.getElementById('searchbox')) {document.getElementById('searchbox').focus();}">
?>

<!--Global Header-->
<div id="UICenter" class="ui-layout-center">
<?php
if (($pagename=="terms") && (getval("url","")=="index.php")) {$loginterms=true;} else {$loginterms=false;}
if (($pagename!="preview" || $preview_header_footer) && $pagename!="preview_all") { ?>

<?php
$homepage_url=$baseurl."/pages/".$default_home_page;
if ($use_theme_as_home){$homepage_url=$baseurl."/pages/themes.php";}
if ($use_recent_as_home){$homepage_url=$baseurl."/pages/search.php?search=".urlencode('!last'.$recent_search_quantity);}
if ($pagename=="login" || $pagename=="user_request" || $pagename=="user_password"){$homepage_url=$baseurl."/index.php";}

hook("beforeheader");

# Calculate Header Image Display #
if(isset($usergroup))
    {
    //Get group logo value
    $curr_group = get_usergroup($usergroup);
    if (!empty($curr_group["group_specific_logo"]))
        {
        $linkedheaderimgsrc = (isset($storageurl)? $storageurl : $baseurl."/filestore"). "/admin/groupheaderimg/group".$usergroup.".".$curr_group["group_specific_logo"];
        }
    }

$linkUrl=isset($header_link_url) ? $header_link_url : $homepage_url;
if($slimheader)
    {
    ?>
    <div id="Header" class="<?php
        echo ((isset($slimheader_darken) && $slimheader_darken) ? 'slimheader_darken' : '');
        echo ((isset($slimheader_fixed_position) && $slimheader_fixed_position) ? ' SlimHeaderFixedPosition' : '');
    ?>"<?php
    if (isset($header_colour_style_override) && $header_colour_style_override!='') { ?> style="background: <?php echo $header_colour_style_override; ?>;"<?php } ?>>
    <?php hook("responsiveheader");
    if($header_text_title) 
        {?>
        <div id="TextHeader"><?php if ($k=="" || $internal_share_access){?><a href="<?php echo $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo $applicationname;?><?php if ($k=="" || $internal_share_access){?></a><?php } ?></div>
        <?php if ($applicationdesc!="")
            {?>
            <div id="TextDesc"><?php echo i18n_get_translated($applicationdesc);?></div>
            <?php 
            }
        }
    else
        {
        if($linkedheaderimgsrc !="") 
            {
            $header_img_src = $linkedheaderimgsrc;
            if(substr($header_img_src, 0, 4) !== 'http')
                {
                // Set via System Config page?
                if (substr($header_img_src, 0, 13) == '[storage_url]')
                    {
                    // Parse and replace the storage URL
                    $header_img_src = str_replace('[storage_url]', $storageurl, $header_img_src);
                    }
                else
                    {
                    // Set via config.php
                    // if image source already has the baseurl short, then remove it and add it here
                    if(substr($header_img_src, 0, 1) === '/')
                        {
                        $header_img_src = substr($header_img_src, 1);
                        }
                    $header_img_src = $baseurl_short . $header_img_src;
                    }
                }
            }
        else 
            {
            $header_img_src = $baseurl.'/gfx/titles/title.png';
            }
        if($header_link && ($k=="" || $internal_share_access))
	    {?>
	    <a href="<?php echo $linkUrl; ?>" onClick="return CentralSpaceLoad(this,true);" class="HeaderImgLink"><img src="<?php echo $header_img_src; ?>" id="HeaderImg"></img></a>
	    <?php
	    }
	else
	    {?>
	    <div class="HeaderImgLink"><img src="<?php echo $header_img_src; ?>" id="HeaderImg"></img></div>
	    <?php
	    }
        }
    }
else
    {
    ?>
    <div id="Header" <?php if ($header_text_title){?>style="background-image:none;"<?php } ?>>
    <?php hook("responsiveheader");
    if ($header_link && !$header_text_title && ($k=="" || $internal_share_access)) 
        {
       if(isset($header_link_height) || isset($header_link_width))
            {
            # compile style attribute for headerlink
            $headerlink_style='';
            if(isset($header_link_height)){$headerlink_style.="height:".$header_link_height."px;";}
            if(isset($header_link_width)){$headerlink_style.="width:".$header_link_width."px;";}
            }
       $onclick = (substr($linkUrl, 0, strlen($baseurl)) === $baseurl || substr($linkUrl, 0, strlen($baseurl_short)) === $baseurl_short) ? "" : ' onclick="return CentralSpaceLoad(this,true);"';
        ?><a class="headerlink" <?php if(isset($headerlink_style)){?> style="<?php echo $headerlink_style?>" <?php } ?> href="<?php echo $linkUrl ?>"<?php echo $onclick?>></a><?php
        }
    if ($header_text_title)
        {?>
        <div id="TextHeader"><?php if ($k=="" || $internal_share_access){?><a href="<?php echo $homepage_url?>"  onClick="return CentralSpaceLoad(this,true);"><?php } ?><?php echo $applicationname;?><?php if ($k=="" || $internal_share_access){?></a><?php } ?></div>
        <?php if ($applicationdesc!="")
            {?>
            <div id="TextDesc"><?php echo i18n_get_translated($applicationdesc);?></div>
            <?php 
            }
        }
    }

hook("headertop");

if (!isset($allow_password_change)) {$allow_password_change=true;}

if (isset($username) && ($pagename!="login") && ($loginterms==false) && ($k=="" || $internal_share_access)) { ?>
<div id="HeaderNav1" class="HorizontalNav ">

<?php
hook("beforeheadernav1");
if (isset($anonymous_login) && ($username==$anonymous_login))
	{
	if (!hook("replaceheadernav1anon")) 
        {
    	?>
    	<ul>
    	<li><a href="<?php echo $baseurl?>/login.php"<?php if($anon_login_modal){?> onClick="return ModalLoad(this,true);" <?php } ?>><?php echo $lang["login"]?></a></li>
    	<?php hook("addtoplinksanon");?>
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
	<li>
	<a href="<?php echo $baseurl?>/pages/user/user_home.php"  onClick="ModalClose();return ModalLoad(this,true,true,'right');"><?php echo htmlspecialchars(($userfullname=="" ? $username : $userfullname)) ?></a>
		<span style="display: none;" class="MessageCountPill"></span>
		<div id="MessageContainer" style="position:absolute; "></div>
	<?php } ?></li>
	
	<!-- Team centre link -->
	<?php if (checkperm("t")) { ?><li><a href="<?php echo $baseurl?>/pages/team/team_home.php" onClick="ModalClose();return ModalLoad(this,true,true,'right');"><?php echo $lang["teamcentre"]?></a>
	<?php if ($team_centre_alert_icon && (checkperm("R")||checkperm("r")) &&  (sql_value("select sum(thecount) value from (select count(*) thecount from request where status = 0 union select count(*) thecount from research_request where status = 0) as theunion",0) > 0)){
			echo '<img src="' . $baseurl . '/gfx/images/attention_16.png" width="16" height="16" alt="Alert" class="TeamCentreAlertIcon" />';
	} ?>
	</li><?php } ?>
	<!-- End of team centre link -->
	
	<?php hook("addtoplinks");
	if(!isset($password_reset_mode) || !$password_reset_mode)
		{?>
		<li><a href="<?php echo $baseurl?>/login.php?logout=true&amp;nc=<?php echo time()?>"><?php echo $lang["logout"]?></a></li>
		<?php
		}
	hook("addtologintoolbarmiddle");?>
	

	
	</ul>
	<?php
	} /* end replaceheadernav1 */
	}
hook("afterheadernav1");
include_once __DIR__ . '/../pages/ajax/message.php';
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
if (!in_array($pagename,$omit_searchbar_pages) && ($loginterms==false) && ($k == '' || $internal_share_access) && !hook("replace_searchbarcontainer")) 	
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
        <?php if (($pagename!="login") && ($pagename!="user_password") && ($pagename!="user_request")) { ?><div id="CentralSpaceContainer"<?php
        if(isset($slimheader) && $slimheader && isset($slimheader_fixed_position) && $slimheader_fixed_position)
            {
            ?> class="SlimHeaderFixedPosition"<?php
            }
        ?>><?php }

hook("aftercentralspacecontainer");
?>
<div id="<?php echo $div?>">


<?php

hook("afterheader");

} // end if !ajax

// Update header links to add a class that indicates current location
$parsed_url = parse_url($baseurl);

$scheme = @$parsed_url['scheme'];
$host = @$parsed_url['host'];
$port = @$parsed_url['port'];
?>
<script>
jQuery(document).ready(function()
		{
		ActivateHeaderLink('<?php echo $scheme . "://" . $host . (isset($port)?":" . $port:"") . $_SERVER["REQUEST_URI"] ?>');
		});
</script>
<?php
// Non-ajax specific hook 
hook("start_centralspace");

	

if ($k!="" && !$internal_share_access) { ?>
<style>
#CentralSpaceContainer  {padding-right:0;margin: 0px 10px 20px 25px;}
</style>
<?php }
// Ajax specific hook
if ($ajax) {
    // remove Spectrum colour picker as it is out of CentralSpace div scope
    ?><script>
        jQuery('.sp-container').remove();
    </script>
    <?php
    hook("afterheaderajax");
}
?>
