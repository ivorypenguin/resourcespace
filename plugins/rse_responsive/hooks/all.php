<?php
function HookRse_responsiveAllResponsivemeta() {
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!--[if lt IE 9]>
        <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <![endif]-->
    <?php
}
function serveHeader() {
    global $lang,$username,$pagename,$loginterms,$baseurl,$linkedheaderimgsrc,$allow_password_change,$userfullname,$username,$slimheader,$theme,$responsiveheaderimgsrc,
            $storageurl;

    if(!empty($linkedheaderimgsrc)) 
        {
        $header_img_src = $linkedheaderimgsrc;
        }
    else if(!empty($responsiveheaderimgsrc))
        {
        $header_img_src=$responsiveheaderimgsrc;
        }
    else 
        {
        $header_img_src = $baseurl.'/gfx/titles/title.png';
        }

    // Set via System Config page?
    if('[storage_url]' == substr($header_img_src, 0, 13))
        {
        // Parse and replace the storage URL
        $header_img_src = str_replace('[storage_url]', $storageurl, $header_img_src);
        }

    // If there is a baseurl, no need to add it again
    if(false === strpos($header_img_src, $baseurl))
        {
        $header_img_src = $baseurl . $header_img_src;
        }

    if (!$slimheader)
        {
        ?>
        <a href="<?php echo $baseurl; ?>"><img src="<?php echo $header_img_src; ?>" id="HeaderImg" style="display:none;"></img></a>
        
        <?php
        }
    else 
        {
        $linkedheaderimgsrc=$header_img_src;
        }

    if (isset($username) && ($pagename!="login") && ($loginterms==false) && getval("k","")=="") 
        { 
        ?>   
        <div id="HeaderButtons" style="display:none;">
            <a href="#" id="HeaderNav1Click" class="ResponsiveHeaderButton ResourcePanel ResponsiveButton"><span class="rbText"><?php echo $allow_password_change == false ? htmlspecialchars(($userfullname=="" ? $username : $userfullname)) : $lang["responsive_settings_menu"]; ?> </span><span class="glyph glyph_user"></span></a>
            <a href="#" id="HeaderNav2Click" class="ResponsiveHeaderButton ResourcePanel ResponsiveButton"><span class="rbText"><?php echo $lang["responsive_main_menu"]; ?> </span><span class="glyph glyph_menu"></span></a>
        </div>
        <?php
        }
    ?>
    </div>
    <?php
}
function HookRse_responsiveAllResponsiveheader() 
    {
    ?>
    <div id="HeaderResponsive">
    <?php
    }
function HookRse_responsiveAllHeadertop()
    {
    serveHeader();
    }
function HookRse_responsiveAllReplaceheaderfullnamelink() 
    {
    global $allow_password_change,$userfullname,$username;
    if ($allow_password_change == false) 
        {
        ?>
        <li class="ResponsiveNav1Username"><?php echo htmlspecialchars(($userfullname=="" ? $username : $userfullname))?></li>
        <?php  
        return true;
        }
    }
function HookRse_responsiveAllResponsivesimplesearch() 
    {
	global $lang,$searchbuttons;
	$searchbuttons.="<input type=\"button\" style=\"display:none;\" id=\"Rssearchexpand\" class=\"searchbutton\"value=\"".$lang["responsive_more"]."\">";
    }
function HookRse_responsiveAllResponsiveresultoptions() 
    {
    global $resources_count,$results_count,$lang;
    ?>
    <div class="ResponsiveResultDisplayControls">
    <a href="#" id="Responsive_ResultDisplayOptions" class="ResourcePanel ResponsiveButton" style="display:none;">Result Settings<span class="glyph glyph_result"></span></a>
    <div id="ResponsiveResultCount"><span class="Selected">
    <?php
    if (isset($collections)) 
        {
        echo number_format($results_count)?> </span><?php echo ($results_count==1) ? $lang["youfoundresult"] : $lang["youfoundresults"];
        } 
    else
        {
        echo number_format($resources_count)?> </span><?php echo ($resources_count==1)? $lang["youfoundresource"] : $lang["youfoundresources"];
        }
     ?></div>
 </div>
    <?php
    }
