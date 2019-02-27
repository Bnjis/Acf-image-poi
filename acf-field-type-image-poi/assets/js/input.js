(function($){

	jQuery.extend({
		stringify  : function stringify(obj) {
			var t = typeof (obj);
			if (t != "object" || obj === null) {
				if (t == "string") obj = '"' + obj + '"';
				return String(obj);
			} else {
				var n, v, json = [], arr = (obj && obj.constructor == Array);

				for (n in obj) {
					v = obj[n];
					t = typeof(v);
					if (obj.hasOwnProperty(n)) {
						if (t == "string") v = '"' + v + '"'; else if (t == "object" && v !== null) v = jQuery.stringify(v);
						json.push((arr ? "" : '"' + n + '":') + String(v));
					}
				}
				return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
			}
		}
	});


	acf.fields.media_poi = acf.field.extend({

        type: 'media_poi',
        $el: null,

        actions: {
            'ready':    'initialize',
            'append':   'initialize'
        },

        events: {
            'click a[data-name="add"]':     'add',
            'click a[data-name="edit"]':    'edit',
            'click a[data-name="remove"]':  'remove',
            'change input[type="file"]':    'change'
        },

        focus: function(){

            // get elements
            this.$el = this.$field.find('.acf-image-uploader');

            // get options
            this.o = acf.get_data( this.$el );


        },

        initialize: function(){
            // add attribute to form
            if( this.o.uploader == 'basic' ) {

                this.$el.closest('form').attr('enctype', 'multipart/form-data');

            }

        },

        add: function() {
            // reference
            var self = this,
                $field = this.$field;


            // get repeater
            var $repeater = acf.get_closest_field( this.$field, 'repeater' );


            // popup
            var frame = acf.media.popup({

                title:      acf._e('image', 'select'),
                mode:       'select',
                type:       'image',
                field:      acf.get_field_key($field),
                multiple:   $repeater.exists(),
                library:    this.o.library,
                mime_types: this.o.mime_types,

                select: function( attachment, i ) {

                    // select / add another image field?
                    if( i > 0 ) {

                        // vars
                        var key = acf.get_field_key( $field ),
                            $tr = $field.closest('.acf-row');


                        // reset field
                        $field = false;


                        // find next image field
                        $tr.nextAll('.acf-row:visible').each(function(){

                            // get next $field
                            $field = acf.get_field( key, $(this) );


                            // bail early if $next was not found
                            if( !$field ) {

                                return;

                            }


                            // bail early if next file uploader has value
                            if( $field.find('.acf-image-uploader.has-value').exists() ) {

                                $field = false;
                                return;

                            }


                            // end loop if $next is found
                            return false;

                        });


                        // add extra row if next is not found
                        if( !$field ) {

                            $tr = acf.fields.repeater.doFocus( $repeater ).add();


                            // bail early if no $tr (maximum rows hit)
                            if( !$tr ) {

                                return false;

                            }


                            // get next $field
                            $field = acf.get_field( key, $tr );

                        }

                    }

                    // focus
                    self.doFocus( $field );


                    // render
                    self.render( self.prepare(attachment) );

                }

            });

        },

        prepare: function( attachment ) {
            // vars
            var image = {
                id:     attachment.id,
                url:    attachment.attributes.url
            };


            // check for preview size
            if( acf.isset(attachment.attributes, 'sizes', this.o.preview_size, 'url') ) {

                image.url = attachment.attributes.sizes[ this.o.preview_size ].url;

            }


            // return
            return image;

        },

        render: function( image ){

        	var imagevalue = '{"value" : '+image.id+', "pins" : [{}]}';

            // set atts
            this.$el.find('[data-name="image"]').attr( 'src', image.url );
            this.$el.find('[data-name="id"]').val( imagevalue ).trigger('change');


            // set div class
            this.$el.addClass('has-value');

        },

        edit: function() {
            // reference
            var self = this;


            // vars
            //var id = this.$el.find('[data-name="id"]').val();

            var id = this.$el.find('.acf-image-value').data('cropped-image');
            if(!$.isNumeric(id)){
                id = this.$el.find('.acf-image-value').data('original-image');;
            }

            // popup
            var frame = acf.media.popup({

                title:      acf._e('image', 'edit'),
                type:       'image',
                button:     acf._e('image', 'update'),
                mode:       'edit',
                id:         id,

                select: function( attachment, i ) {

                    self.render( self.prepare(attachment) );

                }

            });

        },

        remove: function() {

            // vars
            var attachment = {
                id:     '',
                url:    ''
            };


            // add file to field
            this.render( attachment );


            // remove class
            this.$el.removeClass('has-value');

        },

        change: function( e ){


        	var newValue = '{"value" : '+e.$el.val()+', "pins" : [{}]}';

            this.$el.find('[data-name="id"]').val( newValue );

        }

    });

    function showFieldsElements(element){
        element.on('click', function(e){
            e.preventDefault();
            $('.bl-poi-pin').removeClass('opened');
            $(this).addClass('opened');
        })
    }

    // CODE FAIT A L'ARRACHE, A REVOIR POUR SIMPLIFIER TOUS CELA
    // Utiliser des closest
    // revoire les variable pour 

    function majAfterDrag(){
        // Fonction qui sert a rien pour le moment
        var $parent = $('.bl-image-poi');
        var $input = $('.acf-image-poi').find('.bl-image-poi-input input');
        var inputValue = $input.val();
        var inputValueObj = jQuery.parseJSON( inputValue );

        var imgReferece = $parent.find('img');

        var imageWidth = imgReferece.width();
        var imageHeight = imgReferece.height();

         $parent.find('.bl-poi-pin').each(function(i, l){

            var percentLeft = ((parseInt($(this).css('left'))-10) * 100) / imageWidth;
            var percentTop = ((parseInt($(this).css('top'))-10) * 100) / imageHeight;

            inputValueObj.pins[i] = {'val' : $(this).find('input').val(), 'left' : percentLeft+'%', 'top' : percentTop+'%'};
        });

        $input.val(jQuery.stringify(inputValueObj));

    }


	
	
	function initialize_field( $el ) {
		
		//$el.doStuff();
		var $field = $el, $options = $el.find('.acf-image-uploader');



		var $imagePoiContainer = $('.bl-image-poi');
		var pinsArray = [];


		$imagePoiContainer.find('img').on('click', function(e){
			//e.preventDefault();
     
            var $container =  $(this).parent('.bl-image-poi');

			var offset_t = $(this).offset().top - $(window).scrollTop();
		    var offset_l = $(this).offset().left - $(window).scrollLeft();

		    var left = Math.round( (e.clientX - offset_l) );
		    var top = Math.round( (e.clientY - offset_t) );

		    var imageWidth = $(this).width();
		    var imageHeight = $(this).height();

		   	var percentLeft = ((left-10) * 100) / imageWidth;
		   	var percentTop = ((top-10) * 100) / imageHeight;

            var $pin = $('<span class="bl-poi-pin"><input type="text" /><span>Supprimer</span></span>');

		    //$imagePoiContainer.append('<span class="bl-poi-pin"><input type="text" /><span>Supprimer</span></span>');
            $container.append($pin);

            var lastPoi = $container.find('.bl-poi-pin:last');
		    lastPoi.css({'top' : percentTop+'%', 'left' : percentLeft+'%'});
            //$pin.css({'top' : percentTop+'%', 'left' : percentLeft+'%'});

            showFieldsElements(lastPoi);
            lastPoi.draggable({
              containment: "parent",
              drag: function() {
                var $input = $(this).parents('.acf-image-poi').find('.bl-image-poi-input input');
                var inputValue = $input.val();
                var inputValueObj = jQuery.parseJSON( inputValue );

                var imgReferece = $container.find('img');

                var imageWidth = imgReferece.width();
                var imageHeight = imgReferece.height();

                 $container.find('.bl-poi-pin').each(function(i, l){

                    var percentLeft = ((parseInt($(this).css('left'))) * 100) / imageWidth;
                    var percentTop = ((parseInt($(this).css('top'))) * 100) / imageHeight;
                    console.log(percentLeft);
                    console.log(percentTop);

                    inputValueObj.pins[i] = {'val' : $(this).find('input').val(), 'left' : percentLeft+'%', 'top' : percentTop+'%'};
                });

                $input.val(jQuery.stringify(inputValueObj));
              }
            });

		});


		$imagePoiContainer.on('click', '.bl-poi-pin span', function(e){
			e.preventDefault();
            //SUPRESSION


            // A REMPLACER PAR DES CLOSEST 
			var $parent = $(this).parents('.bl-image-poi');

			var $input = $(this).parents('.acf-image-poi').find('.bl-image-poi-input input');
            var inputValue = $input.val();
            var inputValueObj = jQuery.parseJSON( inputValue );

            var blPoiParent = $(this).parent('.bl-poi-pin');

            var pinID = $parent.find('.bl-poi-pin').index(blPoiParent);

            delete inputValueObj.pins[pinID];

            $input.val(jQuery.stringify(inputValueObj));

            $(this).parent('.bl-poi-pin').remove();

		});

        $imagePoiContainer.on('keyup', '.bl-poi-pin input', function(e){
            e.preventDefault();

            var $parent = $(this).parents('.bl-image-poi');

            var $input = $(this).parents('.acf-image-poi').find('.bl-image-poi-input input');
            var inputValue = $input.val();
            var inputValueObj = jQuery.parseJSON( inputValue );

            var imgReferece = $imagePoiContainer.find('img');

            var imageWidth = imgReferece.width();
            var imageHeight = imgReferece.height();
            

            $parent.find('.bl-poi-pin').each(function(i, l){

                var percentLeft = ((parseInt($(this).css('left'))) * 100) / imageWidth;
                var percentTop = ((parseInt($(this).css('top'))) * 100) / imageHeight;

                inputValueObj.pins[i] = {'val' : $(this).find('input').val(), 'left' : percentLeft+'%', 'top' : percentTop+'%'};
            });

            $input.val(jQuery.stringify(inputValueObj));

        });
        
        $('.bl-poi-pin').each(function(){
            showFieldsElements($(this));
        });

        $('.bl-poi-pin').draggable({
          containment: "parent",
          drag: function() {
            var $parent = $(this).parents('.bl-image-poi');
            var $input = $(this).parents('.acf-image-poi').find('.bl-image-poi-input input');
            var inputValue = $input.val();
            var inputValueObj = jQuery.parseJSON( inputValue );

            var imgReferece = $parent.find('img');

            var imageWidth = imgReferece.width();
            var imageHeight = imgReferece.height();

             $parent.find('.bl-poi-pin').each(function(i, l){

                var percentLeft = ((parseInt($(this).css('left'))) * 100) / imageWidth;
                var percentTop = ((parseInt($(this).css('top'))) * 100) / imageHeight;
                console.log(percentLeft);
                console.log(percentTop);

                inputValueObj.pins[i] = {'val' : $(this).find('input').val(), 'left' : percentLeft+'%', 'top' : percentTop+'%'};
            });

            $input.val(jQuery.stringify(inputValueObj));
          }
        });
		
	}

    
	
	
	if( typeof acf.add_action !== 'undefined' ) {
	
		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/
		
		acf.add_action('ready append', function( $el ){
			
			// search $el for fields of type 'FIELD_NAME'
			acf.get_fields({ type : 'media_poi'}, $el).each(function(){
				
				initialize_field( $(this) );
				
			});
			
		});
		
		
	} else {
		
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM. 
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/
		
		$(document).on('acf/setup_fields', function(e, postbox){
			
			$(postbox).find('.field[data-field_type="FIELD_NAME"]').each(function(){
				
				initialize_field( $(this) );
				
			});
		
		});
	
	
	}


})(jQuery);
