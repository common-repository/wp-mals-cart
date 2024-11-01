<?php
/**
 * Class to implement an online commerce application in WordPress using the Mal's Cart service
 *
 * This class is the base class for the plugin and it's main task is to register and validate the settings, incluging error handling and set all the hooks
 *
 * PHP version 5.1.2
 *
 *
 * @package			WordPress
 * @author			Roland Barker <webdesign@xnau.com>
 * @copyright		2011 xnau webdesign
 * @version			0.8.1
 * @license			GPL2
 * @link				http://xnau.com/wp-malscart
 * @since			File available since Release 0.1
 */

class WP_Malscart 
{

	// slug identifer
	const PLUGIN_NAME = 'wp-mals-cart';
	
	// print title for the plugin
	const PLUGIN_TITLE = "WP Mal's Cart";
	
	// current software revision
	const PLUGIN_VERSION = '0.8.1';
	
	// Mal's Cart shopping cart domain for posting; we use this to error-check user input
	public static $malscart_cart_domain = '.aitsafe.com';
	
	// slug for the settings page
	public static $OPTIONS_PAGE_NAME;
	
	// name of the plugin options field
	public static $OPTIONS_SETTINGS;
	
	// absolute url for the plugin's directory
	public static $PLUGIN_URL;
	
	// realtive path to the plugin's directory
	public static $PLUGIN_PATH;
	
	// true if the plugin's options page is displaying
	public static $ON_OPTIONS_PAGE;
	
	// true after settings have been updated (as opposed to just having arrived at the options page)
	public static $is_update;
	
	// this array holds the settings tabs as slug/title pairs
	public static $sections = array();
	
	// this array holds the default attributes for a plugin setting
	public static $setting_defaults;

	// references the plugin settings object; the database is updated from this
	public static $Settings;
	
	// array holds the default parameters for a product purchase button
	public static $product_form_fields;
	
	// float holds the quantity of items in the cart
	public static $cart_quantity = 0;
	
	// float holds the total value of the cart
	public static $cart_total = 0;
	
	// this boolean flags the presence of a plugin shortcode in the content (not working reliably yet)
	public $shortcode_present = false;

	public function __construct() {

		self::$PLUGIN_URL = WP_PLUGIN_URL . "/" . self::PLUGIN_NAME;
		self::$PLUGIN_PATH = WP_PLUGIN_DIR . "/" . self::PLUGIN_NAME;
		self::$OPTIONS_PAGE_NAME = self::PLUGIN_NAME;
		self::$OPTIONS_SETTINGS = self::PLUGIN_NAME.'-settings';
		self::$ON_OPTIONS_PAGE = ( isset( $_GET['page'] ) && $_GET[ 'page' ] == self::$OPTIONS_PAGE_NAME ) ? true : false;
		self::$is_update = ( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ) ? true : false;

		// define the settings sections
		// these will end up on tabs
		$this->sections = array(
														'main_options' => 'Main Settings',
														'buy_button_options' => 'Buy Button Preferences',
														'messages' => 'Messages',
														'help' => 'Help',
														);
		
		// buy button shortcode attributes		
		// this is where we would add any extra fields like product id numbers or state hashes
		// array is filled with default values that can be overridden
		self::$product_form_fields = array( 
												'button_style'	=> self::get_option( 'button_style' ),
												'product[]'			=> self::get_option( 'test_product' ),
												'price'					=> self::get_option( 'test_price' ),
												'return'				=> self::get_option( 'return' ),
												'button_text'		=> self::get_option( 'button_text' ),
												'button_width'	=>	self::get_option( 'button_width' )
												);

		// default values for a plugin setting; these values are stored in the Settings object
		// and are used to register the settings with the API, also to hold the validation status of each setting
		self::$setting_defaults = array(

												// slug string for the setting
												'id'			=> 'default_field',

												// field title string
												'title'		=> 'Default Field',

												/*
												 * field explanation note
												 */
												'desc'		=> 'This is a default description.',

												// the type of field for the setting; text, option, radio, textarea
												'type'		=> 'text',

												// the section the setting is part of
												'section'	=> 'main_options',

												/*
												 * this will be a list of options
												 */
												'items'		=> array(),

												/* 
												 * validation status; can be 'empty','invalid','valid'. The default status is 
												 * 'valid' because we pre-load the setting with a default value (this can be overridden 
												 * in the setting definition).
												 */
												'status'	=> 'valid',

												/*
												 * error messages to go with each status
												 * empty string means no validation error is registered
												 */
												'err_msg'	=> array(
																		'empty' 		=> '',
																		'invalid'	=> '',
																		),

												);

		// instantiate the settings object
		self::$Settings = new WP_Malscart_Settings;	
		
		// if the setting is not present in the database, initialize it
		if ( ! get_option( self::$OPTIONS_SETTINGS ) ) {

			$this->initialize_settings();
			// error_log( __METHOD__.' initializing settings' );

		}			
		
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		
		add_action( 'admin_menu', array( 'WP_Malscart_Options_Page', 'initialize' ) );
		
		// settings validation faults and user feeback in the admin
		add_action( 'admin_notices', array( 'WP_Malscart_Options_Page', 'display_errors' ), 15 );
		
		//add_filter('the_posts', array( $this, 'shortcode_check' ) );// this is problematic and unnecessary

		// add the button CSS
		add_action( 'wp_print_styles', array( $this, 'add_plugin_CSS' ) );
		
		// update the status of the user cart cookie and plugin state
		add_action( 'wp', array( 'WP_Malscart', 'update_cart_values' ) );
		
		// shortcode to get the plugin's info; used on help pages
		add_shortcode( 'wp_malscart_info', array( 'WP_Malscart', 'get_plugin_info' ) );

		// register the shortcode for a buy button
		add_shortcode( 'buy_button', array( 'WP_Malscart_HTML', 'buy_button' ) );

		// register the purchase return shortcode
		add_shortcode( 'purchase_return', array( 'WP_Malscart_HTML', 'purchase_return' ) );

		// register the purchase return message shortcode
		add_shortcode( 'purchase_return_message', array( 'WP_Malscart_HTML', 'purchase_return_message' ) );

		// register the mail_payment return message shortcode
		add_shortcode( 'mail_payment_return_message', array( 'WP_Malscart_HTML', 'mail_payment_return_message' ) );
		
		// internationalization
		load_plugin_textdomain( self::PLUGIN_NAME, false, basename( dirname( __FILE__ ) ) . '/languages' );
		
	} // __construct
	
