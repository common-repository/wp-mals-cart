<?php 
/**
 * Class to display the plugin's options page in the site admin
 *
 * PHP version 5.1.2
 *
 *
 * @package			WordPress
 * @author			Roland Barker <webdesign@xnau.com>
 * @copyright		2011 xnau webdesign
 * @version			0.7.1
 * @license			GPL2
 * @link				http://xnau.com/wp-malscart
 * @since			File available since Release 0.5
 */

class WP_Malscart_Options_Page {

	public function initialize( ) {

		self::add_admin_page();

	}

	// add the admin options page
	private function add_admin_page() {

		$pageName = add_options_page( 
										 WP_Malscart::PLUGIN_TITLE, 
										 WP_Malscart::PLUGIN_TITLE, 
										 'manage_options', 
										 WP_Malscart::$OPTIONS_PAGE_NAME, 
										 array( 'WP_Malscart_Options_Page', 'options_page' ) 
										 );
		
		if ( self::on_options_page() ) {
			
			add_action( 'admin_print_styles', array( 'WP_Malscart_Options_Page', 'admin_header' ) );

			add_action( "in_admin_footer", array( 'WP_Malscart_Options_Page', "admin_footer" ) );
		
			add_action( 'admin_print_scripts-' . $pageName, array( 'WP_Malscart_Options_Page', 'scripts' ) );

		}

	}


	// adds some info to the admin options page footer	
	public function admin_footer() {

		$plugin_data = WP_Malscart::plugin_data();
		echo sprintf( WP_Malscart_Settings::$UI_string[ 'plugin footer' ] .'<br />', $plugin_data['Name'], $plugin_data['Version']);

	}
	
	// add some stuff to the admin options page header	
	public function admin_header() {
		
		wp_enqueue_style( WP_Malscart::PLUGIN_NAME.'-options', WP_Malscart::$PLUGIN_URL . '/'.WP_Malscart::PLUGIN_NAME.'.css' );
		
		wp_enqueue_style( WP_Malscart::PLUGIN_NAME.'-buttons', WP_Malscart::$PLUGIN_URL . '/'.WP_Malscart::PLUGIN_NAME.'-buttons.css' );

	}
	
	// adds jQuery Tabs
	public function scripts() {
		
		wp_enqueue_script( 'jquery-ui-tabs' );
		
	}

	// check to see that we're on the plugin options page
	public function on_options_page() {
		
		//return false !== stripos( WP_Malscart::$OPTIONS_PAGE_NAME, $_GET[ 'page' ] );
		
		return WP_Malscart::$ON_OPTIONS_PAGE;

	}
	
	/**
	* prints a section header body (if any)
	*
	* these sections are defined in WP_Malscart::$sections
	*
	* @param array a set of arguments for each section 
	*/
	public function print_section( $args ) {
		
		extract( $args );
		
		// WP will print the title itself
		// echo '<h3 class="title">'.$title.'</h3>';
		
		switch ( $id ) {
		
			case 'help':
				echo WP_Malscart_Help_Page::help_page();
				break;
				
			case 'buy_button_options' :
				$text = '<p>'.WP_Malscart_Settings::$UI_string[ 'buy button settings blurb' ].'</p>';
				break;
				
			default:
			
		}
		
		if ( ! empty( $text ) ) WP_Malscart_HTML::print_tag( array( 
																			 'tag' 		=> 'div',
																			 'class'	=> 'read_column',
																			 'text' 	=> $text,
																			 )
																);
		
	}

