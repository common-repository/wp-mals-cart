<?php
/*
*
* WP_Malscart_HTML: a class for building and outputting HTML elements
*
* this is a static utility class
*
* we have public methods for building some form element types that are 
* actually several tags in a specific configuration:
* 	text_field
*		radio_field
*		dropdown_field
*		print_text_field
*		print_radio_field
*		print_dropdown_field
*
* and also generic methods:
*		build_tag
*		print_tag
*
*
* the $attributes array is structured thus:
*
* tag: the tag name
* text: whatever the tag is to wrap (only for wrapping tags)
* items: array for complex tags like radio buttons and selects
* attributes:(array)
*	value
* 	style
*	id
*	name
*	class
*	... (any html tag attributes you want)
* desc: used for help text
*
*/

class WP_Malscart_HTML {

	// tags that don't enclose content
	private static $self_closers = array('input','img','hr','br','meta','link');
	
	// reserved shortcode argument names
	private static $standard_atts = array( 'price', 'product','units', 'product[]', 'button_style', 'button_text', 'button_width' );
	
	// this is a list of all the valid hidden fields for a mals cart POST request
	private static $mals_hidden_fields = array( 'userid', 'product[]', 'price', 'discountpr', 'productpr', 'qty', 'return', 'units', 'lg', 'currency', 'tax', 'scode', 'coupon', 'sd', 'thumb', 'producturl', 'noship', 'noqty' );
	
	private static $buy_button = true;

	// CSS class namespacer	
	public static $base_class = WP_Malscart::PLUGIN_NAME;
	
	/* ===============================================   
	 * TOP-LEVEL OUTPUT FUNCTIONS
	 *
	 * outputs HTML element complex groups
	 */
	
	/*
	* this is the shortcode function for outputting a button of any style
	* 
	*/
	public function buy_button( $atts ) {
	
		//error_log( __METHOD__.' called with:'.print_r($atts,true));
		
		global $post;
		
		self::$buy_button = true;// this is a buy button, not an add button
		
		$atts = self::process_shortcode_atts( $atts );
		
		$atts = wp_parse_args( $atts, WP_Malscart::$product_form_fields );
		
		if ( is_object( $post ) ) $atts[ 'return' ] = get_bloginfo( 'url' ).'/'.$post->post_name;
		
		else $atts[ 'return' ] = get_bloginfo( 'url' );

		return self::buy_button_html( $atts );		

	}

	/**
	 * outputs a buy button
	 *
	 * all the formats for the button styles are here
	 * unless we come up with something more elegant, this will be a series of cases with procedural builds for each one
	 */
	public function buy_button_html( $atts ) {
		
		$html = array();	

		//error_log( __METHOD__.' called with:'.print_r($atts,true));
		
		switch ( $atts[ 'button_style' ] ) :
			case 1 :
				$html[] = self::buy_button_form_head( $atts );
				$html[] = '<div class="'.self::$base_class.' '.self::$base_class.'-button-'.$atts[ 'button_style' ].'" style="width:'.$atts[ 'button_width' ].'" >';
				if ( isset( $atts[ 'product_options' ] ) ) $html[] = self::build_options_element( $atts[ 'product_options' ] );
				$html[] = self::buy_button_submit( $atts );
				$html[] = '</div>';
				$html[] = self::buy_button_form_end( $atts );
				break;
			case 2 :
				$html[] = self::buy_button_form_head( $atts );
				$html[] = '<div class="'.self::$base_class.' '.self::$base_class.'-button-'.$atts[ 'button_style' ].'" style="width:'.$atts[ 'button_width' ].'" >';
				$html[] = '<h4>'.$atts[ 'product[]' ].'<br/>$'.$atts[ 'price' ].'</h4>';
				if ( isset( $atts[ 'product_options' ] ) ) $html[] = self::build_options_element( $atts[ 'product_options' ] );
				$html[] = self::buy_button_submit( $atts );
				$html[] = '</div>';
				$html[] = self::buy_button_form_end( $atts );
				break;
			default :
				$html[] = '';
				error_log( __METHOD__.': invalid style selection: '.$atts[ 'button_style' ] );
		endswitch;
	
		//error_log( __METHOD__.': output array:'.htmlentities(print_r( $html,true )));
		return self::output( $html );

	}
	
