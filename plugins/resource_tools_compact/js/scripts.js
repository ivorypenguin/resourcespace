function registerResourcetoolsSlide(context)
	{

	jQuery("#" + context + "_view-download-button").click(function(e){	
		//var current = jQuery(".view-download-button").closest();
		if(jQuery(this).hasClass("active")){
			jQuery("#" + context + " #ResourceDownloadOptions").hide();
			jQuery(this).removeClass("active");
		}
		else {
			jQuery("#" + context + " #ResourceDownloadOptions").show();
			jQuery(this).addClass("active");			
			jQuery("#" + context + " #ResourceToolsContainer").hide();
			jQuery("#" + context + "_view-resource-tools").removeClass("active");
			jQuery("#" + context + "_ResourceShareContainer").hide();
			jQuery("#" + context + "_share-resource-button").removeClass("active");
		}
		return false;
	});
	

	/* Share Button */
	jQuery("#" + context + "_share-resource-button").click(function(e){	
		if(jQuery(this).hasClass("active")){
			jQuery("#" + context + "_ResourceShareContainer").hide();
			jQuery(this).removeClass("active");
		}
		else {
			jQuery("#" + context + "_ResourceShareContainer").show();
			jQuery(this).addClass("active");
			jQuery("#" + context + " #ResourceDownloadOptions").hide();
			jQuery("#" + context + "_view-download-button").removeClass("active");
			jQuery("#" + context + " #ResourceToolsContainer").hide();
			jQuery("#" + context + "_view-resource-tools").removeClass("active");
		}
		return false;
	});
	
	/* Resource Tools (Options) */
	jQuery("#" + context + "_view-resource-tools").click(function(e){
		if(jQuery(this).hasClass("active")){
			jQuery("#" + context + " #ResourceToolsContainer").hide();
			jQuery(this).removeClass("active");
		}
		else {
			jQuery("#" + context + " #ResourceToolsContainer").show();
			jQuery(this).addClass("active");
			jQuery("#" + context + " #ResourceDownloadOptions").hide();
			jQuery("#" + context + "_view-download-button").removeClass("active");
			jQuery("#" + context + "_ResourceShareContainer").hide();
			jQuery("#" + context + "_share-resource-button").removeClass("active");
		}
		return false;
	});

	/* Resource Metadata Information */
	jQuery("#" + context + "_resource-details").click(function(e){
		//var current = jQuery(".resource-details").closest();
		if(jQuery(this).hasClass("active")){
			jQuery("#" + context + " #Metadata").hide();
			jQuery(this).removeClass("active");
			jQuery(this).html("More Information");
		}
		else {
			jQuery("#" + context + " #Metadata").show();
			jQuery(this).addClass("active");
			jQuery(this).html("Less Information");
		}
		return false;
	});		
		
	}
