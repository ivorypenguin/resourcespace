function metadataReport(ref,context) {
	jQuery('#' + context + 'metadata_report').load(
		"ajax/metadata_report.php?ref="+ref+"&context=" + context
		);
	}
