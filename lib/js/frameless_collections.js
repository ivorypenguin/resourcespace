// Functions to support frameless collections.


function ChangeCollection(collection)
	{
	// Set the collection and update the count display
	jQuery.ajax(baseurl_short + 'pages/ajax/collections_frameless_loader.php?collection=' + collection, {complete:function(){UpdateCollectionDisplay('<?php echo $k ?>')}});
	}
	
function UpdateCollectionDisplay()
	{
	// Update the collection count display
	jQuery('#CollectionFrameless').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php');
	}

function AddResourceToCollection(resource)
	{
	jQuery('#CollectionFrameless').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?add=' + resource);
	}
	
function RemoveResourceFromCollection(resource,pagename)
	{
	jQuery('#CollectionFrameless').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?remove=' + resource + '&pagename=' + pagename);
	}
