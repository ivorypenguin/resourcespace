<?php 
if (!hook("renderresultthumb")) 
	{ ?>
	<!--Resource Panel-->
	<div class="ResourcePanelShell" id="ResourceShell<?php echo htmlspecialchars($ref)?>">
		<div class="ResourcePanel  <?php hook("thumbsviewpanelstyle");?>">
		<?php  
		if ($resource_type_icons) 
			{ ?>
	 		<div class="ResourceTypeIcon IconResourceType<?php echo $result[$n]["resource_type"];  ?>"></div>
			<?php 
			}
		hook ("resourcethumbtop");
		if (!hook("renderimagethumb")) 
			{
			# Work out image to use.
			if(isset($watermark))
				{
				$use_watermark=check_use_watermark();
				}
			else
				{
				$use_watermark=false;	
				}
			$thm_url=get_resource_path($ref,false,"thm",false,$result[$n]["preview_extension"],-1,1,$use_watermark,$result[$n]["file_modified"]);
			if (isset($result[$n]["thm_url"])) {$thm_url=$result[$n]["thm_url"];} # Option to override thumbnail image in results, e.g. by plugin using process_Search_results hook above
			?>
			<table border="0" class="ResourceAlign icon_type_<?php echo $result[$n]["resource_type"]; ?> icon_extension_<?php echo $result[$n]['file_extension']; ?><?php if(!hook("replaceresourcetypeicon")){ if (in_array($result[$n]["resource_type"],$videotypes)) { ?> IconVideo<?php } ?><?php } hook('searchdecorateresourcetableclass'); ?>">
			<?php hook("resourcetop")?>
			<tr>
			<td>
				<!-- new code start -->
				<?php
				$show_flv=false;
				$use_mp3_player=false;
				if((in_array($result[$n]["file_extension"],$ffmpeg_supported_extensions) || $result[$n]["file_extension"]=="flv") && $video_player_thumbs_view){ 
					$flvfile=get_resource_path($ref,true,"pre",false,$ffmpeg_preview_extension);
					if (!file_exists($flvfile)){
						$flvfile=get_resource_path($ref,true,"",false,$ffmpeg_preview_extension);
					}
					elseif(!(isset($result[$n]['is_transcoding']) && $result[$n]['is_transcoding']!=0) && file_exists($flvfile) && (strpos(strtolower($flvfile),".".$ffmpeg_preview_extension)!==false)){
						$show_flv=true;
					}
				}
				else
					{
					// Set $use_mp3_player switch if appropriate
					$use_mp3_player = ($mp3_player_thumbs_view && !(isset($result[$n]['is_transcoding']) && $result[$n]['is_transcoding']==1) && ((in_array($result[$n]["file_extension"],$ffmpeg_audio_extensions) || $result[$n]["file_extension"]=="mp3") && $mp3_player));
					if ($use_mp3_player)
						{
						$mp3realpath=get_resource_path($ref,true,"",false,"mp3");
						if (file_exists($mp3realpath))
							{$mp3path=get_resource_path($ref,false,"",false,"mp3");}
						}
					}
				if(isset($flvfile) && hook("replacevideoplayerlogic","",array($flvfile,$result,$n))){
				
				}
    			elseif($show_flv){
					# Include the Flash player if an FLV file exists for this resource.
					if(!hook("customflvplay")){
						include "video_player.php";
					}
				}
				elseif ($use_mp3_player && file_exists($mp3realpath) && !hook("custommp3player"))
					{
					$thumb_path=get_resource_path($ref,true,"pre",false,"jpg");
					if(file_exists($thumb_path))
						{$thumb_url=get_resource_path($ref,false,"pre",false,"jpg"); }
					else
						{$thumb_url=$baseurl_short . "gfx/" . get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],false);}

					include "mp3_play.php";
					}
				else{?><!-- new code end -->
				<div id="triangle" style="border-color: transparent transparent rgb(7, 101, 145); border-width: 0px 0px 200px 200px;"></div>
				<a 
					style="position:relative;" 
					href="<?php echo $url?>"  
					onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" 
					title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field])))?>"
				>
					<?php 
					if ($result[$n]["has_image"]==1) 
						{ ?>
						<img 
							<?php if ($result[$n]["thumb_width"]!="" && $result[$n]["thumb_width"]!=0 && $result[$n]["thumb_height"]!="") 
								{ ?> 
								width="<?php echo $result[$n]["thumb_width"]?>" 
								height="<?php echo $result[$n]["thumb_height"]?>" 
								<?php 
								} ?> 
							src="<?php echo $thm_url ?>" 
							class="ImageBorder" 
							alt="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($result[$n]["field".$view_title_field]))); ?>"
						/>
						<?php 
						} 
					else 
						{ ?>
						<img 
							border=0 
							src="<?php echo $baseurl_short?>gfx/<?php echo get_nopreview_icon($result[$n]["resource_type"],$result[$n]["file_extension"],false) ?>" 

						/>
						<?php 
						}
					hook("aftersearchimg","",array($result[$n]))?>
				</a>
				<!-- new code start -->
				<?php } ?>
				<!-- new code end -->
			</td>
			</tr>
			</table>
			<?php 
			} ?> 
		<!-- END HOOK Renderimagethumb-->
		<?php 
		hook("beforesearchstars");
		if ($display_user_rating_stars && ($k=="" || $internal_share_access))
			{ 
			if (!hook("replacesearchstars"))
				{
				if ($result[$n]['user_rating']=="") {$result[$n]['user_rating']=0;}
				$modified_user_rating=hook("modifyuserrating");
				if ($modified_user_rating!=''){$result[$n]['user_rating']=$modified_user_rating;}
				?>
				<div  class="RatingStars" onMouseOut="UserRatingDisplay(<?php echo $result[$n]['ref']?>,<?php echo $result[$n]['user_rating']?>,'StarCurrent');">&nbsp;<?php 
				for ($z=1;$z<=5;$z++)
					{
					?>
					<a 
						href="#" 
						onMouseOver="UserRatingDisplay(<?php echo $result[$n]['ref']?>,<?php echo $z?>,'StarSelect');" 
						onClick="UserRatingSet(<?php echo $userref?>,<?php echo $result[$n]['ref']?>,<?php echo $z?>);return false;" 
						id="RatingStarLink<?php echo $result[$n]['ref'].'-'.$z?>"
					>
						<span 
							id="RatingStar<?php echo $result[$n]['ref'].'-'.$z?>" 
							class="Star<?php echo ($z<=$result[$n]['user_rating']?"Current":"Empty")?>"
						>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</span>
					</a>
					<?php
					}
				?>
				</div>
				<?php 
				} // end hook replacesearchstars
			}
		if (!hook("replaceicons")) 
			{
			hook("icons");
			} //end hook replaceicons
		if (!hook("rendertitlethumb")) {} ?> <!-- END HOOK Rendertitlethumb -->			
		<?php
		$df_alt=hook("displayfieldsalt");
		$df_normal=$df;
		if ($df_alt){$df=$df_alt;}
		# thumbs_display_fields
		for ($x=0;$x<count($df);$x++)
			{
			if(!in_array($df[$x]['ref'],$thumbs_display_fields))
				{continue;}
			
			#value filter plugin -tbd	
			$value=@$result[$n]['field'.$df[$x]['ref']];
			$plugin="../plugins/value_filter_" . $df[$x]['name'] . ".php";
			if ($df[$x]['value_filter']!="")
				{eval($df[$x]['value_filter']);}
			else if (file_exists($plugin)) 
				{include $plugin;}

			# swap title fields if necessary
			if (isset($metadata_template_resource_type) && isset ($metadata_template_title_field))
				{
				if (($df[$x]['ref']==$view_title_field) && ($result[$n]['resource_type']==$metadata_template_resource_type))
					{
					$value=$result[$n]['field'.$metadata_template_title_field];
					}
				}

			// extended css behavior 
			if (in_array($df[$x]['ref'],$thumbs_display_extended_fields) &&
			((isset($metadata_template_title_field) && $df[$x]['ref']!=$metadata_template_title_field) || !isset($metadata_template_title_field)))
				{
				if (!hook("replaceresourcepanelinfo"))
					{ ?>
					<div class="ResourcePanelInfo ResourceTypeField<?php echo $df[$x]['ref']?>">
						<div class="extended">
						<?php 
						if ($x==0)
							{ // add link if necessary ?>
							<a 
								href="<?php echo $url?>"  
								onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" 
								title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($value)))?>"
							>
							<?php 
							} //end link
						echo format_display_field($value);
						if ($show_extension_in_search) 
							{ 
							echo " " . str_replace_formatted_placeholder("%extension", $result[$n]["file_extension"], $lang["fileextension-inside-brackets"]);
							}
						if ($x==0)
							{ // add link if necessary ?>
							</a>
							<?php 
							} //end link?> 
						&nbsp;
						</div>
					</div>
					<?php 
					} /* end hook replaceresourcepanelinfo */ ?>
				<?php 
				// normal behavior
				} 
			else if  ((isset($metadata_template_title_field)&&$df[$x]['ref']!=$metadata_template_title_field) || !isset($metadata_template_title_field) ) 
				{
				if (!hook("replaceresourcepanelinfonormal"))
					{ ?>
					<div class="ResourcePanelInfo  ResourceTypeField<?php echo $df[$x]['ref']?>">
						<?php 
						if ($x==0)
							{ // add link if necessary ?>
							<a 
								href="<?php echo $url?>"  
								onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" 
								title="<?php echo str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($value)))?>"

							>
							<?php 
							} //end link
						echo highlightkeywords(tidy_trim(TidyList(i18n_get_translated($value)),$search_results_title_trim),$search,$df[$x]['partial_index'],$df[$x]['name'],$df[$x]['indexed']);
						if ($x==0)
							{ // add link if necessary ?>
							</a>
							<?php 
							} //end link ?>
						&nbsp;
					</div>
					<div class="clearer"></div>
					<?php 
					}
				} /* end hook replaceresourcepanelinfonormal */
				hook("processthumbsfields");
			}
		$df=$df_normal;
		?>
		<!-- Checkboxes -->
		<div class="ResourcePanelIcons">
			<?php
			if(!hook("thumbscheckboxes"))
			{
			if ($use_checkboxes_for_selection)
				{ ?>
				<input 
					type="checkbox" 
					id="check<?php echo htmlspecialchars($ref)?>" 
					class="checkselect" 
					<?php 
					if (in_array($ref,$collectionresources))
						{ ?>
						checked
						<?php 
						} ?> 
					onclick="if (jQuery('#check<?php echo htmlspecialchars($ref)?>').attr('checked')=='checked'){ AddResourceToCollection(event,<?php echo htmlspecialchars($ref)?>); } else if (jQuery('#check<?php echo htmlspecialchars($ref)?>').attr('checked')!='checked'){ RemoveResourceFromCollection(event,<?php echo htmlspecialchars($ref)?>); }"
				>
				&nbsp;
				<?php 
				}
			} # end hook thumbscheckboxes
		if(!hook("replacethumbsidinthumbnail"))
			{
			if ($display_resource_id_in_thumbnail && $ref>0) 
				{ echo htmlspecialchars($ref); } 
			else 
				{ ?>&nbsp;<?php }
			} # end hook("replacethumbsidinthumbnail")

		if (!hook("replaceresourcetools"))
			{ ?>
			<!-- Preview icon -->
			<?php 
			if (!hook("replacefullscreenpreviewicon"))
				{
				if ($result[$n]["has_image"]==1)
					{ ?>
					<span class="IconPreview">
						<a 
							onClick="return CentralSpaceLoad(this,true);"
							href="<?php echo $baseurl_short?>pages/preview.php?from=search&amp;ref=<?php echo urlencode($ref)?>&amp;ext=<?php echo $result[$n]["preview_extension"]?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&amp;k=<?php echo urlencode($k)?>" 
							title="<?php echo $lang["fullscreenpreview"]?>"
						>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="<?php echo $lang["fullscreenpreview"]?>" 
								width="22" 
								height="12" 
							/>
						</a>
					</span>
					<?php 
					$showkeypreview = true;
					}
				} /* end hook replacefullscreenpreviewicon */?>

			<!-- Add to collection icon -->
			<?php
			if(!hook("iconcollect"))
				{
				if (!checkperm("b") && ($k=="" || $internal_share_access)  && !$use_checkboxes_for_selection) 
					{ ?>
					<span class="IconCollect">
						<?php echo add_to_collection_link($ref,$search)?>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="" 
								width="22" 
								height="12"
							/>
						</a>
					</span>
					<?php
					$showkeycollect = true;
					}
				} # end hook iconcollect ?>
			<!-- Remove from collection icon -->
			<?php 
			if (!checkperm("b") && substr($search,0,11)=="!collection" && ($k=="" || $internal_share_access) && !$use_checkboxes_for_selection) 
				{
				if ($search=="!collection".$usercollection)
					{ ?>
					<span class="IconCollectOut">
						<?php echo remove_from_collection_link($ref,$search)?>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="" 
								width="22" 
								height="12" 
							/>
						</a>
					</span>
					<?php 
					$showkeycollectout = true;
					}
				} ?>
			<!-- Email icon -->
			<?php 
			if(!hook("iconemail")) 
				{ 
				if ($allow_share && ($k=="" || $internal_share_access)) 
					{ ?>
					<span class="IconEmail">
						<a 
							href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&amp;k=<?php echo urlencode($k)?>"  
							onClick="return CentralSpaceLoad(this,true);" 
							title="<?php echo $lang["share-resource"]?>"
						>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="" 
								width="16" 
								height="12" 
							/>
						</a>
					</span>
					<?php 
					$showkeyemail = true;
					}
				} ?>
			<!-- Edit icon -->
			<?php
			// The permissions check here is intentionally more basic. It doesn't check edit_filter as this would be computationally intensive
			// when displaying many resources. As such this is a convenience feature for users that have system-wide edit access to the given
			// access level.
			if($search_results_edit_icon && checkperm("e" . $result[$n]["archive"]) && !hook("iconedit")) 
				{ 
				if ($allow_share && ($k=="" || $internal_share_access)) 
					{ ?>
					<span class="IconEdit">
						<a 
							href="<?php echo str_replace("view.php","edit.php",$url) ?>"  
							onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" 
							title="<?php echo $lang["editresource"]?>"
						>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="" 
								width="16" 
								height="12" 
							/>
						</a>
					</span>
					<?php
					$showkeyedit = true;
					}
				} ?>	
				
				
			<!-- Star icon -->
			<?php 
			if (isset($result[$n][$rating]) && $result[$n][$rating]>0) 
				{ ?>
				<div class="IconStar"></div>
				<?php $showkeystar = true; 
				} ?>
			<!-- Collection comment icon -->
			<?php 
			if($k=="" || $internal_share_access)
				{
				if (($collection_reorder_caption || $collection_commenting) && (substr($search,0,11)=="!collection")) 
					{ ?>
					<span class="IconComment">
						<a 
							href="<?php echo $baseurl_short?>pages/collection_comment.php?ref=<?php echo urlencode($ref)?>&collection=<?php echo urlencode(substr($search,11))?>"  
							onClick="return CentralSpaceLoad(this,true);" 
							title="<?php echo $lang["addorviewcomments"]?>"
						>
							<img 
								src="<?php echo $baseurl_short?>gfx/interface/sp.gif" 
								alt="" 
								width="14" 
								height="12" 
							/>
						</a>
					</span>
					<?php 
					$showkeycomment = true;
					} 
				} 
			hook("largesearchicon");
			?>
			<div class="clearer"></div>
			<?php 
			} // end hook replaceresourcetools ?>
        </div>	
	</div>
	<div class="PanelShadow"></div>
	</div>
	<?php 
	} # end hook renderresultthumb


