<?php

if ((substr($search,0,11)!="!collection")&&($collections!="")&&is_array($collections)) {
    
for ($n=0;$n<count($collections);$n++)
	{
	$resources=do_search("!collection".$collections[$n]['ref'],"","relevance","",5);	
	$hook_result=hook("process_search_results","",array("result"=>$resources,"search"=>"!collection".$collections[$n]['ref']));
	if ($hook_result!==false) {$resources=$hook_result;}
	
	$pub_url="search.php?search=" . urlencode("!collection" . $collections[$n]["ref"]);
	if ($display=="thumbs")
		{
		?>
		<div class="ResourcePanelShell" id="ResourceShell">
		<div class="ResourcePanel">
	
		<table  border="0" class="ResourceAlign"><?php hook("publicresulttop")?><tr><td>
		
		<div style="position: relative;height:140px;">
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo htmlspecialchars(str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))) ?>">
		
		<?php 
		$images=0;
		for ($m=0;$m<count($resources) && $images<=4;$m++)
            {
            $border=true;    
			$ref=$resources[$m]['ref'];
            if ($resources[$m]['has_image']){
                $previewpath=get_resource_path($ref,false,"col",false,"jpg",-1,1,false,$resources[$m]["file_modified"]);
            }
            else {
                $previewpath="../gfx/".get_nopreview_icon($resources[$m]["resource_type"],$resources[$m]["file_extension"],"col");$border=false;
            }
            $modifiedurl=hook('searchpublicmodifyurl');
			if ($modifiedurl){ $previewpath=$modifiedurl;$border=true;}
            $images++;
            $space=3+($images-1)*18;
            ?>
            <img style="position: absolute; top:<?php echo $space ?>px;left:<?php echo $space ?>px" src="<?php echo $previewpath?>" <?php if ($border){?>class="ImageBorder"<?php } ?>>
            <?php				
			}
		?>
		</a>
		</div>
		</td>
		</tr></table>
        <?php // for spacing
        if ($display_user_rating_stars && $k==""){ ?>
            <div  class="RatingStars">&nbsp;</div>
        <?php } ?>
        <?php hook("icons","search",array("collections"=>true)); //for spacing ?>
        <?php //add spacing for display fields to even out the box size
        for ($x=0;$x<count($df);$x++){
            ?>
            <?php if (!hook("replaceresourcepanelinfopublicsearch")){?>
            <div class="ResourcePanelInfo">
            <?php if (in_array($df[$x]['ref'],$thumbs_display_extended_fields)){
                ?><div class="extended">
            <?php } ?>
            <?php if ($x==count($df)-1){?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo htmlspecialchars(str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))) ?>"><?php echo highlightkeywords(tidy_trim(i18n_get_collection_name($collections[$n]),32),$search)?></a><?php } ?>&nbsp;<?php if (in_array($df[$x]['ref'],$thumbs_display_extended_fields)){ ?></div>
            <?php }
        ?></div><?php } ?>
        <?php } ?>
        <?php if (!hook("replacecollectiontools")){?>
        <div class="ResourcePanelCountry" style="float:right;"><a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $collections[$n]["ref"]?>" onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a>&nbsp;&nbsp;&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a></div>		
        <?php } ?>
		
		<div class="clearer"></div>
		</div>
		<div class="PanelShadow"></div>
		</div>
	<?php } 
	
	
	
		if ($display=="xlthumbs")
		{
		?>
		<div class="ResourcePanelShellLarge" id="ResourceShell">
		<div class="ResourcePanelLarge">
	
		<table  border="0" class="ResourceAlignLarge"><?php hook("publicresulttop")?><tr><td>
		
		<div style="position: relative;height:330px;">
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))?>">

		<?php 
		$images=0;
		for ($m=0;$m<count($resources) && $images<=4;$m++)
            {
            $border=true;    
            $ref=$resources[$m]['ref'];
            $previewpath=get_resource_path($ref, true, "thm", false, "jpg", -1, 1, false);
            if (file_exists($previewpath)){
                $previewpath=get_resource_path($ref,false,"thm",false,"jpg",-1,1,false,$resources[$m]["file_modified"]);
            }
            else {
                $previewpath="../gfx/".get_nopreview_icon($resources[$m]["resource_type"],$resources[$m]["file_extension"],"");$border=false;
            }
            $images++;
            $space=3+($images-1)*45;
            ?>
            <img style="position: absolute; top:<?php echo $space ?>px;left:<?php echo $space ?>px" src="<?php echo $previewpath?>" <?php if ($border){?>class="ImageBorder"<?php } ?>>
            <?php				
			}
		?>
		</a>
		</div>
		</td>
		</tr></table>
        <?php // for spacing
        if ($display_user_rating_stars && $k==""){ ?>
            <div  class="RatingStars">&nbsp;</div>
        <?php } ?>
        <?php hook("icons","search",array("collections"=>true)); //for spacing ?>
        <?php //add spacing for display fields to even out the box size
        for ($x=0;$x<count($df);$x++){
            if (!hook("replaceresourcepanelinfolargepublicsearch")){
            ?></count><div class="ResourcePanelInfo">
            <?php if (in_array($df[$x]['ref'],$xl_thumbs_display_extended_fields)){
                ?><div class="extended">
            <?php } ?>
            <?php if ($x==count($df)-1){?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))?>"><?php echo highlightkeywords(tidy_trim(i18n_get_collection_name($collections[$n]),32),$search)?></a><?php } ?>&nbsp;<?php if (in_array($df[$x]['ref'],$xl_thumbs_display_extended_fields)){ ?></div>
            <?php }
        ?></div><?php } ?>
        <?php } ?>
        <div>
        <?php if (!hook("replacecollectiontools")){?>
		<div class="ResourcePanelCountry" style="float:right;"><a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $collections[$n]["ref"]?>"  onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a>&nbsp;&nbsp;&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a>
		<?php } ?>
		</div>		
		  <?php hook("xlargesearchiconpublic");?></div>
		<div class="clearer"></div>
		</div>
		<div class="PanelShadow"></div>
		</div>
	<?php } 
	
	
	
	
	if ($display=="smallthumbs")
		{
		?>
		<div class="ResourcePanelShellSmall" id="ResourceShell">
		<div class="ResourcePanelSmall">
	
		<table  border="0" class="ResourceAlignSmall"><?php hook("publicresulttop")?><tr><td>
		
		<div style="position: relative;height:70px;">
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))?>">



        
		<?php 
		$images=0;

		for ($m=0;$m<count($resources) && $images<=4;$m++)
            {
            $border=true;    
            $ref=$resources[$m]['ref'];
            $previewpath=get_resource_path($ref, true, "col", false, "jpg", -1, 1, false);
            if (file_exists($previewpath)){
                $previewpath=get_resource_path($ref,false,"col",false,"jpg",-1,1,false,$resources[$m]["file_modified"]);
            }
            else {
                $previewpath="../gfx/".get_nopreview_icon($resources[$m]["resource_type"],$resources[$m]["file_extension"],"col");$border=false;
            }
            $images++;
            $space=3+($images-1)*9;
            if (list($sw,$sh) = @getimagesize($previewpath)){
            ?>
            <img width="<?php echo floor($sw/2)?>" height="<?php echo floor($sh/2)?>" style="position: absolute; top:<?php echo $space ?>px;left:<?php echo $space ?>px" src="<?php echo $previewpath?>" <?php if ($border){?>class="ImageBorder"<?php } ?>>
            <?php
            }
			}
		?>
		</a>
		</div>
		</td>
		</tr></table>	
        <?php // for spacing
        if ($display_user_rating_stars && $k==""){ ?><div  class="RatingStars">&nbsp;&nbsp;</div>
        <?php } ?>
        <?php hook("icons","search",array("collections"=>true)); //for spacing ?>
        <?php //add spacing for display fields to even out the box size
        for ($x=0;$x<count($df);$x++){
            ?>
            <?php if (!hook("replaceresourcepanelinfosmallpublicsearch")){?>
            <div class="ResourcePanelInfo">
            
            <?php if (in_array($df[$x]['ref'],$small_thumbs_display_extended_fields)){
                ?><div class="extended"><?php } ?><?php if ($x==count($df)-1){?><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"",i18n_get_collection_name($collections[$n]))?>"><?php echo highlightkeywords(tidy_trim(i18n_get_collection_name($collections[$n]),32),$search)?></a><?php } ?>&nbsp;<?php if (in_array($df[$x]['ref'],$small_thumbs_display_extended_fields)){ ?></div>
            <?php }
        ?></div><?php } ?>
        <?php } ?>
        <?php if (!hook("replacecollectiontools")){?>
        <div class="ResourcePanelCountry" style="font-size:9px;"><a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $collections[$n]["ref"]?>"  onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a>&nbsp;&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a></div><div class="clearer"></div>
        <?php } ?>
		</div>
		<div class="PanelShadow"></div>
		</div>
	<?php } 
	
	if ($display=="list")
		{
		?>
		<tr <?php hook("collectionlistrowstyle");?>>
		<?php hook ("listsearchpubliccheckboxes");
		if ($use_checkboxes_for_selection){echo "<td></td>";}
		if (!isset($collections[$n]['savedsearch'])||(isset($collections[$n]['savedsearch'])&&$collections[$n]['savedsearch']==null))
			{
			$collection_prefix = $lang["collection"] . ": ";
			$collection_tag = $lang['collection'];
			}
		else
			{
			$collection_prefix = ""; # The prefix $lang['smartcollection'] . ": " is added in i18n_get_collection_name()
			$collection_tag = $lang['smartcollection'];
			}
		if(!hook("replacelistviewcolresults")){?>
		<td nowrap><div class="ListTitle"><a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>" title="<?php echo str_replace(array("\"","'"),"", $collection_prefix . i18n_get_collection_name($collections[$n]))?>"><?php echo $collection_prefix . highlightkeywords(tidy_trim(i18n_get_collection_name($collections[$n]),45),$search)?></a></div></td>
		<?php 
		for ($x=0;$x<count($df)-1;$x++){
			?><td>-</td><?php
			}
				
		?>
		<td>-</td>
	    <?php if ($display_user_rating_stars && $k==""){ ?><td>&nbsp;&nbsp;</td><?php } ?>
		<?php if ($id_column){?><td><?php echo $collections[$n]['ref']?></td><?php } ?>
		<?php if ($resource_type_column){?><td><?php echo $collection_tag?></td><?php } ?>
		<?php if ($date_column){?><td><?php echo nicedate($collections[$n]["created"],false,true)?></td><?php } ?>
        <?php hook("addlistviewcolumnpublic");?>
		<td><div class="ListTools">
		<?php if (!hook("replacecollectiontools")){?>
		<a href="<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo $collections[$n]["ref"]?>"  onClick="return CollectionDivLoad(this);">&gt;&nbsp;<?php echo $lang["action-select"]?></a>&nbsp;&nbsp;<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $pub_url?>">&gt;&nbsp;<?php echo $lang["viewall"]?></a>
		<?php } ?>
		</div></td>
		<?php } ?>
		</tr>
	<?php } ?>		
	
<?php } ?>
<?php } /* end if not a collection search */ ?>