	// display the admin options page
	public function options_page() {
		
		global $WP_Malscart;
?>
		<div class="wrap">
			<div class="icon32" id="icon-options-<?php echo WP_Malscart::PLUGIN_NAME ?>"><img src="<?php echo WP_Malscart::$PLUGIN_URL.'/wpmalscart-logo-image-36x36.png' ?>"><br></div>
			<h2><?php echo WP_Malscart::PLUGIN_TITLE?></h2>
			<form action="options.php" method="post">
			
			
			<?php settings_fields( WP_Malscart::$OPTIONS_SETTINGS ); ?>
			
				<div class="ui-tabs">
					<ul class="ui-tabs-nav">
				
			<?php foreach ( $WP_Malscart->sections as $section )
						echo '<li><a href="#' . strtolower( str_replace( ' ', '_', $section ) ) . '">' . $section . '</a></li>'; ?>
								
					</ul>
			
			<?php do_settings_sections(  WP_Malscart::$OPTIONS_PAGE_NAME ); ?>
			
				</div>
				<p class="submit"><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" class="button-primary"  /></p>
			</form>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
						var wrapped = $(".wrap .ui-tabs>h3").wrap("<div class=\"ui-tabs-panel\">");
						wrapped.each(function() {
							$(this).parent().append($(this).parent().nextUntil("div.ui-tabs-panel"));
						});
						$(".ui-tabs-panel").each(function(index) {
							var str = $(this).children("h3").text().replace(/\s/g, "_");
							$(this).attr("id", str.toLowerCase());
							if (index > 0)
								$(this).addClass("ui-tabs-hide");
						});
						$(".ui-tabs").tabs({ fx: { opacity: "toggle", duration: "fast" } }).bind( 'tabsselect', function( event,ui) {
																											
																											var activeclass = $(ui.tab).attr('href').replace( /^#/, '');
																											$(".wrap").removeClass().addClass( "wrap "+activeclass );
																											});
						//$(".wrap h3, .wrap table").show();
						if ($.browser.mozilla)
         			$("form").attr("autocomplete", "off");
				});
			</script>
		</div>
<?php 

	} // options_page fn

	/**
	 * prints the options page submission errors and field highlight CSS
	 *
	 * this replaces the error display from the WP Settings API
	 *
	 * @static
	 * @return null
	 */
	private function print_error_series( ) {
		
		// perform a validation when the page is first opened (i.e. not on a submission)
		if ( ! WP_Malscart::$is_update  ) {

			WP_Malscart::options_validate( get_option( WP_Malscart::$OPTIONS_SETTINGS ) );

		}
		
		$errors = get_settings_errors();
		
		$html = array();
		
		if( count( $errors ) ) {
		
			$css_selectors = array();
			$css_pattern = '.form-table input[id="%s"] ';
			
			
			$error_divs = array();
			$error_pattern = '<div class="'.WP_Malscart::PLUGIN_NAME.' %3$s settings-%3$s" id="setting-%3$s-%1$s"><p><strong>%2$s</strong></p></div>';
			
			foreach( $errors as $error ) {

				$css_selectors[] = sprintf( $css_pattern, $error[ 'code' ] );

				$error_divs[] = sprintf( $error_pattern, $error[ 'code' ], $error[ 'message' ], $error[ 'type' ] );

			}
			
			
			// print the field highlight css
			if ( $error[ 'type' ] == 'error' ) $html[] = '<style type="text/css" >'.implode( $css_selectors, ',' ).'{ background-color: #FFEBE8; border-color: #CC0000; }</style>';
			
			// print the error messages
			$html[] = "\r".implode( $error_divs, "\r" );
			
			self::print_html( $html );
			
		}
		
		
	}
	
	public function display_errors( ) {

		// we only do this on the plugins own options page
		if ( WP_Malscart::$ON_OPTIONS_PAGE ) self::print_error_series( );
	
	}
	
	
	public function display_setting_field( Array $field_args ) {
		
		extract( $field_args );
		
		$options = get_option( WP_Malscart::$OPTIONS_SETTINGS );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $value; // if it's not set, set the value of the field to the default
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0; // checkboxes need this to be zero
			
		switch ( $type ) {
			
			case 'text':
				self::admin_text_field( $field_args );
				break;
				
			case 'textarea':
				self::admin_textarea_field( $field_args );
				break;
				
			case 'radio':
				self::admin_radio_field( $field_args );
				break;
				
			case 'dropdown':
				self::admin_dropdown_field( $field_args );
				break;
				
			case 'button_style':
				self::button_style_field( $field_args );
				break;
				
			case 'header':
				echo self::settings_header( $field_args );
				break;
			
			default:
				$field_args[ 'tag' ] = $field_args[ 'type' ];
				WP_Malscart_HTML::build_tag( $field_args );
				
		}
		
	}


	/* ==================================  
	 * settings page fields
	 */
	public function admin_text_field( $args ) {
		
		//error_log( __METHOD__.' args:'.print_r( $args, true ));
		extract( $args );
		$options = get_option( WP_Malscart::$OPTIONS_SETTINGS );
		echo WP_Malscart_HTML::text_field( array(
										'name' 	=> WP_Malscart::$OPTIONS_SETTINGS.'['.$id.']',
										'value'	=> esc_attr( $options[$id] ),
										'id'		=> $id,
										'desc'=> $desc,
										'class'	=> $class
										) );
	}
	
	public function admin_textarea_field( $args ) {
		
		extract( $args );
		$options = get_option( WP_Malscart::$OPTIONS_SETTINGS );
		echo WP_Malscart_HTML::textarea_field( array(
										'name' 	=> WP_Malscart::$OPTIONS_SETTINGS.'['.$id.']',
										'value'	=> esc_attr( $options[$id] ),
										'id'		=> $id,
										'desc'=> $desc,
										'class'	=> WP_Malscart_HTML::$base_class.'-message-field'
										) );
	}
	
	public function admin_radio_field( $args ) {
		extract( $args ); // id, $items = array( 'label'=>'value' )
		$options = get_option( WP_Malscart::$OPTIONS_SETTINGS );
		echo WP_Malscart_HTML::radio_field( array(
											'name'	=> WP_Malscart::$OPTIONS_SETTINGS.'['.$id.']',
											'items'	=> $items,
											'value'	=> esc_attr( $options[$id] )
											) );
	}

	public function  admin_dropdown_field( $args ) {
		extract( $args ); // id, $items = array( 'label'=>'value' )
		$options = get_option( self::$OPTIONS_SETTINGS );
		echo WP_Malscart_HTML::dropdown_field( array(
											'name'	=> WP_Malscart::$OPTIONS_SETTINGS.'['.$id.']',
											'items'	=> $items,
											'value'	=> esc_attr( $options[$id] ),
											'id'		=> $id
											) );
	}

	// print a subheading and/or descriptive text
	public function settings_header( $args ) {
		extract( $args );
		if ( ! empty( $title ) )
			echo WP_Malscart_HTML::build_tag( array(
												'tag'		=> 'h4',
												'text'	=> $title
												) );
		if ( ! empty( $desc ) )
			echo WP_Malscart_HTML::build_tag( array(
											'tag'		=> 'p',
											'class'	=> WP_Malscart_HTML::$base_class.'-settings-desc',
											'text'	=> $desc
											) );
	}
	
	// prints all the button styles to choose from
	public function button_style_field( $args ) {

		extract( $args );
		$options = get_option( WP_Malscart::$OPTIONS_SETTINGS );

		$html = array();
		
		$html[] = '<div class="buy-button-styles">';
		
		foreach ( $items as $label => $value ) {
			$html[] =  '<div>';// container for each style
			$html[] =  WP_Malscart_HTML::radio_field( array(
												'name'	=> WP_Malscart::$OPTIONS_SETTINGS.'['.$id.']',
												'items'	=> array( $label => $value ),
												'value'	=> esc_attr( $options[$id] )
												) ); // creates the radio button
			
			// output the button itself to preview it's appearance ( unless we use an iframe, this will never look exactly like it will on the site. ) 
			$html[] = WP_Malscart_HTML::buy_button( array( 
												'demo'			=> true, // this supresses form actions for display purposes
												'product[]' 	=> $options[ 'test_product' ], 
												'price' 		=> $options[ 'test_price' ],
												'button_width'	=> $options[ 'button_width' ],
												'button_style' 		=> $value
												)  ). '</div>';
		}
		
		$html[] = '</div>';
		
		self::print_html( $html );

	}
	

	
	// outputs an array or string	
	private function print_html( $html ) {
		
		if ( ! is_array( $html ) ) $html = array( (string) $html );
		
		echo implode( "\r", $html );
		
	}
	
	public function errors_exist() {
		
		$errors = get_settings_errors();
		
		return	empty( $errors ) ? false : true;
	
	}
	
} // options page class definition
?>
