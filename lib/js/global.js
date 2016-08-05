/* global.js : Functions to support features available globally throughout ResourceSpace */
var modalalign=false;
var modalfit=false;
	
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
function is_touch_device() {
   return 'ontouchstart' in window // works on most browsers 
        || 'onmsgesturechange' in window; // works on ie10
}
function SetCookie (cookieName,cookieValue,nDays,global)
	{
	var today = new Date();
	var expire = new Date();
	if (global === undefined) {
          global = false;
    } 
	
	if (nDays==null || nDays==0) nDays=1;
	expire.setTime(today.getTime() + 3600000*24*nDays);
	if (global_cookies || global)
		{
		/* Remove previously stored cookies */
		//document.cookie = cookieName+"=;expires=Thu, 01-Jan-70 00:00:01 GMT;path="+baseurl_short+"pages/";
		//document.cookie = cookieName+"=;expires=Thu, 01-Jan-70 00:00:01 GMT;path="+baseurl_short;
		/* Use the root path */
		path = ";path=/";
		}
	else {path = "";}
	if (window.location.protocol === "https:") {
            document.cookie = cookieName+"="+escape(cookieValue)+";expires="+expire.toGMTString()+path+";secure";
        } else {
            document.cookie = cookieName+"="+escape(cookieValue)+";expires="+expire.toGMTString()+path;
        }
	}

function getCookie(c_name)
{
	var i,x,y,ARRcookies=document.cookie.split("; ");
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

/* Scroll to top if parameter set - used when changing pages */
function pageScrolltop(element)
	{
	jQuery(element).animate({scrollTop:0}, 'fast');
	}

/* AJAX loading of central space contents given a link */
function CentralSpaceLoad (anchor,scrolltop,modal)
	{
	var CentralSpace=jQuery('#CentralSpace');
	
	if (typeof modal=='undefined') {modal=false;}
	if (!modal)
	    {
	    // If what we're loading isn't a modal, close any modal. Ensures the modal closes if a link on it is clicked.
	    ModalClose();
	    }
	else
	    {
	    // Targeting a modal. Set the target to be the modal, not CentralSpace.
	    CentralSpace=jQuery('#modal');
	    }
	    
	// Handle straight urls:
	if (typeof(anchor)!=='object'){ 
		var plainurl=anchor;
		var anchor = document.createElement('a');
		anchor.href=plainurl;
	}


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
		
    if (typeof fromnoheaderadd!=='undefined' && !modal) 
        {
        for (var i = 0; i < fromnoheaderadd.length; i++)
            {
            if (basename(window.location.href).substr(0,fromnoheaderadd[i].charindex)==fromnoheaderadd[i].page) fromnoheader=true;
            }
        }
    if (typeof tonoheaderadd!=='undefined' && !modal) 
        {
        for (var i = 0; i < tonoheaderadd.length; i++)
            {
            if (basename(anchor.href).substr(0,tonoheaderadd[i].charindex)==tonoheaderadd[i].page) tonoheader=true;
            }
        }
	// XOR to allow these pages to ajax with themselves
	if( ( tonoheader || fromnoheader ) && !( fromnoheader && tonoheader ) && !modal) { 
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
	if (modal) {url+="&modal=true";}
	
	// Fade out the link temporarily while loading. Helps to give the user feedback that their click is having an effect.
	if (!modal) {jQuery(anchor).fadeTo(0,0.6);}
	
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
			if (!modal) {jQuery(anchor).fadeTo(0,1);}
			else {
			    // Show the modal
			     ModalCentre();
			    jQuery('#modal_overlay').fadeIn('fast');
			    if (modalalign=='right')
				{
				// Right aligned "drop down" style modal used for My Account.
				
				jQuery('#modal').slideDown('fast');
				}
			    else
				{
				jQuery('#modal').show();
				}
			   
			    }

			// Activate or deactivate the large slideshow, if this function is enabled.			
			if (typeof ActivateSlideshow == 'function' && !modal)
			    {
			    if (basename(anchor.href).substr(0,8)=="home.php")
				{
				ActivateSlideshow();
				}
			    else
				{
				DeactivateSlideshow();
				}
			    }

		    // Only allow reordering when search results are collections
		    if(basename(anchor.href).substr(0, 10) == 'search.php')
		    	{
				var query_strings = get_query_strings(anchor.href);

				if(!is_empty(query_strings))
					{
					if(query_strings.hasOwnProperty('search') && query_strings.search.substring(0, 11) !== '!collection')
						{
						allow_reorder = false;
						}
					}

				CentralSpace.trigger('CentralSpaceSortable');
		    	}
	    
			//console.log('loaded ' + url);
		
			// Change the browser URL and save the CentralSpace HTML state in the browser's history record.
			if(typeof(top.history.pushState)=='function' && !modal)
				{
				top.history.pushState(document.title+'&&&'+CentralSpace.html(), applicationname, anchor.href);
				}
			}
			
			/* Scroll to top if parameter set - used when changing pages */
		    if (scrolltop==true)
				{
				if (modal)
					{
					pageScrolltop(scrolltopElementModal);
					}
				else
					{
					pageScrolltop(scrolltopElementCentral);
					}
				}
		    
			// Add accessibility enhancement:
			CentralSpace.append('<!-- Use aria-live assertive for high priority changes in the content: -->');
			CentralSpace.append('<span role="status" aria-live="assertive" class="ui-helper-hidden-accessible"></span>');

			// Add global trash bin:
			CentralSpace.append(global_trash_html);
			CentralSpace.trigger('prepareTrash');

			// Add Chosen dropdowns, if configured
			if (typeof chosen_config !== 'undefined')
			    {
			    for (var selector in chosen_config)
				{
				jQuery(selector).chosen(chosen_config[selector]);
				}
			    }
			    
			if (typeof AdditionalJs == 'function') {   
			  AdditionalJs();  
			}
			
		});
	
	    
	return false;
	}