	/*
	 * intializes the settings page
	 */
	public function admin_init() {
		
		$this->register_settings();
	
	}


	/*
	 * registers everything with the WP Settings API
	 *
	 * first the setting array, then the sections, then all the individual settings
	 */
	private function register_settings() {
		
		register_setting( 
										 self::$OPTIONS_SETTINGS, 
										 self::$OPTIONS_SETTINGS, 
										 array( $this,'options_validate' ) 
										 );


		foreach ( $this->sections as $slug => $title )
			add_settings_section( 
												 $slug, 
												 $title, 
												 array( 'WP_Malscart_Options_Page', 'print_section' ), 
												 self::$OPTIONS_PAGE_NAME 
												 );

		foreach ( self::$Settings->get_all() as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}

	}

	/**
	 * adds a setting field to the options page via the Settings API
	 *
	 *
	 * @param $args array
	 *		type	the input type for the setting
	 *		id		the id slug of the setting
	 *		desc	help text for the input field
	 *		value	the current value of the setting
	 *		items	array containing the items for radio buttons or options elements
	 *		class	CSS class for the element
	 *
	 */
	private function create_setting( $args = array() ) {
		
		extract( $args );

		// maps the arguments from each field definition to settings field arguments
		$field_args = array(
				'type'      => $type,
				'id'        => $id,
				'desc'      => @$desc,
				'value'     => @$value,
				'items'			=> @$items,
				'label_for' => $id,
				'class'     => @$class,
		);

		add_settings_field( $id, $title, array( 'WP_Malscart_Options_Page','display_setting_field' ), self::$OPTIONS_PAGE_NAME, $section, $field_args );

	}

	
	/**
	 * validate settings on submit from options page
	 *
	 * user settings submissions are verfied before the settings update can be accepted
	 * a swtich statement filters out some values for special treatment
	 * 
	 * @param array $settings
	 * 	each setting id is the setting slug
	 *
	 * @param bool $register_errors flag to register a setting error with the WP API
	 *
	 * @static  
	 */
	public function options_validate( Array $settings, $register_errors = true ) {
		
		// error_log( __METHOD__.' validating settings='.print_r( $settings, true) );
		
		foreach( $settings as $setting => $value ) :
		
			// picks up the values attached to each setting
			$setting_map = self::$Settings->get( $setting );

			switch ( $setting ) {
				
				case 'cart_server' :
					
					$value = strtolower( $value );
					
					if ( empty( $value ) ) 
							self::$Settings->set_status( $setting, 'empty' );
					
					elseif ( ! preg_match( $setting_map[ 'regex' ], $value, $matches ) )
							self::$Settings->set_status( $setting, 'invalid' );
					
					// construct the final value
					else {
						
						$settings[ 'cart_server' ] = $matches[ 'protocol' ].$matches[ 'subdomain' ].self::$malscart_cart_domain;
						
						self::$Settings->set_status( $setting, 'valid' );
						
					}
					
					break;
					
				case 'cust_id' :
				
					if ( empty( $value ) ) 
						self::$Settings->set_status( $setting, 'empty' );
						
					elseif ( ! preg_match( $setting_map[ 'regex' ], $value ) ) // we need an 8-digit number
						self::$Settings->set_status( $setting, 'invalid' );
							
					else
						self::$Settings->set_status( $setting, 'valid' );
						
					break;

				case 'button_width' :
			
					// grab only valid CSS values
					if ( ! $valid = preg_match( $setting_map[ 'regex' ], $value, $matches ) ) 
						self::$Settings->set_status( $setting, 'invalid' );
							
					else
						self::$Settings->set_status( $setting, 'valid' );
							
					// construct the final value
					$settings[ 'button_width' ] = $matches[1] . ( empty( $matches[2] ) ? 'px' : $matches[2] );

					break;
					
				default :
				
					// if an error message for an empty field is set, test for an empty field
					if ( ! empty( $setting_map[ 'err_msg' ][ 'empty' ] ) && empty( $value ) ) self::$Settings->set_status( $setting, 'empty' );
								
					elseif ( ! empty( $setting_map[ 'err_msg' ][ 'invalid' ] ) && ! preg_match( $setting_map[ 'regex' ], $value ) ) self::$Settings->set_status( $setting, 'invalid' );
							
					else
						self::$Settings->set_status( $setting, 'valid' );
				
			}
			
			// error_log( __METHOD__.' settings data='.print_r( self::$Settings->get( $setting ), true ));
		
		endforeach;
		
		// if we are registering the errors with the API
		if ( $register_errors ) :
		
			// all settings have been validated
			reset( $settings );
			
			foreach ( $settings as $setting => $value ) {
				
				if ( self::$Settings->get_status( $setting ) !== 'valid' ) self::register_error( self::$Settings->get( $setting ) );
				
			}
			
		endif;
					
		return $settings;
		
	}
	
