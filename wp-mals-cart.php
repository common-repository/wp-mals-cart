<?php 
/*
Plugin Name: WP Mals Cart
Plugin URI: http://xnau.com/wp-malscart
Description: Use shortcodes to add Mal's E-Commerce purchase buttons to your pages.
Version: 0.8.2
Author: xnau webdesign
Author URI: http://xnau.com
License: GPL2
Text Domain: wp-mals-cart

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

*/

spl_autoload_register( 'wp_malscart_class_loader' );

if ( ! defined( 'WP_MALSCART_PLUGIN_FILE' ) ) define( 'WP_MALSCART_PLUGIN_FILE', __FILE__ );

$WP_Malscart = new WP_Malscart;

/**
 * ====================================
 * REGISTER WIDGETS
 */
require_once 'classes/MasterWidget.php';

// define "view cart" widget parameters
register_master_widget( array(
									 
	'id' => 'wp-mals-cart-view-cart',
	
	'title' => 'View Cart Button',
	
	'fields' => array(
										array(
													'name' => '',
													'desc' => '',
													'id' => 'top_text',
													'type' => 'custom',
													'std' => '<h3>WP Mals Cart</h3>'
													),
										array(
													'name' => 'Title',
													'desc' => 'Put a title for the button here',
													'id' => 'title',
													'type' => 'text',
													'std' => ''
													),
										array(
													'name' => 'Button Text',
													'desc' => 'The text that will appear on the button',
													'id' => 'button_text',
													'type' => 'text',
													'std' => 'View Cart'
													),
										array(
													'name' => 'Extra Text',
													'desc' => 'Any text you want to appear before the actual button',
													'id' => 'extra_text',
													'type' => 'textarea',
													'std' => ''
													),
										array(
													'name' => 'Auto Hide',
													'desc' => 'Only show the button if there is something in the cart',
													'id' => 'auto_hide',
													'type' => 'checkbox',
													'std' => '1'
													),
										),
	
	// the function we will use to display the widget
	'show_view' => array( 'WP_Malscart_HTML', 'show_cart_button_widget' ),
	
	)
	
);

/**
 * performs the class autoload
 *
 * @param string $class the name of the class to be loaded
 */
function wp_malscart_class_loader( $class ) {

	$class_file = plugin_dir_path( __FILE__ ).'/classes/' . $class . '.php';

	if ( is_file( $class_file ) ) {
	
		require_once $class_file;

	}

}
?>
