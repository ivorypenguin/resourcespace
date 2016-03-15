/// <reference path="jquery-1.2.6-vsdoc.js" />

var button_ok = "";
var button_cancel = "";
var button_delete = "";
var button_add = "";
var button_toggle="";
var button_toggle_off="";
var error_saving = "";
var error_deleting = "";

function isInt(value) { 
    return !isNaN(parseInt(value)) && (parseFloat(value) == parseInt(value)); 
}
(function($) {

    $.fn.annotateImage = function(options) {
        ///	<summary>
        ///		Creates annotations on the given image.
        ///     Images are loaded from the "getUrl" propety passed into the options.
        ///	</summary>
        var opts = $.extend({}, $.fn.annotateImage.defaults, options);
        var image = this;

        this.image = this;
        this.mode = 'view';

        // Assign defaults
        this.getUrl = opts.getUrl;
        this.saveUrl = opts.saveUrl;
        this.deleteUrl = opts.deleteUrl;
        this.editable = opts.editable;
        this.useAjax = opts.useAjax;
        this.notes = opts.notes;
		this.toggle= opts.toggle;
		
        // Add the canvas
        this.canvas = $('<div class="image-annotate-canvas"><div class="image-annotate-view"></div><div class="image-annotate-edit"><div class="image-annotate-edit-area"></div></div></div>');
        this.canvas.children('.image-annotate-edit').hide();
        this.canvas.children('.image-annotate-view').hide();
        this.image.after(this.canvas);

        // Give the canvas and the container their size and background
        this.canvas.height(this.height());
        this.canvas.width(this.width());
        this.canvas.css('background-image', 'url("' + this.attr('src') + '")');
        this.canvas.children('.image-annotate-view, .image-annotate-edit').height(this.height());
        this.canvas.children('.image-annotate-view, .image-annotate-edit').width(this.width());


        // load the notes
        if (this.useAjax) {
            $.fn.annotateImage.ajaxLoad(this);
        } else {
            $.fn.annotateImage.load(this);
        }
        
       /*
        this.canvas.children('.image-annotate-view').hover(function() {image.canvas.css({"overflow":"visible"});
            $(this).show();
        }, function() {image.canvas.css({"overflow":"hidden"});
          if (image.toggle==false){   $(this).hide();}
        });this.canvas.children('.image-annotate-note').hover(function() {image.canvas.css({"overflow":"visible"});
            $(this).show();
        }, function() {image.canvas.css({"overflow":"hidden"});
          if (image.toggle==false){   $(this).hide();}
        });		*/
        
        
        // Add the image actions
        if (this.editable) {
			this.button = $('<div class="image-actions" id="image-actions">');
			this.canvas.append(this.button);$('#image-actions').hide();
			
			            this.button = $('<a class="image-annotate-add" id="image-annotate-add" href="#" style="display:inline;">' + button_add + '</a>');
            this.button.css({width: 'auto', 'padding-right': '5px'});
            this.button.click(function() {
                $.fn.annotateImage.add(image);
                return false;
            });
            $('#image-actions').append(this.button);
			
            this.button = $('<a class="image-annotate-toggle" id="image-annotate-toggle" href="#" style="display:inline;">');
            this.button.css({width: 'auto', 'padding-right': '5px'});this.button.click(function() {
                $.fn.annotateImage.toggle(image);
            }); 
            this.button.css({});
            $('#image-actions').append(this.button);
            $.fn.annotateImage.toggle(image); 
            $.fn.annotateImage.toggle(image);
           
            $('#image-actions').hover(function(){
				toTop('#image-actions');
			});
        }
        
        // Add the behavior: hide/show the notes when hovering the picture
        this.canvas.hover(function() {
			image.canvas.css({"overflow":"visible"});
            if ($(this).children('.image-annotate-edit').css('display') == 'none') {
               $(this).children('.image-annotate-view').show();
               $('#image-actions').show();
               toTop('#image-actions');
            }
        }, function() {
			image.canvas.css({"overflow":"hidden"});
            if (image.toggle==false){   
				$(this).children('.image-annotate-view').hide();
				$(this).children('.image-annotate-note','.image-annotate-toggle').hide();
            }
            $('#image-actions').hide();
        });
        
        // Hide the original
        this.hide();

        return this;
    };

    /**
    * Plugin Defaults
    **/
    $.fn.annotateImage.defaults = {
        getUrl: 'your-get.rails',
        saveUrl: 'your-save.rails',
        deleteUrl: 'your-delete.rails',
        editable: true,
        useAjax: true,
        notes: new Array()
    };

    $.fn.annotateImage.clear = function(image) {
        ///	<summary>
        ///		Clears all existing annotations from the image.
        ///	</summary>    
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]].destroy();
        }
        image.notes = new Array();
    };

    $.fn.annotateImage.ajaxLoad = function(image) {
        ///	<summary>
        ///		Loads the annotations from the "getUrl" property passed in on the
        ///     options object.
        ///	</summary>
        var append_separator = '?';
        
        if(image.getUrl.indexOf('?') > -1) {
            append_separator = '&';
        }
        
        $.getJSON(image.getUrl + append_separator + 'ticks=' + $.fn.annotateImage.getTicks(), function(data) {
            image.notes = data;
            $.fn.annotateImage.load(image);
        });
    };

    $.fn.annotateImage.load = function(image) {
        ///	<summary>
        ///		Loads the annotations from the notes property passed in on the
        ///     options object.
        ///	</summary>
        for (var i = 0; i < image.notes.length; i++) {
            image.notes[image.notes[i]] = new $.fn.annotateView(image, image.notes[i]);
        }  
        // load the default toggle
        if (image.toggle==true){
			$('.image-annotate-view').show();
            $('.image-annotate-note').show();
        }
    };

    $.fn.annotateImage.getTicks = function() {
        ///	<summary>
        ///		Gets a count og the ticks for the current date.
        ///     This is used to ensure that URLs are always unique and not cached by the browser.
        ///	</summary>        
        var now = new Date();
        return now.getTime();
    };

    $.fn.annotateImage.add = function(image) {
        ///	<summary>
        ///		Adds a note to the image.
        ///	</summary>        
        if (image.mode == 'view') {
            image.mode = 'edit';
            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(image);

            $.fn.annotateImage.createSaveButton(editable, image);
            $.fn.annotateImage.createCancelButton(editable, image);
        }
    };
    
    $.fn.annotateImage.toggle = function(image) {
        ///	<summary>
        ///		Toggle notes
        ///	</summary>        
		if (image.mode == 'view') {
			if (image.toggle==false){
				image.toggle=true;
				$('#image-annotate-toggle').html('<a class="image-annotate-toggle" id="image-annotate-toggle" href="#" onclick="return false;">' + button_toggle_off + '</a>');
				SetCookie ('annotate_toggle',true);
				$('.image-annotate-view').show();
				$('.image-annotate-note').show();
			} else {
				image.toggle=false;
				$('#image-annotate-toggle').html('<a class="image-annotate-toggle" id="image-annotate-toggle" href="#" onclick="return false;">' + button_toggle + '</a>');
				SetCookie ('annotate_toggle',false);
                $('.image-annotate-view').hide();
			    $('.image-annotate-note').hide();
		    }
       }
    };

    $.fn.annotateImage.createSaveButton = function(editable, image, note) {
        ///	<summary>
        ///		Creates a Save button on the editable note.
        ///	</summary>
        var ok = $('<a class="image-annotate-edit-ok">' + button_ok + '</a>');

        ok.click(function() {
            var form = $('#image-annotate-edit-form form');
            var text = $('#image-annotate-text').val();
           
            $.fn.annotateImage.appendPosition(form, editable)
            image.mode = 'view';

            // Save via AJAX
            if (image.useAjax) {
                $.ajax({
                    url: image.saveUrl,
                    data: form.serialize(),
                    error: function(e) { alert(error_saving) },
                    success: function(data) {
				if (data != undefined) {
					editable.note.id = data;
				}
		    },
                    dataType: "json"
                });
            }

            // Add to canvas
            if (note) {
                note.resetPosition(editable, text);
            } else {
                editable.note.editable = true;
                note = new $.fn.annotateView(image, editable.note)
                note.resetPosition(editable, text);
                image.notes.push(editable.note);
            }

            editable.destroy();					
            if (image.toggle==true){$('.image-annotate-view').show();$('.image-annotate-note').show();
            
            
            }
        });
        editable.form.append(ok);
        
    };

    $.fn.annotateImage.createCancelButton = function(editable, image) {
        ///	<summary>
        ///		Creates a Cancel button on the editable note.
        ///	</summary>
        var cancel = $('<a class="image-annotate-edit-close">' + button_cancel + '</a>');
        cancel.click(function() {
            editable.destroy();
            image.mode = 'view';
        });
        editable.form.append(cancel);
    };

    $.fn.annotateImage.saveAsHtml = function(image, target) {
        var element = $(target);
        var html = "";
        for (var i = 0; i < image.notes.length; i++) {
            html += $.fn.annotateImage.createHiddenField("text_" + i, image.notes[i].text);
            html += $.fn.annotateImage.createHiddenField("top_" + i, image.notes[i].top);
            html += $.fn.annotateImage.createHiddenField("left_" + i, image.notes[i].left);
            html += $.fn.annotateImage.createHiddenField("height_" + i, image.notes[i].height);
            html += $.fn.annotateImage.createHiddenField("width_" + i, image.notes[i].width);
        }
        element.html(html);
    };

    $.fn.annotateImage.createHiddenField = function(name, value) {
        return '&lt;input type="hidden" name="' + name + '" value="' + value + '" /&gt;<br />';
    };

    $.fn.annotateEdit = function(image, note) {
        ///	<summary>
        ///		Defines an editable annotation area.
        ///	</summary>
        this.image = image;
        console.log(image);
		var top=30;
		var left=30;
        if (note) {
            this.note = note;
        } else {
            var newNote = new Object();
           
           // check if there is an identical note position, and offset it.
           for (var x=0;x<image.notes.length;x++) {
			  if(typeof image.notes[x]['left']!=="undefined") {
			
				if (image.notes[x]['left']==left && image.notes[x]['top']==top){ 
					top=top+15;
					left=left+15;
					x=0;// reset the loop to find further overlaps recursively
				}
			  } 
			}
			
            newNote.id = "new";
            newNote.top = top;
            newNote.left = left;
            newNote.width = 30;
            newNote.height = 30;
            newNote.text = "";
            this.note = newNote;
        }

        // Set area
        var area = image.canvas.children('.image-annotate-edit').children('.image-annotate-edit-area');
        this.area = area;
        this.area.css('height', this.note.height + 'px');
        this.area.css('width', this.note.width + 'px');
        this.area.css('left', this.note.left + 'px');
        this.area.css('top', this.note.top + 'px');

        // Show the edition canvas and hide the view canvas
        $('#image-actions').hide();
        image.canvas.children('.image-annotate-view').hide();
        image.canvas.children('.image-annotate-edit').show();

        // Add the note (which we'll load with the form afterwards)
        var form = $('<div id="image-annotate-edit-form"><form><textarea id="image-annotate-text" name="text" rows="3" cols="30">' + this.note.text.replace(new RegExp('<br />', 'g'), '') + '</textarea></form></div>');
        this.form = form;

		//$('body').append(this.form);
        	
        // Set the area as a draggable/resizable element contained in the image canvas.
        // Would be better to use the containment option for resizable but buggy
		//this.form.css('left', this.area.offset().left + 'px');
        //this.form.css('top', (parseInt(this.area.offset().top) + parseInt(this.area.height()) + 7) + 'px');

		// modified for RS
		image.canvas.after(this.form);	
		this.form.css('left', this.area.offset().left + $('.ui-layout-center').scrollLeft()+ 'px');
        this.form.css('top', (parseInt(this.area.offset().top)+ $('.ui-layout-center').scrollTop() + parseInt(this.area.height()) + 7) + 'px');
		this.form.css('z-index',3);
		//
		
        area.resizable({
            handles: 'all',
            resize: function(e, ui) {
                form.css('left', area.offset().left  + $('.ui-layout-center').scrollLeft()+ 'px');
                form.css('top', (parseInt(area.offset().top) + $('.ui-layout-center').scrollTop() + parseInt(area.height()) + 7) + 'px');
            },
            stop: function(e, ui) {
                form.css('left', area.offset().left + $('.ui-layout-center').scrollLeft() + 'px');
                form.css('top', (parseInt(area.offset().top) + $('.ui-layout-center').scrollTop() + parseInt(area.height()) + 7) + 'px');
            }
        })
        .draggable({
            containment: image.canvas,
            drag: function(e, ui) {
                form.css('left', area.offset().left  + $('.ui-layout-center').scrollLeft()+ 'px');
                form.css('top', (parseInt(area.offset().top) + $('.ui-layout-center').scrollTop() + parseInt(area.height()) + 7) + 'px');
            },
            stop: function(e, ui) {
                form.css('left', area.offset().left + $('.ui-layout-center').scrollLeft() + 'px');
                form.css('top', (parseInt(area.offset().top) + $('.ui-layout-center').scrollTop() + parseInt(area.height()) + 7) + 'px');
            }
        });
        
     
        
        
        
        return this;
    };

    $.fn.annotateEdit.prototype.destroy = function() {
        ///	<summary>
        ///		Destroys an editable annotation area.
        ///	</summary>        
        this.image.canvas.children('.image-annotate-edit').hide();
        this.area.resizable('destroy');
        this.area.draggable('destroy');
        this.area.css('height', '');
        this.area.css('width', '');
        this.area.css('left', '');
        this.area.css('top', '');
        this.form.remove();
    }

    $.fn.annotateView = function(image, note) {
        ///	<summary>
        ///		Defines a annotation area.
        ///	</summary>
        this.image = image;

        this.note = note;

        this.editable = (note.editable && image.editable);

        // Add the area
        this.area = $('<div class="image-annotate-area' + (this.editable ? ' image-annotate-area-editable' : '') + '"><div>');
        image.canvas.children('.image-annotate-view').prepend(this.area);
		
        image.canvas.children('.image-annotate-view').prepend(this.area);
        
        // Add the note
        this.form = $('<div class="image-annotate-note" style="width:auto">' + note.text + '</div>');
        this.form.hide();
        image.canvas.children('.image-annotate-view').append(this.form);
        this.form.children('span.actions').hide();

		

        // Set the position and size of the note
        this.setPosition();

        // Add the behavior: hide/display the note when hovering the area
        var annotation = this;

		// fix z-index when multiple notes are visible
		this.area.hover(function() {
            annotation.show(); 
            toTop(annotation.area);
            toTop(annotation.form);
        }, function() {
			annotation.hide(); 
        });
        this.form.hover(function() {
            annotation.show(); 
            toTop(annotation.area);
            toTop(annotation.form);
        }, function() {
			annotation.hide();
        });
	

        // Edit a note feature
        if (this.editable) {
            var form = this;
            this.area.click(function() {
                form.edit();
            });
            this.form.click(function() {
                form.edit();
            });
        }
    };

    function toTop(element){
		var index_highest = 50;   
			$(".image-annotate-area,.image-annotate-note,.image-actions").each(function() {
				var index_current = parseInt($(this).css("z-index"), 10);
				//console.log(index_current );
				if(index_current > index_highest) {
				index_highest = index_current;
				}
			});
		//console.log('highest '+index_highest);
		$(element).css({'z-index':index_highest+1});
	};

    $.fn.annotateView.prototype.setPosition = function() {
        ///	<summary>
        ///		Sets the position of an annotation.
        ///	</summary>
        this.area.children('div').height((parseInt(this.note.height) - 2) + 'px');
        this.area.children('div').width((parseInt(this.note.width) - 2) + 'px');
        this.area.css('left', (this.note.left) + 'px');
        this.area.css('top', (this.note.top) + 'px');
        this.form.css('left', (this.note.left) + 'px');
        this.form.css('top', (parseInt(this.note.top) + parseInt(this.note.height) + 7) + 'px');
    };

    $.fn.annotateView.prototype.show = function() {
        ///	<summary>
        ///		Highlights the annotation
        ///	</summary>
       this.form.fadeIn(50);
        if (!this.editable) {
            this.area.addClass('image-annotate-area-hover');
        } else {
            this.area.addClass('image-annotate-area-editable-hover');
        }
    };

    $.fn.annotateView.prototype.hide = function() {
        ///	<summary>
        ///		Removes the highlight from the annotation.
        ///	</summary>      
        if (this.image.toggle==false){this.form.fadeOut(50);}
        this.area.removeClass('image-annotate-area-hover');
        this.area.removeClass('image-annotate-area-editable-hover');
    };

    $.fn.annotateView.prototype.destroy = function() {
        ///	<summary>
        ///		Destroys the annotation.
        ///	</summary>      
        this.area.remove();
        this.form.remove();
    }

    $.fn.annotateView.prototype.edit = function() {
        ///	<summary>
        ///		Edits the annotation.
        ///	</summary>      
        if (this.image.mode == 'view') {
            this.image.mode = 'edit';
            var annotation = this;

            // Create/prepare the editable note elements
            var editable = new $.fn.annotateEdit(this.image, this.note);

            $.fn.annotateImage.createSaveButton(editable, this.image, annotation);

            // Add the delete button
            var del = $('<a class="image-annotate-edit-delete">' + button_delete + '</a>');
            del.click(function() {
                var form = $('#image-annotate-edit-form form');

                $.fn.annotateImage.appendPosition(form, editable)

                if (annotation.image.useAjax) {
                    $.ajax({
                        url: annotation.image.deleteUrl,
                        data: form.serialize(),
                        error: function(e) { alert(error_deleting) }
                    });
                    
                }

                annotation.image.mode = 'view';
                editable.destroy();
                annotation.destroy();
            });
            editable.form.append(del);

            $.fn.annotateImage.createCancelButton(editable, this.image);
            
        }
    };

    $.fn.annotateImage.appendPosition = function(form, editable) {
        ///	<summary>
        ///		Appends the annotations coordinates to the given form that is posted to the server.
        ///	</summary>
        var areaFields = $('<input type="hidden" value="' + editable.area.height() + '" name="height"/>' +
                           '<input type="hidden" value="' + editable.area.width() + '" name="width"/>' +
                           '<input type="hidden" value="' + editable.area.position().top + '" name="top"/>' +
                           '<input type="hidden" value="' + editable.area.position().left + '" name="left"/>' +
                           '<input type="hidden" value="' + editable.note.id + '" name="id"/>');
        form.append(areaFields);
    }

    $.fn.annotateView.prototype.resetPosition = function(editable, text) {
        ///	<summary>
        ///		Sets the position of an annotation.
        ///	</summary>
        this.form.html(text.replace(new RegExp('\n', 'g'), '<br />'));
        this.form.hide();

        // Resize
        this.area.children('div').height(editable.area.height() + 'px');
        this.area.children('div').width((editable.area.width() - 2) + 'px');
        this.area.css('left', (editable.area.position().left) + 'px');
        this.area.css('top', (editable.area.position().top) + 'px');
        this.form.css('left', (editable.area.position().left) + 'px');
        this.form.css('top', (parseInt(editable.area.position().top) + parseInt(editable.area.height()) + 7) + 'px');

        // Save new position to note
        this.note.top = editable.area.position().top;
        this.note.left = editable.area.position().left;
        this.note.height = editable.area.height();
        this.note.width = editable.area.width();
        this.note.text = text;
        this.note.id = editable.note.id;
        this.editable = true;
            
        toTop(this.area);
        toTop(this.form);
    };

})(jQuery);