	/**
	 * prints a "view cart" button from a widget
	 *
	 * @params array $args complex data array provided by MasterWidget class
	 */
	public function show_cart_button_widget( $args ) {
		
		//error_log( __METHOD__.' args='.print_r( $args,true));
		
		global $post;
		
		$atts = $args[ 'params' ];
		
		if ( $atts[ 'auto_hide' ] && WP_Malscart::$cart_quantity == 0 ) {
			
			echo '<style type="text/css">aside[id^="wp-mals-cart-view-cart"]{display:none}</style>';
			
		}
		
		$server = WP_Malscart::get_option( 'cart_server' );
		$atts[ 'userid' ] = WP_Malscart::get_option( 'cust_id' );
		
		$button_text = empty( $atts[ 'button_text' ] ) ? 'View Cart' : $atts[ 'button_text' ];
		
		if ( is_object( $post ) ) $atts[ 'return' ] = get_bloginfo( 'url' ).'/'.$post->post_name;
		
		// we add the filename explicitly so the return POST will be accepted by the web server
		else $atts[ 'return' ] = get_bloginfo( 'url' ).'/index.php';
		
		$html = array();
		
		$html[] = '<form action="'.$server.'/cf/review.cfm" method="post">';
		
		$html[] = self::add_hidden_fields( $atts );
		
		if ( ! empty( $atts[ 'extra_text' ] ) )
			$html[] = '<p class="'.self::$base_class.'-view-cart-text" >'.$atts[ 'extra_text' ].'</p>';
		
		$html[] = '<input type="submit" value="'.$button_text.'">';
		
		echo self::output( $html );
		
	}
	
	/**
	 * prints selected return fields after a purchase is made
	 *
	 * expecting a shortcode with one argument: 'show'; will fall back to show='first_name'
	 *
	 * @param array $args the shortcode arguments
	 *
	 * @return string the named field from the POST array
	 */
	 public function purchase_return( $args ) {
		 
		 // maps the shortcode attributes to incoming POST array fields
		 $fields = array(
										'name'				=> $_POST[ 'inv_name' ],
										'first_name'	=> self::first_name( $_POST[ 'inv_name' ] ),
										'last_name'		=> self::last_name( $_POST[ 'inv_name' ] ),
										'email'				=> $_POST[ 'email' ]
										);
		 
		 // tries to show the field they asked for; otherwise just shows the first name
		 $display = isset( $args[ 'show' ] ) && isset( $fields[ $args[ 'show' ] ] ) ? $fields[ $args[ 'show' ] ] : $fields[ 'first_name' ];

		return self::output( $display );	

	}
	
	/* ======================================================================
	 * DISPLAY MESSAGES
	 *
	 * process and display various messages to be displayed to site visitors
	 * includes the ability to use placeholders for a simple templating system
	 */
	
	/**
	 * prints a message to a visitor returning after making a purchase
	 *
	 * called with shortcode [purchase_return_message]
	 *
	 * @return string HTML
	 */
	public function purchase_return_message() {
	
		return self::print_message( WP_Malscart::get_option( 'purchase_return_message' ) );	

	}
	
	/**
	 * prints a message to a visitor returning after making a purchase
	 *
	 * called with shortcode [mail_payment_return_message]
	 *
	 * @return string HTML
	 */
	public function mail_payment_return_message() {
	
		return self::print_message( WP_Malscart::get_option( 'mail_payment_return_message' ) );	

	}
	
	/**
	 * prints a message with placeholder replacement and WP content filtering
	 *
	 * @param string $text the raw string with placeholders
	 * @return displayable HTML string
	 * @static
	 */
	 private function print_message( $text ) {
	
		return apply_filters( 'the_content', self::replace_placeholders( $text, self::map_cart_variables() ) );
		 
	 }

	/**
	 * replaces text placeholders with corresponding array elements
	 *
	 * any text can be safely processed though this function, whether it has 
	 * placeholders or not
	 *
	 * @param string $text the text containing placeholder tags: {key_string}
	 * @param array $replacements key_string=>replace_string
	 *
	 * @return string text with string replacements made
	 * @static
	 */
	private function replace_placeholders( $text, $replacements ) {

		if ( ! is_array( $replacements ) ) return '';

		// text keys to replace
		$placeholders = array();

		// sprintf variables
		$variables = array();

		// new strings to replace sprintf variables
		$new_strings = array();

		$i = 1;

		// build the three arrays
		foreach( $replacements as $key => $value ) {

			$placeholders[] = '{'.$key.'}';
			$variables[] = '%'.$i.'$s';
			$new_strings[] = $value;

			$i++;

		}

		/*
		 * the str_replace func builds the sprintf pattern string by replacing all the 
		 * placeholder tags with sprintf variables, which are then replaced with the 
		 * corresponding values in new_strings by the vsprintf func
		 */
		return vsprintf( str_replace( $placeholders, $variables, $text ), $new_strings );
			
	}
	
