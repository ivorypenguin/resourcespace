<?php 

function HookAnnotateDatabase_pruneDbprune(){
	sql_query("delete from resource_keyword where annotation_ref > 0 and annotation_ref not in (select note_id from annotate_notes)");
	echo sql_affected_rows() . " orphaned annotation resource-keyword relationships deleted.<br/><br/>";
}

function HookAnnotateAllAfterreindexresource($ref){
	// make sure annotation indexing isn't lost when doing a reindex.
	$notes=sql_query("select * from annotate_notes where ref='$ref'");
	global $pagename;

	foreach($notes as $note){
		#Add annotation to keywords
		$keywordtext = substr(strstr($note['note'],": "),2); # don't add the username to the keywords

		add_keyword_mappings($ref,i18n_get_indexable($keywordtext),-1,false,false,"annotation_ref",$note['note_id']);
	}
}

function HookAnnotateAllModifyselect(){
return (" ,r.annotation_count ");

}

function HookAnnotateAllRemoveannotations(){
	global $ref;
	
	sql_query("delete from annotate_notes where ref='$ref'");
	sql_query("update resource set annotation_count=0 where ref='$ref'");	
	sql_query("delete from resource_keyword where resource='$ref' and annotation_ref>0");;
}

function HookAnnotateAllCollectiontoolcompact1($collection, $count_result,$cinfo,$colresult){
	# Link in collections bar (minimised)
	global $lang,$pagename,$annotate_pdf_output,$annotate_pdf_output_only_annotated,$baseurl_short;
	if (!$annotate_pdf_output || $count_result==0){return false;}
	
	// check if this tool should be available based on annotation_counts. 
	$annotations=true;
	if ($annotate_pdf_output_only_annotated){
		// check if there are annotations in this collection
		$annotations=false;
		for($n=0;$n<count($colresult);$n++){
			if ($colresult[$n]['annotation_count']!=0){
				$annotations=true;
				break;
			}
		}
	}
	if (!$annotations){return false;}?>
    
    <option value="<?php echo $collection?>|0|0|<?php echo $baseurl_short?>plugins/annotate/pages/annotate_pdf_config.php?col=<?php echo $collection ?>|main|false">&gt;&nbsp;<?php echo $lang['pdfwithnotes']?>...</option><?php
}

function HookAnnotateAllAdditionalheaderjs(){
	global $baseurl,$k,$baseurl_short,$css_reload_key;
?>
<link rel="stylesheet" type="text/css" media="screen,projection,print" href="<?php echo $baseurl_short?>plugins/annotate/lib/jquery/css/annotation.css?css_reload_key=<?php echo $css_reload_key?>"/>

<script type="text/javascript" src="<?php echo $baseurl_short?>plugins/annotate/lib/jquery/js/jquery.annotate.js?css_reload_key=<?php echo $css_reload_key?>"></script>
<script language="javascript">
	function annotate(ref,k,w,h,annotate_toggle){
	jQuery("#toAnnotate").annotateImage({
		getUrl: "<?php echo $baseurl_short?>plugins/annotate/pages/get.php?ref="+ref+"&k="+k+"&pw="+w+"&ph="+h,
		saveUrl: "<?php echo $baseurl_short?>plugins/annotate/pages/save.php?ref="+ref+"&k="+k+"&pw="+w+"&ph="+h,
		deleteUrl: "<?php echo $baseurl_short?>plugins/annotate/pages/delete.php?ref="+ref+"&k="+k,
		useAjax: true,
		<?php  if ($k==""){?> editable: true, <?php }
			else
		{ ?> editable: false, <?php } ?>  
		toggle: annotate_toggle
	});
	}
</script>
<?php }

?>
