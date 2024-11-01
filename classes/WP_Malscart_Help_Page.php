<?php
/**
 * Help pages for WP Mal's Cart E-Commerce Plugin
 *
 * static class for displaying the help pages
 */
 class WP_Malscart_Help_Page {
	 	/**
	 * outputs a help page
	 *
	 * @return string HTML for the help page
	 */
	public function help_page() {
		
		$plugin_data = WP_Malscart::plugin_data();
		
		ob_start();
?>

<div class="read_column">
	<h3><?php echo WP_Malscart::PLUGIN_TITLE?> Help Page</h3>
	<p>Current Version: <?php echo $plugin_data[ 'Version' ]?></p>
	<p>This wordpress plugin is the first development phase of a full-featured WordPress online commerce application using the third-party cart service <a href="http://mals-e.com">Mal&#8217;s Cart (mals-e.com)</a>. Version 1 provides simple product buy buttons as a shortcode to include in your WordPress pages and posts. Clicking the button sends the customer to the shopping cart and adds the product to the cart.</p>
	<p>This plugin represents what I think is the easiest and most straightforward way to begin selling products to your site&#8217;s visitors. It is a great way to get started with online commerce. It&#8217;s also designed to visually match the theme of your website without a lot of customization.</p>
	<h3>Using The Plugin</h3>
	<p>To use this plugin, <strong>you must first establish an account with Mal&#8217;s Cart.</strong> Your username (actually a number) and the cart server domain goes into the General Settings tab of this plugin. You will be provided with this information when you register your account at mals-e.com.</p>
	<h4>How Mal's E-Commerce Works</h4>
	<p>Briefly, the way this works is when a customer on your site clicks on an &quot;add to cart&quot; button for a product, the information about that product is provided to the mals-e.com site, which places the item in the current user's shoppng cart. The customer will be taken to mals-e.com where they will see the item in the cart with it's price and any options they selected. They can then return to your website to continue shopping, or they can complete the transaction by going through the checkout process. All of that is handled at mals-e.com, including shipping calculations and customer email notifications. If the customer chooses an online payment option, they will log in to their payment account (PayPal, Google Checkout, etc.) and complete the payment. When the order has been processed, you will be notified by email and when you log in to your account at mals-e.com, you will see your orders with all the information you need.</p>
	<h4><a id="shortcode" name="shortcode"></a>The Shortcodes</h4>
	<p>There are currently two types of shortcode provided by the plugin: Buy Buttons and Return Messages. A <strong>Buy Button</strong> just lets the customer put the product in the shopping cart. If the product has options, those can be selected as well. The <strong>Return Messages</strong> are placed on the page you have set up for customers to return to after making a purchase.</p>
	<p>To use the Buy Button shortcode, put something like this in your page or post:</p>
	<p><code>[buy_button product=&quot;The Product Name&quot; price=&quot;25.00&quot;]</code></p>
	<p>The two arguments, &#8216;product&#8217; and &#8216;price&#8217; are required. In addition to these two, you can set up a series of options for the product, such as color or size. For example:</p>
	<p><code>[buy_button product=&quot;Test Product&quot; price=&quot;10.00&quot; color=&quot;Product Color:Red,Light Blue,Hunter&#039;s Green&quot; ]</code></p>
	<p>To add product options, add an argument to the shortcode with any name you want (though it can&#8217;t be any of the names used for other things and no spaces or punctuation) and then in quotes the display name of the option and then all the option names with commas in between each one. As you can see, things like space and some punctuation are allowed here.</p>
	<p>In this version of the plugin, these product options can&#8217;t affect the price. They are really just a way to put this extra information into the order so you know which one to send. (When we get to version 2, we will add a <em>product post type</em> that will allow for much more complex pricing schemes as well as product management and display possibilities.)</p>
	<p>Other arguments you can use in the shortcodes are:</p>
	<ul>
		<li><em>width</em> - overrides the default width of the button container</li>
		<li><em>button_text</em> - sets the text displayed on the button itself</li>
	</ul>
</div>
<div class="read_column">
	<h4>Return Message Shortcodes</h4>
	<p>There are two main ways to use the Return Message shortcodes. You can use the [purchase_return] shortcode to insert the shopper's information into the content of the page, such as their name or how they paid for their order. That would look something like this:</p>
	<blockquote>
		<p>Thank you, [purchase_return show=first_name], for your purchase.</p>
	</blockquote>
	<p>But unless this page is one that only a returning customer would visit, everyone else will see a message with words missing. So, there's the Purchase Return Messages, which are defined in the plugin's settings page. They only appear if someone has returned from making a purchase, so they can be put on any page. There's two of them: one is for a thank you message [payment_return_message], the second [mail_payment_return_message] is to remind them where to send the check or money order if that's how they're paying. That one won't show up if the payment was made online.</p>
	<h4>The View Cart Widget</h4>
	<p>The plugin provides a widget that gives your site visitors a way to see the contents of their cart where they can check out or change quantities. To place this widget, go to the Widgets menu in the WordPress admin.</p>
	<p> The View Cart widget also has an &quot;Auto Hide&quot; making the button visible only if there's something in the cart, since there's not much point in going there if it's empty. If you want it to be there anyway, just uncheck the option.</p>
	<a id="malscart" name="malscart"></a>
	<h3>Setting Up Mal&#8217;s Cart</h3>
	<p>Properly setting up the cart can be a little complicated, and I suggest you give this aspect of setting up your store a lot of attention and testing. Getting it wrong can be a source of stress once you begin selling, so work through all the details before you let the customers in. Just a piece of advice from someone who has been there!</p>
	<p>Mal&#8217;s Cart has a very active and helpful <a href="https://www.mals-e.com/forum.php">community</a>, so you won&#8217;t be without help in your questions.</p>
	<h4>Special Mal's Cart Settings</h4>
	<p>It's helpful to know when a visitor to your site has something in their shopping cart. In order to get this information, you must set up your Mal's E-Commerce cart to send cart contents information back with the visitor when they return from adding something to the cart or viewing the cart. The setting is called &quot;Continue shopping button&quot; under the &quot;customize&quot; menu in the shopping cart setup. This should be set to &quot;HTML form&quot; with &quot;using the POST request method.&quot; checked. Lastly, you should check the &quot;Append cart content vars&quot; checkbox.</p>
	<p>When a shopper returns to your site after having made a purchase, information from that purchase can cme back with them. This information can be used to track sales or display a thank you note. To accomplish this, the &quot;return link&quot; setting must have the &quot;request method&quot; set to either of the two POST options.</p>
	<a id="support" name="support"></a>
	<h3>Help and Support</h3>
	<p>Questions about the plugin can be emailed to me, Roland, at <span class='email' rel='xnau.com-webdesign'>my email</span>. If you need more extensive help setting up your store (as I have done for many others) I will want to invoice you for my time, but don&#8217;t think I&#8217;m going to break your bank there. I specialize in small-scale operations with limited budgets (not that my bookkeeper thinks this is smart!) and I know you mostly just want a little help and then be on your way.</p>
	<p>Time permitting, I can also provide full-service online commerce development.</p>
</div>
<div class="read_column">
	<h3>Future Development</h3>
	<p>The general development plan for the plugin is as follows:</p>
	<ol>
		<li>single-product buy button shortcodes</li>
		<li>product post type, multiple product ordering, product management and display</li>
		<li>advanced product attributes, images, pricing</li>
		<li>sales, discounts and specials, related products</li>
		<li>integrate with users, user role discounts (wholesale/retail)</li>
		<li>local sales records</li>
		<li>integrate cart screens with site</li>
	</ol>
	<p>Some of the more advanced features will only be included in a premium version of the plugin.</p>
	<h3>Release History</h3>
	<h4>0.6</h4>
	<ul>
		<li>initial release</li>
		<li>buy button shortcodes</li>
		<li>internationalization</li>
		<li>admin settings page</li>
		<li>help page</li>
	</ul>
	<h4>0.6.2</h4>
	<ul>
		<li> fixed several bugs due to directory name change </li>
	</ul>
	<h4>0.7</h4>
	<ul>
		<li>refactored settings validation code</li>
		<li>all plugin settings are now held in a class object</li>
		<li>added initial setup notification</li>
	</ul>
	<h4>0.7.1</h4>
	<ul>
		<li>added view cart widget</li>
		<li>added auto_hide feature to veiw cart button</li>
		<li>added purchase return shortcode</li>
		<li>fixed plugin CSS loading on all options pages</li>
		<li>added payment return message shortcodes </li>
	</ul>
	<h3>Acknowledgements</h3>
	<p>This project would not have been possible without knowledge gained from the following authors and sites:</p>
	<ul>
		<li><a href="http://alisothegeek.com" rel="author">Aliso the Geek</a></li>
		<li><a href="http://beerpla.net/">Beer Planet </a></li>
		<li><a href="http://w3prodigy.com/">Jay Fortner</a></li>
		<li><a href="http://www.obenlands.de/">Obenlands</a></li>
		<li><a href="http://planetozh.com/blog/">planetOzh</a></li>
		<li><a href="http://codex.wordpress.org">WordPress Codex</a></li>
	</ul>
	<p>and many others who have generously offered their expertise to the community.</p>
</div>
<?php

		return ob_get_clean();

	}
 }
?>