	/**
	* 
	* test for duplication then register the error with the API
	*
	* @param array $setting_map holds all data associated with the setting
	*
	* @static
	*/
	private static function register_error( $setting_map ) {
			
			// error_log( __METHOD__.' setting_map='.print_r( $setting_map, true ));
				
			if ( self::is_duplicate_error( $setting_map[ 'id' ] ) ) {
				
				error_log( __METHOD__.' duplicate error' );
				
				return;
				
			}
			
			// grab the error message that corresponds to the status
			$error_msg = $setting_map[ 'err_msg' ][ $setting_map[ 'status' ] ];
			
			if ( ! empty( $error_msg ) ) {
				
				add_settings_error( $setting_map[ 'title' ], $setting_map[ 'id' ], $error_msg );
				
			}
			
			// modify the errors array if we are not set up
			if ( ! self::is_set_up() ) self::set_not_set_up_error();
			
	}
	
	private function set_not_set_up_error() {
		
		global $wp_settings_errors;
		
		// we're going to transfer all errors into here except the ones we are deleting
		$rebuilt_errors = array();
		
		foreach ( $wp_settings_errors as $error ) {
			
			// delete error messages for these settings 
			if ( ! in_array( $error[ 'code' ], array( 'cust_id', 'cart_server' ) ) )
				$rebuilt_errors[] = $error;
			
		}
		
		$wp_settings_errors = $rebuilt_errors;
		
		$setting_map = self::$Settings->get_initial_error();
		
		if ( ! self::is_duplicate_error( $setting_map[ 'id' ] ) )
			add_settings_error( $setting_map[ 'title' ], $setting_map[ 'id' ], $setting_map[ 'err_msg' ][ 'empty' ] );
		
	}
	
		/**	
	 *
	 * sees if the error we're about to register is already present
	 */
	private function is_duplicate_error( $id ) {
		
			global $wp_settings_errors;
			
			// if no errors we can set the new error
			if ( ! count( $wp_settings_errors ) ) return false;
				
			foreach( $wp_settings_errors as $error ) {
						
						// if an error for the setting has already been set don't continue
						if ( $error[ 'code' ] == $id ) return true;
						
			}
			
			return false;
		
	}
	
	
	
