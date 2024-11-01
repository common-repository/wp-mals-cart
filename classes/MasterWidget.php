<?php 
/**
 * @Author	Jonathon byrd
 * @link http://www.jonathonbyrd.com
 * @Package Wordpress
 * @SubPackage Widgets
 * @copyright Proprietary Software, Copyright Byrd Incorporated. All Rights Reserved
 * @Since 1.0.0
 * 
 * Plugin Name: Master Widget
 * Plugin URI: http://www.redrokk.com
 * Description: <a href="http://redrokk.com" target="_blank">redrokk</a> Designs and develops software for WordPress, Drupal, Joomla, Cakephp, SugarCRM, Symfony and more!
 * Version: 1.0.0
 * Author: redrokk
 * Author URI: http://www.redrokk.com
 * 
 * 
 */

defined('ABSPATH') or die("Cannot access pages directly.");

/**
 * Register a New Master Widget
 * 
 * The following is an array of settings for a single widget.
 * All that you need to worry about is defining this array and the 
 * logic + administrative options for the widget is handled.
 * 
 * The actual display of the widget is not handled by the Master
 * Widget Class and requires that you provide a callback or a file that
 * can be displayed when the widget is shown on the front end.
 * 
 * A nice array of values is provided to you when displaying the widget
 * UI, simply use extract($args) to retrieve three variables full of
 * useful data.
 * 
 * The following code should be placed within your theme/functions.php
 * 
 * ************** ************* *************

//build an array of settings
$docWidget = array(
	'id' => 'first-custom-widget',	//make sure that this is unique
	'title' => 'aaaFirst Widget',	
	'classname' => 'st-custom-wi',	
	'do_wrapper' => true,	
	'show_view' => 'document_widget_view',	
	'fields' => array(
		array(
			'name' => 'Title',
			'desc' => '',
			'id' => 'title',
			'type' => 'text',
			'std' => ''
		),
		array(
			'name' => 'Textarea',
			'desc' => 'Enter big text here',
			'id' => 'textarea_id',
			'type' => 'textarea',
			'std' => 'Default value 2'
		),
		array(
			'name' => 'Select box',
			'id' => 'select_id',
			'type' => 'select',
			'options' => array('Option 1', 'Option 2', 'Option 3')
		),
		array(
			'name' => 'Radio',
			'id' => 'radio_id',
			'type' => 'radio',
			'options' => array(
				array('name' => 'Name 1', 'value' => 'Value 1'),
				array('name' => 'Name 2', 'value' => 'Value 2')
			)
		),
		array(
			'name' => 'Checkbox',
			'id' => 'checkbox_id',
			'type' => 'checkbox'
		),
	)
);

//register this widget
register_master_widget($docWidget);

function document_widget_view( $args )
{
	extract($args);
	?>
	the view for my widget
	<?php
}

*/

/**
 * Only display once
 * 
 * This line of code will ensure that we only run the master widget class
 * a single time. We don't need to be throwing errors.
 */
if (!class_exists('MasterWidgetClass')) :

/**
 * Initializing 
 * 
 * The directory separator is different between linux and microsoft servers.
 * Thankfully php sets the DIRECTORY_SEPARATOR constant so that we know what
 * to use.
 */
defined("DS") or define("DS", DIRECTORY_SEPARATOR);

/**
 * Actions and Filters
 * 
 * Register any and all actions here. Nothing should actually be called 
 * directly, the entire system will be based on these actions and hooks.
 */
add_action( 'widgets_init', 'widgets_init_declare_registered', 1 );

/**
 * Register a widget
 * 
 * @param $widget
 */
function register_master_widget( $widget = null )
{
	global $masterWidgets;
	if (!isset($masterWidgets))
	{
		$masterWidgets = array();
	}
	
	if (!is_array($widget)) return false;
	
	$defaults = array(
		'id' => '1',
		'title' => 'Generic Widget',
		'classname' => $widget['id'],
		'do_wrapper' => true,
		'description' => '',
		/*'width' => 200,
		'height' => 200,*/
		'fields' => array(),
	);
	
	$masterWidgets[$widget['id']] = wp_parse_args($widget, $defaults);
	
	return true;
}

/**
 * Get the registered widgets
 * 
 * @return array
 */
function get_registered_masterwidgets()
{
	global $masterWidgets;
	
	if (!did_action('init_master_widgets'))
		do_action('init_master_widgets');
		
	return $masterWidgets;
}

/**
 * Initialize the widgets
 * 
 * @return boolean
 */
function widgets_init_declare_registered()
{
	//initialziing variables
	global $wp_widget_factory;
	$widgets = get_registered_masterwidgets();
	
	//reasons to fail
	if (empty($widgets) || !is_array($widgets)) return false;
	
	foreach ($widgets as $id => $widget)
	{
		$wp_widget_factory->widgets[$id] =& new MasterWidgetClass( $widget );
	}
	
	return false;
}

