function showResponsiveCollection() {
    jQuery('#cover').fadeIn(0);
    jQuery('#responsive_collection_toggle').addClass('slide_btn');
    jQuery('#responsive_collection_toggle a').html(responsive_show);
    jQuery('#CollectionDiv').show(0);
    ShowThumbs();
}
function hideResponsiveCollection() {
    if(!jQuery('#CollectionDiv').length){return false;}
    jQuery('#cover').fadeOut(0);
    jQuery('#responsive_collection_toggle').removeClass('slide_btn');
    jQuery('#responsive_collection_toggle a').html(responsive_hide);
    jQuery('#CollectionDiv').hide(0);
    HideThumbs();
}
function checkResponsiveCollection() {
    thumbs = getCookie("thumbs");
    if(thumbs==="show") {
        showResponsiveCollection();
    } else if(thumbs==="hide") {
        hideResponsiveCollection();
    }
}
function PopCollection(thumbs) {
    if(thumbs == "hide" && jQuery(window).width()<=900) {
        showResponsiveCollection();
        ToggleThumbs();
    }else if(thumbs == "hide" && collections_popout) {
        ToggleThumbs();
    }
}
function responsiveCollectionBar() {
    if(jQuery(window).width()<=900 && !(jQuery('#responsive_collection_toggle').length)) {
        jQuery('#CollectionDiv').hide(0);
        responsive_hide = function() {
            return jQuery('#CollectionMinitems').html();
        }  
        jQuery('#UICenter').before("<div id='cover' style='display: none;'></div>");
        jQuery("#CollectionDiv").before("<div id='responsive_collection_toggle' class='CollectBack'><a class='rotate' href='#'></a></div>"); 
        hideResponsiveCollection();

        jQuery("#responsive_collection_toggle").click(function(event) {
            event.preventDefault();
            if(!jQuery('#responsive_collection_toggle').hasClass('slide_btn')) {
                showResponsiveCollection();        
            } else {  
                hideResponsiveCollection(); 
            } 
        });
        jQuery("#CollectionDiv").on('click','a',function() {
            hideResponsiveCollection();
        });
    }
    else if(jQuery(window).width()<=900 && (jQuery('#responsive_collection_toggle').length)) {
        jQuery("#responsive_collection_toggle").show();
        hideResponsiveCollection();
    }
    else if(jQuery(window).width()>900 && (jQuery('#responsive_collection_toggle').length)) {
        jQuery("#CollectionDiv").show();
        jQuery("#responsive_collection_toggle").hide();
        thumbs = getCookie("thumbs");
        if(thumbs==="show") {
            showResponsiveCollection();
        }else if(thumbs==="hide") {
            showResponsiveCollection();
            HideThumbs();
        } 
    }
}