function HookRse_responsiveAllResponsivethumbsloaded() 
    {
    global $lazyload;
    if (!$lazyload) { ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            if(typeof responsive_newpage !== 'undefined' && responsive_newpage === true) {
                hideMyCollectionsCols();
                responsiveCollectionBar();
                responsive_newpage = false;
            }
        }); 
    </script>
    <?php } 
    }
function HookRse_responsiveAllResponsivescripts() 
    {
	global $lang, $baseurl, $slimheader,$rcsskey;
    $plugin_js_path = $baseurl . '/plugins/rse_responsive/js/';
    echo '<script src="' . $plugin_js_path . "general.js?rck=" .$rcsskey.'" type="text/javascript"></script>';
?>
	<script>
        function hideMyCollectionsCols() {
          if(jQuery(window).width() < 700 && jQuery("#collectionform td:nth-child(2)").is(':hidden') && !is_touch_device()) {
                jQuery("td:nth-child(2),th:nth-child(2)").show();
                jQuery("td:nth-child(3),th:nth-child(3)").show();
                jQuery("td:nth-child(4),th:nth-child(4)").show();
                jQuery("td:nth-child(6),th:nth-child(6)").show();
                jQuery("td:nth-child(7),th:nth-child(7)").show();
            }
            if(jQuery(window).width() < 800 && jQuery("#collectionform td:nth-child(8)").is(':hidden') && !is_touch_device()) {
                jQuery("td:nth-child(8),th:nth-child(8)").show();
            }
        }
        function toggleSimpleSearch() {
            if(jQuery("#searchspace").hasClass("ResponsiveSimpleSearch")) {
                jQuery("#searchspace").removeClass("ResponsiveSimpleSearch");
                jQuery("#Rssearchexpand").val("<?php echo $lang["responsive_more"];?>");
            }else {
                jQuery("#searchspace").addClass("ResponsiveSimpleSearch");
                jQuery("#Rssearchexpand").val(" <?php echo $lang["responsive_less"];?> ");
            }
        }
        function toggleResultOptions() {
            jQuery("#CentralSpace .TopInpageNavLeft .InpageNavLeftBlock").slideToggle(100);
            jQuery("#SearchResultFound").hide();
        }
        <?php 
        /* Responsive Stylesheet inclusion based upon viewing device */
        if(!$slimheader) 
            { ?>
            if (document.createStyleSheet){
                    document.createStyleSheet('<?php echo $baseurl;?>/plugins/rse_responsive/css/main-style.css?rcsskey=<?php echo $rcsskey; ?>');
                }
            else {
                    jQuery("head").append("<link rel='stylesheet' href='<?php echo $baseurl;?>/plugins/rse_responsive/css/main-style.css?rcsskey=<?php echo $rcsskey; ?>' type='text/css' media='screen' />");
                }
            if(!is_touch_device() && jQuery(window).width()<=1300) {
                if (document.createStyleSheet){
                    document.createStyleSheet('<?php echo $baseurl;?>/plugins/rse_responsive/css/non-touch.css?rcsskey=<?php echo $rcsskey; ?>');
                }
                else {
                    jQuery("head").append("<link rel='stylesheet' href='<?php echo $baseurl;?>/plugins/rse_responsive/css/non-touch.css?rcsskey=<?php echo $rcsskey; ?>' type='text/css' media='screen' />");
                }
            }
        <?php 
            } 
        else {?>
            if (document.createStyleSheet){
                    document.createStyleSheet('<?php echo $baseurl;?>/plugins/rse_responsive/css/slim-style.css?rcsskey=<?php echo $rcsskey; ?>');
                }
            else {
                    jQuery("head").append("<link rel='stylesheet' href='<?php echo $baseurl;?>/plugins/rse_responsive/css/slim-style.css?rcsskey=<?php echo $rcsskey; ?>' type='text/css' media='screen' />");
                }
            if(!is_touch_device() && jQuery(window).width()<=1300) {
                if (document.createStyleSheet){
                    document.createStyleSheet('<?php echo $baseurl;?>/plugins/rse_responsive/css/slim-non-touch.css?rcsskey=<?php echo $rcsskey; ?>');
                }
                else {
                    jQuery("head").append("<link rel='stylesheet' href='<?php echo $baseurl;?>/plugins/rse_responsive/css/slim-non-touch.css?rcsskey=<?php echo $rcsskey; ?>' type='text/css' media='screen' />");
                }
            }
        <?php

        }?>

        function touchScroll(id){
            if(is_touch_device()){
                var el=document.getElementById(id);
                var scrollStartPos=0;
         
                document.getElementById(id).addEventListener("touchstart", function(event) {
                    scrollStartPos=this.scrollTop+event.touches[0].pageY;
                });
         
                document.getElementById(id).addEventListener("touchmove", function(event) {
                    this.scrollTop=scrollStartPos-event.touches[0].pageY;
                });
            }
        }

        var responsive_show = "<?php echo $lang['responsive_collectiontogglehide'];?>";
        var responsive_hide;
        var responsive_newpage = true;

        if(jQuery(window).width()<=700) {
            touchScroll("UICenter");
        }
        jQuery(window).resize(function() {
            hideMyCollectionsCols();
            responsiveCollectionBar();
        });
        if(jQuery(window).width()<=900) {
            jQuery('#CollectionDiv').hide(0);
        }
        jQuery("#HeaderNav1Click").click(function(event) {
            event.preventDefault();
            if(jQuery(this).hasClass("RSelectedButton")) {
                jQuery(this).removeClass("RSelectedButton");
                jQuery("#HeaderNav1").slideUp(0);
                <?php if($slimheader){ ?>
                    jQuery("#Header").removeClass("HeaderMenu");
                <?php } ?>
            }else {
                jQuery("#HeaderNav2Click").removeClass("RSelectedButton");
                jQuery("#HeaderNav2").slideUp(80);
                <?php if($slimheader){ ?>
                    jQuery("#Header").addClass("HeaderMenu");
                <?php } ?>
                jQuery(this).addClass("RSelectedButton");
                jQuery("#HeaderNav1").slideDown(80);
            }
            if(jQuery("#searchspace").hasClass("ResponsiveSimpleSearch")) {
                toggleSimpleSearch();
            }      
        });
        jQuery("#HeaderNav2Click").click(function(event) {
            event.preventDefault();
            if(jQuery(this).hasClass("RSelectedButton")) {
                jQuery(this).removeClass("RSelectedButton");
                jQuery("#HeaderNav2").slideUp(0);
                <?php if($slimheader){ ?>
                    jQuery("#Header").removeClass("HeaderMenu");
                <?php } ?>
            }else {
                <?php if($slimheader){ ?>
                    jQuery("#Header").addClass("HeaderMenu");
                <?php } ?>
                jQuery("#HeaderNav1Click").removeClass("RSelectedButton");
                jQuery("#HeaderNav1").slideUp(80);
                jQuery(this).addClass("RSelectedButton");
                jQuery("#HeaderNav2").slideDown(80);
            } 
            if(jQuery("#searchspace").hasClass("ResponsiveSimpleSearch")) {
                toggleSimpleSearch();
            }  
        });
        jQuery("#HeaderNav2").on("click","a",function() {
            <?php if($slimheader){ ?>
                if(jQuery(window).width() <= 1200) {
             <?php }
             else { ?>
                if(jQuery(window).width() <= 700) {
             <?php } ?>
                jQuery("#HeaderNav2").slideUp(0);
                jQuery("#HeaderNav2Click").removeClass("RSelectedButton");
            }
        });
        jQuery("#HeaderNav1").on("click","a",function() {
            <?php if($slimheader){ ?>
                if(jQuery(window).width() <= 1200) {
             <?php }
             else { ?>
                if(jQuery(window).width() <= 700) {
             <?php } ?>
                jQuery("#HeaderNav1").slideUp(00);
                jQuery("#HeaderNav1Click").removeClass("RSelectedButton");
            }
        });
        jQuery("#SearchBarContainer").on("click","#Rssearchexpand",toggleSimpleSearch);
        jQuery("#SearchBarContainer").on("click","a",toggleSimpleSearch);
        jQuery("#CentralSpaceContainer").on("click","#Responsive_ResultDisplayOptions",function(event) {
            if(jQuery(this).hasClass("RSelectedButton")) {
                jQuery(this).removeClass("RSelectedButton");
            }else {
                jQuery(this).addClass("RSelectedButton");
            }
            toggleResultOptions();
        });
        if(jQuery(window).width() <= 700 && jQuery(".ListviewStyle").length && is_touch_device()) {
            jQuery("td:last-child,th:last-child").hide();
        }
	</script>
	<?php
}
?>