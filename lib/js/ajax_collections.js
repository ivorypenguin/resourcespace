// Functions to support collections.

 // Prevent caching
 jQuery.ajaxSetup({ cache: false });
 


function ChangeCollection(collection,k)
	{
	thumbs=getCookie("thumbs");
	// Set the collection and update the count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?collection=' + collection+'&thumbs='+thumbs+'&k=' + k);
	}
	
function UpdateCollectionDisplay(k)
	{
		
	thumbs=getCookie("thumbs");
	// Update the collection count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?thumbs='+thumbs+'&k=' + k);
	}

function AddResourceToCollection(event,resource,size)
	{	
	if(event.shiftKey==true)
		{
		if (typeof prevadded != 'undefined')
			{
			lastchecked=jQuery('#check' + prevadded)
			if (lastchecked.length!=0) 
				{
				var resourcelist=new Array();
				addresourceflag=false;
				jQuery('.checkselect').each(function () {
					if(jQuery(this).attr("id")==lastchecked.attr("id"))
						{
						if(addresourceflag==false) 	 // Set flag to mark start of resources to add
							{					
							addresourceflag=true;				
							}
						else // Clear flag to mark end of resources to add
							{
							addresourceflag=false;	
							}
						}
					else if(jQuery(this).attr("id")=='check'+resource)	
						{
						// Add resource to list before clearing flag
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).attr('checked','checked');
						if(addresourceflag==false)	
							{					
							addresourceflag=true;			
							}
						else
							{
							addresourceflag=false;	
							}		
						}
					if(addresourceflag==true)
						{
						// Add resource to list 
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).attr('checked','checked');
						}
					});		
				resource=resourcelist.join(",");
				}			
			}
		prevadded=resource;
		}
	else
		{
		prevadded=resource;	
		}	

	thumbs=getCookie("thumbs");
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?add=' + resource+'&size='+size+'&thumbs='+thumbs);
	}
	
function RemoveResourceFromCollection(resource,pagename)
	{
	thumbs=getCookie("thumbs");
	jQuery('#CollectionDiv').load( baseurl_short + 'pages/collections.php?remove=' + resource + '&thumbs='+thumbs);
	delete prevadded;
	}


