// Functions to support frameless collections.


function ChangeCollection(collection)
	{
	// Set the collection and update the count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?collection=' + collection, {complete:function(){UpdateCollectionDisplay()}});	
	}
	
function UpdateCollectionDisplay()
	{
	// Update the collection count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php');
	}

function AddResourceToCollection(resource)
	{
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?add=' + resource);
	}
	
function RemoveResourceFromCollection(resource,pagename)
	{
	jQuery('#CollectionDiv').load( baseurl_short + 'pages/ajax/collections_frameless_loader.php?remove=' + resource + '&pagename=' + pagename);
	}
