<?php /* -------- Text Box (single line) ---------- */ ?>

<?php
global $merge_filename_with_title,$merge_filename_with_title_default;

if($merge_filename_with_title && $field['title'] == 'Title' && $ref < 0) {

	$do_not_use = FALSE;
	$replace = FALSE;
	$prefix = FALSE;
	$suffix = FALSE;

	// Choose option to be used as default
	switch ('merge_filename_title_' . $merge_filename_with_title_default) {
		case 'merge_filename_title_do_not_use':
			$do_not_use = TRUE;
			break;
		case 'merge_filename_title_replace':
			$replace = TRUE;
			break;
		case 'merge_filename_title_prefix':
			$prefix = TRUE;
			break;
		case 'merge_filename_title_suffix':
			$suffix = TRUE;
			break;
		default:
			$do_not_use = TRUE;
	}
?>
<div id="merge_filename_title_container">
<div id="" class=""><?php echo $lang['merge_filename_title_question']; ?></div>
<table id="" class="radioOptionTable" cellpadding="3" cellspacing="3">
	<tbody>
		<tr>
			<td>
				<input type="radio" id="merge_filename_title_do_not_use" name="merge_filename_with_title_option" value="<?php echo $lang['merge_filename_title_do_not_use']; ?>" <?php if($do_not_use) { ?>checked<?php } ?>/>
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_do_not_use"><?php echo $lang['merge_filename_title_do_not_use']; ?></label>
			</td>
			<td>
				<input type="radio" id="merge_filename_title_replace" name="merge_filename_with_title_option" value="<?php echo $lang['merge_filename_title_replace']; ?>" <?php if($replace) { ?>checked<?php } ?>/>
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_replace"><?php echo $lang['merge_filename_title_replace']; ?></label>
			</td>
			<td>
				<input type="radio" id="merge_filename_title_prefix" name="merge_filename_with_title_option" value="<?php echo $lang['merge_filename_title_prefix']; ?>" <?php if($prefix) { ?>checked<?php } ?>/>
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_prefix"><?php echo $lang['merge_filename_title_prefix']; ?></label>
			</td>
			<td>
				<input type="radio" id="merge_filename_title_suffix" name="merge_filename_with_title_option" value="<?php echo $lang['merge_filename_title_suffix']; ?>" <?php if($suffix) { ?>checked<?php } ?>/>
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_suffix"><?php echo $lang['merge_filename_title_suffix']; ?></label>
			</td>
			<!-- Include extension? -->
			<td>
				<input type="checkbox" id="merge_filename_title_include_extension" name="merge_filename_with_title_include_extensions" value="yes" />
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_include_extension"><?php echo $lang['merge_filename_title_include_extensions']; ?></label>
			</td>
			<!-- Spacer -->
			<td>
				<input type="text" id="merge_filename_title_spacer" name="merge_filename_with_title_spacer" value="" maxlength="3" />
			</td>
			<td>
				<label class="customFieldLabel" for="merge_filename_title_spacer"><?php echo $lang['merge_filename_title_spacer']; ?></label>
			</td>
		</tr>
	</tbody>
</table>
<?php } ?>

<input class="stdwidth" type=text name="<?php echo $name?>" id="<?php echo $name?>" value="<?php echo htmlspecialchars($value)?>" <?php echo $help_js; ?>
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>
>

<?php 
if($merge_filename_with_title && $field['title'] == 'Title' && $ref < 0) { ?>
	</div>
<?php } ?>