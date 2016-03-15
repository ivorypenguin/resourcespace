<?php 
if (!hook("replacelistitem")) 
	{ ?>
	<!--List Item-->
	<tr id="ResourceShell<?php echo htmlspecialchars($ref)?>" <?php hook("listviewrowstyle");?>>
	<?php 
	if(!hook("listcheckboxes"))
		{
		if ($use_checkboxes_for_selection)
			{?>
			<td width="30px">
				<input 
					type="checkbox" 
					style="position:relative;margin-bottom:-4px;top:-3px;height:21px;" 
					id="check<?php echo htmlspecialchars($ref)?>" 
					class="checkselect" 
					<?php 
					if (in_array($ref,$collectionresources))
						{ ?>checked<?php } ?> 
					onclick="if (jQuery('#check<?php echo htmlspecialchars($ref)?>').attr('checked')=='checked'){ AddResourceToCollection(event,<?php echo htmlspecialchars($ref)?>); } else if (jQuery('#check<?php echo htmlspecialchars($ref)?>').attr('checked')!='checked') { RemoveResourceFromCollection(event,<?php echo htmlspecialchars($ref)?>); <?php if (isset($collection)){?>document.location.href='?search=<?php echo urlencode($search)?>&order_by=<?php echo urlencode($order_by)?>&archive=<?php echo urlencode($archive)?>&offset=<?php echo urlencode($offset)?>';<?php } ?> }"
				>
			</td>
			<?php 
			} ?>
		<?php 
		} #end hook listcheckboxes 
		for ($x=0;$x<count($df);$x++)
			{
			if(!in_array($df[$x]['ref'],$list_display_fields))
				{continue;}

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
			if ( (isset($metadata_template_title_field)&& $df[$x]['ref']!=$metadata_template_title_field ) || !isset($metadata_template_title_field) ) 
				{
				if (!hook("replacelisttitle")) 
					{ ?>
					<td 
						nowrap 
						<?php 
						hook("listviewcolumnstyle");?>
					>
						<?php 
						if ($x==0)
							{ // add link to first item only ?>
							<div class="ListTitle">
								<a href="<?php echo $url?>" 
									onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);"
								>
							<?php 
							} //end link conditional
						echo highlightkeywords(tidy_trim(TidyList(i18n_get_translated($value)),$results_title_trim),$search,$df[$x]['partial_index'],$df[$x]['name'],$df[$x]['indexed']);
						if ($x==0)
							{ // add link to first item only ?>
							</a>
							<?php 
							} //end link conditional ?>
						</div>
					</td>
				<?php } 
				} //end replace list title
			}

		hook("beforesearchstars");
		if ($display_user_rating_stars && $k=="")
			{ 
			if (!hook("replacesearchstars"))
				{ ?>
				<td <?php hook("listviewcolumnstyle");?> >
					<?php 
					if ($result[$n]['user_rating']=="") 
						{$result[$n]['user_rating']=0;}

					$modified_user_rating=hook("modifyuserrating");
					if ($modified_user_rating!='')
						{$result[$n]['user_rating']=$modified_user_rating;}
					?>
					<div  
						class="RatingStars" 
						style="text-align:left;margin:0px;" 
						onMouseOut="UserRatingDisplay(<?php echo $result[$n]['ref']?>,<?php echo $result[$n]['user_rating']?>,'StarCurrent');"
					>
						<?php 
						for ($z=1;$z<=5;$z++)
							{ ?>
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
				</td>
				<?php 
				} // end hook replacesearchstars
			}
		
		if (isset($rating_field))
			{ ?>
			<td <?php hook("listviewcolumnstyle");?> >
				<?php 
				if (isset($result[$n][$rating])&& $result[$n][$rating]>0) 
					{ 
					for ($y=0;$y<$result[$n][$rating];$y++)
						{ ?> 
						<div class="IconStar"></div><?php 
						}
					} 
				else 
					{ ?>
					&nbsp;
					<?php 
					} 
			?>
			</td>
			<?php 
			}

		if ($id_column)
			{ ?>
			<td <?php hook("listviewcolumnstyle");?> >
				<?php echo $result[$n]["ref"]?>
			</td>
			<?php
			}
		
		if ($resource_type_column)
			{ ?>
			<td <?php hook("listviewcolumnstyle");?>>
				<?php 
				if (array_key_exists($result[$n]["resource_type"],$rtypes)) 
					{ 
					echo $rtypes[$result[$n]["resource_type"]];
					} 
			?>
			</td>
			<?php 
			}

		if ($list_view_status_column)
			{ ?>
			<td <?php hook("listviewcolumnstyle");?> >
				<?php 
				echo $lang["status" . $result[$n]["archive"]];
				?>
			</td>
			<?php 
			}
		
		if ($date_column)
			{ ?>
		 	<td <?php hook("listviewcolumnstyle");?> >
		 		<?php 
		 		echo nicedate($result[$n]["creation_date"],false,true);
		 		?>
		 	</td>
		 	<?php 
		 	}
		
		hook("addlistviewcolumn");
		?>
		<td <?php hook("listviewcolumnstyle");?> >
			<div class="ListTools">
			<?php
			if($search_results_edit_icon && checkperm("e" . $result[$n]["archive"]) && !hook("iconedit")) 
				{ 
				if ($allow_share && $k=="") 
					{ ?>
						<a 
							href="<?php echo str_replace("view.php","edit.php",$url) ?>"  
							onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this,true);" 
							title="<?php echo $lang["editresource"]?>"
						>&gt;&nbsp;<?php echo $lang["action-edit"] ?>
						</a>&nbsp;
					<?php
					$showkeyedit = true;
					}
				} 				
				?>
				<a 
					onClick="return <?php echo ($resource_view_modal?"Modal":"CentralSpace") ?>Load(this);" 
					href="<?php echo $url?>"
				>
					&gt;&nbsp;
					<?php echo $lang["action-view"]?>
				</a> 
				&nbsp;
				<?php
				if (!hook("replacelistviewaddtocollectionlink"))
					{
					if (!checkperm("b")&& $k=="") 
						{ 
						echo add_to_collection_link($ref,$search);
						?>
							&gt;&nbsp;
							<?php echo $lang["action-addtocollection"]?>
						</a> 
						&nbsp;
						<?php 
						}
					}
				if (!hook('replacelistviewemaillink') && $allow_share && $k=="") 
					{ ?>
					<a 
						class="nowrap" 
						onClick="return CentralSpaceLoad(this);" 
						href="<?php echo $baseurl_short?>pages/resource_share.php?ref=<?php echo htmlspecialchars($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&amp;k=<?php echo urlencode($k)?>"
					>
						&gt;&nbsp;
						<?php echo $lang["share"]?>
					</a>
					<?php 
					} ?>
			</div>
		</td>
	</tr>
	<!--end hook replacelistitem--> 
	<?php
	}
