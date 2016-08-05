<?php 
hook("before_footer_always");

if(getval("loginmodal",""))
	{
	$login_url=$baseurl."/login.php?url=".urlencode(getvalescaped("url",""))."&api=".urlencode(getval("api",""))."&error=".urlencode(getval("error",""))."&auto=".urlencode(getval("auto",""))."&nocookies=".urlencode(getval("nocookies",""))."&logout=".urlencode(getval("logout",""));
	?><script>
		jQuery(document).ready(function(){
			ModalLoad('<?php echo $login_url?>',true);
		});
	</script>
	<?php
	}
	
# Do not display header / footer when dynamically loading CentralSpace contents.
if (getval("ajax","")=="" && !hook("replace_footer")) 
	{ 
	hook("beforefooter");
?>
<div class="clearer"></div>

<!-- Use aria-live assertive for high priority changes in the content: -->
<span role="status" aria-live="assertive" class="ui-helper-hidden-accessible"></span>

<!-- Global Trash Bin -->
<div id="trash_bin">
	<span class="trash_bin_text"><?php echo $lang['trash_bin_title']; ?></span>
</div>
<div id="trash_bin_delete_dialog" style="display: none;"></div>

<div class="clearerleft"></div>
</div><!--End div-CentralSpace-->
<?php if (($pagename!="login") && ($pagename!="user_password") && ($pagename!="preview_all") && ($pagename!="user_request")) { ?></div><?php } ?><!--End div-CentralSpaceContainer-->

<div class="clearer"></div>

<?php hook("footertop"); ?>
<?php
$omit_footer_pages=array("login","user_request","user_password","preview_all","done","change_language");
if(!$preview_header_footer){$omit_footer_pages[]="preview";}
$modify_omit_footer_pages=hook("modify_omit_footer_pages","",array($omit_footer_pages));
if(!empty($modify_omit_footer_pages))
	{
	$omit_footer_pages=$modify_omit_footer_pages;
	}

if(!in_array($pagename,$omit_footer_pages) && ($loginterms==false)) 
{ ?>

<!--Global Footer-->
<div id="Footer">

<?php if ($k=="" || (isset($internal_share_access) && $internal_share_access)) 
	{ ?>
	<div id="FooterNavLeft" class="">
	<span id="FooterLanguages">
	<?php 
	if ($disable_languages==false && $show_language_chooser)
		{
		echo $lang["language"]?>: <a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/pages/change_language.php"><?php echo $languages[$language]?></a>
		<?php 	
		} ?>
	</span>
	</div>
	<?php 
	if (!hook("replacefooternavright"))
		{
		if ($bottom_links_bar && ($about_link || $contact_link))
			{ ?>
			<div id="FooterNavRight" class="HorizontalNav HorizontalWhiteNav">
			<ul>
			<?php 
			if (!hook("replacefooterlinks"))
				{
				if (!$use_theme_as_home && !$use_recent_as_home) { ?><li><a href="<?php echo $baseurl?>/pages/<?php echo $default_home_page?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["home"]?></a></li><?php }
				if ($about_link) { ?><li id="footer_about_link"><a href="<?php echo $baseurl?>/pages/about.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["aboutus"]?></a></li><?php }
				if ($contact_link || $nav2contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php }
				} /* end hook replacefooterlinks */ 
			?>
			</ul>
			</div>
			<?php 
			}
		} /* end hook replacefooternavright */
	} 

if(!hook("replace_footernavrightbottom"))
	{
	?>
	<div id="FooterNavRightBottom" class="OxColourPale"><?php echo text("footer")?></div>
	<?php
	}
?>
<div class="clearer"></div>
</div>
<?php 
} ?>

<br />

<?php echo $extrafooterhtml; ?>

<?php } // end ajax ?>

<?php /* always include the below as they are perpage */?>

<?php hook("footerbottom"); ?>

<?php draw_performance_footer();?>

<?php
//titlebar modifications

if ($show_resource_title_in_titlebar){
$general_title_pages=array("admin_content","team_archive","team_resource","team_user","team_request","team_research","team_plugins","team_mail","team_export","team_stats","team_report","team_user_log","research_request","team_user_edit","admin_content_edit","team_request_edit","team_research_edit","requests","edit","themes","collection_public","collection_manage","team_home","help","home","tag","upload_java_popup","upload_java","contact","geo_search","search_advanced","about","contribute","user_preferences","view_shares","check","index");
$search_title_pages=array("contactsheet_settings","search","preview_all","collection_edit","edit","collection_download","collection_share","collection_request");
$resource_title_pages=array("view","delete","log","alternative_file","alternative_files","resource_email","edit","preview");
$additional_title_pages=array(hook("additional_title_pages_array"));

    // clear resource or search title for pages that don't apply:
    if (!in_array($pagename,array_merge($general_title_pages,$search_title_pages,$resource_title_pages)) && (empty($additional_title_pages) || !in_array($pagename,$additional_title_pages))){
		echo "<script language='javascript'>\n";
		echo "document.title = \"$applicationname\";\n";
		echo "</script>";
    }
    // place resource titles
    else if (in_array($pagename,$resource_title_pages) && !isset($_GET['collection']) && !isset($_GET['java'])) /* for edit page */{
        $title =  str_replace('"',"''",i18n_get_translated(get_data_by_field($ref,$view_title_field)));
        echo "<script type=\"text/javascript\" language='javascript'>\n";
        
        if ($pagename=="edit"){$title=$lang['action-edit']." - ".$title;}
        
        echo "document.title = \"$applicationname - $title\";\n";

        if($pagename=='edit' && $distinguish_uploads_from_edits) {

			$js = sprintf("
				jQuery(document).ready(function() {
					var h1 = jQuery(\"h1\").text();

					if(h1 == \"%s\") {
						document.title = \"%s - \" + h1;\n
					}
				});
			",
				$lang["addresourcebatchbrowser"],
				$applicationname);

			echo $js;

        }
        
        echo "</script>";
    }

    // place collection titles
    else if (in_array($pagename,$search_title_pages)){
        if (isset($search_title)){
            $title=str_replace('"',"''",$lang["searchresults"]." - ".html_entity_decode(strip_tags($search_title)));
        }
        else if (($pagename=="collection_download") || $pagename=="edit" && getval("collection","")!=""){
            $collectiondata=get_collection($collection);
            $title = strip_tags(str_replace('"',"''",i18n_get_collection_name($collectiondata)));
            }  
        else {
            $collection=getval("ref","");
            $collectiondata=get_collection($collection);
            $title = strip_tags(str_replace('"',"''",i18n_get_collection_name($collectiondata)));
            }
        // add a hyphen if title exists  
        if (strlen($title)!=0){$title="- $title";}    
        if ($pagename=="edit"){$title=" - ".$lang['action-editall']." ".$title;}
        if ($pagename=="collection_share"){$title=" - ".$lang['share']." ".$title;}
        if ($pagename=="collection_edit"){$title=" - ".$lang['action-edit']." ".$title;}
        if ($pagename=="preview_all"){$title=" - ".$lang['preview_all']." ".$title;}
        if ($pagename=="collection_download"){$title=" - ".$lang['download']." ".$title;}
        echo "<script language='javascript'>\n";
        echo "document.title = \"$applicationname $title\";\n";
        echo "</script>";
    }
    
      // place page titles
    else if (in_array($pagename,$general_title_pages)){ 
		
		if ($pagename=="themes"){
			$pagetitle=$lang['themes'];
			for ($n=0;$n<$theme_category_levels;$n++){
				if (getval("theme".$n,"")!=""){
					$pagetitle.=" / ".getval("theme".$n,"");
				}
			}
		}
		else if (isset($lang[$pagename])){
			$pagetitle=$lang[$pagename];
		} 
		else if (isset($lang['action-'.$pagename])){
			$pagetitle=$lang["action-".$pagename];
			if (getval("java","")!=""){$pagetitle=$lang['upload']." ".$pagetitle;}
		}
		else if (isset($lang[str_replace("_","",$pagename)])){
			$pagetitle=$lang[str_replace("_","",$pagename)];
		}
		else if ($pagename=="admin_content"){
			$pagetitle=$lang['managecontent'];
		}
		else if ($pagename=="collection_public"){
			$pagetitle=$lang["publiccollections"];
		}
		else if ($pagename=="collection_manage"){
			$pagetitle=$lang["mycollections"];
		}
		else if ($pagename=="team_home"){
			$pagetitle=$lang["teamcentre"];
		}
		else if ($pagename=="help"){
			$pagetitle=$lang["helpandadvice"];
		}
		else if ($pagename=="tag"){
			$pagetitle=$lang["tagging"];
		}
		else if (strpos($pagename,"upload")!==false){
			$pagetitle=$lang["upload"];
		}
		else if ($pagename=="contact"){
			$pagetitle=$lang["contactus"];
		}
		else if ($pagename=="geo_search"){
			$pagetitle=$lang["geographicsearch"];
		}
		else if ($pagename=="search_advanced"){
			$pagetitle=$lang["advancedsearch"];
			if (getval("archive","")==2){$pagetitle.=" - ".$lang['archiveonlysearch'];}
		}	
		else if ($pagename=="about"){
			$pagetitle=$lang["aboutus"];
		}	
		else if ($pagename=="contribute"){
			$pagetitle=$lang["mycontributions"];
		}	
		else if ($pagename=="user_preferences"){
			$pagetitle=$lang["user-preferences"];
		}	
		else if ($pagename=="requests"){
			$pagetitle=$lang["myrequests"];
		}	
		else if ($pagename=="team_resource"){
			$pagetitle=$lang["manageresources"];
		}	
		else if ($pagename=="team_archive"){
			$pagetitle=$lang["managearchiveresources"];
		}	
		else if($pagename=="view_shares"){
			$pagetitle=$lang["shared_collections"];
		}	
		else if($pagename=="team_user"){
			$pagetitle=$lang["manageusers"];
		}
		else if($pagename=="team_request"){
			$pagetitle=$lang["managerequestsorders"];
		}
		else if($pagename=="team_research"){
			$pagetitle=$lang["manageresearchrequests"];
		}
		else if($pagename=="team_plugins"){
			$pagetitle=$lang["pluginmanager"];
		}
		else if($pagename=="team_mail"){
			$pagetitle=$lang["sendbulkmail"];
		}
		else if($pagename=="team_export"){
			$pagetitle=$lang["exportdata"];
		}
		else if($pagename=="team_export"){
			$pagetitle=$lang["exportdata"];
		}
		else if($pagename=="team_stats"){
			$pagetitle=$lang["viewstatistics"];
		}
		else if($pagename=="team_report"){
			$pagetitle=$lang["viewreports"];
		}
		else if($pagename=="check"){
			$pagetitle=$lang["installationcheck"];
		}
		else if($pagename=="index"){
			$pagetitle=$lang["systemsetup"];
		}
		else if($pagename=="team_user_log"){
			global $userdata;
			$pagetitle=$lang["userlog"] . ": " . $userdata["fullname"];
		}
		else if($pagename=="team_user_edit"){
			global $userdata,$display_useredit_ref;
			$pagetitle=$lang["edituser"];
			if($display_useredit_ref){
				$pagetitle.=" ".$ref;
			}
		}
		else if($pagename=="admin_content_edit"){
			$pagetitle=$lang["editcontent"];
		}
		else if($pagename=="team_request_edit"){
			$pagetitle=$lang["editrequestorder"];
		}
		else if($pagename=="team_research_edit"){
			$pagetitle=$lang["editresearchrequest"];
		}
		else {
			$pagetitle="";
		}
		if (strlen($pagetitle)!=0){$pagetitle="- $pagetitle";} 
        echo "<script language='javascript'>\n";
        echo "document.title = \"$applicationname $pagetitle\";\n";
        echo "</script>";
    }
    hook("additional_title_pages");
}
   
?><script src="<?php echo $baseurl?>/lib/js/Placeholders.min.js?css_reload_key=<?php echo $css_reload_key?>" type="text/javascript"></script><?php


if (getval("ajax","")=="") {
	// don't show closing tags if we're in ajax mode
	?>


<!--CollectionDiv-->
<?php 
$omit_collectiondiv_load_pages=array("login","user_request","user_password","index","preview_all");

$more_omit_collectiondiv_load_pages=hook("more_omit_collectiondiv_load_pages");
if(is_array($more_omit_collectiondiv_load_pages)){
	$omit_collectiondiv_load_pages=array_merge($omit_collectiondiv_load_pages,$more_omit_collectiondiv_load_pages);
}
?></div>

<?php # Work out the current collection (if any) from the search string if external access
if (isset($k) && $k!="" && isset($search) && !isset($usercollection))
    {
    if (substr($search,0,11)=="!collection") {
		$usercollection = substr($search,11);
    }
}
?><script>
<?php if (!isset($usercollection)){?>
	usercollection='';
<?php } else { ?>
	usercollection='<?php echo htmlspecialchars($usercollection) ?>';
	var collections_popout = <?php echo $collection_bar_popout? "true": "false"; ?>;
<?php } ?>
</script><?php 
if (!hook("replacecdivrender"))
	{
	if ($collections_footer && !in_array($pagename,$omit_collectiondiv_load_pages) && !checkperm("b") && isset($usercollection)) 
		{
        // Footer requires restypes as a string because it is urlencoding them
        if(isset($restypes) && is_array($restypes))
            {
            $restypes = implode(',', $restypes);
            }
            ?>
		<div id="CollectionDiv" class="CollectBack AjaxCollect ui-layout-south"></div>

		<script type="text/javascript">
			var collection_frame_height=<?php echo $collection_frame_height?>;
			var thumbs="<?php echo htmlspecialchars($thumbs); ?>";
			function ShowThumbs() {
				myLayout.sizePane("south", collection_frame_height);
				jQuery('.ui-layout-south').animate({scrollTop:0}, 'fast');
				jQuery('#CollectionMinDiv').hide();
				jQuery('#CollectionMaxDiv').show();
				SetCookie('thumbs',"show",1000);
				ModalCentre();
			}
			function HideThumbs() {
				myLayout.sizePane("south", 40);
				jQuery('.ui-layout-south').animate({scrollTop:0}, 'fast');			
				jQuery('#CollectionMinDiv').show();
				jQuery('#CollectionMaxDiv').hide();
				SetCookie('thumbs',"hide",1000);
				ModalCentre();
			}
			function ToggleThumbs() {
				thumbs = getCookie("thumbs");
				if (thumbs=="show"){
					HideThumbs();
				} else { 
					ShowThumbs();
				}
			}
			function InitThumbs() {
				if(thumbs!="hide") {
					ShowThumbs();
				} else if(thumbs=="hide") {
					HideThumbs();
				}
			}
			myLayout=jQuery('body').layout({
				//closable:false,
				resizable:true,
				livePaneResizing:true,
				triggerEventsDuringLiveResize: false,
				minSize:40,
				spacing_open:6,
				spacing_closed:6,
				togglerLength_open:"200",
				togglerTip_open: '<?php echo $lang["toggle"]?>',
				resizerTip: '<?php echo $lang["resize"]?>',
				south__onclose_start: function(pane){
					if (pane=="south"){
						if(jQuery('.ui-layout-south').height()>40 && thumbs!="hide"){
							HideThumbs();
						} else if(jQuery('.ui-layout-south').height()<=40 && thumbs=="hide"){
							ShowThumbs();
						}
						return false;
					}
				ModalCentre();
				},
				south__onresize: function(pane){
					if (pane=="south"){
						thumbs = getCookie("thumbs");
						if(jQuery('.ui-layout-south').height() < collection_frame_height && thumbs!="hide"){
							HideThumbs();
						} else if(jQuery('.ui-layout-south').height()> 40 && thumbs=="hide"){
							ShowThumbs();
						}
					}
				ModalCentre();
				}
			});
			window.onload = function() {
				CollectionDivLoad('<?php echo $baseurl_short?>pages/collections.php?thumbs=<?php echo urlencode($thumbs); ?>&collection='+usercollection+'<?php echo (isset($k) ? "&k=".urlencode($k) : ""); ?>&order_by=<?php echo (isset($order_by) ? urlencode($order_by) : ""); ?>&sort=<?php echo (isset($sort) ? urlencode($sort) : ""); ?>&search=<?php echo (isset($search) ? urlencode($search) : ""); ?>&restypes=<?php echo (isset($restypes) ? urlencode($restypes) : "") ?>&archive=<?php echo (isset($archive) ? urlencode($archive) : "" ) ?>&daylimit=<?php echo (isset($daylimit) ? urlencode($daylimit) : "" ) ?>&offset=<?php echo (isset($offset) ? urlencode($offset) : "" );echo (isset($resources_count) ? "&resources_count=$resources_count" :""); ?>');
				InitThumbs();
			}
	</script>
	<?php } // end omit_collectiondiv_load_pages 
	else {?><div class="ui-layout-south" ></div><script>myLayout=jQuery('body').layout({south__initHidden: true });	</script><?php }
	}
	?>


<?php hook("afteruilayout");?>
<?php hook("responsivescripts"); ?>


<!-- Start of modal support -->
<div id="modal_overlay" onClick="ModalClose();"></div>
<div id="modal_outer">
<div id="modal">
</div>
</div>
<div id="modal_dialog" style="display:none;"></div>
<script type="text/javascript">
jQuery(window).bind('resize.modal', ModalCentre);
</script>
<!-- End of modal support -->

<script type="text/javascript">

try{
	top.history.replaceState(document.title+'&&&'+jQuery('#CentralSpace').html(), applicationname);
	}
 catch(e){console.log(e);
	 console.log("failed to load state");
	}

</script>

<?php if ($chosen_dropdowns) { ?>
<!-- Chosen support -->
<script src="<?php echo $baseurl_short ?>lib/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $baseurl_short ?>lib/chosen/chosen.min.css">
<script type="text/javascript">
  var chosen_config = {
    "#CentralSpace select"           : {disable_search_threshold:10},
    "#SearchBox select"           : {disable_search_threshold:10}
  }
  for (var selector in chosen_config) {
    jQuery(selector).chosen(chosen_config[selector]);
  }
</script>
<!-- End of chosen support -->
<?php } ?>

</body>
</html>
<?php } // end if !ajax ?>
