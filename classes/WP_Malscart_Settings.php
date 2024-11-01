<?php
/**
 * this class defines all the plugin's settings and several untility methods to interact with it
 *
 * this class is also the primary location of all user interface strings
 *
 * each setting has a collection of attributes including the name, slug, default values, validation regex 
 * field type, validation status, and settings section membership
 * any new settings are added to the plugin are added to the $settings_data_sets array
 * 
 * PHP version 5.1.2
 *
 *
 * @package			WP_Malscart
 * @author			Roland Barker <webdesign@xnau.com>
 * @copyright		2011 xnau webdesign
 * @version			Release: @package_version@
 * @license			GPL2
 * @link				http://xnau.com/wp-malscart
 * @since				File available since Release 0.7
 */

class WP_Malscart_Settings {

	/**
	 * this array contains all instantiated settings values, status, error messages, etc.
	 *
	 * @param array $settings
	 * 	id			string	slug string for the setting
	 *	desc		string	This is a default description
	 *	value		string	default value for the setting
	 *	type		string	the type of field for the setting; text, option, radio, textarea
	 *	section	string	the section the setting is part of
	 *	items		array		id => title pairs for settings options list
	 *	class		string	if we want to add a CSS class to the element, we can put it here
	 *	status	string	validation status; can be 'empty','invalid','valid'
	 *	err_msg	array		error messages
	 *		empty		error message for an empty field					
	 *		invalid	error message for an invalid field
	 *	regex		string	if validation is needed, use this regex			
	 */
	private $settings = array();
	
	/**
	 * this array defines all settings values, status, error messages, etc.
	 *
	 * @param array $settings
	 * 	id			string	slug string for the setting
	 *	desc		string	This is a default description
	 *	value		string	default value for the setting
	 *	type		string	the type of field for the setting; text, option, radio, textarea
	 *	section	string	the section the setting is part of
	 *	items		array		id => title pairs for settings options list
	 *	class		string	if we want to add a CSS class to the element, we can put it here
	 *	status	string	validation status; can be 'empty','invalid','valid'
	 *	err_msg	array		error messages
	 *		empty		error message for an empty field					
	 *		invalid	error message for an invalid field
	 *	regex		string	if validation is needed, use this regex		
	 *
	 * @static
	 */
	private static $settings_data_sets = array();
	
	/**
	 * all UI strings are held in this array
	 *
	 */
	public static $UI_string = array();

