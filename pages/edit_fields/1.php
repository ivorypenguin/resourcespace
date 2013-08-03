<?php /* -------- Text Box (multi-line) ----------- */ ?>
<textarea class="stdwidth" rows=6 cols=50 name="<?php echo $name?>" id="<?php echo $name?>" <?php echo $help_js; ?>
<?php if ($edit_autosave) {?>onChange="AutoSave('<?php echo $field["ref"] ?>');"<?php } ?>
><?php echo htmlspecialchars($value)?></textarea>