/**
 * Multiple Widget Master Class
 * 
 * This class allows us to easily create qidgets without having to deal with the
 * mass of php code.
 * 
 * @author byrd
 * @since 1.3
 */
class MasterWidgetClass extends WP_Widget
{
	/**
	 * Constructor.
	 * 
	 * @param $widget
	 */
	function MasterWidgetClass( $widget )
	{
		$this->widget = apply_filters('master_widget_setup', $widget);
		$widget_ops = array(
			'classname' => $this->widget['classname'], 
			'description' => $this->widget['description'] 
		);
		$this->WP_Widget($this->widget['id'], $this->widget['title'], $widget_ops);
	}
	
	/**
	 * Display the Widget View
	 * 
	 * @example extract the args within the view template
	 
	 extract($args[1]); 
	 
	 * @param $args
	 * @param $instance
	 */	
	function widget($sidebar, $instance)
	{
		//initializing variables
		$widget = $this->widget;
		$widget['number'] = $this->number;
		
		$args = array(
			'sidebar' => $sidebar,
			'widget' => $widget,
			'params' => $instance,
		);
		
		$show_view = apply_filters('master_widget_view', $this->widget['show_view'], $widget, $instance, $sidebar);
		
		$title = apply_filters( 'master_widget_title', $instance['title'] );
		
		if ( $widget['do_wrapper'] )
			echo $sidebar['before_widget'];
		
		if ( $title && $widget['do_wrapper'] )
			echo $sidebar['before_title'] . $title . $sidebar['after_title'];
		
		//give people an opportunity
		do_action('master_widget_show_'.$widget['id']);
			
		//call the function if it exists
		if (is_callable($show_view))
			call_user_func( $show_view, $args );
		
		//load the file if it exists
		elseif (file_exists($show_view))
			require $show_view;
			
		//echo if we can't figure it out
		else echo $show_view;
		
		if ($widget['do_wrapper'])
			echo $sidebar['after_widget'];
		
	}
	
	/**
	 * Update from within the admin
	 * 
	 * @param $new_instance
	 * @param $old_instance
	 */
	function update($new_instance, $old_instance)
	{
		//initializing variables
		$new_instance = array_map('strip_tags', $new_instance);
		$instance = wp_parse_args($new_instance, $old_instance);
		
		return $instance;
	}
	
	/**
	 * Display the options form
	 * 
	 * @param $instance
	 */
	function form($instance)
	{
		//reasons to fail
		if (empty($this->widget['fields'])) return false;
		
		$defaults = array(
			'id' => '',
			'name' => '',
			'desc' => '',
			'type' => '',
			'options' => '',
			'std' => '',
		);
		
		do_action('master_widget_before');
		foreach ($this->widget['fields'] as $field)
		{
			//making sure we don't throw strict errors
			$field = wp_parse_args($field, $defaults);
			
			$meta = false;
			if (isset($field['id']) && array_key_exists($field['id'], $instance))
				@$meta = attribute_escape($instance[$field['id']]);
			
			if ($field['type'] != 'custom' && $field['type'] != 'metabox') 
			{
				echo '<p><label for="',$this->get_field_id($field['id']),'">';
			}
			if (isset($field['name']) && $field['name']) echo $field['name'],':';
			
			switch ($field['type'])
			{
				case 'text':
					echo '<input type="text" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" value="', ($meta ? $meta : @$field['std']), '" class="vibe_text" />', 
					'<br/><span class="description">', @$field['desc'], '</span>';
					break;
				case 'textarea':
					echo '<textarea class="vibe_textarea" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" cols="60" rows="4" style="width:97%">', $meta ? $meta : @$field['std'], '</textarea>', 
					'<br/><span class="description">', @$field['desc'], '</span>';
					break;
				case 'select':
				
					echo '<select class="vibe_select" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '">';
					foreach ($field['options'] as $option)
					{
						echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
					}
					echo '</select>', 
					'<br/><span class="description">', @$field['desc'], '</span>';
					break;
				case 'radio':
					foreach ($field['options'] as $option)
					{
						echo '<input class="vibe_radio" type="radio" name="', $this->get_field_name($field['id']), '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', 
						$option['name'];
					}
					echo '<br/><span class="description">', @$field['desc'], '</span>';
					break;
				case 'checkbox':
					echo '<input type="hidden" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '" /> ', 
						 '<input class="vibe_checkbox" type="checkbox" name="', $this->get_field_name($field['id']), '" id="', $this->get_field_id($field['id']), '"', $meta ? ' checked="checked"' : '', ' /> ', 
					'<br/><span class="description">', @$field['desc'], '</span>';
					break;
				case 'custom':
					echo $field['std'];
					break;
			}
			
			if ($field['type'] != 'custom' && $field['type'] != 'metabox') 
			{
				echo '</label></p>';
			}
		}
		do_action('master_widget_after');
		return;
	}
	
}// ends Master Widget Class

endif; //if !class_exists