/* When back button is clicked, reload AJAX content stored in browser history record */
top.window.onpopstate = function(event)
	{

	if (!event.state) {console.log('no event state');return true;} // No state

   page=window.history.state;
   mytitle=page.substr(0, page.indexOf('&&&'));
   if (mytitle.substr(-1,1)!="'" && mytitle.length!=0) {
   page=page.substr(mytitle.length+3);
   document.title=mytitle;    

	// Calculate the name of the page the user is navigating to.
	pagename=basename(document.URL);
	pagename=pagename.substr(0, pagename.lastIndexOf('.'));

	if (pagename=="home" || pagename=="view" || pagename=="preview" || pagename=="search" || pagename=="collection_manage"){
		
		// Certain pages do not handle back navigation well as start-up scripts are not executed. Always reload in these cases.
		
		// The ultimate fix is for this all to be unnecessary due
		// to scripts initialising correctly, perhaps using an event or similar,
		// or for all initialisation to have already completed in the header.php load ~DH
		
		CentralSpaceShowLoading();
		window.location.reload(true);
		return true;
	}
	ModalClose();
	jQuery('#CentralSpace').html(page); 
 	
	}
}


/* AJAX posting of a form, result are displayed in the CentralSpace area. */
function CentralSpacePost (form,scrolltop,modal)
	{
	var url=form.action;
	var CentralSpace=jQuery('#CentralSpace');// for ajax targeting top div

	if (typeof modal=='undefined') {modal=false;}
	if (!modal)
	    {
	    // If what we're loading isn't a modal, close any modal. Ensures the modal closes if a link on it is clicked.
	    ModalClose();
	    }
	else
	    {
	    // Targeting a modal. Set the target to be the modal, not CentralSpace.
	    CentralSpace=jQuery('#modal');
	    }
	
	if (url.indexOf("?")!=-1)
		{
		url += '&ajax=true';
		}
	else
		{
		url += '?ajax=true';			
		}
	url += '&posting=true';
	CentralSpaceShowLoading();

	// CKEditor needs to update textareas before any posting is done
	// otherwise it will post null values
	for(instance in CKEDITOR.instances)
		{
		CKEDITOR.instances[instance].updateElement();
		}
	
	var prevtitle=document.title;
	pagename=basename(url);
	pagename=pagename.substr(0, pagename.lastIndexOf('.'));
	jQuery.post(url,jQuery(form).serialize(),function(data)
		{
		CentralSpaceHideLoading();
		CentralSpace.html(data);

		//console.log('ajax posted to ' + form.action);

		// Add global trash bin:
		CentralSpace.append(global_trash_html);
		CentralSpace.trigger('prepareTrash');

		// Activate or deactivate the large slideshow, if this function is enabled.			
		if (typeof ActivateSlideshow == 'function' && !modal)
		    {
		    if (basename(form.action).substr(0,8)=="home.php")
			{
			ActivateSlideshow();
			}
		    else
			{
			DeactivateSlideshow();
			}
		    }
			    
		// Change the browser URL and save the CentralSpace HTML state in the browser's history record.
		if(typeof(top.history.pushState)=='function' && !modal)
			{
			top.history.pushState(document.title+'&&&'+data, applicationname, form.action);
			}
			
		/* Scroll to top if parameter set - used when changing pages */
		if (scrolltop==true) {
				if (modal)
				    {
				    pageScrolltop(scrolltopElementModal);
				    }
				else
				    {
				    pageScrolltop(scrolltopElementCentral);
				    }
		}
		
		// Add Chosen dropdowns, if configured
		if (typeof chosen_config !== 'undefined')
		    {
		    for (var selector in chosen_config)
			{
			jQuery(selector).chosen(chosen_config[selector]);
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
	if (scrolltop==true) {pageScrolltop(scrolltopElementCollection);}
	
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
		
		
		});
		
		
	return false;
	}


function directDownload(url)
	{
	dlIFrma = document.getElementById('dlIFrm');
	if (typeof dlIFrma != "undefined"){
		dlIFrma.src = url;  
	}
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
				ActivateHeaderLink(document.location.href);
				}		
			});		
		return false;
	
    }

