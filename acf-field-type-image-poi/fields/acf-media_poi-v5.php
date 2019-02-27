<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_media_poi') ) :


class acf_field_media_poi extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct( $settings ) {
		
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		
		$this->name = 'media_poi';
		
		
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		
		$this->label = __('POI sur images', 'acf-media_poi');
		
		
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		
		$this->category = 'content';
		
		
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		
		$this->defaults = array(
			'minimum' => 0,
			'maximum' => 0,
		);
		
		
		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('media_poi', 'error');
		*/
		
		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'acf-media_poi'),
		);
		
		
		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/
		
		$this->settings = $settings;
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum','acf-media_poi'),
			'type'			=> 'number',
			'name'			=> 'minimum',
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum','acf-media_poi'),
			'type'			=> 'number',
			'name'			=> 'minimum',
		));

		acf_render_field_setting( $field, array(
            'label'         => __('Preview Size','acf'),
            'instructions'  => __('Shown when entering data','acf'),
            'type'          => 'select',
            'name'          => 'preview_size',
            'choices'       =>  acf_get_image_sizes()
        ));

        acf_render_field_setting( $field, array(
            'label'         => __('Return Value','acf'),
            'instructions'  => __('Specify the returned value on front end','acf'),
            'type'          => 'radio',
            'name'          => 'save_format',
            'layout'        => 'horizontal',
            'class'         =>  'return-value-select',
            'choices'       => array(
                'object'         => __("Image Array",'acf'),
                'url'           => __("Image URL",'acf'),
                'id'            => __("Image ID",'acf')
            )
        ));

        acf_render_field_setting( $field, array(
            'label'         => __('Library','acf'),
            'instructions'  => __('Limit the media library choice','acf'),
            'type'          => 'radio',
            'name'          => 'library',
            'layout'        => 'horizontal',
            'choices'       => array(
                'all'           => __('All', 'acf'),
                'uploadedTo'    => __('Uploaded to post', 'acf')
            )
        ));


	}
	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {

		acf_enqueue_uploader();


		$imageData = $this->get_image_data($field);

		$fieldValues = json_decode($field['value']);

		$url = '';
        $orignialImage = null;

        $width = 0;
        $height = 0;

        if($imageData->original_image){
            $originalImage = wp_get_attachment_image_src($imageData->original_image, 'full');
            $url = $imageData->preview_image_url;
        }

		// vars
		$div_atts = array(
            'class'                 => 'acf-image-uploader acf-cf acf-image-poi',
            'data-field-settings'   => json_encode($field),
            'data-width'            => $width,
            'data-height'           => $height,
            'data-preview_size'     => $field['preview_size'],
            'data-library'          => $field['library']
        );
		$input_atts = array(
            'type'                  => 'hidden',
            'name'                  => $field['name'],
            'value'                 => htmlspecialchars($field['value']),
            'data-name'             => 'id',
            'data-original-image'   => $imageData->original_image,
            'class'                 => 'acf-image-value'
        );
		

		// has value ??? 
        if($imageData->original_image){
            $url = $imageData->preview_image_url;
            $div_atts['class'] .= ' has-value';
        }

		?>
		<div <?php acf_esc_attr_e( $div_atts ); ?>>
		    <div class="acf-hidden bl-image-poi-input">
		        <input <?php acf_esc_attr_e( $input_atts ); ?>/>
		    </div>
		    <div class="view show-if-value acf-soh bl-image-poi">
		        <ul class="acf-hl acf-soh-target">
		            <li><a class="acf-icon -pencil dark" data-name="edit" href="#"><i class="acf-sprite-edit"></i></a></li>
		            <li><a class="acf-icon -cancel dark" data-name="remove" href="#"><i class="acf-sprite-delete"></i></a></li>
		        </ul>
		        <img data-name="image" src="<?php echo $url; ?>" alt=""/>
				<?php
					if(!empty((array)$fieldValues->pins[0])):
					foreach ($fieldValues->pins as $pin):
				?>
						<span class="bl-poi-pin" style="left: <?php echo $pin->left; ?>; top: <?php echo $pin->top; ?>">
							<input type="text" value="<?php echo $pin->val; ?>"/>
							<span>Supprimer</span>
						</span>
				<?php
					endforeach;
					endif;
				?>
		    </div>
		    <div class="view hide-if-value">
		        <p><?php _e('No image selected','acf'); ?> <a data-name="add" class="acf-button button" href="#"><?php _e('Add Image','acf'); ?></a></p>
		    </div>
		</div>
		<?php
	}



	function get_image_data($field){
        $imageData = new stdClass();
        $imageData->original_image = '';
        $imageData->original_image_width = '';
        $imageData->original_image_height = '';
        $imageData->original_image_url = '';
        $imageData->preview_image_url = '';
        $imageData->image_url = '';

        if($field['value'] == ''){
            // Field has not yet been saved or is an empty image field
            return $imageData;
        }

        $data = json_decode($field['value']);

        if(!is_object($data)){
            // Field was saved as a regular image field
            $imageAtts = wp_get_attachment_image_src($data->value, 'full');
            $imageData->original_image = $data->value;
            $imageData->original_image_width = $imageAtts[1];
            $imageData->original_image_height = $imageAtts[2];
            $imageData->preview_image_url = $this->get_image_src($data->value, $field['preview_size']);
            $imageData->image_url = $this->get_image_src($data->value, 'full');
            $imageData->original_image_url = $imageData->image_url;
            return $imageData;
        }

        // By now, we have at least a saved original image
        $imageAtts = wp_get_attachment_image_src($data->value, 'full');
        $imageData->original_image = $data->value;
        $imageData->original_image_width = $imageAtts[1];
        $imageData->original_image_height = $imageAtts[2];
        $imageData->original_image_url = $this->get_image_src($data->value, 'full');

        // Set defaults to original image
        $imageData->image_url = $this->get_image_src($data->value, 'full');
        $imageData->preview_image_url = $this->get_image_src($data->value, $field['preview_size']);

        // Check if there is a cropped version and set appropriate attributes
     
        return $imageData;
    }
	

	function get_image_src($id, $size = 'thumbnail'){
        $atts = wp_get_attachment_image_src( $id, $size);
        return $atts[0];
    }
	
		
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	
	
	function input_admin_enqueue_scripts() {
		
		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		
		// register & include JS
		wp_register_script( 'acf-input-media_poi', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-media_poi');
		
		
		// register & include CSS
		wp_register_style( 'acf-input-media_poi', "{$url}assets/css/input.css", array('acf-input'), $version );
		wp_enqueue_style('acf-input-media_poi');
		
	}
	
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_head() {
	
		
		
	}
	
	*/
	
	
	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/
   	
   	/*
   	
   	function input_form_data( $args ) {
	   	
		
	
   	}
   	
   	*/
	
	
	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_footer() {
	
		
		
	}
	
	*/
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_enqueue_scripts() {
		
	}
	
	*/

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_head() {
	
	}
	
	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	/*
	
	function load_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	*/
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	/*
	
	function update_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	*/
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
		
	
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		$value = json_decode($value);
		
		
		// return
		return $value;
	}
	
	
	
	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/
	
	/*
	
	function validate_value( $valid, $value, $field, $input ){
		
		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}
		
		
		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','acf-media_poi'),
		}
		
		
		// return
		return $valid;
		
	}
	
	*/
	
	
	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/
	
	/*
	
	function delete_value( $post_id, $key ) {
		
		
		
	}
	
	*/
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0	
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function load_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function update_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/
	
	/*
	
	function delete_field( $field ) {
		
		
		
	}	
	
	*/
	
	
}


// initialize
new acf_field_media_poi( $this->settings );


// class_exists check
endif;

?>