	/**
	 * maps some cart return POST array elements to an array indexed by placeholder
	 * keys
	 *
	 * @return array cart return key => placeholder key
	 * @static
	 */
	private function map_cart_variables() {

		if ( ! isset( $_POST[ 'username' ] ) ) return false;
		
		$placeholder_keys = array( 'name', 'first_name', 'last_name', 'cart_total', 'email', 'payment_method', 'purchase_id' );

		$map = array();

		foreach( $placeholder_keys as $key ) {

			switch ( $key ) {

				case 'name':
					$map[ $key ] = $_POST[ 'inv_name' ];
					break;
				case 'first_name':
					$map[ $key ] = self::first_name( $_POST[ 'inv_name' ] );
					break;
				case 'last_name':
					$map[ $key ] = self::last_name( $_POST[ 'inv_name' ] );
					break;
				case 'cart_total':
					$map[ $key ] = $_POST[ 'total' ];
					break;
				case 'payment_method':
					$map[ $key ] = $_POST[ 'method' ];
					break;
				case 'purchase_id':
					$map[ $key ] = $_POST[ 'id' ];
					break;
				default:
					$map[ $key ] = $_POST[ $key ];

			}
		}

		return $map;

	}
	
	/* ==========================================
	 * ELEMENT OUTPUT FUNCTIONS
	 *
	 * outputs HTML elements or element groups
	 */
	
	public function build_tag( $atts ) {
		return self::build( $atts );
	}
	
	public function print_tag( $atts ) {
		echo self::build( $atts );
	}
	
	public function print_radio_field( $atts ) {
		echo self::radio_field( $atts );
	}
	
	public function print_text_field( $atts ) {
		echo self::text_field( $atts );
	}
	
	public function print_dropdown_field( $atts ) {
		echo self::dropdown_field( $atts );
	}
	
	// builds a text form field
	public function text_field( $atts ) {

		$atts[ 'tag' ] = 'input';
		$atts[ 'type' ] = 'text';

		if ( isset( $atts[ 'text' ] ) && ! empty( $atts[ 'text' ] ) ) // if we have text, set it as the label
			return self::build( array( 'tag'=>'label', 'text'=>$atts[ 'text' ].self::build( $atts ) ) ).self::field_help_message( $atts[ 'desc' ] );

		else return self::build( $atts ).self::field_help_message( $atts[ 'desc' ] );

	}
	
	// build a textarea form field
	public function textarea_field( $atts ) {

		$atts[ 'tag' ] = 'textarea';
		$atts[ 'type' ] = 'textarea';
		$atts[ 'text' ] = $atts[ 'value' ];
		unset( $atts[ 'value' ] );

		return self::build( $atts ).self::field_help_message( $atts[ 'desc' ] );

	}
	
	/*
	* builds a radio button element (series of same-named checkboxes) 
	*/
	public function radio_field( $atts ) {
		
		$atts[ 'tag' ] = 'input';
		$atts[ 'type' ] = 'radio';
		
		$html = array();
		
		foreach( $atts['items'] as $label=>$element_value ) {
			$element_atts = $atts;
			if ( $atts[ 'value' ] == $element_value ) $element_atts[ 'checked' ] = 'checked';
			$element_atts[ 'value' ] = $element_value;
			$element_atts[ 'text' ] = $label;
			$html[] = self::build( array( 'tag'=>'label', 'text'=>self::build( $element_atts ).$label.'<br/>' ) );
		}

		return self::output( $html );
	}

