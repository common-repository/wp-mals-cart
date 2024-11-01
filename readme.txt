=== WP Mal's Cart ===
Contributors: xnau
Donate Link: http://xnau.com/wp-malscart
Tags: commerce, products, shopping cart, mals-e.com, e-commerce
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.8.2

Provides the front-end in Wordpress for an online commerce application using
the Mal's E-Commerce (http://mals-e.com) shopping cart service.

== Description ==

This plugin is a very simple and easy-to-set-up way to sell products on a WordPress site. **Please note:** this plugin is a work-in-progress. While it can be used for simple purchases, major features will be added as the plugin is developed. If you would like to be notified of major releases, please sign up on the plugin's [web page.](http://xnau.com/wp-malscart/)

The shopping cart service [Mal's E-Commerce](http://mals-e.com) handles all the shipping calculations, taxes, email notifications, sales records, and multiple payment methods including PayPal, Amazon, Google Checkout, and more, plus offline methods like checks or your own credit card terminal. Shopping cart accounts can be set up free, and there is an active community and good documentation to help you get your store set up.

This version lets you add product purchase buttons to any post or page with a shortcode. As the plugin is developed, features will be added until most of the functionality needed to run an online store will be included. Please post any features you would like to see put in on [the plugin's page](http://xnau.com/wp-malscart/)

An account with [Mal's E-Commerce](http://mals-e.com) is required to use the plugin.

== Installation ==

1. Download the zip file, and unzip it on your computer.
2. Upload this directory (wp-mals-cart) and its contents to the `/wp-content/plugins/` directory

**or**

1. In the admin for your WordPress site, click on "add new" in the plug-ins menu.
2. Search for "wp-mals-cart" in the WP plugin repository and install
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Place [buy_button product="The Product Title" price="25.00"] in your blog posts and pages
5. Additonal features and instructions can be found on the help tab of the plugin's settings page

== Changelog ==

= 0.8.2 =
* better validation in the plugin settings
* added 'units' value to shortcode

= 0.8.1 =
* minor bug fixes
* internationalization of all settings strings
* buttons CSS to new base class

= 0.8 =
* added view cart widget
* added auto_hide feature to veiw cart button
* added purchase return shortcode
* fixed plugin CSS loading on all options pages
* added payment return message shortcodes
* added help page class

= 0.7 =
* refactored settings validation code
* all plugin settings are now held in an object
* added initial setup notification

= 0.6.2 =
* fixed several bugs due to directory name change

= 0.6 =
* initial release
* single-product purchase shortcode

== Upgrade Notice ==

= 0.8.2 =
* minor update fixes validation errors in settings, added 'units' parameter to button shortcode

