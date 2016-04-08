// Functions to support collections.

// Prevent caching
jQuery.ajaxSetup({ cache: false });
 
function PopCollection(thumbs) {
    if(thumbs == "hide" && collections_popout) {
        ToggleThumbs();
    }
}

function ChangeCollection(collection,k,last_collection,searchParams) {
    console.log("changecollection");
    if(typeof last_collection == 'undefined'){last_collection='';}
    if(typeof searchParams == 'undefiend') {searchParams='';}
    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    // Set the collection and update the count display
    CollectionDivLoad(baseurl_short + 'pages/collections.php?collection=' + collection + '&thumbs=' + thumbs + '&last_collection=' + last_collection + '&k=' + k + '&' +searchParams);
}

function UpdateCollectionDisplay(k) {
    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    // Update the collection count display
    jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?thumbs=' + thumbs + '&k=' + k);
}

function AddResourceToCollection(event,resource,size, collection_id) {

    // Optional params
    if(typeof collection_id === 'undefined') {
        collection_id = '';
    }

    if(event.shiftKey == true) {
        if (typeof prevadded != 'undefined') {
            lastchecked = jQuery('#check' + prevadded);
            if (lastchecked.length != 0) {
                var resourcelist = [];
                addresourceflag = false;
                jQuery('.checkselect').each(function () {
                    if(jQuery(this).attr("id") == lastchecked.attr("id")) {
                        if(addresourceflag == false) {   
                            // Set flag to mark start of resources to add
                            addresourceflag = true;
                        }
                        else { 
                            // Clear flag to mark end of resources to add
                            addresourceflag = false;  
                        }
                    }
                    else if(jQuery(this).attr("id") == 'check'+resource) {
                        // Add resource to list before clearing flag
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).attr('checked','checked');
                        if(addresourceflag == false) {
                            addresourceflag = true;
                        }
                        else {
                            addresourceflag = false;
                        }
                    }

                    if(addresourceflag) {
                        // Add resource to list 
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).attr('checked','checked');
                    }
                });
                resource = resourcelist.join(",");
            }
        }
    }
    prevadded = resource;

    thumbs = getCookie("thumbs");
    PopCollection(thumbs);

    jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?add=' + resource + '&toCollection=' + collection_id + '&size=' + size + '&thumbs=' + thumbs);
    delete prevremoved;
}

function RemoveResourceFromCollection(event,resource,pagename, collection_id) {
    // Optional params
    if(typeof collection_id === 'undefined') {
        collection_id = '';
    }

    if(event.shiftKey == true) {
        if (typeof prevremoved != 'undefined') {
            lastunchecked=jQuery('#check' + prevremoved)
            if (lastunchecked.length != 0) {
                var resourcelist = [];
                removeresourceflag = false;
                jQuery('.checkselect').each(function () {
                    if(jQuery(this).attr("id") == lastunchecked.attr("id")) {
                        if(removeresourceflag == false) { 
                            // Set flag to mark start of resources to remove
                            removeresourceflag = true;
                        }
                        else { 
                            // Clear flag to mark end of resources to remove
                            removeresourceflag = false;
                        }
                    }
                    else if(jQuery(this).attr("id") == 'check'+resource) {
                        // Add resource to list before clearing flag
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).removeAttr('checked');
                        if(removeresourceflag == false) {
                            removeresourceflag = true;
                        }
                        else {
                            removeresourceflag = false;
                        }
                    }

                    if(removeresourceflag) {
                        // Add resource to list to remove
                        resourceid = jQuery(this).attr("id").substring(5)
                        resourcelist.push(resourceid);
                        jQuery(this).removeAttr('checked');
                    }
                });
                resource = resourcelist.join(",");
            }
        }
    }
    prevremoved = resource;

    thumbs = getCookie("thumbs");
    PopCollection(thumbs);
    jQuery('#CollectionDiv').load( baseurl_short + 'pages/collections.php?remove=' + resource + '&fromCollection=' + collection_id + '&thumbs=' + thumbs);
    // jQuery('#ResourceShell' + resource).fadeOut(); //manual action (by developers) since now we can have a case where we remove from collection bar but keep it in central space because it's for a different collection
    delete prevadded;
}


function UpdateHiddenCollections(checkbox, collection) {
    var action = (checkbox.checked) ? 'showcollection' : 'hidecollection';
    jQuery.ajax({
        type: 'POST',
        url: baseurl_short + 'pages/ajax/showhide_collection.php?action=' + action + '&collection=' + collection,
        success: function(data) {
            if (data.trim() == "HIDDEN") {
                jQuery(checkbox).removeAttr('checked');
            }
            else if (data.trim() == "UNHIDDEN") {
                jQuery(checkbox).attr('checked','checked');
            }
        },
        error: function (err) {
            console.log("AJAX error : " + JSON.stringify(err, null, 2));
            if(action == 'showcollection') {
                jQuery(checkbox).removeAttr('checked');
            }
            else {
                jQuery(checkbox).attr('checked','checked');
            }
        }
    }); 
}