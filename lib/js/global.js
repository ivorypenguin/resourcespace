/* global.js : Functions to support features available globally throughout ResourceSpace */

// prevent all caching of ajax requests by stupid browsers like IE
jQuery.ajaxSetup({ cache: false });

// function to help determine exceptions
function basename(path) {
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

// IE 8 does not support console.log unless developer dialog is open, so we need a failsafe here if we're going to use it for debugging
   var alertFallback = false;
   if (typeof console === "undefined" || typeof console.log === "undefined") {
     console = {};
     if (alertFallback) {
         console.log = function(msg) {
              alert(msg);
         };
     } else {
         console.log = function() {};
     }
   }

// Cookie functions
function SetCookie (cookieName,cookieValue,nDays)
	{
	/* Store a cookie */
	var today = new Date();
	var expire = new Date();
	if (nDays==null || nDays==0) nDays=1;
	expire.setTime(today.getTime() + 3600000*24*nDays);
	if (global_cookies)
		{
		/* Remove previously stored cookies */
		document.cookie = cookieName+"=;expires=Thu, 01-Jan-70 00:00:01 GMT;path="+baseurl_short+"pages/";
		document.cookie = cookieName+"=;expires=Thu, 01-Jan-70 00:00:01 GMT;path="+baseurl_short;
		/* Use the root path */
		path = ";path=/";
		}
	else {path = "";}
	document.cookie = cookieName+"="+escape(cookieValue)
       + ";expires="+expire.toGMTString()+path;
	}

function getCookie(c_name)
{
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++)
	{
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name)
		{
			return unescape(y);
		}
	}
}



/* Keep a global array of timers */
var timers = new Array();
var loadingtimers = new Array();

function ClearTimers()
	{
	// Remove all existing page timers.
	for (var i = 0; i < timers.length; i++)
    	{
	    clearTimeout(timers[i]);
	    }
	}

function ClearLoadingTimers()
	{
	// Remove all existing page timers.
	for (var i = 0; i < loadingtimers.length; i++)
    	{
	    clearTimeout(loadingtimers[i]);
	    }
	}

/* AJAX loading of searchbar contents for search executed outside of searchbar */
function ReloadSearchBar()
	{
	var SearchBar=jQuery('#SearchBarContainer');
	SearchBar.load(baseurl_short+"pages/ajax/reload_searchbar.php?pagename="+pagename, function (response, status, xhr)
			{
			if (status=="error")
				{				
				SearchBar.html(errorpageload  + xhr.status + " " + xhr.statusText + "<br>" + response);		
				}
			else
				{
				// Load completed	
				//console.log('loaded ' + url);
				}		
			});		
	return false;
    }

/* AJAX loading of central space contents given a link */
function CentralSpaceLoad (anchor,scrolltop)
	{
	
		
	// Handle straight urls:
	if (typeof(anchor)!=='object'){ 
		var plainurl=anchor;
		var anchor = document.createElement('a');
		anchor.href=plainurl;
	}

	var CentralSpace=jQuery('#CentralSpace');
	
	/* Handle link normally if the CentralSpace element does not exist */
	if (!CentralSpace)
		{
		location.href=anchor.href;
		return false;
		} 

	/* more exceptions, going to or from pages without header */
	var fromnoheader=false;
	var tonoheader=false;
	if (
			basename(window.location.href).substr(0,11)=="preview.php" 
			||
			basename(window.location.href).substr(0,15)=="preview_all.php" 
			||
			basename(window.location.href).substr(0,9)=="index.php" 
			||
			basename(window.location.href).substr(0,8)=="done.php"
			||
			basename(window.location.href).substr(0,16)=="team_plugins.php"
			||
			basename(window.location.href).substr(0,19)=="search_advanced.php"
		) { 
			fromnoheader=true; 
		}

	if (	
			basename(anchor.href).substr(0,11)=="preview.php"
			||
			basename(anchor.href).substr(0,15)=="preview_all.php"
			||
			basename(anchor.href).substr(0,9)=="index.php" 
			||
			basename(anchor.href).substr(0,19)=="search_advanced.php" 
		) {
			tonoheader=true;
		}
		
    if (typeof fromnoheaderadd!=='undefined') 
        {
        for (var i = 0; i < fromnoheaderadd.length; i++)
            {
            if (basename(window.location.href).substr(0,fromnoheaderadd[i].charindex)==fromnoheaderadd[i].page) fromnoheader=true;
            }
        }
    if (typeof tonoheaderadd!=='undefined') 
        {
        for (var i = 0; i < tonoheaderadd.length; i++)
            {
            if (basename(anchor.href).substr(0,tonoheaderadd[i].charindex)==tonoheaderadd[i].page) tonoheader=true;
            }
        }
	// XOR to allow these pages to ajax with themselves
	if( ( tonoheader || fromnoheader ) && !( fromnoheader && tonoheader ) ) { 
			location.href=anchor.href;return false;
		}
	
	var url = anchor.href;
	pagename=basename(url);
	pagename=pagename.substr(0, pagename.lastIndexOf('.'));

	if (url.indexOf("?")!=-1)
		{
		url += '&ajax=true';
		}
	else
		{
		url += '?ajax=true';
		}

	// Fade out the link temporarily while loading. Helps to give the user feedback that their click is having an effect.
	jQuery(anchor).fadeTo(0,0.6);
	
	// Start the timer for the loading box.
	CentralSpaceShowLoading(); 
	var prevtitle=document.title;
	
	CentralSpace.load(url, function (response, status, xhr)
		{
		if (status=="error")
			{
			CentralSpaceHideLoading();
			CentralSpace.html(errorpageload  + xhr.status + " " + xhr.statusText + "<br>" + response);
			jQuery(anchor).fadeTo(0,1);
			}
		else
			{
			// Load completed
			CentralSpaceHideLoading();
			jQuery(anchor).fadeTo(0,1);

			//console.log('loaded ' + url);
		
			// Change the browser URL and save the CentralSpace HTML state in the browser's history record.
			if(typeof(top.history.pushState)=='function')
				{
				var newtitle=document.title;
				
				document.title=prevtitle;
				top.history.pushState(newtitle+'&&&'+CentralSpace.html(), applicationname, anchor.href);
				
				document.title=newtitle;
				}
			}
			
			/* Scroll to top if parameter set - used when changing pages */
			if (scrolltop==true) {
				if (jQuery("#CollectionDiv").length==0){
					jQuery('body').animate({scrollTop:0}, 'fast');
				} else {
					jQuery('.ui-layout-center').animate({scrollTop:0}, 'fast');
				}	
			}
			
		});
		
		
	return false;
	}