	public function __construct() {
		/*
		 * this array holds all the data sets for each setting
		 */
		self::$settings_data_sets = array(

			'cart_server' => array( 								
															'title'			=> __('Cart Server', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('The server name for your cart; something like "http://ww1.aitsafe.com"', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'text',
															'value'			=> '',
															'section'		=> 'main_options',
															'err_msg'		=> array(
																										'empty' 		=> __('The Cart URL is required', WP_Malscart::PLUGIN_NAME),
																										'invalid'		=> __('Check that the Cart URL is complete and correct, including the protocol (http://)', WP_Malscart::PLUGIN_NAME),
																										),
															// this breaks the value into pieces and keeps the important part
															'regex'			=> '#^(?P<protocol>http(?P<secure_flag>s)?://)(?P<subdomain>[a-z0-9-]{3})\.'.ltrim( WP_Malscart::$malscart_cart_domain, '.' ).'(?P<path>/.*)?$#',

															),
			'cust_id' => array( 
															'title'			=> __("Mal's E-Commerce Username", WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('This is your 8-digit username', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'text',
															'value'			=> '',
															'section'		=> 'main_options',
															'err_msg'		=> array(
																										'empty' 		=> __("Your Mal's E-Commerce Username is required", WP_Malscart::PLUGIN_NAME),
																										'invalid'		=> __("Your Mal's E-Commerce Username seems to be incorrect", WP_Malscart::PLUGIN_NAME),
																										),
															'regex'			=> '##', //  #^([0-9]{5,8})$# I need to verify the format with Mal

															),
			
															
			'button_CSS' => array( 																					 
															'title'			=> __("Use plugin CSS?", WP_Malscart::PLUGIN_NAME), 
															'desc'			=> '', 
															'type'			=> 'radio',
															'value'			=> 1,
															'section'		=> 'buy_button_options', 
															'items' 			=> array( 
																										__('Yes (plugin CSS will style buy buttons)', WP_Malscart::PLUGIN_NAME ) => 1, 
																										__('No (buttons will be styled by theme CSS)', WP_Malscart::PLUGIN_NAME ) => 0
																										),

															),
															
			'button_width' => array( 		
															'title'			=> __('Width', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('the default width of the box containing the button', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'text',
															'value'			=> '120',
															'section'		=> 'buy_button_options',
															'err_msg'		=> array(
																										'empty' 		=> '',
																										'invalid'		=> __('Button width needs to be a number (for pixels) or a number with a CSS unit', WP_Malscart::PLUGIN_NAME),
																										),
															'regex'			=> "#([0-9\.]+)(px|em|pt|%|ex)?#",
															),
			'selector_preference' => array( 						
															'title'			=> "Which selector for product options?", 
															'desc'			=> '', 
															'type'			=> 'radio',
															'value'			=> 'radio',
															'section'		=> 'buy_button_options', 
															'items' 			=> array( 
																										__('Radio Buttons (such as these)', WP_Malscart::PLUGIN_NAME ) => 'radio', 
																										__('Pull-down List (best for a large number of choices)', WP_Malscart::PLUGIN_NAME ) => 'select'
																										),

															),
			'button_text' => array(																					
															'title'			=> __('Buy Button Label', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('the default text that will appear on buy buttons', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'text',
															'value'			=> __('Add to Cart', WP_Malscart::PLUGIN_NAME),
															'section'		=> 'buy_button_options',
															),
			'test_product' => array( 		 
															'title'			=> __("Test Product Name", WP_Malscart::PLUGIN_NAME), 
															'desc'			=> '', 
															'type'			=> 'text',
															'value'			=> __('Test Product', WP_Malscart::PLUGIN_NAME),
															'section'		=> 'buy_button_options',
															),
			'test_price' => array(		 
															'title'			=> __('Test Product Price', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> '', 
															'type'			=> 'text',
															'value'			=> '25.00',
															'section'		=> 'buy_button_options',
															),
			'button_style' => array( 																						 
															'title'			=> __('Select Button Style', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('Select a base style for the buy button shorcode', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'button_style',
															'value'			=> 1,
															'section'		=> 'buy_button_options', 
															'items' 			=> array( 
																										__('Simple', WP_Malscart::PLUGIN_NAME ) => 1,
																										__('Product and price', WP_Malscart::PLUGIN_NAME ) => 2
																										),
															),
			'purchase_return_message' => array( 																						 
															'title'			=> __('Purchase Return Message', WP_Malscart::PLUGIN_NAME),
															// translators: do not translate words in brackets {} 
															'desc'			=> __('Message to show when someone returns to the site after having made a purchase. You can insert the following values from the cart: {name}, {first_name}, {last_name}, {cart_total}, {email}, {payment_method},{purchase_id}. Place on your purchase return page using this shortcode: [purchase_return_message]', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'textarea',
															'value'			=> '<p>'.__('Thank you, {name}, for your purchase. An email confirmation of your purchase will be sent to: {email}.', WP_Malscart::PLUGIN_NAME).'</p>',
															'section'		=> 'messages',
															),
			'mail_payment_return_message' => array( 																						 
															'title'			=> __('Mail Payment Return Message', WP_Malscart::PLUGIN_NAME), 
															'desc'			=> __('Message to display when someone uses a payment method that requires your address. You can the same placeholders available to the Purchase Return Method. Place on your purchase return page using this shortcode: [mail_payment_return_message]', WP_Malscart::PLUGIN_NAME), 
															'type'			=> 'textarea',
															'value'			=> '<p>'.__('Please send your {payment_method} payment of {cart_total} to this address:', WP_Malscart::PLUGIN_NAME).'</p><address>'.__('Your Buisiness', WP_Malscart::PLUGIN_NAME).'<br/>'.__('Your Street', WP_Malscart::PLUGIN_NAME).'<br />'.__('Your City, State', WP_Malscart::PLUGIN_NAME).'<br />'.__('Your Zip Code', WP_Malscart::PLUGIN_NAME).'</address>',
															'section'		=> 'messages',
															),
		);
		
		// initialize all UI strings
		self::$UI_string = array(
														 'plugin footer'							=> __('%1$s plugin | Version %2$s', WP_Malscart::PLUGIN_NAME ),
														 'buy button settings blurb'	=> __('A "buy button" is a simple button to add a product to the shopping cart. It can be placed in any page or post, and it must have a product name and price. The button can also have radio selectors or a drop-down list of product options.', WP_Malscart::PLUGIN_NAME ),
														 );
		
		// initialize the settings object
		$this->load_settings();

	}


	/**
	* builds the settings object
	*
	* cycles through the static definition array, merging defaults into missing fields and setting the 'id' field
	*
	* @return NULL
	*
	*/
	private function load_settings() {

		foreach ( self::$settings_data_sets as $setting => $setting_data ) {
			
			$setting_data[ 'id' ] = $setting;
			
			$this->settings[ $setting ] = wp_parse_args( $setting_data, WP_Malscart::$setting_defaults );
			
		}

	}
	
	/**
	 * gets all the data sets
	 */
	public function get_all() {
		
		return $this->settings;
		
	}
	
	/**
	 * gets the data set for a setting
	 */
	public function get( $setting ) {
		
		return $this->settings[ $setting ];
		
	}
	
	/**
	 * gets the status of a setting
	 */
	public function get_status( $setting ) {
		
		return $this->get_single( $setting, 'status' );
		
	}
	
	/**
	 * gets a single data value of a setting
	 */
	public function get_single( $setting, $attribute ) {
		
		return isset( $this->settings[ $setting ][ $attribute ] ) ? $this->settings[ $setting ][ $attribute ] : false;
		
	}
	
	/**
	 * sets a data value for a setting
	 */
	public function set_single( $setting, $attribute, $value ) {
		
		 // we may want to validate the value
		 $this->settings[ $setting ][ $attribute ] = $value;
		 
		 return true;
		 
	}
	
	/**
	 * sets the status of a setting
	 */
	public function set_status( $setting, $value ) {
		
		 // validate the input
		 if ( in_array( strtolower( $value ), array( 'valid', 'invalid', 'empty' ) ) ) {
		 
		 	$this->settings[ $setting ][ 'status' ] = strtolower( $value );
		 
		 	return true;
			
		 } else {
			 
			 return false;
			 
		 }
		 
	}
	
	/**
	 * returns a special-case error message for the plugin's initial state
	 * 
	 * @return array the error message data array
	 */
	public function get_initial_error() {
		
		return array(
						
							'err_msg' => array(
																 'empty' => __("The Cart Server and Mal's E-Commerce Username must be set before you can use this plugin")
																 ),
							
							'id' => 'initial_state',
							
							'status' => 'empty',
							
							'title' => 'Initial State',
							
							);
	}

} // class