function relateresources (ref,related,action)
	{
	console.log("relateresources:" +ref + ":" + related + ":" + action);
	url=baseurl_short+"pages/ajax/relate_resources.php?ref=" + ref + "&related=" + related + "&action=" + action;
	jQuery.post(url, function (response, status, xhr)
			{
			if (response.indexOf("error") > 0)
				{				
				alert ("ERROR");
				return false;
				}
			else
				{
				jQuery('#relatedresource' + related).remove();
				return true;
				}		
			});		
		return false;	
	}

/*
When an object of class "CollapsibleSectionHead" is clicked then next element is collpased or expanded.
 */
function registerCollapsibleSections(use_cookies)
	{
	// Default params
	use_cookies = (typeof use_cookies !== 'undefined') ? use_cookies : true;

	jQuery(document).ready(function()
		{
		jQuery('.CollapsibleSectionHead').click(function()
			{
			cur    = jQuery(this).next();
			cur_id = cur.attr("id");

			if (cur.is(':visible'))
				{
				if(use_cookies)
					{
					console.log('collapsed');
					SetCookie(cur_id, 'collapsed');
					}

				jQuery(this).removeClass('expanded');
				jQuery(this).addClass('collapsed');
				}
			else
				{
				if(use_cookies)
					{
					console.log('expanded');
					SetCookie(cur_id, 'expanded');
					}
				jQuery(this).addClass('expanded');
				jQuery(this).removeClass('collapsed');
				}

			cur.stop(); // Stop existing animation if any
			cur.slideToggle();

			return false;
			}).each(function()
				{
				cur_id = jQuery(this).next().attr("id");

				if ((use_cookies && getCookie(cur_id) == 'collapsed') || jQuery(this).hasClass('collapsed'))
					{
					jQuery(this).next().hide();
					jQuery(this).addClass('collapsed');
					}
				else
					{
					jQuery(this).addClass('expanded');
					}
				});
		});
	}

