<?php /* -------- Dynamic Keywords List ----------- */ 

global $baseurl,$pagename,$edit_autosave;

// Decide when the user can add new keywords to a dynamic keywords list
$readonly=($pagename=="search_advanced");

if(checkperm('bdk' . $field['ref'])) {
	$readonly = true;
}

// In case we let new lines in our value, make sure to clean it for Dynamic keywords
if(strpos($value, "\r\n") !== false)
	{
	$value = str_replace("\r\n", ' ', $value);
	}
?>

<div class="dynamickeywords ui-front">
<input type="text" <?php if ($pagename=="search_advanced") { ?> class="SearchWidth" <?php } else {?>  class="stdwidth" <?php } ?> value="<?php echo $lang["starttypingkeyword"]?>" onFocus="<?php if ($pagename=="edit"){ echo "ShowHelp(" . $field["ref"] . ");";} ?>if (this.value=='<?php echo $lang["starttypingkeyword"]?>') {this.value='';}" onBlur="<?php if ($pagename=="edit"){ echo "HideHelp(" . $field["ref"] . ");";} ?>if (this.value=='') {this.value='<?php echo $lang["starttypingkeyword"]?>'}; if(typeof(UpdateResultCount) == 'function' && this.value!='' && this.value!='<?php echo $lang["starttypingkeyword"]?>'){this.value='<?php echo $lang["starttypingkeyword"]?>';}" name="<?php echo $name ?>_selector" id="<?php echo $name ?>_selector" />

<input type='hidden' name='<?php echo $name ?>' id='<?php echo $name ?>' value='<?php echo htmlspecialchars($value) ?>'/>


<div id="<?php echo $name?>_selected" class="keywordsselected"></div>
</div>
<div class="clearerleft"> </div>