	/*
	*
	* builds a dropdown field element, a series of option elements wrapped in a 
	* select tag
	*
	* expects a name for the field and an array of options in $label => $value format
	*
	*/
	public function  dropdown_field( $atts ) {

		// builds an array of option elements
		$option_fields = array();
		foreach( $atts['items'] as $label => $element_value ) {
			$element_atts = $atts;
			if ( $atts[ 'value' ] == $element_value ) $element_atts[ 'selected' ] = 'selected';
			$element_atts[ 'value' ] = $element_value;
			$element_atts[ 'text' ] = $label;
			$element_atts[ 'tag' ] = 'option';
			$option_fields[] = self::build( $element_atts );
		}
		
		// builds the select element and inserts the option fields into it
		$atts[ 'tag' ] = 'select';
		$atts[ 'text' ] = self::output( $option_fields );
		return self::build( $atts );

	}
	
/**
* build the product attributes element for a buy button
*
* the first one will be the selected one (unless we decide to allow the 
* shortcode to determine that)
*
* the buy_button flag sets the options to use the products field instead of a 
* separate field
*
*/
	private function build_options_element( Array $product_options ) {
		
		switch ( WP_Malscart::get_option( 'selector_preference' ) ) {
			
			case 'select':
				$element_function = 'dropdown_field';
				break;
			case 'radio':
			default :
				$element_function = 'radio_field';
				
		}
		
		$html = array();
		
		foreach( $product_options as $product_option => $attributes ) {
		
			$options_html = array();
			
			// title for the options set
			$options_html[] = self::build( array( 
												'tag'=>'p',
												'class'=>'option-title',
												'text'=> $attributes[ 'name' ] 
												) );

			// options select elements
			$options_html[] = self::$element_function( array(
												'title' => $attributes[ 'name' ],
												'name' => self::$buy_button ? 'product[]' : $product_option,
												'value' => current( $attributes[ 'options' ] ),
												'items' => self::values_to_keys( $attributes[ 'options' ] )
												) );

			// wrapper for the set
			$html[] = self::build( array( 
												'tag' => 'div',
												'class' => 'product-options product-options-'.$product_option,
												'text' => self::output( $options_html )
												) );

		}// product options
		
		return self::output( $html );
		
	}
	
/**
* utility to take an indexed array and make it of the form value => value
*
* @return array
*/
	private function values_to_keys( Array $array ) {
		
		if ( self::isAssoc( $array ) ) return $array;
		
		$assoc_array = array();
		
		foreach( $array as $element )
			$assoc_array[ $element ] = $element;
			
		return $assoc_array;
		
	}
	
	/**
	 * builds the the form open and the hidden fields for a buy button
	 *
	 * @param array $atts contains all the attribute values passed by the shortcode 
	 *                    function
	 * @return string HTML
	 */
	private function buy_button_form_head( $atts ) {
		
		if (@$atts[ 'demo' ] ) return ''; // skip the header when just showing the style	
		
		$server = WP_Malscart::get_option( 'cart_server' );
		$atts[ 'userid' ] = WP_Malscart::get_option( 'cust_id' );
		$button_text = $atts['button_text'];
		
		$html = array();
		
		$html[] = '<form action="'.$server.'/cf/add.cfm" method="post">';
		
		$html[] = self::add_hidden_fields( $atts );
		
		return self::output( $html );

	}
	
	/**
	 * builds the hidden fields for a mals cart form submit
	 *
	 * @param array $atts contains all the attribute values passed by the shortcode 
	 *                    function
	 */
	private function add_hidden_fields( array $atts ) {
		
		$html = array();
		
		// only use the valid fields
		foreach( self::$mals_hidden_fields as $valid_field ) {
			
			if ( isset( $atts[ $valid_field ] ) ) {
				
				$html[] = '<input name="'.$valid_field.'" type="hidden" value="'.$atts[ $valid_field ].'">';
				
			}
			
		}
			
		return self::output( $html );

	}
	
	/**
	 * returns a form end tag
	 *
	 * @param array the shortcode attributes
	 * @return string HTML
	 */
	private function buy_button_form_end( $atts ) {

		return @$atts[ 'demo' ] ? '' : '</form>';

	}
	
	/**
	 * returns the submit button for a buy button
	 *
	 * @param array the shortcode attributes
	 * @return string HTML
	 */
	private function buy_button_submit( $atts ) {
				
		$html = array();
		
		$html[] = '<input type="submit" ';
		$html[] = @$atts[ 'demo' ] ? 'onclick="return false;" ' : '';
		$html[] = ' value="' . $atts[ 'button_text' ].'">';
		
		return self::output( $html );
	
	}
	