function getQueryStrings()
{ 
	var assoc  = {};
	var decode = function(s) { return decodeURIComponent(s.replace(/\+/g, " ")); };
	var queryString = location.search.substring(1); 
	var keyValues = queryString.split('&'); 

	for(var i in keyValues) {
		if (typeof keyValues[i].split == 'function'){

			var key = keyValues[i].split('=');
			if (key.length > 1) {
				assoc[decode(key[0])] = decode(key[1]);
			} 
		}
	}

	return assoc; 
}

// Take the current query string and attach it to a form action
// Useful when avoiding moving away from the page you are on
function passQueryStrings(params, formID)
{
	var form_action = '';
	var query_string = '';
	var qs = getQueryStrings();

	if(params.constructor !== Array) {
		console.log('Error - params in passQueryStrings function should be an array!');
		return false;
	}

	// Pass only specified params to the query string
	for(var i = 0; i < params.length; i++) {
		// console.log(params[i]);
		if(qs.hasOwnProperty(params[i]) && query_string === '') {
			query_string = params[i] + '=' + qs[params[i]];
		} else if(qs.hasOwnProperty(params[i]) && query_string !== '') {
			query_string += '&' + params[i] + '=' + qs[params[i]];
		}
	}

    form_action = document.getElementById(formID).action + '?' + query_string;

    if(document.getElementById(formID).action !== form_action) {
    	document.getElementById(formID).action = form_action;
	}

	return true;
}

// Use this function to confirm special searches
// e.g: is_special_search('!collection', 11)
function is_special_search(special_search, string_length)
{
	var query_strings = getQueryStrings();

	if(is_empty(query_strings)) {
		return false;
	}

	if(query_strings.search.substring(0, string_length) === special_search) {
		return true;
	}

	return false;
}

// Check if object is empty or not
// Note: safer solution compared to using keys()
function is_empty(object)
{
	for(var property in object) {
		
		if(object.hasOwnProperty(property)) {
			return false;
		}
	
	}

	return true;
}

// Returns object with all query strings found
// Note: should be used when the location does not have the correct URL
function get_query_strings(url)
{
	if(url.trim() === '')
		{
		console.error('RS_debug: get_query_strings, parameter "url" can\'t be an empty string!');
		return {};
		}

	var query_strings = {};
	var url_split     = url.split('?');

	if(url_split.length === 1)
		{
		console.log('RS_debug: no query strings found on ' + url);
		return query_strings;
		}

	url_split = url_split[1];
	url_split = url_split.split('&');

	for(var i = 0; i < url_split.length; i++)
		{
		var var_value_pair = url_split[i].split('=');
		query_strings[var_value_pair[0]] = decodeURI(var_value_pair[1]);
		}

	return query_strings;
}

function ModalLoad(url,jump,fittosize,align)
	{
	// Load to a modal rather than CentralSpace. "url" can be an anchor object.
	
	// No modal? Don't launch a modal. Go to CentralSpaceLoad
	if (!jQuery('#modal')) {return CentralSpaceLoad(url,jump);}
	
	// Window smaller than the modal? No point showing a modal as it wouldn't appear over the background.
	if (jQuery(window).width()<=jQuery('#modal').width()) {return CentralSpaceLoad(url,jump);}
	
	var top, left;


	jQuery('#modal').draggable({ handle: ".RecordHeader" });
	
    // Set modalfit so that resizing does not change the size
    modalfit=false;
    if ((!(typeof fittosize=='undefined') && fittosize)) {
        modalfit=true;
    }
	
    // Set modalalign to store the alignment of the current modal (needs to be global so that resizing the window still correctly aligns the modal)
    modalalign=false;
    if ((!(typeof align=='undefined'))) {
        modalalign=align;
    }
    
    // To help with calling of a popup modal from full modal, can return to previous modal location
    if (!(typeof modalurl=='undefined')) {
			modalbackurl=modalurl;
			}            
    modalurl=url;
    
	return CentralSpaceLoad(url,false,true); 
	}
	