<script type="text/javascript">


	var Keywords_<?php echo $name ?>= new Array();
	var KeywordCounter_<?php echo $name ?>=0;
	var KeywordsTranslated_<?php echo $name ?>= new Array();

	function selectKeyword_<?php echo $name ?>(event, ui)
		{
		// var keyword=document.getElementById("<?php echo $name ?>_selector").value;
		var keyword=ui.item.value;

		if (keyword.substring(0,<?php echo mb_strlen($lang["createnewentryfor"], 'UTF-8') ?>)=="<?php echo $lang["createnewentryfor"] ?>")
			{
			keyword=keyword.substring(<?php echo mb_strlen($lang["createnewentryfor"], 'UTF-8')+1 ?>);

			// Add the word.
			args = {field: '<?php echo $field["ref"] ?>', keyword: keyword};
			jQuery.ajax({
				type: "POST",
				url: '<?php echo $baseurl?>/pages/edit_fields/9_ajax/add_keyword.php',
				data: args,
				success: function(result) {
					addKeyword_<?php echo $name ?>(keyword);
					updateSelectedKeywords_<?php echo $name ?>(true);
					document.getElementById('<?php echo $name ?>_selector').value='';
					}
				});

			}
		else if (keyword.substring(0,<?php echo mb_strlen($lang["noentryexists"], 'UTF-8') ?>)=="<?php echo $lang["noentryexists"] ?>"){
			document.getElementById('<?php echo $name ?>_selector').value='';
		}
		else
			{
			addKeyword_<?php echo $name ?>(keyword);
			updateSelectedKeywords_<?php echo $name ?>(true);
			document.getElementById('<?php echo $name ?>_selector').value='';
			}
		return false;
		}

	function addKeyword_<?php echo $name ?>(keyword)
		{
		removeKeyword_<?php echo $name ?>(keyword,false); // remove any existing match in the list.
		Keywords_<?php echo $name ?>[KeywordCounter_<?php echo $name ?>]=keyword;
		KeywordCounter_<?php echo $name ?>++;
		}

	function removeKeyword_<?php echo $name ?>(keyword,user_action)
		{
		var replacement=Keywords_<?php echo $name ?>;
		counter=0;
		for (var n=0;n<KeywordCounter_<?php echo $name ?>;n++)
			{
			if (keyword!=escape(Keywords_<?php echo $name ?>[n])) {replacement[counter]=Keywords_<?php echo $name ?>[n];counter++;}
			}
		Keywords_<?php echo $name ?> = replacement;
		KeywordCounter_<?php echo $name ?> =counter;
		updateSelectedKeywords_<?php echo $name ?>(user_action);
		}

	function updateSelectedKeywords_<?php echo $name ?>(user_action)
		{
		var html='';
		var value='';
		for (var n=0;n<KeywordCounter_<?php echo $name ?>;n++)
			{
			html+='<a href="#" onClick="removeKeyword_<?php echo $name ?>(\'' + escape(Keywords_<?php echo $name ?>[n]) +'\',true);return false;">[ x ]</a> &nbsp;' + Keywords_<?php echo $name ?>[n] + '<br/>';
			value+='|' + resolveTranslated_<?php echo $name ?>(Keywords_<?php echo $name ?>[n]);
			}
		document.getElementById('<?php echo $name?>_selected').innerHTML=html;
		jQuery('#<?php echo $name?>').val(value).change(); // Set value and call change to trigger a display condition check if required.
		// Update the result counter, if the function is available (e.g. on Advanced Search).
		if( typeof( UpdateResultCount ) == 'function' )
			{
			UpdateResultCount();
			}
		<?php if ($edit_autosave) {?>if (user_action) {AutoSave('<?php echo $field["ref"] ?>');}<?php } ?>
		}
		
	function resolveTranslated_<?php echo $name ?>(keyword)
		{
		if (typeof KeywordsTranslated_<?php echo $name ?>[keyword]=='undefined')
			{
			return keyword;
			}
		else
			{
			return KeywordsTranslated_<?php echo $name ?>[keyword];
			}
		}

	<?php 
	# Load translations - store original untranslated strings for each keyword, as this is what is actually set.
	for ($m=0;$m<count($field['node_options']);$m++)
		{
		$trans=i18n_get_translated($field['node_options'][$m]);
		
		$trans=escape_check($trans);
		if ($trans!="" && $trans!=$field['node_options'][$m]) # Only add if actually different (i.e., an i18n string)
			{
			?>
			KeywordsTranslated_<?php echo $name ?>['<?php echo $trans ?>']='<?php echo escape_check(addslashes($field['node_options'][$m])) ?>';
			<?php
			}
		}

	$selected_values = array();
    if('' === trim($value) && (isset($ref) && 0 < $ref))
        {
        $selected_values = explode(',', $field['value']);
        }
	else
	    {
	    $selected_values = explode(',', $value);
	    }
    $selected_values = trim_array($selected_values);

	# Select all selected options
	for ($m=0;$m<count($field['node_options']);$m++)
		{
		$trans=i18n_get_translated($field['node_options'][$m]);
			
		if ($trans!="" && in_array(trim($field['node_options'][$m]),$selected_values))
			{
			?>
			addKeyword_<?php echo $name ?>('<?php echo escape_check($trans) ?>');
			<?php
			}
		}
	?>

	jQuery('#<?php echo $name?>_selector').autocomplete( { source: "<?php echo $baseurl?>/pages/edit_fields/9_ajax/suggest_keywords.php?field=<?php echo $field["ref"] ?>&readonly=<?php echo $readonly ?>", 
		select : selectKeyword_<?php echo $name ?>
		});

	// prevent return in autocomplete field from submitting entire form
	// we want the user to explicitly choose what they want to do
	jQuery('#<?php echo $name?>_selector').keydown(function(event){ 
			var keyCode = event.keyCode ? event.keyCode : event.which;
			if (keyCode == 13) {
				event.stopPropagation();
				event.preventDefault();
				return false;
			}
		 });


	updateSelectedKeywords_<?php echo $name ?>(false);

</script>
<?php
/* include dirname(__FILE__) . "/../../include/user_select.php"; 
*/
?>