/* When back button is clicked, reload AJAX content stored in browser history record */
top.window.onpopstate = function(event)
	{
	if (!event.state) {return true;} // No state
	page=event.state;
	mytitle=page.substr(0, page.indexOf('&&&'));
	if (mytitle.substr(-1,1)!="'" && mytitle.length!=0) {
	page=page.substr(mytitle.length+3);
	document.title=mytitle;  	
	}
	pagename=basename(document.URL);
	pagename=pagename.substr(0, pagename.lastIndexOf('.'));
	jQuery('#CentralSpace').html(page);
	}


/* AJAX posting of a form, result are displayed in the CentralSpace area. */
function CentralSpacePost (form,scrolltop)
	{
	var url=form.action;
	var CentralSpace=jQuery('#CentralSpace');// for ajax targeting top div

	if (url.indexOf("?")!=-1)
		{
		url += '&ajax=true';
		}
	else
		{
		url += '?ajax=true';			
		}

	CentralSpaceShowLoading();		
	
	var prevtitle=document.title;
	pagename=basename(url);
	pagename=pagename.substr(0, pagename.lastIndexOf('.'));
	jQuery.post(url,jQuery(form).serialize(),function(data)
		{
		CentralSpaceHideLoading();
		CentralSpace.html(data);
		
		//console.log('ajax posted to ' + form.action);
		
		// Change the browser URL and save the CentralSpace HTML state in the browser's history record.
		if(typeof(top.history.pushState)=='function')
			{
			var newtitle=document.title;
				
			document.title=prevtitle;
			top.history.pushState(newtitle+'&&&'+data, applicationname, form.action);
				
			document.title=newtitle;		
			}
			
		/* Scroll to top if parameter set - used when changing pages */
		if (scrolltop==true) {
			if (jQuery("#CollectionDiv").length==0){
				jQuery('body').animate({scrollTop:0}, 'fast');
			} else {
				jQuery('.ui-layout-center').animate({scrollTop:0}, 'fast');
			}	
		}
			
		return false;
		})

	.error(function(result) {
		if (result.status>0)                        
			{
			CentralSpaceHideLoading();
			CentralSpace.html(errorpageload + result.status + ' ' + result.statusText + '<br>URL:  ' + url + '<br>POST data: ' + jQuery(form).serialize()); 
			return false;
			}
		});
	return false;
	}


function CentralSpaceShowLoading()
	{
	ClearLoadingTimers();
	loadingtimers.push(window.setTimeout("jQuery('#CentralSpace').fadeTo('fast',0.7);jQuery('#LoadingBox').fadeIn('fast');",ajaxLoadingTimer));
	}

function CentralSpaceHideLoading()
	{
	ClearLoadingTimers();
	jQuery('#LoadingBox').fadeOut('fast');  
	jQuery('#CentralSpace').fadeTo('fast',1);
	}







/* AJAX loading of CollectionDiv contents given a link */
function CollectionDivLoad (anchor,scrolltop)
	{
	// Handle straight urls:
	if (typeof(anchor)!=='object'){ 
		var plainurl=anchor;
		var anchor = document.createElement('a');
		anchor.href=plainurl;
	}
	
	/* Handle link normally if the CollectionDiv element does not exist */
	
	if (jQuery('#CollectionDiv').length==0 && top.collections!==undefined)
		{
		top.collections.location.href=anchor.href;
		return false;
		} 
		
	/* Scroll to top if parameter set - used when changing pages */
	if (scrolltop==true) {jQuery('.ui-layout-south').animate({scrollTop:0}, 'fast');};
	
	var url = anchor.href;
	
	if (url.indexOf("?")!=-1)
		{
		url += '&ajax=true';
		}
	else
		{
		url += '?ajax=true';
		}
	
	jQuery('#CollectionDiv').load(url, function ()
		{
		// it's helpful at this stage to know when an ajax load happened for debugging purposes
		console.log('loaded CollectionDiv ' + url);
			
		});
		
		
	return false;
	}


function directDownload(url)
	{
	dlIFrma = document.getElementById('dlIFrm');
	dlIFrma.src = url;  
	}

/* AJAX loading of navigation link */
function ReloadLinks()
    {
    
	var nav2=jQuery('#HeaderNav2');
	nav2.load(baseurl_short+"pages/ajax/reload_links.php", function (response, status, xhr)
			{
			if (status=="error")
				{				
				SearchBar.html(errorpageload  + xhr.status + " " + xhr.statusText + "<br>" + response);		
				}
			else
				{
				// Load completed	
				//console.log('loaded ' + url);
				}		
			});		
		return false;
	
    }