	// easy way to grab one value or see if it is set
	public function get_option( $key ) {
		
		$settings = get_option( self::$OPTIONS_SETTINGS );
		
		return empty( $settings[ $key ] ) ? false : $settings[ $key ];
		
	}

	/**	
	 *
	 * sets all default settings values
	 *
	 * this triggers on first activation to fill the options record in the db
	 *
	 */
	public function initialize_settings() {
		
		// error_log( __METHOD__. print_r( self::$Settings, true ));
	
		$default_settings = array();
		foreach ( self::$Settings->get_all() as $id => $setting ) {
			
			if ( $setting['type'] != 'heading' )
				$default_settings[$id] = $setting['value'];
				
		}
	
		update_option( self::$OPTIONS_SETTINGS, $default_settings );
	
	}

	/**
	 * determines whether the plugin has been set up or not ( valid settings for both user ID and server )
	 */
	private function is_set_up() {
	
		return ( self::$Settings->get_status( 'cust_id' ) === 'empty' && self::$Settings->get_status( 'cart_server' ) === 'empty' ) ? false : true;

	}

	// provides a way to check the content for one of our shortcodes
	// for now, we don't use this, it needs fixing and we don't need it
	// but we may eventually need it...
	// this is meant to work with the `the_posts` filter
	public function shortcode_check( $post ) {
		
		if (empty($posts)) return $posts;

		foreach ($posts as $post) {
			if ( false !== stripos( $post->post_content, '[buy_button' ) ) {
				$this->shortcode_present = true;
				break;
			}
		}
	 
		return $posts;
	}
	
	// return info about the plugin	
	public function plugin_data() {
		
		if ( function_exists( 'get_plugin_data' ) )
			return get_plugin_data( WP_MALSCART_PLUGIN_FILE );
		else 
			return array( 'Version'=>self::PLUGIN_VERSION );
		
	}
	
	public function get_plugin_info( $atts ) {
		
		extract( shortcode_atts( array( 'field' => 'Version' ), $atts ) );
		
		$info = self::plugin_data();
		
		return $info[ $field ];
		
	}
	
	// conditionally adds the button CSS
	public function add_plugin_CSS() {

		if ( $this->get_option( 'button_CSS' ) )
		
		wp_register_style( self::PLUGIN_NAME.'-css', self::$PLUGIN_URL.'/'.self::PLUGIN_NAME.'-buttons.css' );
		
		wp_enqueue_style( self::PLUGIN_NAME.'-css' );
		
	}
	
	/**
	 * updates the cart values and cookie
	 *
	 * @static
	 * @return null
	 */
	public function update_cart_values() {
		
		// checks cart return POST values, if any
		if ( isset( $_POST[ 'qty' ] ) ) {
				
			self::$cart_quantity = $_POST[ 'qty' ];
			self::$cart_total = $_POST[ 'tot' ];
	
			$cookie_data = array(
													 'cart_quantity' 	=> self::$cart_quantity,
													 'cart_total'			=> self::$cart_total,
													 );
			
			self::set_cookie( $cookie_data );
				
		} else {
			
			$cookie_data = self::get_cookie();
			
			if ( isset( $cookie_data[ 'cart_quantity' ] ) ) {
				
				self::$cart_quantity = $cookie_data[ 'cart_quantity' ];
				self::$cart_total = $cookie_data[ 'cart_total' ];
				
			}
			
		}
		
	}
	
	/**
	 * sets plugin cookie values
	 *
	 * @static
	 * @param array contains cookie data fields to set
	 * @return null
	 */
	public function set_cookie( array $new_cookie_data ) {
		
		$old_cookie_data = self::get_cookie();
		
		$cookie_data = wp_parse_args( $old_cookie_data, $new_cookie_data );
		
		setcookie( self::PLUGIN_NAME, serialize( $cookie_data ), 0, get_bloginfo( 'url' ) );
		
	}
	
	/**
	 * gets the ciookie value array
	 *
	 * @return array
	 * @static
	 */
	public function get_cookie() {
		
		$cookie_array = isset( $_COOKIE[ self::PLUGIN_NAME ] ) ? unserialize( stripslashes( $_COOKIE[ self::PLUGIN_NAME ] ) ) : array();
		
		return $cookie_array;
		
	}
																 
	

} // class

/*  
		Copyright 2011  Roland Barker  (email : webdesign@xnau.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/?>