	/**
	* processes the plugin shortcode arguments
	* mainly getting any product attributes and making them into arrays
	*
	* product options are expected to be in the form: 
	* 'Option Name:Option 1,Option 2,Option 3'
	*
	* @params array $arguments the arguments included in the shortcode
	* @return array with keys altered, values validated or altered, product options 
	*               broken out into an array
	*/
	private function process_shortcode_atts( $arguments ) {
		
		// if the shorcode was called with nothing, use the default values
		if ( ! is_array( $arguments ) ) return array();
		
		$processed_atts = array();
		
		// this is so the shortcode can use the argument 'product'; we change it to the bracket form
		if ( isset( $arguments[ 'product' ] ) ) {
			$arguments[ 'product[]' ] =  $arguments[ 'product' ];
		}
		if ( isset( $arguments[ 'width' ] ) ) {
			$arguments[ 'button_width' ] = $arguments[ 'width' ];
		}
		$atts[ 'button_text' ] = isset( $atts[ 'button_text' ] ) ? $atts[ 'button_text' ] : WP_Malscart::get_option( 'button_text' );
		
		// transfer, process and get rid of reserved arguments, leaving only user arguments
		foreach( self::$standard_atts as $attribute ) {
			if ( isset( $arguments[ $attribute ] ) ) {

				// any processing of specific arguments can go here
				switch ( $attribute ) {

					case 'price' :

						// remove any denominations, commas or spaces added by the user.
						$number = preg_match( "|([0-9\.]+)|", str_replace( array( ',', ' '), '', $arguments[ $attribute ] ), $matches );
						$arguments[ $attribute ] = $matches[1];
						break;
						

					default:

				}

				$processed_atts[ $attribute ] = $arguments[ $attribute ];
				unset( $arguments[ $attribute ] );
			}
		}
			
		// process remaining attributes, looking for product options
		// 
		$processed_atts[ 'product_options' ] = array();

		foreach ( $arguments as $argument => $value ) :

			// since we don't know what product attribute name a shortcode will have
			// we look for one that has the proper formatting and create the product
			// attribute array with it
		
			// check the argument and capture the values
			if ( preg_match( "|([^:]+):(.+)|", $value, $matches ) > 0 ) {
	
				$name = $matches[1];
	
				// now get the option names
				$option_names = explode( ',', $matches[2] );
	
				$processed_atts[ 'product_options' ][ $argument ] = array(
																							'name' => $name,
																							'options' => $option_names
																							);

			}

		endforeach;
		
		//error_log( __METHOD__.' atts='.print_r( $processed_atts, true ));
		
		return $processed_atts;
		
	}
	
	/**
	* builds the HTML string for output
	*
	* given an associative array of arguments, this function returns an HTML text 
	* string
	*
	* @param array $args
	*	tag	the html tag to build
	*	text	the string to wrap the tag around
	*	class	the class attribute for the element
	*	...	any HTML element attribute
	*
	*/	
	private function build( $args ) {
		
		$atts = self::build_element_attributes( $args );
		
		$text = isset( $atts[ 'text' ] ) ? $atts[ 'text' ] : '';
		$tag = $atts[ 'tag' ];
		$attributes = implode( ' ', $atts[ 'attributes' ] ); 

		if ( in_array( $tag, self::$self_closers	) )
			$pattern = '<%1$s %2$s />'; // self-closers don't wrap anything
		else $pattern = '<%1$s %2$s >%3$s</%1$s>';

		return sprintf( $pattern, $tag, $attributes, $text );

	}
	
	/**	
	* builds an attribute array for HTML elements and then processes the array into 
	* strings to build the HTML element with
	*
	* @param array of elements attributes
	* @return array with HTML element attributes in subarray
	*/
	private function build_element_attributes( Array $attributes_array ) {

		$atts = array( 'attributes' => array() );// this is the blank array we will fill

		foreach( $attributes_array as $attribute => $value ) {
			
			switch ( $attribute ) {
				
				case 'tag':
				case 'button_style':
				case 'demo':
				case 'text':
				case 'items':
						if ( $attribute == 'items' && ! is_array( $value ) ) break;// this must be array or nothing
				case 'desc':
					// everything above falls through to here if valid
					$atts[ $attribute ] = $value;
					break;
					
				// the following all get dropped into the attributes subarray
				case 'button_text':
					$atts[ 'attributes' ][ 'value' ] = ' value="'.$value.'" ';
					break;
				case 'class':
					$value = self::make_class_string( $value );
				case 'id':
				case 'name':
				case 'value':
				case 'style':
				default: // and any other attrubutes
				
					// make the attribute into a string to add to the tag as it's built
					$atts[ 'attributes' ][ $attribute ] = ' '.$attribute.'="'.$value.'" ';
					
			}
			
		}
		
		return $atts;
		
	}
	
