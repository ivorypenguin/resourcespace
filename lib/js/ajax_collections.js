// Functions to support frameless collections.

function ChangeCollection(collection,k)
	{
	// quick dirty hack to try assessing the state of the max/min collection display
	var windowHeight = getWindowHeight();
	var colHeight = windowHeight - document.getElementById('CollectionDiv').style.top.replace('px','');
	if (colHeight ==40){thumbs='hide';} else {thumbs='show'};
		console.log(colHeight+'= thumbs ' +thumbs + ' col=' + collection);
	// Set the collection and update the count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?collection=' + collection+'&thumbs='+thumbs+'&k=' + k);
	}
	
function UpdateCollectionDisplay(k)
	{
		
	var windowHeight = getWindowHeight();
	var colHeight = windowHeight - document.getElementById('CollectionDiv').style.top.replace('px','');
	if (colHeight ==40){thumbs='hide';} else {thumbs='show'};
		console.log(colHeight+'= thumbs ' +thumbs);
	// Update the collection count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?thumbs='+thumbs+'&k=' + k);
	}

function AddResourceToCollection(resource)
	{
		
	var windowHeight = getWindowHeight();
	var colHeight = windowHeight - document.getElementById('CollectionDiv').style.top.replace('px','');
	if (colHeight ==40){thumbs='hide';} else {thumbs='show'};
		console.log(colHeight+'= thumbs ' +thumbs);
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/ajax/collections_frameless_loader.php?add=' + resource+'&thumbs='+thumbs);
	}
	
function RemoveResourceFromCollection(resource,pagename)
	{
		
	var windowHeight = getWindowHeight();
	var colHeight = windowHeight - document.getElementById('CollectionDiv').style.top.replace('px','');
	if (colHeight ==40){thumbs='hide';} else {thumbs='show'};
		console.log(colHeight+'= thumbs ' +thumbs);
	jQuery('#CollectionDiv').load( baseurl_short + 'pages/ajax/collections_frameless_loader.php?remove=' + resource + '&pagename=' + pagename+'&thumbs='+thumbs);
	}
