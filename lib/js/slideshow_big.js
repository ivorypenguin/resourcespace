$( document ).ready(function() {
  
    if (basename(window.location.href).substr(0,8)=="home.php")
        {
        ActivateSlideshow();
        }
  
  });


var SlideshowImages = new Array();
var SlideshowLinks = new Array();
var SlideshowCurrent = -1;
var SlideshowActive=false;
var SlideshowTimer=0;

function RegisterSlideshowImage(image, resource, single_image_flag)
    {
    if(typeof single_image_flag === 'undefined')
        {
        single_image_flag = false;
        }

    // If we are only registering one image then remove any images registered so far
    if(single_image_flag)
        {
        SlideshowImages.length = 0;
        SlideshowLinks.length = 0;
        }

    SlideshowImages.push(image);
    SlideshowLinks.push(resource);
    }

function SlideshowChange()
    {
    if (SlideshowImages.length==0 || !SlideshowActive) {return false;}
    
    SlideshowCurrent++;        
    
    if (SlideshowCurrent>=SlideshowImages.length)
        {
        SlideshowCurrent=0;
        }

    jQuery('#UICenter').css('background-image','url(' + SlideshowImages[SlideshowCurrent] + ')');

    // Preload the next image
    if (SlideshowCurrent<(SlideshowImages.length-1)) // For all slides except the last one.
        {
        // Method to preload an image.
        jQuery("<img />").attr("src", SlideshowImages[SlideshowCurrent+1]);
        }

    var photo_delay = 1000 * big_slideshow_timer;
        
    SlideshowTimer=window.setTimeout('SlideshowChange();', photo_delay);
    
    return true;
    }

function ActivateSlideshow()
    {
    if (!SlideshowActive)
        {
        SlideshowCurrent=-1;
        SlideshowActive=true;
        SlideshowChange();
        
        jQuery('#Footer').hide();
        }
    }
    
function DeactivateSlideshow()
    {
    jQuery('#UICenter').css('background-image','none');
    SlideshowActive=false;
    window.clearTimeout(SlideshowTimer);

    jQuery('#Footer').show();
    }