	/**
	 * wraps a field help message
	 *
	 * @param string the help message
	 * @return string HTML string with message wrapped in a tag
	 */
	private function field_help_message( $text ) {
		
		return '<span class="field-help-message" >'.$text.'</span>';
		
	}
	
	/* =================================================
	 * UTILITY FUNCTIONS
	 * =================================================
	 */
	
	/**
	 * trim each element in an array
	 *
	 * @param array $array
	 */
	private function trim_array( Array $array ) {
				
		foreach( $array as &$value ) $value = trim( $value );
		unset( $value );// break the last reference
		
		return $array;
		
	}
	
	/**
	 * create a CSS space-delimited class string from various inputs
	 *
	 * @todo add string treatment to ensure proper syntax
	 *
	 * @param mixed $input can be array, space or comma-delimited string, or string
	 * @return string a space-delimited string of classnames
	 */
	private function make_class_string( $input ) {
		
		// make the classes into a space-delimited string from different kinds of inputs
		switch ( true ) {
			case ( false !== strpos( $input, ',' ) ):
				$classes = explode( ',', $input );// comma-delimited string
				break;
			case ( false !== strpos( $input, ' ' ) ):
				$classes = explode( ' ', $input );// space-delimited string
				break;
			case ( is_array( $input ) ):
				$classes = $input;
				break;
			default: 
				$classes = array( $input );
		}
		$classes = self::trim_array( $classes );
		return implode( ' ', $classes );
		
	}
	
	// this function prepares a couple of special cases then merges the called array 
	// with the default array so all members are present
	//
	// orphan function not used
	private function html_attributes_merge_defaults( $defaults, $attributes ) {
		foreach( $attributes as $attribute => &$value ) {
			if ( is_array( $value ) ) {
										foreach ( $value as $sub_attribute => &$sub_value ) {
											switch ( $sub_attribute ) {
												case 'class' :
													$sub_value = $defaults[ $attribute ][ $sub_attribute ] . ' ' . $sub_value;
													break;
												case 'style' :
													$sub_value = rtrim( $defaults[ $attribute ][ $sub_attribute ], ';' ) . ';' . $sub_value;
													break;
												default:
											}
										}
			}
		}
		
		unset( $value, $sub_value );
		
		return self::multimerge( $defaults, $attributes );
	}

 /**
  * merges multidimensional arrays
  *
  * @param array $array1
  * @param array $array2
  * @return array
  */
	private function multimerge ( $array1, $array2 ) {
		
		if (is_array($array2) && count($array2)) {
      foreach ($array2 as $k => $v) {
        if (is_array($v) && count($v)) {
          $array1[$k] = self::multimerge($array1[$k], $v);
        } else {
          $array1[$k] = $v;
        }
      }
    } else {
      $array1 = $array2;
    }

    return $array1;
		
  }

	/**
		* outputs an HTML string by collapsing an array or just return a string if that 
		* was the input
		*
		* @param mixed string or array
		* @return string HTML
	 */
	private function output( $html ) {
		
		if ( ! is_array( $html ) ) $html = array( $html );
		
		return implode( "\r", $html );
		
	}
	
	/**
	 * tests for associative array
	 *
	 * @param array
	 * @return bool
	 */
	public function isAssoc( $array ) {
		
			if ( ! is_array( $array ) ) return false;
		
			return ( $array !== array_values( $array ) );
			
	}
	
	/**
	 * returns the first name of a full name string
	 *
	 * @param string $fullname expecting a natural-language name with spaces between
	 * @param int $which 0 for first name, 1 for last name
	 *
	 * @return string normally returns the first name defined as everything up to the 
	 *                first space, last name is everyting else
	 *
	 * @static
	 */
	private function first_name( $fullname, $which = 0 ) {
		
		$names = explode( ' ', $fullname, 2 );
		
		return $names[ $which ];
		
	}
	
	/**
	 * returns the last name of a full name string; will include a middle name if 
	 * given, won't break up last name with two parts
	 *
	 * @param string $fullname expecting a natural-language name with spaces between
	 *
	 * @return string
	 *
	 * @static
	 */
	private function last_name( $fullname ) {
		
		return self::first_name( $fullname, 1 );
		
	}

} // class def
?>