function ModalPost(form,jump,fittosize)
	// Post to a modal rather than CentralSpace. "url" can be an anchor object.
	{
    var top, left;
	
	var url=form.action;
	jQuery('#modal_overlay').show();
	jQuery('#modal').show();
	jQuery('#modal').draggable({ handle: ".RecordHeader" });
	
     // Set modalfit so that resizing does not change the size
    modalfit=false;
    if ((!(typeof fittosize=='undefined') && fittosize)) {
        modalfit=true;
    }
    
	ModalCentre();
    
    if (!(typeof modalurl=='undefined')) {
			modalbackurl=modalurl;
			}
    modalurl=url;
	return CentralSpacePost(form,jump,true);
	}
    
function ModalCentre()
	{
	// Centre the modal and overlay on the screen. Called automatically when opened and also when the browser is resized, but can also be called manually.
	
    // If modalfit is not specified default to the full modal dimensions
	if (modalalign=='right')
	    {
	    modalheight='auto';
	    modalwidth=300;
	    }
	else if ((!(typeof modalfit=='undefined') && modalfit))
	    {
	    modalheight='auto';
	    modalwidth='auto';
	    }
	else
	    {
	    modalheight=jQuery('.ui-layout-center').height() - 120;
	    modalwidth=1235;
	    }
    
    
    jQuery('#modal').css({	
        height: modalheight,
        width: modalwidth
    });

    // Support alignment of modal (e.g. for 'My Account')
    topmargin=80;
    if (modalalign=='right')
	{
        left = Math.max(jQuery(window).width() - jQuery('#modal').outerWidth(), 0) - 70;
	topmargin=50;
	}
    else
	{
        left = Math.max(jQuery(window).width() - jQuery('#modal').outerWidth(), 0) / 2;
	}    
    
    // Make sure that modal clears the collection bar
    if (jQuery('#modal').height() > jQuery('.ui-layout-center').height() - 120) {
        modalheight = jQuery('.ui-layout-center').height() - 120;
        jQuery('#modal').css({	
            height: modalheight
        });
    }    
    
    jQuery('#modal').css({
	top:topmargin + jQuery(window).scrollTop(), 
	left:left + jQuery(window).scrollLeft()
	});
		
	}
function ModalClose()
	{
	jQuery('#modal_overlay').hide();
	jQuery('#modal').hide();
	jQuery('#modal').html('');
	delete modalurl;
	}
	

// Update header links to add a class that indicates current location 
function ActivateHeaderLink(activeurl)
		{
        var matchedstring=0;
        activelink='';
		jQuery('#HeaderNav2 li a').each(function(){
            // Remove current class from all header links
            jQuery(this).removeClass('current');     
			if(activeurl.indexOf(this.href)>-1){
                    if (this.href.length>matchedstring) {
                    // Find longest matched URL
                    matchedstring=this.href.length;
                    activelink=jQuery(this);
                    }				
				}				
			});
        jQuery(activelink).addClass('current');
		}
        
function ReplaceUrlParameter(url, parameter, value){
	var parameterstring = new RegExp('\\b(' + parameter + '=).*?(&|$)')
	if(url.search(parameterstring)>=0){
		return url.replace(parameterstring,'$1' + value + '$2');
        }
	return url + (url.indexOf('?')>0 ? '&' : '?') + parameter + '=' + value; 
	}
	
function nl2br(text) {
  return (text).replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br />' + '$2');
}

// Check if given JSON can be parsed or not
function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function styledalert(title,text){
    jQuery("#modal_dialog").html(text);
    jQuery("#modal_dialog").dialog({
        title: title,
        resizable: false,
        dialogClass: 'no-close',
         buttons: [{
                  text: oktext,
                  click: function() {
                    jQuery( this ).dialog( "close" );
                  }
                }]
     });
    }

// Restrict all defined numeric fields 
jQuery(document).ready(function()
	{
	jQuery('.numericinput').keypress(function(e)
		{
		var key = e.which || e.keyCode;
		if (!(key >= 48 && key <= 57) && // Interval of values (0-9)
			 (key !== 8))             // Backspace
			{
			e.preventDefault();
			return false;
			}
		})
	});


