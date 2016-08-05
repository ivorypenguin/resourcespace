/*
 *  infobox.js
 *  Part of ResourceSpace
 *  Displays an information box when the user hovers over resource results.
 *
 *--------------------------------------------------------------------------*/

var InfoBoxEnabled=true;
var InfoBoxWaiting=false;
var InfoBoxVisible=false;
var InfoBoxRef=0;
var InfoBoxTop=0;
var InfoBoxLeft=0;
var InfoBoxTimer=false;

function InfoBoxMM(event)
    {
	event = event || window.event;
	tgt = event.target || event.srcElement;
	if (!InfoBoxEnabled || InfoBoxRef==0) {return false;}
	var iname = 'InfoBox';
	var iiname = 'InfoBoxInner';
	var iscollection = (tgt.getAttribute('class')=='CollectImageBorder');
	if (iscollection)
		{
		iname = 'InfoBoxCollection';
		iiname = 'InfoBoxCollectionInner';
		}
   	var i=document.getElementById(iname);
   	if (!i) {return false;} // no object? ignore for now
   	
   	var ii=document.getElementById(iiname);
    var x=event.clientX;
    var y=event.clientY;
    
	// Set up the box background / shadow
	if(iscollection) {
		// Deal with scrolling in CollectionDiv
		var collectionDiv = document.getElementById('CollectionDiv');
		y -= collectionDiv.offsetTop - collectionDiv.scrollTop;
		x -= collectionDiv.offsetLeft - collectionDiv.scrollLeft;
	    // move the box higher up if the cursor is low enough to support this.
	    InfoBoxTop = y - 25;
	    if (InfoBoxTop<5) {InfoBoxTop=5;}
	    InfoBoxLeft = x + 10;    	
	    if (x>400)
	    	{
	    	InfoBoxLeft-=396;
	   		i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_left.png')";
	   		ii.style.marginLeft="15px";
	   		ii.style.marginRight="50px";
	    	}
		else
			{
			i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_right.png')";
	   		ii.style.marginLeft="50px";
	  		ii.style.marginRight="15px";
			}
		}
	else
		{
		// Deal with scrolling in UICenter
		y += document.getElementById('UICenter').scrollTop;
		x += document.getElementById('UICenter').scrollLeft;
	    // move the box higher up if the cursor is low enough to support this.
		InfoBoxTop =  y + 15;
	    if (InfoBoxTop<5) {InfoBoxTop=5;}
		InfoBoxLeft = x - 25;

		if(InfoBoxImageMode)
			{
			// move the box higher up if the cursor is low enough to support this.
			if (y>400+document.getElementById('UICenter').scrollTop)
				{
				InfoBoxTop-=470;
				i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_image_up.png')";
				ii.style.marginTop="20px";
				ii.style.marginBottom="70px";
				}
			else
				{
				i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_image_down.png')";
				ii.style.marginTop="73px";
				ii.style.marginBottom="15px";
				}
			}
		else
			{
			if (y>310+document.getElementById('UICenter').scrollTop)
    			{
				InfoBoxTop-=320;
				i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_up.png')";
				ii.style.marginTop="15px";
				ii.style.marginBottom="70px";
    			}
			else
				{
				i.style.backgroundImage="url('" + baseurl_short + "gfx/interface/infobox_down.png')";
				ii.style.marginTop="70px";
				ii.style.marginBottom="15px";
				}
			}
		}
    
		if (!InfoBoxImageMode) {
			if (parseInt(x)+360 > parseInt(document.getElementById('UICenter').style.width)){
				InfoBoxLeft=parseInt(document.getElementById('UICenter').style.width)-380;}
		} /*else {  // this doesn't really work for image mode, the side scroll is not breaking anything.
			if (parseInt(x)+390 > parseInt(document.getElementById('UICenter').style.width)){
				InfoBoxLeft=parseInt(document.getElementById('UICenter').style.width)-415;
			}
		}*/
    i.style.top=InfoBoxTop + "px";
   	i.style.left=InfoBoxLeft + "px";
		
	// set a timer for the infobox to appear
    if ((InfoBoxWaiting==false) && (InfoBoxVisible==false))
    	{
    	if (InfoBoxTimer) {window.clearTimeout(InfoBoxTimer);}
    	InfoBoxTimer=window.setTimeout("InfoBoxAppear('" + iname + "', '" + iiname + "', " + iscollection + ", '"+x+"')",800);
	    InfoBoxWaiting=true;
		}
    }

function InfoBoxSetResource(ref)
	{
	InfoBoxRef=ref;
	if (ref==0)
		{
		InfoBoxVisible=false;
		if (ib=document.getElementById('InfoBox')) {ib.style.display='none';}
		if (ib=document.getElementById('InfoBoxCollection')) {ib.style.display='none';}
		}
	}
	
function InfoBoxAppear(boxName, innerBoxName, iscollection,x)
	{
	if (!InfoBoxEnabled) {return false;}
	// Make sure we are still waiting for a box to appear and that the mouse has not yet moved.
	if ((InfoBoxWaiting) && (InfoBoxRef!=0))
		{
		var i=document.getElementById(boxName);

		//Ajax loader here
    	document.getElementById(innerBoxName).innerHTML='';
    	jQuery.ajax({
			success:function (data){jQuery('#' + innerBoxName).html(data);},
			url:baseurl_short + 'pages/ajax/infobox_loader.php?ref=' + InfoBoxRef + ((InfoBoxImageMode && !iscollection)?'&image=true':'')
		});
	   	i.style.display='block';
        if (!InfoBoxImageMode) {jQuery(i).fadeTo(0,0.9);
			if (parseInt(x)+360 > parseInt(document.getElementById('UICenter').style.width)){
				InfoBoxLeft=parseInt(document.getElementById('UICenter').style.width)-380;}
		} /*else {
			if (parseInt(x)+390 > parseInt(document.getElementById('UICenter').style.width)){
				InfoBoxLeft=parseInt(document.getElementById('UICenter').style.width)-415;
			}
		}*/
	    i.style.top=InfoBoxTop + "px";
    	i.style.left=InfoBoxLeft + "px";
    	
    	InfoBoxVisible=true;
    	}
    InfoBoxWaiting=false;
	}
