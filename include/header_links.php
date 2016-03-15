		<?php if (!hook("replaceheadernav2")) { ?>
		<ul>
		<?php if (!hook("replacehomelinknav")) { ?>
		<?php if (!$use_theme_as_home && !$use_recent_as_home) { ?><li><a href="<?php echo $baseurl?>/pages/<?php echo $default_home_page?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["home"]?></a></li> <?php } 
		 }  
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
		<?php if (!hook("replacethemelink")) { ?>
		<?php if (checkperm("s") && $enable_themes && !$theme_direct_jump && $themes_navlink) { ?><li><a href="<?php echo $baseurl?>/pages/themes.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["themes"]?></a></li><?php } ?>
		<?php } /* end hook replacethemelink */?>
		<?php if (checkperm("s") && ($public_collections_top_nav || $public_collections_header_only)) { ?><li><a href="<?php echo $baseurl?>/pages/collection_public.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["publiccollections"]?></a></li><?php } ?>
		<?php if (checkperm("s") && $mycollections_link && !checkperm("b")) { ?><li><a href="<?php echo $baseurl?>/pages/collection_manage.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycollections"]?></a></li><?php } ?>
		<?php if (!hook("replacerecentlink")) { ?>
		<?php if (checkperm("s") && $recent_link) { ?><li><a href="<?php echo $baseurl?>/pages/search.php?search=<?php if ($recent_search_by_days) {echo "&amp;recentdaylimit=" . $recent_search_by_days_default . "&amp;order_by=resourceid&amp;sort=desc";} else {echo urlencode("!last".$recent_search_quantity);}?>&order_by=resourceid" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["recent"]?></a></li><?php } ?>
		<?php } /* end hook replacerecentlink */?>
		<?php if (checkperm("s") && $myrequests_link && checkperm("q")) { ?><li><a href="<?php echo $baseurl?>/pages/requests.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["myrequests"]?></a></li><?php } ?>
		<?php if (!hook("replacemycontributionslink")) { ?>
		<?php if ((checkperm("d") && $mycontributions_userlink)||($mycontributions_link && checkperm("c"))) { ?><li><a href="<?php echo $baseurl?>/pages/contribute.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["mycontributions"]?></a></li><?php } ?>
		<?php } /* end hook replacemycontributionslink */?>
		<?php if (!hook("replaceresearchrequestlink")) { ?>
		<?php if (($research_request) && ($research_link) && (checkperm("s")) && (checkperm("q"))) { ?><li><a href="<?php echo $baseurl?>/pages/research_request.php" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["researchrequest"]?></a></li><?php } ?>
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
		
		
		<?php if ($help_link) { ?><li><a href="<?php echo $baseurl?>/pages/help.php" onClick="return <?php if (!$help_modal) { ?>CentralSpaceLoad(this,true);<?php } else { ?>ModalLoad(this,true);<?php } ?>"><?php echo $lang["helpandadvice"]?></a></li><?php } ?>
		<?php global $nav2contact_link; if ($nav2contact_link) { ?><li><a href="<?php echo $baseurl?>/pages/contact.php"  onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["contactus"]?></a></li><?php } ?>
		<?php if (($top_nav_upload && checkperm("c")) || ($top_nav_upload_user && checkperm("d"))) { ?><li><a href="<?php echo $baseurl?>/pages/edit.php?ref=-<?php echo @$userref?>&amp;uploader=<?php echo $top_nav_upload_type ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo $lang["upload"]?></a></li><?php } ?>

<?php hook("toptoolbaradder"); ?>
		</ul>
<?php } /* end replaceheadernav1 */ ?>
		
