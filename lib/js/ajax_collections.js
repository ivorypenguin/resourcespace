// Functions to support collections.

 // Prevent caching
 jQuery.ajaxSetup({ cache: false });
 


function ChangeCollection(collection,k)
	{
	thumbs=getCookie("thumbs");
	// Set the collection and update the count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?collection=' + collection+'&thumbs='+thumbs+'&k=' + k);
	}
	
function UpdateCollectionDisplay(k)
	{
		
	thumbs=getCookie("thumbs");
	// Update the collection count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?thumbs='+thumbs+'&k=' + k);
	}

function AddResourceToCollection(resource)
	{
		
	thumbs=getCookie("thumbs");
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?add=' + resource+'&thumbs='+thumbs);
	}
	
function RemoveResourceFromCollection(resource,pagename)
	{
		
	thumbs=getCookie("thumbs");
	jQuery('#CollectionDiv').load( baseurl_short + 'pages/ajax/collections_frameless_loader.php?remove=' + resource + '&pagename=' + pagename+'&thumbs='+thumbs);
	}
